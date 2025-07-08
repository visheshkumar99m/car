// Handle navbar background color change on scroll
document.addEventListener('DOMContentLoaded', function() {
    window.addEventListener('scroll', function() {
        const navbar = document.getElementById('color_change');
        
        if (window.scrollY > 50) {
            // When scrolled down
            navbar.classList.add('scrolled');
        } else {
            // When at the top
            navbar.classList.remove('scrolled');
        }
    });
    
    // Trigger the scroll event once on page load to set initial state
    window.dispatchEvent(new Event('scroll'));
}); 