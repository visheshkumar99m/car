let nav = document.getElementById("color_change");
let add_line_nav=document.querySelector(".navbar");
window.addEventListener('scroll', () => {
    if (window.scrollY >= 30) {
        nav.style.backgroundColor = "white";
        add_line_nav.style.outline="3px solid whitesmoke";
    }
    else {
        nav.style.backgroundColor = "transparent";
        add_line_nav.style.outline="2px solid white";
    }
})