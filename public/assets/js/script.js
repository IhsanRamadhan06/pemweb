// niflix_project/public/assets/js/script.js

document.addEventListener('DOMContentLoaded', () => {
    const menuToggle = document.querySelector('.menu-toggle');
    const navMenu = document.querySelector('.nav-menu');
    const mainContent = document.querySelector('main');
    const header = document.querySelector('header');

    if (menuToggle && navMenu && mainContent && header) {
        menuToggle.addEventListener('click', () => {
            navMenu.classList.toggle('active');
            // Sesuaikan margin-top main content berdasarkan tinggi header dan apakah menu aktif
            if (navMenu.classList.contains('active')) {
                mainContent.style.marginTop = `${header.offsetHeight + navMenu.offsetHeight}px`;
            } else {
                mainContent.style.marginTop = `${header.offsetHeight}px`;
            }
        });

        // Tambahkan event listener untuk mereset margin-top saat ukuran jendela berubah
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 768) {
                // Jika lebar lebih dari 768px (breakpoint desktop)
                navMenu.classList.remove('active'); // Pastikan menu mobile tidak aktif
                mainContent.style.marginTop = `${header.offsetHeight}px`; // Reset margin ke tinggi header saja
            } else {
                // Di layar mobile, jika menu sedang aktif, hitung ulang margin
                if (navMenu.classList.contains('active')) {
                    mainContent.style.marginTop = `${header.offsetHeight + navMenu.offsetHeight}px`;
                }
            }
        });
    }

    // --- Kode AJAX untuk Profil ---
    const profileForm = document.querySelector('.profile-container form');
    const notificationContainer = document.getElementById('profile-notification'); // Tambahkan elemen ini di profile.php
    const profilePhotoImg = document.querySelector('.profile-photo');
    const currentPasswordInput = document.getElementById('current_password');
    const newPasswordInput = document.getElementById('new_password');
    const confirmPasswordInput = document.getElementById('confirm_password');

    if (profileForm && notificationContainer && profilePhotoImg) {
        profileForm.addEventListener('submit', async (e) => {
            e.preventDefault(); // Mencegah submit form tradisional

            notificationContainer.innerHTML = ''; // Bersihkan notifikasi sebelumnya

            const formData = new FormData(profileForm); // Ambil data form, termasuk file
            const url = profileForm.action; // Ambil URL dari atribut action form

            // Tambahkan indikator loading
            profileForm.querySelector('.btn-update').disabled = true;
            profileForm.querySelector('.btn-update').textContent = 'Updating...';

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    body: formData, // FormData otomatis mengatur header Content-Type
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest', // Menandai ini adalah request AJAX
                    },
                });

                const result = await response.json(); // Menguraikan respons JSON

                if (result.success) {
                    notificationContainer.innerHTML = `<div class="notification success">${result.message}</div>`;
                    // Update foto profil jika diunggah
                    if (result.new_photo_url) {
                        profilePhotoImg.src = result.new_photo_url;
                    }
                    // Bersihkan kolom password setelah update berhasil
                    if (result.password_updated) {
                        if (currentPasswordInput) currentPasswordInput.value = '';
                        if (newPasswordInput) newPasswordInput.value = '';
                        if (confirmPasswordInput) confirmPasswordInput.value = '';
                    }
                } else {
                    notificationContainer.innerHTML = `<div class="notification error">${result.message}</div>`;
                }
            } catch (error) {
                console.error('Error:', error);
                notificationContainer.innerHTML = `<div class="notification error">Terjadi kesalahan jaringan atau server.</div>`;
            } finally {
                // Kembalikan tombol ke keadaan semula
                profileForm.querySelector('.btn-update').disabled = false;
                profileForm.querySelector('.btn-update').textContent = 'Update Profile';
            }
        });
    }
});
