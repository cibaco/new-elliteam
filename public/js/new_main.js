// Add click event listeners to buttons
document.querySelectorAll('.btn').forEach(button => {
    button.addEventListener('click', function() {
        const text = this.textContent.trim();
        if (text.includes('offre')) {
            //alert('Redirection vers le formulaire de dépôt d\'offre');
        } else {
           // alert('Redirection vers le formulaire de candidature');
        }
    });
});