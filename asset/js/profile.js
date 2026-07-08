document.addEventListener("DOMContentLoaded", () => {
    const btnSetari = document.getElementById("setaricont");
    const infoContainer = document.getElementById("info");
    const btnColectii = document.getElementById("colectii");

    let numeGlobal = "";
    let emailGlobal = "";
    let pozaGlobala = "imagini/imagini_profile/default_pfp.png";

    fetch('controller/get_current_user.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const user = data.user;
                
                numeGlobal = user.username;
                emailGlobal = user.email;
                pozaGlobala = user.user_pfp ? `imagini/imagini_profile/${user.user_pfp}` : 'imagini/imagini_profile/default_pfp.png';

                if (document.getElementById('nume-nou')) {
                    document.getElementById('nume-nou').value = numeGlobal;
                    document.getElementById('email-afisare').value = emailGlobal;
                    document.getElementById('imagine-profil-preview').src = pozaGlobala;
                }
            } else {
                window.location.href = "loginpage.html?error=unauthorized";
            }
        })
        .catch(error => console.error("Eroare la aducerea datelor:", error));

    function ataseazaEvenimenteSetari() {
        const formNume = document.getElementById("form-schimbare-nume");
        const inputNume = document.getElementById("nume-nou");
        const btnSalvare = document.getElementById("btn-salvare");
        const divMesaj = document.getElementById("mesaj-raspuns");

        if (!inputNume || !btnSalvare) return; 

        let usernameCurent = inputNume.value;

        inputNume.addEventListener("dblclick", () => {
            inputNume.removeAttribute("readonly");
            inputNume.focus();
        });

        inputNume.addEventListener("input", () => {
            const textIntrodus = inputNume.value.trim(); 
            if (textIntrodus !== usernameCurent && textIntrodus !== "") {
                btnSalvare.style.display = "block";
            } else {
                btnSalvare.style.display = "none";
            }
        });

        formNume.addEventListener("submit", function(eveniment) {
            eveniment.preventDefault();
            
            const numeNou = inputNume.value.trim();
            divMesaj.innerHTML = "Se salveaza..."; 

            fetch("controller/update_username.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                },
                body: "nume_nou=" + encodeURIComponent(numeNou)
            })
            .then(response => response.json())
            .then(date => {
                if(date.success) {
                    divMesaj.innerHTML = `<span style="color: #28a745;">${date.message}</span>`;
                    usernameCurent = numeNou;
                    
                    numeGlobal = numeNou; 

                    inputNume.setAttribute("readonly", true); 
                    btnSalvare.style.display = "none"; 
                    setTimeout(() => {
                        divMesaj.innerHTML = ""; 
                    }, 2000);
                } else {
                    divMesaj.innerHTML = `<span style="color: red;">${date.message}</span>`;
                }
            })
            .catch(eroare => {
                divMesaj.innerHTML = `<span style="color: red;">Eroare de conexiune cu serverul.</span>`;
            });
        });
    }

    function ataseazaEvenimentePoza() {
        const wrapperPoza = document.getElementById("schimba-poza-btn");
        const inputPoza = document.getElementById("input-poza");
        const imaginePreview = document.getElementById("imagine-profil-preview");

        if (!wrapperPoza || !inputPoza) return;

        wrapperPoza.addEventListener("click", () => {
            inputPoza.click();
        });

        inputPoza.addEventListener("change", function() {
            const fisierNou = this.files[0];
            
            if (fisierNou) {
                const cititor = new FileReader();
                cititor.onload = function(eveniment) {
                    imaginePreview.src = eveniment.target.result;
                }
                cititor.readAsDataURL(fisierNou);

                const formData = new FormData();
                formData.append("poza_profil", fisierNou);

                fetch("controller/upload_pfp.php", {
                    method: "POST",
                    body: formData 
                })
                .then(response => response.json())
                .then(date => {
                    if(!date.success) {
                        alert("Eroare la incarcare: " + date.message);
                    } else {
                        pozaGlobala = `imagini/imagini_profile/${date.nume_poza}`;
                    }
                })
                .catch(eroare => console.error("Eroare conexiune:", eroare));
            }
        });
    }

    ataseazaEvenimenteSetari();
    ataseazaEvenimentePoza();

    btnSetari.addEventListener("click", () => {
        infoContainer.innerHTML = `
            <h2>Account Settings</h2>
        <hr>
        <div id="continut">
            <div id="camp">
                <form id="form-schimbare-nume">
                    <p>
                        <label for="nume-nou">Username:</label>
                        <input type="text" id="nume-nou" name="nume_nou" value="${numeGlobal}" readonly required>
                    </p>
                    <p>
                        <button type="submit" id="btn-salvare" class="btn-salvare" style="display: none;">Salveaza</button>
                    </p>
                    <div id="mesaj-raspuns"></div>
                </form>
                <p style="margin-top: 20px;">
                    <label for="email-afisare">Email:</label>
                    <input type="text" id="email-afisare" value="${emailGlobal}" readonly disabled>
                </p>
            </div>
            
            <div class="container-poza-profil">
                <div class="poza-wrapper" id="schimba-poza-btn">
                    <img src="${pozaGlobala}" alt="Poza de profil" class="profil-img" id="imagine-profil-preview">
                    
                    <div class="poza-overlay">
                        <i class="fa-solid fa-camera"></i> Change
                    </div>
                </div>
                
                <input type="file" id="input-poza" accept="image/*" style="display: none;">
            </div>
        </div>
        `;

        ataseazaEvenimenteSetari();
        ataseazaEvenimentePoza();
    });

    btnColectii.addEventListener("click", () => {
        const pozaAdd = "imagini/imagini_collection/add.png";

        infoContainer.innerHTML = `
            <h2>My Collections</h2>
            <hr>
            <div id="collections" class="colectii-grid"></div>
        `;

        fetch("controller/get_collections.php")
        .then(response => response.json())
        .then(date => {
            const container = document.getElementById("collections");
            container.innerHTML = ""; 

            if(date.success && date.colectii.length > 0) {
                date.colectii.forEach(colectie => {
                    const imagine = colectie.collection_image ? colectie.collection_image : 'default_collection.png';
                    
                    container.innerHTML += `
                        <a class="colectie-card" href="view_collection.html?id=${colectie.id}">
                            <img src="imagini/imagini_collection/${imagine}" alt="${colectie.name}" class="imagine-colectie">
                            <div class="info-text-colectie">
                            <h3>${colectie.name}</h3>
                            <p>${colectie.description}</p>
                            </div>
                        </a>
                    `;
                });
            }
            container.innerHTML += `
                <button id="add-collection" onclick="window.location.href='new_collection.html'">
                    <img src="${pozaAdd}" id="imagine-add">
                </button>
            `;

        })
        .catch(eroare => {
            document.getElementById("collections").innerHTML = "<p style='color:red;'>Eroare la conexiune.</p>";
        });
    });

    if (window.location.hash === "#colectii") {     
        if (btnColectii) {
            btnColectii.click();
        }
    }
});