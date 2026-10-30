    // Run the related action when the user interacts with this control.
document.addEventListener('DOMContentLoaded', () => {
    const themeKey = 'relax-spa-theme';
    const topbars = [...document.querySelectorAll('header.topbar')];
    const role = (document.body.dataset.role || document.querySelector('.user-role')?.textContent || '')
        .trim()
        .toLowerCase();
    const settingsRoutes = {
        admin: ['profile', 'My Profile'],
        manager: ['profile', 'My Profile'],
        cashier: ['profile', 'My Profile'],
        therapist: ['profile', 'My Profile'],
        staff: ['profile', 'My Profile'],
        receptionist: ['profile', 'My Profile'],
    };

    let activeTheme = 'light';
    try {
        activeTheme = window.localStorage.getItem(themeKey) === 'dark' ? 'dark' : 'light';
    } catch (error) {
        console.error('Unable to read the saved theme preference.', error);
    }

    const sunIcon = '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="4"></circle><path d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42"></path></svg>';
    const moonIcon = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.2 15.4A8.5 8.5 0 0 1 8.6 3.8 8.5 8.5 0 1 0 20.2 15.4Z"></path></svg>';
    const gearIcon = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 8.5a3.5 3.5 0 1 0 0 7 3.5 3.5 0 0 0 0-7Z"></path><path d="m19.4 15 .1.1a1.7 1.7 0 0 1-2.4 2.4l-.1-.1a1.7 1.7 0 0 0-2.9 1.2v.2a1.7 1.7 0 0 1-3.4 0v-.2a1.7 1.7 0 0 0-2.9-1.2l-.1.1a1.7 1.7 0 1 1-2.4-2.4l.1-.1a1.7 1.7 0 0 0-1.2-2.9H4a1.7 1.7 0 0 1 0-3.4h.2a1.7 1.7 0 0 0 1.2-2.9l-.1-.1a1.7 1.7 0 1 1 2.4-2.4l.1.1a1.7 1.7 0 0 0 2.9-1.2V2a1.7 1.7 0 0 1 3.4 0v.2a1.7 1.7 0 0 0 2.9 1.2l.1-.1a1.7 1.7 0 1 1 2.4 2.4l-.1.1a1.7 1.7 0 0 0 1.2 2.9h.2a1.7 1.7 0 0 1 0 3.4h-.2a1.7 1.7 0 0 0-1.2 2.9Z"></path></svg>';

    // Save the selected theme and refresh the page colors and buttons.
    const updateTheme = (theme) => {
        activeTheme = theme;
        document.documentElement.dataset.theme = theme;
        document.body.dataset.theme = theme;
        document.documentElement.style.colorScheme = theme;
        try {
            window.localStorage.setItem(themeKey, theme);
        } catch (error) {
            console.error('Unable to save the theme preference.', error);
        }

    // Switch the password field between hidden and visible.
        document.querySelectorAll('.app-theme-toggle').forEach((button) => {
            const nextTheme = theme === 'dark' ? 'light' : 'dark';
            button.innerHTML = theme === 'dark' ? sunIcon : moonIcon;
            button.setAttribute('aria-label', `Switch to ${nextTheme} mode`);
            button.title = `Switch to ${nextTheme} mode`;
        });
    // Handle this small task for the current item.
        document.querySelectorAll('.app-settings-choice').forEach((button) => {
            button.setAttribute('aria-pressed', String(button.dataset.themeChoice === theme));
        });
    };

    // Handle this small task for the current item.
    topbars.forEach((topbar, index) => {
        topbar.querySelectorAll(
            '.icon-button, .therapist-notification, .therapist-calendar-shortcut'
    // Handle this small task for the current item.
        ).forEach((shortcut) => shortcut.remove());
    // Handle this small task for the current item.
        topbar.querySelectorAll('.receptionist-topbar-tools').forEach((tools) => {
            if (tools.children.length === 0) tools.remove();
        });

        const container = topbar.querySelector('.topbar-actions') || topbar;
        const utilityGroup = document.createElement('div');
        utilityGroup.className = 'app-theme-controls';

        const themeToggle = document.createElement('button');
        themeToggle.className = 'icon-button app-theme-toggle';
        themeToggle.type = 'button';

        const settingsToggle = document.createElement('button');
        settingsToggle.className = 'icon-button app-settings-toggle';
        settingsToggle.type = 'button';
        settingsToggle.setAttribute('aria-label', 'Open appearance settings');
        settingsToggle.setAttribute('aria-haspopup', 'dialog');
        settingsToggle.setAttribute('aria-expanded', 'false');
        settingsToggle.innerHTML = gearIcon;

        const panel = document.createElement('section');
        panel.className = 'app-settings-popover';
        panel.id = `appearance-settings-${index + 1}`;
        panel.setAttribute('role', 'dialog');
        panel.setAttribute('aria-label', 'Appearance settings');
        panel.hidden = true;

        const title = document.createElement('h2');
        title.textContent = 'Appearance';
        const description = document.createElement('p');
        description.textContent = 'Choose a theme for this browser.';
        const choices = document.createElement('div');
        choices.className = 'app-settings-choices';

        [
            { theme: 'light', label: 'Light mode', icon: sunIcon },
            { theme: 'dark', label: 'Dark mode', icon: moonIcon },
    // Handle this small task for the current item.
        ].forEach(({ theme, label, icon }) => {
            const choice = document.createElement('button');
            choice.className = 'app-settings-choice';
            choice.type = 'button';
            choice.dataset.themeChoice = theme;
            choice.innerHTML = `${icon}<span>${label}</span>`;
    // Save the selected theme and refresh the page colors and buttons.
            choice.addEventListener('click', () => updateTheme(theme));
            choices.append(choice);
        });
        panel.append(title, description, choices);

        const settingsRoute = settingsRoutes[role];
        if (settingsRoute) {
            const accountLink = document.createElement('a');
            const routeUrl = new URL(window.location.href);
            routeUrl.search = '';
            routeUrl.searchParams.set('route', settingsRoute[0]);
            routeUrl.hash = '';
            accountLink.className = 'app-settings-account-link';
            accountLink.href = routeUrl.toString();
            accountLink.textContent = settingsRoute[1];
            panel.append(accountLink);
        }

        utilityGroup.append(themeToggle, settingsToggle, panel);
        const userChip = container.querySelector('.user-chip');
        if (userChip) {
            container.insertBefore(utilityGroup, userChip);
        } else {
            container.append(utilityGroup);
        }
        settingsToggle.setAttribute('aria-controls', panel.id);

    // Hide the popover and restore focus when needed.
        const closePanel = (restoreFocus = false) => {
            panel.hidden = true;
            settingsToggle.setAttribute('aria-expanded', 'false');
            if (restoreFocus) settingsToggle.focus();
        };
    // Switch the password field between hidden and visible.
        themeToggle.addEventListener('click', () => {
            updateTheme(activeTheme === 'dark' ? 'light' : 'dark');
        });
    // Switch the password field between hidden and visible.
        settingsToggle.addEventListener('click', () => {
            const open = panel.hidden;
            panel.hidden = !open;
            settingsToggle.setAttribute('aria-expanded', String(open));
        });
    // Run the related action when the user interacts with this control.
        document.addEventListener('click', (event) => {
            if (!utilityGroup.contains(event.target)) closePanel();
        });
    // Run the related action when the user interacts with this control.
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !panel.hidden) closePanel(true);
        });
    });

    updateTheme(activeTheme);

    const pageTitle = document.querySelector('.page-title');
    const topbarLeft = document.querySelector('.topbar-left');
    if (pageTitle && topbarLeft && pageTitle.textContent.trim() !== 'DASHBOARD OVERVIEW' && !document.body.classList.contains('service-page') && !document.body.classList.contains('inventory-page') && !document.body.classList.contains('analytics-page')) {
    // Clean up the text before showing it in the page.
        const title = pageTitle.textContent.trim().toLowerCase().replace(/\b\w/g, (letter) => letter.toUpperCase());
        const subtitle = document.querySelector('.page-date')?.textContent.trim() || 'Manage your spa operations.';
        const titleElement = document.createElement('div');
        titleElement.className = 'topbar-page-title';
        titleElement.textContent = title;
        const subtitleElement = document.createElement('div');
        subtitleElement.className = 'topbar-page-subtitle';
        subtitleElement.textContent = subtitle;
        topbarLeft.replaceChildren(titleElement, subtitleElement);
    }

    const sidebarMenu = document.querySelector('.sidebar-menu');
    if (sidebarMenu) {
        const linksByRoute = new Map();
        const therapistAccountSection = sidebarMenu.querySelector('.nav-section[data-label="Account"]');

    // Handle this small task for the current item.
        sidebarMenu.querySelectorAll('.nav-item').forEach((link) => {
            const route = new URL(link.href, window.location.origin).searchParams.get('route');
            if (route && !linksByRoute.has(route)) {
                linksByRoute.set(route, link);
            }
        });

        const roleLabel = (document.body.dataset.role || document.querySelector('.user-role')?.textContent || '').trim().toLowerCase();
        const currentRole = roleLabel === 'administrator' ? 'admin' : roleLabel;
        let profileLink = linksByRoute.get('profile');
        if (!profileLink) {
            profileLink = document.createElement('a');
            profileLink.className = 'nav-item';
            const profileUrl = new URL(window.location.href);
            profileUrl.search = '';
            profileUrl.searchParams.set('route', 'profile');
            profileUrl.hash = '';
            profileLink.href = profileUrl.toString();
            const icon = document.createElement('span');
            icon.className = 'nav-icon';
            icon.textContent = '◎';
            const label = document.createElement('span');
            label.textContent = 'My Profile';
            profileLink.append(icon, label);
            linksByRoute.set('profile', profileLink);
        }
        if (new URL(window.location.href).searchParams.get('route') === 'profile') {
            profileLink.classList.add('active');
        }
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
    // Add the link for this page in the sidebar.
                const addManagerLink = (route, label, icon) => {
                    if (linksByRoute.has(route)) return;
                    const link = document.createElement('a');
                    link.className = 'nav-item';
                    link.href = `${dashboardUrl.split('?')[0]}?route=${route}`;
                    const iconElement = document.createElement('span');
                    iconElement.className = 'nav-icon';
                    iconElement.textContent = icon;
                    const labelElement = document.createElement('span');
                    labelElement.textContent = label;
                    link.append(iconElement, labelElement);
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
                { label: 'Settings', routes: ['users-and-roles', 'settings', 'profile'] },
            ];
            const managerLabels = {
                services: 'Services',
                products: 'Inventory',
            };
    // Handle this small task for the current item.
            Object.entries(managerLabels).forEach(([route, label]) => {
                const link = linksByRoute.get(route);
                const text = link?.querySelector('span:last-child');
                if (text) text.textContent = label;
            });

            sidebarMenu.replaceChildren();
    // Handle this small task for the current item.
            managerGroups.forEach((group) => {
                const section = document.createElement('div');
                section.className = 'nav-section';
                section.dataset.label = group.label;

    // Handle this small task for the current item.
                group.routes.forEach((route) => {
                    const link = linksByRoute.get(route);
                    if (link) section.append(link);
                });

                if (section.children.length > 0) sidebarMenu.append(section);
            });
        } else if (
            (currentRole === 'staff' || currentRole === 'therapist')
            && (document.body.classList.contains('therapist-dashboard-page') || document.body.classList.contains('account-profile-page'))
        ) {
            const therapistGroups = [
                { label: 'Main', routes: ['dashboard', 'appointments', 'customers', 'customer-history', 'calendar'] },
                { label: 'Work', routes: ['services', 'products'] },
            ];
            const therapistLabels = {
                appointments: 'My Appointments',
                customers: 'My Customer',
                'customer-history': 'Treatment History',
                calendar: 'My Schedule',
                services: 'Service',
            };
    // Handle this small task for the current item.
            Object.entries(therapistLabels).forEach(([route, label]) => {
                const link = linksByRoute.get(route);
                const text = link?.querySelector('span:last-child');
                if (text) text.textContent = label;
            });

            sidebarMenu.replaceChildren();
    // Handle this small task for the current item.
            therapistGroups.forEach((group) => {
                const section = document.createElement('div');
                section.className = 'nav-section';
                section.dataset.label = group.label;
    // Handle this small task for the current item.
                group.routes.forEach((route) => {
                    const link = linksByRoute.get(route);
                    if (link) section.append(link);
                });
                if (section.children.length > 0) sidebarMenu.append(section);
            });
            if (therapistAccountSection) {
                sidebarMenu.append(therapistAccountSection);
            }
        } else if (currentRole === 'receptionist') {
            const dashboardUrl = linksByRoute.get('dashboard')?.href;
    // Add the link for this page in the sidebar.
            const addReceptionistLink = (route, label, icon) => {
                if (linksByRoute.has(route) || !dashboardUrl) return;
                const link = document.createElement('a');
                link.className = 'nav-item';
                link.href = `${dashboardUrl.split('?')[0]}?route=${route}`;
                const iconElement = document.createElement('span');
                iconElement.className = 'nav-icon';
                iconElement.textContent = icon;
                const labelElement = document.createElement('span');
                labelElement.textContent = label;
                link.append(iconElement, labelElement);
                if (new URL(window.location.href).searchParams.get('route') === route) {
                    link.classList.add('active');
                }
                linksByRoute.set(route, link);
            };
            addReceptionistLink('calendar', 'Calendar', '▦');
            addReceptionistLink('new-appointment', 'New Appointment', '＋');
            addReceptionistLink('register-customer', 'Register Customer', '♙');

            const receptionistGroups = [
                { label: 'Main', routes: ['dashboard', 'appointments', 'customers', 'calendar', 'rooms'] },
                { label: 'Quick Actions', routes: ['new-appointment', 'register-customer'] },
                { label: 'Account', routes: ['profile'] },
            ];

            sidebarMenu.replaceChildren();
    // Handle this small task for the current item.
            receptionistGroups.forEach((group) => {
                const section = document.createElement('div');
                section.className = 'nav-section';
                section.dataset.label = group.label;
    // Handle this small task for the current item.
                group.routes.forEach((route) => {
                    const link = linksByRoute.get(route);
                    if (link) section.append(link);
                });
                if (section.children.length > 0) sidebarMenu.append(section);
            });
        } else if (currentRole === 'cashier') {
            document.body.dataset.role = 'cashier';
            const dashboardLink = linksByRoute.get('dashboard');
            const dashboardUrl = dashboardLink
                ? new URL(dashboardLink.href)
                : new URL(window.location.href);
            const currentUrl = new URL(window.location.href);
            const currentRoute = currentUrl.searchParams.get('route') || 'dashboard';
    // Create the needed item for this page section.
            const createCashierLink = (route, label, icon, hash = '') => {
                const link = document.createElement('a');
                const targetUrl = new URL(dashboardUrl.pathname, dashboardUrl.origin);
                targetUrl.searchParams.set('route', route);
                targetUrl.hash = hash;
                link.className = 'nav-item';
                link.href = targetUrl.href;
                const anchor = document.createElement('span');
                anchor.className = 'nav-icon';
                anchor.setAttribute('aria-hidden', 'true');
                anchor.textContent = icon;
                const text = document.createElement('span');
                text.textContent = label;
                link.append(anchor, text);

                const isAnchorActive = hash !== '' && currentUrl.hash === `#${hash}`;
                const isProfileActive = route === 'profile' && ['profile', 'cashier-profile'].includes(currentRoute);
                const isRouteActive = hash === '' && (route === currentRoute || isProfileActive) && currentUrl.hash === '';
                if (isAnchorActive || isRouteActive) link.classList.add('active');
                return link;
            };
            const cashierGroups = [
                {
                    label: 'Main',
                    links: [
                        ['dashboard', 'Dashboard', '⌂', ''],
                        ['pos', 'POS / New Sale', '▤', ''],
                        ['dashboard', 'Transactions', '▧', 'recent-transactions'],
                        ['customers', 'Customers', '♙', ''],
                        ['receipts', 'Receipt', '▤', ''],
                    ],
                },
                {
                    label: 'Quick Access',
                    links: [
                        ['services', 'Service', '✦', ''],
                        ['products', 'Products', '▣', ''],
                        ['memberships', 'Memberships', '◌', ''],
                    ],
                },
                {
                    label: 'Settings',
                    links: [
                        ['profile', 'My Profile', '◎', ''],
                    ],
                },
            ];

            sidebarMenu.replaceChildren();
    // Handle this small task for the current item.
            cashierGroups.forEach((group) => {
                const section = document.createElement('div');
                section.className = 'nav-section';
                section.dataset.label = group.label;
    // Handle this small task for the current item.
                group.links.forEach(([route, label, icon, hash]) => {
                    section.append(createCashierLink(route, label, icon, hash));
                });
                sidebarMenu.append(section);
            });
        } else {
            const adminGroups = [
                ['dashboard', 'appointments', 'services', 'therapists', 'rooms'],
                ['customers', 'memberships', 'customer-history'],
                ['products', 'stock-management', 'suppliers'],
                ['reports'],
                ['users-and-roles', 'settings', 'profile'],
            ];

            const staffManagementLabel = currentRole === 'admin' ? 'Staff Management' : 'Users & Roles';
            const staffManagementLink = linksByRoute.get('users-and-roles');
            const staffManagementText = staffManagementLink?.querySelector('span:last-child');
            if (staffManagementText) staffManagementText.textContent = staffManagementLabel;

            sidebarMenu.replaceChildren();
    // Handle this small task for the current item.
            adminGroups.forEach((routes) => {
                const section = document.createElement('div');
                section.className = 'nav-section';
    // Handle this small task for the current item.
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
    // Handle this small task for the current item.
    ].map((link) => ({
        label: link.textContent.trim(),
        href: link.href,
    // Handle this small task for the current item.
    })).filter((item, index, items) => (
    // Handle this small task for the current item.
        item.label && items.findIndex((candidate) => candidate.href === item.href) === index
    ));

    // Handle this small task for the current item.
    const attachRouteSearch = (searchBox) => {
        let input = searchBox.querySelector('input');

        if (!input) {
            input = document.createElement('input');
            input.type = 'search';
            input.className = 'search-input';
            input.placeholder = 'Search pages';
            input.setAttribute('aria-label', 'Search pages');
    // Handle this small task for the current item.
            searchBox.querySelectorAll('span:not(.search-icon)').forEach((label) => label.remove());
            searchBox.append(input);
        }

        searchBox.classList.add('search-input-wrap');
        const results = document.createElement('div');
        results.className = 'search-results';
        results.setAttribute('role', 'listbox');
        searchBox.append(results);

    // Handle this small task for the current item.
        const closeResults = () => {
            results.classList.remove('visible');
            results.replaceChildren();
        };

    // Handle this small task for the current item.
        const renderResults = () => {
            const query = input.value.trim().toLowerCase();
    // Handle this small task for the current item.
            const matches = routeSearchItems.filter((item) => (
                query === '' || item.label.toLowerCase().includes(query)
            ));

            results.replaceChildren();
    // Handle this small task for the current item.
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
    // Run the related action when the user interacts with this control.
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

    // Run the related action when the user interacts with this control.
        document.addEventListener('click', (event) => {
            if (!searchBox.contains(event.target)) {
                closeResults();
            }
        });
    };

    document.querySelectorAll('.search-box').forEach(attachRouteSearch);

    // Switch the password field between hidden and visible.
    document.querySelectorAll('[data-password-toggle]').forEach((toggle) => {
        const password = toggle.closest('.password-wrap')?.querySelector('input[type="password"], input[type="text"]');
        const visibleIcon = toggle.querySelector('[data-password-visible]');
        const hiddenIcon = toggle.querySelector('[data-password-hidden]');
        if (!password) return;

    // Switch the password field between hidden and visible.
        toggle.addEventListener('click', () => {
            const showing = password.type === 'text';
            password.type = showing ? 'password' : 'text';
            toggle.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
            toggle.setAttribute('aria-pressed', showing ? 'false' : 'true');
            if (visibleIcon) visibleIcon.hidden = !showing;
            if (hiddenIcon) hiddenIcon.hidden = showing;
        });
    });

    const validationMessages = new WeakMap();
    let validationMessageId = 0;
    const validationControls = 'input:not([type="hidden"]):not([type="button"]):not([type="submit"]):not([type="reset"]), select, textarea';
    const textLengthRules = {
        customer_name: [2, 150],
        customer_notes: [0, 2000],
        room_code: [2, 30],
        room_name: [2, 100],
        room_type: [0, 100],
        notes: [0, 1000],
        product_code: [2, 40],
        product_name: [2, 150],
        category: [0, 100],
        unit: [1, 30],
        service_code: [2, 30],
        service_name: [2, 150],
        description: [0, 2000],
        therapist_name: [2, 150],
        therapist_specialization: [0, 150],
        therapist_bio: [0, 2000],
        therapist_username: [3, 100],
        user_name: [2, 150],
        username: [3, 100],
        user_email: [1, 150],
        user_phone: [0, 30],
    };
    // Read a clear label for the current field.
    const getFieldLabel = (field) => {
        const label = field.labels?.[0];
        const labelText = label?.querySelector('span')?.textContent || label?.textContent || field.name || 'This field';
        return labelText.replace(/\s+/g, ' ').trim().replace(/[:*]$/, '') || 'This field';
    };
    // Check the field value and return a simple message.
    const getValidationMessage = (field) => {
        const label = getFieldLabel(field);
        const value = typeof field.value === 'string'
            ? (field.type === 'password' ? field.value : field.value.trim())
            : '';
        const name = field.name.toLowerCase();
        const customValidity = field.validity.customError ? field.validationMessage : '';
        if (customValidity) return customValidity;
        if (field.required && !value && !['file', 'checkbox', 'radio'].includes(field.type)) {
            return field.tagName === 'SELECT' ? `Please select ${label.toLowerCase()}.` : `${label} is required.`;
        }
        if (field.type === 'file') {
            const file = field.files?.[0];
            if (field.required && !file) return `${label} is required.`;
            if (!file) return '';
            if (file.size > 5 * 1024 * 1024) return 'Choose a file no larger than 5 MB.';
    // Handle this small task for the current item.
            const acceptedTypes = (field.accept || '').split(',').map((type) => type.trim().toLowerCase()).filter(Boolean);
            if (acceptedTypes.length > 0) {
                const extension = `.${file.name.split('.').pop().toLowerCase()}`;
    // Handle this small task for the current item.
                const accepted = acceptedTypes.some((type) =>
                    type === file.type.toLowerCase()
                    || (type.endsWith('/*') && file.type.toLowerCase().startsWith(type.slice(0, -1)))
                    || type === extension
                );
                if (!accepted) return 'Choose a file in one of the permitted formats.';
            }
        }
        if (field.type === 'tel' && value && !/^[0-9+()\-\s]{7,30}$/.test(value)) {
            return 'Enter a valid phone number using 7 to 30 digits or phone symbols.';
        }
        if (field.type === 'number' && value && Number.isFinite(Number(value))) {
            if (/price|discount|cost/.test(name) && Number(value) < 0) {
                return `${label} cannot be negative.`;
            }
            if (/(^|_)quantity$/.test(name) && !/(stock|reorder)/.test(name)
                && (!Number.isInteger(Number(value)) || Number(value) <= 0)) {
                return `${label} must be a positive whole number.`;
            }
        }
        if (field.type === 'password' && ['new_password', 'user_password', 'therapist_password'].includes(name) && value) {
            if (value.length < 16 || value.length > 255
                || !/[A-Za-z]/.test(value)
                || !/[0-9]/.test(value)
                || !/[^A-Za-z0-9\s]/.test(value)) {
                return 'Use at least 16 characters, including a letter, a number, and a symbol.';
            }
        }
        if (field.type === 'password' && name.includes('confirm') && value) {
            const password = field.form?.querySelector('input[name="new_password"], input[name="password"]');
            if (password && value !== password.value) return 'Passwords do not match.';
        }
        if (['checkbox', 'radio'].includes(field.type) && field.required && !field.checked) {
            return `${label} is required.`;
        }
        if (field.matches('input[type="text"], input[type="search"], textarea') && /[<>]/.test(value)) {
            return 'Please remove < and > characters.';
        }
        const lengthRule = textLengthRules[name];
        if (lengthRule && value) {
            if (value.length < lengthRule[0]) return `${label} must be at least ${lengthRule[0]} characters.`;
            if (value.length > lengthRule[1]) return `${label} must be no more than ${lengthRule[1]} characters.`;
        }
        if (field.validity.valueMissing) {
            return field.tagName === 'SELECT' ? `Please select ${label.toLowerCase()}.` : `${label} is required.`;
        }
        if (field.validity.typeMismatch) {
            if (field.type === 'email') return 'Enter a valid email address (for example, name@example.com).';
            return `Enter a valid ${label.toLowerCase()}.`;
        }
        if (field.validity.badInput) return `Enter a valid ${label.toLowerCase()}.`;
        if (field.validity.tooShort) return `${label} must be at least ${field.minLength} characters.`;
        if (field.validity.tooLong) return `${label} must be no more than ${field.maxLength} characters.`;
        if (field.validity.rangeUnderflow) return `${label} must be ${field.type === 'date' ? 'on or after' : 'at least'} ${field.min}.`;
        if (field.validity.rangeOverflow) return `${label} must be ${field.type === 'date' ? 'on or before' : 'no more than'} ${field.max}.`;
        if (field.validity.stepMismatch) {
            return field.step === '1' ? `${label} must be a whole number.` : `Enter a valid value for ${label.toLowerCase()}.`;
        }
        if (field.validity.patternMismatch) {
            if (field.title) return field.title;
            if (field.type === 'tel') return 'Enter a valid phone number.';
            return `Check the format for ${label.toLowerCase()}.`;
        }
        if (field.type === 'email' && value && value.length > 150) return `${label} must be no more than 150 characters.`;
        if (field.type === 'tel' && value.length > 30) return `${label} must be no more than 30 characters.`;
        return '';
    };
    // Check the field value and return a simple message.
    const getValidationMessageElement = (field) => {
        let message = validationMessages.get(field);
        if (message) return message;

        message = document.createElement('small');
        message.className = 'field-validation-message';
        message.setAttribute('aria-live', 'polite');
        message.id = `field-validation-${++validationMessageId}`;
        const anchor = field.parentElement?.classList.contains('password-wrap') ? field.parentElement : field;
        anchor.insertAdjacentElement('afterend', message);
        validationMessages.set(field, message);
        const describedBy = (field.getAttribute('aria-describedby') || '').split(/\s+/).filter(Boolean);
        field.setAttribute('aria-describedby', [...describedBy, message.id].join(' '));
        return message;
    };
    // Check one input and show or hide its message.
    const validateField = (field, showSuccess = true) => {
        if (field.disabled || field.readOnly) return true;
        const message = getValidationMessage(field);
        const hasValue = field.type === 'file'
            ? Boolean(field.files?.length)
            : ['checkbox', 'radio'].includes(field.type) ? field.checked
                : field.type === 'password' ? field.value !== '' : field.value.trim() !== '';
        const requiredEmpty = field.required && !hasValue;
        const invalid = Boolean(message) || !field.validity.valid || requiredEmpty;
        const displayedMessage = message || (invalid ? `Check the value for ${getFieldLabel(field).toLowerCase()}.` : '');
        const messageElement = getValidationMessageElement(field);
        field.classList.toggle('is-invalid', invalid);
        field.classList.toggle('is-valid', !invalid && hasValue && showSuccess);
        field.setAttribute('aria-invalid', invalid ? 'true' : 'false');
        messageElement.classList.toggle('is-error', invalid);
        messageElement.classList.toggle('is-success', !invalid && hasValue && showSuccess);
        messageElement.textContent = invalid ? displayedMessage : (!invalid && hasValue && showSuccess ? 'Looks good.' : '');
        return !invalid;
    };

    // Handle this small task for the current item.
    document.querySelectorAll('form').forEach((currentForm) => {
        const fields = [...currentForm.querySelectorAll(validationControls)];
        if (fields.length === 0) return;
        currentForm.noValidate = true;

    // Run the related action when the user interacts with this control.
        currentForm.addEventListener('input', (event) => {
            if (!event.target.matches(validationControls)) return;
            validateField(event.target);
            if (event.target.type === 'password') {
                const confirmation = currentForm.querySelector('input[name*="confirm"]');
                if (confirmation && validationMessages.has(confirmation)) validateField(confirmation);
            }
        });
    // Run the related action when the user interacts with this control.
        currentForm.addEventListener('change', (event) => {
            if (event.target.matches(validationControls)) validateField(event.target);
        });
    // Run the related action when the user interacts with this control.
        currentForm.addEventListener('blur', (event) => {
            if (event.target.matches(validationControls)) validateField(event.target);
        }, true);
    // Run the related action when the user interacts with this control.
        currentForm.addEventListener('submit', (event) => {
    // Check one input and show or hide its message.
            const invalidFields = fields.filter((field) => !validateField(field));
            if (invalidFields.length > 0) {
                event.preventDefault();
                invalidFields[0].focus();
                invalidFields[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    });

    const openModalButtons = document.querySelectorAll('[data-open-modal]');
    const closeModalButtons = document.querySelectorAll('[data-close-modal]');

    const bookingForm = document.querySelector('[data-booking-form]');
    if (bookingForm) {
        const customerPickerSearch = bookingForm.querySelector('[data-customer-picker-search]');
        const customerPickerOptions = [...bookingForm.querySelectorAll('#appointment-customer-options option')];
        const customerIdField = bookingForm.querySelector('[data-booking-customer]');
        const customerByName = new Map(
    // Handle this small task for the current item.
            customerPickerOptions.map((option) => [option.value.trim().toLocaleLowerCase(), option.dataset.customerId || ''])
        );
    // Handle this small task for the current item.
        const matchCustomerName = (value) => {
            const search = value.trim().toLocaleLowerCase();
            if (!search) return '';
            const exactId = customerByName.get(search);
            if (exactId) return exactId;
    // Handle this small task for the current item.
            const matches = customerPickerOptions.filter((option) => option.value.trim().toLocaleLowerCase().includes(search));
            return matches.length === 1 ? (matches[0].dataset.customerId || '') : '';
        };
    // Handle this small task for the current item.
        const syncCustomerPicker = () => {
            if (!customerPickerSearch || !customerIdField) return;
            customerIdField.value = matchCustomerName(customerPickerSearch.value);
            customerPickerSearch.setCustomValidity(
                customerPickerSearch.value.trim() && !customerIdField.value
                    ? 'Choose a customer from the suggestions.'
                    : ''
            );
        };
        customerPickerSearch?.addEventListener('input', syncCustomerPicker);
        customerPickerSearch?.addEventListener('change', syncCustomerPicker);
        customerPickerSearch?.addEventListener('blur', syncCustomerPicker);
        syncCustomerPicker();

        const summaryTargets = {
            customer: customerPickerSearch,
            service: bookingForm.querySelector('[data-booking-service]'),
            therapist: bookingForm.querySelector('[name="therapist_id"]'),
            room: bookingForm.querySelector('[name="room_id"]'),
            date: bookingForm.querySelector('[name="appointment_date"]'),
            time: bookingForm.querySelector('[name="start_time"]'),
        };
    // Handle this small task for the current item.
        const updateBookingSummary = () => {
    // Handle this small task for the current item.
            Object.entries(summaryTargets).forEach(([key, field]) => {
                const target = document.querySelector(`[data-summary-${key}]`);
                if (!target || !field) return;
                const selected = field instanceof HTMLSelectElement ? field.selectedOptions[0] : null;
                const rawValue = selected ? selected.textContent.trim() : field.value;
                if (key === 'date' && rawValue) {
                    const parsedDate = new Date(`${rawValue}T00:00:00`);
                    target.textContent = Number.isNaN(parsedDate.getTime())
                        ? rawValue
                        : parsedDate.toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
                } else if (key === 'time' && rawValue) {
                    const [hour, minute] = rawValue.split(':').map(Number);
                    if (Number.isFinite(hour) && Number.isFinite(minute)) {
                        target.textContent = new Date(2000, 0, 1, hour, minute).toLocaleTimeString(undefined, { hour: 'numeric', minute: '2-digit' });
                    } else {
                        target.textContent = rawValue;
                    }
                } else {
                    target.textContent = selected && selected.value
                        ? rawValue
                        : (key === 'customer' || key === 'service' ? `Select ${key}` : 'Not selected');
                }
            });

            const service = summaryTargets.service;
            const selectedService = service?.selectedOptions[0];
            const duration = bookingForm.querySelector('[data-booking-duration]');
            const price = document.querySelector('[data-summary-price]');
            if (duration) {
                duration.value = selectedService?.value
                    ? `${selectedService.dataset.duration || '—'} minutes`
                    : 'Select a service';
            }
            if (price) {
                const servicePrice = Number(selectedService?.dataset.price);
                price.textContent = selectedService?.value && Number.isFinite(servicePrice)
                    ? new Intl.NumberFormat(undefined, { style: 'currency', currency: 'USD' }).format(servicePrice)
                    : '—';
            }
        };

    // Run the related action when the user interacts with this control.
        Object.values(summaryTargets).forEach((field) => field?.addEventListener('change', updateBookingSummary));
        updateBookingSummary();
    }

    const customerSearch = document.querySelector('[data-customer-search]');
    const membershipFilter = document.querySelector('[data-membership-filter]');
    const customerCards = [...document.querySelectorAll('.receptionist-customer-card')];
    const customerNoResults = document.querySelector('[data-customer-no-results]');
    // Handle this small task for the current item.
    const filterCustomers = () => {
        const query = customerSearch?.value.trim().toLocaleLowerCase() || '';
        const membership = membershipFilter?.value || '';
    // Handle this small task for the current item.
        customerCards.forEach((card) => {
            const matchesQuery = card.textContent.toLocaleLowerCase().includes(query);
            const matchesMembership = !membership || card.dataset.membership === membership;
            card.hidden = !matchesQuery || !matchesMembership;
        });
        if (customerNoResults) {
    // Handle this small task for the current item.
            customerNoResults.hidden = customerCards.length === 0 || customerCards.some((card) => !card.hidden);
        }
    };
    customerSearch?.addEventListener('input', filterCustomers);
    membershipFilter?.addEventListener('change', filterCustomers);

    const cashierCustomerSearch = document.querySelector('[data-cashier-customer-search]');
    const cashierCustomerRows = [...document.querySelectorAll('[data-cashier-customer-row]')];
    const cashierCustomerNoResults = document.querySelector('[data-cashier-customer-no-results]');
    // Run the related action when the user interacts with this control.
    cashierCustomerSearch?.addEventListener('input', () => {
        const query = cashierCustomerSearch.value.trim().toLocaleLowerCase();
        let visibleCount = 0;
    // Handle this small task for the current item.
        cashierCustomerRows.forEach((row) => {
            row.hidden = !row.textContent.toLocaleLowerCase().includes(query);
            if (!row.hidden) visibleCount += 1;
        });
        if (cashierCustomerNoResults) cashierCustomerNoResults.hidden = visibleCount > 0;
    });

    // Handle this small task for the current item.
    document.querySelectorAll('[data-cashier-customer-view]').forEach((button) => {
    // Run the related action when the user interacts with this control.
        button.addEventListener('click', () => {
            const modal = document.getElementById('cashier-customer-details-modal');
            if (!modal) return;
    // Handle this small task for the current item.
            ['name', 'phone', 'membership', 'visits', 'spent', 'currency'].forEach((field) => {
                const target = modal.querySelector(`[data-cashier-detail="${field}"]`);
                if (target) target.textContent = button.dataset[field] || '';
            });
            modal.classList.remove('hidden');
            modal.setAttribute('aria-hidden', 'false');
        });
    });

    const cashierReceiptSearch = document.querySelector('[data-cashier-receipt-search]');
    const cashierReceiptRows = [...document.querySelectorAll('[data-cashier-receipt-row]')];
    const cashierReceiptNoResults = document.querySelector('[data-cashier-receipt-no-results]');
    // Run the related action when the user interacts with this control.
    cashierReceiptSearch?.addEventListener('input', () => {
        const query = cashierReceiptSearch.value.trim().toLocaleLowerCase();
        let visibleCount = 0;
    // Handle this small task for the current item.
        cashierReceiptRows.forEach((row) => {
            row.hidden = !row.textContent.toLocaleLowerCase().includes(query);
            if (!row.hidden) visibleCount += 1;
        });
        if (cashierReceiptNoResults) cashierReceiptNoResults.hidden = visibleCount > 0;
    });

    // Handle this small task for the current item.
    document.querySelectorAll('[data-open-modal="cashier-receipt-modal"]').forEach((button) => {
    // Run the related action when the user interacts with this control.
        button.addEventListener('click', () => {
            const modal = document.getElementById('cashier-receipt-modal');
            if (!modal) return;
    // Handle this small task for the current item.
            ['receiptNo', 'customer', 'date', 'items', 'method', 'amount', 'currency'].forEach((field) => {
                const target = modal.querySelector(`[data-receipt-detail="${field}"]`);
                if (target) target.textContent = button.dataset[field] || '';
            });
        });
    });

    const printCashierReceipt = document.querySelector('[data-print-cashier-receipt]');
    // Run the related action when the user interacts with this control.
    printCashierReceipt?.addEventListener('click', () => {
        document.body.classList.add('cashier-printing-receipt');
        window.print();
    });
    // Run the related action when the user interacts with this control.
    window.addEventListener('afterprint', () => {
        document.body.classList.remove('cashier-printing-receipt');
    });

    const therapistCustomerSearch = document.querySelector('[data-therapist-customer-search]');
    const therapistCustomerCards = [...document.querySelectorAll('[data-therapist-customer-card]')];
    const therapistCustomerEmpty = document.querySelector('[data-therapist-customer-empty]');
    // Run the related action when the user interacts with this control.
    therapistCustomerSearch?.addEventListener('input', () => {
        const query = therapistCustomerSearch.value.trim().toLocaleLowerCase();
        let visibleCount = 0;
    // Handle this small task for the current item.
        therapistCustomerCards.forEach((card) => {
            card.hidden = !card.textContent.toLocaleLowerCase().includes(query);
            if (!card.hidden) visibleCount += 1;
        });
        if (therapistCustomerEmpty) {
            therapistCustomerEmpty.hidden = therapistCustomerCards.length === 0 || visibleCount > 0;
        }
    });

    const therapistHistorySearch = document.querySelector('[data-therapist-history-search]');
    const therapistHistoryRows = [...document.querySelectorAll('[data-therapist-history-row]')];
    const therapistHistoryNoResults = document.querySelector('.therapist-history-no-results');
    // Run the related action when the user interacts with this control.
    therapistHistorySearch?.addEventListener('input', () => {
        const query = therapistHistorySearch.value.trim().toLocaleLowerCase();
        let visibleCount = 0;
    // Handle this small task for the current item.
        therapistHistoryRows.forEach((row) => {
            row.hidden = !row.textContent.toLocaleLowerCase().includes(query);
            if (!row.hidden) visibleCount += 1;
        });
        if (therapistHistoryNoResults) {
            therapistHistoryNoResults.hidden = visibleCount > 0;
        }
    });

    const therapistScheduleSearch = document.querySelector('[data-therapist-schedule-search]');
    const therapistScheduleBookings = [...document.querySelectorAll('[data-schedule-booking]')];
    const therapistScheduleEmpty = document.querySelector('[data-therapist-calendar-empty]');
    // Run the related action when the user interacts with this control.
    therapistScheduleSearch?.addEventListener('input', () => {
        const query = therapistScheduleSearch.value.trim().toLocaleLowerCase();
        let visibleCount = 0;
    // Handle this small task for the current item.
        therapistScheduleBookings.forEach((booking) => {
            booking.hidden = !((booking.dataset.scheduleSearch || booking.textContent).toLocaleLowerCase().includes(query));
            if (!booking.hidden) visibleCount += 1;
        });
        if (therapistScheduleEmpty) {
            therapistScheduleEmpty.hidden = query === '' || visibleCount > 0;
        }
    });

    const therapistServiceSearch = document.querySelector('[data-therapist-service-search]');
    const therapistServiceCards = [...document.querySelectorAll('[data-service-card]')];
    const therapistServiceEmpty = document.querySelector('[data-therapist-service-empty]');
    // Run the related action when the user interacts with this control.
    therapistServiceSearch?.addEventListener('input', () => {
        const query = therapistServiceSearch.value.trim().toLocaleLowerCase();
        let visibleCount = 0;
    // Handle this small task for the current item.
        therapistServiceCards.forEach((card) => {
            card.hidden = !((card.dataset.search || card.textContent).toLocaleLowerCase().includes(query));
            if (!card.hidden) visibleCount += 1;
        });
        if (therapistServiceEmpty) {
            therapistServiceEmpty.hidden = therapistServiceCards.length === 0 || visibleCount > 0;
        }
    });

    const therapistProductSearch = document.querySelector('[data-therapist-product-search]');
    const therapistProductCards = [...document.querySelectorAll('[data-therapist-product-card]')];
    const therapistProductsEmpty = document.querySelector('[data-therapist-products-empty]');
    // Run the related action when the user interacts with this control.
    therapistProductSearch?.addEventListener('input', () => {
        const query = therapistProductSearch.value.trim().toLocaleLowerCase();
        let visibleCount = 0;
    // Handle this small task for the current item.
        therapistProductCards.forEach((card) => {
            card.hidden = !((card.dataset.search || card.textContent).toLocaleLowerCase().includes(query));
            if (!card.hidden) visibleCount += 1;
        });
        if (therapistProductsEmpty) {
            therapistProductsEmpty.hidden = therapistProductCards.length === 0 || visibleCount > 0;
        }
    });

    const appointmentFilters = [...document.querySelectorAll('[data-appointment-filter]')];
    const appointmentRows = [...document.querySelectorAll('tbody tr[data-appointment-status]')];
    const appointmentSearch = document.querySelector('[data-appointment-search]');
    const appointmentDateFilter = document.querySelector('[data-appointment-date-filter]');
    const appointmentStatusFilter = document.querySelector('[data-appointment-status-filter]');
    const appointmentNoResults = document.querySelector('.appointment-no-results');
    const therapistAppointmentsPage = document.body.classList.contains('therapist-dashboard-page');
    let activeAppointmentFilter = therapistAppointmentsPage ? 'today' : 'all';
    // Build today's date string for the current view.
    const todayDate = () => {
        const now = new Date();
        return `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;
    };
    // Hide any appointment that does not match the active filters.
    const filterAppointments = () => {
        const query = appointmentSearch?.value.trim().toLocaleLowerCase() || '';
        const selectedDate = appointmentDateFilter?.value || '';
        const selectedStatus = appointmentStatusFilter?.value || 'all';
        let visibleCount = 0;
    // Handle this small task for the current item.
        appointmentRows.forEach((row) => {
            const matchesSearch = !query || (row.dataset.appointmentSearch || row.textContent).toLocaleLowerCase().includes(query);
            const appointmentDate = row.dataset.appointmentDate || '';
            const today = todayDate();
            const matchesDate = therapistAppointmentsPage
                ? activeAppointmentFilter === 'today'
                    ? appointmentDate === today
                    : activeAppointmentFilter === 'upcoming'
                        ? appointmentDate > today && row.dataset.appointmentStatus !== 'cancelled'
                        : activeAppointmentFilter === 'past'
                            ? appointmentDate < today && row.dataset.appointmentStatus !== 'cancelled'
                            : true
                : activeAppointmentFilter === 'today'
                    ? appointmentDate === today
                    : !selectedDate || appointmentDate === selectedDate;
            const tabStatus = activeAppointmentFilter === 'all' || activeAppointmentFilter === 'today'
                ? 'all'
                : therapistAppointmentsPage && activeAppointmentFilter !== 'cancelled'
                    ? 'all'
                    : activeAppointmentFilter;
            const matchesStatus = (tabStatus === 'all' || row.dataset.appointmentStatus === tabStatus)
                && (selectedStatus === 'all' || row.dataset.appointmentStatus === selectedStatus)
                && (!therapistAppointmentsPage || activeAppointmentFilter === 'cancelled' || row.dataset.appointmentStatus !== 'cancelled');
            row.hidden = !matchesSearch || !matchesDate || !matchesStatus;
            if (!row.hidden) visibleCount += 1;
        });
        if (appointmentNoResults) appointmentNoResults.hidden = visibleCount > 0;
    };
    // Handle this small task for the current item.
    appointmentFilters.forEach((filter) => {
    // Run the related action when the user interacts with this control.
        filter.addEventListener('click', () => {
            activeAppointmentFilter = filter.dataset.appointmentFilter || 'all';
    // Switch the password field between hidden and visible.
            appointmentFilters.forEach((button) => button.classList.toggle('active', button === filter));
            if (appointmentStatusFilter) {
                appointmentStatusFilter.value = activeAppointmentFilter === 'today' ? 'all' : activeAppointmentFilter;
            }
            filterAppointments();
        });
    });
    appointmentSearch?.addEventListener('input', filterAppointments);
    appointmentDateFilter?.addEventListener('change', filterAppointments);
    // Run the related action when the user interacts with this control.
    appointmentStatusFilter?.addEventListener('change', () => {
        activeAppointmentFilter = 'all';
    // Switch the password field between hidden and visible.
        appointmentFilters.forEach((button) => button.classList.toggle('active', button.dataset.appointmentFilter === 'all'));
        filterAppointments();
    });
    if (appointmentRows.length > 0) filterAppointments();

    // Handle this small task for the current item.
    document.querySelectorAll('[data-appointment-view]').forEach((button) => {
    // Run the related action when the user interacts with this control.
        button.addEventListener('click', () => {
    // Handle this small task for the current item.
            document.querySelectorAll('[data-appointment-detail]').forEach((detail) => {
                const key = detail.dataset.appointmentDetail;
                detail.textContent = key ? button.dataset[key] || '' : '';
            });
        });
    });

    // Set up the profile photo upload and preview.
    const configureProfilePhotoPicker = (inputId, imageId, initialsId, filenameId) => {
        const input = document.getElementById(inputId);
        const image = document.getElementById(imageId);
        const initials = document.getElementById(initialsId);
        const filename = document.getElementById(filenameId);
        let objectUrl = '';

    // Run the related action when the user interacts with this control.
        input?.addEventListener('change', () => {
            const file = input.files?.[0];
            if (!file || !image || !initials || !filename) return;

            if (objectUrl) URL.revokeObjectURL(objectUrl);
            objectUrl = URL.createObjectURL(file);
            image.src = objectUrl;
            image.hidden = false;
            initials.hidden = true;
            filename.textContent = file.name;
        });
    };

    configureProfilePhotoPicker(
        'user-profile-photo-input',
        'user-profile-photo-image',
        'user-profile-photo-initials',
        'user-profile-photo-filename',
    );
    configureProfilePhotoPicker(
        'therapist-profile-photo-input',
        'therapist-profile-photo-image',
        'therapist-profile-photo-initials',
        'therapist-profile-photo-filename',
    );

    const therapistLoginAccount = document.getElementById('therapist-login-account');
    const therapistLoginCredentials = document.getElementById('therapist-login-credentials');
    const therapistProfilePhotoInput = document.getElementById('therapist-profile-photo-input');
    const therapistProfilePhotoImage = document.getElementById('therapist-profile-photo-image');
    const therapistProfilePhotoInitials = document.getElementById('therapist-profile-photo-initials');
    const therapistProfilePhotoFilename = document.getElementById('therapist-profile-photo-filename');
    const therapistProfilePhotoGuidance = 'JPG, PNG, GIF, or WEBP. Maximum 5 MB. Choose or create a login account to save the photo.';
    // Handle this small task for the current item.
    const updateTherapistAccountPhoto = () => {
        if (!therapistProfilePhotoImage || !therapistProfilePhotoInitials || therapistProfilePhotoInput?.files?.length) return;
        const selectedOption = therapistLoginAccount?.selectedOptions?.[0];
        const imageUrl = selectedOption?.dataset.profileImage || '';
        if (imageUrl) {
            therapistProfilePhotoImage.src = imageUrl;
        } else {
            therapistProfilePhotoImage.removeAttribute('src');
        }
        therapistProfilePhotoImage.hidden = imageUrl === '';
        therapistProfilePhotoInitials.hidden = imageUrl !== '';
        if (therapistProfilePhotoFilename) {
            therapistProfilePhotoFilename.textContent = imageUrl
                ? 'Current account photo. Choose a new photo to replace it.'
                : therapistProfilePhotoGuidance;
        }
    };
    // Run the related action when the user interacts with this control.
    therapistLoginAccount?.addEventListener('change', () => {
        if (!therapistLoginCredentials) return;
        const creatingLogin = therapistLoginAccount.value === 'new';
        therapistLoginCredentials.hidden = !creatingLogin;
    // Handle this small task for the current item.
        therapistLoginCredentials.querySelectorAll('input').forEach((input) => {
            input.required = creatingLogin;
        });
        updateTherapistAccountPhoto();
    });
    updateTherapistAccountPhoto();

    const customerPhotoInput = document.querySelector('[data-customer-photo]');
    const customerPhotoPreview = document.querySelector('[data-customer-photo-preview]');
    const customerPhotoPrompt = document.querySelector('[data-customer-photo-prompt]');
    const customerPhotoError = document.querySelector('[data-customer-photo-error]');
    let customerPhotoObjectUrl = '';
    // Run the related action when the user interacts with this control.
    customerPhotoInput?.addEventListener('change', () => {
        const file = customerPhotoInput.files?.[0];
        if (!file || !customerPhotoPreview || !customerPhotoPrompt || !customerPhotoError) return;

        customerPhotoError.hidden = true;
        customerPhotoError.textContent = '';
        if (!['image/jpeg', 'image/png'].includes(file.type) || file.size > 5 * 1024 * 1024) {
            customerPhotoError.textContent = file.size > 5 * 1024 * 1024
                ? 'Choose a photo no larger than 5 MB.'
                : 'Choose a PNG or JPG photo.';
            customerPhotoError.hidden = false;
            customerPhotoInput.value = '';
            if (customerPhotoObjectUrl) URL.revokeObjectURL(customerPhotoObjectUrl);
            customerPhotoObjectUrl = '';
            customerPhotoPreview.removeAttribute('src');
            customerPhotoPreview.hidden = true;
            customerPhotoPrompt.hidden = false;
            return;
        }

        if (customerPhotoObjectUrl) URL.revokeObjectURL(customerPhotoObjectUrl);
        customerPhotoObjectUrl = URL.createObjectURL(file);
        customerPhotoPreview.src = customerPhotoObjectUrl;
        customerPhotoPreview.hidden = false;
        customerPhotoPrompt.hidden = true;
    });

    // Handle this small task for the current item.
    openModalButtons.forEach((button) => {
    // Run the related action when the user interacts with this control.
        button.addEventListener('click', () => {
            const modalId = button.getAttribute('data-open-modal');
            const modal = document.getElementById(modalId);
            if (!modal) return;

            modal.classList.remove('hidden');
            modal.setAttribute('aria-hidden', 'false');
        });
    });

    // Handle this small task for the current item.
    closeModalButtons.forEach((button) => {
    // Run the related action when the user interacts with this control.
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
    // Run the related action when the user interacts with this control.
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
    // Handle this small task for the current item.
    const filterServices = () => {
        if (!serviceSearch || !serviceCategory) return;
        const query = serviceSearch.value.trim().toLowerCase();
        const category = serviceCategory.value;
        let visibleCount = 0;
    // Handle this small task for the current item.
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

    const cashierServiceSearch = document.querySelector('[data-cashier-service-search]');
    const cashierServiceCards = [...document.querySelectorAll('[data-cashier-service-card]')];
    const cashierServiceCategories = [...document.querySelectorAll('[data-cashier-service-category]')];
    const cashierServiceNoResults = document.querySelector('[data-cashier-service-no-results]');
    let activeCashierServiceCategory = 'all';
    // Handle this small task for the current item.
    const filterCashierServices = () => {
        const query = cashierServiceSearch?.value.trim().toLocaleLowerCase() || '';
        let visibleCount = 0;
    // Handle this small task for the current item.
        cashierServiceCards.forEach((card) => {
            const matchesQuery = (card.dataset.search || '').includes(query);
            const matchesCategory = activeCashierServiceCategory === 'all'
                || card.dataset.category === activeCashierServiceCategory;
            card.hidden = !matchesQuery || !matchesCategory;
            if (!card.hidden) visibleCount += 1;
        });
        if (cashierServiceNoResults) cashierServiceNoResults.hidden = visibleCount > 0;
    };
    cashierServiceSearch?.addEventListener('input', filterCashierServices);
    // Handle this small task for the current item.
    cashierServiceCategories.forEach((button) => {
    // Run the related action when the user interacts with this control.
        button.addEventListener('click', () => {
            activeCashierServiceCategory = button.dataset.cashierServiceCategory || 'all';
    // Handle this small task for the current item.
            cashierServiceCategories.forEach((categoryButton) => {
                const active = categoryButton === button;
                categoryButton.classList.toggle('active', active);
                categoryButton.setAttribute('aria-pressed', String(active));
            });
            filterCashierServices();
        });
    });

    const cashierProductSearch = document.querySelector('[data-cashier-product-search]');
    const cashierProductRows = [...document.querySelectorAll('[data-cashier-product-row]')];
    const cashierProductNoResults = document.querySelector('[data-cashier-product-no-results]');
    // Run the related action when the user interacts with this control.
    cashierProductSearch?.addEventListener('input', () => {
        const query = cashierProductSearch.value.trim().toLocaleLowerCase();
        let visibleCount = 0;
    // Handle this small task for the current item.
        cashierProductRows.forEach((row) => {
            row.hidden = !row.textContent.toLocaleLowerCase().includes(query);
            if (!row.hidden) visibleCount += 1;
        });
        if (cashierProductNoResults) cashierProductNoResults.hidden = visibleCount > 0;
    });

    const cashierMembershipSearch = document.querySelector('[data-cashier-membership-search]');
    const cashierMembershipRows = [...document.querySelectorAll('[data-cashier-membership-row]')];
    const cashierMembershipNoResults = document.querySelector('[data-cashier-membership-no-results]');
    // Run the related action when the user interacts with this control.
    cashierMembershipSearch?.addEventListener('input', () => {
        const query = cashierMembershipSearch.value.trim().toLocaleLowerCase();
        let visibleCount = 0;
    // Handle this small task for the current item.
        cashierMembershipRows.forEach((row) => {
            row.hidden = !row.textContent.toLocaleLowerCase().includes(query);
            if (!row.hidden) visibleCount += 1;
        });
        if (cashierMembershipNoResults) cashierMembershipNoResults.hidden = visibleCount > 0;
    });

    const inventorySearch = document.getElementById('inventory-search');
    const inventoryStatus = document.getElementById('inventory-status');
    const inventoryRows = [...document.querySelectorAll('#inventory-table tbody tr')];
    const inventoryEmpty = document.getElementById('inventory-empty');
    // Handle this small task for the current item.
    const filterInventory = () => {
        if (!inventorySearch || !inventoryStatus) return;
        const query = inventorySearch.value.trim().toLowerCase();
        const status = inventoryStatus.value;
        let visibleCount = 0;
    // Handle this small task for the current item.
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
    // Handle this small task for the current item.
    searchInputs.forEach((input) => {
    // Run the related action when the user interacts with this control.
        input.addEventListener('input', () => {
            const query = input.value.trim().toLowerCase();
            const table = input.closest('.main-panel')?.querySelector('.searchable-table');

            if (!table) {
                return;
            }

            const rows = table.querySelectorAll('tbody tr');
    // Handle this small task for the current item.
            rows.forEach((row) => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(query) ? '' : 'none';
            });
        });
    });

    const filterPills = document.querySelectorAll('.pill');
    // Handle this small task for the current item.
    filterPills.forEach((pill) => {
    // Run the related action when the user interacts with this control.
        pill.addEventListener('click', () => {
    // Handle this small task for the current item.
            filterPills.forEach((item) => item.classList.remove('active'));
            pill.classList.add('active');
        });
    });

    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    const mainPanel = document.querySelector('.main-panel');
    const sidebar = document.querySelector('.sidebar');
    const dashboardShell = document.querySelector('.dashboard-shell');
    const topbar = mainPanel?.querySelector('.topbar');
    if (sidebar && dashboardShell && topbar) {
        const drawerToggle = document.createElement('button');
        drawerToggle.className = 'sidebar-drawer-toggle';
        drawerToggle.type = 'button';
        drawerToggle.setAttribute('aria-label', 'Open navigation menu');
        drawerToggle.setAttribute('aria-controls', 'app-sidebar');
        drawerToggle.setAttribute('aria-expanded', 'false');
        for (let index = 0; index < 3; index += 1) {
            drawerToggle.append(document.createElement('span'));
        }
        sidebar.id = sidebar.id || 'app-sidebar';
        topbar.insertBefore(drawerToggle, topbar.firstChild);

        const drawerOverlay = document.createElement('button');
        drawerOverlay.className = 'sidebar-drawer-overlay';
        drawerOverlay.type = 'button';
        drawerOverlay.setAttribute('aria-label', 'Close navigation menu');
        drawerOverlay.tabIndex = -1;
        dashboardShell.insertBefore(drawerOverlay, sidebar);

    // Handle this small task for the current item.
        const setDrawerOpen = (isOpen) => {
            document.body.classList.toggle('sidebar-drawer-open', isOpen);
            sidebar.classList.toggle('is-open', isOpen);
            drawerOverlay.classList.toggle('is-visible', isOpen);
            sidebar.setAttribute('aria-hidden', String(!isOpen && window.innerWidth <= 1024));
            sidebar.inert = !isOpen && window.innerWidth <= 1024;
            drawerToggle.setAttribute('aria-expanded', String(isOpen));
            drawerToggle.setAttribute('aria-label', isOpen ? 'Close navigation menu' : 'Open navigation menu');
            if (isOpen && window.innerWidth <= 1024) {
                sidebar.querySelector('a.nav-item')?.focus();
            } else if (window.innerWidth <= 1024) {
                drawerToggle.focus();
            }
        };

    // Switch the password field between hidden and visible.
        drawerToggle.addEventListener('click', () => {
            setDrawerOpen(!sidebar.classList.contains('is-open'));
        });
    // Run the related action when the user interacts with this control.
        drawerOverlay.addEventListener('click', () => setDrawerOpen(false));
    // Run the related action when the user interacts with this control.
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && sidebar.classList.contains('is-open')) setDrawerOpen(false);
        });
    // Handle this small task for the current item.
        sidebar.querySelectorAll('a.nav-item').forEach((link) => {
    // Run the related action when the user interacts with this control.
            link.addEventListener('click', () => {
                if (window.innerWidth <= 1024) setDrawerOpen(false);
            });
        });
    // Run the related action when the user interacts with this control.
        window.addEventListener('resize', () => {
            if (window.innerWidth > 1024) {
                document.body.classList.remove('sidebar-drawer-open');
                sidebar.classList.remove('is-open');
                drawerOverlay.classList.remove('is-visible');
                sidebar.removeAttribute('aria-hidden');
                sidebar.inert = false;
                drawerToggle.setAttribute('aria-expanded', 'false');
                drawerToggle.setAttribute('aria-label', 'Open navigation menu');
            } else if (!sidebar.classList.contains('is-open')) {
                sidebar.setAttribute('aria-hidden', 'true');
                sidebar.inert = true;
            }
        });
        if (window.innerWidth <= 1024) {
            sidebar.setAttribute('aria-hidden', 'true');
            sidebar.inert = true;
        }
    }

    if (mainPanel && window.location.hash === '#spa-route-transition') {
        history.replaceState(null, '', `${window.location.pathname}${window.location.search}`);
        mainPanel.classList.add('route-transition-enter');
    // Run the related action when the user interacts with this control.
        mainPanel.addEventListener('animationend', () => {
            mainPanel.classList.remove('route-transition-enter');
        }, { once: true });
    }

    if (!reducedMotion.matches) {
    // Handle this small task for the current item.
        const numberFormat = (value, decimals) => value.toLocaleString(undefined, {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals,
        });
        document.querySelectorAll(
            '.manager-stat-card strong, .receptionist-stat-card strong, .analytics-stat-card strong, .stat-card .stat-value'
    // Handle this small task for the current item.
        ).forEach((counter) => {
            const original = counter.textContent.trim();
            const match = original.match(/^([^0-9-]*)(-?[\d,]+(?:\.\d+)?)(\s*%?)$/);
            if (!match) return;

            const startValue = Number(match[2].replace(/,/g, ''));
            if (!Number.isFinite(startValue) || startValue === 0) return;
            const decimals = (match[2].split('.')[1] || '').length;
            const startedAt = performance.now();
            const duration = 650;
    // Handle this small task for the current item.
            const updateCounter = (timestamp) => {
                const progress = Math.min((timestamp - startedAt) / duration, 1);
                const easedProgress = 1 - ((1 - progress) ** 3);
                counter.textContent = `${match[1]}${numberFormat(startValue * easedProgress, decimals)}${match[3]}`;
                if (progress < 1) {
                    requestAnimationFrame(updateCounter);
                } else {
                    counter.textContent = original;
                }
            };
            counter.textContent = `${match[1]}${numberFormat(0, decimals)}${match[3]}`;
            requestAnimationFrame(updateCounter);
        });

    // Run the related action when the user interacts with this control.
        document.addEventListener('click', (event) => {
            const button = event.target.closest('button.primary-button, button.secondary-button, button.icon-button, button.pill, a.primary-button, a.secondary-button');
            if (!button || button.matches(':disabled')) return;

            const bounds = button.getBoundingClientRect();
            const ripple = document.createElement('span');
            ripple.className = 'button-ripple';
            ripple.style.left = `${event.clientX ? event.clientX - bounds.left : bounds.width / 2}px`;
            ripple.style.top = `${event.clientY ? event.clientY - bounds.top : bounds.height / 2}px`;
            button.append(ripple);
    // Run the related action when the user interacts with this control.
            ripple.addEventListener('animationend', () => ripple.remove(), { once: true });
        });
    }

    if (mainPanel) {
    // Handle this small task for the current item.
        document.querySelectorAll('.sidebar-menu a.nav-item, .calendar-navigation a').forEach((link) => {
    // Run the related action when the user interacts with this control.
            link.addEventListener('click', (event) => {
                if (
                    reducedMotion.matches
                    || event.defaultPrevented
                    || event.button !== 0
                    || event.metaKey
                    || event.ctrlKey
                    || event.shiftKey
                    || event.altKey
                    || link.target
                    || link.hasAttribute('download')
                ) return;

                const destination = new URL(link.href, window.location.href);
                if (
                    destination.origin !== window.location.origin
                    || !destination.searchParams.has('route')
                    || destination.href === window.location.href
                ) return;

                event.preventDefault();
                mainPanel.classList.add('route-transition-leave');
    // Handle this small task for the current item.
                document.querySelectorAll('.main-panel .table-wrap').forEach((table) => {
                    table.classList.add('is-loading');
                });
                destination.hash = 'spa-route-transition';
    // Handle this small task for the current item.
                window.setTimeout(() => window.location.assign(destination.href), 130);
            });
        });
    }
});
