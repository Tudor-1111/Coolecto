document.addEventListener("DOMContentLoaded", () => {
    
    const urlParams = new URLSearchParams(window.location.search);
    const collectionId = urlParams.get('id');

    if (!collectionId) {
        window.location.href = "profilepage.html";
        return;
    }

    const selectCat = document.getElementById('category_id');

    fetch('controller/get_categories.php')
        .then(response => response.json())
        .then(categoriesData => {
            
            if (categoriesData.success) {
                
                selectCat.innerHTML = '<option value="" disabled selected>Select a category...</option>';
                
                
                categoriesData.categories.forEach(cat => {
                    const option = document.createElement('option');
                    option.value = cat.id;
                    option.textContent = cat.name;
                    selectCat.appendChild(option);
                });
            }

           
            return fetch(`controller/get_collection.php?id=${collectionId}`);
        })
        .then(response => response.json())
        .then(data => {
            
            if (data.success) {
                const col = data.collection;

               
                document.getElementById('collection_id').value = col.id;
                document.getElementById('name').value = col.name;
                document.getElementById('description').value = col.description;
                
              
                if (col.category_id) {
                    selectCat.value = col.category_id;
                }

                
                if (col.is_public == 1) {
                    document.getElementById('is_public').checked = true;
                }

                
                const imagine = col.collection_image ? col.collection_image : 'default_collection.png';
                document.getElementById('preview-colectie').src = `imagini/imagini_collection/${imagine}`;

            } else {
                alert(data.message);
                window.location.href = "profilepage.html";
            }
        })
        .catch(error => {
            console.error("Eroare la preluarea datelor:", error);
            window.location.href = "profilepage.html";
        });
});