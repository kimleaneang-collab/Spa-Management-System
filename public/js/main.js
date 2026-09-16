document.addEventListener('DOMContentLoaded', () => {
    const pageTitle = document.querySelector('.page-title');
    const topbarLeft = document.querySelector('.topbar-left');
    if (pageTitle && topbarLeft && pageTitle.textContent.trim() !== 'DASHBOARD OVERVIEW') {
        const title = pageTitle.textContent.trim().toLowerCase().replace(/\b\w/g, (letter) => letter.toUpperCase());
        const subtitle = document.querySelector('.page-date')?.textContent.trim() || 'Manage your spa operations.';
        topbarLeft.innerHTML = `<div class="topbar-page-title">${title}</div><div class="topbar-page-subtitle">${subtitle}</div>`;
    }

    const sidebarMenu = document.querySelector('.sidebar-menu');
    if (sidebarMenu) {
        const sidebarGroups = [
            ['dashboard', 'appointments', 'services', 'therapists', 'rooms'],
            ['customers', 'memberships', 'customer-history'],
            ['products', 'stock-management', 'suppliers'],
            ['reports'],
            ['users-and-roles', 'settings'],
        ];
        const linksByRoute = new Map();

        sidebarMenu.querySelectorAll('.nav-item').forEach((link) => {
            const route = new URL(link.href, window.location.origin).searchParams.get('route');
            if (route && !linksByRoute.has(route)) {
                linksByRoute.set(route, link);
            }
        });

        sidebarMenu.replaceChildren();
        sidebarGroups.forEach((routes) => {
            const section = document.createElement('div');
            section.className = 'nav-section';

            routes.forEach((route) => {
                const link = linksByRoute.get(route);
                if (link) {
                    section.append(link);
                }
            });

            if (section.children.length > 0) {
                sidebarMenu.append(section);
            }
        });
    }

    const routeSearchItems = [
        ...document.querySelectorAll('.nav-item')
    ].map((link) => ({
        label: link.textContent.trim(),
        href: link.href,
    })).filter((item, index, items) => (
        item.label && items.findIndex((candidate) => candidate.href === item.href) === index
    ));

    const attachRouteSearch = (searchBox) => {
        let input = searchBox.querySelector('input');

        if (!input) {
            input = document.createElement('input');
            input.type = 'search';
            input.className = 'search-input';
            input.placeholder = 'Search pages';
            input.setAttribute('aria-label', 'Search pages');
            searchBox.querySelectorAll('span:not(.search-icon)').forEach((label) => label.remove());
            searchBox.append(input);
        }

        searchBox.classList.add('search-input-wrap');
        const results = document.createElement('div');
        results.className = 'search-results';
        results.setAttribute('role', 'listbox');
        searchBox.append(results);

        const closeResults = () => {
            results.classList.remove('visible');
            results.replaceChildren();
        };

        const renderResults = () => {
            const query = input.value.trim().toLowerCase();
            const matches = routeSearchItems.filter((item) => (
                query === '' || item.label.toLowerCase().includes(query)
            ));

            results.replaceChildren();
            matches.forEach((item) => {
                const result = document.createElement('a');
                result.className = 'search-result';
                result.href = item.href;
                result.setAttribute('role', 'option');
                result.textContent = item.label;
                results.append(result);
            });
            results.classList.toggle('visible', matches.length > 0);
        };

        input.addEventListener('focus', renderResults);
        input.addEventListener('input', renderResults);
        input.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeResults();
                input.blur();
            }

            if (event.key === 'Enter') {
                const firstResult = results.querySelector('.search-result');
                if (firstResult) {
                    event.preventDefault();
                    window.location.href = firstResult.href;
                }
            }
        });

        document.addEventListener('click', (event) => {
            if (!searchBox.contains(event.target)) {
                closeResults();
            }
        });
    };

    document.querySelectorAll('.search-box').forEach(attachRouteSearch);

    const toggle = document.querySelector('[data-password-toggle]');
    const password = document.querySelector('#password');

    if (toggle && password) {
        toggle.addEventListener('click', () => {
            const showing = password.type === 'text';
            password.type = showing ? 'password' : 'text';
            toggle.textContent = showing ? 'Show' : 'Hide';
            toggle.setAttribute(
                'aria-label',
                showing ? 'Show password' : 'Hide password'
            );
        });
    }

    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', (event) => {
            const username = document.querySelector('#username');
            const password = document.querySelector('#password');

            username?.setCustomValidity('');
            password?.setCustomValidity('');

            if (!username?.value.trim()) {
                username?.setCustomValidity('Username is required.');
            } else if (username.value.trim().length < 3) {
                username?.setCustomValidity('Username must be at least 3 characters.');
            } else if (!/^[A-Za-z0-9_.-]+$/.test(username.value.trim())) {
                username?.setCustomValidity('Username may contain letters, numbers, dots, underscores, and hyphens only.');
            }

            if (!password?.value) {
                password?.setCustomValidity('Password is required.');
            }

            if (!form.checkValidity()) {
                event.preventDefault();
                form.reportValidity();
            }
        });
    }

    const openModalButtons = document.querySelectorAll('[data-open-modal]');
    const closeModalButtons = document.querySelectorAll('[data-close-modal]');

    openModalButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const modalId = button.getAttribute('data-open-modal');
            const modal = document.getElementById(modalId);
            if (!modal) return;

            modal.classList.remove('hidden');
            modal.setAttribute('aria-hidden', 'false');
        });
    });

    closeModalButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const modalId = button.getAttribute('data-close-modal');
            const modal = document.getElementById(modalId);
            if (!modal) return;

            modal.classList.add('hidden');
            modal.setAttribute('aria-hidden', 'true');
        });
    });

    const modalBackdrop = document.querySelector('.modal-backdrop');
    if (modalBackdrop) {
        modalBackdrop.addEventListener('click', (event) => {
            if (event.target === modalBackdrop) {
                modalBackdrop.classList.add('hidden');
                modalBackdrop.setAttribute('aria-hidden', 'true');
            }
        });
    }

    const searchInputs = document.querySelectorAll('.search-input');
    searchInputs.forEach((input) => {
        input.addEventListener('input', () => {
            const query = input.value.trim().toLowerCase();
            const table = input.closest('.main-panel')?.querySelector('.searchable-table');

            if (!table) {
                return;
            }

            const rows = table.querySelectorAll('tbody tr');
            rows.forEach((row) => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(query) ? '' : 'none';
            });
        });
    });

    const filterPills = document.querySelectorAll('.pill');
    filterPills.forEach((pill) => {
        pill.addEventListener('click', () => {
            filterPills.forEach((item) => item.classList.remove('active'));
            pill.classList.add('active');
        });
    });
});
