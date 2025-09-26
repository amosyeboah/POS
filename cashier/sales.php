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
    <link rel="stylesheet" href="../assets/css/cashier-sales.css">
    <link rel="stylesheet" href="../assets/css/cashier-customers.css">

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
            <button class="btn-back" onclick="window.history.back()">←</button>
            <h1>New Sale</h1>
            <button class="btn-menu">☰</button>
        </header>

        <!-- Product Search -->
        <div class="search-container">
            <input type="text" id="product-search" placeholder="Search products..." autocomplete="off">
            <button id="barcode-scan" class="btn-scan">📷</button>
        </div>

        <!-- Categories Filter -->
        <div class="categories-scroll">
            <div class="category active">All</div>
            <!-- Categories will be loaded dynamically from the database -->
        </div>

        <!-- Current Order -->
        <div class="order-summary">
            <div class="order-header">
                <span>Order #<span id="order-number">10045</span></span>
                <button id="clear-order" class="btn-text">Clear</button>
            </div>
            <div class="order-customer" style="margin: 6px 0; font-size: 0.95rem; color: #333;">
                Customer: <strong><span id="selected-customer">Walk-in</span></strong>
            </div>
            
            <div class="order-items" id="order-items">
                <!-- Items will be added here dynamically -->
                <div class="empty-cart">No items added yet</div>
            </div>
            
            <div class="order-totals">
                <div class="total-row">
                    <span>Subtotal:</span>
                    <span id="subtotal">$0.00</span>
                </div>
                <div class="total-row">
                    <span>Tax:</span>
                    <span id="tax">$0.00</span>
                </div>
                <div class="total-row grand-total">
                    <span>Total:</span>
                    <span id="total">$0.00</span>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <button id="btn-discount" class="btn-action">Discount</button>
            <button id="btn-customer" class="btn-action">Customer</button>
            <button id="btn-hold" class="btn-action">Hold</button>
        </div>

        <!-- Payment Buttons -->
        <div class="payment-buttons">
            <button id="btn-cash" class="btn-payment cash">Cash</button>
            <button id="btn-card" class="btn-payment card">Card</button>
            <button id="btn-mobile" class="btn-payment mobile">Mobile</button>
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
        <!-- Discount form would go here -->
    </div>

    <!-- Loading spinner -->
    <div id="loading-spinner" class="loading-overlay" style="display: none;">
        <div class="spinner"></div>
    </div>

    <!-- Notification Modal -->
    <div id="notification-modal" class="notification-modal">
        <div class="notification-content">
            <div class="notification-icon">
                <span class="success-icon">✓</span>
                <span class="error-icon">✕</span>
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
    <script src="../assets/js/cashier.js"></script>

</body>
</html>