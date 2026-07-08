document.addEventListener("DOMContentLoaded", () => {
    
    const yearSpan = document.getElementById('current-year');
    if (yearSpan) {
        yearSpan.textContent = new Date().getFullYear();
    }

    const container = document.getElementById('trending-container');

    fetch('controller/get_trending_collections.php')
        .then(response => response.json())
        .then(data => {
            container.innerHTML = '';

            if (!data.success) {
                container.innerHTML = `<p class="no-data">${data.message}</p>`;
                return;
            }

            if (data.data.length === 0) {
                container.innerHTML = '<p class="no-data">No public collections available at the moment.</p>';
                return;
            }

            data.data.forEach((col, index) => {
                const badgeClass = `medal-${index + 1}`;
                const rating = parseFloat(col.medie_rating).toFixed(2);
                
                const safeName = col.name.replace(/</g, "&lt;").replace(/>/g, "&gt;");
                const safeUsername = col.username.replace(/</g, "&lt;").replace(/>/g, "&gt;");

                const cardHTML = `
                    <div class="trending-card">
                        <div class="medal-badge ${badgeClass}">
                            <i class="fa-solid fa-trophy"></i> #${index + 1}
                        </div>

                        <h3>${safeName}</h3>
                        <p class="owner">by <strong>${safeUsername}</strong></p>

                        <div class="rating">
                            <i class="fa-solid fa-star"></i> ${rating} / 5
                        </div>
                        
                        <a href="view_collection.html?id=${col.id}" class="btn-view">View Collection</a>
                    </div>
                `;
                
                container.innerHTML += cardHTML;
            });
        })
        .catch(error => {
            console.error("Eroare la aducerea colectiilor populare:", error);
            container.innerHTML = '<p class="no-data">A aparut o eroare de conexiune.</p>';
        });
});