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
    <link rel="stylesheet" href="../assets/css/desktop.css">
    <link rel="stylesheet" href="../assets/css/d.css">

    
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
                <div class="order-count-info">
                    Items: <span id="order-item-count">0</span>
                </div>
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

    <!-- Success Modal -->
    <div id="success-modal" class="modal success-modal">
        <div class="modal-content success-content">
            <div class="success-header">
                <div class="success-icon">✓</div>
                <h3>Payment Successful!</h3>
            </div>
            <div class="success-body">
                <div class="success-details">
                    <div class="detail-row">
                        <span>Order #:</span>
                        <span id="success-order-number"></span>
                    </div>
                    <div class="detail-row">
                        <span>Payment Method:</span>
                        <span id="success-payment-method"></span>
                    </div>
                    <div class="detail-row total-row">
                        <span>Total:</span>
                        <span id="success-total"></span>
                    </div>
                </div>
                <button id="continue-btn" class="btn-continue">Continue</button>
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
       
        <!-- Customer Modal -->
        <div id="customer-modal" class="modal">
      <div class="modal-content">
        <span class="close-customer">&times;</span>
        <h2>Select or Add Customer</h2>

        <!-- Search existing customers -->
        <section>
          <input type="text" id="customer-search" placeholder="Search by name, phone or email...">
          <div id="customer-results"></div>
        </section>
        <hr>

        <!-- Add new customer -->
        <h3>Add New Customer</h3>
        <form id="new-customer-form">
          <input type="text" id="cust-name" placeholder="Full Name" required>
          <input type="text" id="cust-phone" placeholder="Phone Number">
          <input type="email" id="cust-email" placeholder="Email">
          <button type="submit" class="btn btn-primary">Add Customer</button>
        </form>
      </div>
    </div>

    <script src="../assets/js/utils.js"></script>
    <script src="../assets/js/desktop.js"></script>
</body>
</html>