/**
 * About Page - JavaScript
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
        setupCounters();
        setupScrollAnimations();
        setupTeamHover();
        console.log('📖 Page À propos chargée');
    }

    // ===========================
    // COMPTEURS ANIMÉS
    // ===========================
    function setupCounters() {
        const counters = document.querySelectorAll('.stat-detailed h3');

        const observerOptions = {
            threshold: 0.5,
            rootMargin: '0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    animateCounter(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        counters.forEach(function(counter) {
            observer.observe(counter);
        });
    }

    function animateCounter(element) {
        const target = element.textContent;
        const isPercentage = target.includes('%');
        const hasPlus = target.includes('+');
        const hasDays = target.includes('jours');
        const hasSlash = target.includes('/');

        let numericValue;
        let suffix = '';

        if (isPercentage) {
            numericValue = parseInt(target);
            suffix = '%';
        } else if (hasSlash) {
            // Pour 4.8/5
            numericValue = parseFloat(target);
            suffix = '/5';
        } else if (hasDays) {
            numericValue = parseInt(target);
            suffix = ' jours';
        } else {
            numericValue = parseInt(target.replace(/[^0-9]/g, ''));
            suffix = hasPlus ? '+' : '';

            // Ajouter K si nécessaire
            if (target.includes('K')) {
                suffix = 'K' + suffix;
                numericValue = parseInt(target.replace(/[^0-9]/g, '')) / 1000;
            }
        }

        const duration = 2000; // 2 secondes
        const steps = 60;
        const increment = numericValue / steps;
        let current = 0;

        const timer = setInterval(function() {
            current += increment;

            if (current >= numericValue) {
                current = numericValue;
                clearInterval(timer);
            }

            if (hasSlash) {
                element.textContent = current.toFixed(1) + suffix;
            } else if (suffix.includes('K')) {
                element.textContent = Math.floor(current * 1000).toLocaleString() + suffix;
            } else {
                element.textContent = Math.floor(current).toLocaleString() + suffix;
            }
        }, duration / steps);
    }

    // ===========================
    // ANIMATIONS AU SCROLL
    // ===========================
    function setupScrollAnimations() {
        const animatedElements = document.querySelectorAll(
            '.mission-card, .value-card, .team-member, .stat-detailed, .partner-logo, .timeline-item'
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
    // HOVER EFFET TEAM
    // ===========================
    function setupTeamHover() {
        const teamMembers = document.querySelectorAll('.team-member');

        teamMembers.forEach(function(member) {
            const image = member.querySelector('.member-image img');

            member.addEventListener('mouseenter', function() {
                image.style.transform = 'scale(1.1)';
                image.style.transition = 'transform 0.3s ease';
            });

            member.addEventListener('mouseleave', function() {
                image.style.transform = 'scale(1)';
            });
        });
    }

    // ===========================
    // SMOOTH SCROLL POUR LES ANCRES
    // ===========================
    document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');

            // Ignorer les # vides ou juste "#"
            if (href === '#' || href === '') {
                e.preventDefault();
                return;
            }

            const target = document.querySelector(href);

            if (target) {
                e.preventDefault();
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // ===========================
    // PARALLAX EFFECT (optionnel)
    // ===========================
    function setupParallax() {
        const heroImage = document.querySelector('.hero-image-about');

        if (!heroImage) return;

        window.addEventListener('scroll', function() {
            const scrolled = window.pageYOffset;
            const rate = scrolled * 0.3;

            if (heroImage) {
                heroImage.style.transform = 'translateY(' + rate + 'px)';
            }
        });
    }

    // Activer le parallax si souhaité
    // setupParallax();

})();