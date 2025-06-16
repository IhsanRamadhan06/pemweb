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
    const notificationContainer = document.getElementById('profile-notification');
    const profilePhotoImg = document.querySelector('.profile-photo');
    const currentPasswordInput = document.getElementById('current_password');
    const newPasswordInput = document.getElementById('new_password');
    const confirmPasswordInput = document.getElementById('confirm_password');

    if (profileForm && notificationContainer && profilePhotoImg) {
        profileForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            notificationContainer.innerHTML = '';

            const formData = new FormData(profileForm);
            const url = profileForm.action;

            profileForm.querySelector('.btn-update').disabled = true;
            profileForm.querySelector('.btn-update').textContent = 'Updating...';

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                const result = await response.json();

                if (result.success) {
                    notificationContainer.innerHTML = `<div class="notification success">${result.message}</div>`;
                    if (result.new_photo_url) {
                        profilePhotoImg.src = result.new_photo_url;
                    }
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
                profileForm.querySelector('.btn-update').disabled = false;
                profileForm.querySelector('.btn-update').textContent = 'Update Profile';
            }
        });
    }

    // --- Slider Logic for Daftar Series Page ---
    const seriesSliderContainer = document.querySelector('.series-container .slider-container');
    const leftArrow = document.querySelector('.series-container .left-arrow');
    const rightArrow = document.querySelector('.series-container .right-arrow');

    if (seriesSliderContainer && leftArrow && rightArrow) {
        const calculateScrollAmount = () => {
            const firstSliderItem = seriesSliderContainer.querySelector('.slider-item');
            let itemWidth = 0;
            if (firstSliderItem) {
                itemWidth = firstSliderItem.offsetWidth + 15;
            }
            let itemsToScroll;
            if (window.innerWidth <= 425) {
                itemsToScroll = 2;
            } else {
                itemsToScroll = 4;
            }
            return itemWidth > 0 ? itemWidth * itemsToScroll : seriesSliderContainer.offsetWidth / itemsToScroll;
        };
        let scrollAmount = calculateScrollAmount();
        leftArrow.addEventListener('click', () => {
            seriesSliderContainer.scrollBy({
                left: -scrollAmount,
                behavior: 'smooth'
            });
        });
        rightArrow.addEventListener('click', () => {
            seriesSliderContainer.scrollBy({
                left: scrollAmount,
                behavior: 'smooth'
            });
        });
        window.addEventListener('resize', () => {
            scrollAmount = calculateScrollAmount();
        });
    }

    // --- AJAX for Series Like Button (Halaman Daftar Series) ---
    const likeButtons = document.querySelectorAll('.btn-like-series-ajax');

    likeButtons.forEach(button => {
        button.addEventListener('click', async (e) => {
            e.preventDefault();

            const seriesId = button.dataset.seriesId;
            const icon = button.querySelector('i');
            const totalLikesSpan = document.querySelector(`.total-likes-${seriesId}`);

            const baseUrl = window.location.origin + '<?= $basePath ?>';

            try {
                const formData = new FormData();
                formData.append('series_id', seriesId);

                const response = await fetch(`${baseUrl}/daftar_series/toggleLikeAjax`, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                const result = await response.json();

                if (result.success) {
                    if (result.is_liked_by_user) {
                        icon.classList.remove('bx-heart');
                        icon.classList.add('bxs-heart');
                        button.dataset.isLiked = '1';
                    } else {
                        icon.classList.remove('bxs-heart');
                        icon.classList.add('bx-heart');
                        button.dataset.isLiked = '0';
                    }
                    if (totalLikesSpan) {
                        totalLikesSpan.textContent = result.total_likes;
                    }
                    console.log(result.message);
                } else {
                    console.error('Error toggling like:', result.message);
                    if (result.redirect) {
                        window.location.href = result.redirect;
                    }
                }
            } catch (error) {
                console.error('Network or server error:', error);
            }
        });
    });
});
