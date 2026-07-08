document.addEventListener("DOMContentLoaded", () => {
    console.log("Scriptul paginii a pornit! Incepem verificarile...");

    
    fetch('config/logstatus.php')
        .then(response => response.json())
        .then(dateSesiune => {
            if (dateSesiune.esteLogat !== true) {
                console.error("Nu esti logat. Se face redirect spre login...");
                window.location.href = "loginpage.html";
                return; 
            }
            console.log("Logare confirmata!");
        })
        .catch(err => console.error("Eroare la verificarea sesiunii:", err));

   
    const urlParams = new URLSearchParams(window.location.search);
    const parentId = urlParams.get('parent_id');
    const inputFormular = document.getElementById('parent_id_input');
    const inputImport = document.getElementById('parent_id_import');

    if (parentId) {
        if (inputFormular) inputFormular.value = parentId;
        if (inputImport) inputImport.value = parentId;
    } else {
        if (inputFormular) inputFormular.removeAttribute('name');
        if (inputImport) inputImport.removeAttribute('name');
    }

   
    const inputPoza = document.getElementById("poza_colectie");
    const previewImagine = document.getElementById("preview-colectie");
    let pozaDefault = "imagini/imagini_collection/default_collection.png"; 

    if (inputPoza && previewImagine) {
        inputPoza.addEventListener("change", function () {
            const fisier = this.files[0];
            if (fisier) {
                const cititor = new FileReader();
                cititor.onload = function (eveniment) {
                    previewImagine.src = eveniment.target.result;
                    pozaDefault = previewImagine.src;
                };
                cititor.readAsDataURL(fisier);
            } else {
                previewImagine.src = pozaDefault;
            }
        });
    }

   
    const inputFisier = document.getElementById("fisier_import");
    const formImport = document.getElementById("form-import");

    if (inputFisier && formImport) {
        inputFisier.addEventListener("change", function() {
            if (this.files && this.files.length > 0) {
                
                if (parentId) {
                    let hiddenInput = document.getElementById("parent_id_import");
                    if (!hiddenInput) {
                        hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.id = 'parent_id_import';
                        hiddenInput.name = 'parent_id';
                        formImport.appendChild(hiddenInput);
                    }
                    hiddenInput.value = parentId;
                }
                formImport.submit();
            }
        });
    }



    const categorySelect = document.getElementById('category_id');
    const customCategoryContainer = document.getElementById('custom-category-container');
    const customCategoryInput = document.getElementById('custom_category');

    if (categorySelect) {
        
        fetch('controller/get_categories.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    categorySelect.innerHTML = '<option value="" disabled selected>Select a category...</option>';
                    data.categories.forEach(cat => {
                        categorySelect.innerHTML += `<option value="${cat.id}">${cat.name}</option>`;
                    });
                    categorySelect.innerHTML += `<option value="custom" style="font-weight: bold; color: #28a745;">Custom category</option>`;
                } else {
                    categorySelect.innerHTML = '<option value="" disabled>Eroare la incarcare</option>';
                }
            })
            .catch(error => console.error("Eroare la preluarea categoriilor:", error));

       
        if (customCategoryContainer) {
            categorySelect.addEventListener('change', function() {
                if (this.value === 'custom') {
                    customCategoryContainer.style.display = 'block';
                    customCategoryInput.required = true;
                } else {
                    customCategoryContainer.style.display = 'none';
                    customCategoryInput.required = false;
                    customCategoryInput.value = ''; 
                }
            });
        }
    }
});