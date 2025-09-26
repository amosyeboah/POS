<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | POS</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">

    <style>
       :root {
    --primary: #4361ee;
    --secondary: #3f37c9;
    --accent: #4cc9f0;
    --danger: #f72585;
    --success: #4ad66d;
    --warning: #f8961e;
    --dark: #212529;
    --light: #f8f9fa;
    --gray: #6c757d;
    --radius: 12px;
    --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Poppins', sans-serif;
}

body {
    background-color: #f5f7fa;
    color: var(--dark);
}

.admin-container {
    display: flex;
    min-height: 100vh;
}

/* Sidebar */
.sidebar {
    width: 250px;
    background: white;
    box-shadow: var(--shadow);
    transition: transform 0.3s ease;
    z-index: 100;
}

.logo {
    display: flex;
    align-items: center;
    padding: 1.5rem;
    color: var(--primary);
    font-size: 1.2rem;
    font-weight: 600;
}

.logo i {
    font-size: 1.5rem;
    margin-right: 0.75rem;
}

nav ul {
    list-style: none;
}

nav li a {
    display: flex;
    align-items: center;
    padding: 1rem 1.5rem;
    color: var(--gray);
    text-decoration: none;
    transition: all 0.3s;
}

nav li a:hover, nav li.active a {
    color: var(--primary);
    background-color: rgba(67, 97, 238, 0.1);
}

nav li a i {
    margin-right: 0.75rem;
    width: 20px;
    text-align: center;
}

/* Main Content */
.main-content {
    flex: 1;
    padding: 1.5rem;
    transition: margin-left 0.3s;
}

.top-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.menu-toggle {
    background: none;
    border: none;
    font-size: 1.25rem;
    color: var(--dark);
    cursor: pointer;
    display: none;
}

.user-profile {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.user-profile img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

/* Dashboard Widgets */
.dashboard-widgets {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
}

.widget {
    background: white;
    border-radius: var(--radius);
    padding: 1.5rem;
    box-shadow: var(--shadow);
}

.summary-card {
    display: flex;
    align-items: center;
    position: relative;
    overflow: hidden;
}

.summary-card .icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin-right: 1rem;
    color: white;
}

.summary-card.sales .icon { background: var(--success); }
.summary-card.inventory .icon { background: var(--warning); }
.summary-card.employees .icon { background: var(--primary); }

.summary-card .info h3 {
    font-size: 1.75rem;
    margin-bottom: 0.25rem;
}

.summary-card .info p {
    color: var(--gray);
    font-size: 0.9rem;
}

.trend {
    position: absolute;
    right: 1.5rem;
    font-size: 0.85rem;
    padding: 0.25rem 0.5rem;
    border-radius: 20px;
}

.trend.up {
    background: rgba(74, 214, 109, 0.2);
    color: var(--success);
}

.trend.down {
    background: rgba(248, 150, 30, 0.2);
    color: var(--warning);
}

/* Quick Actions */
.action-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
    margin-top: 1rem;
}

.action-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 1.5rem 1rem;
    background: var(--light);
    border-radius: var(--radius);
    text-decoration: none;
    color: var(--dark);
    transition: all 0.3s;
}

.action-btn:hover {
    background: var(--primary);
    color: white;
    transform: translateY(-3px);
}

.action-btn i {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
}

.action-btn span {
    font-size: 0.9rem;
}

/* Recent Activity */
.recent-activity li {
    display: flex;
    padding: 1rem 0;
    border-bottom: 1px solid #eee;
}

.recent-activity li:last-child {
    border-bottom: none;
}

.activity-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    color: white;
}

.activity-icon.sale { background: var(--success); }
.activity-icon.inventory { background: var(--warning); }
.activity-icon.employee { background: var(--primary); }

.activity-details p {
    font-weight: 500;
}

.activity-details small {
    color: var(--gray);
    font-size: 0.8rem;
}

/* Chart */
.chart-container {
    position: relative;
    height: 250px;
    margin-top: 1rem;
}

/* Mobile Responsiveness */
@media (max-width: 768px) {
    .sidebar {
        position: fixed;
        transform: translateX(-100%);
        height: 100vh;
    }

    .sidebar.active {
        transform: translateX(0);
    }

    .main-content {
        margin-left: 0;
    }

    .menu-toggle {
        display: block;
    }

    .dashboard-widgets {
        grid-template-columns: 1fr;
    }
}
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar (Hidden on mobile) -->
        <aside class="sidebar">
            <div class="logo">
                <i class="fas fa-cash-register"></i>
                <span>POS Admin</span>
            </div>
            <nav>
                <ul>
                    <li class="active"><a href="#"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="#"><i class="fas fa-boxes"></i> Inventory</a></li>
                    <li><a href="#"><i class="fas fa-users"></i> Employees</a></li>
                    <li><a href="#"><i class="fas fa-chart-line"></i> Reports</a></li>
                    <li><a href="#"><i class="fas fa-cog"></i> Settings</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Bar -->
            <header class="top-bar">
                <button class="menu-toggle" id="menu-toggle">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="user-profile">
                    <span>Admin User</span>
                    <!-- <img src="../../assets/images/admin-avatar.jpg" alt="Admin"> -->
                </div>
            </header>

            <!-- Dashboard Widgets -->
            <div class="dashboard-widgets">
                <!-- Summary Cards -->
                <div class="widget summary-card sales">
                    <div class="icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="info">
                        <h3>$12,845</h3>
                        <p>Today's Sales</p>
                    </div>
                    <div class="trend up">
                        <i class="fas fa-arrow-up"></i> 12%
                    </div>
                </div>

                <div class="widget summary-card inventory">
                    <div class="icon">
                        <i class="fas fa-box-open"></i>
                    </div>
                    <div class="info">
                        <h3>24</h3>
                        <p>Low Stock Items</p>
                    </div>
                    <div class="trend down">
                        <i class="fas fa-arrow-down"></i> 5%
                    </div>
                </div>

                <div class="widget summary-card employees">
                    <div class="icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="info">
                        <h3>8</h3>
                        <p>Active Staff</p>
                    </div>
                </div>

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
                            <span>Alerts</span>
                        </a>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="widget recent-activity">
                    <h3>Recent Activity</h3>
                    <ul>
                        <li>
                            <i class="fas fa-shopping-cart activity-icon sale"></i>
                            <div class="activity-details">
                                <p>New sale #10045 - $85.20</p>
                                <small>2 mins ago · Cashier: John</small>
                            </div>
                        </li>
                        <li>
                            <i class="fas fa-box activity-icon inventory"></i>
                            <div class="activity-details">
                                <p>Stock updated: Soda (Qty: 50)</p>
                                <small>15 mins ago · Admin</small>
                            </div>
                        </li>
                        <li>
                            <i class="fas fa-user activity-icon employee"></i>
                            <div class="activity-details">
                                <p>New employee added: Sarah</p>
                                <small>1 hour ago · Admin</small>
                            </div>
                        </li>
                    </ul>
                </div>

                <!-- Sales Chart -->
                <div class="widget sales-chart">
                    <h3>Sales Overview</h3>
                    <div class="chart-container">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="../../assets/js/admin.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</body>
</html>