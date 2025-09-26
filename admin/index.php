<?php
// bootstrap: fetch real data for dashboard summary cards
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['tenant_id'])) {
    header('Location: ../login.php');
    exit;
}
$tenant_id = (int)$_SESSION['tenant_id'];

function getCurrencySymbol($currencyType) {
    switch (strtoupper($currencyType)) {
        case 'GHS': return '₵';
        case 'USD': return '$';
        case 'EUR': return '€';
        case 'GBP': return '£';
        case 'NGN': return '₦';
        default: return $currencyType;
    }
}

$conn = getConnection();

// currency
$currency_type = 'GHS';
if ($stmt = $conn->prepare('SELECT currency_type FROM tenants WHERE tenant_id = ?')) {
    $stmt->bind_param('i', $tenant_id);
    $stmt->execute();
    $stmt->bind_result($currency_type);
    $stmt->fetch();
    $stmt->close();
}
$currency_symbol = getCurrencySymbol($currency_type);

// Store name (settings.store_name fallback tenants.business_name)
$store_name = '';
if ($stmt = $conn->prepare("SELECT setting_value FROM settings WHERE tenant_id = ? AND setting_key = 'store_name' LIMIT 1")) {
    $stmt->bind_param('i', $tenant_id);
    $stmt->execute();
    $stmt->bind_result($store_name);
    $stmt->fetch();
    $stmt->close();
}
if ($store_name === '' || $store_name === null) {
    if ($stmt = $conn->prepare('SELECT business_name FROM tenants WHERE tenant_id = ?')) {
        $stmt->bind_param('i', $tenant_id);
        $stmt->execute();
        $stmt->bind_result($store_name);
        $stmt->fetch();
        $stmt->close();
    }
}
if (!$store_name) { $store_name = 'My Store'; }

// Store logo URL (settings.store_logo_url or settings.logo_url), fallback to UI Avatars
$store_logo_url = '';
if ($stmt = $conn->prepare("SELECT setting_value FROM settings WHERE tenant_id = ? AND setting_key IN ('store_logo_url','logo_url') ORDER BY setting_key LIMIT 1")) {
    $stmt->bind_param('i', $tenant_id);
    $stmt->execute();
    $stmt->bind_result($store_logo_url);
    $stmt->fetch();
    $stmt->close();
}
if (!$store_logo_url) {
    $encoded = urlencode($store_name);
    $store_logo_url = "https://ui-avatars.com/api/?name={$encoded}&background=6366f1&color=fff&size=100&rounded=true";
}

// today's sales (completed only)
$today_sales = 0.0;
if ($stmt = $conn->prepare("SELECT COALESCE(SUM(total),0) FROM sales WHERE tenant_id = ? AND status = 'completed' AND DATE(created_at) = CURDATE()")) {
    $stmt->bind_param('i', $tenant_id);
    $stmt->execute();
    $stmt->bind_result($today_sales);
    $stmt->fetch();
    $stmt->close();
}

// low stock items
$low_stock = 0;
if ($stmt = $conn->prepare('SELECT COUNT(*) FROM products WHERE tenant_id = ? AND stock <= COALESCE(min_stock, 0)')) {
    $stmt->bind_param('i', $tenant_id);
    $stmt->execute();
    $stmt->bind_result($low_stock);
    $stmt->fetch();
    $stmt->close();
}

// active staff
$active_staff = 0;
if ($stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE tenant_id = ? AND status = 'active'")) {
    $stmt->bind_param('i', $tenant_id);
    $stmt->execute();
    $stmt->bind_result($active_staff);
    $stmt->fetch();
    $stmt->close();
}

// categories count
$categories_count = 0;
if ($stmt = $conn->prepare('SELECT COUNT(*) FROM categories WHERE tenant_id = ?')) {
    $stmt->bind_param('i', $tenant_id);
    $stmt->execute();
    $stmt->bind_result($categories_count);
    $stmt->fetch();
    $stmt->close();
}

// total products
$total_products = 0;
if ($stmt = $conn->prepare('SELECT COUNT(*) FROM products WHERE tenant_id = ?')) {
    $stmt->bind_param('i', $tenant_id);
    $stmt->execute();
    $stmt->bind_result($total_products);
    $stmt->fetch();
    $stmt->close();
}

// out of stock
$out_of_stock = 0;
if ($stmt = $conn->prepare('SELECT COUNT(*) FROM products WHERE tenant_id = ? AND stock <= 0')) {
    $stmt->bind_param('i', $tenant_id);
    $stmt->execute();
    $stmt->bind_result($out_of_stock);
    $stmt->fetch();
    $stmt->close();
}

// Weekly sales (last 7 days including today)
$weekly_labels = [];
$weekly_data = [];
$dayTotals = [];
if ($stmt = $conn->prepare("SELECT DATE(created_at) d, COALESCE(SUM(total),0) t
                             FROM sales
                             WHERE tenant_id = ? AND status = 'completed' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
                             GROUP BY DATE(created_at)")) {
    $stmt->bind_param('i', $tenant_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $dayTotals[$row['d']] = (float)$row['t'];
    }
    $stmt->close();
}
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-{$i} days"));
    $weekly_labels[] = date('D', strtotime($date));
    $weekly_data[] = isset($dayTotals[$date]) ? round($dayTotals[$date], 2) : 0;
}

// Top 5 products by units sold in last 30 days
$top_labels = [];
$top_data = [];
if ($stmt = $conn->prepare("SELECT p.name, SUM(si.quantity) qty
                             FROM sale_items si
                             JOIN sales s ON si.sale_id = s.sale_id
                             JOIN products p ON si.product_id = p.product_id
                             WHERE s.tenant_id = ? AND s.status = 'completed' AND s.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                             GROUP BY p.product_id, p.name
                             ORDER BY qty DESC
                             LIMIT 5")) {
    $stmt->bind_param('i', $tenant_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $top_labels[] = $row['name'];
        $top_data[] = (int)$row['qty'];
    }
    $stmt->close();
}

// Recent sales activity (latest 5 sales)
$recent_sales = [];
if ($stmt = $conn->prepare("SELECT s.transaction_code, s.total, s.created_at, u.full_name AS cashier
                             FROM sales s
                             JOIN users u ON s.user_id = u.user_id
                             WHERE s.tenant_id = ?
                             ORDER BY s.created_at DESC
                             LIMIT 5")) {
    $stmt->bind_param('i', $tenant_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $recent_sales[] = $row;
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | POS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <div class="admin-container">
        <!-- Dark Overlay for Mobile -->
        <div class="overlay" id="overlay"></div>

        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="logo">
                <i class="fas fa-cash-register"></i>
                <span><?= htmlspecialchars($store_name) ?></span>
            </div>
            <nav>
                <ul>
                    <li class="active"><a href="#"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="inventory/index.php"><i class="fas fa-boxes"></i> Inventory</a></li>
                    <li><a href="employee.php"><i class="fas fa-users"></i> Employees</a></li>
                    <li><a href="report.php"><i class="fas fa-chart-line"></i> Reports</a></li>
                    <li><a href="categories.php"><i class="fas fa-tags"></i> Categories</a></li>
                    <!-- <li><a href="inventory/p.php"><i class="fas fa-tags"></i> Products</a></li> -->
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
                    <h1>Dashboard</h1>
                    <p>Welcome back! Here's what's happening today.</p>
                </div>
                <button class="menu-toggle" id="menu-toggle">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="user-profile">
                    <img src="<?= htmlspecialchars($store_logo_url) ?>" alt="<?= htmlspecialchars($store_name) ?> Logo">
                    <span><?= htmlspecialchars($store_name) ?></span>
                </div>
            </header>

            <!-- Summary Cards -->
            <div class="summary-grid">
                <div class="summary-card sales">
                    <div class="content">
                        <div class="info">
                            <h3><?= $currency_symbol . number_format((float)$today_sales, 2) ?></h3>
                            <p>Today's Sales</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                    </div>
                </div>

                <div class="summary-card inventory">
                    <div class="content">
                        <div class="info">
                            <h3><?= (int)$low_stock ?></h3>
                            <p>Low Stock Items</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                    </div>
                </div>

                <div class="summary-card employees">
                    <div class="content">
                        <div class="info">
                            <h3><?= (int)$active_staff ?></h3>
                            <p>Active Staff</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>

                <div class="summary-card categories">
                    <div class="content">
                        <div class="info">
                            <h3><?= (int)$categories_count ?></h3>
                            <p>Categories</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-tags"></i>
                        </div>
                    </div>
                </div>

                <div class="summary-card products">
                    <div class="content">
                        <div class="info">
                            <h3><?= (int)$total_products ?></h3>
                            <p>Total Products</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-cube"></i>
                        </div>
                    </div>
                </div>

                <div class="summary-card out_of_stock">
                    <div class="content">
                        <div class="info">
                            <h3><?= (int)$out_of_stock ?></h3>
                            <p>Out of Stock</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-times-circle"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dashboard Widgets -->
            <div class="dashboard-widgets">
                <!-- Quick Actions -->
                <div class="widget quick-actions">
                    <h3>Quick Actions</h3>
                    <div class="action-grid">
                        <a href="#" class="action-btn">
                            <i class="fas fa-plus-circle"></i>
                            <span>Add Product</span>
                        </a>
                        <a href="#" class="action-btn">
                            <i class="fas fa-user-plus"></i>
                            <span>Add Employee</span>
                        </a>
                        <a href="#" class="action-btn">
                            <i class="fas fa-file-export"></i>
                            <span>Export Report</span>
                        </a>
                        <a href="#" class="action-btn">
                            <i class="fas fa-bell"></i>
                            <span>View Alerts</span>
                        </a>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="widget recent-activity">
                    <h3>Recent Activity</h3>
                    <ul>
                        <?php if (!empty($recent_sales)): ?>
                            <?php foreach ($recent_sales as $rs): ?>
                                <li>
                                    <div class="activity-icon sale">
                                        <i class="fas fa-shopping-cart"></i>
                                    </div>
                                    <div class="activity-details">
                                        <p>Sale #<?= htmlspecialchars($rs['transaction_code']) ?> - <?= $currency_symbol . number_format((float)$rs['total'], 2) ?></p>
                                        <small><?= date('M j, g:i A', strtotime($rs['created_at'])) ?> · Cashier: <?= htmlspecialchars($rs['cashier']) ?></small>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li>
                                <div class="activity-icon">
                                    <i class="fas fa-info-circle"></i>
                                </div>
                                <div class="activity-details">
                                    <p>No recent activity yet.</p>
                                </div>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- Sales Chart -->
                <div class="widget sales-chart">
                    <h3>Weekly Sales Overview</h3>
                    <div class="chart-container">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>

                <!-- Top Selling Products Chart -->
                <div class="widget top-products">
                    <h3>Top 5 Best Selling Products</h3>
                    <div class="chart-container chart-small">
                        <canvas id="topProductsChart"></canvas>
                    </div>
                </div>
            </div>
        </main>

        <!-- Floating Menu Button for Mobile -->
        <button class="floating-menu" id="floating-menu">
            <i class="fas fa-bars"></i>
        </button>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Mobile menu functionality
        const menuToggle = document.getElementById('menu-toggle');
        const floatingMenu = document.getElementById('floating-menu');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');

        function toggleSidebar() {
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        }

        function closeSidebar() {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
        }

        menuToggle?.addEventListener('click', toggleSidebar);
        floatingMenu?.addEventListener('click', toggleSidebar);
        overlay?.addEventListener('click', closeSidebar);

        // Close sidebar on window resize if screen becomes large
        window.addEventListener('resize', () => {
            if (window.innerWidth > 768) {
                closeSidebar();
            }
        });

        // Sales Chart (Weekly)
        const currencySymbol = "<?= $currency_symbol ?>";
        const weeklyLabels = <?= json_encode($weekly_labels) ?>;
        const weeklyData = <?= json_encode($weekly_data) ?>;

        const salesCtx = document.getElementById('salesChart').getContext('2d');
        new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: weeklyLabels,
                datasets: [{
                    label: 'Sales',
                    data: weeklyData,
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#6366f1',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 6,
                    pointHoverRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#f3f4f6'
                        },
                        ticks: {
                            color: '#6b7280',
                            callback: function(value) { return currencySymbol + value; }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#6b7280'
                        }
                    }
                }
            }
        });

        // Top Products Chart
        const topLabels = <?= json_encode($top_labels) ?>;
        const topData = <?= json_encode($top_data) ?>;

        const topProductsCtx = document.getElementById('topProductsChart').getContext('2d');
        new Chart(topProductsCtx, {
            type: 'bar',
            data: {
                labels: topLabels,
                datasets: [{
                    label: 'Units Sold',
                    data: topData,
                    backgroundColor: [
                        '#3b82f6',
                        '#10b981',
                        '#f59e0b',
                        '#ef4444',
                        '#8b5cf6'
                    ],
                    borderRadius: 6,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#f3f4f6'
                        },
                        ticks: {
                            color: '#6b7280'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#6b7280'
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
