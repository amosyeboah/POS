// Use a single hardcoded icon for all product images for now
document.addEventListener('DOMContentLoaded', () => {
    loadCategoriesUI();
    loadProductsUI();
    setupSearch();
    setupPaymentHandlers();
    setupSuccessModal();
});

async function loadCategoriesUI() {
    const list = document.getElementById('category-list');
    if (!list) return;

    try {
        const res = await fetch(`${BASE_API_URL}/get_categories.php?tenant_id=${tenant_id}`);
        const data = await res.json();
        list.innerHTML = '';

        // All categories item
        const allLi = document.createElement('li');
        allLi.className = 'category-item active';
        allLi.dataset.category = 'all';
        allLi.innerHTML = `<div class="category-icon all"></div>All Categories`;
        allLi.addEventListener('click', () => {
            setActiveCategory(allLi);
            loadProductsUI();
        });
        list.appendChild(allLi);

        if (data.success && Array.isArray(data.categories)) {
            data.categories.forEach(cat => {
                const li = document.createElement('li');
                li.className = 'category-item';
                li.dataset.category = cat.id;
                li.innerHTML = `<div class="category-icon"></div>${cat.name}`;
                li.addEventListener('click', () => {
                    setActiveCategory(li);
                    loadProductsUI(cat.id);
                });
                list.appendChild(li);
            });
        }
    } catch (e) {
        console.error('Failed to load categories', e);
    }
}

function setActiveCategory(activeLi) {
    document.querySelectorAll('#category-list .category-item').forEach(el => el.classList.remove('active'));
    activeLi.classList.add('active');
}

async function loadProductsUI(categoryId = null) {
    const grid = document.getElementById('product-grid');
    if (!grid) return;
    grid.innerHTML = '<div class="loading">Loading...</div>';

    try {
        const url = new URL(`${BASE_API_URL}/get_products.php`);
        url.searchParams.set('tenant_id', tenant_id);
        if (categoryId) url.searchParams.set('category_id', categoryId);

        const res = await fetch(url.toString());
        const data = await res.json();

        if (!data.success) throw new Error(data.error || 'Failed to load products');

        renderProductsGrid(data.products || []);
    } catch (e) {
        console.error('Failed to load products', e);
        grid.innerHTML = '<div class="error">Failed to load products.</div>';
    }
}

function renderProductsGrid(products) {
    const grid = document.getElementById('product-grid');
    grid.innerHTML = '';

    if (!products.length) {
        grid.innerHTML = '<div class="empty">No products found</div>';
        return;
    }

    products.forEach(p => {
        const card = document.createElement('div');
        card.className = 'product-card';
        card.dataset.productId = p.id;
        card.innerHTML = `
            <div class="product-image">🧊</div>
            <div class="product-name">${p.name}</div>
            <div class="product-price">${typeof formatCurrency === 'function' ? formatCurrency(parseFloat(p.price)) : '$' + parseFloat(p.price).toFixed(2)}</div>
        `;
        // Add to cart when clicked
        card.addEventListener('click', () => addToCart(p));
        grid.appendChild(card);
    });
}

function setupSearch() {
    const input = document.getElementById('product-search');
    if (!input) return;
    input.addEventListener('input', () => {
        const term = input.value.trim().toLowerCase();
        document.querySelectorAll('#product-grid .product-card').forEach(card => {
            const name = card.querySelector('.product-name')?.textContent?.toLowerCase() || '';
            card.style.display = name.includes(term) ? '' : 'none';
        });
    });
}

function setupPaymentHandlers() {
    // Payment method selection
    document.querySelectorAll('.payment-method').forEach(method => {
        method.addEventListener('click', () => {
            document.querySelectorAll('.payment-method').forEach(m => m.classList.remove('selected'));
            method.classList.add('selected');
        });
    });
    
    // Complete payment button
    document.getElementById('complete-payment')?.addEventListener('click', processPayment);
}

function setupSuccessModal() {
    // Continue button handler
    document.getElementById('continue-btn')?.addEventListener('click', () => {
        hideSuccessModal();
    });
}

// -----------------------
// Cart management section
// -----------------------
const TAX_RATE = 0.085; // 8.5%
let cart = [];
let orderNumber = 1;

// Update the order count in the header
function updateOrderCount() {
    const totalItems = cart.reduce((sum, item) => sum + item.qty, 0);
    const orderCountEl = document.getElementById('order-item-count');
    if (orderCountEl) {
        orderCountEl.textContent = totalItems;
    }
}

// Add product to cart or increase quantity
function addToCart(product) {
    const existing = cart.find(it => it.id === product.id);
    if (existing) {
        // optional: respect stock if provided
        if (typeof product.stock === 'number' && existing.qty >= product.stock) {
            showToast('Not enough stock available', 'error');
            return;
        }
        existing.qty += 1;
    } else {
        if (typeof product.stock === 'number' && product.stock <= 0) {
            showToast('Product out of stock', 'error');
            return;
        }
        cart.push({
            id: product.id,
            name: product.name,
            price: parseFloat(product.price),
            qty: 1
        });
    }
    renderCart();
}

// Update item quantity by delta
function changeQty(productId, delta) {
    const idx = cart.findIndex(it => it.id === productId);
    if (idx === -1) return;
    cart[idx].qty += delta;
    if (cart[idx].qty <= 0) {
        cart.splice(idx, 1);
    }
    renderCart();
}

// Remove item from cart
function removeFromCart(productId) {
    cart = cart.filter(it => it.id !== productId);
    renderCart();
}

// Clear entire cart
function clearCart() {
    cart = [];
    renderCart();
    hidePaymentOptions();
}

// Render the cart sidebar
function renderCart() {
    const container = document.getElementById('cart-items');
    if (!container) return;

    container.innerHTML = '';
    if (cart.length === 0) {
        container.innerHTML = '<div class="empty-cart">Cart is empty</div>';
    } else {
        // Build each item row
        cart.forEach(item => {
            const row = document.createElement('div');
            row.className = 'cart-item';
            row.dataset.productId = item.id;
            row.innerHTML = `
                <div class="item-details">
                    <div class="item-name">${item.name}</div>
                    <div class="item-price">${formatCurrency(item.price * item.qty)}</div>
                </div>
                <div class="item-quantity-control">
                    <button class="qty-btn minus" data-action="minus">-</button>
                    <span class="item-quantity">${item.qty}</span>
                    <button class="qty-btn plus" data-action="plus">+</button>
                    <button class="btn-remove" data-action="remove">×</button>
                </div>
            `;
            container.appendChild(row);
        });
    }

    // Attach event delegation for qty and remove
    container.onclick = (e) => {
        const actionBtn = e.target.closest('[data-action]');
        if (!actionBtn) return;
        const row = e.target.closest('.cart-item');
        if (!row) return;
        const productId = parseInt(row.dataset.productId, 10);
        const action = actionBtn.dataset.action;
        if (action === 'minus') changeQty(productId, -1);
        if (action === 'plus') changeQty(productId, 1);
        if (action === 'remove') removeFromCart(productId);
    };

    // Update totals, checkout button, and order count
    updateTotals();
    updateCheckoutButton();
    updateOrderCount();
}

function updateTotals() {
    const subtotal = cart.reduce((sum, it) => sum + (it.price * it.qty), 0);
    const tax = subtotal * TAX_RATE;
    const total = subtotal + tax;

    const subtotalEl = document.getElementById('subtotal');
    const taxEl = document.getElementById('tax');
    const totalEl = document.getElementById('total');
    if (subtotalEl) subtotalEl.textContent = formatCurrency(subtotal);
    if (taxEl) taxEl.textContent = formatCurrency(tax);
    if (totalEl) totalEl.textContent = formatCurrency(total);
}

function updateCheckoutButton() {
    const checkoutBtn = document.getElementById('btn-checkout');
    if (!checkoutBtn) return;
    checkoutBtn.disabled = cart.length === 0;
}

function showPaymentOptions() {
    const paymentOptions = document.getElementById('payment-options');
    if (paymentOptions) {
        paymentOptions.style.display = 'block';
    }
}

function hidePaymentOptions() {
    const paymentOptions = document.getElementById('payment-options');
    if (paymentOptions) {
        paymentOptions.style.display = 'none';
    }
}

function showSuccessModal(orderData) {
    const modal = document.getElementById('success-modal');
    const orderNumberEl = document.getElementById('success-order-number');
    const paymentMethodEl = document.getElementById('success-payment-method');
    const totalEl = document.getElementById('success-total');
    
    if (modal && orderNumberEl && paymentMethodEl && totalEl) {
        orderNumberEl.textContent = orderData.order_id;
        paymentMethodEl.textContent = orderData.payment_method.charAt(0).toUpperCase() + orderData.payment_method.slice(1);
        totalEl.textContent = formatCurrency(orderData.total);
        
        modal.style.display = 'block';
    }
}

function hideSuccessModal() {
    const modal = document.getElementById('success-modal');
    if (modal) {
        modal.style.display = 'none';
    }
}

function processPayment() {
    const selectedMethod = document.querySelector('.payment-method.selected');
    if (!selectedMethod) {
        showToast('Please select a payment method', 'error');
        return;
    }
    
    const paymentMethod = selectedMethod.dataset.method;
    completeCheckout(paymentMethod);
}

async function completeCheckout(paymentMethod) {
    if (cart.length === 0) return;

    // Build payload matching assets/js/cashier.js and api/save_order.php expectations
    const subtotal = cart.reduce((sum, it) => sum + (it.price * it.qty), 0);
    const tax = subtotal * TAX_RATE;
    const total = subtotal + tax;
    const payload = {
        items: cart.map(it => ({ id: it.id, name: it.name, price: it.price, quantity: it.qty })),
        subtotal: parseFloat(subtotal.toFixed(2)),
        tax: parseFloat(tax.toFixed(2)),
        total: parseFloat(total.toFixed(2)),
        payment_method: paymentMethod
    };

    try {
        const res = await fetch(`${BASE_API_URL}/save_order.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();

        if (data.success) {
            // Show success modal with order details
            showSuccessModal({
                order_id: data.order_id,
                payment_method: paymentMethod,
                total: total
            });
            
            // Update order number and clear cart
            orderNumber++;
            document.getElementById('order-number').textContent = orderNumber;
            clearCart();
            hidePaymentOptions();
        } else {
            showToast(`Failed to save order: ${data.error || 'Unknown error'}`, 'error');
        }
    } catch (err) {
        console.error('Checkout error:', err);
        showToast('Network error during checkout', 'error');
    }
}

// Hook up clear and checkout buttons
document.getElementById('clear-order')?.addEventListener('click', clearCart);
document.getElementById('btn-checkout')?.addEventListener('click', () => {
    if (cart.length === 0) return;
    showPaymentOptions();
});

// Initialize cart UI on load
renderCart();



// Open modal
document.getElementById('btn-customer').onclick = () => {
    document.getElementById('customer-modal').style.display = 'block';
};

// Close modal
document.querySelector('.close-customer').onclick = () => {
    document.getElementById('customer-modal').style.display = 'none';
};

// Search customers
document.getElementById('customer-search').addEventListener('input', async function () {
    const query = this.value.trim();
    if (!query) {
        document.getElementById('customer-results').innerHTML = '';
        return;
    }

    const res = await fetch(`${BASE_API_URL}/search_customers.php?tenant_id=${tenant_id}&q=${query}`);
    const data = await res.json();
    
    if (data.success) {
        const resultsDiv = document.getElementById('customer-results');
        resultsDiv.innerHTML = '';
        data.customers.forEach(cust => {
            const div = document.createElement('div');
            div.textContent = `${cust.name} (${cust.phone || 'No phone'})`;
            div.onclick = () => selectCustomer(cust);
            resultsDiv.appendChild(div);
        });
    }
});

// Add new customer
document.getElementById('new-customer-form').onsubmit = async (e) => {
    e.preventDefault();
    const name = document.getElementById('cust-name').value;
    const phone = document.getElementById('cust-phone').value;
    const email = document.getElementById('cust-email').value;

    const res = await fetch(`${BASE_API_URL}/add_customer.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ tenant_id, name, phone, email })
    });
    const data = await res.json();

    if (data.success) {
        selectCustomer(data.customer);
    } else {
        alert(data.error);
    }
};

// Select customer
function selectCustomer(cust) {
    selectedCustomer = cust;
    document.getElementById('selected-customer').textContent = cust.name;
    document.getElementById('customer-modal').style.display = 'none';
}
