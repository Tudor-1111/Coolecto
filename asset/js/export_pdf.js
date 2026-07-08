document.addEventListener("DOMContentLoaded", () => {
    
    
    const queryString = window.location.search;

    fetch(`controller/get_stats_pdf.php${queryString}`)
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                alert(data.message);
                return;
            }

          
            const global = data.globalStats;
            document.getElementById('g-users').textContent = global.total_users;
            document.getElementById('g-colls').textContent = global.total_collections;
            document.getElementById('g-reviews').textContent = global.total_reviews;
            
            const ratingFormat = parseFloat(global.top_rated_score || 0).toFixed(2);
            document.getElementById('g-top-rated').innerHTML = `
                ${global.top_rated_name} <small>(by ${global.top_rated_owner})</small> - ${ratingFormat} / 5
            `;

            const expFormat = parseFloat(global.expensive_value || 0).toFixed(2);
            document.getElementById('g-expensive').innerHTML = `
                ${global.expensive_name} <small>(by ${global.expensive_owner})</small> - <strong>${expFormat} RON</strong>
            `;



           
            const filtered = data.filteredStats;
            document.getElementById('f-colls').textContent = filtered.total_filtered_collections || 0;
            document.getElementById('f-items').textContent = filtered.total_filtered_items || 0;
            
            const fValueFormat = parseFloat(filtered.total_filtered_value_ron || 0).toFixed(2);
            document.getElementById('f-value').textContent = `${fValueFormat} RON`;

          



            const listBody = document.getElementById('filtered-list-body');
            const lista = data.filteredList;

            if (lista.length === 0) {
                listBody.innerHTML = `<tr><td colspan="6" style="text-align: center; color: #888;">No collections found matching current filters.</td></tr>`;
            } else {
                let htmlRanduri = '';
                lista.forEach(row => {
                    const valFormat = parseFloat(row.total_value_ron || 0).toFixed(2);
                    const ratFormat = parseFloat(row.medie_rating || 0).toFixed(2);
                    const catName = row.category_name ? row.category_name : 'N/A';
                    
                    htmlRanduri += `
                        <tr>
                            <td><strong>${row.collection_name}</strong></td>
                            <td>${catName}</td>
                            <td>${row.owner}</td>
                            <td>${row.items_count}</td>
                            <td class="highlight">${valFormat} RON</td>
                            <td>${ratFormat} / 5</td>
                        </tr>
                    `;
                });
                listBody.innerHTML = htmlRanduri;
            }

           
            document.getElementById('footer-date').textContent = `Report extracted on: ${data.currentDate}`;

           
            setTimeout(() => {
                window.print();
            }, 300);

        })
        .catch(error => {
            console.error("Eroare la aducerea datelor PDF:", error);
            document.body.innerHTML = "<h2 style='text-align:center;'>A aparut o eroare la generarea raportului.</h2>";
        });
});