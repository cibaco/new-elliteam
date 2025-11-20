// File input handling
const fileInput = document.getElementById('attachment');
const fileNameDisplay = document.getElementById('fileName');

fileInput.addEventListener('change', function(e) {
    if (this.files.length > 0) {
        const fileName = this.files[0].name;
        const fileSize = (this.files[0].size / 1024 / 1024).toFixed(2); // Size in MB

        if (this.files[0].size > 5 * 1024 * 1024) {
            alert('Le fichier est trop volumineux. La taille maximale est de 5 Mo.');
            this.value = '';
            fileNameDisplay.textContent = '';
            return;
        }

        fileNameDisplay.textContent = `Fichier sélectionné: ${fileName} (${fileSize} Mo)`;
    } else {
        fileNameDisplay.textContent = '';
    }
});

// Form submission handling
const form = document.getElementById('offerForm');
const successMessage = document.getElementById('successMessage');
const formContainer = document.querySelector('.form-container');

form.addEventListener('submit', function(e) {
    e.preventDefault();

    // Validate form
    if (!form.checkValidity()) {
        form.reportValidity();
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
        fileNameDisplay.textContent = '';
        submitBtn.disabled = false;
        submitBtn.textContent = 'Envoyer ma demande';

        // Optional: Redirect to home page after 3 seconds
        setTimeout(() => {
            // window.location.href = 'elliteam_final.html';
        }, 3000);
    }, 1500);
});