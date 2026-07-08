document.addEventListener("DOMContentLoaded", () => {
    const urlParams = new URLSearchParams(window.location.search);
    const errorType = urlParams.get('error');
    
    const errorContainer = document.getElementById('containereroare');
    const errorText = document.getElementById('error-text');

    if (errorType && errorContainer && errorText) {
        let mesaj = "A aparut o eroare la autentificare.";
        
        if (errorType === 'wrong_pass') {
            mesaj = "Parola gresita!";
        } else if (errorType === 'not_found') {
            mesaj = "Nu exista niciun user cu acest username sau email!";
        } else if (errorType === 'unauthorized') {
            mesaj = "Trebuie sa te loghezi pentru a accesa pagina!";
        } else if (errorType === 'auto_login_failed') {
            mesaj = "Cont creat cu succes, dar logarea automata a esuat. Te rugam sa te loghezi manual.";
        }

        errorText.textContent = mesaj;
        errorContainer.style.display = "flex"; 
    }

    const destination = sessionStorage.getItem('redirect_to');
    
    if (destination) {
        const loginForm = document.querySelector('form');
        
        if (loginForm) {
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'redirect_to';
            hiddenInput.value = '../' + destination; 
            
            loginForm.appendChild(hiddenInput);
            
            sessionStorage.removeItem('redirect_to');
        }
    }
});