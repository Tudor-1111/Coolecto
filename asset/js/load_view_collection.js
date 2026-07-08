document.addEventListener("DOMContentLoaded", () => {
    const urlParams = new URLSearchParams(window.location.search);
    const collectionId = urlParams.get('id');
    const from = urlParams.get('from') || '';
    const via = urlParams.get('via') || '';

    if (!collectionId) {
        window.location.href = "profilepage.html";
        return;
    }

    const fromSuffix = from ? `&from=${from}` : '';
    const viaSuffix = via ? `&via=${via}` : '';

    const itemsContainer = document.getElementById("items-container");
    const subcollectionsContainer = document.getElementById("subcollections-container");

    fetch(`controller/get_view_collection.php?id=${collectionId}`)
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                alert(data.message);
                window.location.href = "profilepage.html";
                return;
            }

            const col = data.collection;
            const isAbsoluteOwner = data.is_owner;
            const isLoggedIn = data.is_logged_in;
            const reviews = data.reviews;
            const showOwnerControls = isAbsoluteOwner;

            document.title = `${col.name} - Coolecto`;

            let backUrl = "profilepage.html#colectii";
            if (from === 'community' && via !== 'parent') {
                backUrl = "community.html";
            } else if (col.parent_id) {
                backUrl = `view_collection.html?id=${col.parent_id}`;
                if (from) backUrl += `&from=${encodeURIComponent(from)}`;
            }


            const sidebarContainer = document.getElementById('sidebar-container');
            if (sidebarContainer) {
                if (showOwnerControls) {
                    sidebarContainer.innerHTML = `
                        <div class="collection-sidebar">
                            <h3>Settings</h3><hr>
                            <ul>
                                <li><a href="edit_collection.html?id=${col.id}"><i class="fa-solid fa-pen"></i> Edit Details</a></li>
                                <li><a href="new_item.html?collection_id=${col.id}"><i class="fa-solid fa-plus"></i> Add Item</a></li>
                                <li><a href="new_collection.html?parent_id=${col.id}"><i class="fa-solid fa-folder-plus"></i> Add Subcollection</a></li>
                                <li><a href="controller/export_collection.php?id=${col.id}"><i class="fa-solid fa-download"></i> Exporta JSON</a></li>
                                <li>
                                    <form id="form-merge" action="controller/merge_collection.php" method="POST" enctype="multipart/form-data" style="display: inline;">
                                        <input type="hidden" name="target_collection_id" value="${col.id}">
                                        <label for="fisier_merge" style="cursor: pointer; width: 100%;">
                                            <i class="fa-solid fa-code-merge"></i> Merge JSON
                                        </label>
                                        <input type="file" id="fisier_merge" name="fisier_merge" accept=".json" style="display: none;">
                                    </form>
                                </li>
                                <li class="spacer"><hr></li>
                                <li><a href="controller/delete_collection.php?id=${col.id}" class="delete-btn"><i class="fa-solid fa-trash"></i> Delete Collection</a></li>
                            </ul>
                            <div>
                                <a href="${backUrl}" class="back-btn"><i class="fa-solid fa-chevron-left"></i> Back</a>
                            </div>
                        </div>
                    `;
                    initMergeLogic();
                } else {
                    const creatorName = col.username ? col.username : 'Utilizator';
                    sidebarContainer.innerHTML = `
                        <div class="collection-sidebar info-sidebar">
                            <h3>Informations</h3><hr>
                            <p class="info-creator">
                                <span class="info-label">
                                    Created by : <i class="fa-solid fa-user"></i> ${creatorName}
                                </span>
                                <a href="controller/export_collection.php?id=${col.id}" class="btn-export">
                                    <i class="fa-solid fa-download"></i> Exporta JSON
                                </a>
                                <a href="${backUrl}" class="back-btn back-btn-margin">
                                    <i class="fa-solid fa-chevron-left"></i> Back
                                </a>
                            </p>
                        </div>
                    `;
                }
            }

            const headerContainer = document.getElementById('collection-header-container');
            if (headerContainer) {
                const dataCreare = new Date(col.created_at).toLocaleDateString('ro-RO');
                const descriereFormatata = col.description ? col.description.replace(/\n/g, '<br>') : '';
                const categorie = col.category_name ? col.category_name : 'Fara categorie';
                const statusBadge = col.is_public == 1 
                    ? `<span class="badge public"><i class="fa-solid fa-globe"></i> Public</span>` 
                    : `<span class="badge private"><i class="fa-solid fa-lock"></i> Private</span>`;

                headerContainer.innerHTML = `
                    <img src="imagini/imagini_collection/${col.collection_image}" alt="Poza Colectie" class="cover-image">
                    <div class="text-header">
                        <h2>${col.name}</h2>
                        <p class="desc">Created at: ${dataCreare}</p>
                        <span class="badge-category"><i class="fa-solid fa-tag"></i> ${categorie}</span>
                        <br>
                        ${statusBadge}
                        <p class="desc" style="margin-top: 15px;">${descriereFormatata}</p>
                    </div>
                `;
            }

            const reviewsContainer = document.getElementById('reviews-list-container');
            if (reviewsContainer) {
                if (!reviews || reviews.length === 0) {
                    reviewsContainer.innerHTML = `<p class="empty-msg">No reviews yet for this collection.</p>`;
                } else {
                    let htmlRecenzii = `<ul class="reviews-list">`;
                    reviews.forEach(rev => {
                        let stele = '';
                        for (let i = 1; i <= 5; i++) {
                            if (i <= rev.nota) stele += `<i class="fa-solid fa-star active-star"></i>`;
                            else stele += `<i class="fa-regular fa-star" style="color: #444;"></i>`;
                        }
                        const dataReview = new Date(rev.created_at).toLocaleDateString('ro-RO');
                        htmlRecenzii += `
                            <li class="review-card">
                                <div class="review-header">
                                    <span class="review-author"><i class="fa-solid fa-user"></i> ${rev.username}</span>
                                    <span class="review-stars-display">${stele}</span>
                                </div>
                                <p class="review-text">${rev.descriere.replace(/\n/g, '<br>')}</p>
                                <small class="review-date">${dataReview}</small>
                            </li>
                        `;
                    });
                    htmlRecenzii += `</ul>`;
                    reviewsContainer.innerHTML = htmlRecenzii;
                }
            }

            const formContainer = document.getElementById('review-form-container');
            if (formContainer && isLoggedIn && !isAbsoluteOwner) {
                formContainer.innerHTML = `
                    <div class="add-review-section">
                        <h3 style="color: #fff; margin-top: 30px; margin-bottom: 15px;">Leave a review</h3>
                        <form action="controller/add_review.php" method="POST" class="review-form" id="review-form">
                            <input type="hidden" name="collection_id" value="${col.id}">
                            <input type="hidden" name="nota" id="rating-value" value="">
                            
                            <div class="form-group">
                                <div class="star-rating" id="star-rating">
                                    <i class="fa-regular fa-star" data-value="1"></i>
                                    <i class="fa-regular fa-star" data-value="2"></i>
                                    <i class="fa-regular fa-star" data-value="3"></i>
                                    <i class="fa-regular fa-star" data-value="4"></i>
                                    <i class="fa-regular fa-star" data-value="5"></i>
                                </div>
                                <p id="rating-error" class="error-msg hidden-error">Please select a star rating.</p>
                            </div>
                            <div class="form-group">
                                <textarea name="descriere" id="descriere" rows="4" required class="form-textarea" placeholder="Write your thoughts here..."></textarea>
                            </div>
                            <button type="submit" class="btn-submit">Submit Review</button>
                        </form>
                    </div>
                `;
                initStarRating();
            }
        })
        .catch(error => {
            console.error("Eroare la aducerea datelor generale:", error);
        });

    if (itemsContainer) {
        fetch(`controller/get_items_by_collection.php?collection_id=${collectionId}`)
            .then(response => response.json())
            .then(data => {
                itemsContainer.innerHTML = "";
                if (data.success && data.items && data.items.length > 0) {
                    data.items.forEach(item => {
                        const imagine = item.item_image ? item.item_image : 'default_item.png';
                        let priceHtml = '';
                        if (item.price) {
                            const currency = item.currency ? item.currency : '';
                            priceHtml = `<p class="item-price"><strong>Price:</strong> ${item.price} ${currency}</p>`;
                        }
                        let dateHtml = '';
                        if (item.date_of_purchase && item.date_of_purchase !== '0000-00-00') {
                            dateHtml = `<p class="item-date"><strong>Acquired:</strong> ${item.date_of_purchase}</p>`;
                        }

                        itemsContainer.innerHTML += `
                           <a href="view_item.html?id=${item.id}${fromSuffix}${viaSuffix}" class="item-card" style="text-decoration: none; color: inherit;">
                                <img src="imagini/imagini_item/${imagine}" alt="${item.name}" class="imagine-item">
                                <div class="info-text-item">
                                    <h4>${item.name}</h4>
                                    ${priceHtml}
                                    ${dateHtml}
                                </div>
                            </a>
                        `;
                    });
                } else if (data.success && data.items.length === 0) {
                     itemsContainer.innerHTML = "<p style='color: #888;'>No items in this collection yet.</p>";
                } else {
                     itemsContainer.innerHTML = `<p style='color: red;'>Eroare: ${data.message}</p>`;
                }
            })
            .catch(eroare => {
                console.error("Eroare fetch iteme:", eroare);
                itemsContainer.innerHTML = "<p style='color:red;'>A aparut o eroare la conexiune.</p>";
            });
    }

    if (subcollectionsContainer) {
        fetch(`controller/get_subcollections_by_collection.php?collection_id=${collectionId}${fromSuffix}`)
            .then(response => response.json())
            .then(data => {
                subcollectionsContainer.innerHTML = "";
                if (data.success && data.subcollections && data.subcollections.length > 0) {
                    data.subcollections.forEach(subcollection => {
                        const imagine = subcollection.collection_image ? subcollection.collection_image : 'default_collection.png';
                        let descriptionHtml = '';
                        if (subcollection.description) {
                            descriptionHtml = `<p class="item-price"><strong>Description: </strong>${subcollection.description}</p>`;
                        }

                        subcollectionsContainer.innerHTML += `
                            <a href="view_collection.html?id=${subcollection.id}${fromSuffix}&via=parent" class="item-card" style="text-decoration: none; color: inherit;">
                                <img src="imagini/imagini_collection/${imagine}" alt="${subcollection.name}" class="imagine-item">
                                <div class="info-text-item">
                                    <h4>${subcollection.name}</h4>
                                    ${descriptionHtml}
                                </div>
                            </a>
                        `;
                    });
                } else if (data.success && data.subcollections.length === 0) {
                     subcollectionsContainer.innerHTML = "<p style='color: #888;'>No subcollections yet.</p>";
                } else {
                     subcollectionsContainer.innerHTML = `<p style='color: red;'>Eroare: ${data.message}</p>`;
                }
            })
            .catch(eroare => {
                console.error("Eroare fetch subcolectii:", eroare);
                subcollectionsContainer.innerHTML = "<p style='color:red;'>A aparut o eroare la conexiune.</p>";
            });
    }

    function initMergeLogic() {
        const inputMerge = document.getElementById("fisier_merge");
        const formMerge = document.getElementById("form-merge");

        if (inputMerge && formMerge) {
            inputMerge.addEventListener("change", function() {
                if (this.files && this.files.length > 0) {
                    formMerge.submit();
                }
            });
        }
    }

    function initStarRating() {
        const stars = document.querySelectorAll('#star-rating i');
        const ratingInput = document.getElementById('rating-value');
        const reviewForm = document.getElementById('review-form');
        const ratingError = document.getElementById('rating-error');

        if (stars.length > 0) {
            stars.forEach(star => {

                star.addEventListener('mouseover', function() {
                    const value = this.getAttribute('data-value');
                    updateStars(value);
                });

                star.addEventListener('mouseout', function() {
                    const currentValue = ratingInput.value || 0;
                    updateStars(currentValue);
                });

                star.addEventListener('click', function() {
                    const value = this.getAttribute('data-value');
                    ratingInput.value = value;
                    updateStars(value);
                    if (ratingError) ratingError.classList.add('hidden-error');
                });
            });

            function updateStars(value) {
                stars.forEach(star => {
                    const starValue = star.getAttribute('data-value');
                    if (starValue <= value) {
                        star.classList.remove('fa-regular');
                        star.classList.add('fa-solid', 'active-star');
                    } else {
                        star.classList.remove('fa-solid', 'active-star');
                        star.classList.add('fa-regular');
                    }
                });
            }

            if (reviewForm) {
                reviewForm.addEventListener('submit', function(e) {
                    if (!ratingInput.value) {
                        e.preventDefault(); 
                        if (ratingError) ratingError.classList.remove('hidden-error'); 
                    }
                });
            }
        }
    }
});