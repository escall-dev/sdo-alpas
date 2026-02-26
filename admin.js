/**
 * SDO CTS Admin Panel JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    initSidebar();
    initFlashMessages();
    initFormValidation();
    initFilterEnterSubmit();
    initAvatarUpload();
});

/**
 * Initialize Enter key submit for filter forms
 * Allows users to press Enter in any filter input to submit the form
 */
function initFilterEnterSubmit() {
    const filterForms = document.querySelectorAll('.filter-form');
    
    filterForms.forEach(function(form) {
        // Handle Enter key on the entire form
        form.addEventListener('keypress', function(e) {
            // Check if Enter key was pressed and not in a textarea
            if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA') {
                e.preventDefault();
                form.submit();
            }
        });
        
        // Also handle keydown for select elements (keypress doesn't always fire for selects)
        const filterSelects = form.querySelectorAll('.filter-select');
        filterSelects.forEach(function(select) {
            select.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    form.submit();
                }
            });
        });
    });
}

/**
 * Sidebar Toggle
 */
function initSidebar() {
    const sidebar = document.getElementById('sidebar');
    const adminLayout = document.querySelector('.admin-layout');
    const mobileToggle = document.getElementById('mobileMenuToggle');
    const desktopToggle = document.getElementById('desktopSidebarToggle');
    
    if (!sidebar) return;
    
    // Restore sidebar state from localStorage
    const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    if (sidebarCollapsed && window.innerWidth >= 992) {
        sidebar.classList.add('collapsed');
        if (adminLayout) adminLayout.classList.add('sidebar-collapsed');
    }
    
    if (mobileToggle) {
        mobileToggle.addEventListener('click', function(e) {
            e.preventDefault();
            sidebar.classList.toggle('open');
        });
    }
    
    if (desktopToggle) {
        desktopToggle.addEventListener('click', function(e) {
            if (e) e.preventDefault();
            const isCollapsed = sidebar.classList.toggle('collapsed');
            if (adminLayout) adminLayout.classList.toggle('sidebar-collapsed', isCollapsed);
            
            // Save state to localStorage
            localStorage.setItem('sidebarCollapsed', isCollapsed);
            
            // Trigger resize event for any charts/components that need to adjust
            window.dispatchEvent(new Event('resize'));
        });
    }
    
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
        if (window.innerWidth < 992) {
            if (mobileToggle && !sidebar.contains(e.target) && !mobileToggle.contains(e.target)) {
                sidebar.classList.remove('open');
            }
        }
    });
    
    // Handle window resize
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            if (window.innerWidth < 992) {
                // On mobile, remove collapsed state
                sidebar.classList.remove('collapsed');
                adminLayout.classList.remove('sidebar-collapsed');
            } else {
                // On desktop, restore saved state
                const savedState = localStorage.getItem('sidebarCollapsed') === 'true';
                sidebar.classList.toggle('collapsed', savedState);
                adminLayout.classList.toggle('sidebar-collapsed', savedState);
            }
        }, 100);
    });
}

/**
 * Flash Messages Auto-hide
 */
function initFlashMessages() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(function() {
                alert.remove();
            }, 300);
        }, 5000);
    });
}

/**
 * Form Validation
 */
function initFormValidation() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(function(field) {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('error');
                    
                    // Remove error class on input
                    field.addEventListener('input', function() {
                        field.classList.remove('error');
                    }, { once: true });
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                showNotification('Please fill in all required fields.', 'error');
            }
        });
    });
}

/**
 * Show Notification
 */
function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existing = document.querySelector('.notification');
    if (existing) {
        existing.remove();
    }
    
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    const iconClass = type === 'error' ? 'fa-exclamation-triangle' : type === 'success' ? 'fa-check-circle' : 'fa-info-circle';
    notification.innerHTML = `
        <span class="notification-icon"><i class="fas ${iconClass}"></i></span>
        <span class="notification-message">${message}</span>
        <button class="notification-close" onclick="this.parentElement.remove()">×</button>
    `;
    
    document.body.appendChild(notification);
    
    // Trigger animation
    setTimeout(function() {
        notification.classList.add('show');
    }, 10);
    
    // Auto-hide after 5 seconds
    setTimeout(function() {
        notification.classList.remove('show');
        setTimeout(function() {
            notification.remove();
        }, 300);
    }, 5000);
}

/**
 * Confirm Action
 */
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

/**
 * Format Date
 */
function formatDate(dateString, format = 'short') {
    const date = new Date(dateString);
    const options = format === 'short' 
        ? { month: 'short', day: 'numeric', year: 'numeric' }
        : { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
    return date.toLocaleDateString('en-US', options);
}

/**
 * Copy to Clipboard
 */
function copyToClipboard(text) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(function() {
            showNotification('Copied to clipboard!', 'success');
        }).catch(function() {
            fallbackCopy(text);
        });
    } else {
        fallbackCopy(text);
    }
}

function fallbackCopy(text) {
    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.style.position = 'fixed';
    textarea.style.opacity = '0';
    document.body.appendChild(textarea);
    textarea.select();
    document.execCommand('copy');
    document.body.removeChild(textarea);
    showNotification('Copied to clipboard!', 'success');
}

/**
 * Debounce Function
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = function() {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Search with Debounce
 */
function initSearchDebounce(inputId, callback) {
    const input = document.getElementById(inputId);
    if (input) {
        input.addEventListener('input', debounce(function() {
            callback(input.value);
        }, 300));
    }
}

/**
 * Toggle Loading State
 */
function toggleLoading(button, isLoading) {
    if (isLoading) {
        button.disabled = true;
        button.dataset.originalText = button.innerHTML;
        button.innerHTML = '<span class="spinner"></span> Loading...';
    } else {
        button.disabled = false;
        button.innerHTML = button.dataset.originalText;
    }
}

/**
 * AJAX Request Helper
 */
async function ajaxRequest(url, options = {}) {
    const defaultOptions = {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    };
    
    const config = { ...defaultOptions, ...options };
    
    try {
        const response = await fetch(url, config);
        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.message || 'Request failed');
        }
        
        return data;
    } catch (error) {
        showNotification(error.message, 'error');
        throw error;
    }
}

/**
 * Avatar Upload Handler
 */
function initAvatarUpload() {
    // Skip if inline script on profile page already initialized avatar upload
    if (window.__avatarUploadInitialized) return;

    const avatarInput = document.getElementById('avatarInput');
    const avatarOverlay = document.getElementById('avatarOverlay');
    const avatarWrapper = document.getElementById('avatarWrapper');
    const changeBtn = document.getElementById('changeAvatarBtn');
    const removeBtn = document.getElementById('removeAvatarBtn');

    if (!avatarInput) return;

    // Trigger file input from overlay, wrapper, or button click
    function triggerFileSelect(e) {
        e.preventDefault();
        e.stopPropagation();
        avatarInput.click();
    }

    if (avatarOverlay) avatarOverlay.addEventListener('click', triggerFileSelect);
    if (avatarWrapper) avatarWrapper.addEventListener('click', triggerFileSelect);
    if (changeBtn) changeBtn.addEventListener('click', triggerFileSelect);

    // Hover effect for wrapper (inline fallback)
    if (avatarWrapper && avatarOverlay) {
        avatarWrapper.addEventListener('mouseenter', function() {
            avatarOverlay.style.opacity = '1';
        });
        avatarWrapper.addEventListener('mouseleave', function() {
            avatarOverlay.style.opacity = '0';
        });
    }

    // Handle file selection
    avatarInput.addEventListener('change', function() {
        const file = avatarInput.files[0];
        if (!file) return;

        // Client-side validation
        const allowedTypes = ['image/jpeg', 'image/png'];
        if (!allowedTypes.includes(file.type)) {
            showNotification('Invalid file type. Only JPG and PNG are allowed.', 'error');
            avatarInput.value = '';
            return;
        }

        const maxSize = 5 * 1024 * 1024; // 5 MB
        if (file.size > maxSize) {
            showNotification('File size exceeds 5 MB limit.', 'error');
            avatarInput.value = '';
            return;
        }

        // Show loading state on avatar
        const wrapper = document.querySelector('.profile-avatar-wrapper');
        if (wrapper) wrapper.classList.add('avatar-uploading');

        // Build FormData and upload
        const formData = new FormData();
        formData.append('avatar', file);

        fetch(ADMIN_URL + '/api/avatar-upload.php', {
            method: 'POST',
            body: formData
            // Do NOT set Content-Type — let browser set multipart boundary
            // X-Auth-Token is auto-injected by the monkey-patched fetch in footer.php
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (wrapper) wrapper.classList.remove('avatar-uploading');

            if (data.success) {
                showNotification(data.message, 'success');
                updateAvatarDisplay(data.avatar_url);
                updateSidebarAvatar(data.avatar_url);
            } else {
                showNotification(data.message || 'Upload failed.', 'error');
            }
        })
        .catch(function(err) {
            if (wrapper) wrapper.classList.remove('avatar-uploading');
            showNotification('Upload failed. Please try again.', 'error');
        })
        .finally(function() {
            avatarInput.value = '';
        });
    });

    // Handle remove avatar
    if (removeBtn) {
        removeBtn.addEventListener('click', function() {
            if (!confirm('Remove your profile avatar?')) return;

            const formData = new FormData();
            formData.append('action', 'remove');

            fetch(ADMIN_URL + '/api/avatar-upload.php', {
                method: 'POST',
                body: formData
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success) {
                    showNotification(data.message, 'success');
                    revertToInitials();
                    updateSidebarAvatar(null);
                } else {
                    showNotification(data.message || 'Failed to remove avatar.', 'error');
                }
            })
            .catch(function() {
                showNotification('Failed to remove avatar. Please try again.', 'error');
            });
        });
    }
}

/**
 * Update avatar display on profile page after successful upload
 */
function updateAvatarDisplay(avatarUrl) {
    const wrapper = document.querySelector('.profile-avatar-wrapper');
    if (!wrapper) return;

    const cacheBuster = '?t=' + Date.now();
    const fullUrl = avatarUrl + cacheBuster;

    // Check if img already exists
    let img = document.getElementById('profileAvatarImg');
    const placeholder = document.getElementById('profileAvatarPlaceholder');

    if (img) {
        // Update existing image
        img.src = fullUrl;
    } else {
        // Replace placeholder with image
        if (placeholder) placeholder.style.display = 'none';
        img = document.createElement('img');
        img.src = fullUrl;
        img.alt = 'Avatar';
        img.className = 'user-avatar profile-avatar-img';
        img.id = 'profileAvatarImg';
        wrapper.insertBefore(img, wrapper.firstChild);
    }

    // Show remove button if it doesn't exist
    if (!document.getElementById('removeAvatarBtn')) {
        const cardBody = wrapper.closest('.detail-card-body');
        if (cardBody) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'btn-avatar-remove';
            btn.id = 'removeAvatarBtn';
            btn.title = 'Remove avatar';
            btn.innerHTML = '<i class="fas fa-trash-alt"></i> Remove Avatar';
            cardBody.appendChild(btn);
            // Re-initialize to attach event
            btn.addEventListener('click', function() {
                if (!confirm('Remove your profile avatar?')) return;
                const fd = new FormData();
                fd.append('action', 'remove');
                fetch(ADMIN_URL + '/api/avatar-upload.php', { method: 'POST', body: fd })
                    .then(function(r) { return r.json(); })
                    .then(function(d) {
                        if (d.success) {
                            showNotification(d.message, 'success');
                            revertToInitials();
                            updateSidebarAvatar(null);
                        } else {
                            showNotification(d.message || 'Failed to remove avatar.', 'error');
                        }
                    })
                    .catch(function() {
                        showNotification('Failed to remove avatar.', 'error');
                    });
            });
        }
    }
}

/**
 * Revert profile avatar to initials placeholder
 */
function revertToInitials() {
    const img = document.getElementById('profileAvatarImg');
    const placeholder = document.getElementById('profileAvatarPlaceholder');
    const removeBtn = document.getElementById('removeAvatarBtn');

    if (img) img.remove();
    if (placeholder) {
        placeholder.style.display = '';
    } else {
        // Create placeholder if it doesn't exist
        const wrapper = document.querySelector('.profile-avatar-wrapper');
        if (wrapper) {
            const div = document.createElement('div');
            div.className = 'user-avatar-placeholder profile-avatar-icon';
            div.id = 'profileAvatarPlaceholder';
            div.style.cssText = 'width: 100px; height: 100px; font-size: 1.85rem; background:rgb(241, 142, 37); color: white;';
            // Get first letter of name from the page
            const nameEl = wrapper.closest('.detail-card-body')?.querySelector('h3');
            div.textContent = nameEl ? nameEl.textContent.trim().charAt(0).toUpperCase() : '?';
            wrapper.insertBefore(div, wrapper.firstChild);
        }
    }
    if (removeBtn) removeBtn.remove();
}

/**
 * Update sidebar avatar display
 */
function updateSidebarAvatar(avatarUrl) {
    const sidebarFooter = document.querySelector('.sidebar-footer .user-info');
    if (!sidebarFooter) return;

    const existingImg = sidebarFooter.querySelector('.sidebar-user-avatar');
    const existingPlaceholder = sidebarFooter.querySelector('.user-avatar-placeholder');

    if (avatarUrl) {
        const cacheBuster = '?t=' + Date.now();
        if (existingImg) {
            existingImg.src = avatarUrl + cacheBuster;
        } else {
            if (existingPlaceholder) existingPlaceholder.style.display = 'none';
            const img = document.createElement('img');
            img.src = avatarUrl + cacheBuster;
            img.alt = 'Avatar';
            img.className = 'user-avatar sidebar-user-avatar';
            sidebarFooter.insertBefore(img, sidebarFooter.firstChild);
        }
    } else {
        // Remove image, show placeholder
        if (existingImg) existingImg.remove();
        if (existingPlaceholder) {
            existingPlaceholder.style.display = '';
        }
    }
}

/**
 * Add notification styles dynamically
 */
(function() {
    const style = document.createElement('style');
    style.textContent = `
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 14px 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            display: flex;
            align-items: center;
            gap: 12px;
            z-index: 9999;
            transform: translateX(120%);
            transition: transform 0.3s ease;
            max-width: 400px;
        }
        
        .notification.show {
            transform: translateX(0);
        }
        
        .notification-error {
            border-left: 4px solid #ef4444;
        }
        
        .notification-success {
            border-left: 4px solid #10b981;
        }
        
        .notification-info {
            border-left: 4px solid #3b82f6;
        }
        
        .notification-icon {
            font-size: 1.25rem;
        }
        
        .notification-message {
            flex: 1;
            font-size: 0.9rem;
            color: #1e293b;
        }
        
        .notification-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #94a3b8;
            cursor: pointer;
            padding: 0;
            line-height: 1;
        }
        
        .notification-close:hover {
            color: #64748b;
        }
        
        .spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 0.8s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .form-control.error {
            border-color: #ef4444;
            animation: shake 0.4s ease;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
    `;
    document.head.appendChild(style);
})();

