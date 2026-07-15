(function () {
    const MAX_BYTES = 5 * 1024 * 1024;
    const MAX_DIMENSION = 1600;
    const JPEG_QUALITY = 0.88;

    function isImageFile(file) {
        return file && (file.type.startsWith('image/') || /\.(jpe?g|png|gif|webp)$/i.test(file.name));
    }

    function readAsImage(file) {
        return new Promise(function (resolve, reject) {
            const url = URL.createObjectURL(file);
            const img = new Image();
            img.onload = function () {
                URL.revokeObjectURL(url);
                resolve(img);
            };
            img.onerror = function () {
                URL.revokeObjectURL(url);
                reject(new Error('invalid'));
            };
            img.src = url;
        });
    }

    function canvasToBlob(canvas, type, quality) {
        return new Promise(function (resolve) {
            canvas.toBlob(resolve, type, quality);
        });
    }

    async function preparePhotoFile(file) {
        if (!isImageFile(file)) {
            throw new Error('Lütfen JPEG, PNG, GIF veya WebP formatında bir fotoğraf seçin.');
        }

        if (file.size <= MAX_BYTES && file.type === 'image/jpeg') {
            return file;
        }

        const img = await readAsImage(file);
        const scale = Math.min(1, MAX_DIMENSION / Math.max(img.width, img.height));
        const width = Math.max(1, Math.round(img.width * scale));
        const height = Math.max(1, Math.round(img.height * scale));
        const canvas = document.createElement('canvas');
        canvas.width = width;
        canvas.height = height;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(img, 0, 0, width, height);

        const blob = await canvasToBlob(canvas, 'image/jpeg', JPEG_QUALITY);
        if (!blob) {
            throw new Error('Fotoğraf işlenemedi. Başka bir görsel deneyin.');
        }

        if (blob.size > MAX_BYTES) {
            throw new Error('Fotoğraf 5 MB sınırını aşıyor. Daha küçük bir görsel seçin.');
        }

        const baseName = file.name.replace(/\.[^.]+$/, '') || 'profile';
        return new File([blob], baseName + '.jpg', { type: 'image/jpeg', lastModified: Date.now() });
    }

    function setPreview(container, file) {
        if (!container) return;

        const img = document.createElement('img');
        img.src = URL.createObjectURL(file);
        img.alt = 'Profil önizleme';

        const avatar = container.querySelector('.story-avatar');
        if (avatar) {
            avatar.innerHTML = '';
            avatar.appendChild(img);
            return;
        }

        container.innerHTML = '';
        container.appendChild(img);
    }

    function showError(form, message) {
        const scope = form.closest('.profile-page, .form-card, .auth-form-wrap, .register-photo-block');
        let el = scope?.querySelector('.profile-photo-error');
        if (!el) {
            el = document.createElement('small');
            el.className = 'form-error profile-photo-error';
            const anchor = form.closest('.profile-photo-wrap')
                || form.closest('.register-photo-meta')
                || form;
            anchor.insertAdjacentElement('afterend', el);
        }
        el.textContent = message;
    }

    function clearError(form) {
        const scope = form.closest('.profile-page, .form-card, .auth-form-wrap, .register-photo-block');
        scope?.querySelectorAll('.profile-photo-error').forEach(function (node) {
            node.remove();
        });
    }

    function setLoading(form, loading) {
        const btn = form.querySelector('.profile-photo-change');
        if (btn) {
            btn.classList.toggle('profile-photo-change--loading', loading);
            btn.setAttribute('aria-busy', loading ? 'true' : 'false');
        }
        const input = form.querySelector('input[type="file"]');
        if (input) input.disabled = loading;
    }

    async function uploadWithFetch(form, file) {
        const token = form.querySelector('[name="_token"]')?.value;
        if (!token) {
            throw new Error('Oturum süresi doldu. Sayfayı yenileyip tekrar deneyin.');
        }

        const formData = new FormData();
        formData.append('_token', token);
        formData.append('photo', file, file.name);

        const response = await fetch(form.action, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin',
            redirect: 'follow',
        });

        if (response.redirected) {
            window.location.href = response.url;
            return;
        }

        if (!response.ok) {
            throw new Error('Profil fotoğrafı yüklenemedi. Lütfen tekrar deneyin.');
        }

        window.location.reload();
    }

    async function submitRegisterForm(form, file) {
        const formData = new FormData(form);
        formData.delete('photo');
        if (file) {
            formData.append('photo', file, file.name);
        }

        const response = await fetch(form.action, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin',
            redirect: 'manual',
        });

        if (response.type === 'opaqueredirect' || (response.status >= 300 && response.status < 400)) {
            window.location.href = response.headers.get('Location') || '/feed';
            return;
        }

        if (response.redirected) {
            window.location.href = response.url;
            return;
        }

        const html = await response.text();
        document.open();
        document.write(html);
        document.close();
    }

    async function handlePhotoInput(input, autoSubmit) {
        const form = input.form;
        const file = input.files?.[0];
        if (!form || !file) return;

        clearError(form);

        try {
            if (autoSubmit) setLoading(form, true);
            const prepared = await preparePhotoFile(file);
            const preview = document.getElementById('profilePhotoPreview')
                || document.getElementById('registerPhotoPreview');
            setPreview(preview, prepared);

            if (autoSubmit) {
                await uploadWithFetch(form, prepared);
            } else {
                input._preparedPhoto = prepared;
            }
        } catch (err) {
            if (autoSubmit) setLoading(form, false);
            input.value = '';
            input._preparedPhoto = null;
            showError(form, err.message || 'Fotoğraf yüklenemedi.');
        }
    }

    document.querySelectorAll('.profile-photo-form input[type="file"]').forEach(function (input) {
        input.removeAttribute('required');
        input.addEventListener('change', function () {
            handlePhotoInput(input, true);
        });
    });

    document.querySelectorAll('.register-photo-input').forEach(function (input) {
        input.addEventListener('change', function () {
            handlePhotoInput(input, false);
        });
    });

    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', async function (e) {
            const input = registerForm.querySelector('.register-photo-input');
            const prepared = input?._preparedPhoto;
            const rawFile = input?.files?.[0];

            if (!prepared && !rawFile) return;

            e.preventDefault();
            clearError(registerForm);

            const submitBtn = registerForm.querySelector('[type="submit"]');
            if (submitBtn) submitBtn.disabled = true;

            try {
                const file = prepared || await preparePhotoFile(rawFile);
                await submitRegisterForm(registerForm, file);
            } catch (err) {
                if (submitBtn) submitBtn.disabled = false;
                showError(registerForm, err.message || 'Kayıt sırasında fotoğraf yüklenemedi.');
            }
        });
    }

    document.querySelectorAll('[data-profile-open-story]').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            if (e.target.closest('.profile-photo-change')) return;
            const index = parseInt(btn.dataset.profileOpenStory, 10);
            const storyItem = document.querySelector('.story-item[data-story-index="' + index + '"]');
            if (storyItem) {
                storyItem.click();
                return;
            }
            const viewer = document.getElementById('igStoryViewer');
            if (viewer && typeof window.gkOpenStory === 'function') {
                window.gkOpenStory(index);
            }
        });
    });
})();
