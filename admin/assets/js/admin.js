/**
 * SDO CTS Admin Panel JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    initSidebar();
    initFlashMessages();
    initFormValidation();
    initFilterEnterSubmit();
    initModalNotificationHandlers();
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
            let errorMessages = [];
            
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
            
            // Date validation: prevent past dates for request forms
            const dateFields = form.querySelectorAll('input[type="date"], input[type="datetime-local"]');
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            dateFields.forEach(function(field) {
                if (field.value && field.name && (field.name.includes('date_from') || field.name.includes('date_to') || field.name.includes('date_time'))) {
                    const fieldDate = new Date(field.value);
                    fieldDate.setHours(0, 0, 0, 0);
                    
                    if (fieldDate < today) {
                        isValid = false;
                        field.classList.add('error');
                        errorMessages.push('Request date cannot be in the past. Please select today or a future date.');
                        
                        field.addEventListener('input', function() {
                            field.classList.remove('error');
                        }, { once: true });
                    }
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                const message = errorMessages.length > 0 ? errorMessages[0] : 'Please fill in all required fields.';
                showNotification(message, 'error');
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
    showConfirmModal(message).then(function(confirmed) {
        if (confirmed) {
            callback();
        }
    });
}

/**
 * Modal Notifications (alert/confirm style)
 */
let modalDialogState = null;

function createModalDialog() {
    if (modalDialogState) return modalDialogState;

    const overlay = document.createElement('div');
    overlay.className = 'alpas-modal-overlay';
    overlay.innerHTML = `
        <div class="alpas-modal" role="dialog" aria-modal="true" aria-live="assertive">
            <div class="alpas-modal-header">
                <h3 class="alpas-modal-title">Confirm Action</h3>
            </div>
            <div class="alpas-modal-body">
                <p class="alpas-modal-message"></p>
            </div>
            <div class="alpas-modal-footer">
                <button type="button" class="btn btn-secondary alpas-modal-cancel">Cancel</button>
                <button type="button" class="btn btn-primary alpas-modal-confirm">OK</button>
            </div>
        </div>
    `;

    document.body.appendChild(overlay);

    const state = {
        overlay,
        title: overlay.querySelector('.alpas-modal-title'),
        message: overlay.querySelector('.alpas-modal-message'),
        cancel: overlay.querySelector('.alpas-modal-cancel'),
        confirm: overlay.querySelector('.alpas-modal-confirm'),
        resolver: null,
        previousActive: null
    };

    const close = function(result) {
        if (!state.resolver) return;
        const resolve = state.resolver;
        state.resolver = null;
        overlay.classList.remove('active');
        document.body.classList.remove('alpas-modal-open');
        if (state.previousActive && typeof state.previousActive.focus === 'function') {
            state.previousActive.focus();
        }
        resolve(result);
    };

    state.cancel.addEventListener('click', function() {
        close(false);
    });

    state.confirm.addEventListener('click', function() {
        close(true);
    });

    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) {
            close(false);
        }
    });

    document.addEventListener('keydown', function(e) {
        if (!overlay.classList.contains('active')) return;
        if (e.key === 'Escape') {
            e.preventDefault();
            close(false);
        }
    });

    modalDialogState = state;
    return state;
}

function openModalDialog(options = {}) {
    const state = createModalDialog();
    const title = options.title || 'Confirm Action';
    const message = options.message || '';
    const confirmText = options.confirmText || 'OK';
    const cancelText = options.cancelText || 'Cancel';
    const hideCancel = options.hideCancel === true;
    const tone = options.tone || 'info';

    state.previousActive = document.activeElement;
    state.title.textContent = title;
    state.message.textContent = message;
    state.confirm.textContent = confirmText;
    state.cancel.textContent = cancelText;
    state.cancel.style.display = hideCancel ? 'none' : 'inline-flex';

    state.overlay.classList.remove('tone-info', 'tone-success', 'tone-warning', 'tone-error');
    state.overlay.classList.add('tone-' + tone);
    state.overlay.classList.add('active');
    document.body.classList.add('alpas-modal-open');

    setTimeout(function() {
        state.confirm.focus();
    }, 10);

    return new Promise(function(resolve) {
        state.resolver = resolve;
    });
}

function showConfirmModal(message, options = {}) {
    return openModalDialog({
        title: options.title || 'Please Confirm',
        message,
        confirmText: options.confirmText || 'OK',
        cancelText: options.cancelText || 'Cancel',
        hideCancel: false,
        tone: options.tone || 'warning'
    });
}

function showAlertModal(message, options = {}) {
    return openModalDialog({
        title: options.title || 'Notice',
        message,
        confirmText: options.confirmText || 'OK',
        hideCancel: true,
        tone: options.tone || 'info'
    });
}

function initModalNotificationHandlers() {
    document.addEventListener('click', function(e) {
        const trigger = e.target.closest('[data-confirm]');
        if (!trigger || trigger.tagName === 'FORM') return;

        const message = trigger.getAttribute('data-confirm');
        if (!message) return;

        const href = trigger.getAttribute('href');
        const triggerForm = trigger.closest('form') || trigger.form;
        const isSubmitButton = trigger.tagName === 'BUTTON' && (trigger.type === 'submit' || !trigger.type);

        e.preventDefault();

        showConfirmModal(message).then(function(confirmed) {
            if (!confirmed) return;

            if (triggerForm && isSubmitButton) {
                triggerForm.dataset.skipModalConfirm = '1';
                if (typeof triggerForm.requestSubmit === 'function') {
                    triggerForm.requestSubmit(trigger);
                } else {
                    triggerForm.submit();
                }
                return;
            }

            if (href) {
                window.location.href = href;
            }
        });
    });

    document.addEventListener('submit', function(e) {
        const form = e.target;
        if (!(form instanceof HTMLFormElement)) return;
        if (!form.hasAttribute('data-confirm')) return;

        if (form.dataset.skipModalConfirm === '1') {
            delete form.dataset.skipModalConfirm;
            return;
        }

        const message = form.getAttribute('data-confirm');
        if (!message) return;

        e.preventDefault();
        showConfirmModal(message).then(function(confirmed) {
            if (!confirmed) return;

            form.dataset.skipModalConfirm = '1';
            if (typeof form.requestSubmit === 'function') {
                form.requestSubmit();
            } else {
                form.submit();
            }
        });
    });
}

window.showConfirmModal = showConfirmModal;
window.showAlertModal = showAlertModal;

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

        .alpas-modal-open {
            overflow: hidden;
        }

        .alpas-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.45);
            backdrop-filter: blur(2px);
            -webkit-backdrop-filter: blur(2px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            padding: 16px;
        }

        .alpas-modal-overlay.active {
            display: flex;
        }

        .alpas-modal {
            width: 100%;
            max-width: 520px;
            background: #0f172a;
            color: #f8fafc;
            border-radius: 16px;
            border: 1px solid rgba(148, 163, 184, 0.3);
            box-shadow: 0 24px 60px rgba(2, 6, 23, 0.55);
            overflow: hidden;
        }

        .alpas-modal-header {
            padding: 20px 24px 10px;
        }

        .alpas-modal-title {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 700;
        }

        .alpas-modal-body {
            padding: 0 24px 16px;
        }

        .alpas-modal-message {
            margin: 0;
            color: #e2e8f0;
            line-height: 1.55;
        }

        .alpas-modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding: 0 24px 20px;
        }

        .alpas-modal-footer .btn {
            min-width: 110px;
            border-radius: 999px;
        }

        .alpas-modal-overlay.tone-error .alpas-modal {
            border-color: rgba(239, 68, 68, 0.5);
        }

        .alpas-modal-overlay.tone-warning .alpas-modal {
            border-color: rgba(245, 158, 11, 0.5);
        }

        .alpas-modal-overlay.tone-success .alpas-modal {
            border-color: rgba(16, 185, 129, 0.5);
        }

        .alpas-modal-overlay.tone-info .alpas-modal {
            border-color: rgba(14, 165, 233, 0.5);
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
    `;
    document.head.appendChild(style);
})();

