(function () {
    function initProfileGridAnimations() {
        const items = document.querySelectorAll('.user-profile-grid-item');
        if (!items.length) return;

        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            items.forEach(function (item) {
                item.classList.add('user-profile-grid-item--visible');
            });
            return;
        }

        items.forEach(function (item) {
            if (item.classList.contains('user-profile-grid-item--visible')) return;
            item.classList.add('user-profile-grid-item--animate');
        });

        const observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('user-profile-grid-item--visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.08, rootMargin: '0px 0px -16px 0px' });

        items.forEach(function (item) {
            if (!item.classList.contains('user-profile-grid-item--visible')) {
                observer.observe(item);
            }
        });
    }

    initProfileGridAnimations();

    const dialog = document.getElementById('postDetailDialog');
    if (!dialog) return;

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    const imageEl = document.getElementById('postDetailImage');
    const captionEl = document.getElementById('postDetailCaption');
    const likeBtn = document.getElementById('postDetailLikeBtn');
    const likeCountEl = document.getElementById('postDetailLikeCount');
    const deleteForm = document.getElementById('postDetailDeleteForm');

    function setLikeState(liked, count) {
        if (!likeBtn) return;
        likeBtn.classList.toggle('like-btn--active', liked);
        likeBtn.setAttribute('aria-pressed', liked ? 'true' : 'false');
        if (likeCountEl) likeCountEl.textContent = count;
    }

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

    function updateGridItemLikeState(gridBtn, liked, count) {
        if (!gridBtn) return;

        gridBtn.dataset.isLiked = liked ? '1' : '0';
        gridBtn.dataset.likesCount = String(count);
        gridBtn.classList.toggle('user-profile-grid-item--liked', liked);

        const likesEl = gridBtn.querySelector('.user-profile-grid-likes');
        if (likesEl) likesEl.textContent = count;

        let badge = gridBtn.querySelector('.user-profile-grid-badge');
        if (count > 0) {
            if (!badge) {
                badge = document.createElement('span');
                badge.className = 'user-profile-grid-badge';
                badge.setAttribute('aria-hidden', 'true');
                badge.innerHTML = '<span>♥</span><span class="user-profile-grid-badge-count"></span>';
                gridBtn.appendChild(badge);
            }
            const badgeCount = badge.querySelector('.user-profile-grid-badge-count');
            if (badgeCount) badgeCount.textContent = count;
            badge.hidden = false;
        } else if (badge) {
            badge.remove();
        }
    }

    const editBtn = document.getElementById('postDetailEditCaption');
    let activeGridBtn = null;

    function openPostDetail(btn) {
        activeGridBtn = btn;
        const imageUrl = btn.dataset.imageUrl;
        const caption = btn.dataset.caption || '';
        const likesCount = btn.dataset.likesCount || '0';
        const isLiked = btn.dataset.isLiked === '1';
        const likeUrl = btn.dataset.likeUrl || '';

        if (imageEl) {
            imageEl.src = imageUrl;
            imageEl.style.animation = 'none';
            void imageEl.offsetWidth;
            imageEl.style.animation = '';
        }
        if (likeBtn) likeBtn.dataset.likeUrl = likeUrl;
        setLikeState(isLiked, likesCount);

        if (captionEl) {
            if (caption) {
                captionEl.textContent = caption;
                captionEl.hidden = false;
            } else {
                captionEl.textContent = '';
                captionEl.hidden = true;
            }
        }

        if (deleteForm && btn.dataset.destroyUrl) {
            deleteForm.action = btn.dataset.destroyUrl;
        }

        if (editBtn) {
            if (btn.dataset.updateUrl) {
                editBtn.hidden = false;
                editBtn.dataset.updateUrl = btn.dataset.updateUrl;
                editBtn.dataset.postId = btn.dataset.postId || '';
                editBtn.dataset.caption = caption;
                editBtn.textContent = caption ? 'Açıklamayı düzenle' : 'Açıklama ekle';
            } else {
                editBtn.hidden = true;
            }
        }

        dialog.showModal();
    }

    if (editBtn) {
        editBtn.addEventListener('click', function () {
            // Prefer shared caption editor from feed.js if present
            if (typeof window.gkOpenCaptionEditor === 'function') {
                window.gkOpenCaptionEditor(editBtn);
                return;
            }
            const captionDialog = document.getElementById('postCaptionEditDialog');
            const input = document.getElementById('postCaptionEditInput');
            const form = document.getElementById('postCaptionEditForm');
            const countEl = document.getElementById('postCaptionEditCount');
            const errorEl = document.getElementById('postCaptionEditError');
            const saveBtn = document.getElementById('postCaptionEditSave');
            if (!captionDialog || !input || !form || !csrf) return;

            input.value = editBtn.dataset.caption || '';
            if (countEl) countEl.textContent = input.value.length + ' / 500';
            if (errorEl) { errorEl.hidden = true; errorEl.textContent = ''; }
            captionDialog.showModal();
            input.focus();

            form.onsubmit = async function (e) {
                e.preventDefault();
                const url = editBtn.dataset.updateUrl;
                if (!url || !saveBtn) return;
                saveBtn.disabled = true;
                try {
                    const res = await fetch(url, {
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
                        if (errorEl) { errorEl.textContent = msg; errorEl.hidden = false; }
                        return;
                    }
                    const caption = (data.data && data.data.caption) || '';
                    editBtn.dataset.caption = caption;
                    editBtn.textContent = caption ? 'Açıklamayı düzenle' : 'Açıklama ekle';
                    if (captionEl) {
                        if (caption) { captionEl.textContent = caption; captionEl.hidden = false; }
                        else { captionEl.textContent = ''; captionEl.hidden = true; }
                    }
                    if (activeGridBtn) activeGridBtn.dataset.caption = caption;
                    captionDialog.close();
                } catch (err) {
                    console.error(err);
                    if (errorEl) { errorEl.textContent = 'Açıklama kaydedilemedi.'; errorEl.hidden = false; }
                } finally {
                    saveBtn.disabled = false;
                }
            };
        });
    }

    document.querySelectorAll('[data-open-post-detail]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            openPostDetail(btn);
        });
    });

    document.querySelectorAll('[data-close-post-detail]').forEach(function (el) {
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

                setLikeState(data.is_liked, data.likes_count);
                playLikeAnimation(likeBtn, data.is_liked);
                if (data.is_liked) bumpLikeCount(likeCountEl);

                document.querySelectorAll('[data-open-post-detail]').forEach(function (gridBtn) {
                    if (gridBtn.dataset.likeUrl === url) {
                        updateGridItemLikeState(gridBtn, data.is_liked, data.likes_count);
                    }
                });
            } catch (err) {
                console.error(err);
            } finally {
                likeBtn.disabled = false;
            }
        });
    }
})();
