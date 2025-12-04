// candidature-form.js - Version corrigée pour Symfony

document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form[name="candidature"]');

    if (!form) return;

    const submitBtn = form.querySelector('button[type="submit"]');
    const cvFileInput = document.querySelector('input[type="file"]');

    // Validation en temps réel du fichier CV
    if (cvFileInput) {
        const cvLabel = cvFileInput.closest('.form-group')?.querySelector('label');
        const cvHelp = cvFileInput.closest('.form-group')?.querySelector('.form-help');

        cvFileInput.addEventListener('change', function(e) {
            const file = this.files[0];

            if (file) {
                // Vérifier la taille (5Mo max)
                const maxSize = 5 * 1024 * 1024; // 5MB en bytes
                if (file.size > maxSize) {
                    alert('Le fichier est trop volumineux. Taille maximale : 5Mo');
                    this.value = '';
                    return;
                }

                // Vérifier le type
                if (file.type !== 'application/pdf') {
                    alert('Seuls les fichiers PDF sont acceptés');
                    this.value = '';
                    return;
                }

                // Afficher le nom du fichier
                const fileName = file.name;
                const fileSize = (file.size / 1024 / 1024).toFixed(2);

                if (cvHelp) {
                    cvHelp.textContent = `Fichier sélectionné: ${fileName} (${fileSize} Mo)`;
                    cvHelp.style.color = '#27ae60';
                }

                if (cvLabel) {
                    cvLabel.style.color = '#27ae60';
                }
            }
        });
    }

    // Animation du bouton pendant la soumission
    form.addEventListener('submit', function(e) {
        // NE PAS empêcher la soumission par défaut
        // e.preventDefault(); // ← SUPPRIMER CETTE LIGNE

        // Désactiver le bouton pour éviter les doubles soumissions
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Envoi en cours...';
        }

        // Laisser le formulaire se soumettre normalement à Symfony
        // Le serveur redirigera vers la page de succès
    });

    // Validation du téléphone en temps réel
    const phoneInput = document.querySelector('input[type="tel"]');
    if (phoneInput) {
        phoneInput.addEventListener('blur', function() {
            const phoneRegex = /^(?:(?:\+|00)33|0)\s*[1-9](?:[\s.-]*\d{2}){4}$/;
            if (this.value && !phoneRegex.test(this.value)) {
                this.classList.add('is-invalid');

                // Créer un message d'erreur si il n'existe pas
                let errorMsg = this.parentElement.querySelector('.invalid-feedback');
                if (!errorMsg) {
                    errorMsg = document.createElement('div');
                    errorMsg.className = 'invalid-feedback';
                    errorMsg.textContent = 'Format de téléphone invalide (ex: 06 12 34 56 78)';
                    this.parentElement.appendChild(errorMsg);
                }
            } else {
                this.classList.remove('is-invalid');
            }
        });
    }

    // Validation de l'email en temps réel
    const emailInput = document.querySelector('input[type="email"]');
    if (emailInput) {
        emailInput.addEventListener('blur', function() {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (this.value && !emailRegex.test(this.value)) {
                this.classList.add('is-invalid');

                let errorMsg = this.parentElement.querySelector('.invalid-feedback');
                if (!errorMsg) {
                    errorMsg = document.createElement('div');
                    errorMsg.className = 'invalid-feedback';
                    errorMsg.textContent = 'Format d\'email invalide';
                    this.parentElement.appendChild(errorMsg);
                }
            } else {
                this.classList.remove('is-invalid');
            }
        });
    }

    // Auto-hide flash messages après 5 secondes
    const flashMessages = document.querySelectorAll('.flash-message, .alert');
    flashMessages.forEach(function(message) {
        setTimeout(function() {
            message.style.transition = 'opacity 0.5s';
            message.style.opacity = '0';
            setTimeout(() => message.remove(), 500);
        }, 5000);
    });
});