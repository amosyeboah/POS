<?php
session_start();
require_once '../db.php';

// Check if user is logged in and is a cashier
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$conn = getConnection();

// Fetch user info and tenant info
$user_id = $_SESSION['user_id'];
$sql = "SELECT u.full_name, u.role, u.tenant_id, t.business_name
        FROM users u
        JOIN tenants t ON u.tenant_id = t.tenant_id
        WHERE u.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($full_name, $role, $tenant_id, $business_name);

if ($stmt->fetch()) {
    // Set tenant_id in session to match the user's tenant
    $_SESSION['tenant_id'] = $tenant_id;


} else {
    // Invalid session/user
    header('Location: ../login.php');
    exit;
}

//--------------------------
// Re-establish database connection for the new queries
$conn = getConnection();

// Get the current date in 'YYYY-MM-DD' format
$today_date = date('Y-m-d');

// Query 1: Fetch Today's Sales
$sql_sales = "SELECT COALESCE(SUM(total), 0) AS todays_sales FROM sales WHERE tenant_id = ? AND DATE(created_at) = ?";
$stmt_sales = $conn->prepare($sql_sales);
$stmt_sales->bind_param("is", $tenant_id, $today_date);
$stmt_sales->execute();
$stmt_sales->bind_result($todays_sales);
$stmt_sales->fetch();
$stmt_sales->close();

// Query 2: Fetch Today's Transaction Count
$sql_transactions = "SELECT COUNT(*) AS todays_transactions FROM sales WHERE tenant_id = ? AND DATE(created_at) = ?";
$stmt_transactions = $conn->prepare($sql_transactions);
$stmt_transactions->bind_param("is", $tenant_id, $today_date);
$stmt_transactions->execute();
$stmt_transactions->bind_result($todays_transactions);
$stmt_transactions->fetch();
$stmt_transactions->close();

// $conn->close();
//---------------------

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($business_name) ?> - Cashier Dashboard</title>
    <link rel="stylesheet" href="../assets/css/cashier.css">
    <link rel="stylesheet" href="../assets/css/cashier-dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Header Section -->
        <header class="dashboard-header">
            <div class="user-info">
                <div class="user-avatar">
                    <i class="fas fa-user-circle"></i>
                </div>
                <div class="user-details">
                    <span class="user-name">Cashier: <?= htmlspecialchars($full_name) ?></span>
                    <span class="shift-status">Shift: Active (8:00 AM - 4:00 PM)</span>
                    <span class="business-name"><?= htmlspecialchars($business_name) ?></span>
                </div>
            </div>
            <button class="btn-logout" id="btn-logout">
                <i class="fas fa-sign-out-alt"></i>
            </button>
        </header>

        <!-- Quick Stats -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-icon sales">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-value" id="today-sales"><?= '₵' . number_format($todays_sales, 2) ?></span>
                    <span class="stat-label">Today's Sales</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon transactions">
                    <i class="fas fa-receipt"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-value" id="today-transactions"><?= htmlspecialchars($todays_transactions) ?></span>
                    <span class="stat-label">Transactions</span>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <h2 class="section-title">Quick Actions</h2>
            <div class="action-grid">
                <a href="sales.php" class="action-card new-sale">
                    <div class="action-icon">
                        <i class="fas fa-plus-circle"></i>
                    </div>
                    <span class="action-label">New Sale</span>
                </a>
                <!-- <a href="returns.php" class="action-card returns"> -->
                <a href="#" class="action-card returns" onclick="alert('Upgrade to use this feature'); return false;">
                    <div class="action-icon">
                        <i class="fas fa-exchange-alt"></i>
                    </div>
                    <span class="action-label">Returns</span>
                </a>
                <!-- <a href="shift.php" class="action-card shift"> -->
                <a href="#" class="action-card shift" onclick="alert('Upgrade to use this feature'); return false;">
                    <div class="action-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <span class="action-label">My Shift</span>
                </a>
                <a href="history.php" class="action-card history">
                    <div class="action-icon">
                        <i class="fas fa-history"></i>
                    </div>
                    <span class="action-label">History</span>
                </a>
            </div>
        </div>

        <!-- Quick Products -->
        <!-- <div class="quick-products">
            <h2 class="section-title">Quick Products</h2>
            <div class="product-scroll" id="quick-products"> -->
                <!-- Products will be loaded here dynamically -->
                <!-- <div class="product-chip" data-id="1">
                    <span class="product-name">Soda</span>
                    <span class="product-price">$1.50</span>
                </div>
                <div class="product-chip" data-id="2">
                    <span class="product-name">Chips</span>
                    <span class="product-price">$2.00</span>
                </div>
                <div class="product-chip" data-id="3">
                    <span class="product-name">Candy</span>
                    <span class="product-price">$1.25</span>
                </div>
                <div class="product-chip" data-id="4">
                    <span class="product-name">Beer</span>
                    <span class="product-price">$4.50</span>
                </div>
                <div class="product-chip" data-id="5">
                    <span class="product-name">Coffee</span>
                    <span class="product-price">$2.50</span>
                </div>
            </div> -->
        <!-- </div> -->

        <!-- Recent Transactions -->
                <div class="recent-transactions">
            <h2 class="section-title">Recent Transactions</h2>
            <div class="transactions-list" id="recent-transactions">
                </div>
            <a href="history.php" class="view-all">View All Transactions →</a>
        </div>

    </div>

    <script src="../assets/js/utils.js"></script>
    <script src="../assets/js/cashier-dashboard.js"></script>
     <script src="../assets/js/cashier-dashboard.js"></script>
    <script>
        document.getElementById('btn-logout').addEventListener('click', function() {
            window.location.href = '../logout.php';
        });
    </script>
</body>
</html>