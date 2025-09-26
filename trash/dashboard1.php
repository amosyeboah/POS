<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | POS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;
            --primary-light: #818cf8;
            --primary-dark: #4338ca;
            --secondary: #1e293b;
            --accent: #06b6d4;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --dark: #0f172a;
            --light: #f8fafc;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
            --radius: 16px;
            --radius-sm: 8px;
            --shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
            --blur: backdrop-filter: blur(10px);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg,rgb(210, 213, 227) 0%,rgb(221, 221, 221) 100%);
            min-height: 100vh;
            color: var(--gray-900);
            overflow-x: hidden;
        }

        .admin-container {
            display: flex;
            min-height: 100vh;
            position: relative;
        }

        /* Sidebar */
        .sidebar {
            width: 280px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-right: 1px solid rgba(255, 255, 255, 0.2);
            position: fixed;
            height: 100vh;
            transform: translateX(-100%);
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1000;
            box-shadow: var(--shadow-xl);
        }

                .sidebar.active {
                    transform: translateX(0);
                }

                @media (min-width: 1025px) {
            .sidebar {
                transform: translateX(0);
                position: relative;
            }
            
            .main-content {
                margin-left: 280px;
            }
        }

        .logo {
            display: flex;
            align-items: center;
            padding: 2rem 1.5rem;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            font-size: 1.25rem;
            font-weight: 700;
            margin: 1rem;
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
        }

        .logo i {
            font-size: 1.75rem;
            margin-right: 0.75rem;
        }

        nav {
            padding: 1rem;
        }

        nav ul {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        nav li a {
            display: flex;
            align-items: center;
            padding: 1rem 1.25rem;
            color: var(--gray-600);
            text-decoration: none;
            border-radius: var(--radius-sm);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-weight: 500;
            position: relative;
            overflow: hidden;
        }

        nav li a::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 0;
            height: 100%;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: -1;
        }

        nav li a:hover::before,
        nav li.active a::before {
            width: 100%;
        }

        nav li a:hover,
        nav li.active a {
            color: white;
            transform: translateX(8px);
            box-shadow: var(--shadow-lg);
        }

        nav li a i {
            margin-right: 1rem;
            width: 20px;
            text-align: center;
            font-size: 1.1rem;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 0;
            padding: 1rem;
            transition: margin-left 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .main-content.sidebar-open {
            margin-left: 280px;
        }

        /* Top Bar */
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            padding: 1rem 1.5rem;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .menu-toggle {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            border: none;
            width: 48px;
            height: 48px;
            border-radius: var(--radius-sm);
            color: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: var(--shadow);
        }

        .menu-toggle:hover {
            transform: scale(1.05);
            box-shadow: var(--shadow-lg);
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 1rem;
            background: linear-gradient(135deg, var(--gray-50), var(--gray-100));
            padding: 0.75rem 1rem;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            border: 1px solid var(--gray-200);
        }

        .user-profile span {
            font-weight: 600;
            color: var(--gray-700);
        }

        .user-profile .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            box-shadow: var(--shadow);
        }

        /* Dashboard Widgets */
        .dashboard-widgets {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 1.5rem;
        }

        .widget {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: var(--radius);
            padding: 2rem;
            box-shadow: var(--shadow-lg);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .widget::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 2px;
        }

        .widget:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-xl);
        }

        .widget h3 {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--gray-800);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Summary Cards */
        .summary-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .summary-card::before {
            background: linear-gradient(90deg, var(--success), var(--accent));
        }

        .summary-card .left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .summary-card .icon {
            width: 70px;
            height: 70px;
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            color: white;
            box-shadow: var(--shadow-lg);
        }

        .summary-card.sales .icon {
            background: linear-gradient(135deg, var(--success), #34d399);
        }

        .summary-card.inventory .icon {
            background: linear-gradient(135deg, var(--warning), #fbbf24);
        }

        .summary-card.employees .icon {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
        }

        .summary-card .info h3 {
            font-size: 2rem;
            margin-bottom: 0.25rem;
            background: linear-gradient(135deg, var(--gray-800), var(--gray-600));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .summary-card .info p {
            color: var(--gray-500);
            font-weight: 500;
        }

        .trend {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            font-size: 0.875rem;
            font-weight: 600;
            padding: 0.5rem 0.75rem;
            border-radius: var(--radius-sm);
        }

        .trend.up {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .trend.down {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning);
        }

        /* Quick Actions */
        .quick-actions::before {
            background: linear-gradient(90deg, var(--primary), var(--primary-light));
        }

        .action-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .action-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1.5rem 1rem;
            background: linear-gradient(135deg, var(--gray-50), var(--gray-100));
            border-radius: var(--radius-sm);
            text-decoration: none;
            color: var(--gray-700);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid var(--gray-200);
            position: relative;
            overflow: hidden;
        }

        .action-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 0;
            height: 100%;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: -1;
        }

        .action-btn:hover::before {
            width: 100%;
        }

        .action-btn:hover {
            color: white;
            transform: translateY(-2px) scale(1.02);
            box-shadow: var(--shadow-lg);
        }

        .action-btn i {
            font-size: 1.5rem;
            margin-bottom: 0.75rem;
        }

        .action-btn span {
            font-weight: 500;
            font-size: 0.875rem;
        }

        /* Recent Activity */
        .recent-activity::before {
            background: linear-gradient(90deg, var(--accent), var(--primary-light));
        }

        .recent-activity ul {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .recent-activity li {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: linear-gradient(135deg, var(--gray-50), rgba(255, 255, 255, 0.8));
            border-radius: var(--radius-sm);
            border: 1px solid var(--gray-200);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .recent-activity li:hover {
            transform: translateX(4px);
            box-shadow: var(--shadow);
        }

        .activity-icon {
            width: 48px;
            height: 48px;
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.1rem;
            box-shadow: var(--shadow);
        }

        .activity-icon.sale {
            background: linear-gradient(135deg, var(--success), #34d399);
        }

        .activity-icon.inventory {
            background: linear-gradient(135deg, var(--warning), #fbbf24);
        }

        .activity-icon.employee {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
        }

        .activity-details p {
            font-weight: 600;
            color: var(--gray-800);
            margin-bottom: 0.25rem;
        }

        .activity-details small {
            color: var(--gray-500);
            font-size: 0.8rem;
        }

        /* Sales Chart */
        .sales-chart::before {
            background: linear-gradient(90deg, var(--danger), var(--warning));
        }

        .chart-container {
            position: relative;
            height: 300px;
            background: linear-gradient(135deg, var(--gray-50), rgba(255, 255, 255, 0.8));
            border-radius: var(--radius-sm);
            padding: 1rem;
            border: 1px solid var(--gray-200);
        }

        /* Overlay for mobile menu */
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(4px);
            z-index: 999;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .overlay.active {
            opacity: 1;
            visibility: visible;
        }

        /* Floating Action Button (Mobile) */
        .fab {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.25rem;
            box-shadow: var(--shadow-xl);
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1001;
            display: none;
        }

        .fab:hover {
            transform: scale(1.1);
            box-shadow: 0 25px 50px -12px rgb(0 0 0 / 0.25);
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
    .sidebar {
        position: fixed;
    }
    
    .main-content {
        margin-left: 0;
    }
    
    .main-content.sidebar-open {
        margin-left: 0;
    }
}

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                max-width: 320px;
            }

            .dashboard-widgets {
                grid-template-columns: 1fr;
            }

            .fab {
                display: flex;
            }

            .action-grid {
                grid-template-columns: 1fr;
            }

            .summary-card {
                flex-direction: column;
                text-align: center;
                gap: 1rem;
            }

            .summary-card .left {
                flex-direction: column;
            }

            .top-bar {
                padding: 1rem;
            }

            .user-profile span {
                display: none;
            }
        }

        @media (max-width: 480px) {
            .main-content {
                padding: 0.75rem;
            }

            .widget {
                padding: 1.5rem;
            }

            .dashboard-widgets {
                gap: 1rem;
            }
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .widget {
            animation: fadeInUp 0.6s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }

        .widget:nth-child(1) { animation-delay: 0.1s; }
        .widget:nth-child(2) { animation-delay: 0.2s; }
        .widget:nth-child(3) { animation-delay: 0.3s; }
        .widget:nth-child(4) { animation-delay: 0.4s; }
        .widget:nth-child(5) { animation-delay: 0.5s; }
        .widget:nth-child(6) { animation-delay: 0.6s; }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Overlay -->
        <div class="overlay" id="overlay"></div>

        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="logo">
                <i class="fas fa-cash-register"></i>
                <span>POS Admin</span>
            </div>
            <nav>
                <ul>
                    <li class="active"><a href="#"><i class="fas fa-chart-pie"></i> Dashboard</a></li>
                    <li><a href="#"><i class="fas fa-boxes"></i> Inventory</a></li>
                    <li><a href="#"><i class="fas fa-users"></i> Employees</a></li>
                    <li><a href="#"><i class="fas fa-chart-line"></i> Reports</a></li>
                    <li><a href="#"><i class="fas fa-bell"></i> Notifications</a></li>
                    <li><a href="#"><i class="fas fa-cog"></i> Settings</a></li>
                    <li><a href="#"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content" id="main-content">
            <!-- Top Bar -->
            <header class="top-bar">
                <button class="menu-toggle" id="menu-toggle">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="user-profile">
                    <span>Admin User</span>
                    <div class="avatar">AU</div>
                </div>
            </header>

            <!-- Dashboard Widgets -->
            <div class="dashboard-widgets">
                <!-- Summary Cards -->
                <div class="widget summary-card sales">
                    <div class="left">
                        <div class="icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="info">
                            <h3>$12,845</h3>
                            <p>Today's Sales</p>
                        </div>
                    </div>
                    <div class="trend up">
                        <i class="fas fa-arrow-up"></i> 12%
                    </div>
                </div>

                <div class="widget summary-card inventory">
                    <div class="left">
                        <div class="icon">
                            <i class="fas fa-box-open"></i>
                        </div>
                        <div class="info">
                            <h3>24</h3>
                            <p>Low Stock Alert</p>
                        </div>
                    </div>
                    <div class="trend down">
                        <i class="fas fa-exclamation-triangle"></i> Alert
                    </div>
                </div>

                <div class="widget summary-card inventory">
                    <div class="left">
                        <div class="icon">
                            <i class="fas fa-box-open"></i>
                        </div>
                        <div class="info">
                            <h3>24</h3>
                            <p>Low Stock Alert</p>
                        </div>
                    </div>
                    <div class="trend down">
                        <i class="fas fa-exclamation-triangle"></i> Alert
                    </div>
                </div>

                <div class="widget summary-card employees">
                    <div class="left">
                        <div class="icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="info">
                            <h3>8</h3>
                            <p>Active Staff</p>
                        </div>
                    </div>
                    <div class="trend up">
                        <i class="fas fa-check-circle"></i> Online
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="widget quick-actions">
                    <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
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
                    <h3><i class="fas fa-clock"></i> Recent Activity</h3>
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
                    <h3><i class="fas fa-chart-area"></i> Sales Overview</h3>
                    <div class="chart-container">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
            </div>
        </main>

        <!-- Floating Action Button -->
        <div class="fab" id="fab">
            <i class="fas fa-plus"></i>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Mobile menu functionality
        const menuToggle = document.getElementById('menu-toggle');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        const mainContent = document.getElementById('main-content');
        const fab = document.getElementById('fab');

        function toggleSidebar() {
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
            
            if (window.innerWidth > 1024) {
                mainContent.classList.toggle('sidebar-open');
            }
        }

        function closeSidebar() {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
            mainContent.classList.remove('sidebar-open');
        }

        menuToggle.addEventListener('click', toggleSidebar);
        overlay.addEventListener('click', closeSidebar);
        fab.addEventListener('click', toggleSidebar);

        // Close sidebar when clicking on nav links on mobile
        document.querySelectorAll('nav a').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 1024) {
                    closeSidebar();
                }
            });
        });

        // Handle window resize
        window.addEventListener('resize', () => {
            if (window.innerWidth > 1024) {
                overlay.classList.remove('active');
            } else {
                mainContent.classList.remove('sidebar-open');
            }
        });

        // Chart.js implementation
        const ctx = document.getElementById('salesChart').getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, 'rgba(99, 102, 241, 0.3)');
        gradient.addColorStop(1, 'rgba(99, 102, 241, 0.05)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Sales',
                    data: [1200, 1900, 3000, 5000, 2000, 3000, 4500],
                    borderColor: '#6366f1',
                    backgroundColor: gradient,
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
                    x: {
                        grid: {
                            display: false
                        },
                        border: {
                            display: false
                        }
                    },
                    y: {
                        grid: {
                            color: 'rgba(0,0,0,0.05)'
                        },
                        border: {
                            display: false
                        },
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    }
                },
                elements: {
                    point: {
                        hoverBackgroundColor: '#6366f1'
                    }
                }
            }
        });

        // Add smooth scroll behavior
        document.documentElement.style.scrollBehavior = 'smooth';
    </script>
</body>
</html>