<?php
session_start();
require_once '../db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// Fetch user info and tenant info
$conn = getConnection();
$user_id = $_SESSION['user_id'];
$sql = "SELECT u.full_name, u.role, u.tenant_id, t.business_name
        FROM users u
        JOIN tenants t ON u.tenant_id = t.tenant_id
        WHERE u.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($full_name, $role, $tenant_id, $business_name);
$stmt->fetch();
$stmt->close();
$conn->close();

if (!isset($tenant_id)) {
    // Invalid session/user
    header('Location: ../login.php');
    exit;
}
$_SESSION['tenant_id'] = $tenant_id;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($business_name) ?> - Transaction History</title>
    <link rel="stylesheet" href="../assets/css/cashier.css">
    <!-- <link rel="stylesheet" href="../assets/css/cashier-history.css"> -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="history-container">
        <header class="history-header">
            <button class="btn-back" onclick="window.history.back()">←</button>
            <h1>Transaction History</h1>
            <a href="sales_report.php">
                <button class="btn-filter" id="">Sales</button>
            </a>
            <button class="btn-filter" id="btn-filter">Filter</button>

        </header>

        <div class="filter-overlay" id="filter-overlay">
            <div class="filter-content">
                <div class="filter-header">
                    <h3>Filter Transactions</h3>
                    <span class="close-filter">&times;</span>
                </div>
                <form id="filter-form" onsubmit="return false;">
                    <div class="filter-body">
                        <div class="filter-group">
                            <label for="date-range">Date Range</label>
                            <select id="date-range">
                                <option value="today">Today</option>
                                <option value="yesterday">Yesterday</option>
                                <option value="week">This Week</option>
                                <option value="month">This Month</option>
                                <option value="custom">Custom Range</option>
                            </select>
                        </div>
                        <div class="filter-group custom-range" id="custom-range" style="display: none;">
                            <label for="start-date">Start Date</label>
                            <input type="date" id="start-date">
                            <label for="end-date">End Date</label>
                            <input type="date" id="end-date">
                        </div>
                        <div class="filter-group">
                            <label for="payment-type">Payment Type</label>
                            <select id="payment-type">
                                <option value="all">All Types</option>
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="mobile">Mobile</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="min-amount">Minimum Amount</label>
                            <input type="number" id="min-amount" placeholder="0.00" min="0" step="0.01">
                        </div>
                    </div>
                    <div class="filter-actions">
                        <button type="button" class="btn-cancel-filter" id="btn-cancel-filter">Cancel</button>
                        <button type="submit" class="btn-apply-filter" id="btn-apply-filter">Apply Filters</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="summary-cards">
            <div class="summary-card">
                <div class="summary-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="summary-info">
                    <span class="summary-value" id="total-transactions">0</span>
                    <span class="summary-label">Transactions</span>
                </div>
            </div>
            <div class="summary-card">
                <div class="summary-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="summary-info">
                    <span class="summary-value" id="total-sales">$0.00</span>
                    <span class="summary-label">Total Sales</span>
                </div>
            </div>
        </div>

        <div class="transaction-list" id="transaction-list">
            </div>
        
        <div class="load-more" id="load-more">
            <button id="btn-load-more">Load More Transactions</button>
        </div>
    </div>

    <script src="../assets/js/utils.js"></script>
    <script src="../assets/js/cashier-history.js"></script>
</body>
</html>