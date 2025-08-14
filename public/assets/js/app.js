// CNA Upholstery Management System - Main JavaScript

class CNA {
    constructor() {
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.setupFormValidation();
        this.setupAjaxDefaults();
    }

    setupEventListeners() {
        // Close flash messages
        document.querySelectorAll('.alert .close-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.target.closest('.alert').style.display = 'none';
            });
        });

        // Auto-hide flash messages after 5 seconds
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => {
                if (!alert.classList.contains('alert-error')) {
                    alert.style.opacity = '0';
                    setTimeout(() => alert.style.display = 'none', 300);
                }
            });
        }, 5000);

        // Mobile menu toggle
        const mobileMenuBtn = document.querySelector('#mobile-menu-btn');
        const mobileMenu = document.querySelector('#mobile-menu');
        
        if (mobileMenuBtn && mobileMenu) {
            mobileMenuBtn.addEventListener('click', () => {
                mobileMenu.classList.toggle('hidden');
            });
        }
    }

    setupFormValidation() {
        // Basic client-side validation
        document.querySelectorAll('form[data-validate]').forEach(form => {
            form.addEventListener('submit', (e) => {
                const requiredFields = form.querySelectorAll('[required]');
                let isValid = true;

                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        this.showFieldError(field, 'This field is required');
                        isValid = false;
                    } else {
                        this.clearFieldError(field);
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                }
            });
        });

        // Real-time validation
        document.querySelectorAll('input[type="email"]').forEach(input => {
            input.addEventListener('blur', () => {
                if (input.value && !this.isValidEmail(input.value)) {
                    this.showFieldError(input, 'Please enter a valid email address');
                } else {
                    this.clearFieldError(input);
                }
            });
        });

        document.querySelectorAll('input[type="tel"], input[data-phone]').forEach(input => {
            input.addEventListener('blur', () => {
                if (input.value && !this.isValidPhone(input.value)) {
                    this.showFieldError(input, 'Please enter a valid phone number');
                } else {
                    this.clearFieldError(input);
                }
            });
        });
    }

    setupAjaxDefaults() {
        // Set up CSRF token for all AJAX requests
        if (window.csrfToken) {
            const originalFetch = window.fetch;
            window.fetch = function(url, options = {}) {
                if (!options.headers) {
                    options.headers = {};
                }
                if (options.method && options.method.toUpperCase() !== 'GET') {
                    options.headers['X-CSRF-TOKEN'] = window.csrfToken;
                }
                return originalFetch(url, options);
            };
        }
    }

    showFieldError(field, message) {
        this.clearFieldError(field);
        
        field.classList.add('border-red-500', 'focus:border-red-500', 'focus:ring-red-500');
        field.classList.remove('border-gray-300', 'focus:border-cna-primary-500', 'focus:ring-cna-primary-500');
        
        const errorDiv = document.createElement('div');
        errorDiv.className = 'text-red-600 text-sm mt-1 field-error';
        errorDiv.textContent = message;
        
        field.parentNode.insertBefore(errorDiv, field.nextSibling);
    }

    clearFieldError(field) {
        field.classList.remove('border-red-500', 'focus:border-red-500', 'focus:ring-red-500');
        field.classList.add('border-gray-300', 'focus:border-cna-primary-500', 'focus:ring-cna-primary-500');
        
        const errorDiv = field.parentNode.querySelector('.field-error');
        if (errorDiv) {
            errorDiv.remove();
        }
    }

    isValidEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    isValidPhone(phone) {
        const digits = phone.replace(/\D/g, '');
        return digits.length >= 10 && digits.length <= 15;
    }

    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} fixed top-4 right-4 z-50 max-w-sm`;
        notification.innerHTML = `
            <div class="flex items-center">
                <span>${message}</span>
                <button type="button" class="ml-3 close-btn">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            notification.style.opacity = '0';
            setTimeout(() => notification.remove(), 300);
        }, 5000);
        
        // Close button
        notification.querySelector('.close-btn').addEventListener('click', () => {
            notification.remove();
        });
    }

    formatCurrency(amount, locale = 'en-US') {
        return new Intl.NumberFormat(locale, {
            style: 'currency',
            currency: 'USD'
        }).format(amount);
    }

    formatDate(date, locale = 'en-US') {
        return new Date(date).toLocaleDateString(locale);
    }
}

// Initialize the application
document.addEventListener('DOMContentLoaded', () => {
    window.CNA = new CNA();
});

// Global functions for backward compatibility
function confirmDelete(url, message) {
    if (confirm(message || 'Are you sure you want to delete this item?')) {
        fetch(url, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (window.CNA) {
                    window.CNA.showNotification(data.message || 'Item deleted successfully', 'success');
                }
                setTimeout(() => location.reload(), 1000);
            } else {
                if (window.CNA) {
                    window.CNA.showNotification(data.message || 'An error occurred', 'error');
                } else {
                    alert(data.message || 'An error occurred');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (window.CNA) {
                window.CNA.showNotification('An error occurred', 'error');
            } else {
                alert('An error occurred');
            }
        });
    }
}