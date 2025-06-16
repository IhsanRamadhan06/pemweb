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

            const baseUrl = window.location.origin + BASE_URL; // Use BASE_URL

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

    // --- AJAX Validation for Series Create/Edit Form ---
    const seriesForm = document.querySelector('.form-container form');
    if (seriesForm) {
        const inputFields = seriesForm.querySelectorAll('input[data-field], textarea[data-field], select[data-field]');
        const currentYear = new Date().getFullYear();
        let validationTimers = {}; // Object to hold timers for debouncing

        const validateField = async (inputElement) => {
            const fieldName = inputElement.dataset.field;
            const fieldValue = inputElement.value;
            const errorSpan = document.getElementById(`${fieldName}-error`);

            // Clear previous error message immediately
            if (errorSpan) {
                errorSpan.textContent = '';
                errorSpan.classList.remove('error'); // Ensure error class is removed
            }

            // Client-side validation first (less aggressive, mainly for empty or obviously malformed inputs)
            let clientSideError = '';
            if (fieldName === 'release_year') {
                const year = parseInt(fieldValue);
                if (fieldValue.trim() === '') {
                    clientSideError = 'Tahun rilis tidak boleh kosong.';
                } else if (isNaN(year) || year <= 0) { // Check for non-numeric or zero/negative
                    clientSideError = 'Tahun rilis harus berupa angka valid.';
                } else if (year > currentYear) {
                    clientSideError = `Tahun rilis tidak boleh lebih dari tahun sekarang (${currentYear}).`;
                } else if (year < 1888) {
                    clientSideError = 'Tahun rilis terlalu lama (minimal 1888).';
                }
            } else if (fieldName === 'title' && fieldValue.trim() === '') {
                clientSideError = 'Judul series tidak boleh kosong.';
            } else if (fieldName === 'description' && fieldValue.trim() === '') {
                clientSideError = 'Deskripsi series tidak boleh kosong.';
            } else if (fieldName === 'image_url' && fieldValue.trim() !== '' && !/^(https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|www\.[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9]+\.[^\s]{2,}|www\.[a-zA-Z0-9]+\.[^\s]{2,})$/i.test(fieldValue)) {
                clientSideError = 'Format URL gambar tidak valid.';
            }

            if (clientSideError) {
                if (errorSpan) {
                    errorSpan.textContent = clientSideError;
                    errorSpan.classList.add('error');
                }
                return; // Stop here if client-side validation fails
            }

            // If client-side checks pass, proceed to server-side validation
            const formData = new FormData();
            formData.append('field', fieldName);
            formData.append('value', fieldValue);

            try {
                const baseUrl = window.location.origin + BASE_URL; // Use BASE_URL
                const response = await fetch(`${baseUrl}/daftar_series/validateFieldAjax`, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                const result = await response.json();

                if (!result.isValid) {
                    if (errorSpan) {
                        errorSpan.textContent = result.message;
                        errorSpan.classList.add('error');
                    }
                } else {
                    // If server-side validation passes, ensure no error message is displayed
                    if (errorSpan) {
                        errorSpan.textContent = '';
                        errorSpan.classList.remove('error');
                    }
                }
            } catch (error) {
                console.error('Error during AJAX validation:', error);
                // Only show a generic error if it's a network/server issue, not a validation error from server
                if (errorSpan && !errorSpan.textContent) { // Only set if no client-side error already
                    errorSpan.textContent = 'Terjadi kesalahan validasi.'; // More specific could be "Terjadi kesalahan koneksi."
                    errorSpan.classList.add('error');
                }
            }
        };

        inputFields.forEach(input => {
            input.addEventListener('input', (e) => {
                const fieldName = e.target.dataset.field;
                // Clear any existing timer for this field
                if (validationTimers[fieldName]) {
                    clearTimeout(validationTimers[fieldName]);
                }
                // Set a new timer to call validateField after a delay
                // This prevents validation on every keystroke but still provides near real-time feedback
                validationTimers[fieldName] = setTimeout(() => {
                    validateField(e.target);
                }, 500); // 500ms delay
            });

            input.addEventListener('blur', (e) => {
                const fieldName = e.target.dataset.field;
                // Immediately validate on blur, cancelling any pending input timer
                if (validationTimers[fieldName]) {
                    clearTimeout(validationTimers[fieldName]);
                }
                validateField(e.target);
            });
        });
    }

    // --- AJAX Validation for Film Create/Edit Form ---
    const filmForm = document.querySelector('.form-container form'); // Ini bisa sama, tapi pastikan hanya menargetkan form yang relevan
    // Anda mungkin perlu menargetkan form secara lebih spesifik jika ada banyak form dengan class .form-container form
    // Contoh: const filmForm = document.getElementById('createFilmForm') || document.getElementById('editFilmForm');
    // Untuk saat ini, kita asumsikan .form-container form hanya ada di halaman create/edit film/series
    // dan logic `if (filmForm)` akan memastikan kode hanya berjalan jika form film ada.

    if (filmForm) {
        // Cek apakah ini form Film atau Series. Jika form Series sudah ditangani, lewati ini.
        // Asumsi: form Series memiliki input 'title' dengan data-field='title' dan form Film juga.
        // Kita bisa periksa URL atau keberadaan elemen spesifik.
        // Untuk kesederhanaan, kita akan jalankan jika filmForm ada dan belum ditangani oleh logic seriesForm sebelumnya.
        const titleInputFilm = filmForm.querySelector('input[data-field="title"]');
        // Pastikan ini adalah form film dan bukan form series yang sudah ditangani
        // Salah satu cara adalah memeriksa action URL atau ID spesifik pada form jika ada
        const isFilmForm = filmForm.action.includes('daftar_film'); // Asumsi action URL mengandung 'daftar_film'

        if (isFilmForm) {
            const inputFieldsFilm = filmForm.querySelectorAll('input[data-field], textarea[data-field], select[data-field]');
            const currentYearFilm = new Date().getFullYear();
            let validationTimersFilm = {}; // Objek untuk menyimpan timer untuk debouncing

            const validateFieldFilm = async (inputElement) => {
                const fieldName = inputElement.dataset.field;
                const fieldValue = inputElement.value;
                const errorSpan = document.getElementById(`${fieldName}-error`);

                // Hapus pesan error sebelumnya
                if (errorSpan) {
                    errorSpan.textContent = '';
                    errorSpan.classList.remove('error');
                }

                // Validasi sisi klien awal
                let clientSideError = '';
                if (fieldName === 'release_year') {
                    const year = parseInt(fieldValue);
                    if (fieldValue.trim() === '') {
                        clientSideError = 'Tahun rilis tidak boleh kosong.';
                    } else if (isNaN(year) || year <= 0) {
                        clientSideError = 'Tahun rilis harus berupa angka valid.';
                    } else if (year > currentYearFilm) {
                        clientSideError = `Tahun rilis tidak boleh lebih dari tahun sekarang (${currentYearFilm}).`;
                    } else if (year < 1888) {
                        clientSideError = 'Tahun rilis terlalu lama (minimal 1888).';
                    }
                } else if (fieldName === 'title' && fieldValue.trim() === '') {
                    clientSideError = 'Judul film tidak boleh kosong.';
                } else if (fieldName === 'description' && fieldValue.trim() === '') {
                    clientSideError = 'Deskripsi film tidak boleh kosong.';
                } else if (fieldName === 'image_url' && fieldValue.trim() !== '' && !/^(https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|www\.[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9]+\.[^\s]{2,}|www\.[a-zA-Z0-9]+\.[^\s]{2,})$/i.test(fieldValue)) {
                    clientSideError = 'Format URL gambar tidak valid.';
                }

                if (clientSideError) {
                    if (errorSpan) {
                        errorSpan.textContent = clientSideError;
                        errorSpan.classList.add('error');
                    }
                    return; // Hentikan jika validasi klien gagal
                }

                // Jika validasi klien lolos, lanjutkan ke validasi server
                const formData = new FormData();
                formData.append('field', fieldName);
                formData.append('value', fieldValue);

                try {
                    const baseUrl = window.location.origin + BASE_URL;
                    const response = await fetch(`${baseUrl}/daftar_film/validateFieldAjax`, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });

                    const result = await response.json();

                    if (!result.isValid) {
                        if (errorSpan) {
                            errorSpan.textContent = result.message;
                            errorSpan.classList.add('error');
                        }
                    } else {
                        if (errorSpan) {
                            errorSpan.textContent = '';
                            errorSpan.classList.remove('error');
                        }
                    }
                } catch (error) {
                    console.error('Error during AJAX validation for film:', error);
                    if (errorSpan && !errorSpan.textContent) {
                        errorSpan.textContent = 'Terjadi kesalahan validasi atau koneksi.';
                        errorSpan.classList.add('error');
                    }
                }
            };

            inputFieldsFilm.forEach(input => {
                input.addEventListener('input', (e) => {
                    const fieldName = e.target.dataset.field;
                    if (validationTimersFilm[fieldName]) {
                        clearTimeout(validationTimersFilm[fieldName]);
                    }
                    validationTimersFilm[fieldName] = setTimeout(() => {
                        validateFieldFilm(e.target);
                    }, 500); // 500ms delay
                });

                input.addEventListener('blur', (e) => {
                    const fieldName = e.target.dataset.field;
                    if (validationTimersFilm[fieldName]) {
                        clearTimeout(validationTimersFilm[fieldName]);
                    }
                    validateFieldFilm(e.target);
                });
            });
        }
    }
});
