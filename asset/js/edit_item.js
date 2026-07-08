document.addEventListener("DOMContentLoaded", function() {
    
    const pozaItem = document.getElementById('poza_item');
    if (pozaItem) {
        pozaItem.addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('preview-item').src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
    }

    const countrySelect = document.getElementById('country');
    
    if (countrySelect) {
        const savedCountry = countrySelect.dataset.savedCountry;
        
        fetch('https://restcountries.com/v3.1/all')
            .then(response => response.json())
            .then(data => {
                data.sort((a, b) => a.name.common.localeCompare(b.name.common));
                
                countrySelect.innerHTML = '<option value="" disabled>Select a country...</option>';
                
                data.forEach(country => {
                    let option = document.createElement('option');
                    option.value = country.name.common;
                    option.textContent = country.name.common;
                    
                    if (savedCountry && country.name.common === savedCountry) {
                        option.selected = true;
                    }
                    
                    countrySelect.appendChild(option);
                });
            })
            .catch(error => console.error('Error fetching countries:', error));
    }
});