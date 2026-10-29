document.addEventListener('DOMContentLoaded', () => {
    const config = window.posConfig || { currency: 'USD', taxPercent: 0 };
    const cart = new Map();
    const cartContainer = document.getElementById('pos-cart-items');
    const emptyMessage = document.getElementById('pos-cart-empty');
    const itemsInput = document.getElementById('pos-items-json');
    const discountInput = document.getElementById('pos-discount-input') || document.getElementById('pos-discount');
    const submittedDiscountInput = document.querySelector('input[name="discount_amount"]');

    if (!cartContainer || !itemsInput || !discountInput) return;

    const formatMoney = (amount) => `${config.currencySymbol || `${config.currency} `}${Number(amount).toFixed(2)}`;
    const cartKey = (item) => `${item.type}:${item.id}`;
    const categoryFilters = [...document.querySelectorAll('[data-pos-category-filter]')];
    const searchInput = document.querySelector('[data-pos-search]');
    const noResults = document.querySelector('[data-pos-no-results]');
    let activeCategory = 'all';

    document.querySelectorAll('[data-pos-image]').forEach((image) => {
        image.addEventListener('error', () => {
            image.closest('.cashier-pos-item-image')?.classList.add('image-failed');
        }, { once: true });
    });

    const filterCatalog = () => {
        const searchTerm = (searchInput?.value || '').trim().toLowerCase();
        let visibleCount = 0;
        document.querySelectorAll('.cashier-pos-item-card').forEach((item) => {
            const matchesCategory = activeCategory === 'all' || item.dataset.posCategory === activeCategory;
            const matchesSearch = !searchTerm || (item.dataset.posSearchValue || '').includes(searchTerm);
            const isVisible = matchesCategory && matchesSearch;
            item.hidden = !isVisible;
            if (isVisible) visibleCount += 1;
        });
        if (noResults) noResults.hidden = visibleCount > 0;
    };

    const renderCart = () => {
        cartContainer.querySelectorAll('.pos-cart-row').forEach((row) => row.remove());
        let subtotal = 0;
        const items = [...cart.values()].map((item) => {
            subtotal += item.price * item.quantity;
            return { type: item.type, id: item.id, quantity: item.quantity };
        });
        itemsInput.value = JSON.stringify(items);
        if (emptyMessage) emptyMessage.hidden = items.length > 0;

        cart.forEach((item, key) => {
            const row = document.createElement('div');
            row.className = 'pos-cart-row';
            const description = document.createElement('div');
            description.className = 'pos-cart-description';
            const name = document.createElement('strong');
            name.textContent = item.name;
            const line = document.createElement('small');
            line.textContent = `${formatMoney(item.price)} each`;
            description.append(name, line);

            const quantity = document.createElement('div');
            quantity.className = 'pos-quantity-control';
            const decrease = document.createElement('button');
            decrease.type = 'button';
            decrease.textContent = '−';
            decrease.setAttribute('aria-label', `Remove one ${item.name}`);
            decrease.addEventListener('click', () => {
                item.quantity -= 1;
                if (item.quantity <= 0) cart.delete(key);
                renderCart();
            });
            const count = document.createElement('span');
            count.textContent = Number.isInteger(item.quantity) ? String(item.quantity) : item.quantity.toFixed(3);
            const increase = document.createElement('button');
            increase.type = 'button';
            increase.textContent = '+';
            increase.setAttribute('aria-label', `Add one ${item.name}`);
            increase.addEventListener('click', () => {
                if (item.type === 'product' && item.quantity + 1 > item.stock) return;
                item.quantity += 1;
                renderCart();
            });
            quantity.append(decrease, count, increase);

            const lineTotal = document.createElement('strong');
            lineTotal.className = 'pos-line-total';
            lineTotal.textContent = formatMoney(item.price * item.quantity);
            row.append(description, quantity, lineTotal);
            cartContainer.append(row);
        });

        const discount = Math.max(0, Number(discountInput.value) || 0);
        const taxBase = Math.max(0, subtotal - discount);
        const tax = taxBase * Number(config.taxPercent || 0) / 100;
        const total = taxBase + tax;
        document.getElementById('pos-subtotal').textContent = formatMoney(subtotal);
        document.getElementById('pos-tax').textContent = formatMoney(tax);
        document.getElementById('pos-total').textContent = formatMoney(total);
        document.getElementById('cashier-pos-charge-total')?.replaceChildren(document.createTextNode(formatMoney(total)));
        const itemCount = [...cart.values()].reduce((count, item) => count + item.quantity, 0);
        const cartCount = document.querySelector('[data-pos-cart-count]');
        if (cartCount) cartCount.textContent = `(${itemCount})`;
        if (submittedDiscountInput && submittedDiscountInput !== discountInput) {
            submittedDiscountInput.value = String(discount);
        }
    };

    document.querySelectorAll('.pos-item-card').forEach((button) => {
        button.addEventListener('click', () => {
            let item;
            try {
                item = JSON.parse(button.dataset.item || '{}');
            } catch (error) {
                console.error('Could not read the selected POS catalog item.', error);
                return;
            }

            if (!item.id || !item.type || !Number.isFinite(Number(item.price))) return;
            const key = cartKey(item);
            const current = cart.get(key);
            if (item.type === 'product' && current && current.quantity + 1 > Number(item.stock)) return;
            if (current) {
                current.quantity += 1;
            } else {
                cart.set(key, { ...item, price: Number(item.price), quantity: 1 });
            }
            renderCart();
        });
    });

    discountInput.addEventListener('input', renderCart);
    document.getElementById('pos-clear')?.addEventListener('click', () => {
        cart.clear();
        renderCart();
    });

    categoryFilters.forEach((button) => {
        button.addEventListener('click', () => {
            activeCategory = button.dataset.posCategoryFilter || 'all';
            categoryFilters.forEach((filter) => {
                const isActive = filter === button;
                filter.classList.toggle('active', isActive);
                filter.setAttribute('aria-pressed', String(isActive));
            });
            filterCatalog();
        });
    });
    searchInput?.addEventListener('input', filterCatalog);

    const paymentInput = document.getElementById('pos-payment-method');
    document.querySelectorAll('[data-payment-method]').forEach((button) => {
        button.addEventListener('click', () => {
            if (paymentInput) paymentInput.value = button.dataset.paymentMethod || 'cash';
            document.querySelectorAll('[data-payment-method]').forEach((methodButton) => {
                const isActive = methodButton === button;
                methodButton.classList.toggle('active', isActive);
                methodButton.setAttribute('aria-pressed', String(isActive));
            });
        });
    });

    filterCatalog();
    renderCart();
});
