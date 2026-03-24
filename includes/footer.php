            </div><!-- .content-wrapper -->
            
            <footer class="admin-footer">
                <p>DepEd — Schools Division Office of San Pedro City</p>
                <span>&copy; <?php echo date('Y'); ?> ICT Unit</span>
            </footer>
        </main>
    </div>

    <script src="<?php echo ADMIN_URL; ?>/assets/js/admin.js?v=<?php echo time(); ?>"></script>
    <!-- Sidebar Avatar Options Script -->
    <script>
    (function() {
        document.addEventListener('DOMContentLoaded', function() {
            var trigger = document.getElementById('sidebarAvatarTrigger');
            var optionsModal = document.getElementById('avatarOptionsModal');
            var lightbox = document.getElementById('avatarLightbox');
            var confirmModal = document.getElementById('sidebarAvatarConfirmModal');
            var fileInput = document.getElementById('sidebarAvatarInput');
            var pendingFile = null;

            if (!trigger || !optionsModal) return;

            // Open options modal
            trigger.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                optionsModal.classList.add('active');
            });

            // Close options modal
            function closeOptions() { optionsModal.classList.remove('active'); }
            document.getElementById('avatarOptClose').addEventListener('click', closeOptions);
            optionsModal.addEventListener('click', function(e) { if (e.target === optionsModal) closeOptions(); });

            // View profile picture
            document.getElementById('avatarOptView').addEventListener('click', function() {
                closeOptions();
                lightbox.classList.add('active');
            });

            // Close lightbox
            function closeLightbox() { lightbox.classList.remove('active'); }
            document.getElementById('avatarLightboxClose').addEventListener('click', closeLightbox);
            lightbox.addEventListener('click', function(e) { if (e.target === lightbox) closeLightbox(); });
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') { closeLightbox(); closeOptions(); closeConfirm(); }
            });

            // Change profile picture - open file picker
            document.getElementById('avatarOptChange').addEventListener('click', function() {
                closeOptions();
                fileInput.click();
            });

            // File selected -> validate -> show confirm modal
            fileInput.addEventListener('change', function() {
                var file = fileInput.files[0];
                if (!file) return;
                if (['image/jpeg','image/png'].indexOf(file.type) === -1) {
                    if (typeof showNotification === 'function') showNotification('Only JPG and PNG are allowed.', 'error');
                    fileInput.value = '';
                    return;
                }
                if (file.size > 5 * 1024 * 1024) {
                    if (typeof showNotification === 'function') showNotification('File size exceeds 5 MB limit.', 'error');
                    fileInput.value = '';
                    return;
                }
                pendingFile = file;
                var reader = new FileReader();
                reader.onload = function(ev) {
                    document.getElementById('sidebarAvatarPreviewImg').src = ev.target.result;
                    confirmModal.classList.add('active');
                };
                reader.readAsDataURL(file);
            });

            // Confirm modal close
            function closeConfirm() { confirmModal.classList.remove('active'); pendingFile = null; fileInput.value = ''; }
            document.getElementById('sidebarAvatarConfirmClose').addEventListener('click', closeConfirm);
            document.getElementById('sidebarAvatarConfirmCancel').addEventListener('click', closeConfirm);
            confirmModal.addEventListener('click', function(e) { if (e.target === confirmModal) closeConfirm(); });

            // Confirm upload
            document.getElementById('sidebarAvatarConfirmYes').addEventListener('click', function() {
                if (!pendingFile) return;
                var fileToUpload = pendingFile;
                confirmModal.classList.remove('active');
                pendingFile = null;

                var formData = new FormData();
                formData.append('avatar', fileToUpload);

                var apiUrl = '<?php echo ADMIN_URL; ?>/api/avatar-upload.php';

                fetch(apiUrl, { method: 'POST', body: formData })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.success) {
                        if (typeof showNotification === 'function') showNotification(data.message || 'Profile picture updated!', 'success');
                        var bust = '?t=' + Date.now();
                        var newUrl = data.avatar_url + bust;

                        // Update sidebar avatar
                        var sidebarTrigger = document.getElementById('sidebarAvatarTrigger');
                        var sImg = sidebarTrigger.querySelector('.sidebar-user-avatar');
                        var sPlaceholder = sidebarTrigger.querySelector('.user-avatar-placeholder');
                        if (sImg) {
                            sImg.src = data.avatar_url + bust;
                        } else {
                            if (sPlaceholder) sPlaceholder.style.display = 'none';
                            sImg = document.createElement('img');
                            sImg.src = newUrl;
                            sImg.alt = 'Avatar';
                            sImg.className = 'user-avatar sidebar-user-avatar';
                            sidebarTrigger.insertBefore(sImg, sidebarTrigger.firstChild);
                        }

                        // Update options modal preview
                        var optPreview = document.getElementById('avatarOptionsPreviewImg');
                        var optPlaceholder = document.getElementById('avatarOptionsPlaceholderEl');
                        if (optPreview) {
                            optPreview.src = newUrl;
                        } else if (optPlaceholder) {
                            optPlaceholder.style.display = 'none';
                            var ni = document.createElement('img');
                            ni.src = newUrl; ni.alt = 'Avatar'; ni.id = 'avatarOptionsPreviewImg';
                            optPlaceholder.parentNode.insertBefore(ni, optPlaceholder);
                        }

                        // Update lightbox
                        var lbImg = document.getElementById('avatarLightboxImg');
                        var lbPlaceholder = document.getElementById('avatarLightboxPlaceholderEl');
                        if (lbImg) {
                            lbImg.src = newUrl;
                        } else if (lbPlaceholder) {
                            lbPlaceholder.style.display = 'none';
                            var li = document.createElement('img');
                            li.src = newUrl; li.alt = 'Profile Picture'; li.id = 'avatarLightboxImg';
                            lbPlaceholder.parentNode.insertBefore(li, lbPlaceholder);
                        }

                        // Update profile page avatar if present
                        var profileImg = document.getElementById('profileAvatarImg');
                        var profilePlaceholder = document.getElementById('profileAvatarPlaceholder');
                        if (profileImg) {
                            profileImg.src = newUrl;
                        } else if (profilePlaceholder) {
                            profilePlaceholder.style.display = 'none';
                            var pi = document.createElement('img');
                            pi.src = newUrl; pi.alt = 'Avatar'; pi.id = 'profileAvatarImg';
                            pi.className = 'profile-avatar-img';
                            pi.style.cssText = 'width:120px;height:120px;border-radius:50%;object-fit:cover;display:block;border:3px solid rgba(255,255,255,0.25);';
                            profilePlaceholder.parentNode.insertBefore(pi, profilePlaceholder);
                        }
                    } else {
                        if (typeof showNotification === 'function') showNotification(data.message || 'Upload failed.', 'error');
                    }
                })
                .catch(function(err) {
                    console.error('Avatar upload error:', err);
                    if (typeof showNotification === 'function') showNotification('Upload failed. Please try again.', 'error');
                })
                .finally(function() { fileInput.value = ''; });
            });
        });
    })();
    </script>
    <script>
    // Store token for AJAX requests
    const ALPAS_TOKEN = '<?php echo $currentToken ?? ''; ?>';
    const ADMIN_URL = '<?php echo ADMIN_URL; ?>';
    
    // Helper to add token to URLs
    function addToken(url) {
        if (!ALPAS_TOKEN) return url;
        const separator = url.includes('?') ? '&' : '?';
        return url + separator + 'token=' + ALPAS_TOKEN;
    }
    
    // Override fetch to add token header
    const originalFetch = window.fetch;
    window.fetch = function(url, options = {}) {
        options.headers = options.headers || {};
        if (ALPAS_TOKEN) {
            options.headers['X-Auth-Token'] = ALPAS_TOKEN;
        }
        return originalFetch(url, options);
    };
    
    </script>
    <script>
    // Live clock - Manila, Philippines (Asia/Manila)
    (function() {
        var clockEl = document.getElementById('liveClock');
        if (!clockEl) return;
        function updateClock() {
            var now = new Date().toLocaleTimeString('en-US', {
                timeZone: 'Asia/Manila',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: true
            });
            clockEl.textContent = now;
        }
        updateClock();
        setInterval(updateClock, 1000);
    })();
    </script>
</body>
</html>
