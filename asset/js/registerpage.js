document.addEventListener("DOMContentLoaded", () => {
    const urlParams = new URLSearchParams(window.location.search);
    const errorType = urlParams.get('error');
    
    const errorContainer = document.getElementById('containereroare');
    const errorText = document.getElementById('error-text');

    if (errorType && errorContainer && errorText) {
        let mesaj = "A aparut o eroare la crearea contului.";
        
        if (errorType === 'password_mismatch') {
            mesaj = "Parolele nu coincid! Incearca din nou!";
        } else if (errorType === 'duplicate_user') {
            mesaj = "Exista deja un cont cu acest username sau email!";
        } else if (errorType === 'db_error') {
            mesaj = "Eroare la baza de date. Te rugam sa incerci mai tarziu.";
        }

        errorText.textContent = mesaj;
        errorContainer.style.display = "flex"; 
    }
});