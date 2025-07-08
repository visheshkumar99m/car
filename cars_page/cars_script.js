// Wait for the document to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide messages after 5 seconds
    const messages = document.querySelectorAll('.message');
    if (messages.length > 0) {
        setTimeout(function() {
            messages.forEach(function(message) {
                message.style.opacity = '0';
                setTimeout(function() {
                    message.style.display = 'none';
                }, 500);
            });
        }, 5000);
    }
    
    // Initialize any necessary components
    const filterCollapse = document.getElementById('filterCollapse');
    if (filterCollapse) {
        // If filter options are active, show the collapse by default
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('brand') || urlParams.has('minPrice') || urlParams.has('maxPrice')) {
            const bsCollapse = new bootstrap.Collapse(filterCollapse, {
                toggle: true
            });
        }
    }
}); 