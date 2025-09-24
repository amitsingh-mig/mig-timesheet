// Application JavaScript

// Confirmation dialog functionality
document.addEventListener('click', function(e) {
    const target = e.target.closest('[data-confirm]');
    if (target) {
        if (!confirm(target.getAttribute('data-confirm'))) {
            e.preventDefault();
        }
    }
});

// Real-time clock functionality
function updateTime() {
    const now = new Date();
    const timeOptions = { 
        hour: '2-digit', 
        minute: '2-digit', 
        second: '2-digit',
        hour12: false
    };
    const dateOptions = { 
        weekday: 'short',
        month: 'short', 
        day: 'numeric'
    };
    
    const timeDisplay = document.getElementById('timeDisplay');
    const dateDisplay = document.getElementById('dateDisplay');
    
    if (timeDisplay) {
        timeDisplay.textContent = now.toLocaleTimeString('en-US', timeOptions);
    }
    if (dateDisplay) {
        dateDisplay.textContent = now.toLocaleDateString('en-US', dateOptions);
    }
}

// Update time immediately and then every second
document.addEventListener('DOMContentLoaded', function() {
    updateTime();
    setInterval(updateTime, 1000);
    
    // Add smooth animations to navigation links
    const navLinks = document.querySelectorAll('.nav-link-enhanced');
    navLinks.forEach(link => {
        link.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
            this.style.transition = 'all 0.3s ease';
        });
        link.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
    
    // Add loading states for navigation clicks
    const allNavLinks = document.querySelectorAll('.nav-link, .dropdown-item');
    allNavLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Skip if it's a dropdown toggle or logout button
            if (this.getAttribute('data-bs-toggle') === 'dropdown' || 
                this.closest('form') || 
                this.getAttribute('href') === '#') {
                return;
            }
            
            // Add loading state
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Loading...';
            this.style.opacity = '0.7';
            
            // Reset after 3 seconds (fallback)
            setTimeout(() => {
                this.innerHTML = originalText;
                this.style.opacity = '1';
            }, 3000);
        });
    });
});
});
