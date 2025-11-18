// ===========================
// CONFIGURATION
// ===========================
'use strict';

// Attendre que le DOM soit chargé
document.addEventListener('DOMContentLoaded', function() {
    initMobileMenu();
    initSmoothScroll();
    initHeaderScroll();
    initActiveNavigation();
    initJobCards();
    initSearchBar();
    initApplyButtons();
    initScrollAnimations();
    initStatsCounter();
    initPopularTags();
    logWelcomeMessage();
});

// ===========================
// MOBILE MENU
// ===========================
function initMobileMenu() {
    const toggle = document.querySelector('.mobile-menu-toggle');
    const menu = document.querySelector('.nav-menu');

    if (!toggle || !menu) return;

    toggle.addEventListener('click', function() {
        menu.classList.toggle('active');
        toggle.classList.toggle('active');

        // Animation des barres du menu burger
        const spans = toggle.querySelectorAll('span');
        if (toggle.classList.contains('active')) {
            spans[0].style.transform = 'rotate(45deg) translateY(10px)';
            spans[1].style.opacity = '0';
            spans[2].style.transform = 'rotate(-45deg) translateY(-10px)';
        } else {
            spans[0].style.transform = 'none';
            spans[1].style.opacity = '1';
            spans[2].style.transform = 'none';
        }
    });

    // Fermer le menu au clic sur un lien
    const navLinks = menu.querySelectorAll('a');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            menu.classList.remove('active');
            toggle.classList.remove('active');
        });
    });
}

// ===========================
// SMOOTH SCROLL
// ===========================
function initSmoothScroll() {
    const links = document.querySelectorAll('a[href^="#"]');

    links.forEach(link => {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');

            if (href === '#' || href === '#!') return;

            const target = document.querySelector(href);
            if (!target) return;

            e.preventDefault();

            const headerOffset = 100;
            const elementPosition = target.getBoundingClientRect().top;
            const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

            window.scrollTo({
                top: offsetPosition,
                behavior: 'smooth'
            });
        });
    });
}

// ===========================
// HEADER SCROLL EFFECT
// ===========================
function initHeaderScroll() {
    const header = document.querySelector('.header');
    if (!header) return;

    let lastScroll = 0;

    window.addEventListener('scroll', function() {
        const currentScroll = window.pageYOffset;

        if (currentScroll > 50) {
            header.style.boxShadow = '0 4px 20px rgba(0, 0, 0, 0.08)';
        } else {
            header.style.boxShadow = '0 1px 3px rgba(0, 0, 0, 0.05)';
        }

        lastScroll = currentScroll;
    });
}

// ===========================
// ACTIVE NAVIGATION
// ===========================
function initActiveNavigation() {
    const sections = document.querySelectorAll('section[id]');
    const navLinks = document.querySelectorAll('.nav-menu a[href^="#"]');

    if (sections.length === 0 || navLinks.length === 0) return;

    function updateActiveNav() {
        const scrollY = window.pageYOffset;

        sections.forEach(section => {
            const sectionHeight = section.offsetHeight;
            const sectionTop = section.offsetTop - 200;
            const sectionId = section.getAttribute('id');

            if (scrollY > sectionTop && scrollY <= sectionTop + sectionHeight) {
                navLinks.forEach(link => {
                    link.classList.remove('active');
                    if (link.getAttribute('href') === '#' + sectionId) {
                        link.classList.add('active');
                    }
                });
            }
        });
    }

    window.addEventListener('scroll', updateActiveNav);
    updateActiveNav(); // Initial call
}

// ===========================
// JOB CARDS HOVER
// ===========================
function initJobCards() {
    const jobCards = document.querySelectorAll('.job-card');

    jobCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-8px)';
        });

        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
}

// ===========================
// SEARCH BAR
// ===========================
function initSearchBar() {
    const searchButton = document.querySelector('.btn-search');
    const searchInputs = document.querySelectorAll('.search-input-group input');

    if (!searchButton || searchInputs.length === 0) return;

    searchButton.addEventListener('click', function(e) {
        e.preventDefault();

        const jobTitle = searchInputs[0].value.trim();
        const location = searchInputs[1].value.trim();

        if (jobTitle || location) {
            console.log('Recherche:', { jobTitle, location });

            // Animation du bouton
            searchButton.innerHTML = '<i class="fas fa-check"></i> Recherche...';
            searchButton.style.background = '#10B981';

            // Simuler une recherche
            setTimeout(function() {
                searchButton.innerHTML = 'Rechercher <i class="fas fa-arrow-right"></i>';
                searchButton.style.background = '';

                // Scroll vers les offres
                const jobsSection = document.querySelector('#jobs');
                if (jobsSection) {
                    jobsSection.scrollIntoView({ behavior: 'smooth' });
                }
            }, 1500);
        } else {
            // Secouer le bouton si vide
            searchButton.style.animation = 'shake 0.5s';
            setTimeout(function() {
                searchButton.style.animation = '';
            }, 500);
        }
    });

    // Enter key
    searchInputs.forEach(input => {
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchButton.click();
            }
        });
    });
}

// ===========================
// APPLY BUTTONS
// ===========================
function initApplyButtons() {
    const applyButtons = document.querySelectorAll('.btn-apply');

    applyButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();

            // Animation
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.style.transform = 'scale(1)';
            }, 150);

            // Feedback
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-check"></i> Envoyée';
            this.style.background = '#10B981';

            setTimeout(() => {
                this.innerHTML = originalText;
                this.style.background = '';
            }, 2500);
        });
    });
}

// ===========================
// SCROLL ANIMATIONS
// ===========================
function initScrollAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -80px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    // Elements à animer
    const animatedElements = document.querySelectorAll(
        '.job-card, .category-card, .step-card, .stat-item, .company-card'
    );

    animatedElements.forEach((el, index) => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(40px)';
        el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        el.style.transitionDelay = (index * 0.1) + 's';
        observer.observe(el);
    });
}

// ===========================
// STATS COUNTER
// ===========================
function initStatsCounter() {
    const statsSection = document.querySelector('.stats');
    if (!statsSection) return;

    let hasAnimated = false;

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting && !hasAnimated) {
                hasAnimated = true;
                animateCounters();
            }
        });
    }, { threshold: 0.5 });

    observer.observe(statsSection);

    function animateCounters() {
        const statItems = document.querySelectorAll('.stat-item h3');

        statItems.forEach((item, index) => {
            const text = item.textContent;
            const isPercentage = text.includes('%');
            const hasPlus = text.includes('+');
            const number = parseInt(text.replace(/[^0-9]/g, ''));

            if (isNaN(number)) return;

            let current = 0;
            const increment = number / 50;
            const duration = 2000;
            const stepTime = duration / 50;

            const timer = setInterval(function() {
                current += increment;

                if (current >= number) {
                    current = number;
                    clearInterval(timer);
                }

                let displayValue = Math.floor(current);

                if (number >= 1000) {
                    displayValue = (displayValue / 1000).toFixed(0) + 'K';
                }

                if (hasPlus) displayValue += '+';
                if (isPercentage) displayValue += '%';

                item.textContent = displayValue;
            }, stepTime);
        });
    }
}

// ===========================
// POPULAR TAGS
// ===========================
function initPopularTags() {
    const tags = document.querySelectorAll('.popular-searches .tag');
    const searchInput = document.querySelector('.search-input-group input');

    if (tags.length === 0 || !searchInput) return;

    tags.forEach(tag => {
        tag.addEventListener('click', function(e) {
            e.preventDefault();

            const searchText = this.textContent.trim();
            searchInput.value = searchText;

            // Animation
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.style.transform = 'scale(1)';
            }, 150);

            // Focus sur le champ
            searchInput.focus();

            // Scroll vers les offres après un délai
            setTimeout(function() {
                const jobsSection = document.querySelector('#jobs');
                if (jobsSection) {
                    jobsSection.scrollIntoView({ behavior: 'smooth' });
                }
            }, 300);
        });
    });
}

// ===========================
// CATEGORY CARDS
// ===========================
function initCategoryCards() {
    const categoryCards = document.querySelectorAll('.category-card');

    categoryCards.forEach(card => {
        card.addEventListener('click', function(e) {
            if (e.target.classList.contains('category-link')) return;

            const link = this.querySelector('.category-link');
            if (link) link.click();
        });
    });
}

// ===========================
// FORM VALIDATION
// ===========================
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// ===========================
// UTILITIES
// ===========================
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

// ===========================
// CONSOLE MESSAGE
// ===========================
function logWelcomeMessage() {
    console.log(
        '%c🚀 TechRecruit',
        'color: #4F46E5; font-size: 24px; font-weight: bold;'
    );
    console.log(
        '%cPlateforme de recrutement IT',
        'color: #6B7280; font-size: 14px;'
    );
}

// ===========================
// PERFORMANCE MONITORING
// ===========================
window.addEventListener('load', function() {
    if (window.performance) {
        const perfData = window.performance.timing;
        const loadTime = perfData.loadEventEnd - perfData.navigationStart;
        console.log('⚡ Page chargée en ' + loadTime + 'ms');
    }
});

// ===========================
// ERROR HANDLING
// ===========================
window.addEventListener('error', function(e) {
    console.error('Une erreur est survenue:', e.message);
});

// ===========================
// SHAKE ANIMATION (CSS)
// ===========================
const style = document.createElement('style');
style.textContent = `
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
        20%, 40%, 60%, 80% { transform: translateX(5px); }
    }
`;
document.head.appendChild(style);