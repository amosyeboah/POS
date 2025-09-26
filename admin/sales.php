<?php
// filepath: c:\xampp\htdocs\mobile-pos\admin\sales.php
session_start();
require_once '../db.php';

// Ensure user is logged in and tenant_id is set
if (!isset($_SESSION['user_id']) || !isset($_SESSION['tenant_id'])) {
    header('Location: ../login.php');
    exit;
}
$tenant_id = $_SESSION['tenant_id'];

// Connect to DB
$conn = getConnection();

// Handle filter parameters
$date_filter = isset($_GET['date_filter']) ? $_GET['date_filter'] : 'today';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$cashier_filter = isset($_GET['cashier']) ? $_GET['cashier'] : '';
$payment_filter = isset($_GET['payment_method']) ? $_GET['payment_method'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Build WHERE clause for filters
$where_conditions = ["s.tenant_id = ?"];
$params = [$tenant_id];
$param_types = "i";

// Date filter logic
if (!empty($start_date) && !empty($end_date)) {
    $where_conditions[] = "DATE(s.created_at) BETWEEN ? AND ?";
    $params[] = $start_date;
    $params[] = $end_date;
    $param_types .= "ss";
} else {
    switch ($date_filter) {
        case 'today':
            $where_conditions[] = "DATE(s.created_at) = CURDATE()";
            break;
        case 'yesterday':
            $where_conditions[] = "DATE(s.created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
            break;
        case 'week':
            $where_conditions[] = "YEARWEEK(s.created_at, 1) = YEARWEEK(CURDATE(), 1)";
            break;
        case 'month':
            $where_conditions[] = "MONTH(s.created_at) = MONTH(CURDATE()) AND YEAR(s.created_at) = YEAR(CURDATE())";
            break;
        case 'all':
            // No date filter
            break;
    }
}

// Cashier filter
if (!empty($cashier_filter)) {
    $where_conditions[] = "s.user_id = ?";
    $params[] = $cashier_filter;
    $param_types .= "i";
}

// Payment method filter
if (!empty($payment_filter)) {
    $where_conditions[] = "s.payment_method = ?";
    $params[] = $payment_filter;
    $param_types .= "s";
}

// Status filter
if (!empty($status_filter)) {
    $where_conditions[] = "s.status = ?";
    $params[] = $status_filter;
    $param_types .= "s";
}

$where_clause = implode(" AND ", $where_conditions);

// Pagination setup
$per_page = 20;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $per_page;

// Count total sales for pagination
$count_sql = "SELECT COUNT(*) FROM sales s WHERE $where_clause";
$count_stmt = $conn->prepare($count_sql);
$count_stmt->bind_param($param_types, ...$params);
$count_stmt->execute();
$count_stmt->bind_result($total_sales);
$count_stmt->fetch();
$count_stmt->close();

$total_pages = ceil($total_sales / $per_page);

// Fetch sales with filters and pagination
$sales_sql = "SELECT s.sale_id, s.transaction_code, s.created_at, u.full_name AS cashier, s.total, s.payment_method, s.status
              FROM sales s
              JOIN users u ON s.user_id = u.user_id
              WHERE $where_clause
              ORDER BY s.created_at DESC
              LIMIT ? OFFSET ?";
$params[] = $per_page;
$params[] = $offset;
$param_types .= "ii";

$sales_stmt = $conn->prepare($sales_sql);
$sales_stmt->bind_param($param_types, ...$params);
$sales_stmt->execute();
$sales_result = $sales_stmt->get_result();

$sales = [];
while ($row = $sales_result->fetch_assoc()) {
    $sales[] = $row;
}
$sales_stmt->close();

// Fetch cashiers for filter dropdown
$cashiers_sql = "SELECT user_id, full_name FROM users WHERE tenant_id = ? AND role = 'cashier'";
$cashiers_stmt = $conn->prepare($cashiers_sql);
$cashiers_stmt->bind_param("i", $tenant_id);
$cashiers_stmt->execute();
$cashiers_result = $cashiers_stmt->get_result();

$cashiers = [];
while ($row = $cashiers_result->fetch_assoc()) {
    $cashiers[] = $row;
}
$cashiers_stmt->close();

// Fetch stats based on filters
$stats_sql = "
SELECT
    SUM(CASE WHEN DATE(s.created_at) = CURDATE() THEN s.total ELSE 0 END) AS today,
    SUM(CASE WHEN DATE(s.created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY) THEN s.total ELSE 0 END) AS yesterday,
    SUM(CASE WHEN YEARWEEK(s.created_at, 1) = YEARWEEK(CURDATE(), 1) THEN s.total ELSE 0 END) AS week,
    SUM(CASE WHEN MONTH(s.created_at) = MONTH(CURDATE()) AND YEAR(s.created_at) = YEAR(CURDATE()) THEN s.total ELSE 0 END) AS month,
    SUM(s.total) AS total,
    COUNT(*) AS transactions
FROM sales s
WHERE $where_clause
";
$stats_stmt = $conn->prepare($stats_sql);

// Build stats-only params/types (exclude LIMIT/OFFSET appended for the sales list query)
$stats_params = $params;
$stats_param_types = $param_types;
// If the last two types are the LIMIT/OFFSET integers, strip them off
if (strlen($stats_param_types) >= 2 && substr($stats_param_types, -2) === 'ii') {
    $stats_param_types = substr($stats_param_types, 0, -2);
    $stats_params = array_slice($stats_params, 0, count($stats_params) - 2);
}
// Bind only if we actually have parameters to bind
if (!empty($stats_params)) {
    $stats_stmt->bind_param($stats_param_types, ...$stats_params);
}
$stats_stmt->execute();
$stats_stmt->bind_result($today, $yesterday, $week, $month, $total, $transactions);
if ($stats_stmt->fetch()) {
    $stats = [
        'today' => $today,
        'yesterday' => $yesterday,
        'week' => $week,
        'month' => $month,
        'total' => $total,
        'transactions' => $transactions
    ];
} else {
    $stats = [
        'today' => 0,
        'yesterday' => 0,
        'week' => 0,
        'month' => 0,
        'total' => 0,
        'transactions' => 0
    ];
}
$stats_stmt->close();

// Fetch currency type for this tenant
$currency_type = 'GHS'; // Default
$currency_stmt = $conn->prepare("SELECT currency_type FROM tenants WHERE tenant_id = ?");
$currency_stmt->bind_param("i", $tenant_id);
$currency_stmt->execute();
$currency_stmt->bind_result($currency_type);
$currency_stmt->fetch();
$currency_stmt->close();

$conn->close();
?>

<?php
function getCurrencySymbol($currencyType) {
    switch (strtoupper($currencyType)) {
        case 'GHS': return '₵';      // Ghana Cedi
        case 'USD': return '$';      // US Dollar
        case 'EUR': return '€';      // Euro
        case 'GBP': return '£';      // British Pound
        case 'NGN': return '₦';      // Nigerian Naira
        default: return $currencyType; // fallback to code
    }
}
$currency_symbol = getCurrencySymbol($currency_type);

// PHP helper to modify the current query string with overrides, used for pagination links
function modifyQueryString($overrides = []) {
    $params = $_GET;
    foreach ($overrides as $key => $value) {
        if ($value === null || $value === '') {
            unset($params[$key]);
        } else {
            $params[$key] = $value;
        }
    }
    $query = http_build_query($params);
    return 'sales.php' . ($query ? ('?' . $query) : '');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Reports | POS Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #eef2ff;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --danger: #f72585;
            --warning: #f8961e;
            --info: #4895ef;
            --dark: #212529;
            --light: #f8f9fa;
            --gray: #6c757d;
            --border-radius: 12px;
            --shadow: 0 4px 20px rgba(0,0,0,0.08);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        body {
            background-color: #f5f7fb;
            color: var(--dark);
            line-height: 1.6;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 1.5rem;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .header h1 {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--dark);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            border-radius: var(--border-radius);
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            font-size: 0.95rem;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--secondary);
            transform: translateY(-2px);
        }

        .btn-outline {
            background-color: transparent;
            border: 1px solid var(--primary);
            color: var(--primary);
        }

        .btn-outline:hover {
            background-color: var(--primary-light);
        }

        .btn-icon {
            background: none;
            border: none;
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
        }

        .btn-icon:hover {
            background-color: rgba(0,0,0,0.05);
        }

        .card {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .card-header {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .card-header h2 {
            font-size: 1.25rem;
            font-weight: 600;
        }

        .card-body {
            padding: 1.5rem;
        }

        .filter-bar {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 1.5rem;
            align-items: end;
        }

        .filter-group {
            position: relative;
            min-width: 180px;
        }

        .filter-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--gray);
        }

        .filter-group input,
        .filter-group select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid rgba(0,0,0,0.1);
            border-radius: var(--border-radius);
            font-size: 0.95rem;
            transition: var(--transition);
        }

        .filter-group input:focus,
        .filter-group select:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
        }

        .filter-actions {
            display: flex;
            gap: 0.5rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background-color: white;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            box-shadow: var(--shadow);
            text-align: center;
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card .value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .stat-card .label {
            font-size: 0.9rem;
            color: var(--gray);
        }

        .stat-card .trend {
            font-size: 0.8rem;
            margin-top: 0.5rem;
            color: var(--gray);
        }

        .stat-card.today .value {
            color: var(--success);
        }

        .stat-card.yesterday .value {
            color: var(--info);
        }

        .stat-card.week .value {
            color: var(--warning);
        }

        .stat-card.month .value {
            color: var(--primary);
        }

        .stat-card.total .value {
            color: var(--secondary);
        }

        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }

        th {
            text-align: left;
            padding: 1rem 0.75rem;
            font-weight: 600;
            color: var(--gray);
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }

        td {
            padding: 1rem 0.75rem;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            vertical-align: middle;
        }

        tr:hover {
            background-color: rgba(67, 97, 238, 0.03);
        }

        .badge {
            display: inline-block;
            padding: 0.35rem 0.75rem;
            border-radius: 100px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .badge.completed {
            background-color: rgba(16, 183, 89, 0.1);
            color: #10b759;
        }

        .badge.refunded {
            background-color: rgba(244, 67, 54, 0.1);
            color: #f44336;
        }

        .badge.pending {
            background-color: rgba(247, 144, 9, 0.1);
            color: #f79009;
        }

        .payment-method {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .payment-method i {
            color: var(--gray);
        }

        .pagination {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem;
            border-top: 1px solid rgba(0,0,0,0.05);
        }

        .pagination-info {
            color: var(--gray);
            font-size: 0.9rem;
        }

        .pagination-controls {
            display: flex;
            gap: 0.5rem;
        }

        .export-options {
            display: flex;
            gap: 0.5rem;
        }

        .filter-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .filter-tag {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 0.75rem;
            background-color: var(--primary-light);
            color: var(--primary);
            border-radius: 100px;
            font-size: 0.85rem;
        }

        .filter-tag .remove {
            cursor: pointer;
            padding: 0.15rem;
            border-radius: 50%;
        }

        .filter-tag .remove:hover {
            background-color: rgba(0,0,0,0.05);
        }

        /* Responsive adjustments */
        @media (max-width: 1024px) {
            .filter-group {
                min-width: 160px;
            }
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .filter-bar {
                flex-direction: column;
                gap: 1rem;
                align-items: stretch;
            }
            
            .filter-group {
                width: 100%;
            }
            
            .stats-grid {
                grid-template-columns: 1fr 1fr;
            }
            
            .pagination {
                flex-direction: column;
                gap: 1rem;
                align-items: center;
            }
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .export-options {
                flex-direction: column;
                width: 100%;
            }
            
            .export-options .btn {
                width: 100%;
            }
            
            .filter-actions {
                flex-direction: column;
                width: 100%;
            }
            
            .filter-actions .btn {
                width: 100%;
            }
        }

        /* Loading animation */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: white;
            border-radius: var(--border-radius);
            width: 95%;
            max-width: 900px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: var(--shadow);
            animation: modalIn 0.2s ease-out;
        }

        .modal-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            background: #fff;
            z-index: 2;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--gray);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="index.php" class="btn btn-outline" id="back-arrow" style="display:inline-flex;align-items:center;gap:0.5rem;">
                <i class="fas fa-arrow-left"></i> Back
            </a>
            <h1>Sales Reports</h1>
            <div class="export-options">
                <button class="btn btn-outline" id="export-excel">
                    <i class="fas fa-file-excel"></i> Export Excel
                </button>
                <button class="btn btn-outline" id="export-pdf">
                    <i class="fas fa-file-pdf"></i> Export PDF
                </button>
            </div>
        </div>

        <!-- Filter tags -->
        <div class="filter-tags" id="filter-tags">
            <?php if (!empty($date_filter) && $date_filter != 'all'): ?>
                <div class="filter-tag">
                    Date: <?= ucfirst($date_filter) ?>
                    <span class="remove" onclick="removeFilter('date_filter')">&times;</span>
                </div>
            <?php endif; ?>
            <?php if (!empty($start_date) && !empty($end_date)): ?>
                <div class="filter-tag">
                    Custom Date: <?= $start_date ?> to <?= $end_date ?>
                    <span class="remove" onclick="removeFilter('custom_date')">&times;</span>
                </div>
            <?php endif; ?>
            <?php if (!empty($cashier_filter)): 
                $cashier_name = '';
                foreach ($cashiers as $cashier) {
                    if ($cashier['user_id'] == $cashier_filter) {
                        $cashier_name = $cashier['full_name'];
                        break;
                    }
                }
            ?>
                <div class="filter-tag">
                    Cashier: <?= $cashier_name ?>
                    <span class="remove" onclick="removeFilter('cashier')">&times;</span>
                </div>
            <?php endif; ?>
            <?php if (!empty($payment_filter)): ?>
                <div class="filter-tag">
                    Payment: <?= ucfirst($payment_filter) ?>
                    <span class="remove" onclick="removeFilter('payment_method')">&times;</span>
                </div>
            <?php endif; ?>
            <?php if (!empty($status_filter)): ?>
                <div class="filter-tag">
                    Status: <?= ucfirst($status_filter) ?>
                    <span class="remove" onclick="removeFilter('status')">&times;</span>
                </div>
            <?php endif; ?>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>Quick Stats</h2>
                <button class="btn btn-outline" id="toggle-filters">
                    <i class="fas fa-filter"></i> Toggle Filters
                </button>
            </div>
            <div class="card-body">
                <form id="filters-form" method="GET" action="sales.php">
                    <input type="hidden" name="page" value="1">
                    <input type="hidden" name="start_date" id="start_date">
                    <input type="hidden" name="end_date" id="end_date">
                    <div class="filter-bar">
                        <div class="filter-group">
                            <label for="date-filter">Date Range</label>
                            <select id="date-filter" name="date_filter">
                                <option value="today" <?= $date_filter == 'today' ? 'selected' : '' ?>>Today</option>
                                <option value="yesterday" <?= $date_filter == 'yesterday' ? 'selected' : '' ?>>Yesterday</option>
                                <option value="week" <?= $date_filter == 'week' ? 'selected' : '' ?>>This Week</option>
                                <option value="month" <?= $date_filter == 'month' ? 'selected' : '' ?>>This Month</option>
                                <option value="all" <?= $date_filter == 'all' ? 'selected' : '' ?>>All Time</option>
                                <option value="custom" <?= !empty($start_date) && !empty($end_date) ? 'selected' : '' ?>>Custom Range</option>
                            </select>
                        </div>
                        
                        <div class="filter-group" id="custom-date-range" style="display: <?= (!empty($start_date) && !empty($end_date)) ? 'block' : 'none' ?>;">
                            <label for="date-range">Select Dates</label>
                            <input type="text" id="date-range" name="date_range" placeholder="Select date range" 
                                value="<?= !empty($start_date) && !empty($end_date) ? $start_date . ' to ' . $end_date : '' ?>">
                        </div>
                        
                        <div class="filter-group">
                            <label for="cashier-filter">Cashier</label>
                            <select id="cashier-filter" name="cashier">
                                <option value="">All Cashiers</option>
                                <?php foreach ($cashiers as $cashier): ?>
                                    <option value="<?= $cashier['user_id'] ?>" <?= $cashier_filter == $cashier['user_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cashier['full_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label for="payment-filter">Payment Method</label>
                            <select id="payment-filter" name="payment_method">
                                <option value="">All Methods</option>
                                <option value="cash" <?= $payment_filter == 'cash' ? 'selected' : '' ?>>Cash</option>
                                <option value="card" <?= $payment_filter == 'card' ? 'selected' : '' ?>>Card</option>
                                <option value="mobile" <?= $payment_filter == 'mobile' ? 'selected' : '' ?>>Mobile</option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label for="status-filter">Status</label>
                            <select id="status-filter" name="status">
                                <option value="">All Statuses</option>
                                <option value="completed" <?= $status_filter == 'completed' ? 'selected' : '' ?>>Completed</option>
                                <option value="pending" <?= $status_filter == 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="refunded" <?= $status_filter == 'refunded' ? 'selected' : '' ?>>Refunded</option>
                            </select>
                        </div>
                        
                        <div class="filter-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Apply Filters
                            </button>
                            <button id="reset-filters" class="btn btn-outline" type="button">
                                <i class="fas fa-times"></i> Clear Filters
                            </button>
                        </div>
                    </div>
                </form>

                <div class="stats-grid">
                    <div class="stat-card today">
                        <div class="value"><?= $currency_symbol . number_format($stats['today'], 2) ?></div>
                        <div class="label">Today's Sales</div>
                    </div>
                    <div class="stat-card yesterday">
                        <div class="value"><?= $currency_symbol . number_format($stats['yesterday'], 2) ?></div>
                        <div class="label">Yesterday's Sales</div>
                    </div>
                    <div class="stat-card week">
                        <div class="value"><?= $currency_symbol . number_format($stats['week'], 2) ?></div>
                        <div class="label">This Week</div>
                    </div>
                    <div class="stat-card month">
                        <div class="value"><?= $currency_symbol . number_format($stats['month'], 2) ?></div>
                        <div class="label">This Month</div>
                    </div>
                    <div class="stat-card total">
                        <div class="value"><?= $currency_symbol . number_format($stats['total'], 2) ?></div>
                        <div class="label">Total Sales</div>
                        <div class="trend"><?= $stats['transactions'] ?> Transactions</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>Transaction Details</h2>
                <div class="pagination-info">
                    Showing <?= count($sales) ?> of <?= $total_sales ?> transactions
                </div>
            </div>
            <div class="card-body">
                <div class="table-container">
                    <table id="transactions-table">
                        <thead>
                            <tr>
                                <th>Transaction ID</th>
                                <th>Date/Time</th>
                                <th>Cashier</th>
                                <th>Amount</th>
                                <th>Payment</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($sales) > 0): ?>
                                <?php foreach ($sales as $sale): ?>
                                    <tr>
                                        <td>#<?= htmlspecialchars($sale['transaction_code']) ?></td>
                                        <td><?= date('Y-m-d h:i A', strtotime($sale['created_at'])) ?></td>
                                        <td><?= htmlspecialchars($sale['cashier']) ?></td>
                                        <td><?= $currency_symbol . number_format($sale['total'], 2) ?></td>
                                        <td>
                                            <span class="payment-method">
                                                <i class="fas fa-<?= $sale['payment_method'] === 'cash' ? 'money-bill-wave' : ($sale['payment_method'] === 'card' ? 'credit-card' : 'mobile-alt') ?>"></i>
                                                <?= ucfirst($sale['payment_method']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge <?= $sale['status'] ?>">
                                                <?= ucfirst($sale['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn-icon view-details" title="View Details" data-id="<?= $sale['sale_id'] ?>">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn-icon print-receipt" title="Print Receipt" data-id="<?= $sale['sale_id'] ?>">
                                                <i class="fas fa-print"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 2rem;">
                                        No transactions found with the current filters.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="pagination">
                    <div class="pagination-info">
                        Page <?= $page ?> of <?= $total_pages ?> • <?= $total_sales ?> total transactions
                    </div>
                    <div class="pagination-controls">
                        <?php if ($page > 1): ?>
                            <a class="btn btn-outline" href="<?= modifyQueryString(['page' => $page - 1]) ?>">
                                <i class="fas fa-chevron-left"></i> Previous
                            </a>
                        <?php else: ?>
                            <button class="btn btn-outline" disabled>
                                <i class="fas fa-chevron-left"></i> Previous
                            </button>
                        <?php endif; ?>
                        
                        <?php 
                        // Show page numbers with ellipsis for many pages
                        $maxPagesToShow = 5;
                        $startPage = max(1, $page - floor($maxPagesToShow / 2));
                        $endPage = min($total_pages, $startPage + $maxPagesToShow - 1);
                        
                        if ($endPage - $startPage < $maxPagesToShow - 1) {
                            $startPage = max(1, $endPage - $maxPagesToShow + 1);
                        }
                        
                        if ($startPage > 1): ?>
                            <a class="btn btn-outline" href="<?= modifyQueryString(['page' => 1]) ?>">1</a>
                            <?php if ($startPage > 2): ?>
                                <span class="btn btn-outline" style="border: none; background: transparent;">...</span>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                            <a class="btn <?= $i == $page ? 'btn-primary' : 'btn-outline' ?>" href="<?= modifyQueryString(['page' => $i]) ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($endPage < $total_pages): ?>
                            <?php if ($endPage < $total_pages - 1): ?>
                                <span class="btn btn-outline" style="border: none; background: transparent;">...</span>
                            <?php endif; ?>
                            <a class="btn btn-outline" href="<?= modifyQueryString(['page' => $total_pages]) ?>"><?= $total_pages ?></a>
                        <?php endif; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <a class="btn btn-outline" href="<?= modifyQueryString(['page' => $page + 1]) ?>">
                                Next <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php else: ?>
                            <button class="btn btn-outline" disabled>
                                Next <i class="fas fa-chevron-right"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Transaction Details Modal -->
    <div class="modal" id="details-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Transaction Details</h3>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body" id="modal-body">
                <!-- Content will be loaded via AJAX -->
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        // Helper function to modify query string
        function modifyQueryString(params) {
            const url = new URL(window.location.href);
            Object.entries(params).forEach(([key, value]) => {
                if (value) {
                    url.searchParams.set(key, value);
                } else {
                    url.searchParams.delete(key);
                }
            });
            return url.toString();
        }

        // Helper function to remove a filter
        function removeFilter(filterType) {
            const url = new URL(window.location.href);
            switch(filterType) {
                case 'date_filter':
                    // Instead of deleting, set to 'all' so we don't revert to default 'today'
                    url.searchParams.set('date_filter', 'all');
                    url.searchParams.delete('start_date');
                    url.searchParams.delete('end_date');
                    break;
                case 'custom_date':
                    url.searchParams.delete('start_date');
                    url.searchParams.delete('end_date');
                    url.searchParams.set('date_filter', 'all');
                    break;
                case 'cashier':
                    url.searchParams.delete('cashier');
                    break;
                case 'payment_method':
                    url.searchParams.delete('payment_method');
                    break;
                case 'status':
                    url.searchParams.delete('status');
                    break;
            }
            url.searchParams.set('page', 1);
            window.location.href = url.toString();
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize date range picker
            const dateRangePicker = flatpickr("#date-range", {
                mode: "range",
                dateFormat: "Y-m-d",
                defaultDate: <?= !empty($start_date) && !empty($end_date) ? "['$start_date', '$end_date']" : "[new Date().fp_incr(-7), new Date()]" ?>,
                maxDate: "today"
            });

            // Toggle custom date range field
            const dateFilter = document.getElementById('date-filter');
            const customDateRange = document.getElementById('custom-date-range');
            
            dateFilter.addEventListener('change', function() {
                if (this.value === 'custom') {
                    customDateRange.style.display = 'block';
                } else {
                    customDateRange.style.display = 'none';
                }
            });

            // Toggle filters visibility
            document.getElementById('toggle-filters').addEventListener('click', function() {
                const filtersForm = document.getElementById('filters-form');
                filtersForm.style.display = filtersForm.style.display === 'none' ? 'block' : 'none';
            });

            // Reset filters
            document.getElementById('reset-filters').addEventListener('click', function(e) {
                e.preventDefault();
                window.location.href = 'sales.php?page=1';
            });

            // Modal functionality for transaction details
            const detailsModal = document.getElementById('details-modal');
            const modalCloseBtn = detailsModal.querySelector('.modal-close');
            const modalBody = document.getElementById('modal-body');

            document.querySelectorAll('.view-details').forEach(btn => {
                btn.addEventListener('click', function() {
                    const saleId = this.getAttribute('data-id');
                    modalBody.innerHTML = '<div style="text-align:center;padding:2rem;"><span class="loading"></span> Loading...</div>';
                    detailsModal.style.display = 'flex';

                    // Fetch transaction details via AJAX
                    fetch('get_sale_details.php?sale_id=' + saleId)
                        .then(response => response.text())
                        .then(html => {
                            modalBody.innerHTML = html;
                            bindDetailsModalActions();
                        })
                        .catch(() => {
                            modalBody.innerHTML = '<div style="color:red;text-align:center;">Failed to load details.</div>';
                        });
                });
            });

            modalCloseBtn.addEventListener('click', function() {
                detailsModal.style.display = 'none';
                modalBody.innerHTML = '';
            });

            // Close modal with ESC key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && detailsModal.style.display === 'flex') {
                    detailsModal.style.display = 'none';
                    modalBody.innerHTML = '';
                }
            });

            // Bind actions inside the details modal content (copy, print)
            function bindDetailsModalActions() {
                const copyBtn = document.getElementById('btn-copy-code');
                if (copyBtn) {
                    copyBtn.addEventListener('click', function() {
                        const code = this.getAttribute('data-code') || '';
                        navigator.clipboard.writeText(code).then(() => {
                            this.textContent = 'Copied';
                            setTimeout(() => (this.textContent = 'Copy Code'), 1500);
                        }).catch(() => {
                            alert('Failed to copy');
                        });
                    });
                }

                const printBtn = document.getElementById('btn-print');
                if (printBtn) {
                    printBtn.addEventListener('click', function() {
                        const printWindow = window.open('', '_blank');
                        const styles = document.querySelector('head').innerHTML; // reuse page styles
                        const content = document.getElementById('modal-body').innerHTML;
                        printWindow.document.write(`<!DOCTYPE html><html><head>${styles}<style>body{padding:16px;font-family:Segoe UI,system-ui,sans-serif}</style></head><body>${content}<script>window.onload=()=>{window.focus();window.print();setTimeout(()=>window.close(),100);};<\/script></body></html>`);
                        printWindow.document.close();
                    });
                }
            }

            // Print receipt stub
            document.querySelectorAll('.print-receipt').forEach(btn => {
                btn.addEventListener('click', function() {
                    const saleId = this.getAttribute('data-id');
                    alert('Print functionality coming soon for transaction: ' + saleId);
                });
            });

            // Close modal when clicking outside content
            detailsModal.addEventListener('click', function(e) {
                if (e.target === detailsModal) {
                    detailsModal.style.display = 'none';
                    modalBody.innerHTML = '';
                }
            });

            // Update hidden start/end date fields on form submit
            document.getElementById('filters-form').addEventListener('submit', function(e) {
                if (dateFilter.value === 'custom') {
                    const range = document.getElementById('date-range').value.split(' to ');
                    document.getElementById('start_date').value = range[0] || '';
                    document.getElementById('end_date').value = range[1] || '';
                } else {
                    document.getElementById('start_date').value = '';
                    document.getElementById('end_date').value = '';
                }
            });
        });
    </script>
</body>
</html>