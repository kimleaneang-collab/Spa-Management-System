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
        const therapistAccountSection = sidebarMenu.querySelector('.nav-section[data-label="Account"]');

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
        } else if ((currentRole === 'staff' || currentRole === 'therapist') && document.body.classList.contains('therapist-dashboard-page')) {
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
            Object.entries(therapistLabels).forEach(([route, label]) => {
                const link = linksByRoute.get(route);
                const text = link?.querySelector('span:last-child');
                if (text) text.textContent = label;
            });

            sidebarMenu.replaceChildren();
            therapistGroups.forEach((group) => {
                const section = document.createElement('div');
                section.className = 'nav-section';
                section.dataset.label = group.label;
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
            const addReceptionistLink = (route, label, icon) => {
                if (linksByRoute.has(route) || !dashboardUrl) return;
                const link = document.createElement('a');
                link.className = 'nav-item';
                link.href = `${dashboardUrl.split('?')[0]}?route=${route}`;
                link.innerHTML = `<span class="nav-icon">${icon}</span><span>${label}</span>`;
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
            ];

            sidebarMenu.replaceChildren();
            receptionistGroups.forEach((group) => {
                const section = document.createElement('div');
                section.className = 'nav-section';
                section.dataset.label = group.label;
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
                const isRouteActive = hash === '' && route === currentRoute && currentUrl.hash === '';
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
                        ['cashier-profile', 'My Profile', '◎', ''],
                    ],
                },
            ];

            sidebarMenu.replaceChildren();
            cashierGroups.forEach((group) => {
                const section = document.createElement('div');
                section.className = 'nav-section';
                section.dataset.label = group.label;
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

    const bookingForm = document.querySelector('[data-booking-form]');
    if (bookingForm) {
        const customerPickerSearch = bookingForm.querySelector('[data-customer-picker-search]');
        const customerPickerOptions = [...bookingForm.querySelectorAll('#appointment-customer-options option')];
        const customerIdField = bookingForm.querySelector('[data-booking-customer]');
        const customerByName = new Map(
            customerPickerOptions.map((option) => [option.value.trim().toLocaleLowerCase(), option.dataset.customerId || ''])
        );
        const matchCustomerName = (value) => {
            const search = value.trim().toLocaleLowerCase();
            if (!search) return '';
            const exactId = customerByName.get(search);
            if (exactId) return exactId;
            const matches = customerPickerOptions.filter((option) => option.value.trim().toLocaleLowerCase().includes(search));
            return matches.length === 1 ? (matches[0].dataset.customerId || '') : '';
        };
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
        const updateBookingSummary = () => {
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

        Object.values(summaryTargets).forEach((field) => field?.addEventListener('change', updateBookingSummary));
        updateBookingSummary();
    }

    const customerSearch = document.querySelector('[data-customer-search]');
    const membershipFilter = document.querySelector('[data-membership-filter]');
    const customerCards = [...document.querySelectorAll('.receptionist-customer-card')];
    const customerNoResults = document.querySelector('[data-customer-no-results]');
    const filterCustomers = () => {
        const query = customerSearch?.value.trim().toLocaleLowerCase() || '';
        const membership = membershipFilter?.value || '';
        customerCards.forEach((card) => {
            const matchesQuery = card.textContent.toLocaleLowerCase().includes(query);
            const matchesMembership = !membership || card.dataset.membership === membership;
            card.hidden = !matchesQuery || !matchesMembership;
        });
        if (customerNoResults) {
            customerNoResults.hidden = customerCards.length === 0 || customerCards.some((card) => !card.hidden);
        }
    };
    customerSearch?.addEventListener('input', filterCustomers);
    membershipFilter?.addEventListener('change', filterCustomers);

    const cashierCustomerSearch = document.querySelector('[data-cashier-customer-search]');
    const cashierCustomerRows = [...document.querySelectorAll('[data-cashier-customer-row]')];
    const cashierCustomerNoResults = document.querySelector('[data-cashier-customer-no-results]');
    cashierCustomerSearch?.addEventListener('input', () => {
        const query = cashierCustomerSearch.value.trim().toLocaleLowerCase();
        let visibleCount = 0;
        cashierCustomerRows.forEach((row) => {
            row.hidden = !row.textContent.toLocaleLowerCase().includes(query);
            if (!row.hidden) visibleCount += 1;
        });
        if (cashierCustomerNoResults) cashierCustomerNoResults.hidden = visibleCount > 0;
    });

    document.querySelectorAll('[data-cashier-customer-view]').forEach((button) => {
        button.addEventListener('click', () => {
            const modal = document.getElementById('cashier-customer-details-modal');
            if (!modal) return;
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
    cashierReceiptSearch?.addEventListener('input', () => {
        const query = cashierReceiptSearch.value.trim().toLocaleLowerCase();
        let visibleCount = 0;
        cashierReceiptRows.forEach((row) => {
            row.hidden = !row.textContent.toLocaleLowerCase().includes(query);
            if (!row.hidden) visibleCount += 1;
        });
        if (cashierReceiptNoResults) cashierReceiptNoResults.hidden = visibleCount > 0;
    });

    document.querySelectorAll('[data-open-modal="cashier-receipt-modal"]').forEach((button) => {
        button.addEventListener('click', () => {
            const modal = document.getElementById('cashier-receipt-modal');
            if (!modal) return;
            ['receiptNo', 'customer', 'date', 'items', 'method', 'amount', 'currency'].forEach((field) => {
                const target = modal.querySelector(`[data-receipt-detail="${field}"]`);
                if (target) target.textContent = button.dataset[field] || '';
            });
        });
    });

    const printCashierReceipt = document.querySelector('[data-print-cashier-receipt]');
    printCashierReceipt?.addEventListener('click', () => {
        document.body.classList.add('cashier-printing-receipt');
        window.print();
    });
    window.addEventListener('afterprint', () => {
        document.body.classList.remove('cashier-printing-receipt');
    });

    const therapistCustomerSearch = document.querySelector('[data-therapist-customer-search]');
    const therapistCustomerCards = [...document.querySelectorAll('[data-therapist-customer-card]')];
    const therapistCustomerEmpty = document.querySelector('[data-therapist-customer-empty]');
    therapistCustomerSearch?.addEventListener('input', () => {
        const query = therapistCustomerSearch.value.trim().toLocaleLowerCase();
        let visibleCount = 0;
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
    therapistHistorySearch?.addEventListener('input', () => {
        const query = therapistHistorySearch.value.trim().toLocaleLowerCase();
        let visibleCount = 0;
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
    therapistScheduleSearch?.addEventListener('input', () => {
        const query = therapistScheduleSearch.value.trim().toLocaleLowerCase();
        let visibleCount = 0;
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
    therapistServiceSearch?.addEventListener('input', () => {
        const query = therapistServiceSearch.value.trim().toLocaleLowerCase();
        let visibleCount = 0;
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
    therapistProductSearch?.addEventListener('input', () => {
        const query = therapistProductSearch.value.trim().toLocaleLowerCase();
        let visibleCount = 0;
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
    const todayDate = () => {
        const now = new Date();
        return `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;
    };
    const filterAppointments = () => {
        const query = appointmentSearch?.value.trim().toLocaleLowerCase() || '';
        const selectedDate = appointmentDateFilter?.value || '';
        const selectedStatus = appointmentStatusFilter?.value || 'all';
        let visibleCount = 0;
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
    appointmentFilters.forEach((filter) => {
        filter.addEventListener('click', () => {
            activeAppointmentFilter = filter.dataset.appointmentFilter || 'all';
            appointmentFilters.forEach((button) => button.classList.toggle('active', button === filter));
            if (appointmentStatusFilter) {
                appointmentStatusFilter.value = activeAppointmentFilter === 'today' ? 'all' : activeAppointmentFilter;
            }
            filterAppointments();
        });
    });
    appointmentSearch?.addEventListener('input', filterAppointments);
    appointmentDateFilter?.addEventListener('change', filterAppointments);
    appointmentStatusFilter?.addEventListener('change', () => {
        activeAppointmentFilter = 'all';
        appointmentFilters.forEach((button) => button.classList.toggle('active', button.dataset.appointmentFilter === 'all'));
        filterAppointments();
    });
    if (appointmentRows.length > 0) filterAppointments();

    document.querySelectorAll('[data-appointment-view]').forEach((button) => {
        button.addEventListener('click', () => {
            document.querySelectorAll('[data-appointment-detail]').forEach((detail) => {
                const key = detail.dataset.appointmentDetail;
                detail.textContent = key ? button.dataset[key] || '' : '';
            });
        });
    });

    const configureProfilePhotoPicker = (inputId, imageId, initialsId, filenameId) => {
        const input = document.getElementById(inputId);
        const image = document.getElementById(imageId);
        const initials = document.getElementById(initialsId);
        const filename = document.getElementById(filenameId);
        let objectUrl = '';

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
    therapistLoginAccount?.addEventListener('change', () => {
        if (!therapistLoginCredentials) return;
        const creatingLogin = therapistLoginAccount.value === 'new';
        therapistLoginCredentials.hidden = !creatingLogin;
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

    const cashierServiceSearch = document.querySelector('[data-cashier-service-search]');
    const cashierServiceCards = [...document.querySelectorAll('[data-cashier-service-card]')];
    const cashierServiceCategories = [...document.querySelectorAll('[data-cashier-service-category]')];
    const cashierServiceNoResults = document.querySelector('[data-cashier-service-no-results]');
    let activeCashierServiceCategory = 'all';
    const filterCashierServices = () => {
        const query = cashierServiceSearch?.value.trim().toLocaleLowerCase() || '';
        let visibleCount = 0;
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
    cashierServiceCategories.forEach((button) => {
        button.addEventListener('click', () => {
            activeCashierServiceCategory = button.dataset.cashierServiceCategory || 'all';
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
    cashierProductSearch?.addEventListener('input', () => {
        const query = cashierProductSearch.value.trim().toLocaleLowerCase();
        let visibleCount = 0;
        cashierProductRows.forEach((row) => {
            row.hidden = !row.textContent.toLocaleLowerCase().includes(query);
            if (!row.hidden) visibleCount += 1;
        });
        if (cashierProductNoResults) cashierProductNoResults.hidden = visibleCount > 0;
    });

    const cashierMembershipSearch = document.querySelector('[data-cashier-membership-search]');
    const cashierMembershipRows = [...document.querySelectorAll('[data-cashier-membership-row]')];
    const cashierMembershipNoResults = document.querySelector('[data-cashier-membership-no-results]');
    cashierMembershipSearch?.addEventListener('input', () => {
        const query = cashierMembershipSearch.value.trim().toLocaleLowerCase();
        let visibleCount = 0;
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
