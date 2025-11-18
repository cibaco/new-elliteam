/**
 * Jobs Page - JavaScript
 */

(function() {
    'use strict';

    // État de l'application
    let state = {
        filters: {
            contract: [],
            experience: [],
            workmode: [],
            category: [],
            size: [],
            salaryMin: 30,
            salaryMax: 80
        },
        search: {
            job: '',
            location: ''
        },
        sort: 'recent',
        view: 'list'
    };

    // Attendre le chargement du DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    function init() {
        setupSearch();
        setupQuickFilters();
        setupFilters();
        setupSalaryRange();
        setupSort();
        setupViewMode();
        setupJobCards();
        setupPagination();
        console.log('💼 Page Offres chargée');
    }

    // ===========================
    // SEARCH
    // ===========================
    function setupSearch() {
        const searchBtn = document.querySelector('.btn-search-main');
        const jobInput = document.getElementById('searchJob');
        const locationInput = document.getElementById('searchLocation');

        if (!searchBtn) return;

        searchBtn.addEventListener('click', function() {
            state.search.job = jobInput.value;
            state.search.location = locationInput.value;

            console.log('Recherche:', state.search);
            applyFilters();
        });

        // Enter key
        [jobInput, locationInput].forEach(function(input) {
            if (input) {
                input.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        searchBtn.click();
                    }
                });
            }
        });
    }

    // ===========================
    // QUICK FILTERS
    // ===========================
    function setupQuickFilters() {
        const quickFilters = document.querySelectorAll('.quick-filter');

        quickFilters.forEach(function(filter) {
            filter.addEventListener('click', function() {
                // Retirer active de tous
                quickFilters.forEach(function(f) {
                    f.classList.remove('active');
                });

                // Ajouter active à celui cliqué
                this.classList.add('active');

                const filterType = this.dataset.filter;
                console.log('Quick filter:', filterType);

                // Appliquer le filtre
                applyQuickFilter(filterType);
            });
        });
    }

    function applyQuickFilter(type) {
        // Réinitialiser les filtres
        const checkboxes = document.querySelectorAll('.filter-checkbox input[type="checkbox"]');
        checkboxes.forEach(function(cb) {
            cb.checked = false;
        });

        // Appliquer le filtre rapide
        switch(type) {
            case 'remote':
                document.querySelector('input[value="remote"]').checked = true;
                break;
            case 'hybrid':
                document.querySelector('input[value="hybrid"]').checked = true;
                break;
            case 'urgent':
                // Filtrer les cartes avec badge urgent
                filterUrgentJobs();
                return;
            case 'featured':
                // Filtrer les cartes featured
                filterFeaturedJobs();
                return;
        }

        applyFilters();
    }

    function filterUrgentJobs() {
        const jobCards = document.querySelectorAll('.job-card');
        let count = 0;

        jobCards.forEach(function(card) {
            const hasUrgent = card.querySelector('.badge-urgent');
            if (hasUrgent) {
                card.style.display = 'block';
                count++;
            } else {
                card.style.display = 'none';
            }
        });

        updateJobCount(count);
    }

    function filterFeaturedJobs() {
        const jobCards = document.querySelectorAll('.job-card');
        let count = 0;

        jobCards.forEach(function(card) {
            if (card.classList.contains('featured')) {
                card.style.display = 'block';
                count++;
            } else {
                card.style.display = 'none';
            }
        });

        updateJobCount(count);
    }

    // ===========================
    // FILTERS SIDEBAR
    // ===========================
    function setupFilters() {
        const checkboxes = document.querySelectorAll('.filter-checkbox input[type="checkbox"]');
        const resetBtn = document.querySelector('.btn-reset-filters');

        checkboxes.forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                const name = this.name;
                const value = this.value;

                if (this.checked) {
                    if (!state.filters[name].includes(value)) {
                        state.filters[name].push(value);
                    }
                } else {
                    state.filters[name] = state.filters[name].filter(function(v) {
                        return v !== value;
                    });
                }

                console.log('Filters:', state.filters);
                applyFilters();
            });
        });

        // Reset filters
        if (resetBtn) {
            resetBtn.addEventListener('click', function() {
                // Décocher toutes les checkboxes
                checkboxes.forEach(function(cb) {
                    cb.checked = false;
                });

                // Réinitialiser l'état
                state.filters = {
                    contract: [],
                    experience: [],
                    workmode: [],
                    category: [],
                    size: [],
                    salaryMin: 30,
                    salaryMax: 80
                };

                // Reset salary range
                document.getElementById('salaryMin').value = 30;
                document.getElementById('salaryMax').value = 80;
                document.getElementById('salaryMinValue').textContent = '30K€';
                document.getElementById('salaryMaxValue').textContent = '80K€';

                applyFilters();
            });
        }
    }

    // ===========================
    // SALARY RANGE
    // ===========================
    function setupSalaryRange() {
        const salaryMin = document.getElementById('salaryMin');
        const salaryMax = document.getElementById('salaryMax');
        const salaryMinValue = document.getElementById('salaryMinValue');
        const salaryMaxValue = document.getElementById('salaryMaxValue');

        if (!salaryMin || !salaryMax) return;

        salaryMin.addEventListener('input', function() {
            let min = parseInt(this.value);
            let max = parseInt(salaryMax.value);

            if (min > max) {
                min = max;
                this.value = min;
            }

            salaryMinValue.textContent = min + 'K€';
            state.filters.salaryMin = min;
            applyFilters();
        });

        salaryMax.addEventListener('input', function() {
            let max = parseInt(this.value);
            let min = parseInt(salaryMin.value);

            if (max < min) {
                max = min;
                this.value = max;
            }

            salaryMaxValue.textContent = max + 'K€';
            state.filters.salaryMax = max;
            applyFilters();
        });
    }

    // ===========================
    // SORT
    // ===========================
    function setupSort() {
        const sortSelect = document.getElementById('sortBy');

        if (!sortSelect) return;

        sortSelect.addEventListener('change', function() {
            state.sort = this.value;
            console.log('Sort by:', state.sort);
            applySorting();
        });
    }

    function applySorting() {
        const jobsList = document.getElementById('jobsList');
        const jobCards = Array.from(jobsList.querySelectorAll('.job-card'));

        jobCards.sort(function(a, b) {
            switch(state.sort) {
                case 'recent':
                    // Simuler tri par date
                    return 0;
                case 'relevant':
                    // Featured en premier
                    if (a.classList.contains('featured') && !b.classList.contains('featured')) {
                        return -1;
                    }
                    if (!a.classList.contains('featured') && b.classList.contains('featured')) {
                        return 1;
                    }
                    return 0;
                case 'salary-high':
                case 'salary-low':
                    const salaryA = extractSalary(a);
                    const salaryB = extractSalary(b);
                    return state.sort === 'salary-high' ? salaryB - salaryA : salaryA - salaryB;
                default:
                    return 0;
            }
        });

        // Réorganiser le DOM
        jobCards.forEach(function(card) {
            jobsList.appendChild(card);
        });
    }

    function extractSalary(card) {
        const salaryText = card.querySelector('.job-salary span').textContent;
        const match = salaryText.match(/(\d+)/);
        return match ? parseInt(match[1]) : 0;
    }

    // ===========================
    // VIEW MODE
    // ===========================
    function setupViewMode() {
        const viewBtns = document.querySelectorAll('.btn-view-mode');
        const jobsList = document.getElementById('jobsList');

        viewBtns.forEach(function(btn) {
            btn.addEventListener('click', function() {
                viewBtns.forEach(function(b) {
                    b.classList.remove('active');
                });

                this.classList.add('active');
                const view = this.dataset.view;
                state.view = view;

                if (view === 'grid') {
                    jobsList.style.display = 'grid';
                    jobsList.style.gridTemplateColumns = 'repeat(auto-fill, minmax(400px, 1fr))';
                } else {
                    jobsList.style.display = 'flex';
                    jobsList.style.flexDirection = 'column';
                }
            });
        });
    }

    // ===========================
    // JOB CARDS
    // ===========================
    function setupJobCards() {
        // Save buttons
        const saveBtns = document.querySelectorAll('.btn-save');

        saveBtns.forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                this.classList.toggle('saved');

                const icon = this.querySelector('i');
                if (this.classList.contains('saved')) {
                    icon.classList.remove('far');
                    icon.classList.add('fas');
                    console.log('Job saved');
                } else {
                    icon.classList.remove('fas');
                    icon.classList.add('far');
                    console.log('Job unsaved');
                }
            });
        });

        // Apply buttons
        const applyBtns = document.querySelectorAll('.btn-apply');

        applyBtns.forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const card = this.closest('.job-card');
                const jobTitle = card.querySelector('.job-title').textContent;
                console.log('Apply to:', jobTitle);

                alert(`Candidature pour le poste:\n${jobTitle}\n\nFonctionnalité en cours de développement.`);
            });
        });

        // Details buttons
        const detailsBtns = document.querySelectorAll('.btn-details');

        detailsBtns.forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const card = this.closest('.job-card');
                const jobTitle = card.querySelector('.job-title').textContent;
                console.log('View details:', jobTitle);

                // Redirection vers page détails (à implémenter)
                // window.location.href = '/job/' + jobId;
            });
        });

        // Tags click
        const tags = document.querySelectorAll('.tag');

        tags.forEach(function(tag) {
            tag.addEventListener('click', function(e) {
                e.stopPropagation();
                const skill = this.textContent;
                console.log('Filter by skill:', skill);

                // Remplir la recherche avec le skill
                const searchInput = document.getElementById('searchJob');
                if (searchInput) {
                    searchInput.value = skill;
                    document.querySelector('.btn-search-main').click();
                }
            });
        });
    }

    // ===========================
    // PAGINATION
    // ===========================
    function setupPagination() {
        const paginationBtns = document.querySelectorAll('.pagination-number');
        const prevBtn = document.querySelectorAll('.pagination-btn')[0];
        const nextBtn = document.querySelectorAll('.pagination-btn')[1];

        paginationBtns.forEach(function(btn) {
            btn.addEventListener('click', function() {
                paginationBtns.forEach(function(b) {
                    b.classList.remove('active');
                });

                this.classList.add('active');
                const page = this.textContent;
                console.log('Go to page:', page);

                // Scroll to top
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        });

        if (prevBtn) {
            prevBtn.addEventListener('click', function() {
                console.log('Previous page');
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', function() {
                console.log('Next page');
            });
        }
    }

    // ===========================
    // APPLY FILTERS
    // ===========================
    function applyFilters() {
        const jobCards = document.querySelectorAll('.job-card');
        let count = 0;

        jobCards.forEach(function(card) {
            let show = true;

            // Vérifier chaque filtre
            // (Logique simplifiée - à adapter selon vos besoins)

            if (show) {
                card.style.display = 'block';
                count++;
            } else {
                card.style.display = 'none';
            }
        });

        updateJobCount(count);
    }

    function updateJobCount(count) {
        const jobCountEl = document.getElementById('jobCount');
        if (jobCountEl) {
            jobCountEl.textContent = count.toLocaleString();
        }
    }

})();