<?php
session_start();
require_once '../db.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['tenant_id'])) {
    header('Location: ../login.php');
    exit;
}
$tenant_id = $_SESSION['tenant_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS - New Sale</title>
    <link rel="stylesheet" href="desktop.css">
    <link rel="stylesheet" href="d.css">
    
    <!-- Add base URL for API paths -->
    <script>
        // Set the base API URL with absolute path
        const tenant_id = <?= json_encode($tenant_id) ?>;
        const BASE_API_URL = '<?php 
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
            $host = $_SERVER['HTTP_HOST'];
            $path = rtrim(dirname(dirname($_SERVER['PHP_SELF'])), '/');
            echo $protocol . $host . $path . '/api';
        ?>';
        console.log('API URL:', BASE_API_URL, 'Tenant:', tenant_id);
    </script>
</head>
<body>
    <div class="pos-container">
        <!-- Header Section -->
        <header class="pos-header">
            <div class="header-left">
                <div class="logo">POS</div>
                <div class="store-info">Modern Coffee House</div>
            </div>
            <div class="header-right">
                <div class="cashier-info">Cashier: John Doe</div>
                <div class="order-count">Orders: <span id="header-order-count">0</span></div>
                <a href="#" class="btn-history">History</a>
            </div>
        </header>

        <!-- Left Sidebar - Categories -->
        <aside class="categories-sidebar">
            <div class="sidebar-title">Categories</div>
            <ul class="category-list" id="category-list">
                <!-- Dynamic categories will be injected here -->
            </ul>
        </aside>

        <!-- Main Product Area -->
        <main class="products-section">
            <div class="search-bar">
                <div class="search-container">
                    <div class="search-icon">🔍</div>
                    <input type="text" id="product-search" class="search-input" placeholder="Search products..." autocomplete="off">
                    <button id="barcode-scan" class="barcode-btn">📷</button>
                </div>
            </div>
            
            <div class="products-grid">
                <div class="product-grid" id="product-grid">
                    <!-- Products will be loaded dynamically here -->
                </div>
            </div>
        </main>

        <!-- Right Sidebar - Cart -->
        <aside class="cart-sidebar">
            <div class="cart-header">
                <div class="cart-title">Current Order</div>
                <div class="order-number" id="order-number">0</div>
                <button id="clear-order" class="btn-clear">Clear</button>
            </div>
            
            <div class="cart-items" id="cart-items">
                <div class="empty-cart">Cart is empty</div>
            </div>
            
            <div class="cart-footer">
                <div class="action-buttons">
                    <button id="btn-discount" class="btn-action">Discount</button>
                    <button id="btn-customer" class="btn-action">Customer</button>
                    <button id="btn-hold" class="btn-action">Hold</button>
                </div>
                
                <div class="order-totals">
                    <div class="total-row">
                        <span>Subtotal</span>
                        <span id="subtotal">$0.00</span>
                    </div>
                    <div class="total-row">
                        <span>Tax (8.5%)</span>
                        <span id="tax">$0.00</span>
                    </div>
                    <div class="total-row grand-total">
                        <span>Total</span>
                        <span id="total">$0.00</span>
                    </div>
                </div>
                
                <button id="btn-checkout" class="btn-checkout" disabled>Checkout</button>
                
                <!-- Payment Options (initially hidden) -->
                <div id="payment-options" class="payment-options">
                    <div class="payment-title">Select Payment Method</div>
                    <div class="payment-methods">
                        <div class="payment-method" data-method="cash">
                            <div class="payment-icon">💰</div>
                            <div class="payment-label">Cash</div>
                        </div>
                        <div class="payment-method" data-method="card">
                            <div class="payment-icon">💳</div>
                            <div class="payment-label">Card</div>
                        </div>
                        <div class="payment-method" data-method="mobile">
                            <div class="payment-icon">📱</div>
                            <div class="payment-label">Mobile</div>
                        </div>
                    </div>
                    <button id="complete-payment" class="complete-payment">Complete Payment</button>
                </div>
            </div>
        </aside>
    </div>

    <!-- Success Modal -->
    <div id="success-modal" class="modal">
        <div class="modal-content success-modal-content">
            <div class="modal-header">
                <h3>Payment Successful!</h3>
            </div>
            <div class="modal-body">
                <div class="success-icon">✓</div>
                <div class="success-message">
                    <p>Payment completed with <span id="payment-method-used"></span></p>
                    <p>Total: <span id="payment-total-amount"></span></p>
                </div>
                <button id="continue-button" class="btn-continue">Continue</button>
            </div>
        </div>
    </div>

    <!-- Product Selection Modal -->
    <div id="product-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Select Product</h3>
                <span class="close-modal">&times;</span>
            </div>
            <div class="modal-body" id="product-list">
                <!-- Products will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Discount Modal -->
    <div id="discount-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Apply Discount</h3>
                <span class="close-modal">&times;</span>
            </div>
            <div class="modal-body">
                <!-- Discount form would go here -->
            </div>
        </div>
    </div>

    <!-- Loading spinner -->
    <div id="loading-spinner" class="loading-overlay" style="display: none;">
        <div class="spinner"></div>
    </div>

    <!-- Notification Modal -->
    <div id="notification-modal" class="notification-modal">
        <div class="notification-content">
            <div class="notification-icon">
                <span class="success-icon" style="display: none;">✓</span>
                <span class="error-icon" style="display: none;">✕</span>
            </div>
            <div class="notification-message"></div>
            <button class="notification-close">OK</button>
        </div>
    </div>

    <script src="../assets/js/utils.js"></script>
    <script>
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
            const continueBtn = document.getElementById('continue-button');
            if (continueBtn) {
                continueBtn.addEventListener('click', () => {
                    document.getElementById('success-modal').style.display = 'none';
                });
            }
        }

        // -----------------------
        // Cart management section
        // -----------------------
        const TAX_RATE = 0.085; // 8.5%
        let cart = [];
        let orderNumber = 1;
        let orderCount = 0;

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

            // Update order count in header
            updateOrderCount();

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

            // Update totals and checkout button
            updateTotals();
            updateCheckoutButton();
        }

        function updateOrderCount() {
            const orderCountEl = document.getElementById('header-order-count');
            if (orderCountEl) {
                orderCountEl.textContent = orderCount;
            }
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

        function processPayment() {
            const selectedMethod = document.querySelector('.payment-method.selected');
            if (!selectedMethod) {
                showToast('Please select a payment method', 'error');
                return;
            }
            
            const paymentMethod = selectedMethod.dataset.method;
            completeCheckout(paymentMethod);
        }

        function showSuccessModal(paymentMethod, total) {
            const modal = document.getElementById('success-modal');
            const methodEl = document.getElementById('payment-method-used');
            const totalEl = document.getElementById('payment-total-amount');
            
            if (methodEl && totalEl && modal) {
                methodEl.textContent = paymentMethod;
                totalEl.textContent = formatCurrency(total);
                modal.style.display = 'block';
            }
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
                    // Show success modal
                    showSuccessModal(paymentMethod, total);
                    
                    // Increment order count
                    orderCount++;
                    updateOrderCount();
                    
                    // Reset cart and UI
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
        document.getElementById('clear-order')?.addEventListener('click', () => {
            clearCart();
            orderCount = 0;
            updateOrderCount();
        });
        
        document.getElementById('btn-checkout')?.addEventListener('click', () => {
            if (cart.length === 0) return;
            showPaymentOptions();
        });

        // Initialize cart UI on load
        renderCart();
    </script>
</body>
</html>