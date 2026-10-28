document.addEventListener('DOMContentLoaded', () => {
    const pageTitle = document.querySelector('.page-title');
    const topbarLeft = document.querySelector('.topbar-left');
    if (pageTitle && topbarLeft && pageTitle.textContent.trim() !== 'DASHBOARD OVERVIEW' && !document.body.classList.contains('service-page') && !document.body.classList.contains('inventory-page') && !document.body.classList.contains('analytics-page')) {
        const title = pageTitle.textContent.trim().toLowerCase().replace(/\b\w/g, (letter) => letter.toUpperCase());
        const subtitle = document.querySelector('.page-date')?.textContent.trim() || 'Manage your spa operations.';
        topbarLeft.innerHTML = `<div class="topbar-page-title">${title}</div><div class="topbar-page-subtitle">${subtitle}</div>`;
    }

    const sidebarMenu = document.querySelector('.sidebar-menu');
    if (sidebarMenu) {
        const linksByRoute = new Map();

        sidebarMenu.querySelectorAll('.nav-item').forEach((link) => {
            const route = new URL(link.href, window.location.origin).searchParams.get('route');
            if (route && !linksByRoute.has(route)) {
                linksByRoute.set(route, link);
            }
        });

        const currentRole = (document.body.dataset.role || document.querySelector('.user-role')?.textContent || '').trim().toLowerCase();
        const isManager = currentRole === 'manager';
        document.body.classList.toggle('manager-role', isManager);

        if (isManager && !document.querySelector('.sidebar-role')) {
            const userInitials = document.querySelector('.user-name')?.textContent.trim() || 'M';
            const roleFooter = document.createElement('div');
            roleFooter.className = 'sidebar-role';
            const footerLabel = document.createElement('span');
            footerLabel.textContent = 'Current Role';
            const roleSelect = document.createElement('div');
            roleSelect.className = 'role-select';
            const avatar = document.createElement('span');
            avatar.className = 'role-avatar';
            avatar.textContent = userInitials;
            const roleName = document.createElement('span');
            roleName.textContent = 'Manager';
            const chevron = document.createElement('span');
            chevron.className = 'chevron';
            chevron.textContent = '▾';
            roleSelect.append(avatar, roleName, chevron);
            roleFooter.append(footerLabel, roleSelect);
            sidebarMenu.parentElement?.append(roleFooter);
        }

        if (isManager) {
            const dashboardUrl = linksByRoute.get('dashboard')?.href;
            if (dashboardUrl) {
                const addManagerLink = (route, label, icon) => {
                    if (linksByRoute.has(route)) return;
                    const link = document.createElement('a');
                    link.className = 'nav-item';
                    link.href = `${dashboardUrl.split('?')[0]}?route=${route}`;
                    link.innerHTML = `<span class="nav-icon">${icon}</span><span>${label}</span>`;
                    if (new URL(window.location.href).searchParams.get('route') === route) {
                        link.classList.add('active');
                    }
                    linksByRoute.set(route, link);
                };
                addManagerLink('calendar', 'Calendar', '▦');
                addManagerLink('pos', 'POS & Billing', '▤');
            }

            const managerGroups = [
                { label: 'Main', routes: ['dashboard', 'appointments', 'customers', 'calendar', 'rooms'] },
                { label: 'Quick Actions', routes: ['services', 'pos', 'products'] },
                { label: 'Reports', routes: ['reports'] },
                { label: 'Settings', routes: ['users-and-roles', 'settings'] },
            ];
            const managerLabels = {
                services: 'Services',
                products: 'Inventory',
            };
            Object.entries(managerLabels).forEach(([route, label]) => {
                const link = linksByRoute.get(route);
                const text = link?.querySelector('span:last-child');
                if (text) text.textContent = label;
            });

            sidebarMenu.replaceChildren();
            managerGroups.forEach((group) => {
                const section = document.createElement('div');
                section.className = 'nav-section';
                section.dataset.label = group.label;

                group.routes.forEach((route) => {
                    const link = linksByRoute.get(route);
                    if (link) section.append(link);
                });

                if (section.children.length > 0) sidebarMenu.append(section);
            });
        } else {
            const adminGroups = [
                ['dashboard', 'appointments', 'services', 'therapists', 'rooms'],
                ['customers', 'memberships', 'customer-history'],
                ['products', 'stock-management', 'suppliers'],
                ['reports'],
                ['users-and-roles', 'settings'],
            ];

            sidebarMenu.replaceChildren();
            adminGroups.forEach((routes) => {
                const section = document.createElement('div');
                section.className = 'nav-section';
                routes.forEach((route) => {
                    const link = linksByRoute.get(route);
                    if (link) section.append(link);
                });
                if (section.children.length > 0) sidebarMenu.append(section);
            });
        }
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

    document.querySelectorAll('[data-password-toggle]').forEach((toggle) => {
        const password = toggle.closest('.password-wrap')?.querySelector('input[type="password"], input[type="text"]');
        const visibleIcon = toggle.querySelector('[data-password-visible]');
        const hiddenIcon = toggle.querySelector('[data-password-hidden]');
        if (!password) return;

        toggle.addEventListener('click', () => {
            const showing = password.type === 'text';
            password.type = showing ? 'password' : 'text';
            toggle.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
            toggle.setAttribute('aria-pressed', showing ? 'false' : 'true');
            if (visibleIcon) visibleIcon.hidden = !showing;
            if (hiddenIcon) hiddenIcon.hidden = showing;
        });
    });

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
            } else if (!/^[A-Za-z0-9@_.-]+$/.test(username.value.trim())) {
                username?.setCustomValidity('Username may contain letters, numbers, @, dots, underscores, and hyphens only.');
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

    const profilePhotoInput = document.getElementById('user-profile-photo-input');
    const profilePhotoImage = document.getElementById('user-profile-photo-image');
    const profilePhotoInitials = document.getElementById('user-profile-photo-initials');
    const profilePhotoFilename = document.getElementById('user-profile-photo-filename');
    let profilePhotoObjectUrl = '';
    profilePhotoInput?.addEventListener('change', () => {
        const file = profilePhotoInput.files?.[0];
        if (!file || !profilePhotoImage || !profilePhotoInitials || !profilePhotoFilename) return;

        if (profilePhotoObjectUrl) URL.revokeObjectURL(profilePhotoObjectUrl);
        profilePhotoObjectUrl = URL.createObjectURL(file);
        profilePhotoImage.src = profilePhotoObjectUrl;
        profilePhotoImage.hidden = false;
        profilePhotoInitials.hidden = true;
        profilePhotoFilename.textContent = file.name;
    });

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

    const serviceSearch = document.getElementById('service-search');
    const serviceCategory = document.getElementById('service-category');
    const serviceCards = [...document.querySelectorAll('#service-grid .service-card')];
    const noServiceResults = document.getElementById('service-no-results');
    const filterServices = () => {
        if (!serviceSearch || !serviceCategory) return;
        const query = serviceSearch.value.trim().toLowerCase();
        const category = serviceCategory.value;
        let visibleCount = 0;
        serviceCards.forEach((card) => {
            const matchesQuery = (card.dataset.search || '').includes(query);
            const matchesCategory = category === 'all' || card.dataset.category === category;
            const visible = matchesQuery && matchesCategory;
            card.hidden = !visible;
            if (visible) visibleCount += 1;
        });
        if (noServiceResults) noServiceResults.hidden = visibleCount > 0;
    };
    serviceSearch?.addEventListener('input', filterServices);
    serviceCategory?.addEventListener('change', filterServices);

    const inventorySearch = document.getElementById('inventory-search');
    const inventoryStatus = document.getElementById('inventory-status');
    const inventoryRows = [...document.querySelectorAll('#inventory-table tbody tr')];
    const inventoryEmpty = document.getElementById('inventory-empty');
    const filterInventory = () => {
        if (!inventorySearch || !inventoryStatus) return;
        const query = inventorySearch.value.trim().toLowerCase();
        const status = inventoryStatus.value;
        let visibleCount = 0;
        inventoryRows.forEach((row) => {
            const matchesQuery = (row.dataset.search || '').includes(query);
            const matchesStatus = status === 'all' || row.dataset.status === status;
            const visible = matchesQuery && matchesStatus;
            row.hidden = !visible;
            if (visible) visibleCount += 1;
        });
        if (inventoryEmpty) inventoryEmpty.hidden = visibleCount > 0;
    };
    inventorySearch?.addEventListener('input', filterInventory);
    inventoryStatus?.addEventListener('change', filterInventory);

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
