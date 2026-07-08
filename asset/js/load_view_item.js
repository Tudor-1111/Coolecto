document.addEventListener("DOMContentLoaded", () => {
    const urlParams = new URLSearchParams(window.location.search);
    const itemId = urlParams.get('id');
    const from = urlParams.get('from') || '';
    const via = urlParams.get('via') || '';

    if (!itemId) {
        window.location.href = "profilepage.html";
        return;
    }

   

    fetch(`controller/get_view_item.php?id=${itemId}`)
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                alert(data.message);
                window.location.href = "profilepage.html";
                return;
            }

            const item = data.item;
            const isAbsoluteOwner = data.is_owner;
            const showOwnerControls = isAbsoluteOwner;

            document.title = `${item.name} - Coolecto`;

            let backUrl = `view_collection.html?id=${item.collection_id}`;
            if (from) backUrl += `&from=${encodeURIComponent(from)}`;
            if (via) backUrl += `&via=${encodeURIComponent(via)}`;

            const sidebarContainer = document.getElementById('sidebar-container');
            if (showOwnerControls) {
                sidebarContainer.innerHTML = `
                    <div class="collection-sidebar">
                        <h3>Item Settings</h3>
                        <hr>
                        <ul>
                            <li><a href="edit_item.html?id=${item.id}"><i class="fa-solid fa-pen"></i> Edit Details</a></li>
                            <li class="spacer"><hr></li>
                            <li><a href="controller/delete_item.php?id=${item.id}" class="delete-btn"><i class="fa-solid fa-trash"></i> Delete Item</a></li>
                        </ul>
                        <div>
                            <a href="${backUrl}" class="back-btn">
                                <i class="fa-solid fa-chevron-left"></i> Back
                            </a>
                        </div>
                    </div>
                `;
            } else {
                sidebarContainer.innerHTML = `
                    <div class="collection-sidebar info-sidebar">
                        <div>
                            <a href="${backUrl}" class="back-btn">
                                <i class="fa-solid fa-chevron-left"></i> Back
                            </a>
                        </div>
                    </div>
                `;
            }

            const contentContainer = document.getElementById('item-content-container');
            
            const imagine = item.item_image ? item.item_image : 'default_item.png';
            
            const labelBadge = item.has_label == 1 
                ? `<span class="badge public" style="background-color: #28a745; margin-left: 0px;"><i class="fa-solid fa-check"></i> With Label</span>`
                : `<span class="badge private" style="margin-left: 0px;"><i class="fa-solid fa-times"></i> Without Label</span>`;
            
            const descriere = item.description ? item.description.replace(/\n/g, '<br>') : '';
            const istoric = item.history ? `<p class="item-description" style="margin-top: 15px;"><strong>History:</strong> ${item.history.replace(/\n/g, '<br>')}</p>` : '';
            const tara = item.country ? `<p class="item-description" style="margin-top: 15px;"><strong>Country of origin:</strong> <i class="fa-solid fa-globe"></i> ${item.country}</p>` : '';

            const pretValoare = item.price ? `${item.price} ${item.currency || ''}` : 'N/A';
            
            const startStr = (item.usage_start_date && item.usage_start_date !== '0000-00-00') ? item.usage_start_date : 'N/A';
            const endStr = (item.usage_end_date && item.usage_end_date !== '0000-00-00') ? item.usage_end_date : 'Prezent';
            let usageValoare = 'Unknown';
            if (startStr !== 'N/A' || endStr !== 'Prezent') {
                usageValoare = `<span style="white-space: nowrap;">${startStr}</span><br>
                                <i class="fa-solid fa-arrow-down" style="font-size: 0.8em; color: #888; margin: 4px 0;"></i><br>
                                <span style="white-space: nowrap;">${endStr}</span>`;
            }

            const dataAchizitie = (item.date_of_purchase && item.date_of_purchase !== '0000-00-00') ? item.date_of_purchase : 'Unknown';

            contentContainer.innerHTML = `
                <div class="item-hero-container">
                    <img src="imagini/imagini_item/${imagine}" alt="Poza Item" class="item-hero-image">
                </div>
                
                <div class="item-title-section">
                    <h2>${item.name}</h2>
                    ${labelBadge}
                    <p class="item-description" style="margin-top: 10px;"><strong>Description:</strong> ${descriere}</p>
                    ${istoric}
                    ${tara}
                </div>

                <hr class="divider">

                <h3>Item Details</h3>
                <div class="item-details-box">
                    <div class="detail-col">
                        <p class="detail-label">Price</p>
                        <p class="detail-value">${pretValoare}</p>
                    </div>
                    <div class="detail-col">
                        <p class="detail-label">Usage Period</p>
                        <p class="detail-value">${usageValoare}</p>
                    </div>
                    <div class="detail-col right-align">
                        <p class="detail-label">Acquired On</p>
                        <p class="detail-value">${dataAchizitie}</p>
                    </div>
                </div>
            `;
        })
        .catch(error => {
            console.error("Eroare la aducerea datelor itemului:", error);
            document.getElementById('item-content-container').innerHTML = "<p>Eroare de conexiune.</p>";
        });
});