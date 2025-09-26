<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modern POS System</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Main Variables & Reset */
:root {
  --primary: #6366f1;
  --primary-dark: #4f46e5;
  --secondary: #f3f4f6;
  --text: #1f2937;
  --text-light: #6b7280;
  --background: #ffffff;
  --card-bg: #ffffff;
  --card-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  --border-color: #e5e7eb;
  --danger: #ef4444;
  --success: #10b981;
  --radius: 12px;
  --spacing-xs: 4px;
  --spacing-sm: 8px;
  --spacing-md: 16px;
  --spacing-lg: 24px;
  --spacing-xl: 32px;
  --sidebar-width: 80px;
  --sidebar-width-expanded: 240px;
  --header-height: 60px;
  --transition: all 0.3s ease;
  --font-sans: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
}

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

html, body {
  font-family: var(--font-sans);
  color: var(--text);
  background-color: var(--secondary);
  height: 100%;
  overflow-x: hidden;
}

/* Main Layout */
.app-container {
  display: flex;
  flex-direction: column;
  height: 100vh;
  width: 100%;
}

/* Sidebar/Navigation */
.sidebar {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  height: 64px;
  display: flex;
  background-color: var(--card-bg);
  box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.05);
  z-index: 10;
}

.logo {
  display: none;
}

.nav-links {
  display: flex;
  list-style: none;
  width: 100%;
  justify-content: space-around;
  align-items: center;
}

.nav-links li {
  display: flex;
  align-items: center;
  justify-content: center;
  flex-direction: column;
  height: 64px;
  width: 25%;
  cursor: pointer;
  opacity: 0.7;
  transition: var(--transition);
}

.nav-links li.active {
  opacity: 1;
  color: var(--primary);
}

.nav-links .icon {
  font-size: 20px;
  margin-bottom: 4px;
}

.nav-links span {
  font-size: 12px;
  font-weight: 500;
}

/* Main Content Area */
.main-content {
  flex: 1;
  padding-bottom: 64px; /* Space for bottom navbar */
  overflow-y: auto;
  -webkit-overflow-scrolling: touch;
}

.view {
  display: none;
  height: 100%;
}

.view.active {
  display: flex;
  flex-direction: column;
}

/* View Header */
.view-header {
  background-color: var(--card-bg);
  padding: var(--spacing-md);
  display: flex;
  flex-direction: column; /* Changed to column for better mobile stacking */
  gap: var(--spacing-md);
  border-bottom: 1px solid var(--border-color);
  position: sticky;
  top: 0;
  z-index: 5;
}

.view-header h2 {
  font-size: 18px;
  font-weight: 600;
}

.header-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 100%;
}

.header-meta {
  display: flex;
  flex-direction: column;
  font-size: 14px;
  color: var(--text-light);
}

.header-meta span:first-child {
  font-size: 16px;
  font-weight: 600;
  color: var(--text);
}

.header-actions {
  display: flex;
  align-items: center;
  gap: var(--spacing-sm);
}

.header-controls {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-sm);
}

.search-bar-container {
  position: relative;
  flex: 1;
}

.search-bar-container input {
  width: 100%;
  padding: 10px 10px 10px 40px;
  border-radius: var(--radius);
  border: 1px solid var(--border-color);
  background-color: var(--secondary);
  font-size: 14px;
  transition: var(--transition);
}

.search-bar-container .search-icon {
  position: absolute;
  left: 10px;
  top: 50%;
  transform: translateY(-50%);
  color: var(--text-light);
}

.search-bar-container input:focus {
  outline: none;
  border-color: var(--primary);
  background-color: var(--background);
}

.select-category {
  padding: 10px;
  border-radius: var(--radius);
  border: 1px solid var(--border-color);
  background-color: var(--secondary);
  font-size: 14px;
  cursor: pointer;
  transition: var(--transition);
}

.select-category:focus {
  outline: none;
  border-color: var(--primary);
  background-color: var(--background);
}


/* Buttons */
.btn {
  padding: 8px 16px;
  border-radius: var(--radius);
  font-weight: 500;
  font-size: 14px;
  transition: var(--transition);
  cursor: pointer;
  border: none;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.btn-primary {
  background-color: var(--primary);
  color: white;
}

.btn-primary:hover {
  background-color: var(--primary-dark);
}

.btn-outline {
  background-color: transparent;
  border: 1px solid var(--border-color);
  color: var(--text);
}

.btn-outline:hover {
  background-color: var(--border-color);
}

/* POS View */
.pos-container {
  display: flex;
  flex-direction: column;
  height: calc(100% - var(--header-height) - 100px); /* Adjust height for new header controls */
}

/* Products Grid */
.products-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: var(--spacing-sm);
  padding: var(--spacing-sm);
  overflow-y: auto;
}

.product-item {
  background-color: var(--card-bg);
  border-radius: var(--radius);
  overflow: hidden;
  box-shadow: var(--card-shadow);
  cursor: pointer;
  transition: var(--transition);
  position: relative;
}

.product-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.product-image {
  width: 100%;
  height: auto;
  overflow: hidden;
}

.product-image img {
  width: 100%;
  height: auto;
  object-fit: cover;
  transition: var(--transition);
}

.product-info {
  padding: var(--spacing-sm);
  text-align: center;
}

.product-info h4 {
  font-size: 14px;
  margin-bottom: 4px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.product-info p {
  font-weight: 600;
  color: var(--primary);
  font-size: 14px;
}

.add-to-cart-btn {
  position: absolute;
  top: var(--spacing-sm);
  right: var(--spacing-sm);
  background-color: rgba(var(--primary-dark), 0.85);
  color: white;
  border: none;
  border-radius: 50%;
  width: 36px;
  height: 36px;
  font-size: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: var(--transition);
  opacity: 0;
}

.product-item:hover .add-to-cart-btn {
  opacity: 1;
}

.product-quantity-controls {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: var(--spacing-sm);
  background-color: rgba(0, 0, 0, 0.6);
  color: white;
  padding: var(--spacing-sm);
  transform: translateY(100%);
  transition: transform 0.3s ease;
}

.product-item:hover .product-quantity-controls {
  transform: translateY(0);
}

.product-quantity-controls button {
  background: none;
  border: 1px solid white;
  color: white;
  font-size: 18px;
  width: 28px;
  height: 28px;
  border-radius: 50%;
  cursor: pointer;
}

.product-quantity-controls span {
  font-size: 16px;
  font-weight: bold;
}


/* Cart Panel */
.cart-panel {
  background-color: var(--card-bg);
  padding: var(--spacing-md);
  border-top: 1px solid var(--border-color);
  margin-top: auto;
}

.cart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: var(--spacing-md);
}

.cart-header h3 {
  font-size: 16px;
  font-weight: 600;
}

.badge {
  background-color: var(--primary);
  color: white;
  border-radius: 50%;
  font-size: 12px;
  min-width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.cart-items {
  max-height: 30vh;
  overflow-y: auto;
  margin-bottom: var(--spacing-md);
}

.empty-cart {
  text-align: center;
  color: var(--text-light);
  padding: var(--spacing-xl) 0;
}

.cart-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: var(--spacing-sm) 0;
  border-bottom: 1px solid var(--border-color);
}

.item-details {
  flex: 1;
}

.item-details h4 {
  font-size: 14px;
  margin-bottom: 2px;
}

.item-price {
  font-weight: 600;
  color: var(--primary);
  font-size: 14px;
}

.item-quantity {
  display: flex;
  align-items: center;
}

.quantity-btn {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  background-color: var(--secondary);
  border: none;
  font-weight: bold;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
}

.quantity-btn:hover {
  background-color: var(--border-color);
}

.item-quantity span {
  width: 28px;
  text-align: center;
  font-weight: 500;
}

.cart-summary {
  margin-bottom: var(--spacing-md);
}

.summary-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 8px;
}

.summary-row.total {
  font-weight: 600;
  font-size: 18px;
  margin-top: 8px;
  padding-top: 8px;
  border-top: 1px solid var(--border-color);
}

.cart-actions {
  display: flex;
}

.cart-actions .btn {
  width: 100%;
  padding: 12px;
}

/* Admin View */
.report-container {
  padding: var(--spacing-md);
}

.report-filters {
  margin-bottom: var(--spacing-md);
}

.filter-group {
  margin-bottom: var(--spacing-md);
}

.filter-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 500;
}

.select {
  width: 100%;
  padding: 10px;
  border-radius: var(--radius);
  border: 1px solid var(--border-color);
  background-color: var(--card-bg);
  font-size: 14px;
}

.report-cards {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--spacing-md);
  margin-bottom: var(--spacing-lg);
}

.report-card {
  background-color: var(--card-bg);
  padding: var(--spacing-md);
  border-radius: var(--radius);
  box-shadow: var(--card-shadow);
}

.card-title {
  font-size: 14px;
  color: var(--text-light);
  margin-bottom: 4px;
}

.card-value {
  font-size: 24px;
  font-weight: 600;
  margin-bottom: 4px;
}

.card-change {
  font-size: 14px;
  font-weight: 500;
}

.card-change.positive {
  color: var(--success);
}

.card-change.negative {
  color: var(--danger);
}

.chart-container {
  background-color: var(--card-bg);
  border-radius: var(--radius);
  padding: var(--spacing-md);
  margin-bottom: var(--spacing-lg);
  box-shadow: var(--card-shadow);
}

.chart-placeholder {
  width: 100%;
}

.chart-placeholder img {
  width: 100%;
  height: auto;
  border-radius: var(--radius);
}

.chart-title {
  font-weight: 500;
  margin-bottom: var(--spacing-md);
}

.report-table-section h3 {
  margin-bottom: var(--spacing-md);
}

.table-responsive {
  overflow-x: auto;
}

.report-table {
  width: 100%;
  border-collapse: collapse;
}

.report-table th, .report-table td {
  padding: var(--spacing-sm);
  text-align: left;
  border-bottom: 1px solid var(--border-color);
}

.report-table th {
  font-weight: 500;
  color: var(--text-light);
}

.payment-badge {
  display: inline-block;
  padding: 4px 8px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 500;
}

.payment-badge.card {
  background-color: #eff6ff;
  color: #3b82f6;
}

.payment-badge.cash {
  background-color: #f0fdf4;
  color: #22c55e;
}

/* Modal */
.modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 100;
  opacity: 0;
  pointer-events: none;
  transition: opacity 0.3s ease;
}

.modal.active {
  opacity: 1;
  pointer-events: auto;
}

.modal-content {
  background-color: var(--card-bg);
  border-radius: var(--radius);
  width: 90%;
  max-width: 400px;
  overflow: hidden;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: var(--spacing-md);
  border-bottom: 1px solid var(--border-color);
}

.close-modal {
  background: transparent;
  border: none;
  font-size: 24px;
  cursor: pointer;
  line-height: 1;
}

.modal-body {
  padding: var(--spacing-lg);
}

.payment-total {
  text-align: center;
  margin-bottom: var(--spacing-xl);
}

.payment-total h2:first-child {
  font-size: 16px;
  color: var(--text-light);
  margin-bottom: 8px;
}

.payment-total h2:last-child {
  font-size: 36px;
  font-weight: 600;
  color: var(--primary);
}

.payment-methods {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: var(--spacing-md);
}

.payment-method-btn {
  background-color: var(--secondary);
  border: none;
  padding: var(--spacing-lg);
  border-radius: var(--radius);
  font-size: 16px;
  font-weight: 500;
  cursor: pointer;
  transition: var(--transition);
}

.payment-method-btn:hover {
  background-color: var(--border-color);
}

/* Notification */
.notification {
  position: fixed;
  bottom: 80px;
  left: 50%;
  transform: translateX(-50%) translateY(100px);
  background-color: var(--text);
  color: white;
  padding: var(--spacing-md);
  border-radius: var(--radius);
  text-align: center;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  z-index: 50;
  opacity: 0;
  transition: all 0.3s ease;
  max-width: 90%;
}

.notification.active {
  transform: translateX(-50%) translateY(0);
  opacity: 1;
}

/* Media Queries for larger screens */
@media (min-width: 768px) {
  .app-container {
    flex-direction: row;
  }
  
  .sidebar {
    position: fixed;
    top: 0;
    left: 0;
    bottom: 0;
    width: var(--sidebar-width);
    height: 100%;
    flex-direction: column;
    box-shadow: 2px 0 8px rgba(0, 0, 0, 0.05);
  }
  
  .logo {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 64px;
    padding: var(--spacing-md);
    border-bottom: 1px solid var(--border-color);
  }
  
  .logo h1 {
    font-size: 24px;
    color: var(--primary);
  }
  
  .nav-links {
    flex-direction: column;
    padding: var(--spacing-md) 0;
  }
  
  .nav-links li {
    width: 100%;
    height: 64px;
    margin-bottom: var(--spacing-sm);
  }
  
  .main-content {
    margin-left: var(--sidebar-width);
    padding-bottom: 0;
    width: calc(100% - var(--sidebar-width));
  }
  
  .view-header {
    flex-direction: row;
    justify-content: space-between;
  }

  .header-controls {
    flex-direction: row;
    justify-content: flex-end;
  }
  
  .pos-container {
    flex-direction: row;
  }
  
  .products-grid {
    grid-template-columns: repeat(3, 1fr);
    flex: 1;
    height: 100%;
    padding: var(--spacing-md);
  }
  
  .cart-panel {
    width: 35%;
    min-width: 320px;
    border-top: none;
    border-left: 1px solid var(--border-color);
    margin-top: 0;
    height: 100%;
    display: flex;
    flex-direction: column;
  }
  
  .cart-items {
    max-height: none;
    flex: 1;
    overflow-y: auto;
  }
  
  .report-cards {
    grid-template-columns: repeat(3, 1fr);
  }
}

@media (min-width: 1024px) {
  .products-grid {
    grid-template-columns: repeat(4, 1fr);
  }
  
  .sidebar {
    width: var(--sidebar-width-expanded);
  }
  
  .nav-links li {
    flex-direction: row;
    justify-content: flex-start;
    padding: 0 var(--spacing-md);
  }
  
  .nav-links .icon {
    margin-right: var(--spacing-md);
    margin-bottom: 0;
  }
  
  .main-content {
    margin-left: var(--sidebar-width-expanded);
    width: calc(100% - var(--sidebar-width-expanded));
  }
}
    </style>
</head>
<body>
    <div class="app-container">
        <nav class="sidebar">
            <div class="logo">
                <h1>POS</h1>
            </div>
            <ul class="nav-links">
                <li class="active" data-view="pos"><i class="icon">🛒</i> <span>Sales</span></li>
                <li data-view="admin"><i class="icon">📊</i> <span>Reports</span></li>
                <li data-view="settings"><i class="icon">⚙️</i> <span>Settings</span></li>
            </ul>
        </nav>

        <main class="main-content">
            <section id="pos-view" class="view active">
                <header class="view-header">
                    <div class="header-top">
                        <div class="header-meta">
                            <span id="store-name">My Store POS</span>
                            <span id="cashier-name">Cashier: Jane Doe</span>
                        </div>
                        <div class="header-actions">
                            <button id="clear-cart" class="btn btn-outline">Clear</button>
                        </div>
                    </div>
                    <div class="header-controls">
                        <div class="search-bar-container">
                            <i class="search-icon">🔍</i>
                            <input type="text" id="search-input" placeholder="Search products...">
                        </div>
                        <select id="category-filter" class="select-category">
                            <option value="all">All Categories</option>
                            </select>
                    </div>
                </header>

                <div class="pos-container">
                    <div class="products-grid">
                        </div>

                    <div class="cart-panel">
                        <div class="cart-header">
                            <h3>Current Order</h3>
                            <span id="cart-count" class="badge">0</span>
                        </div>
                        <div class="cart-items">
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

            <section id="admin-view" class="view">
                <header class="view-header">
                    <h2>Sales Reports</h2>
                    <div class="header-actions">
                        <button id="export-data" class="btn btn-outline">Export</button>
                    </div>
                </header>

                <div class="report-container">
                    <div class="report-filters">
                        <div class="filter-group">
                            <label>Period</label>
                            <select id="time-filter" class="select">
                                <option value="today">Today</option>
                                <option value="week" selected>This Week</option>
                                <option value="month">This Month</option>
                                <option value="custom">Custom Range</option>
                            </select>
                        </div>
                    </div>

                    <div class="report-cards">
                        <div class="report-card">
                            <div class="card-title">Total Sales</div>
                            <div class="card-value" id="total-sales">$4,238.50</div>
                            <div class="card-change positive">+15.3%</div>
                        </div>
                        <div class="report-card">
                            <div class="card-title">Transactions</div>
                            <div class="card-value" id="total-transactions">128</div>
                            <div class="card-change positive">+8.7%</div>
                        </div>
                        <div class="report-card">
                            <div class="card-title">Average Sale</div>
                            <div class="card-value" id="average-sale">$33.11</div>
                            <div class="card-change positive">+6.1%</div>
                        </div>
                    </div>

                    <div class="chart-container">
                        <canvas id="sales-chart"></canvas>
                    </div>

                    <div class="report-table-section">
                        <h3>Recent Transactions</h3>
                        <div class="table-responsive">
                            <table class="report-table">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Date</th>
                                        <th>Items</th>
                                        <th>Total</th>
                                        <th>Payment</th>
                                    </tr>
                                </thead>
                                <tbody id="recent-orders">
                                    </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>

            <section id="settings-view" class="view">
                <header class="view-header">
                    <h2>Settings</h2>
                </header>
                <div class="settings-container">
                    <div class="settings-group">
                        <h3>System Settings</h3>
                        </div>
                </div>
            </section>
        </main>
    </div>

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

    <div id="notification" class="notification"></div>

    <script>
        // Sample product data
        const products = [
            { id: 1, name: "Espresso", price: 3.50, category: "Coffee", image: "/api/placeholder/80/80" },
            { id: 2, name: "Cappuccino", price: 4.50, category: "Coffee", image: "/api/placeholder/80/80" },
            { id: 3, name: "Latte", price: 4.75, category: "Coffee", image: "/api/placeholder/80/80" },
            { id: 4, name: "Croissant", price: 3.25, category: "Pastry", image: "/api/placeholder/80/80" },
            { id: 5, name: "Blueberry Muffin", price: 3.50, category: "Pastry", image: "/api/placeholder/80/80" },
            { id: 6, name: "Chocolate Chip Cookie", price: 2.50, category: "Pastry", image: "/api/placeholder/80/80" },
            { id: 7, name: "Caesar Salad", price: 8.95, category: "Food", image: "/api/placeholder/80/80" },
            { id: 8, name: "Turkey Sandwich", price: 9.50, category: "Food", image: "/api/placeholder/80/80" },
            { id: 9, name: "Bottled Water", price: 1.75, category: "Drinks", image: "/api/placeholder/80/80" },
            { id: 10, name: "Iced Tea", price: 2.95, category: "Drinks", image: "/api/placeholder/80/80" },
            { id: 11, name: "Fresh Orange Juice", price: 3.95, category: "Drinks", image: "/api/placeholder/80/80" },
            { id: 12, name: "Avocado Toast", price: 7.50, category: "Food", image: "/api/placeholder/80/80" }
        ];

        // Sample transaction data
        const transactions = [
            { id: "ORD-7829", date: "2025-04-17 09:45:22", items: 3, total: 12.75, payment: "Card" },
            { id: "ORD-7828", date: "2025-04-17 09:32:17", items: 2, total: 8.25, payment: "Cash" },
            { id: "ORD-7827", date: "2025-04-17 09:15:03", items: 4, total: 18.50, payment: "Card" },
            { id: "ORD-7826", date: "2025-04-16 17:22:45", items: 1, total: 4.75, payment: "Card" },
            { id: "ORD-7825", date: "2025-04-16 16:55:12", items: 3, total: 14.20, payment: "Cash" }
        ];

        // Application state
        let cart = [];
        let currentView = "pos";

        // DOM Elements
        const storeNameElement = document.getElementById('store-name');
        const cashierNameElement = document.getElementById('cashier-name');
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
        const searchInput = document.getElementById('search-input');
        const categoryFilter = document.getElementById('category-filter');
        
        const posViewHeader = document.querySelector('#pos-view .view-header');

        // Initialize the application
        function init() {
            setStoreInfo('My Coffee Shop', 'John Doe');
            populateCategories();
            renderProducts(products);
            renderCart();
            setupEventListeners();
            populateRecentOrders();
            initSalesChart();
        }

        function setStoreInfo(store, cashier) {
            storeNameElement.textContent = store;
            cashierNameElement.textContent = `Cashier: ${cashier}`;
        }
        
        function populateCategories() {
            const categories = [...new Set(products.map(p => p.category))];
            categories.forEach(category => {
                const option = document.createElement('option');
                option.value = category;
                option.textContent = category;
                categoryFilter.appendChild(option);
            });
        }

        function renderProducts(productsToRender) {
            productGrid.innerHTML = '';
            if (productsToRender.length === 0) {
                productGrid.innerHTML = '<div style="text-align: center; color: var(--text-light); padding: var(--spacing-xl);">No products found.</div>';
                return;
            }
            productsToRender.forEach(product => {
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
                    <button class="add-to-cart-btn" data-id="${product.id}">+</button>
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
                const removedItem = cart.splice(index, 1)[0];
                showNotification(`Removed ${removedItem.name} from cart`);
            } else {
                showNotification(`Updated quantity for ${cart[index].name}`);
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

        function filterProducts() {
            const searchTerm = searchInput.value.toLowerCase();
            const selectedCategory = categoryFilter.value;

            const filteredProducts = products.filter(product => {
                const matchesSearch = product.name.toLowerCase().includes(searchTerm);
                const matchesCategory = selectedCategory === 'all' || product.category === selectedCategory;
                return matchesSearch && matchesCategory;
            });
            renderProducts(filteredProducts);
        }

        function setupEventListeners() {
            // Navigation
            navLinks.forEach(link => {
                link.addEventListener('click', () => {
                    const view = link.getAttribute('data-view');
                    showView(view);
                });
            });

            // Product selection (Add to Cart)
            productGrid.addEventListener('click', event => {
                const addButton = event.target.closest('.add-to-cart-btn');
                if (addButton) {
                    const productId = parseInt(addButton.getAttribute('data-id'));
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

            // Search and Filter
            searchInput.addEventListener('input', filterProducts);
            categoryFilter.addEventListener('change', filterProducts);
        }

        // Initialize the application when the DOM is fully loaded
        document.addEventListener('DOMContentLoaded', init);
    </script>
</body>
</html>