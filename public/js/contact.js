/**
 * Contact Page - JavaScript
 */

(function() {
    'use strict';

    // Attendre le chargement du DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    function init() {
        setupContactForm();
        setupFAQ();
        setupChatButton();
        console.log('📧 Page Contact chargée');
    }

    // ===========================
    // FORMULAIRE DE CONTACT
    // ===========================
    function setupContactForm() {
        const form = document.getElementById('contactForm');
        const successDiv = document.getElementById('formSuccess');

        if (!form) return;

        form.addEventListener('submit', function(e) {
            e.preventDefault();

            // Validation
            if (!validateForm(form)) {
                return;
            }

            // Animation du bouton
            const submitBtn = form.querySelector('.btn-submit');
            const originalText = submitBtn.innerHTML;

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi en cours...';

            // Simuler l'envoi (remplacer par votre API)
            setTimeout(function() {
                // Cacher le formulaire
                form.style.display = 'none';

                // Afficher le message de succès
                successDiv.classList.add('active');

                // Scroll vers le haut du formulaire
                successDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });

                // Réinitialiser le bouton
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;

                // Envoyer les données (exemple)
                const formData = new FormData(form);
                console.log('Données du formulaire:', Object.fromEntries(formData));

            }, 2000);
        });
    }

    // Validation du formulaire
    function validateForm(form) {
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;

        requiredFields.forEach(function(field) {
            if (!field.value.trim()) {
                isValid = false;
                showError(field, 'Ce champ est requis');
            } else {
                clearError(field);
            }

            // Validation email
            if (field.type === 'email' && field.value) {
                if (!isValidEmail(field.value)) {
                    isValid = false;
                    showError(field, 'Email invalide');
                }
            }
        });

        return isValid;
    }

    function showError(field, message) {
        field.style.borderColor = '#EF4444';

        // Supprimer l'ancien message d'erreur
        const existingError = field.parentNode.querySelector('.error-message');
        if (existingError) {
            existingError.remove();
        }

        // Ajouter le message d'erreur
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.style.color = '#EF4444';
        errorDiv.style.fontSize = '13px';
        errorDiv.style.marginTop = '4px';
        errorDiv.textContent = message;
        field.parentNode.appendChild(errorDiv);

        // Animation shake
        field.style.animation = 'shake 0.5s';
        setTimeout(function() {
            field.style.animation = '';
        }, 500);
    }

    function clearError(field) {
        field.style.borderColor = '';
        const errorDiv = field.parentNode.querySelector('.error-message');
        if (errorDiv) {
            errorDiv.remove();
        }
    }

    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    // Réinitialiser le formulaire
    window.resetForm = function() {
        const form = document.getElementById('contactForm');
        const successDiv = document.getElementById('formSuccess');

        form.reset();
        form.style.display = 'block';
        successDiv.classList.remove('active');

        // Scroll vers le formulaire
        form.scrollIntoView({ behavior: 'smooth', block: 'start' });
    };

    // ===========================
    // FAQ ACCORDION
    // ===========================
    function setupFAQ() {
        const faqItems = document.querySelectorAll('.faq-item');

        faqItems.forEach(function(item) {
            const question = item.querySelector('.faq-question');

            question.addEventListener('click', function() {
                // Fermer les autres items
                faqItems.forEach(function(otherItem) {
                    if (otherItem !== item) {
                        otherItem.classList.remove('active');
                    }
                });

                // Toggle l'item actuel
                item.classList.toggle('active');
            });
        });
    }

    // ===========================
    // BOUTON CHAT
    // ===========================
    function setupChatButton() {
        const chatBtn = document.querySelector('.btn-chat');

        if (!chatBtn) return;

        chatBtn.addEventListener('click', function() {
            // Simuler l'ouverture d'un chat
            alert('Fonctionnalité de chat en cours de développement.\nContactez-nous par téléphone ou email pour le moment.');

            // Ici, intégrer votre solution de chat (Intercom, Crisp, etc.)
            // window.Intercom('show');
        });
    }

    // ===========================
    // VALIDATION EN TEMPS RÉEL
    // ===========================
    document.addEventListener('DOMContentLoaded', function() {
        const inputs = document.querySelectorAll('.contact-form input, .contact-form textarea, .contact-form select');

        inputs.forEach(function(input) {
            input.addEventListener('blur', function() {
                if (this.hasAttribute('required') && !this.value.trim()) {
                    showError(this, 'Ce champ est requis');
                } else if (this.type === 'email' && this.value && !isValidEmail(this.value)) {
                    showError(this, 'Email invalide');
                } else {
                    clearError(this);
                }
            });

            input.addEventListener('input', function() {
                if (this.value.trim()) {
                    clearError(this);
                }
            });
        });
    });

    // ===========================
    // ANIMATION SHAKE
    // ===========================
    const style = document.createElement('style');
    style.textContent = `
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
    `;
    document.head.appendChild(style);

})();