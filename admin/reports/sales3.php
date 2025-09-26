<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modern POS System</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="sales3.css">
    <style>
        .view-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1.5rem;
    padding: 0.5rem 1rem;
    background: #fff;
    border-bottom: 1px solid #eee;
}
.header-left {
    display: flex;
    align-items: center;
    gap: 1.5rem;
}
.header-left .store-name,
.header-left .cashier-name {
    font-weight: 500;
    font-size: 1.05rem;
    color: #333;
}
.header-left .report-link {
    font-size: 0.95rem;
    padding: 0.3rem 0.8rem;
}
.header-center {
    flex: 1;
    display: flex;
    justify-content: center;
}
.search-bar {
    width: 260px;
    padding: 0.4rem 0.8rem;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 1rem;
}
.header-actions {
    display: flex;
    align-items: center;
    gap: 1rem;
}
.products-grid {
    max-height: 520px;      /* Adjust height as needed */
    overflow-y: auto;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 1rem;
    padding: 1rem 0.5rem;
    background: #f9f9f9;
    border-radius: 8px;
}
    </style>
</head>
<body>
    <div class="app-container">
        <!-- Sidebar Navigation -->
        <nav class="sidebar">
            <div class="logo">
                <h1>POS</h1>
            </div>
            <ul class="category-links">
                <li class="active" data-category="all"><i class="icon">📦</i> <span>All Categories</span></li>
                <li data-category="coffee"><i class="icon">☕</i> <span>Coffee</span></li>
                <li data-category="pastry"><i class="icon">🥐</i> <span>Pastry</span></li>
                <li data-category="food"><i class="icon">🍽️</i> <span>Food</span></li>
                <li data-category="drinks"><i class="icon">🥤</i> <span>Drinks</span></li>
                <!-- Add more categories as needed -->
            </ul>
        </nav>

        <!-- Main Content Area -->
        <main class="main-content">
            <!-- POS View -->
            <section id="pos-view" class="view active">
                <header class="view-header">
                    <div class="header-left">
                        <span class="store-name"><i class="fas fa-store"></i> Modern Coffee House</span>
                        <span class="cashier-name"><i class="fas fa-user"></i> Cashier: John Doe</span>
                        <a href="../report.php" class="report-link btn btn-outline"><i class="fas fa-chart-line"></i> History</a>
                    </div>
                    <div class="header-center">
                        <input type="text" id="product-search" class="search-bar" placeholder="Search products...">
                    </div>
                    <div class="header-actions">
                        <button id="clear-cart" class="btn btn-outline">Clear</button>
                    </div>
                </header>

                <div class="pos-container">
                    <div class="products-grid">
                        <!-- Product items will be dynamically populated -->
                    </div>

                    <div class="cart-panel">
                        <div class="cart-header">
                            <h3>Current Order</h3>
                            <span id="cart-count" class="badge">0</span>
                        </div>
                        <div class="cart-items">
                            <!-- Cart items will be populated here -->
                        </div>
                        <div class="cart-summary">
                            <div class="summary-row">
                                <span>Subtotal</span>
                                <span id="subtotal">$0.00</span>
                            </div>
                            <div class="summary-row">
                                <span>Tax (8.5%)</span>
                                <span id="tax">$0.00</span>
                            </div>
                            <div class="summary-row total">
                                <span>Total</span>
                                <span id="total">$0.00</span>
                            </div>
                        </div>
                        <div class="cart-actions">
                            <button id="checkout-btn" class="btn btn-primary">Checkout</button>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- Modal for checkout -->
    <div class="modal" id="checkout-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Complete Sale</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="payment-total">
                    <h2>Total</h2>
                    <h2 id="modal-total">$0.00</h2>
                </div>
                <div class="payment-methods">
                    <button class="payment-method-btn" data-method="cash">Cash</button>
                    <button class="payment-method-btn" data-method="card">Card</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Notification System -->
    <div id="notification" class="notification"></div>

    <script>
        // Sample product data
        const products = [
            { id: 1, name: "Espresso", price: 3.50, category: "coffee", image: "/api/placeholder/80/80" },
            { id: 2, name: "Cappuccino", price: 4.50, category: "coffee", image: "/api/placeholder/80/80" },
            { id: 3, name: "Latte", price: 4.75, category: "coffee", image: "/api/placeholder/80/80" },
            { id: 4, name: "Croissant", price: 3.25, category: "pastry", image: "/api/placeholder/80/80" },
            { id: 5, name: "Blueberry Muffin", price: 3.50, category: "pastry", image: "/api/placeholder/80/80" },
            { id: 6, name: "Chocolate Chip Cookie", price: 2.50, category: "pastry", image: "/api/placeholder/80/80" },
            { id: 7, name: "Caesar Salad", price: 8.95, category: "food", image: "/api/placeholder/80/80" },
            { id: 8, name: "Turkey Sandwich", price: 9.50, category: "food", image: "/api/placeholder/80/80" },
            { id: 9, name: "Bottled Water", price: 1.75, category: "drinks", image: "/api/placeholder/80/80" },
            { id: 10, name: "Iced Tea", price: 2.95, category: "drinks", image: "/api/placeholder/80/80" },
            { id: 11, name: "Fresh Orange Juice", price: 3.95, category: "drinks", image: "/api/placeholder/80/80" },
            { id: 12, name: "Avocado Toast", price: 7.50, category: "food", image: "/api/placeholder/80/80" }
            
        ];

        // Application state
        let cart = [];
        let currentView = "pos";

        // DOM Elements
        const productGrid = document.querySelector('.products-grid');
        const cartItemsContainer = document.querySelector('.cart-items');
        const navLinks = document.querySelectorAll('.nav-links li');
        const views = document.querySelectorAll('.view');
        const subtotalElement = document.getElementById('subtotal');
        const taxElement = document.getElementById('tax');
        const totalElement = document.getElementById('total');
        const cartCountElement = document.getElementById('cart-count');
        const checkoutButton = document.getElementById('checkout-btn');
        const clearCartButton = document.getElementById('clear-cart');
        const checkoutModal = document.getElementById('checkout-modal');
        const modalTotal = document.getElementById('modal-total');
        const closeModalButton = document.querySelector('.close-modal');
        const paymentButtons = document.querySelectorAll('.payment-method-btn');
        const notification = document.getElementById('notification');
        const recentOrdersTable = document.getElementById('recent-orders');

        // Initialize the application
        function init() {
            renderProducts();
            renderCart();
            setupEventListeners();
            populateRecentOrders();
            initSalesChart();
        }

        function renderProducts() {
            productGrid.innerHTML = '';
            products.forEach(product => {
                const productElement = document.createElement('div');
                productElement.className = 'product-item';
                productElement.setAttribute('data-id', product.id);
                productElement.innerHTML = `
                    <div class="product-image">
                        <img src="${product.image}" alt="${product.name}">
                    </div>
                    <div class="product-info">
                        <h4>${product.name}</h4>
                        <p>$${product.price.toFixed(2)}</p>
                    </div>
                `;
                productGrid.appendChild(productElement);
            });
        }

        function renderCart() {
            cartItemsContainer.innerHTML = '';
            
            if (cart.length === 0) {
                cartItemsContainer.innerHTML = '<div class="empty-cart">Cart is empty</div>';
                updateCartTotals();
                return;
            }

            cart.forEach((item, index) => {
                const itemElement = document.createElement('div');
                itemElement.className = 'cart-item';
                itemElement.innerHTML = `
                    <div class="item-details">
                        <h4>${item.name}</h4>
                        <div class="item-price">$${(item.price * item.quantity).toFixed(2)}</div>
                    </div>
                    <div class="item-quantity">
                        <button class="quantity-btn minus" data-index="${index}">-</button>
                        <span>${item.quantity}</span>
                        <button class="quantity-btn plus" data-index="${index}">+</button>
                    </div>
                `;
                cartItemsContainer.appendChild(itemElement);
            });

            updateCartTotals();
        }

        function updateCartTotals() {
            const subtotal = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);
            const tax = subtotal * 0.085;
            const total = subtotal + tax;

            subtotalElement.textContent = `$${subtotal.toFixed(2)}`;
            taxElement.textContent = `$${tax.toFixed(2)}`;
            totalElement.textContent = `$${total.toFixed(2)}`;
            cartCountElement.textContent = cart.reduce((sum, item) => sum + item.quantity, 0);

            // Also update the checkout modal
            modalTotal.textContent = `$${total.toFixed(2)}`;
        }

        function addToCart(productId) {
            const product = products.find(p => p.id === productId);
            if (!product) return;

            const existingItem = cart.find(item => item.id === productId);
            if (existingItem) {
                existingItem.quantity += 1;
            } else {
                cart.push({
                    id: product.id,
                    name: product.name,
                    price: product.price,
                    quantity: 1
                });
            }

            renderCart();
            showNotification(`Added ${product.name} to cart`);
        }

        function updateItemQuantity(index, change) {
            if (index < 0 || index >= cart.length) return;

            cart[index].quantity += change;
            if (cart[index].quantity <= 0) {
                cart.splice(index, 1);
            }

            renderCart();
        }

        function clearCart() {
            cart = [];
            renderCart();
            showNotification('Cart has been cleared');
        }

        function showView(viewId) {
            currentView = viewId;
            
            // Update navigation
            navLinks.forEach(link => {
                link.classList.toggle('active', link.getAttribute('data-view') === viewId);
            });

            // Show the selected view
            views.forEach(view => {
                view.classList.toggle('active', view.id === `${viewId}-view`);
            });
        }

        function showCheckoutModal() {
            if (cart.length === 0) {
                showNotification('Cart is empty');
                return;
            }
            checkoutModal.classList.add('active');
        }

        function hideCheckoutModal() {
            checkoutModal.classList.remove('active');
        }

        function processPayment(method) {
            // In a real app, this would process the payment
            const total = cart.reduce((sum, item) => sum + item.price * item.quantity, 0) * 1.085;
            
            // Create a new transaction
            const newTransaction = {
                id: `ORD-${Math.floor(Math.random() * 10000)}`,
                date: new Date().toISOString().replace('T', ' ').substring(0, 19),
                items: cart.reduce((sum, item) => sum + item.quantity, 0),
                total: total,
                payment: method === 'cash' ? 'Cash' : 'Card'
            };
            
            // Add to transactions (in a real app, this would be sent to a database)
            transactions.unshift(newTransaction);
            
            // Update UI
            populateRecentOrders();
            hideCheckoutModal();
            clearCart();
            showNotification(`Payment successful! Order #${newTransaction.id}`);
        }

        function showNotification(message) {
            notification.textContent = message;
            notification.classList.add('active');
            
            setTimeout(() => {
                notification.classList.remove('active');
            }, 3000);
        }

        function populateRecentOrders() {
            if (!recentOrdersTable) return;

            recentOrdersTable.innerHTML = '';
            transactions.slice(0, 5).forEach(transaction => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${transaction.id}</td>
                    <td>${transaction.date}</td>
                    <td>${transaction.items}</td>
                    <td>$${transaction.total.toFixed(2)}</td>
                    <td><span class="payment-badge ${transaction.payment.toLowerCase()}">${transaction.payment}</span></td>
                `;
                recentOrdersTable.appendChild(row);
            });
        }

        function initSalesChart() {
            // This would normally use a charting library like Chart.js
            // For this demo, we'll just create a placeholder
            const chartContainer = document.querySelector('.chart-container');
            if (chartContainer) {
                chartContainer.innerHTML = `
                    <div class="chart-placeholder">
                        <div class="chart-title">Weekly Sales Performance</div>
                        <img src="/api/placeholder/800/300" alt="Sales Chart Placeholder">
                    </div>
                `;
            }
        }

        function setupEventListeners() {
            // Navigation
            navLinks.forEach(link => {
                link.addEventListener('click', () => {
                    const view = link.getAttribute('data-view');
                    showView(view);
                });
            });

            // Product selection
            productGrid.addEventListener('click', event => {
                const productItem = event.target.closest('.product-item');
                if (productItem) {
                    const productId = parseInt(productItem.getAttribute('data-id'));
                    addToCart(productId);
                }
            });

            // Cart quantity buttons
            cartItemsContainer.addEventListener('click', event => {
                if (event.target.classList.contains('quantity-btn')) {
                    const index = parseInt(event.target.getAttribute('data-index'));
                    const change = event.target.classList.contains('plus') ? 1 : -1;
                    updateItemQuantity(index, change);
                }
            });

            // Checkout button
            checkoutButton.addEventListener('click', showCheckoutModal);
            
            // Clear cart button
            clearCartButton.addEventListener('click', clearCart);

            // Modal close button
            closeModalButton.addEventListener('click', hideCheckoutModal);
            
            // Close modal when clicking outside
            checkoutModal.addEventListener('click', event => {
                if (event.target === checkoutModal) {
                    hideCheckoutModal();
                }
            });

            // Payment buttons
            paymentButtons.forEach(button => {
                button.addEventListener('click', () => {
                    const method = button.getAttribute('data-method');
                    processPayment(method);
                });
            });

            // Product search
            document.getElementById('product-search').addEventListener('input', function(e) {
                const query = e.target.value.toLowerCase();
                document.querySelectorAll('.product-item').forEach(item => {
                    const name = item.querySelector('.product-info h4').textContent.toLowerCase();
                    item.style.display = name.includes(query) ? '' : 'none';
                });
            });
        }

        // Initialize the application when the DOM is fully loaded
        document.addEventListener('DOMContentLoaded', init);
    </script>
</body>
</html>