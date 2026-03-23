// SkyVoyage - Main JavaScript

document.addEventListener('DOMContentLoaded', function () {

    // --- Mobile Nav Toggle ---
    const navToggle = document.getElementById('navToggle');
    const navLinks = document.getElementById('navLinks');

    if (navToggle && navLinks) {
        navToggle.addEventListener('click', function () {
            navLinks.classList.toggle('active');
            navToggle.classList.toggle('active');
        });

        // Close menu when clicking a link
        navLinks.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                navLinks.classList.remove('active');
                navToggle.classList.remove('active');
            });
        });
    }

    // --- Auto-dismiss flash alerts ---
    const flashAlert = document.getElementById('flashAlert');
    if (flashAlert) {
        setTimeout(() => {
            flashAlert.style.opacity = '0';
            flashAlert.style.transform = 'translateY(-10px)';
            flashAlert.style.transition = 'all 0.3s ease';
            setTimeout(() => flashAlert.remove(), 300);
        }, 4000);
    }

    // --- Form Validation ---
    const forms = document.querySelectorAll('form[data-validate]');
    forms.forEach(form => {
        form.addEventListener('submit', function (e) {
            let isValid = true;

            // Clear previous errors
            form.querySelectorAll('.form-error').forEach(el => el.remove());
            form.querySelectorAll('.form-control.error').forEach(el => el.classList.remove('error'));

            // Validate required fields
            form.querySelectorAll('[required]').forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    showFieldError(field, 'This field is required');
                }
            });

            // Validate email fields
            form.querySelectorAll('input[type="email"]').forEach(field => {
                if (field.value && !isValidEmail(field.value)) {
                    isValid = false;
                    showFieldError(field, 'Please enter a valid email');
                }
            });

            // Validate password match
            const password = form.querySelector('input[name="password"]');
            const confirmPassword = form.querySelector('input[name="confirm_password"]');
            if (password && confirmPassword && password.value !== confirmPassword.value) {
                isValid = false;
                showFieldError(confirmPassword, 'Passwords do not match');
            }

            if (!isValid) {
                e.preventDefault();
            }
        });
    });

    // --- Confirm Delete ---
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', function (e) {
            const msg = this.getAttribute('data-confirm') || 'Are you sure?';
            if (!confirm(msg)) {
                e.preventDefault();
            }
        });
    });

    // --- Animate elements on scroll ---
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-fade-in');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    document.querySelectorAll('.flight-card, .stat-card, .booking-card').forEach(el => {
        observer.observe(el);
    });

    // --- Set minimum date for date inputs ---
    const dateInputs = document.querySelectorAll('input[type="date"]');
    const today = new Date().toISOString().split('T')[0];
    dateInputs.forEach(input => {
        if (!input.min) {
            input.min = today;
        }
    });
});

// Helper: Show field error
function showFieldError(field, message) {
    field.classList.add('error');
    field.style.borderColor = '#ef4444';
    const errorEl = document.createElement('span');
    errorEl.className = 'form-error';
    errorEl.style.cssText = 'color: #ef4444; font-size: 0.8rem; margin-top: 4px; display: block;';
    errorEl.textContent = message;
    field.parentNode.appendChild(errorEl);
}

// Helper: Validate email
function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}
