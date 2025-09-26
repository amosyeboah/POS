<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS - New Sale</title>
    <style>
        /* Base styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            color: #333;
        }
        
        .pos-container {
            display: grid;
            grid-template-columns: 250px 1fr 350px;
            grid-template-rows: 70px 1fr;
            height: 100vh;
            grid-template-areas:
                "header header header"
                "categories products cart";
        }
        
        /* Header */
        .pos-header {
            grid-area: header;
            background: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            z-index: 10;
        }
        
        .header-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .logo {
            font-weight: 800;
            font-size: 1.5rem;
            color: #4a6cf7;
        }
        
        .store-info {
            font-weight: 500;
        }
        
        .header-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .cashier-info {
            color: #666;
        }
        
        .btn-history {
            background: #f1f5f9;
            border: none;
            padding: 8px 15px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
        }
        
        /* Categories sidebar */
        .categories-sidebar {
            grid-area: categories;
            background: white;
            border-right: 1px solid #e2e8f0;
            padding: 20px 0;
            overflow-y: auto;
        }
        
        .sidebar-title {
            padding: 0 20px 15px;
            font-weight: 600;
            color: #64748b;
            font-size: 0.9rem;
        }
        
        .category-list {
            list-style: none;
        }
        
        .category-item {
            padding: 12px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .category-item:hover, .category-item.active {
            background: #f1f5f9;
        }
        
        .category-icon {
            width: 24px;
            height: 24px;
            border-radius: 6px;
            background: #e2e8f0;
        }
        
        /* Products area */
        .products-section {
            grid-area: products;
            padding: 20px;
            overflow-y: auto;
        }
        
        .search-bar {
            margin-bottom: 20px;
        }
        
        .search-container {
            position: relative;
            display: flex;
            align-items: center;
        }
        
        .search-icon {
            position: absolute;
            left: 12px;
            color: #94a3b8;
        }
        
        .search-input {
            width: 100%;
            padding: 12px 12px 12px 40px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
        }
        
        .barcode-btn {
            position: absolute;
            right: 10px;
            background: none;
            border: none;
            font-size: 1.2rem;
            cursor: pointer;
        }
        
        .products-grid {
            margin-top: 20px;
        }
        
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 16px;
        }
        
        .product-card {
            background: white;
            border-radius: 12px;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .product-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .product-image {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .product-name {
            font-weight: 500;
            margin-bottom: 5px;
        }
        
        .product-price {
            color: #4a6cf7;
            font-weight: 600;
        }
        
        /* Cart Sidebar Styles */
        .cart-sidebar {
            grid-area: cart;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
            border-left: 1px solid #d1d9e6;
            box-shadow: -5px 0 15px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
        }
        
        .cart-header {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            padding: 15px;
            border-radius: 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .cart-title {
            font-size: 1.2rem;
            font-weight: 600;
        }
        
        .order-number {
            background: rgba(255, 255, 255, 0.2);
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.9rem;
        }
        
        .btn-clear {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            border-radius: 6px;
            padding: 6px 12px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .btn-clear:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        
        .cart-items {
            flex: 1;
            overflow-y: auto;
            padding: 10px;
            background: white;
            margin: 10px;
            border-radius: 8px;
            box-shadow: inset 0 0 5px rgba(0,0,0,0.05);
        }
        
        .cart-item {
            background: white;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border-left: 4px solid #6a11cb;
            animation: slideIn 0.3s ease;
            transition: all 0.3s ease;
        }
        
        .cart-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.12);
        }
        
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(20px); }
            to { opacity: 1; transform: translateX(0); }
        }
        
        .item-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        
        .item-name {
            font-weight: 600;
            color: #2d3748;
        }
        
        .item-price {
            color: #6a11cb;
            font-weight: 600;
        }
        
        .item-quantity-control {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .qty-btn {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            border: none;
            background: #6a11cb;
            color: white;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .qty-btn:hover {
            background: #2575fc;
            transform: scale(1.1);
        }
        
        .btn-remove {
            margin-left: auto;
            background: #ff4757;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 4px 8px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .btn-remove:hover {
            background: #ff3742;
            transform: scale(1.05);
        }
        
        .item-quantity {
            font-weight: 600;
            min-width: 20px;
            text-align: center;
        }
        
        .empty-cart {
            text-align: center;
            padding: 20px;
            color: #a0aec0;
            font-style: italic;
        }
        
        .cart-footer {
            background: white;
            padding: 15px;
            border-top: 1px solid #e2e8f0;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.05);
        }
        
        .action-buttons {
            display: flex;
            gap: 8px;
            margin-bottom: 15px;
        }
        
        .btn-action {
            flex: 1;
            padding: 8px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            background: white;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .btn-action:hover {
            background: #f7fafc;
            border-color: #cbd5e0;
        }
        
        .order-totals {
            background: #f8fafc;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        
        .grand-total {
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
            font-weight: 700;
            font-size: 1.1rem;
            color: #2d3748;
        }
        
        .btn-checkout {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 12px;
            width: 100%;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(37, 117, 252, 0.2);
        }
        
        .btn-checkout:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(37, 117, 252, 0.3);
        }
        
        .btn-checkout:disabled {
            background: #cbd5e0;
            cursor: not-allowed;
        }
        
        /* Payment Options */
        .payment-options {
            display: none;
            background: white;
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            animation: fadeIn 0.3s ease;
        }
        
        .payment-title {
            font-weight: 600;
            margin-bottom: 12px;
            text-align: center;
            color: #2d3748;
        }
        
        .payment-methods {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 15px;
        }
        
        .payment-method {
            flex: 1;
            padding: 10px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .payment-method:hover {
            border-color: #6a11cb;
            transform: translateY(-2px);
        }
        
        .payment-method.selected {
            border-color: #6a11cb;
            background: rgba(106, 17, 203, 0.05);
        }
        
        .payment-icon {
            font-size: 1.5rem;
            margin-bottom: 5px;
        }
        
        .payment-label {
            font-size: 0.8rem;
        }
        
        .complete-payment {
            background: #6a11cb;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 12px;
            width: 100%;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .complete-payment:hover {
            background: #2575fc;
            transform: translateY(-2px);
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Success Message */
        .success-message {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            z-index: 1000;
            animation: fadeIn 0.3s ease;
        }
        
        .success-icon {
            font-size: 3rem;
            color: #4ade80;
            margin-bottom: 15px;
        }
        
        .success-text {
            font-size: 1.2rem;
            margin-bottom: 20px;
            color: #334155;
        }
        
        .btn-continue {
            background: #6a11cb;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s;
        }
        
        .btn-continue:hover {
            background: #2575fc;
        }
        
        /* Overlay for modals */
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 999;
        }
    </style>
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
                <button class="btn-history">History</button>
            </div>
        </header>

        <!-- Left Sidebar - Categories -->
        <aside class="categories-sidebar">
            <div class="sidebar-title">Categories</div>
            <ul class="category-list">
                <li class="category-item active">
                    <div class="category-icon"></div>
                    All Categories
                </li>
                <li class="category-item">
                    <div class="category-icon"></div>
                    Sandwiches
                </li>
                <li class="category-item">
                    <div class="category-icon"></div>
                    Beverages
                </li>
                <li class="category-item">
                    <div class="category-icon"></div>
                    Breakfast
                </li>
                <li class="category-item">
                    <div class="category-icon"></div>
                    Pastries
                </li>
            </ul>
        </aside>

        <!-- Main Product Area -->
        <main class="products-section">
            <div class="search-bar">
                <div class="search-container">
                    <div class="search-icon">🔍</div>
                    <input type="text" class="search-input" placeholder="Search products...">
                    <button class="barcode-btn">📷</button>
                </div>
            </div>
            
            <div class="products-grid">
                <div class="product-grid">
                    <!-- Sample products -->
                    <div class="product-card" data-id="1" data-name="Turkey Sandwich" data-price="9.50">
                        <div class="product-image">🥪</div>
                        <div class="product-name">Turkey Sandwich</div>
                        <div class="product-price">$9.50</div>
                    </div>
                    <div class="product-card" data-id="2" data-name="Fresh Orange Juice" data-price="3.95">
                        <div class="product-image">🧃</div>
                        <div class="product-name">Fresh Orange Juice</div>
                        <div class="product-price">$3.95</div>
                    </div>
                    <div class="product-card" data-id="3" data-name="Avocado Toast" data-price="7.50">
                        <div class="product-image">🥑</div>
                        <div class="product-name">Avocado Toast</div>
                        <div class="product-price">$7.50</div>
                    </div>
                    <div class="product-card" data-id="4" data-name="Croissant" data-price="3.25">
                        <div class="product-image">🥐</div>
                        <div class="product-name">Croissant</div>
                        <div class="product-price">$3.25</div>
                    </div>
                    <div class="product-card" data-id="5" data-name="Cappuccino" data-price="4.50">
                        <div class="product-image">☕</div>
                        <div class="product-name">Cappuccino</div>
                        <div class="product-price">$4.50</div>
                    </div>
                    <div class="product-card" data-id="6" data-name="Greek Salad" data-price="8.75">
                        <div class="product-image">🥗</div>
                        <div class="product-name">Greek Salad</div>
                        <div class="product-price">$8.75</div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Right Sidebar - Cart -->
        <aside class="cart-sidebar">
            <div class="cart-header">
                <div class="cart-title">Current Order</div>
                <div class="order-number" id="order-count">0</div>
                <button class="btn-clear" id="clear-cart">Clear</button>
            </div>
            
            <div class="cart-items" id="cart-items">
                <div class="empty-cart">Cart is empty</div>
                <!-- Cart items will be added here dynamically -->
            </div>
            
            <div class="cart-footer">
                <div class="action-buttons">
                    <button class="btn-action">Discount</button>
                    <button class="btn-action">Customer</button>
                    <button class="btn-action">Hold</button>
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
                
                <button class="btn-checkout" id="checkout-btn" disabled>Checkout</button>
                
                <!-- Payment Options (initially hidden) -->
                <div class="payment-options" id="payment-options">
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
                    <button class="complete-payment" id="complete-payment">Complete Payment</button>
                </div>
            </div>
        </aside>
    </div>

    <!-- Success Message (initially hidden) -->
    <div class="success-message" id="success-message">
        <div class="success-icon">✓</div>
        <div class="success-text" id="success-text">Order completed successfully!</div>
        <button class="btn-continue" id="continue-btn">Continue</button>
    </div>

    <!-- Overlay for modals -->
    <div class="overlay" id="overlay"></div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize cart
            let cart = [];
            let orderCount = 0;
            const TAX_RATE = 0.085; // 8.5%
            
            // DOM elements
            const cartItemsContainer = document.getElementById('cart-items');
            const subtotalElement = document.getElementById('subtotal');
            const taxElement = document.getElementById('tax');
            const totalElement = document.getElementById('total');
            const checkoutBtn = document.getElementById('checkout-btn');
            const clearCartBtn = document.getElementById('clear-cart');
            const paymentOptions = document.getElementById('payment-options');
            const paymentMethods = document.querySelectorAll('.payment-method');
            const completePaymentBtn = document.getElementById('complete-payment');
            const successMessage = document.getElementById('success-message');
            const successText = document.getElementById('success-text');
            const continueBtn = document.getElementById('continue-btn');
            const overlay = document.getElementById('overlay');
            const orderCountElement = document.getElementById('order-count');
            
            // Product event listeners
            document.querySelectorAll('.product-card').forEach(product => {
                product.addEventListener('click', () => {
                    const id = product.getAttribute('data-id');
                    const name = product.getAttribute('data-name');
                    const price = parseFloat(product.getAttribute('data-price'));
                    
                    addToCart(id, name, price);
                });
            });
            
            // Clear cart button
            clearCartBtn.addEventListener('click', clearCart);
            
            // Checkout button
            checkoutBtn.addEventListener('click', () => {
                paymentOptions.style.display = 'block';
            });
            
            // Payment method selection
            paymentMethods.forEach(method => {
                method.addEventListener('click', () => {
                    paymentMethods.forEach(m => m.classList.remove('selected'));
                    method.classList.add('selected');
                });
            });
            
            // Complete payment
            completePaymentBtn.addEventListener('click', processPayment);
            
            // Continue button after success
            continueBtn.addEventListener('click', () => {
                successMessage.style.display = 'none';
                overlay.style.display = 'none';
            });
            
            // Add to cart function
            function addToCart(id, name, price) {
                // Check if item already exists in cart
                const existingItem = cart.find(item => item.id === id);
                
                if (existingItem) {
                    existingItem.quantity += 1;
                } else {
                    cart.push({
                        id,
                        name,
                        price,
                        quantity: 1
                    });
                }
                
                updateCart();
            }
            
            // Update cart UI
            function updateCart() {
                // Clear cart items container
                cartItemsContainer.innerHTML = '';
                
                if (cart.length === 0) {
                    cartItemsContainer.innerHTML = '<div class="empty-cart">Cart is empty</div>';
                    checkoutBtn.disabled = true;
                } else {
                    // Add each item to cart
                    cart.forEach(item => {
                        const cartItem = document.createElement('div');
                        cartItem.className = 'cart-item';
                        cartItem.innerHTML = `
                            <div class="item-details">
                                <div class="item-name">${item.name}</div>
                                <div class="item-price">$${(item.price * item.quantity).toFixed(2)}</div>
                            </div>
                            <div class="item-quantity-control">
                                <button class="qty-btn minus" data-id="${item.id}">-</button>
                                <span class="item-quantity">${item.quantity}</span>
                                <button class="qty-btn plus" data-id="${item.id}">+</button>
                                <button class="btn-remove" data-id="${item.id}">×</button>
                            </div>
                        `;
                        cartItemsContainer.appendChild(cartItem);
                    });
                    
                    checkoutBtn.disabled = false;
                    
                    // Add event listeners to quantity buttons
                    document.querySelectorAll('.qty-btn.minus').forEach(btn => {
                        btn.addEventListener('click', (e) => {
                            const id = e.target.getAttribute('data-id');
                            updateQuantity(id, -1);
                        });
                    });
                    
                    document.querySelectorAll('.qty-btn.plus').forEach(btn => {
                        btn.addEventListener('click', (e) => {
                            const id = e.target.getAttribute('data-id');
                            updateQuantity(id, 1);
                        });
                    });
                    
                    document.querySelectorAll('.btn-remove').forEach(btn => {
                        btn.addEventListener('click', (e) => {
                            const id = e.target.getAttribute('data-id');
                            removeFromCart(id);
                        });
                    });
                }
                
                // Update totals
                updateTotals();
                
                // Update order count
                orderCount = cart.reduce((total, item) => total + item.quantity, 0);
                orderCountElement.textContent = orderCount;
            }
            
            // Update item quantity
            function updateQuantity(id, change) {
                const item = cart.find(item => item.id === id);
                
                if (item) {
                    item.quantity += change;
                    
                    if (item.quantity <= 0) {
                        removeFromCart(id);
                    } else {
                        updateCart();
                    }
                }
            }
            
            // Remove item from cart
            function removeFromCart(id) {
                cart = cart.filter(item => item.id !== id);
                updateCart();
            }
            
            // Clear cart
            function clearCart() {
                cart = [];
                updateCart();
                paymentOptions.style.display = 'none';
            }
            
            // Update order totals
            function updateTotals() {
                const subtotal = cart.reduce((total, item) => total + (item.price * item.quantity), 0);
                const tax = subtotal * TAX_RATE;
                const total = subtotal + tax;
                
                subtotalElement.textContent = `$${subtotal.toFixed(2)}`;
                taxElement.textContent = `$${tax.toFixed(2)}`;
                totalElement.textContent = `$${total.toFixed(2)}`;
            }
            
            // Process payment
            function processPayment() {
                const selectedMethod = document.querySelector('.payment-method.selected');
                
                if (!selectedMethod) {
                    alert('Please select a payment method');
                    return;
                }
                
                const method = selectedMethod.getAttribute('data-method');
                
                // Hide payment options
                paymentOptions.style.display = 'none';
                
                // Show success message
                const orderTotal = totalElement.textContent;
                successText.textContent = `Order completed successfully with ${method} payment! Total: ${orderTotal}`;
                successMessage.style.display = 'block';
                overlay.style.display = 'block';
                
                // Clear cart
                clearCart();
            }
            
            // Initialize cart
            updateCart();
        });
    </script>
</body>
</html>