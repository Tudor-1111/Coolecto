
const mesajEroare = document.getElementById('containereroare');
const butonClose = document.getElementById('closeErrorBtn');

if (mesajEroare && butonClose) {

    const closeContainer=()=>{
        mesajEroare.style.opacity = '0';
        setTimeout(() => {
            mesajEroare.style.display = 'none';
        } , 300)
    }

    butonClose.addEventListener('click',closeContainer);
    setTimeout(closeContainer,9000);
}