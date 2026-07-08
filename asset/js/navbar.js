
async function incarcaMeniu() {
    const container = document.getElementById("navbar-container");
    if (container) {
        try {
            const raspunsServer = await fetch('config/logstatus.php');
            const dateSesiune = await raspunsServer.json();

            let meniuHTML = `
                <nav id="bara-meniu">
                
                <div class="logo" style="display: flex; align-items: center; gap: 12px; font-weight: 500; font-size: 1.3rem; color: white; letter-spacing: 0.5px;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" width="34" height="34" fill="none" stroke="#FFFFFF" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="32" cy="32" r="6" />
                        <path d="M32,10 L32,26" />
                        <path d="M50,18 L38,26" />
                        <path d="M54,32 L38,32" />
                        <path d="M50,46 L38,38" />
                        <path d="M32,54 L32,38" />
                        <path d="M14,46 L26,38" />
                        <path d="M10,32 L26,32" />
                        <path d="M14,18 L26,26" />
                    </svg>
                    <span>Coolecto</span>
                </div>
                            
                <div class="meniu-butoane">
                <a href="community.html">Community</a>
            `;

            if (dateSesiune.esteLogat == true) {
                meniuHTML += `
                <a href="firstpage.html">Home</a>
                <a href="profilepage.html">Profile</a>
                `;
            }
            else {
                meniuHTML += `
                    <a href="loginpage.html">Login</a>
                    <a href="registerpage.html">Register</a>
                `;
            }

            meniuHTML += `
            </div>
            </nav>
            `;

            container.innerHTML = meniuHTML;

        }
        catch (eroare) {
            console.error("Eroare la nav bar", eroare);
        }
    }


}

incarcaMeniu();