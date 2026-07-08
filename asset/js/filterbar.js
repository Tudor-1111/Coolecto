

document.addEventListener("DOMContentLoaded", function() {
    const inputUser = document.getElementById("filter-user");
    const suggestionsBox = document.getElementById("user-suggestions");


    const urlParams = new URLSearchParams(window.location.search);

    
    if (urlParams.has('name')) {
        const numeInput = document.getElementById('filter-name');
        if (numeInput) numeInput.value = urlParams.get('name');
    }

    
    if (urlParams.has('category')) {
        const categorySelect = document.getElementById('filter-category');
        if (categorySelect) categorySelect.value = urlParams.get('category');
    }

   
    if (urlParams.has('user')) {
        const userInput = document.getElementById('filter-user');
        if (userInput) userInput.value = urlParams.get('user');
    }

   
    if (urlParams.has('sort')) {
        const sortSelect = document.getElementById('filter-sort');
        if (sortSelect) sortSelect.value = urlParams.get('sort');
    }

  
    if (inputUser && suggestionsBox) {
        
        inputUser.addEventListener("input", async function() {
            const valoare = this.value.trim();

            
            if (valoare.length < 1) {
                suggestionsBox.style.display = "none";
                suggestionsBox.innerHTML = "";
                return;
            }

            try {
              
                const response = await fetch(`controller/search_users.php?q=${encodeURIComponent(valoare)}`);
                const users = await response.json();

           
                suggestionsBox.innerHTML = "";

                if (users.length > 0) {
                  
                    users.forEach(user => {
                        const li = document.createElement("li");
                        li.textContent = user;
                        
                      
                        li.addEventListener("click", function() {
                            inputUser.value = this.textContent;
                            suggestionsBox.style.display = "none"; 
                        });
                        
                        suggestionsBox.appendChild(li);
                    });
                    suggestionsBox.style.display = "block"; 
                } else {
                    suggestionsBox.style.display = "none";
                }
            } catch (error) {
                console.error("Eroare la fetch useri:", error);
            }
        });

        document.addEventListener("click", function(e) {
            if (e.target !== inputUser && e.target !== suggestionsBox) {
                suggestionsBox.style.display = "none";
            }
        });
    }



    const applyBtn = document.querySelector('.apply-btn'); 
    
    if (applyBtn) {
        applyBtn.addEventListener('click', function() {
            
            const name = document.getElementById('filter-name').value.trim();
            const category = document.getElementById('filter-category').value;
            const user = document.getElementById('filter-user').value.trim();
            const sort = document.getElementById('filter-sort').value;

            
            const params = new URLSearchParams();
            
            if (name) {
                params.append('name', name);
            }

            if (category && category !== 'all' && category !== '') {
                params.append('category', category);
            }

            if (user) {
                params.append('user', user);
            }

            if (sort) {
                params.append('sort', sort);
            }

            
            window.location.href = 'community.html?' + params.toString();
        });
    }

    const mobileFilterBtn = document.getElementById("mobile-filter-btn"); 
    const closeFilterBtn = document.getElementById("close-filter-btn");   
    const sidebar = document.querySelector(".sidebar");

    
    if (mobileFilterBtn && sidebar) {
        mobileFilterBtn.addEventListener("click", function() {
            sidebar.classList.add("active-mobile");
        });
    }

    
    if (closeFilterBtn && sidebar) {
        closeFilterBtn.addEventListener("click", function(e) {
            e.preventDefault(); 
            sidebar.classList.remove("active-mobile");
        });
    }



});



