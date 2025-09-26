<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports Dashboard | POS</title>
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
                <span>POS Admin</span>
            </div>
            <nav>
                <ul>
                    <li><a href="#"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="inventory/index.php"><i class="fas fa-boxes"></i> Inventory</a></li>
                    <li><a href="#"><i class="fas fa-users"></i> Employees</a></li>
                    <li class="active"><a href="report.php"><i class="fas fa-chart-line"></i> Reports</a></li>
                    <li><a href="inventory/categories.php"><i class="fas fa-tags"></i> Categories</a></li>
                    <li><a href="#"><i class="fas fa-shopping-cart"></i> Sales</a></li>
                    <li><a href="#"><i class="fas fa-cog"></i> Settings</a></li>
                    <li><a href="#"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Bar -->
            <header class="top-bar">
                <div class="page-title">
                    <h1>Reports</h1>
                    <p>Analyze your business performance with detailed reports</p>
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
            <div class="report-filters">
                <div class="filter-group">
                    <label for="report-type">Report Type</label>
                    <select id="report-type" class="filter-select">
                        <option value="sales">Sales Report</option>
                        <option value="inventory">Inventory Report</option>
                        <option value="employee">Employee Performance</option>
                        <option value="profit">Profit Analysis</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="time-period">Time Period</label>
                    <select id="time-period" class="filter-select">
                        <option value="today">Today</option>
                        <option value="week" selected>This Week</option>
                        <option value="month">This Month</option>
                        <option value="quarter">This Quarter</option>
                        <option value="year">This Year</option>
                        <option value="custom">Custom Range</option>
                    </select>
                </div>
                
                <div class="filter-group date-range" style="display: none;">
                    <label for="start-date">Start Date</label>
                    <input type="date" id="start-date" class="filter-select">
                </div>
                
                <div class="filter-group date-range" style="display: none;">
                    <label for="end-date">End Date</label>
                    <input type="date" id="end-date" class="filter-select">
                </div>
                
                <button class="generate-btn">
                    <i class="fas fa-sync-alt"></i> Generate Report
                </button>
                
                <button class="export-btn">
                    <i class="fas fa-file-export"></i> Export
                </button>
            </div>

            <!-- Report Summary Cards -->
            <div class="summary-grid">
                <div class="summary-card sales">
                    <div class="content">
                        <div class="info">
                            <h3>$12,845</h3>
                            <p>Total Sales</p>
                            <div class="trend up">
                                <i class="fas fa-arrow-up"></i> 12.5%
                            </div>
                        </div>
                        <div class="icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                    </div>
                </div>

                <div class="summary-card transactions">
                    <div class="content">
                        <div class="info">
                            <h3>328</h3>
                            <p>Transactions</p>
                            <div class="trend up">
                                <i class="fas fa-arrow-up"></i> 8.2%
                            </div>
                        </div>
                        <div class="icon">
                            <i class="fas fa-receipt"></i>
                        </div>
                    </div>
                </div>

                <div class="summary-card profit">
                    <div class="content">
                        <div class="info">
                            <h3>$4,253</h3>
                            <p>Total Profit</p>
                            <div class="trend down">
                                <i class="fas fa-arrow-down"></i> 3.1%
                            </div>
                        </div>
                        <div class="icon">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                    </div>
                </div>

                <div class="summary-card avg-order">
                    <div class="content">
                        <div class="info">
                            <h3>$39.16</h3>
                            <p>Avg. Order Value</p>
                            <div class="trend up">
                                <i class="fas fa-arrow-up"></i> 5.7%
                            </div>
                        </div>
                        <div class="icon">
                            <i class="fas fa-shopping-basket"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Report Widgets -->
            <div class="dashboard-widgets">
                <!-- Sales Trend Chart -->
                <div class="widget sales-trend">
                    <div class="widget-header">
                        <h3>Sales Trend</h3>
                        <div class="widget-actions">
                            <button class="action-btn"><i class="fas fa-expand"></i></button>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="salesTrendChart"></canvas>
                    </div>
                </div>

                <!-- Sales by Category -->
                <div class="widget sales-category">
                    <div class="widget-header">
                        <h3>Sales by Category</h3>
                        <div class="widget-actions">
                            <button class="action-btn"><i class="fas fa-expand"></i></button>
                        </div>
                    </div>
                    <div class="chart-container chart-small">
                        <canvas id="salesCategoryChart"></canvas>
                    </div>
                </div>

                <!-- Top Performing Products -->
                <div class="widget top-products">
                    <div class="widget-header">
                        <h3>Top Performing Products</h3>
                        <div class="widget-actions">
                            <button class="action-btn"><i class="fas fa-expand"></i></button>
                        </div>
                    </div>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Category</th>
                                    <th>Units Sold</th>
                                    <th>Revenue</th>
                                    <th>Profit</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Premium Coffee</td>
                                    <td>Beverages</td>
                                    <td>128</td>
                                    <td>$1,024.00</td>
                                    <td>$384.00</td>
                                </tr>
                                <tr>
                                    <td>Organic Bread</td>
                                    <td>Bakery</td>
                                    <td>96</td>
                                    <td>$288.00</td>
                                    <td>$115.20</td>
                                </tr>
                                <tr>
                                    <td>Handmade Soap</td>
                                    <td>Personal Care</td>
                                    <td>84</td>
                                    <td>$420.00</td>
                                    <td>$210.00</td>
                                </tr>
                                <tr>
                                    <td>Organic Milk</td>
                                    <td>Dairy</td>
                                    <td>72</td>
                                    <td>$216.00</td>
                                    <td>$64.80</td>
                                </tr>
                                <tr>
                                    <td>Mineral Water</td>
                                    <td>Beverages</td>
                                    <td>68</td>
                                    <td>$136.00</td>
                                    <td>$47.60</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Employee Performance -->
                <div class="widget employee-performance">
                    <div class="widget-header">
                        <h3>Employee Performance</h3>
                        <div class="widget-actions">
                            <button class="action-btn"><i class="fas fa-expand"></i></button>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="employeeChart"></canvas>
                    </div>
                </div>

                <!-- Detailed Transaction Report -->
                <div class="widget transaction-report">
                    <div class="widget-header">
                        <h3>Detailed Transaction Report</h3>
                        <div class="widget-actions">
                            <button class="action-btn"><i class="fas fa-expand"></i></button>
                            <button class="action-btn"><i class="fas fa-download"></i></button>
                        </div>
                    </div>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Transaction ID</th>
                                    <th>Date/Time</th>
                                    <th>Employee</th>
                                    <th>Items</th>
                                    <th>Total</th>
                                    <th>Payment</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>#10045</td>
                                    <td>Today, 14:32</td>
                                    <td>John Doe</td>
                                    <td>3</td>
                                    <td>$85.20</td>
                                    <td>Credit Card</td>
                                </tr>
                                <tr>
                                    <td>#10044</td>
                                    <td>Today, 12:15</td>
                                    <td>Sarah Smith</td>
                                    <td>5</td>
                                    <td>$125.50</td>
                                    <td>Cash</td>
                                </tr>
                                <tr>
                                    <td>#10043</td>
                                    <td>Today, 10:48</td>
                                    <td>Mike Johnson</td>
                                    <td>2</td>
                                    <td>$42.75</td>
                                    <td>Mobile Pay</td>
                                </tr>
                                <tr>
                                    <td>#10042</td>
                                    <td>Yesterday, 18:22</td>
                                    <td>John Doe</td>
                                    <td>7</td>
                                    <td>$210.30</td>
                                    <td>Credit Card</td>
                                </tr>
                                <tr>
                                    <td>#10041</td>
                                    <td>Yesterday, 16:10</td>
                                    <td>Sarah Smith</td>
                                    <td>4</td>
                                    <td>$98.60</td>
                                    <td>Cash</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="widget-footer">
                        <button class="view-all-btn">View All Transactions</button>
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

        // Time period filter change handler
        const timePeriodSelect = document.getElementById('time-period');
        const dateRangeGroups = document.querySelectorAll('.date-range');
        
        timePeriodSelect.addEventListener('change', function() {
            if (this.value === 'custom') {
                dateRangeGroups.forEach(group => group.style.display = 'flex');
            } else {
                dateRangeGroups.forEach(group => group.style.display = 'none');
            }
        });

        // Sales Trend Chart
        const salesTrendCtx = document.getElementById('salesTrendChart').getContext('2d');
        new Chart(salesTrendCtx, {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Sales ($)',
                    data: [1200, 1900, 3000, 5000, 2300, 3200, 4100],
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
                            callback: function(value) {
                                return '$' + value;
                            }
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

        // Sales by Category Chart
        const salesCategoryCtx = document.getElementById('salesCategoryChart').getContext('2d');
        new Chart(salesCategoryCtx, {
            type: 'doughnut',
            data: {
                labels: ['Beverages', 'Bakery', 'Dairy', 'Personal Care', 'Snacks', 'Others'],
                datasets: [{
                    data: [35, 25, 15, 12, 8, 5],
                    backgroundColor: [
                        '#3b82f6',
                        '#10b981',
                        '#f59e0b',
                        '#ef4444',
                        '#8b5cf6',
                        '#06b6d4'
                    ],
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            boxWidth: 12,
                            padding: 20,
                            usePointStyle: true,
                            pointStyle: 'circle',
                            color: '#6b7280'
                        }
                    }
                },
                cutout: '70%'
            }
        });

        // Employee Performance Chart
        const employeeCtx = document.getElementById('employeeChart').getContext('2d');
        new Chart(employeeCtx, {
            type: 'bar',
            data: {
                labels: ['John D.', 'Sarah S.', 'Mike J.', 'Emily W.', 'David B.'],
                datasets: [{
                    label: 'Sales ($)',
                    data: [4200, 3800, 3500, 2900, 2400],
                    backgroundColor: [
                        'rgba(99, 102, 241, 0.8)',
                        'rgba(99, 102, 241, 0.8)',
                        'rgba(99, 102, 241, 0.8)',
                        'rgba(99, 102, 241, 0.8)',
                        'rgba(99, 102, 241, 0.8)'
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
                            color: '#6b7280',
                            callback: function(value) {
                                return '$' + value;
                            }
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