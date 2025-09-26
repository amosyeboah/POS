<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['tenant_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}
require_once '../db.php';
$tenant_id = (int)$_SESSION['tenant_id'];
$conn = getConnection();

// Read filters
$range = $_GET['date_range'] ?? 'week';
$category = isset($_GET['category']) && $_GET['category'] !== '' ? (int)$_GET['category'] : 0; // 0 => all
$employee = isset($_GET['employee']) && $_GET['employee'] !== '' ? (int)$_GET['employee'] : 0; // 0 => all
$startDateParam = $_GET['start_date'] ?? '';
$endDateParam = $_GET['end_date'] ?? '';

// Compute date range
$today = new DateTime('today');
$start = clone $today;
$end = clone $today;
switch ($range) {
    case 'today':
        break;
    case 'yesterday':
        $start->modify('-1 day');
        $end->modify('-1 day');
        break;
    case 'week':
        // Week starting Sunday
        $start->modify('-' . $today->format('w') . ' days');
        break;
    case 'month':
        $start = new DateTime($today->format('Y-m-01'));
        break;
    case 'quarter':
        $q = (int)floor(((int)$today->format('n') - 1) / 3);
        $start = new DateTime($today->format('Y') . '-' . (str_pad((string)($q*3 + 1), 2, '0', STR_PAD_LEFT)) . '-01');
        break;
    case 'year':
        $start = new DateTime($today->format('Y-01-01'));
        break;
    case 'custom':
        if ($startDateParam && $endDateParam) {
            $start = DateTime::createFromFormat('Y-m-d', $startDateParam) ?: $start;
            $end = DateTime::createFromFormat('Y-m-d', $endDateParam) ?: $end;
        }
        break;
}
$startDate = $start->format('Y-m-d') . ' 00:00:00';
$endDate = $end->format('Y-m-d') . ' 23:59:59';

// Load categories for select
$categoriesList = [];
$stmt = $conn->prepare("SELECT category_id, name FROM categories WHERE tenant_id = ? ORDER BY name ASC");
$stmt->bind_param('i', $tenant_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) { $categoriesList[] = $row; }
$stmt->close();

// Load employees for select
$employeesList = [];
$stmt = $conn->prepare("SELECT user_id, full_name FROM users WHERE tenant_id = ? AND status = 'active' ORDER BY full_name ASC");
$stmt->bind_param('i', $tenant_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) { $employeesList[] = $row; }
$stmt->close();

// Helper to build dynamic filter SQL
$filters = ["s.tenant_id = ?", "s.status = 'completed'", "s.created_at BETWEEN ? AND ?"];
$params = [$tenant_id, $startDate, $endDate];
$types = 'iss';

if ($employee > 0) {
    $filters[] = "s.user_id = ?";
    $params[] = $employee;
    $types .= 'i';
}

$joinCategory = '';
if ($category > 0) {
    $joinCategory = "JOIN sale_items si ON si.sale_id = s.sale_id JOIN products p ON p.product_id = si.product_id";
    $filters[] = "p.category_id = ?";
    $params[] = $category;
    $types .= 'i';
}
$whereClause = 'WHERE ' . implode(' AND ', $filters);

// 1) Sales Performance per day
$salesSeriesLabels = [];
$salesSeriesValues = [];
if ($category > 0) {
    $sql = "SELECT DATE(s.created_at) d, SUM(si.total_price) total
            FROM sales s
            $joinCategory
            $whereClause
            GROUP BY DATE(s.created_at)
            ORDER BY d ASC";
} else {
    $sql = "SELECT DATE(s.created_at) d, SUM(s.total) total
            FROM sales s
            $whereClause
            GROUP BY DATE(s.created_at)
            ORDER BY d ASC";
}
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) { $salesSeriesLabels[] = $row['d']; $salesSeriesValues[] = (float)$row['total']; }
$stmt->close();

// 2) Top Products (Units Sold)
$topProductsLabels = [];
$topProductsValues = [];
$sql = "SELECT p.name, SUM(si.quantity) units
        FROM sale_items si
        JOIN sales s ON s.sale_id = si.sale_id
        JOIN products p ON p.product_id = si.product_id
        $whereClause
        GROUP BY p.product_id
        ORDER BY units DESC
        LIMIT 5";
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) { $topProductsLabels[] = $row['name']; $topProductsValues[] = (int)$row['units']; }
$stmt->close();

// 3) Revenue by Category
$categoryLabels = [];
$categoryValues = [];
$sql = "SELECT c.name, SUM(si.total_price) total
        FROM sale_items si
        JOIN sales s ON s.sale_id = si.sale_id
        JOIN products p ON p.product_id = si.product_id
        JOIN categories c ON c.category_id = p.category_id
        $whereClause
        GROUP BY c.category_id
        ORDER BY total DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) { $categoryLabels[] = $row['name']; $categoryValues[] = (float)$row['total']; }
$stmt->close();

// 4) Payment Methods Breakdown
$paymentLabels = [];
$paymentValues = [];
if ($category > 0) {
    $sql = "SELECT s.payment_method, SUM(si.total_price) total
            FROM sales s
            $joinCategory
            $whereClause
            GROUP BY s.payment_method";
} else {
    $sql = "SELECT s.payment_method, SUM(s.total) total
            FROM sales s
            $whereClause
            GROUP BY s.payment_method";
}
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) { $paymentLabels[] = ucfirst($row['payment_method']); $paymentValues[] = (float)$row['total']; }
$stmt->close();

// 5) Employee Performance Table
$employeeRows = [];
if ($category > 0) {
    $sql = "SELECT u.full_name, SUM(si.total_price) sales_amount, COUNT(DISTINCT s.sale_id) transactions
            FROM sales s
            JOIN users u ON u.user_id = s.user_id
            $joinCategory
            $whereClause
            GROUP BY u.user_id
            ORDER BY sales_amount DESC";
} else {
    $sql = "SELECT u.full_name, SUM(s.total) sales_amount, COUNT(*) transactions
            FROM sales s
            JOIN users u ON u.user_id = s.user_id
            $whereClause
            GROUP BY u.user_id
            ORDER BY sales_amount DESC";
}
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) { $employeeRows[] = $row; }
$stmt->close();

// =========================
// KPI COMPUTATIONS
// =========================
// Build base filters again for clarity
$kpiFilters = ["s.tenant_id = ?", "s.status = 'completed'", "s.created_at BETWEEN ? AND ?"];
$kpiParams = [$tenant_id, $startDate, $endDate];
$kpiTypes = 'iss';
if ($employee > 0) { $kpiFilters[] = 's.user_id = ?'; $kpiParams[] = $employee; $kpiTypes .= 'i'; }
$kpiWhere = 'WHERE ' . implode(' AND ', $kpiFilters);

// Sales total (respect category if provided)
if ($category > 0) {
    $sql = "SELECT COALESCE(SUM(si.total_price),0) total
            FROM sales s
            JOIN sale_items si ON si.sale_id = s.sale_id
            JOIN products p ON p.product_id = si.product_id
            $kpiWhere AND p.category_id = ?";
    $kpiTypesWithCat = $kpiTypes . 'i';
    $kpiParamsWithCat = $kpiParams;
    $kpiParamsWithCat[] = $category;
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($kpiTypesWithCat, ...$kpiParamsWithCat);
} else {
    $sql = "SELECT COALESCE(SUM(s.total),0) total FROM sales s $kpiWhere";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($kpiTypes, ...$kpiParams);
}
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$salesTotal = (float)($row['total'] ?? 0);
$stmt->close();

// Transactions count (distinct sales)
$sql = "SELECT COUNT(DISTINCT s.sale_id) cnt FROM sales s $kpiWhere" . ($category > 0 ? " AND EXISTS (SELECT 1 FROM sale_items si JOIN products p ON p.product_id = si.product_id WHERE si.sale_id = s.sale_id AND p.category_id = ?)" : "");
if ($category > 0) {
    $kpiTypesWithCat = $kpiTypes . 'i';
    $kpiParamsWithCat = $kpiParams;
    $kpiParamsWithCat[] = $category;
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($kpiTypesWithCat, ...$kpiParamsWithCat);
} else {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($kpiTypes, ...$kpiParams);
}
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$transactions = (int)($row['cnt'] ?? 0);
$stmt->close();

// Items sold and profit earned from sold items within selected period
$kpiSiFilters = $kpiFilters;
$kpiSiTypes = $kpiTypes;
$kpiSiParams = $kpiParams;
if ($category > 0) { $kpiSiFilters[] = 'p.category_id = ?'; $kpiSiParams[] = $category; $kpiSiTypes .= 'i'; }
$kpiSiWhere = 'WHERE ' . implode(' AND ', $kpiSiFilters);

$sql = "SELECT 
            COALESCE(SUM(si.quantity),0) items,
            COALESCE(SUM((si.unit_price - COALESCE(p.cost,0)) * si.quantity),0) profit_earned
        FROM sales s
        JOIN sale_items si ON si.sale_id = s.sale_id
        JOIN products p ON p.product_id = si.product_id
        $kpiSiWhere";
$stmt = $conn->prepare($sql);
$stmt->bind_param($kpiSiTypes, ...$kpiSiParams);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$itemsSold = (int)($row['items'] ?? 0);
$profitEarned = (float)($row['profit_earned'] ?? 0);
$stmt->close();

// Inventory-based KPIs (ignore date/employee; respect category)
$invFilters = ["p.tenant_id = ?", "p.status = 'active'"];
$invParams = [$tenant_id];
$invTypes = 'i';
if ($category > 0) { $invFilters[] = 'p.category_id = ?'; $invParams[] = $category; $invTypes .= 'i'; }
$invWhere = 'WHERE ' . implode(' AND ', $invFilters);

$sql = "SELECT 
            COALESCE(SUM(COALESCE(p.cost,0) * COALESCE(p.stock,0)),0) inv_value,
            COALESCE(SUM((COALESCE(p.price,0) - COALESCE(p.cost,0)) * COALESCE(p.stock,0)),0) inv_expected_profit
        FROM products p
        $invWhere";
$stmt = $conn->prepare($sql);
$stmt->bind_param($invTypes, ...$invParams);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$inventoryValue = (float)($row['inv_value'] ?? 0);
$inventoryExpectedProfit = (float)($row['inv_expected_profit'] ?? 0);
$stmt->close();

// Taxes collected
$sql = "SELECT COALESCE(SUM(s.tax_amount),0) taxes FROM sales s $kpiWhere" . ($category > 0 ? " AND EXISTS (SELECT 1 FROM sale_items si JOIN products p ON p.product_id = si.product_id WHERE si.sale_id = s.sale_id AND p.category_id = ?)" : "");
if ($category > 0) {
    $kpiTypesWithCat = $kpiTypes . 'i';
    $kpiParamsWithCat = $kpiParams;
    $kpiParamsWithCat[] = $category;
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($kpiTypesWithCat, ...$kpiParamsWithCat);
} else {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($kpiTypes, ...$kpiParams);
}
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$taxes = (float)($row['taxes'] ?? 0);
$stmt->close();

$avgTxn = $transactions > 0 ? ($salesTotal / $transactions) : 0;
$grossMarginPct = $salesTotal > 0 ? ($profitEarned / $salesTotal) * 100.0 : 0.0;

$reportData = [
    'sales' => ['labels' => $salesSeriesLabels, 'values' => $salesSeriesValues],
    'topProducts' => ['labels' => $topProductsLabels, 'values' => $topProductsValues],
    'byCategory' => ['labels' => $categoryLabels, 'values' => $categoryValues],
    'payments' => ['labels' => $paymentLabels, 'values' => $paymentValues],
    'employees' => $employeeRows,
    'filters' => [
        'range' => $range,
        'start' => (new DateTime($startDate))->format('Y-m-d'),
        'end' => (new DateTime($endDate))->format('Y-m-d'),
        'category' => $category,
        'employee' => $employee,
    ],
    'kpis' => [
        'sales_total' => $salesTotal,
        'transactions' => $transactions,
        'avg_transaction' => $avgTxn,
        'items_sold' => $itemsSold,
        'total_value' => $inventoryValue, // inventory value = sum(cost * stock)
        'profit_earned' => $profitEarned, // from sold items over period
        'profit_expected' => $inventoryExpectedProfit, // from inventory yet to sell
        'gross_margin_pct' => $grossMarginPct,
        'taxes' => $taxes,
    ]
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports | POS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
   <link rel="stylesheet" href="css/admin.css">
   <link rel="stylesheet" href="css/reports.css">

   <style>
            /* KPI Section */
            .kpi-section {
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow);
            border: 1px solid var(--gray-200);
        }

        .kpi-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
            color: var(--gray-800);
        }

        .kpi-header i {
            color: var(--primary);
        }

        .kpi-header h2 {
            font-size: 1.25rem;
            font-weight: 700;
        }

        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .kpi-card {
            position: relative;
            overflow: hidden;
            border-radius: var(--radius-lg);
            padding: 1rem 1.25rem;
            color: var(--white);
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
            box-shadow: var(--shadow);
            transform: translateZ(0);
        }

        .kpi-card::after {
            content: '';
            position: absolute;
            right: -20px;
            top: -20px;
            width: 120px;
            height: 120px;
            background: rgba(255,255,255,0.12);
            border-radius: 50%;
            filter: blur(0.5px);
        }

        .kpi-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(2px);
            box-shadow: inset 0 0 0 1px rgba(255,255,255,0.25);
        }

        .kpi-value {
            font-size: 1.5rem;
            font-weight: 800;
            line-height: 1.1;
            letter-spacing: -0.02em;
        }

        .kpi-label {
            font-size: 0.85rem;
            opacity: 0.95;
            font-weight: 600;
        }

        /* Color variants */
        .kpi-card.kpi-sales {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }
        .kpi-card.kpi-trans {
            background: linear-gradient(135deg, #0ea5e9 0%, #2563eb 100%);
        }
        .kpi-card.kpi-avg {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        }
        .kpi-card.kpi-items {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }
        .kpi-card.kpi-value-card {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
        }
        .kpi-card.kpi-profit {
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
        }
        .kpi-card.kpi-exp {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
        }
        .kpi-card.kpi-margin {
            background: linear-gradient(135deg, #64748b 0%, #475569 100%);
        }
        .kpi-card.kpi-taxes {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }

        .kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        @media (max-width: 768px) {
            .kpi-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .kpi-grid {
                grid-template-columns: 1fr;
            }
        }
   </style>
</head>
<body>
    <div class="admin-container">
        <!-- Dark Overlay for Mobile -->
        <div class="overlay" id="overlay"></div>

        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="logo">
                <i class="fas fa-cash-register"></i>
                <span>POS Admin</span>
            </div>
            <nav>
                <ul>
                    <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="inventory/index.php"><i class="fas fa-boxes"></i> Inventory</a></li>
                    <li><a href="employee.php"><i class="fas fa-users"></i> Employees</a></li>
                    <li class="active"><a href="report.php"><i class="fas fa-chart-line"></i> Reports</a></li>
                    <li><a href="categories.php"><i class="fas fa-tags"></i> Categories</a></li>
                    <li><a href="sales.php"><i class="fas fa-shopping-cart"></i> Sales</a></li>
                    <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Bar -->
            <header class="top-bar">
                <div class="page-title">
                    <h1>Reports & Analytics</h1>
                    <p>Comprehensive business insights and performance metrics</p>
                </div>
                <button class="menu-toggle" id="menu-toggle">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="user-profile">
                    <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=100&h=100&fit=crop&crop=face" alt="Admin">
                    <span>John Admin</span>
                </div>
            </header>

            <!-- Report Filters -->
            <section class="filters-section">
                <div class="filters-header">
                    <div class="filters-title">
                        <i class="fas fa-filter"></i>
                        Report Filters
                    </div>
                </div>
                <form id="filters-form" method="get" class="filters-grid">
                    <div class="filter-group">
                        <label for="date-range">Date Range</label>
                        <select id="date-range" name="date_range">
                            <?php
                            $ranges = [
                                'today' => 'Today',
                                'yesterday' => 'Yesterday',
                                'week' => 'This Week',
                                'month' => 'This Month',
                                'quarter' => 'This Quarter',
                                'year' => 'This Year',
                                'custom' => 'Custom Range'
                            ];
                            foreach ($ranges as $val => $label) {
                                $sel = $reportData['filters']['range'] === $val ? 'selected' : '';
                                echo "<option value=\"$val\" $sel>$label</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="start-date">Start Date</label>
                        <input type="date" id="start-date" name="start_date" value="<?= htmlspecialchars($reportData['filters']['start']) ?>">
                    </div>
                    <div class="filter-group">
                        <label for="end-date">End Date</label>
                        <input type="date" id="end-date" name="end_date" value="<?= htmlspecialchars($reportData['filters']['end']) ?>">
                    </div>
                    <div class="filter-group">
                        <label for="category">Category</label>
                        <select id="category" name="category">
                            <option value="">All Categories</option>
                            <?php foreach ($categoriesList as $cat): ?>
                                <option value="<?= (int)$cat['category_id'] ?>" <?= $reportData['filters']['category'] === (int)$cat['category_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="employee">Employee</label>
                        <select id="employee" name="employee">
                            <option value="">All Employees</option>
                            <?php foreach ($employeesList as $emp): ?>
                                <option value="<?= (int)$emp['user_id'] ?>" <?= $reportData['filters']['employee'] === (int)$emp['user_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($emp['full_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
                <div class="filter-actions">
                    <button class="btn btn-primary" onclick="applyFilters()">
                        <i class="fas fa-search"></i>
                        Apply Filters
                    </button>
                    <button class="btn btn-secondary" onclick="resetFilters()">
                        <i class="fas fa-undo"></i>
                        Reset
                    </button>
                    <button class="btn btn-success" onclick="exportAllReports()">
                        <i class="fas fa-download"></i>
                        Export All
                    </button>
                </div>
            </section>

            <!-- KPI Section -->
            <section class="kpi-section">
                <div class="kpi-header">
                    <i class="fas fa-lightbulb"></i>
                    <h2>Key Performance Indicators</h2>
                </div>
                <div class="kpi-grid">
                    <div class="kpi-card kpi-sales">
                        <div class="kpi-icon"><i class="fas fa-dollar-sign"></i></div>
                        <div class="kpi-value">$<?= number_format($reportData['kpis']['sales_total'] ?? 0, 2) ?></div>
                        <div class="kpi-label">Total Sales</div>
                    </div>
                    <div class="kpi-card kpi-trans">
                        <div class="kpi-icon"><i class="fas fa-receipt"></i></div>
                        <div class="kpi-value"><?= number_format((int)($reportData['kpis']['transactions'] ?? 0)) ?></div>
                        <div class="kpi-label">Transactions</div>
                    </div>
                    <div class="kpi-card kpi-avg">
                        <div class="kpi-icon"><i class="fas fa-money-bill-wave"></i></div>
                        <div class="kpi-value">$<?= number_format($reportData['kpis']['avg_transaction'] ?? 0, 2) ?></div>
                        <div class="kpi-label">Avg. Transaction</div>
                    </div>
                    <div class="kpi-card kpi-items">
                        <div class="kpi-icon"><i class="fas fa-box"></i></div>
                        <div class="kpi-value"><?= number_format((int)($reportData['kpis']['items_sold'] ?? 0)) ?></div>
                        <div class="kpi-label">Items Sold</div>
                    </div>
                    <div class="kpi-card kpi-value-card">
                        <div class="kpi-icon"><i class="fas fa-sack-dollar"></i></div>
                        <div class="kpi-value">$<?= number_format($reportData['kpis']['total_value'] ?? 0, 2) ?></div>
                        <div class="kpi-label">Total Value</div>
                    </div>
                    <div class="kpi-card kpi-profit">
                        <div class="kpi-icon"><i class="fas fa-coins"></i></div>
                        <div class="kpi-value">$<?= number_format($reportData['kpis']['profit_earned'] ?? 0, 2) ?></div>
                        <div class="kpi-label">Profits Earned</div>
                    </div>
                    <div class="kpi-card kpi-exp">
                        <div class="kpi-icon"><i class="fas fa-bullseye"></i></div>
                        <div class="kpi-value">$<?= number_format($reportData['kpis']['profit_expected'] ?? 0, 2) ?></div>
                        <div class="kpi-label">Profits Expected</div>
                    </div>
                    <div class="kpi-card kpi-margin">
                        <div class="kpi-icon"><i class="fas fa-percent"></i></div>
                        <div class="kpi-value"><?= number_format($reportData['kpis']['gross_margin_pct'] ?? 0, 2) ?>%</div>
                        <div class="kpi-label">Gross Profit Margin</div>
                    </div>
                    <div class="kpi-card kpi-taxes">
                        <div class="kpi-icon"><i class="fas fa-file-invoice-dollar"></i></div>
                        <div class="kpi-value">$<?= number_format($reportData['kpis']['taxes'] ?? 0, 2) ?></div>
                        <div class="kpi-label">Taxes Collected</div>
                    </div>
                </div>
            </section>

            <!-- Reports Grid -->
            <div class="reports-grid">
                <!-- Sales Performance -->
                <div class="report-card wide">
                    <div class="report-header">
                        <div class="report-title">
                            <i class="fas fa-chart-line"></i>
                            Sales Performance
                        </div>
                        <button class="export-btn" onclick="exportChart('salesChart')">
                            <i class="fas fa-download"></i>
                        </button>
                    </div>
                    <div class="chart-container chart-large">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>

                <!-- Top Products -->
                <div class="report-card">
                    <div class="report-header">
                        <div class="report-title">
                            <i class="fas fa-trophy"></i>
                            Top Selling Products
                        </div>
                        <button class="export-btn" onclick="exportChart('topProductsChart')">
                            <i class="fas fa-download"></i>
                        </button>
                    </div>
                    <div class="chart-container">
                        <canvas id="topProductsChart"></canvas>
                    </div>
                </div>

                <!-- Revenue by Category -->
                <div class="report-card">
                    <div class="report-header">
                        <div class="report-title">
                            <i class="fas fa-chart-pie"></i>
                            Revenue by Category
                        </div>
                        <button class="export-btn" onclick="exportChart('categoryChart')">
                            <i class="fas fa-download"></i>
                        </button>
                    </div>
                    <div class="chart-container">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>

                <!-- Employee Performance -->
                <div class="report-card">
                    <div class="report-header">
                        <div class="report-title">
                            <i class="fas fa-user-friends"></i>
                            Employee Performance
                        </div>
                        <button class="export-btn" onclick="exportTable('employeeTable')">
                            <i class="fas fa-download"></i>
                        </button>
                    </div>
                    <table class="data-table" id="employeeTable">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Sales</th>
                                <th>Transactions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($reportData['employees'])): ?>
                                <tr><td colspan="3">No data for the selected filters.</td></tr>
                            <?php else: ?>
                                <?php foreach ($reportData['employees'] as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['full_name']) ?></td>
                                    <td><?= number_format((float)$row['sales_amount'], 2) ?></td>
                                    <td><?= (int)$row['transactions'] ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Inventory Analysis -->
                <div class="report-card">
                    <div class="report-header">
                        <div class="report-title">
                            <i class="fas fa-boxes"></i>
                            Inventory Analysis
                        </div>
                        <button class="export-btn" onclick="exportChart('inventoryChart')">
                            <i class="fas fa-download"></i>
                        </button>
                    </div>
                    <div class="chart-container">
                        <canvas id="inventoryChart"></canvas>
                    </div>
                </div>

                <!-- Payment Methods -->
                <div class="report-card">
                    <div class="report-header">
                        <div class="report-title">
                            <i class="fas fa-credit-card"></i>
                            Payment Methods
                        </div>
                        <button class="export-btn" onclick="exportChart('paymentChart')">
                            <i class="fas fa-download"></i>
                        </button>
                    </div>
                    <div class="chart-container">
                        <canvas id="paymentChart"></canvas>
                    </div>
                </div>
                </div> <!-- End of Reports Grid -->
        </main> <!-- End of Main Content -->
    </div> <!-- End of Admin Container -->

    <!-- JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    const REPORT_DATA = <?= json_encode($reportData) ?>;
    // Reports page functionality and Chart initializations
    document.addEventListener('DOMContentLoaded', () => {
        // Filters
        const dateRange = document.getElementById('date-range');
        const startDate = document.getElementById('start-date');
        const endDate = document.getElementById('end-date');
        const form = document.getElementById('filters-form');

        function setRange(range) {
            if (range !== 'custom') {
                // Server computed dates are already placed; no change needed here
                // But we can disable manual dates when not custom
                startDate.disabled = false;
                endDate.disabled = false;
            }
        }
        setRange(dateRange.value);
        dateRange.addEventListener('change', (e) => {
            if (e.target.value === 'custom') {
                startDate.disabled = false;
                endDate.disabled = false;
            } else {
                startDate.disabled = false;
                endDate.disabled = false;
            }
        });

        // Wire buttons
        window.applyFilters = function applyFilters() {
            form.submit();
        };
        window.resetFilters = function resetFilters() {
            window.location = 'report.php';
        };

        // Charts
        const salesCtx = document.getElementById('salesChart')?.getContext('2d');
        if (salesCtx) {
            new Chart(salesCtx, {
                type: 'line',
                data: {
                    labels: REPORT_DATA.sales.labels,
                    datasets: [{
                        label: 'Sales',
                        data: REPORT_DATA.sales.values,
                        borderColor: '#4f46e5',
                        backgroundColor: 'rgba(79,70,229,0.1)',
                        fill: true,
                        tension: 0.3,
                    }]
                },
                options: { responsive: true, plugins: { legend: { display: false } } }
            });
        }

        const topProductsCtx = document.getElementById('topProductsChart')?.getContext('2d');
        if (topProductsCtx) {
            new Chart(topProductsCtx, {
                type: 'bar',
                data: {
                    labels: REPORT_DATA.topProducts.labels,
                    datasets: [{
                        label: 'Units Sold',
                        data: REPORT_DATA.topProducts.values,
                        backgroundColor: ['#10b981','#3b82f6','#f59e0b','#ef4444','#8b5cf6']
                    }]
                },
                options: { responsive: true, plugins: { legend: { display: false } } }
            });
        }

        const categoryCtx = document.getElementById('categoryChart')?.getContext('2d');
        if (categoryCtx) {
            new Chart(categoryCtx, {
                type: 'doughnut',
                data: {
                    labels: REPORT_DATA.byCategory.labels,
                    datasets: [{
                        data: REPORT_DATA.byCategory.values,
                        backgroundColor: ['#6366f1','#22c55e','#f97316','#e11d48','#0ea5e9','#f43f5e']
                    }]
                },
                options: { responsive: true }
            });
        }

        const inventoryCtx = document.getElementById('inventoryChart')?.getContext('2d');
        if (inventoryCtx) {
            new Chart(inventoryCtx, {
                type: 'bar',
                data: {
                    labels: ['Low','Optimal','Overstock'],
                    datasets: [{
                        label: 'Items',
                        data: [0, 0, 0], // Placeholder; implement real inventory analysis later if needed
                        backgroundColor: ['#ef4444','#10b981','#f59e0b']
                    }]
                },
                options: { responsive: true, plugins: { legend: { display: false } } }
            });
        }

        const paymentCtx = document.getElementById('paymentChart')?.getContext('2d');
        if (paymentCtx) {
            new Chart(paymentCtx, {
                type: 'pie',
                data: {
                    labels: REPORT_DATA.payments.labels,
                    datasets: [{
                        data: REPORT_DATA.payments.values,
                        backgroundColor: ['#f59e0b','#3b82f6','#10b981','#8b5cf6']
                    }]
                },
                options: { responsive: true }
            });
        }

        // Export helpers remain the same
        window.exportChart = function exportChart(canvasId) {
            const canvas = document.getElementById(canvasId);
            if (!canvas) return alert('Chart not found: ' + canvasId);
            const link = document.createElement('a');
            link.href = canvas.toDataURL('image/png');
            link.download = `${canvasId}.png`;
            link.click();
        };

        window.exportTable = function exportTable(tableId) {
            const table = document.getElementById(tableId);
            if (!table) return alert('Table not found: ' + tableId);
            let csv = '';
            const rows = table.querySelectorAll('tr');
            rows.forEach(row => {
                const cells = Array.from(row.querySelectorAll('th,td')).map(c => '"' + c.innerText.replace(/"/g, '""') + '"');
                if (cells.length) csv += cells.join(',') + '\n';
            });
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = `${tableId}.csv`;
            link.click();
            URL.revokeObjectURL(url);
        };

        window.exportAllReports = function exportAllReports() {
            ['salesChart','topProductsChart','categoryChart','inventoryChart','paymentChart'].forEach(exportChart);
            exportTable('employeeTable');
        };
    });
    </script>

    <!-- Sidebar Toggle Script -->
    <script>
        const menuToggle = document.getElementById('menu-toggle');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');

        menuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        });

        overlay.addEventListener('click', () => {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
        });
    </script>
</body>
</html>
