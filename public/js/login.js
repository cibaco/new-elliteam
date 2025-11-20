// Password toggle
const togglePassword = document.getElementById('togglePassword');
const passwordInput = document.getElementById('password');

togglePassword.addEventListener('click', function() {
    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordInput.setAttribute('type', type);
    this.textContent = type === 'password' ? '👁️' : '🙈';
});

// Form submission
const loginForm = document.getElementById('loginForm');
const submitBtn = loginForm.querySelector('.submit-btn');
const alertMessage = document.getElementById('alertMessage');
const alertText = document.getElementById('alertText');

// Show alert function
function showAlert(message, type = 'error') {
    alertMessage.className = 'alert alert-' + type + ' show';
    alertText.textContent = message;

    // Auto-hide after 5 seconds
    setTimeout(() => {
        alertMessage.classList.remove('show');
    }, 5000);
}

// Handle form submission
loginForm.addEventListener('submit', function(e) {
    e.preventDefault();

    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;

    // Validation
    if (!email || !password) {
        showAlert('Veuillez remplir tous les champs', 'error');
        return;
    }

    // Add loading state
    submitBtn.classList.add('loading');
    submitBtn.disabled = true;

    // Simulate API call (replace with actual login logic)
    setTimeout(() => {
        // Remove loading state
        submitBtn.classList.remove('loading');
        submitBtn.disabled = false;

        // Example: Check credentials (replace with real authentication)
        if (email === 'admin@elliteam.com' && password === 'admin') {
            showAlert('Connexion réussie ! Redirection...', 'success');

            // Redirect to admin dashboard after 1 second
            setTimeout(() => {
                window.location.href = '/admin';
            }, 1000);
        } else {
            showAlert('Email ou mot de passe incorrect', 'error');
        }
    }, 1500);
});

// Show info message on page load (optional)
window.addEventListener('load', function() {
    // Uncomment to show info message on load
    // showAlert('Bienvenue ! Connectez-vous pour accéder à votre espace.', 'info');
});

// Handle "Enter" key in form
loginForm.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        loginForm.dispatchEvent(new Event('submit'));
    }
});

// Focus management
const emailInput = document.getElementById('email');
emailInput.focus();

// Prevent multiple submissions
let isSubmitting = false;
loginForm.addEventListener('submit', function(e) {
    if (isSubmitting) {
        e.preventDefault();
        return false;
    }
    isSubmitting = true;

    // Reset after 2 seconds
    setTimeout(() => {
        isSubmitting = false;
    }, 2000);
});