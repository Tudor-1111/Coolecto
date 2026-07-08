document.addEventListener("DOMContentLoaded", () => {
    
    fetch('controller/get_current_user.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const user = data.user;

                document.getElementById('nume-nou').value = user.username;
                document.getElementById('email-afisare').value = user.email;

                const imagine = user.user_pfp ? user.user_pfp : 'default_pfp.png';
                document.getElementById('imagine-profil-preview').src = `imagini/imagini_profile/${imagine}`;
                
            } else {
                window.location.href = "loginpage.html?error=unauthorized";
            }
        })
        .catch(error => {
            console.error("Eroare la obținerea datelor utilizatorului:", error);
        });
});