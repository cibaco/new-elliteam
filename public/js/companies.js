/**
 * Companies Page - JavaScript
 */

(function() {
    'use strict';

    // Attendre le chargement du DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    function init() {
        setupFAQ();
        setupScrollAnimations();
        setupPricingCards();
        setupSmoothScroll();
        console.log('🏢 Page Entreprises chargée');
    }

    // ===========================
    // FAQ ACCORDION
    // ===========================
    function setupFAQ() {
        const faqItems = document.querySelectorAll('.faq-item');

        faqItems.forEach(function(item) {
            const question = item.querySelector('.faq-question');

            question.addEventListener('click', function() {
                // Fermer les autres items
                faqItems.forEach(function(otherItem) {
                    if (otherItem !== item && otherItem.classList.contains('active')) {
                        otherItem.classList.remove('active');
                    }
                });

                // Toggle l'item actuel
                item.classList.toggle('active');
            });
        });
    }

    // ===========================
    // ANIMATIONS AU SCROLL
    // ===========================
    function setupScrollAnimations() {
        const animatedElements = document.querySelectorAll(
            '.benefit-card, .step-item, .pricing-card, .testimonial-card, .stat-item'
        );

        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry, index) {
                if (entry.isIntersecting) {
                    setTimeout(function() {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }, index * 50);

                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        animatedElements.forEach(function(element) {
            element.style.opacity = '0';
            element.style.transform = 'translateY(30px)';
            element.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(element);
        });
    }

    // ===========================
    // PRICING CARDS INTERACTION
    // ===========================
    function setupPricingCards() {
        const pricingCards = document.querySelectorAll('.pricing-card');

        pricingCards.forEach(function(card) {
            const btn = card.querySelector('.btn-pricing');

            card.addEventListener('mouseenter', function() {
                if (!card.classList.contains('popular')) {
                    card.style.borderColor = 'var(--primary)';
                }
            });

            card.addEventListener('mouseleave', function() {
                if (!card.classList.contains('popular')) {
                    card.style.borderColor = 'transparent';
                }
            });

            // Tracking des clics sur les boutons (analytics)
            btn.addEventListener('click', function(e) {
                const planName = card.querySelector('.pricing-header h3').textContent;
                console.log('Plan sélectionné:', planName);

                // Ici vous pouvez ajouter votre code de tracking analytics
                // gtag('event', 'select_plan', { plan_name: planName });
            });
        });
    }

    // ===========================
    // SMOOTH SCROLL
    // ===========================
    function setupSmoothScroll() {
        document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
            anchor.addEventListener('click', function(e) {
                const href = this.getAttribute('href');

                // Ignorer les # vides
                if (href === '#' || href === '') {
                    return;
                }

                const target = document.querySelector(href);

                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });

                    // Mettre à jour l'URL sans recharger
                    history.pushState(null, null, href);
                }
            });
        });
    }

    // ===========================
    // COMPTEUR DE STATS (optionnel)
    // ===========================
    function setupStatsCounter() {
        const statItems = document.querySelectorAll('.stat-item h3');

        const observerOptions = {
            threshold: 0.5
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    animateCounter(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        statItems.forEach(function(stat) {
            observer.observe(stat);
        });
    }

    function animateCounter(element) {
        const target = element.textContent;
        const hasPlus = target.includes('+');
        const hasPercent = target.includes('%');
        const hasDays = target.includes('jours');

        let numericValue;
        let suffix = '';

        if (hasPercent) {
            numericValue = parseInt(target);
            suffix = '%';
        } else if (hasDays) {
            numericValue = parseInt(target);
            suffix = ' jours';
        } else {
            numericValue = parseInt(target.replace(/[^0-9]/g, ''));
            suffix = hasPlus ? '+' : '';

            if (target.includes(',')) {
                // Pour les grands nombres comme 10,000+
                numericValue = parseInt(target.replace(/[^0-9]/g, ''));
            }
        }

        const duration = 2000;
        const steps = 60;
        const increment = numericValue / steps;
        let current = 0;

        const timer = setInterval(function() {
            current += increment;

            if (current >= numericValue) {
                current = numericValue;
                clearInterval(timer);
            }

            if (target.includes(',')) {
                element.textContent = Math.floor(current).toLocaleString() + suffix;
            } else {
                element.textContent = Math.floor(current) + suffix;
            }
        }, duration / steps);
    }

    // Activer le compteur de stats
    setupStatsCounter();

    // ===========================
    // TRUST LOGOS ANIMATION
    // ===========================
    const trustLogos = document.querySelectorAll('.trust-logo');

    trustLogos.forEach(function(logo, index) {
        setTimeout(function() {
            logo.style.opacity = '0';
            logo.style.transform = 'scale(0.5)';
            logo.style.transition = 'all 0.5s ease';

            setTimeout(function() {
                logo.style.opacity = '1';
                logo.style.transform = 'scale(1)';
            }, 100);
        }, index * 100);
    });

    // ===========================
    // FORMULAIRE DEMO (si présent)
    // ===========================
    const demoButtons = document.querySelectorAll('a[href="#demo"]');

    demoButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();

            // Simuler l'ouverture d'un modal de démo
            alert('Fonctionnalité de demande de démo en cours de développement.\nContactez-nous à contact@techrecruit.fr');

            // Ici vous pouvez ouvrir un modal ou rediriger vers un formulaire
            // window.location.href = 'contact.html?subject=demo';
        });
    });

    // ===========================
    // HIGHLIGHT CTA AU SCROLL
    // ===========================
    let lastScrollTop = 0;
    const ctaSection = document.querySelector('.cta-section');

    if (ctaSection) {
        window.addEventListener('scroll', function() {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            const windowHeight = window.innerHeight;
            const documentHeight = document.documentElement.scrollHeight;

            // Si proche du bas de la page
            if (scrollTop + windowHeight > documentHeight - 300) {
                ctaSection.style.position = 'sticky';
                ctaSection.style.bottom = '0';
            } else {
                ctaSection.style.position = 'relative';
            }

            lastScrollTop = scrollTop;
        }, false);
    }

})();