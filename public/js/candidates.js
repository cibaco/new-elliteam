/**
 * Candidates Page - JavaScript
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
        setupSearchBox();
        setupPopularTags();
        setupScrollAnimations();
        setupCategoryCards();
        setupResourceCards();
        console.log('👤 Page Candidats chargée');
    }

    // ===========================
    // SEARCH BOX
    // ===========================
    function setupSearchBox() {
        const searchBtn = document.querySelector('.btn-search-hero');
        const inputs = document.querySelectorAll('.search-input-wrapper input');

        if (!searchBtn) return;

        searchBtn.addEventListener('click', function() {
            const jobInput = inputs[0].value;
            const locationInput = inputs[1].value;

            if (!jobInput && !locationInput) {
                alert('Veuillez entrer au moins un critère de recherche');
                return;
            }

            // Simuler la recherche
            console.log('Recherche:', {
                job: jobInput,
                location: locationInput
            });

            // Redirection vers page de résultats (à adapter)
            // window.location.href = `/jobs?q=${jobInput}&location=${locationInput}`;

            alert(`Recherche lancée pour:\nPoste: ${jobInput || 'Tous'}\nLocalisation: ${locationInput || 'Toutes'}`);
        });

        // Enter key sur les inputs
        inputs.forEach(function(input) {
            input.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    searchBtn.click();
                }
            });
        });
    }

    // ===========================
    // POPULAR TAGS
    // ===========================
    function setupPopularTags() {
        const tags = document.querySelectorAll('.popular-tags .tag');

        tags.forEach(function(tag) {
            tag.addEventListener('click', function(e) {
                e.preventDefault();
                const skill = this.textContent;
                const jobInput = document.querySelector('.search-input-wrapper input');

                if (jobInput) {
                    jobInput.value = skill;
                    jobInput.focus();
                }

                console.log('Tag cliqué:', skill);
            });
        });
    }

    // ===========================
    // ANIMATIONS AU SCROLL
    // ===========================
    function setupScrollAnimations() {
        const animatedElements = document.querySelectorAll(
            '.advantage-card, .category-item, .step-card, .story-card, .resource-card'
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
    // CATEGORY CARDS
    // ===========================
    function setupCategoryCards() {
        const categoryCards = document.querySelectorAll('.category-item');

        categoryCards.forEach(function(card) {
            const link = card.querySelector('.category-link');

            link.addEventListener('click', function(e) {
                e.preventDefault();
                const categoryName = card.querySelector('h3').textContent;

                console.log('Catégorie sélectionnée:', categoryName);

                // Redirection vers la page de catégorie (à adapter)
                // window.location.href = `/jobs?category=${categoryName}`;

                alert(`Explorer la catégorie: ${categoryName}`);
            });
        });
    }

    // ===========================
    // RESOURCE CARDS
    // ===========================
    function setupResourceCards() {
        const resourceCards = document.querySelectorAll('.resource-card');

        resourceCards.forEach(function(card) {
            const link = card.querySelector('.resource-link');

            link.addEventListener('click', function(e) {
                e.preventDefault();
                const resourceTitle = card.querySelector('h4').textContent;

                console.log('Ressource cliquée:', resourceTitle);

                // Tracking analytics
                // gtag('event', 'resource_click', { resource_name: resourceTitle });
            });
        });
    }

    // ===========================
    // SALARY GUIDE DOWNLOAD
    // ===========================
    const downloadBtn = document.querySelector('.btn-download');

    if (downloadBtn) {
        downloadBtn.addEventListener('click', function(e) {
            e.preventDefault();

            // Simuler le téléchargement
            console.log('Téléchargement du guide des salaires');

            // Tracking
            // gtag('event', 'download', { file_name: 'salary_guide_2025' });

            // Afficher un message ou ouvrir un modal pour collecter l'email
            const email = prompt('Entrez votre email pour télécharger le guide des salaires :');

            if (email && validateEmail(email)) {
                alert('Merci ! Le guide des salaires vous a été envoyé par email.');

                // Ici, envoyer l'email au backend
                // sendGuideByEmail(email);
            } else if (email) {
                alert('Email invalide. Veuillez réessayer.');
            }
        });
    }

    function validateEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    // ===========================
    // FLOATING CARDS ANIMATION
    // ===========================
    const floatingCards = document.querySelectorAll('.hero-card');

    floatingCards.forEach(function(card, index) {
        // Ajouter un délai initial pour l'animation
        card.style.animationDelay = (index * 1.5) + 's';

        // Hover effect
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px) scale(1.05)';
        });

        card.addEventListener('mouseleave', function() {
            this.style.transform = '';
        });
    });

    // ===========================
    // STATS COUNTER
    // ===========================
    function setupStatsCounter() {
        const statsInline = document.querySelectorAll('.stat-inline strong');

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

        statsInline.forEach(function(stat) {
            observer.observe(stat);
        });
    }

    function animateCounter(element) {
        const target = element.textContent;
        const hasPlus = target.includes('+');
        const numericValue = parseInt(target.replace(/[^0-9]/g, ''));

        let suffix = hasPlus ? '+' : '';

        if (target.includes('K')) {
            suffix = 'K' + suffix;
        }

        const duration = 1500;
        const steps = 50;
        const increment = numericValue / steps;
        let current = 0;

        const timer = setInterval(function() {
            current += increment;

            if (current >= numericValue) {
                current = numericValue;
                clearInterval(timer);
            }

            element.textContent = Math.floor(current).toLocaleString() + suffix;
        }, duration / steps);
    }

    // Activer le compteur
    setupStatsCounter();

    // ===========================
    // SMOOTH SCROLL
    // ===========================
    document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');

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
            }
        });
    });

    // ===========================
    // STORY CARDS INTERACTION
    // ===========================
    const storyCards = document.querySelectorAll('.story-card');

    storyCards.forEach(function(card) {
        card.addEventListener('click', function() {
            const name = this.querySelector('h4').textContent;
            console.log('Story clicked:', name);

            // Ouvrir un modal avec plus de détails (à implémenter)
        });
    });

})();