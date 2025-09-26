<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Admin - Inventory</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/inventory1.css">

    <style>
                /* CSS/Inventory.css */
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f8fafc;
            color: #334155;
            line-height: 1.6;
        }

        /* Container */
        .admin-container {
            position: relative;
            min-height: 100vh;
        }

        /* Overlay for mobile sidebar */
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 998;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        /* Override for blur overlay */
        .overlay {
            background: rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(5px);
        }

        .overlay.active {
            opacity: 1;
            visibility: visible;
        }

        /* Sidebar - Mobile First */
        .sidebar {
            position: fixed;
            top: 0;
            left: -280px;
            width: 280px;
            height: 100vh;
            background-color: white;
            color: black;
            z-index: 999;
            transition: left 0.3s ease;
            overflow-y: auto;
        }

        .sidebar.active {
            left: 0;
        }

        .logo {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 600;
            font-size: 1.25rem;
        }

        .sidebar nav ul {
            list-style: none;
            padding: 1rem 0;
        }

        .sidebar nav li {
            margin: 0.25rem 0;
        }

        .sidebar nav a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.875rem 1.5rem;
            color: rgba(0, 0, 0, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }

        .sidebar nav a:hover,
        .sidebar nav li.active a {
            background-color: rgba(255, 255, 255, 0.1);
            color: #3b82f6;
        }

        /* Main Content */
        .main-content {
            padding: 0;
            transition: margin-left 0.3s ease;
        }

        /* Top Bar */
        .top-bar {
            background: white;
            padding: 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .menu-toggle {
            background: none;
            border: none;
            font-size: 1.25rem;
            color: #64748b;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 0.375rem;
            transition: background-color 0.3s ease;
        }

        .menu-toggle:hover {
            background-color: #f1f5f9;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .user-profile img {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-color: #e2e8f0;
        }

        /* Page Header */
        .page-header {
            padding: 1.5rem 1rem;
            background: white;
            border-bottom: 1px solid #e2e8f0;
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .page-subtitle {
            color: #64748b;
            font-size: 0.875rem;
        }

        /* Stats Cards - Mobile First (2 columns) */
        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            padding: 1rem;
        }

        .stat-card {
            background: white;
            padding: 1.25rem;
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border-left: 4px solid;
        }

        .stat-card.total { border-left-color: #3b82f6; }
        .stat-card.low-stock { border-left-color: #ef4444; }
        .stat-card.categories { border-left-color: #10b981; }
        .stat-card.value { border-left-color: #f59e0b; }

        .stat-card .stat-icon {
            font-size: 1.5rem;
            margin-bottom: 0.75rem;
        }

        .stat-card.total .stat-icon { color: #3b82f6; }
        .stat-card.low-stock .stat-icon { color: #ef4444; }
        .stat-card.categories .stat-icon { color: #10b981; }
        .stat-card.value .stat-icon { color: #f59e0b; }

        .stat-number {
            font-size: 1.75rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.25rem;
        }

        .stat-label {
            font-size: 0.75rem;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* Controls Section */
        .controls-section {
            padding: 1rem;
            background: white;
            border-bottom: 1px solid #e2e8f0;
        }

        /* Search and Filters - Mobile First (stacked) */
        .search-filters {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .search-input {
            width: 100%;
            padding: 0.875rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            font-size: 1rem;
            background-color: #f9fafb;
        }

        .search-input:focus {
            outline: none;
            border-color: #3b82f6;
            background-color: white;
        }

        .filter-select {
            width: 100%;
            padding: 0.875rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            font-size: 1rem;
            background-color: white;
        }

        /* Action Buttons - Mobile First (icons only) */
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .action-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.875rem;
            background: #3b82f6;
            color: white;
            text-decoration: none;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.3s ease;
            min-height: 48px;
            min-width: 48px;
        }

        .action-btn:hover {
            background: #2563eb;
            transform: translateY(-1px);
        }

        .action-btn.secondary {
            background: #6b7280;
        }

        .action-btn.secondary:hover {
            background: #4b5563;
        }

        /* Inventory List */
        .inventory-section {
            padding: 1rem;
        }

        .inventory-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .section-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: #1e293b;
        }

        .view-toggle {
            display: flex;
            background: #f1f5f9;
            border-radius: 0.5rem;
            padding: 0.25rem;
        }

        .view-btn {
            padding: 0.5rem 0.75rem;
            background: none;
            border: none;
            border-radius: 0.375rem;
            color: #64748b;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .view-btn.active {
            background: white;
            color: #3b82f6;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        /* Inventory Items */
        .inventory-grid {
            display: grid;
            gap: 1rem;
        }

        .inventory-item {
            background: white;
            border-radius: 0.75rem;
            padding: 1.25rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
        }

        .item-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 0.75rem;
        }

        .item-name {
            font-weight: 600;
            color: #1e293b;
            font-size: 1rem;
        }

        .item-status {
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .status-in-stock {
            background: #dcfce7;
            color: #166534;
        }

        .status-low-stock {
            background: #fef3c7;
            color: #92400e;
        }

        .status-out-stock {
            background: #fee2e2;
            color: #991b1b;
        }

        .item-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .item-detail {
            display: flex;
            flex-direction: column;
        }

        .detail-label {
            font-size: 0.75rem;
            color: #64748b;
            margin-bottom: 0.25rem;
        }

        .detail-value {
            font-weight: 600;
            color: #1e293b;
        }

        .item-actions {
            display: flex;
            gap: 0.5rem;
        }

        .item-action-btn {
            flex: 1;
            padding: 0.5rem;
            border: 1px solid #d1d5db;
            background: white;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .item-action-btn:hover {
            background: #f9fafb;
        }

        .item-action-btn.primary {
            background: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }

        .item-action-btn.primary:hover {
            background: #2563eb;
        }

        /* Desktop Styles */
        @media (min-width: 768px) {
            /* Sidebar visible by default */
            .sidebar {
                left: 0;
                position: fixed;
            }

            .main-content {
                margin-left: 280px;
            }

            .menu-toggle {
                display: none;
            }

            /* Stats grid - 4 columns on desktop */
            .stats-grid {
                grid-template-columns: repeat(4, 1fr);
                padding: 2rem;
            }

            /* Search filters horizontal */
            .search-filters {
                flex-direction: row;
                align-items: center;
            }

            .search-input {
                flex: 1;
            }

            .filter-select {
                width: auto;
                min-width: 200px;
            }

            /* Action buttons show text */
            .action-btn .btn-text {
                display: inline;
            }

            .action-btn {
                width: auto;
            }

            /* Page layout adjustments */
            .page-header,
            .controls-section,
            .inventory-section {
                padding-left: 2rem;
                padding-right: 2rem;
            }

            /* Inventory grid - 2 columns on tablet, 3 on desktop */
            .inventory-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (min-width: 1024px) {
            .inventory-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (min-width: 1280px) {
            .inventory-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }
    
    </style>

</head>
<body>
    <div class="admin-container">
        <!-- Overlay for mobile -->
        <div class="overlay" id="overlay"></div>

        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="logo">
                <i class="fas fa-cash-register"></i>
                <span>POS Admin</span>
            </div>
            <nav>
                <ul>
                     <li><a href="../index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li class="active"><a href="#"><i class="fas fa-boxes"></i> Inventory</a></li>
                    <li><a href="#"><i class="fas fa-users"></i> Employees</a></li>
                    <li><a href="#"><i class="fas fa-chart-line"></i> Reports</a></li>
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
                <button class="menu-toggle" id="menu-toggle">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="user-profile">
                    <span>Admin User</span>
                    <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzIiIGhlaWdodD0iMzIiIHZpZXdCb3g9IjAgMCAzMiAzMiIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iMTYiIGN5PSIxNiIgcj0iMTYiIGZpbGw9IiNlMmU4ZjAiLz4KPC9zdmc+" alt="Admin">
                </div>
            </header>

            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">Inventory Management</h1>
                <p class="page-subtitle">Manage your products and stock levels</p>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card total">
                    <div class="stat-icon">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <div class="stat-number">1,247</div>
                    <div class="stat-label">Total Items</div>
                </div>
                <div class="stat-card low-stock">
                    <div class="stat-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-number">24</div>
                    <div class="stat-label">Low Stock</div>
                </div>
                <div class="stat-card categories">
                    <div class="stat-icon">
                        <i class="fas fa-tags"></i>
                    </div>
                    <div class="stat-number">18</div>
                    <div class="stat-label">Categories</div>
                </div>
                <div class="stat-card value">
                    <div class="stat-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-number">$89.2K</div>
                    <div class="stat-label">Total Value</div>
                </div>
            </div>

            <!-- Controls Section -->
            <div class="controls-section">
                <div class="search-filters">
                    <input type="text" class="search-input" placeholder="Search products...">
                    <select class="filter-select">
                        <option>All Categories</option>
                        <option>Beverages</option>
                        <option>Snacks</option>
                        <option>Electronics</option>
                    </select>
                    <select class="filter-select">
                        <option>All Status</option>
                        <option>In Stock</option>
                        <option>Low Stock</option>
                        <option>Out of Stock</option>
                    </select>
                </div>
                <div class="action-buttons">
                    <a href="#" class="action-btn">
                        <i class="fas fa-plus"></i>
                        <span class="btn-text">Add Product</span>
                    </a>
                    <a href="#" class="action-btn secondary">
                        <i class="fas fa-file-import"></i>
                        <span class="btn-text">Import</span>
                    </a>
                    <a href="#" class="action-btn secondary">
                        <i class="fas fa-file-export"></i>
                        <span class="btn-text">Export</span>
                    </a>
                </div>
            </div>

            <!-- Inventory Section -->
            <div class="inventory-section">
                <div class="inventory-header">
                    <h2 class="section-title">Products</h2>
                    <div class="view-toggle">
                        <button class="view-btn active">
                            <i class="fas fa-th"></i>
                        </button>
                        <button class="view-btn">
                            <i class="fas fa-list"></i>
                        </button>
                    </div>
                </div>

                <div class="inventory-grid">
                    <div class="inventory-item">
                        <div class="item-header">
                            <h3 class="item-name">Coca Cola 500ml</h3>
                            <span class="item-status status-in-stock">In Stock</span>
                        </div>
                        <div class="item-details">
                            <div class="item-detail">
                                <span class="detail-label">Stock</span>
                                <span class="detail-value">150 units</span>
                            </div>
                            <div class="item-detail">
                                <span class="detail-label">Price</span>
                                <span class="detail-value">$2.50</span>
                            </div>
                            <div class="item-detail">
                                <span class="detail-label">Category</span>
                                <span class="detail-value">Beverages</span>
                            </div>
                            <div class="item-detail">
                                <span class="detail-label">SKU</span>
                                <span class="detail-value">BEV001</span>
                            </div>
                        </div>
                        <div class="item-actions">
                            <button class="item-action-btn">Edit</button>
                            <button class="item-action-btn primary">Restock</button>
                        </div>
                    </div>

                    <div class="inventory-item">
                        <div class="item-header">
                            <h3 class="item-name">Lay's Chips Original</h3>
                            <span class="item-status status-low-stock">Low Stock</span>
                        </div>
                        <div class="item-details">
                            <div class="item-detail">
                                <span class="detail-label">Stock</span>
                                <span class="detail-value">8 units</span>
                            </div>
                            <div class="item-detail">
                                <span class="detail-label">Price</span>
                                <span class="detail-value">$3.99</span>
                            </div>
                            <div class="item-detail">
                                <span class="detail-label">Category</span>
                                <span class="detail-value">Snacks</span>
                            </div>
                            <div class="item-detail">
                                <span class="detail-label">SKU</span>
                                <span class="detail-value">SNK002</span>
                            </div>
                        </div>
                        <div class="item-actions">
                            <button class="item-action-btn">Edit</button>
                            <button class="item-action-btn primary">Restock</button>
                        </div>
                    </div>

                    <div class="inventory-item">
                        <div class="item-header">
                            <h3 class="item-name">Samsung Earbuds</h3>
                            <span class="item-status status-out-stock">Out of Stock</span>
                        </div>
                        <div class="item-details">
                            <div class="item-detail">
                                <span class="detail-label">Stock</span>
                                <span class="detail-value">0 units</span>
                            </div>
                            <div class="item-detail">
                                <span class="detail-label">Price</span>
                                <span class="detail-value">$89.99</span>
                            </div>
                            <div class="item-detail">
                                <span class="detail-label">Category</span>
                                <span class="detail-value">Electronics</span>
                            </div>
                            <div class="item-detail">
                                <span class="detail-label">SKU</span>
                                <span class="detail-value">ELE003</span>
                            </div>
                        </div>
                        <div class="item-actions">
                            <button class="item-action-btn">Edit</button>
                            <button class="item-action-btn primary">Restock</button>
                        </div>
                    </div>

                    <div class="inventory-item">
                        <div class="item-header">
                            <h3 class="item-name">Energy Drink Monster</h3>
                            <span class="item-status status-in-stock">In Stock</span>
                        </div>
                        <div class="item-details">
                            <div class="item-detail">
                                <span class="detail-label">Stock</span>
                                <span class="detail-value">75 units</span>
                            </div>
                            <div class="item-detail">
                                <span class="detail-label">Price</span>
                                <span class="detail-value">$4.25</span>
                            </div>
                            <div class="item-detail">
                                <span class="detail-label">Category</span>
                                <span class="detail-value">Beverages</span>
                            </div>
                            <div class="item-detail">
                                <span class="detail-label">SKU</span>
                                <span class="detail-value">BEV004</span>
                            </div>
                        </div>
                        <div class="item-actions">
                            <button class="item-action-btn">Edit</button>
                            <button class="item-action-btn primary">Restock</button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Mobile-first sidebar toggle functionality
        const menuToggle = document.getElementById('menu-toggle');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');

        function toggleSidebar() {
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
            document.body.style.overflow = sidebar.classList.contains('active') ? 'hidden' : '';
        }

        function closeSidebar() {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        }

        // Event listeners
        menuToggle.addEventListener('click', toggleSidebar);
        overlay.addEventListener('click', closeSidebar);

        // Close sidebar on window resize to desktop size
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 768) {
                closeSidebar();
            }
        });

        // View toggle functionality
        const viewBtns = document.querySelectorAll('.view-btn');
        viewBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                viewBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // Search functionality (placeholder)
        const searchInput = document.querySelector('.search-input');
        searchInput.addEventListener('input', function() {
            // Add search logic here
            console.log('Searching for:', this.value);
        });

        // Filter functionality (placeholder)
        const filterSelects = document.querySelectorAll('.filter-select');
        filterSelects.forEach(select => {
            select.addEventListener('change', function() {
                // Add filter logic here
                console.log('Filter changed:', this.value);
            });
        });
    </script>
</body>
</html>