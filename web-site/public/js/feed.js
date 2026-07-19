(function () {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

    const POST_MAX_WIDTH = 1080;
    const STORY_IMAGE_MAX_WIDTH = 1080;
    const IMAGE_QUALITY = 0.82;
    const STORY_VIDEO_MAX_BYTES = 25 * 1024 * 1024;
    const STORY_VIDEO_MAX_SECONDS = 15;

    const storyVideoState = {
        duration: 0,
        startTime: 0,
        needsTrim: false,
        previewUrl: null,
    };

    function formatVideoTime(seconds) {
        const safe = Math.max(0, seconds || 0);
        const mins = Math.floor(safe / 60);
        const secs = Math.floor(safe % 60);
        return mins + ':' + String(secs).padStart(2, '0');
    }

    function resetStoryVideoState() {
        if (storyVideoState.previewUrl) {
            URL.revokeObjectURL(storyVideoState.previewUrl);
        }
        storyVideoState.duration = 0;
        storyVideoState.startTime = 0;
        storyVideoState.needsTrim = false;
        storyVideoState.previewUrl = null;

        const trimPanel = document.getElementById('storyVideoTrim');
        const trimSlider = document.getElementById('storyTrimStart');
        if (trimPanel) trimPanel.hidden = true;
        if (trimSlider) {
            trimSlider.value = '0';
            trimSlider.max = '0';
        }
    }

    function pickRecorderMimeType() {
        const types = [
            'video/webm;codecs=vp9,opus',
            'video/webm;codecs=vp8,opus',
            'video/webm;codecs=vp9',
            'video/webm;codecs=vp8',
            'video/webm',
        ];
        for (let i = 0; i < types.length; i++) {
            if (typeof MediaRecorder !== 'undefined' && MediaRecorder.isTypeSupported(types[i])) {
                return types[i];
            }
        }
        return '';
    }

    function seekVideo(video, time) {
        return new Promise(function (resolve) {
            if (Math.abs(video.currentTime - time) < 0.05) {
                resolve();
                return;
            }
            video.addEventListener('seeked', function onSeeked() {
                video.removeEventListener('seeked', onSeeked);
                resolve();
            });
            video.currentTime = time;
        });
    }

    async function extractVideoClip(file, startSeconds, durationSeconds) {
        const mimeType = pickRecorderMimeType();
        if (!mimeType) {
            throw new Error('Tarayıcınız video kırpmayı desteklemiyor. Daha kısa bir video deneyin.');
        }

        const url = URL.createObjectURL(file);
        const video = document.createElement('video');
        video.playsInline = true;
        video.muted = false;
        video.src = url;

        await new Promise(function (resolve, reject) {
            video.onloadedmetadata = resolve;
            video.onerror = reject;
        });

        await seekVideo(video, startSeconds);

        const stream = video.captureStream ? video.captureStream() : video.mozCaptureStream?.();
        if (!stream) {
            URL.revokeObjectURL(url);
            throw new Error('Video işlenemedi. Lütfen tekrar deneyin.');
        }

        const recorder = new MediaRecorder(stream, { mimeType: mimeType });
        const chunks = [];

        return new Promise(function (resolve, reject) {
            recorder.ondataavailable = function (event) {
                if (event.data && event.data.size) {
                    chunks.push(event.data);
                }
            };
            recorder.onstop = function () {
                URL.revokeObjectURL(url);
                video.pause();
                video.src = '';
                const blobType = mimeType.split(';')[0];
                const blob = new Blob(chunks, { type: blobType });
                if (!blob.size) {
                    reject(new Error('Video kırpılamadı. Lütfen tekrar deneyin.'));
                    return;
                }
                const baseName = file.name.replace(/\.[^.]+$/, '') || 'story';
                resolve(new File([blob], baseName + '-clip.webm', { type: blobType, lastModified: Date.now() }));
            };
            recorder.onerror = function () {
                URL.revokeObjectURL(url);
                reject(new Error('Video kaydı başarısız.'));
            };

            recorder.start(200);
            video.play().catch(function () {
                video.muted = true;
                video.play();
            });

            window.setTimeout(function () {
                try {
                    recorder.stop();
                } catch (err) {
                    reject(err);
                }
            }, durationSeconds * 1000 + 150);
        });
    }

    function openModal(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        modal.hidden = false;
        document.body.classList.add('ig-compose-open');
    }

    function closeModals() {
        document.querySelectorAll('.ig-compose-modal').forEach(function (modal) {
            modal.hidden = true;
        });
        document.body.classList.remove('ig-compose-open');
    }

    function resetComposeForm(formId, inputId, previewId, previewMediaId, submitId, progressId, statusId) {
        const form = document.getElementById(formId);
        const input = document.getElementById(inputId);
        const preview = document.getElementById(previewId);
        const previewMedia = document.getElementById(previewMediaId);
        const submit = document.getElementById(submitId);
        const progress = document.getElementById(progressId);
        const status = document.getElementById(statusId);

        if (form) form.reset();
        if (previewMedia) {
            previewMedia.innerHTML = '';
            previewMedia.hidden = true;
        }
        if (preview) preview.hidden = false;
        if (submit) {
            submit.disabled = true;
            submit.textContent = 'Paylaş';
            submit.classList.remove('ig-compose-submit--loading');
        }
        if (progress) progress.hidden = true;
        if (status) {
            status.hidden = true;
            status.textContent = '';
            status.className = 'ig-compose-status';
        }
        if (input) input.value = '';
        if (formId === 'storyComposeForm') {
            resetStoryVideoState();
        }

        const caption = form?.querySelector('.ig-compose-caption');
        if (caption) {
            caption.dispatchEvent(new Event('input', { bubbles: true }));
        }
        const emojiPanel = form?.querySelector('[data-emoji-panel]');
        if (emojiPanel) emojiPanel.hidden = true;
        const emojiToggle = form?.querySelector('[data-emoji-toggle]');
        if (emojiToggle) emojiToggle.setAttribute('aria-expanded', 'false');
    }

    function initComposeEmoji() {
        document.querySelectorAll('.ig-compose-caption-wrap').forEach(function (wrap) {
            const textarea = wrap.querySelector('.ig-compose-caption');
            const toggle = wrap.querySelector('[data-emoji-toggle]');
            const panel = wrap.querySelector('[data-emoji-panel]');
            const counter = wrap.querySelector('.ig-compose-caption-count');
            if (!textarea || !toggle || !panel) return;

            function updateCount() {
                if (!counter) return;
                const len = textarea.value.length;
                const max = parseInt(textarea.getAttribute('maxlength') || '500', 10);
                counter.textContent = len + '/' + max;
                counter.classList.toggle('ig-compose-caption-count--near', len >= max - 40 && len < max);
                counter.classList.toggle('ig-compose-caption-count--max', len >= max);
            }

            function insertEmoji(emoji) {
                const max = parseInt(textarea.getAttribute('maxlength') || '500', 10);
                const start = textarea.selectionStart ?? textarea.value.length;
                const end = textarea.selectionEnd ?? textarea.value.length;
                const nextLen = textarea.value.length - (end - start) + emoji.length;
                if (nextLen > max) return;

                textarea.value = textarea.value.slice(0, start) + emoji + textarea.value.slice(end);
                const cursor = start + emoji.length;
                textarea.focus();
                textarea.setSelectionRange(cursor, cursor);
                textarea.dispatchEvent(new Event('input', { bubbles: true }));
            }

            toggle.addEventListener('click', function () {
                const open = panel.hidden;
                panel.hidden = !open;
                toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
                if (open) textarea.focus();
            });

            panel.querySelectorAll('[data-emoji]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    insertEmoji(btn.dataset.emoji || '');
                });
            });

            textarea.addEventListener('input', updateCount);
            updateCount();
        });

        document.addEventListener('click', function (e) {
            document.querySelectorAll('.ig-compose-caption-wrap').forEach(function (wrap) {
                const toggle = wrap.querySelector('[data-emoji-toggle]');
                const panel = wrap.querySelector('[data-emoji-panel]');
                if (!toggle || !panel || panel.hidden) return;
                if (wrap.contains(e.target)) return;
                panel.hidden = true;
                toggle.setAttribute('aria-expanded', 'false');
            });
        });
    }

    initComposeEmoji();

    document.querySelectorAll('[data-open-compose]').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            const type = btn.dataset.openCompose;
            if (type === 'story') {
                resetComposeForm('storyComposeForm', 'storyMediaInput', 'storyComposePreview', 'storyComposePreviewMedia', 'storyComposeSubmit', 'storyComposeProgress', 'storyComposeStatus');
                openModal('storyComposeModal');
            } else {
                resetComposeForm('postComposeForm', 'postImageInput', 'postComposePreview', 'postComposePreviewMedia', 'postComposeSubmit', 'postComposeProgress', 'postComposeStatus');
                openModal('postComposeModal');
            }
        });
    });

    document.querySelectorAll('[data-close-compose]').forEach(function (el) {
        el.addEventListener('click', closeModals);
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeModals();
    });

    function setupPreview(inputId, previewId, previewMediaId, submitId) {
        const input = document.getElementById(inputId);
        const preview = document.getElementById(previewId);
        const previewMedia = document.getElementById(previewMediaId);
        const submit = document.getElementById(submitId);
        if (!input || !preview || !previewMedia || !submit) return;

        input.addEventListener('change', function () {
            const file = input.files?.[0];
            previewMedia.innerHTML = '';

            if (!file) {
                submit.disabled = true;
                previewMedia.hidden = true;
                preview.hidden = false;
                return;
            }

            if (file.type.startsWith('video/') && file.size > STORY_VIDEO_MAX_BYTES) {
                submit.disabled = true;
                previewMedia.hidden = true;
                preview.hidden = false;
                window.alert('Video en fazla 25 MB olabilir. Daha kısa bir video seçin.');
                input.value = '';
                return;
            }

            submit.disabled = false;
            preview.hidden = true;
            previewMedia.hidden = false;

            const modal = input.closest('.ig-compose-modal');
            if (modal) {
                modal.scrollTop = 0;
            }

            const url = URL.createObjectURL(file);

            if (file.type.startsWith('video/')) {
                const video = document.createElement('video');
                video.src = url;
                video.controls = true;
                video.playsInline = true;
                previewMedia.appendChild(video);
            } else {
                const img = document.createElement('img');
                img.src = url;
                img.alt = 'Önizleme';
                previewMedia.appendChild(img);
            }
        });
    }

    function setupStoryMediaPreview() {
        const input = document.getElementById('storyMediaInput');
        const preview = document.getElementById('storyComposePreview');
        const previewMedia = document.getElementById('storyComposePreviewMedia');
        const submit = document.getElementById('storyComposeSubmit');
        const trimPanel = document.getElementById('storyVideoTrim');
        const trimSlider = document.getElementById('storyTrimStart');
        const trimStartLabel = document.getElementById('storyTrimStartLabel');
        const trimEndLabel = document.getElementById('storyTrimEndLabel');
        if (!input || !preview || !previewMedia || !submit) return;

        function updateTrimLabels() {
            if (!trimStartLabel || !trimEndLabel) return;
            trimStartLabel.textContent = formatVideoTime(storyVideoState.startTime);
            trimEndLabel.textContent = formatVideoTime(storyVideoState.startTime + STORY_VIDEO_MAX_SECONDS);
        }

        function bindPreviewVideoLoop(video) {
            video.addEventListener('timeupdate', function () {
                if (!storyVideoState.needsTrim) return;
                const endTime = storyVideoState.startTime + STORY_VIDEO_MAX_SECONDS;
                if (video.currentTime >= endTime - 0.05) {
                    video.currentTime = storyVideoState.startTime;
                }
            });
        }

        if (trimSlider) {
            trimSlider.addEventListener('input', function () {
                storyVideoState.startTime = parseFloat(trimSlider.value) || 0;
                updateTrimLabels();
                const video = previewMedia.querySelector('video');
                if (video) {
                    video.currentTime = storyVideoState.startTime;
                }
            });
        }

        input.addEventListener('change', function () {
            const file = input.files?.[0];
            resetStoryVideoState();
            previewMedia.innerHTML = '';

            if (!file) {
                submit.disabled = true;
                previewMedia.hidden = true;
                preview.hidden = false;
                return;
            }

            if (file.type.startsWith('video/') && file.size > STORY_VIDEO_MAX_BYTES) {
                submit.disabled = true;
                previewMedia.hidden = true;
                preview.hidden = false;
                window.alert('Video en fazla 25 MB olabilir. Daha kısa bir video seçin.');
                input.value = '';
                return;
            }

            preview.hidden = true;
            previewMedia.hidden = false;

            const modal = input.closest('.ig-compose-modal');
            if (modal) {
                modal.scrollTop = 0;
            }

            storyVideoState.previewUrl = URL.createObjectURL(file);
            const url = storyVideoState.previewUrl;

            if (file.type.startsWith('video/')) {
                submit.disabled = true;
                const video = document.createElement('video');
                video.src = url;
                video.controls = true;
                video.playsInline = true;
                previewMedia.appendChild(video);

                video.addEventListener('loadedmetadata', function () {
                    const duration = video.duration || 0;
                    storyVideoState.duration = duration;

                    if (duration > STORY_VIDEO_MAX_SECONDS + 0.05) {
                        if (typeof MediaRecorder === 'undefined' || !pickRecorderMimeType()) {
                            submit.disabled = true;
                            previewMedia.hidden = true;
                            preview.hidden = false;
                            window.alert('Bu video 15 saniyeden uzun. Tarayıcınız kırpma desteklemiyor; lütfen 15 saniyeden kısa bir video seçin.');
                            input.value = '';
                            resetStoryVideoState();
                            return;
                        }

                        storyVideoState.needsTrim = true;
                        storyVideoState.startTime = 0;
                        const maxStart = Math.max(0, duration - STORY_VIDEO_MAX_SECONDS);

                        if (trimPanel) trimPanel.hidden = false;
                        if (trimSlider) {
                            trimSlider.min = '0';
                            trimSlider.max = String(maxStart);
                            trimSlider.value = '0';
                        }

                        updateTrimLabels();
                        bindPreviewVideoLoop(video);
                        video.currentTime = 0;
                    } else {
                        if (trimPanel) trimPanel.hidden = true;
                    }

                    submit.disabled = false;
                });

                video.addEventListener('error', function () {
                    submit.disabled = true;
                    window.alert('Video okunamadı. Lütfen başka bir dosya seçin.');
                    input.value = '';
                    resetStoryVideoState();
                });
            } else {
                if (trimPanel) trimPanel.hidden = true;
                submit.disabled = false;
                const img = document.createElement('img');
                img.src = url;
                img.alt = 'Önizleme';
                previewMedia.appendChild(img);
            }
        });
    }

    setupPreview('postImageInput', 'postComposePreview', 'postComposePreviewMedia', 'postComposeSubmit');
    setupStoryMediaPreview();

    if (document.querySelector('#postComposeForm .ig-compose-error')) {
        openModal('postComposeModal');
    }
    if (document.querySelector('#storyComposeForm .ig-compose-error')) {
        openModal('storyComposeModal');
    }

    function loadImageFromFile(file) {
        return new Promise(function (resolve, reject) {
            const url = URL.createObjectURL(file);
            const img = new Image();
            img.onload = function () {
                URL.revokeObjectURL(url);
                resolve(img);
            };
            img.onerror = function () {
                URL.revokeObjectURL(url);
                reject(new Error('image load failed'));
            };
            img.src = url;
        });
    }

    async function compressImageFile(file, maxWidth) {
        if (!file.type.startsWith('image/') || file.type === 'image/gif') {
            return file;
        }

        try {
            const img = await loadImageFromFile(file);
            const scale = Math.min(1, maxWidth / img.naturalWidth);
            const width = Math.max(1, Math.round(img.naturalWidth * scale));
            const height = Math.max(1, Math.round(img.naturalHeight * scale));
            const canvas = document.createElement('canvas');
            canvas.width = width;
            canvas.height = height;
            const ctx = canvas.getContext('2d');
            if (!ctx) return file;
            ctx.drawImage(img, 0, 0, width, height);

            const blob = await new Promise(function (resolve) {
                canvas.toBlob(resolve, 'image/webp', IMAGE_QUALITY);
            });

            if (!blob || blob.size >= file.size) {
                return file;
            }

            const baseName = file.name.replace(/\.[^.]+$/, '') || 'upload';

            return new File([blob], baseName + '.webp', { type: 'image/webp', lastModified: Date.now() });
        } catch (err) {
            return file;
        }
    }

    function setProgress(progressWrap, progressBar, percent) {
        if (!progressWrap || !progressBar) return;
        progressWrap.hidden = false;
        progressBar.style.width = Math.max(0, Math.min(100, percent)) + '%';
    }

    function setStatus(statusEl, message, type) {
        if (!statusEl) return;
        statusEl.hidden = !message;
        statusEl.textContent = message || '';
        statusEl.className = 'ig-compose-status' + (type ? ' ig-compose-status--' + type : '');
    }

    function uploadComposeForm(form, config) {
        return new Promise(function (resolve, reject) {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', form.action);
            xhr.setRequestHeader('X-CSRF-TOKEN', csrf || '');
            xhr.setRequestHeader('Accept', 'application/json');
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

            xhr.upload.addEventListener('progress', function (event) {
                if (!event.lengthComputable) return;
                const percent = (event.loaded / event.total) * 100;
                setProgress(config.progressWrap, config.progressBar, percent);
            });

            xhr.onload = function () {
                let payload = null;
                try {
                    payload = JSON.parse(xhr.responseText);
                } catch (e) {
                    payload = null;
                }

                if (xhr.status >= 200 && xhr.status < 300 && payload?.success) {
                    resolve(payload);
                    return;
                }

                const message = payload?.message
                    || payload?.errors?.[config.errorField]?.[0]
                    || 'Yükleme başarısız. Lütfen tekrar deneyin.';
                reject(new Error(message));
            };

            xhr.onerror = function () {
                reject(new Error('Bağlantı hatası. İnternetinizi kontrol edin.'));
            };

            xhr.send(config.formData);
        });
    }

    function bindComposeSubmit(formId, inputId, submitId, progressWrapId, progressBarId, statusId, fieldName, maxWidth, isVideoAllowed, options) {
        options = options || {};
        const form = document.getElementById(formId);
        const input = document.getElementById(inputId);
        const submit = document.getElementById(submitId);
        const progressWrap = document.getElementById(progressWrapId);
        const progressBar = document.getElementById(progressBarId);
        const statusEl = document.getElementById(statusId);

        if (!form || !input || !submit) return;

        let uploading = false;

        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            if (uploading || !csrf) return;

            const file = input.files?.[0];
            if (!file) return;

            uploading = true;
            submit.disabled = true;
            submit.classList.add('ig-compose-submit--loading');
            submit.textContent = 'Yükleniyor…';
            setStatus(statusEl, 'Dosya hazırlanıyor…', 'info');
            setProgress(progressWrap, progressBar, 4);

            try {
                let uploadFile = file;

                if (typeof options.prepareFile === 'function') {
                    uploadFile = await options.prepareFile(file, function (message, type) {
                        setStatus(statusEl, message, type);
                    });
                } else if (file.type.startsWith('image/')) {
                    uploadFile = await compressImageFile(file, maxWidth);
                    setStatus(statusEl, 'Optimize edildi · yükleniyor…', 'info');
                } else if (!isVideoAllowed) {
                    throw new Error('Lütfen bir fotoğraf seçin.');
                }

                const formData = new FormData(form);
                formData.set(fieldName, uploadFile, uploadFile.name);

                const result = await uploadComposeForm(form, {
                    formData: formData,
                    progressWrap: progressWrap,
                    progressBar: progressBar,
                    errorField: fieldName === 'media' ? 'media' : 'image',
                });

                setProgress(progressWrap, progressBar, 100);
                setStatus(statusEl, result.message || 'Paylaşıldı!', 'success');
                window.setTimeout(function () {
                    window.location.reload();
                }, 450);
            } catch (err) {
                setStatus(statusEl, err.message || 'Yükleme başarısız.', 'error');
                submit.disabled = false;
                submit.classList.remove('ig-compose-submit--loading');
                submit.textContent = 'Paylaş';
                uploading = false;
            }
        });
    }

    bindComposeSubmit(
        'postComposeForm',
        'postImageInput',
        'postComposeSubmit',
        'postComposeProgress',
        'postComposeProgressBar',
        'postComposeStatus',
        'image',
        POST_MAX_WIDTH,
        false,
    );

    bindComposeSubmit(
        'storyComposeForm',
        'storyMediaInput',
        'storyComposeSubmit',
        'storyComposeProgress',
        'storyComposeProgressBar',
        'storyComposeStatus',
        'media',
        STORY_IMAGE_MAX_WIDTH,
        true,
        {
            prepareFile: async function (file, reportStatus) {
                if (file.type.startsWith('image/')) {
                    return compressImageFile(file, STORY_IMAGE_MAX_WIDTH);
                }
                if (!file.type.startsWith('video/')) {
                    throw new Error('Lütfen bir fotoğraf veya video seçin.');
                }

                const duration = storyVideoState.duration || 0;
                if (duration <= STORY_VIDEO_MAX_SECONDS + 0.05) {
                    return file;
                }

                reportStatus('15 saniyelik bölüm hazırlanıyor…', 'info');
                return extractVideoClip(file, storyVideoState.startTime, STORY_VIDEO_MAX_SECONDS);
            },
        },
    );

    function playLikeAnimation(btn, liked) {
        btn.classList.remove('like-btn--pop', 'like-btn--unpop');
        void btn.offsetWidth;
        btn.classList.add(liked ? 'like-btn--pop' : 'like-btn--unpop');
        window.setTimeout(function () {
            btn.classList.remove('like-btn--pop', 'like-btn--unpop');
        }, liked ? 550 : 300);
    }

    function bumpLikeCount(countEl) {
        if (!countEl) return;
        countEl.classList.remove('like-count--bump');
        void countEl.offsetWidth;
        countEl.classList.add('like-count--bump');
        window.setTimeout(function () {
            countEl.classList.remove('like-count--bump');
        }, 400);
    }

    function showPostLikeBurst(card) {
        const burst = card?.querySelector('.post-like-burst');
        if (!burst) return;
        burst.classList.remove('post-like-burst--show');
        void burst.offsetWidth;
        burst.classList.add('post-like-burst--show');
        window.setTimeout(function () {
            burst.classList.remove('post-like-burst--show');
        }, 750);
    }

    function initPostCardAnimations() {
        const cards = document.querySelectorAll('.post-card');
        if (!cards.length) return;

        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            cards.forEach(function (card) {
                card.classList.add('post-card--visible');
            });
            return;
        }

        const observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('post-card--visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.08, rootMargin: '0px 0px -32px 0px' });

        cards.forEach(function (card) {
            observer.observe(card);
        });
    }

    function initPostImageLoad() {
        document.querySelectorAll('[data-post-image]').forEach(function (wrap) {
            const img = wrap.querySelector('.post-image-media');
            if (!img) return;

            function markLoaded() {
                img.classList.add('post-image-media--loaded');
                wrap.classList.add('post-image--loaded');
            }

            if (img.complete && img.naturalWidth > 0) {
                markLoaded();
            } else {
                img.addEventListener('load', markLoaded, { once: true });
                img.addEventListener('error', markLoaded, { once: true });
            }
        });
    }

    function initPostCaptions() {
        document.querySelectorAll('[data-post-caption]').forEach(function (block) {
            const text = block.querySelector('.post-caption-text');
            const toggle = block.querySelector('[data-caption-toggle]');
            if (!text || !toggle) return;

            if (text.scrollHeight > text.clientHeight + 2) {
                toggle.hidden = false;
            }

            toggle.addEventListener('click', function () {
                const expanded = block.classList.toggle('is-expanded');
                toggle.textContent = expanded ? 'Daha az' : 'Daha fazla';
            });
        });
    }

    function syncLikeButtons(likeUrl, liked, count) {
        document.querySelectorAll('.like-btn[data-like-url]').forEach(function (btn) {
            if (btn.dataset.likeUrl !== likeUrl) return;
            btn.classList.toggle('like-btn--active', liked);
            btn.setAttribute('aria-pressed', liked ? 'true' : 'false');
            btn.setAttribute('aria-label', liked ? 'Beğeniyi kaldır' : 'Beğen');
            const countEl = btn.querySelector('.like-count');
            if (countEl) countEl.textContent = count;
        });

        document.querySelectorAll('.post-card').forEach(function (card) {
            const cardBtn = card.querySelector('.like-btn[data-like-url]');
            if (cardBtn && cardBtn.dataset.likeUrl === likeUrl) {
                card.classList.toggle('post-card--liked', liked);
            }
        });

        document.querySelectorAll('[data-open-feed-post]').forEach(function (trigger) {
            if (trigger.dataset.likeUrl !== likeUrl) return;
            trigger.dataset.isLiked = liked ? '1' : '0';
            trigger.dataset.likesCount = String(count);
        });
    }

    function initFeedPostDetail() {
        const dialog = document.getElementById('feedPostDetailDialog');
        if (!dialog) return;

        const imageEl = document.getElementById('feedPostDetailImage');
        const usernameEl = document.getElementById('feedPostDetailUsername');
        const captionEl = document.getElementById('feedPostDetailCaption');
        const likeBtn = document.getElementById('feedPostDetailLikeBtn');
        const likeCountEl = document.getElementById('feedPostDetailLikeCount');
        const deleteForm = document.getElementById('feedPostDetailDeleteForm');

        function openFeedPostDetail(trigger) {
            const detailWrap = dialog.querySelector('[data-post-detail-image]');

            if (imageEl) {
                imageEl.classList.remove('post-image-media--loaded');
                if (detailWrap) detailWrap.classList.remove('post-image--loaded');

                imageEl.src = trigger.dataset.imageUrl || '';
                imageEl.alt = trigger.dataset.caption
                    ? trigger.dataset.caption.slice(0, 80)
                    : trigger.dataset.username + ' gönderisi';

                function markDetailLoaded() {
                    imageEl.classList.add('post-image-media--loaded');
                    if (detailWrap) detailWrap.classList.add('post-image--loaded');
                }

                if (imageEl.complete && imageEl.naturalWidth > 0) {
                    markDetailLoaded();
                } else {
                    imageEl.addEventListener('load', markDetailLoaded, { once: true });
                    imageEl.addEventListener('error', markDetailLoaded, { once: true });
                }
            }
            if (usernameEl) usernameEl.textContent = trigger.dataset.username || '';
            if (likeBtn) likeBtn.dataset.likeUrl = trigger.dataset.likeUrl || '';

            const isLiked = trigger.dataset.isLiked === '1';
            const likesCount = trigger.dataset.likesCount || '0';
            if (likeBtn) {
                likeBtn.classList.toggle('like-btn--active', isLiked);
                likeBtn.setAttribute('aria-pressed', isLiked ? 'true' : 'false');
            }
            if (likeCountEl) likeCountEl.textContent = likesCount;

            if (captionEl) {
                const caption = trigger.dataset.caption || '';
                if (caption) {
                    captionEl.textContent = caption;
                    captionEl.hidden = false;
                } else {
                    captionEl.textContent = '';
                    captionEl.hidden = true;
                }
            }

            if (deleteForm) {
                if (trigger.dataset.destroyUrl) {
                    deleteForm.action = trigger.dataset.destroyUrl;
                    deleteForm.hidden = false;
                } else {
                    deleteForm.action = '';
                    deleteForm.hidden = true;
                }
            }

            dialog.showModal();
        }

        document.querySelectorAll('[data-open-feed-post]').forEach(function (trigger) {
            trigger.addEventListener('click', function () {
                openFeedPostDetail(trigger);
            });
        });

        document.querySelectorAll('[data-close-feed-post-detail]').forEach(function (el) {
            el.addEventListener('click', function () {
                dialog.close();
            });
        });

        dialog.addEventListener('click', function (e) {
            if (e.target === dialog) dialog.close();
        });

        if (likeBtn && csrf) {
            likeBtn.addEventListener('click', async function () {
                const url = likeBtn.dataset.likeUrl;
                if (!url || likeBtn.disabled) return;

                likeBtn.disabled = true;
                try {
                    const res = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                        },
                    });
                    const data = await res.json();
                    if (!data.success) return;

                    syncLikeButtons(url, data.is_liked, data.likes_count);
                    playLikeAnimation(likeBtn, data.is_liked);
                    if (data.is_liked) bumpLikeCount(likeCountEl);
                } catch (err) {
                    console.error(err);
                } finally {
                    likeBtn.disabled = false;
                }
            });
        }
    }

    initPostCardAnimations();
    initPostImageLoad();
    initPostCaptions();
    initFeedPostDetail();

    document.querySelectorAll('.post-card .like-btn[data-like-url]').forEach(function (btn) {
        btn.addEventListener('click', async function (e) {
            if (!csrf || btn.disabled) return;
            e.stopPropagation();

            btn.disabled = true;
            try {
                const res = await fetch(btn.dataset.likeUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                    },
                });
                const data = await res.json();
                if (!data.success) return;

                syncLikeButtons(btn.dataset.likeUrl, data.is_liked, data.likes_count);
                const countEl = btn.querySelector('.like-count');
                if (data.is_liked && countEl) bumpLikeCount(countEl);
                playLikeAnimation(btn, data.is_liked);

                const card = btn.closest('.post-card');
                if (data.is_liked && card) showPostLikeBurst(card);
            } catch (err) {
                console.error(err);
            } finally {
                btn.disabled = false;
            }
        });
    });

    document.querySelectorAll('[data-post-menu]').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            const dropdown = btn.parentElement?.querySelector('.post-menu-dropdown');
            if (!dropdown) return;

            document.querySelectorAll('.post-menu-dropdown').forEach(function (menu) {
                if (menu !== dropdown) menu.hidden = true;
            });
            dropdown.hidden = !dropdown.hidden;
        });
    });

    document.addEventListener('click', function () {
        document.querySelectorAll('.post-menu-dropdown').forEach(function (menu) {
            menu.hidden = true;
        });
    });

    function applyCaptionToDom(postId, caption) {
        const card = document.querySelector('.post-card[data-post-id="' + postId + '"]');
        if (card) {
            const trigger = card.querySelector('[data-open-feed-post]');
            if (trigger) trigger.dataset.caption = caption || '';

            card.querySelectorAll('[data-edit-caption]').forEach(function (btn) {
                btn.dataset.caption = caption || '';
            });

            let captionBlock = card.querySelector('[data-post-caption]');
            const footer = card.querySelector('.post-footer');
            if (caption) {
                if (captionBlock && captionBlock.classList.contains('post-caption--empty')) {
                    captionBlock.classList.remove('post-caption--empty');
                    captionBlock.innerHTML =
                        '<p class="post-caption-text">' +
                        '<span class="post-caption-body"></span>' +
                        '</p>' +
                        '<button type="button" class="post-caption-more" hidden data-caption-toggle></button>' +
                        '<button type="button" class="post-caption-edit-btn" data-edit-caption data-post-id="' + postId + '"></button>';
                    const editBtn = captionBlock.querySelector('[data-edit-caption]');
                    if (editBtn && trigger) {
                        editBtn.dataset.updateUrl = trigger.dataset.updateUrl || '';
                        editBtn.textContent = editBtn.getAttribute('data-edit-label') || 'Düzenle';
                    }
                }
                if (!captionBlock) {
                    // leave structure; reload not required for menu-driven edits
                } else {
                    const body = captionBlock.querySelector('.post-caption-body');
                    if (body) body.textContent = caption;
                    const editBtn = captionBlock.querySelector('[data-edit-caption]');
                    if (editBtn) editBtn.dataset.caption = caption;
                }
                if (footer) footer.classList.remove('post-footer--compact');
            } else if (captionBlock) {
                const body = captionBlock.querySelector('.post-caption-body');
                if (body) body.textContent = '';
            }
        }

        const detailCaption = document.getElementById('feedPostDetailCaption');
        if (detailCaption) {
            if (caption) {
                detailCaption.textContent = caption;
                detailCaption.hidden = false;
            } else {
                detailCaption.textContent = '';
                detailCaption.hidden = true;
            }
        }
    }

    function initCaptionEditor() {
        const dialog = document.getElementById('postCaptionEditDialog');
        const form = document.getElementById('postCaptionEditForm');
        const input = document.getElementById('postCaptionEditInput');
        const countEl = document.getElementById('postCaptionEditCount');
        const errorEl = document.getElementById('postCaptionEditError');
        const saveBtn = document.getElementById('postCaptionEditSave');
        if (!dialog || !form || !input || !csrf) return;

        let activeUrl = '';
        let activePostId = '';

        function updateCount() {
            const len = input.value.length;
            if (countEl) countEl.textContent = len + ' / 500';
        }

        function openEditor(btn) {
            activeUrl = btn.dataset.updateUrl || '';
            activePostId = btn.dataset.postId || '';
            if (!activeUrl) return;
            input.value = btn.dataset.caption || '';
            if (errorEl) {
                errorEl.hidden = true;
                errorEl.textContent = '';
            }
            updateCount();
            dialog.showModal();
            input.focus();
        }

        window.gkOpenCaptionEditor = openEditor;

        function closeEditor() {
            if (dialog.open) dialog.close();
            activeUrl = '';
            activePostId = '';
        }

        document.querySelectorAll('[data-edit-caption]').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                const menu = btn.closest('.post-menu-dropdown');
                if (menu) menu.hidden = true;
                openEditor(btn);
            });
        });

        document.querySelectorAll('[data-close-caption-edit]').forEach(function (el) {
            el.addEventListener('click', function (e) {
                e.preventDefault();
                closeEditor();
            });
        });

        input.addEventListener('input', updateCount);

        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            if (!activeUrl || !saveBtn) return;
            saveBtn.disabled = true;
            if (errorEl) {
                errorEl.hidden = true;
                errorEl.textContent = '';
            }
            try {
                const res = await fetch(activeUrl, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ caption: input.value }),
                });
                const data = await res.json().catch(function () { return {}; });
                if (!res.ok || !data.success) {
                    const msg = (data && (data.message || (data.errors && data.errors.caption && data.errors.caption[0]))) || 'Açıklama kaydedilemedi.';
                    if (errorEl) {
                        errorEl.textContent = msg;
                        errorEl.hidden = false;
                    }
                    return;
                }
                const caption = (data.data && data.data.caption) || '';
                const postId = String(activePostId || (data.data && data.data.id) || '');
                const hadEmpty = postId && document.querySelector('.post-card[data-post-id="' + postId + '"] .post-caption--empty');
                applyCaptionToDom(postId, caption);
                document.querySelectorAll('[data-edit-caption][data-post-id="' + postId + '"]').forEach(function (btn) {
                    btn.dataset.caption = caption || '';
                });
                closeEditor();
                if (hadEmpty && caption) {
                    window.location.reload();
                }
            } catch (err) {
                console.error(err);
                if (errorEl) {
                    errorEl.textContent = 'Açıklama kaydedilemedi.';
                    errorEl.hidden = false;
                }
            } finally {
                saveBtn.disabled = false;
            }
        });
    }

    initCaptionEditor();
})();
