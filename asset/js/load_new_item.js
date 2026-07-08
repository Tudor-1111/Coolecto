document.addEventListener("DOMContentLoaded", () => {
    
    fetch('config/logstatus.php')
        .then(response => response.json())
        .then(dateSesiune => {
            if (dateSesiune.esteLogat !== true) {
                window.location.href = "loginpage.html";
                return;
            }

            const urlParams = new URLSearchParams(window.location.search);
            const collectionId = urlParams.get('collection_id');

            if (!collectionId) {
                window.location.href = "profilepage.html";
                return;
            }

            const inputCollectionId = document.getElementById('collection_id_input');
            if (inputCollectionId) {
                inputCollectionId.value = collectionId;
            }
        })
        .catch(eroare => {
            console.error("Eroare la verificarea sesiunii:", eroare);
            window.location.href = "loginpage.html";
        });
});