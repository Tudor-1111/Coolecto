document.addEventListener("DOMContentLoaded", () => {
    const csvBtn = document.getElementById("btn-export-csv");
    const pdfBtn = document.getElementById("id-export-pdf");
    const applyBtn = document.getElementById("apply-filters-btn"); 
    const container = document.getElementById("collections-container");

  
    const filterName = document.getElementById('filter-name');
    const filterCategory = document.getElementById('filter-category');
    const filterUser = document.getElementById('filter-user');
    const filterSort = document.getElementById('filter-sort');

   
    if (filterName) filterName.value = sessionStorage.getItem('saved_name') || '';
    if (filterUser) filterUser.value = sessionStorage.getItem('saved_user') || '';
    if (filterSort) {
        const savedSort = sessionStorage.getItem('saved_sort');
        if (savedSort) filterSort.value = savedSort;
    }
    
    
    if (filterCategory) {
        const savedCategory = sessionStorage.getItem('saved_category');
        if (savedCategory !== null) {
            filterCategory.value = savedCategory;
        } else {
            filterCategory.value = '7'; 
        }
    }


    function fetchAndRenderCollections() {
       
        const nameVal = filterName ? filterName.value : '';
        const categoryVal = filterCategory ? filterCategory.value : '7';
        const userVal = filterUser ? filterUser.value : '';
        const sortVal = filterSort ? filterSort.value : '';

        
        sessionStorage.setItem('saved_name', nameVal);
        sessionStorage.setItem('saved_category', categoryVal);
        sessionStorage.setItem('saved_user', userVal);
        sessionStorage.setItem('saved_sort', sortVal);

       
        const params = new URLSearchParams({
            name: nameVal,
            category: categoryVal,
            user: userVal,
            sort: sortVal
        }).toString();

        const queryString = `?${params}`;

        
        if(csvBtn) csvBtn.href = `controller/export_stats_csv.php${queryString}`;
        if(pdfBtn) pdfBtn.href = `export_pdf.html${queryString}`;

       
        fetch(`controller/get_community_collections.php${queryString}`)
            .then(response => response.json())
            .then(data => {
                container.innerHTML = "";

                if (data.success === false) {
                    sessionStorage.setItem('redirect_to', 'community.html');
                    window.location.href = "loginpage.html";
                    return;
                }

                if (!data.collections || data.collections.length === 0) {
                    container.innerHTML = `<div class="no-results-container"><p class="no-results-text">No collections found matching your filters.</p></div>`;
                    return;
                }

              
                data.collections.forEach(c => {
                    const card = document.createElement('a');
                    card.href = `view_collection.html?id=${c.id}&from=community`;
                    card.className = "card";
                    card.style.cssText = "text-decoration: none; color: inherit; display: flex; flex-direction: column;";

                    card.innerHTML = `
                        <div class="card-image" style="background-image: url('imagini/imagini_collection/${c.collection_image}');"></div>
                        <div class="card-content">
                            <h2 class="card-title">${c.name}</h2>
                            <p class="card-desc">${c.description}</p>
                            <p class="card-category">${c.category_name || 'Fara categorie'}</p>
                        </div>
                    `;
                    container.appendChild(card);
                });
            })
            .catch(error => {
                console.error("Eroare la preluarea datelor:", error);
                container.innerHTML = `<p>A aparut o eroare la incarcarea datelor.</p>`;
            });
    }

    
    fetchAndRenderCollections();

   
    if (applyBtn) {
        applyBtn.addEventListener("click", (e) => {
            e.preventDefault();
            fetchAndRenderCollections();
        });
    }
});