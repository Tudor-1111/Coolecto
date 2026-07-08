document.addEventListener("DOMContentLoaded", () => {
    const urlParams = new URLSearchParams(window.location.search);
    const itemId = urlParams.get('id');

    if (!itemId) {
        window.location.href = "profilepage.html";
        return;
    }

    fetch(`controller/get_item.php?id=${itemId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const item = data.item;

                document.getElementById('item_id').value = item.id;
                document.getElementById('collection_id').value = item.collection_id;
                document.getElementById('name').value = item.name;
                document.getElementById('description').value = item.description || "";
                document.getElementById('history').value = item.history || "";
                
                if (item.price) document.getElementById('price').value = item.price;
                if (item.usage_start_date) document.getElementById('usage_start_date').value = item.usage_start_date;
                if (item.usage_end_date) document.getElementById('usage_end_date').value = item.usage_end_date;

                if (item.date_of_purchase && item.date_of_purchase !== '0000-00-00') {
                    document.getElementById('datepurchase').value = item.date_of_purchase;
                }

                if (item.category_id) {
                    document.getElementById('category_id').value = item.category_id;
                }

                if (item.currency) {
                    let currencyVal = item.currency;
                    if (currencyVal === 'RON') currencyVal = '1';
                    if (currencyVal === 'EUR') currencyVal = '2';
                    if (currencyVal === 'USD') currencyVal = '3';
                    if (currencyVal === 'GBP') currencyVal = '4';
                    document.getElementById('currency').value = currencyVal;
                }

                if (item.has_label == 1) {
                    document.getElementById('has_label').checked = true;
                }

                if (item.country) {
                    const countrySelect = document.getElementById('country');
                    countrySelect.setAttribute('data-saved-country', item.country);
                    if (countrySelect.querySelector(`option[value="${item.country}"]`)) {
                        countrySelect.value = item.country;
                    }
                }

                const imagine = item.item_image ? item.item_image : 'default_item.png';
                document.getElementById('current_image').value = imagine;
                document.getElementById('preview-item').src = `imagini/imagini_item/${imagine}`;

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