const countrySelect = document.getElementById('country');

fetch('https://restcountries.com/v3.1/all?fields=name')
    .then(response => {
        if (!response.ok) {
            throw new Error('Eroare la conectarea cu API-ul de tari');
        }
        return response.json();
    })
    .then(data => {
        data.sort((a, b) => a.name.common.localeCompare(b.name.common));
        
        countrySelect.innerHTML = '<option value="" disabled selected>Select a country...</option>';
        
        data.forEach(country => {
            const option = document.createElement('option');
            option.value = country.name.common;
            option.textContent = country.name.common;
            countrySelect.appendChild(option);
        });
    })
    .catch(error => {
        console.error('Eroare tari:', error);
        countrySelect.innerHTML = '<option value="" disabled>Eroare la incarcarea tarilor</option>';
    });