// CV file input handling
const cvInput = document.getElementById('cv');
const cvFileName = document.getElementById('cvFileName');
const cvLabel = document.getElementById('cvLabel');

cvInput.addEventListener('change', function(e) {
    if (this.files.length > 0) {
        const fileName = this.files[0].name;
        const fileSize = (this.files[0].size / 1024 / 1024).toFixed(2); // Size in MB

        if (this.files[0].size > 5 * 1024 * 1024) {
            alert('Le fichier est trop volumineux. La taille maximale est de 5 Mo.');
            this.value = '';
            cvFileName.textContent = '';
            cvLabel.classList.add('required-file');
            return;
        }

        cvFileName.textContent = `✓ Fichier sélectionné: ${fileName} (${fileSize} Mo)`;
        cvFileName.classList.add('success');
        cvLabel.classList.remove('required-file');
        cvLabel.style.borderColor = '#27ae60';
        cvLabel.style.background = '#f0f9f4';
        cvLabel.style.color = '#27ae60';
    } else {
        cvFileName.textContent = '';
        cvFileName.classList.remove('success');
        cvLabel.classList.add('required-file');
        cvLabel.style.borderColor = '';
        cvLabel.style.background = '';
        cvLabel.style.color = '';
    }
});

// Form submission handling
const form = document.getElementById('candidatureForm');
const successMessage = document.getElementById('successMessage');
const formContainer = document.querySelector('.form-container');

form.addEventListener('submit', function(e) {
    e.preventDefault();

    // Validate form
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    // Check if CV is uploaded
    if (!cvInput.files.length) {
        alert('Veuillez joindre votre CV (obligatoire)');
        cvInput.focus();
        return;
    }

    // Disable submit button
    const submitBtn = form.querySelector('.submit-btn');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Envoi en cours...';

    // Simulate form submission (replace with actual API call)
    setTimeout(() => {
        // Hide form
        formContainer.classList.add('hidden');

        // Show success message
        successMessage.classList.add('show');

        // Scroll to success message
        successMessage.scrollIntoView({ behavior: 'smooth', block: 'center' });

        // Reset form
        form.reset();
        cvFileName.textContent = '';
        cvFileName.classList.remove('success');
        cvLabel.classList.add('required-file');
        cvLabel.style.borderColor = '';
        cvLabel.style.background = '';
        cvLabel.style.color = '';
        submitBtn.disabled = false;
        submitBtn.textContent = 'Envoyer ma candidature';

        // Optional: Redirect to home page after 4 seconds
        setTimeout(() => {
            // window.location.href = 'elliteam_final.html';
        }, 4000);
    }, 1500);
});