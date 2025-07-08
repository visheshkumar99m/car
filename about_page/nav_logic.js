let nav = document.getElementById("color_change");
window.addEventListener('scroll', () => {
    if (window.scrollY >= 30) {
        nav.style.backgroundColor = "white";

    }
    else {
        nav.style.backgroundColor = "transparent";
    }
})