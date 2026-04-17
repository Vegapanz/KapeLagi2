// ==========================================
// NAVBAR SHADOW ON SCROLL
// ==========================================

document.addEventListener('DOMContentLoaded', function() {
    const navbar = document.querySelector('.navbar-custom');
    
    function updateNavbarShadow() {
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    }
    
    window.addEventListener('scroll', updateNavbarShadow);
});

// ==========================================
// SMOOTH SCROLL FOR NAVIGATION LINKS
// ==========================================

document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const targetId = this.getAttribute('href');
        
        if (targetId === '#') {
            return;
        }
        
        const targetElement = document.querySelector(targetId);
        if (targetElement) {
            targetElement.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
            
            // Close mobile navbar if open
            const navbarToggle = document.querySelector('.navbar-toggler');
            if (navbarToggle.offsetParent !== null) {
                navbarToggle.click();
            }
        }
    });
});

// ==========================================
// COFFEE CUPS HOVER INTERACTION
// ==========================================

document.addEventListener('DOMContentLoaded', function() {
    const coffeeCups = document.querySelectorAll('.coffee-cup');
    
    coffeeCups.forEach((cup, index) => {
        cup.addEventListener('mouseenter', function() {
            // Slight scale and lift on hover
            this.style.transform = 'scale(1.08) translateY(-20px)';
        });
        
        cup.addEventListener('mouseleave', function() {
            // Return to original position
            if (index === 0) {
                this.style.transform = 'rotate(-15deg) translateX(-40px)';
            } else {
                this.style.transform = 'rotate(8deg) translateX(40px)';
            }
        });
    });
});

// ==========================================
// NAVBAR ACTIVE LINK TRACKING
// ==========================================

document.addEventListener('DOMContentLoaded', function() {
    const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
    
    function setActiveLink() {
        navLinks.forEach(link => {
            link.classList.remove('active');
        });
        
        const sections = document.querySelectorAll('section');
        let currentSection = '';
        
        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            if (scrollY >= sectionTop - 200) {
                currentSection = section.getAttribute('id');
            }
        });
        
        navLinks.forEach(link => {
            if (link.getAttribute('href') === `#${currentSection}`) {
                link.classList.add('active');
            }
        });
    }
    
    window.addEventListener('scroll', setActiveLink);
});

// ==========================================
// PAGE LOAD ANIMATION
// ==========================================

document.addEventListener('DOMContentLoaded', function() {
    document.body.style.opacity = '1';
    document.body.style.animation = 'fadeIn 0.5s ease-out';
});

// ==========================================
// UTILITY ANIMATIONS
// ==========================================

// Add fade-in animation to dynamically loaded content
function animateOnScroll() {
    const elements = document.querySelectorAll('[data-animate]');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });
    
    elements.forEach(element => {
        element.style.opacity = '0';
        element.style.transform = 'translateY(20px)';
        element.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(element);
    });
}

// Initialize scroll animations when DOM loads
document.addEventListener('DOMContentLoaded', animateOnScroll);

// ==========================================
// RESPONSIVE NAVBAR ADJUSTMENT
// ==========================================

window.addEventListener('resize', function() {
    const navbarToggle = document.querySelector('.navbar-toggler');
    if (window.innerWidth > 991) {
        // Ensure navbar is visible on large screens
        const navbarCollapse = document.querySelector('.navbar-collapse');
        navbarCollapse.classList.add('show');
    }
});

// ==========================================
// PREVENT FLASH OF UNSTYLED CONTENT (FOUC)
// ==========================================

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        document.body.style.visibility = 'visible';
    });
} else {
    document.body.style.visibility = 'visible';
}

// ==========================================
// PERFORMANCE OPTIMIZATION
// ==========================================

// Debounce scroll events for better performance
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// ==========================================
// ACCESSIBILITY IMPROVEMENTS
// ==========================================

// Trap focus within mobile navbar when open
document.addEventListener('DOMContentLoaded', function() {
    const navbarToggle = document.querySelector('.navbar-toggler');
    const navbarCollapse = document.querySelector('.navbar-collapse');
    
    navbarToggle.addEventListener('click', function() {
        setTimeout(() => {
            const isOpen = navbarCollapse.classList.contains('show');
            if (isOpen) {
                const firstFocusable = navbarCollapse.querySelector('a, button');
                if (firstFocusable) {
                    firstFocusable.focus();
                }
            }
        }, 150);
    });
});
