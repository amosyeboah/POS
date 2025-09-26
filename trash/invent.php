<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management | POS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Desktop CS -->
    <link rel="stylesheet" href="desktop1.css" media="screen and (min-width: 769px)"> 
    <!--Mobile CSS -->
    <link rel="stylesheet" href="mobile.css" media="screen and (max-width: 768px)">

    <style>
        /* Modern Inventory Management System CSS */

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

:root {
    /* Primary Colors */
    --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    --warning-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    --danger-gradient: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%);
    
    /* Light Theme Colors */
    --bg-primary: #f8fafc;
    --bg-secondary: #ffffff;
    --bg-card: rgba(255, 255, 255, 0.8);
    --bg-glass: rgba(255, 255, 255, 0.7);
    --bg-modal: rgba(0, 0, 0, 0.5);
    
    /* Text Colors */
    --text-primary: #1a202c;
    --text-secondary: #4a5568;
    --text-muted: #718096;
    
    /* Accent Colors */
    --accent-primary: #667eea;
    --accent-secondary: #764ba2;
    --accent-success: #00f2fe;
    --accent-warning: #fee140;
    --accent-danger: #ff6b6b;
    
    /* Spacing */
    --spacing-xs: 0.5rem;
    --spacing-sm: 1rem;
    --spacing-md: 1.5rem;
    --spacing-lg: 2rem;
    --spacing-xl: 3rem;
    
    /* Border Radius */
    --radius-sm: 8px;
    --radius-md: 12px;
    --radius-lg: 16px;
    --radius-xl: 20px;
    
    /* Shadows */
    --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.08);
    --shadow-md: 0 4px 20px rgba(0, 0, 0, 0.12);
    --shadow-lg: 0 8px 32px rgba(0, 0, 0, 0.16);
    --shadow-glow: 0 0 20px rgba(102, 126, 234, 0.2);
    
    /* Transitions */
    --transition-fast: 0.2s ease;
    --transition-smooth: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    --transition-bounce: 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    background: var(--bg-primary);
    color: var(--text-primary);
    line-height: 1.6;
    overflow-x: hidden;
}

/* Background Pattern */
body::before {
    content: '';
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: 
        radial-gradient(circle at 20% 20%, rgba(102, 126, 234, 0.03) 0%, transparent 50%),
        radial-gradient(circle at 80% 80%, rgba(118, 75, 162, 0.03) 0%, transparent 50%),
        radial-gradient(circle at 60% 30%, rgba(0, 242, 254, 0.02) 0%, transparent 50%);
    pointer-events: none;
    z-index: -1;
}

.app-container {
    min-height: 100vh;
    position: relative;
}
.sidebar {
            width: 280px;
            background: var(--white);
            box-shadow: var(--shadow-lg);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 100;
            border-right: 1px solid var(--gray-200);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: var(--gray-100);
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: var(--gray-300);
            border-radius: 2px;
        }

        .logo {
            display: flex;
            align-items: center;
            padding: 2rem 1.5rem;
            border-bottom: 1px solid var(--gray-200);
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: var(--white);
            font-size: 1.25rem;
            font-weight: 700;
        }

        .logo i {
            font-size: 1.75rem;
            margin-right: 0.75rem;
        }

        nav {
            padding: 1rem 0;
        }

        nav ul {
            list-style: none;
        }

        nav li {
            margin: 0.25rem 0;
        }

        nav li a {
            display: flex;
            align-items: center;
            padding: 1rem 1.5rem;
            color: var(--gray-600);
            text-decoration: none;
            transition: all 0.3s;
            border-radius: 0 25px 25px 0;
            margin-right: 1rem;
            font-weight: 500;
        }

        nav li a:hover {
            color: var(--primary);
            background: linear-gradient(90deg, rgba(99, 102, 241, 0.1) 0%, rgba(99, 102, 241, 0.05) 100%);
            transform: translateX(5px);
        }

        nav li.active a {
            color: var(--primary);
            background: linear-gradient(90deg, rgba(99, 102, 241, 0.15) 0%, rgba(99, 102, 241, 0.08) 100%);
            border-right: 3px solid var(--primary);
        }

        nav li a i {
            margin-right: 0.75rem;
            width: 20px;
            text-align: center;
            font-size: 1.1rem;
        }

/* Mobile Header */
.mobile-header {
    background: var(--bg-glass);
    backdrop-filter: blur(20px);
    border-bottom: 1px solid rgba(0, 0, 0, 0.08);
    padding: var(--spacing-sm) var(--spacing-lg);
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: sticky;
    top: 0;
    z-index: 100;
    box-shadow: var(--shadow-md);
}

.header-left {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
}

.menu-btn {
    background: var(--bg-glass);
    border: 1px solid rgba(0, 0, 0, 0.08);
    border-radius: var(--radius-sm);
    padding: var(--spacing-xs);
    color: var(--text-primary);
    cursor: pointer;
    transition: var(--transition-smooth);
    display: none; /* Hide on desktop */
}

.menu-btn:hover {
    background: var(--bg-card);
    transform: translateY(-2px);
    box-shadow: var(--shadow-sm);
}

.page-title h1 {
    font-size: 2rem;
    font-weight: 700;
    background: var(--primary-gradient);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.header-actions {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
}

.search-btn, .filter-btn {
    background: var(--bg-glass);
    border: 1px solid rgba(0, 0, 0, 0.08);
    border-radius: var(--radius-sm);
    padding: var(--spacing-xs);
    color: var(--text-secondary);
    cursor: pointer;
    transition: var(--transition-smooth);
}

.search-btn:hover, .filter-btn:hover {
    background: var(--primary-gradient);
    color: white;
    transform: translateY(-2px);
    box-shadow: var(--shadow-glow);
}

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: 2px solid var(--accent-primary);
    box-shadow: var(--shadow-sm);
    transition: var(--transition-smooth);
}

.user-avatar:hover {
    transform: scale(1.1);
    box-shadow: var(--shadow-glow);
}

/* Main Content */
.main-content {
    padding: var(--spacing-xl);
    max-width: 1400px;
    margin: 0 auto;
}

/* Stats Section */
.stats-section {
    margin-bottom: var(--spacing-xl);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: var(--spacing-lg);
    margin-bottom: var(--spacing-xl);
}

.stat-card {
    background: var(--bg-glass);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(0, 0, 0, 0.06);
    border-radius: var(--radius-lg);
    padding: var(--spacing-lg);
    position: relative;
    overflow: hidden;
    transition: var(--transition-smooth);
    cursor: pointer;
    box-shadow: var(--shadow-sm);
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: var(--primary-gradient);
    transition: var(--transition-smooth);
}

.stat-card:hover {
    transform: translateY(-8px);
    box-shadow: var(--shadow-lg);
    border-color: rgba(0, 0, 0, 0.12);
    background: var(--bg-secondary);
}

.stat-card:hover::before {
    height: 6px;
    box-shadow: 0 0 20px rgba(102, 126, 234, 0.5);
}

.stat-card.warning::before {
    background: var(--warning-gradient);
}

.stat-card.danger::before {
    background: var(--danger-gradient);
}

.stat-card.success::before {
    background: var(--success-gradient);
}

.stat-content {
    display: flex;
    align-items: center;
    gap: var(--spacing-md);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    background: var(--primary-gradient);
    color: white;
    box-shadow: var(--shadow-sm);
}

.stat-card.warning .stat-icon {
    background: var(--warning-gradient);
}

.stat-card.danger .stat-icon {
    background: var(--danger-gradient);
}

.stat-card.success .stat-icon {
    background: var(--success-gradient);
}

.stat-number {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--text-primary);
    line-height: 1;
}

.stat-label {
    font-size: 0.9rem;
    color: var(--text-secondary);
    font-weight: 500;
    margin-top: var(--spacing-xs);
}

/* Quick Actions */
.quick-actions {
    display: flex;
    gap: var(--spacing-md);
    flex-wrap: wrap;
}

.action-btn {
    background: var(--bg-glass);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(0, 0, 0, 0.06);
    border-radius: var(--radius-md);
    padding: var(--spacing-md) var(--spacing-lg);
    color: var(--text-primary);
    cursor: pointer;
    transition: var(--transition-smooth);
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    font-weight: 500;
    position: relative;
    overflow: hidden;
    box-shadow: var(--shadow-sm);
}

.action-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: var(--primary-gradient);
    transition: var(--transition-smooth);
    z-index: -1;
}

.action-btn:hover::before {
    left: 0;
}

.action-btn:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-lg);
    color: white;
}

.action-btn.primary {
    background: var(--primary-gradient);
    color: white;
    box-shadow: var(--shadow-glow);
}

.action-btn.primary:hover {
    transform: translateY(-3px) scale(1.05);
}

.action-icon {
    width: 40px;
    height: 40px;
    border-radius: var(--radius-sm);
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(0, 0, 0, 0.05);
    font-size: 1.1rem;
}

/* Products Section */
.products-section {
    margin-top: var(--spacing-xl);
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--spacing-lg);
}

.section-title {
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--text-primary);
}

.view-toggle {
    display: flex;
    background: var(--bg-glass);
    border-radius: var(--radius-sm);
    padding: 4px;
    border: 1px solid rgba(0, 0, 0, 0.08);
    box-shadow: var(--shadow-sm);
}

.view-btn {
    background: transparent;
    border: none;
    padding: var(--spacing-xs) var(--spacing-sm);
    border-radius: var(--radius-sm);
    color: var(--text-secondary);
    cursor: pointer;
    transition: var(--transition-smooth);
}

.view-btn.active {
    background: var(--primary-gradient);
    color: white;
    box-shadow: var(--shadow-sm);
}

.view-btn:hover:not(.active) {
    background: var(--bg-card);
    color: var(--text-primary);
}

/* Products Grid */
.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: var(--spacing-lg);
}

.product-card {
    background: var(--bg-glass);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(0, 0, 0, 0.06);
    border-radius: var(--radius-lg);
    padding: var(--spacing-lg);
    position: relative;
    overflow: hidden;
    transition: var(--transition-smooth);
    cursor: pointer;
    box-shadow: var(--shadow-sm);
}

.product-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 2px;
    background: var(--primary-gradient);
    transform: scaleX(0);
    transition: var(--transition-smooth);
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg);
    border-color: rgba(0, 0, 0, 0.12);
    background: var(--bg-secondary);
}

.product-card:hover::before {
    transform: scaleX(1);
    box-shadow: 0 0 15px rgba(102, 126, 234, 0.5);
}

.product-header {
    display: flex;
    align-items: flex-start;
    gap: var(--spacing-md);
    margin-bottom: var(--spacing-lg);
}

.product-image {
    width: 80px;
    height: 80px;
    border-radius: var(--radius-md);
    object-fit: cover;
    box-shadow: var(--shadow-sm);
    transition: var(--transition-smooth);
}

.product-card:hover .product-image {
    transform: scale(1.1);
    box-shadow: var(--shadow-md);
}

.product-info {
    flex: 1;
}

.product-name {
    font-size: 1.2rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: var(--spacing-xs);
}

.product-sku {
    font-size: 0.85rem;
    color: var(--text-muted);
    font-family: 'Monaco', monospace;
    background: var(--bg-card);
    padding: 2px 8px;
    border-radius: var(--radius-sm);
    display: inline-block;
    margin-bottom: var(--spacing-xs);
}

.product-category {
    background: var(--primary-gradient);
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
}

.product-details {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: var(--spacing-md);
    margin-bottom: var(--spacing-lg);
}

.detail-item {
    text-align: center;
}

.detail-label {
    font-size: 0.85rem;
    color: var(--text-muted);
    margin-bottom: var(--spacing-xs);
    font-weight: 500;
}

.detail-value {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--text-primary);
}

.detail-value.price {
    background: var(--success-gradient);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    font-weight: 700;
}

.stock-status {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stock-status.in-stock {
    background: rgba(0, 242, 254, 0.2);
    color: var(--accent-success);
    border: 1px solid var(--accent-success);
}

.stock-status.low-stock {
    background: rgba(254, 225, 64, 0.2);
    color: var(--accent-warning);
    border: 1px solid var(--accent-warning);
}

.stock-status.out-of-stock {
    background: rgba(255, 107, 107, 0.2);
    color: var(--accent-danger);
    border: 1px solid var(--accent-danger);
}

.product-actions {
    display: flex;
    justify-content: flex-end;
    gap: var(--spacing-sm);
}

.action-btn-sm {
    width: 40px;
    height: 40px;
    border-radius: var(--radius-sm);
    border: 1px solid rgba(0, 0, 0, 0.08);
    background: var(--bg-glass);
    color: var(--text-secondary);
    cursor: pointer;
    transition: var(--transition-smooth);
    display: flex;
    align-items: center;
    justify-content: center;
}

.action-btn-sm:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-sm);
}

.btn-edit:hover {
    background: var(--primary-gradient);
    color: white;
    border-color: var(--accent-primary);
}

.btn-delete:hover {
    background: var(--danger-gradient);
    color: white;
    border-color: var(--accent-danger);
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: var(--bg-modal);
    backdrop-filter: blur(10px);
}

.modal-content {
    background: var(--bg-secondary);
    margin: 5% auto;
    padding: var(--spacing-xl);
    border-radius: var(--radius-xl);
    width: 90%;
    max-width: 500px;
    position: relative;
    box-shadow: var(--shadow-lg);
    border: 1px solid rgba(0, 0, 0, 0.08);
    animation: modalSlideIn 0.3s ease-out;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-50px) scale(0.9);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.close {
    position: absolute;
    right: var(--spacing-lg);
    top: var(--spacing-lg);
    font-size: 2rem;
    font-weight: bold;
    color: var(--text-secondary);
    cursor: pointer;
    transition: var(--transition-smooth);
}

.close:hover {
    color: var(--accent-danger);
    transform: scale(1.2);
}

.modal-content h2 {
    margin-bottom: var(--spacing-lg);
    color: var(--text-primary);
    font-size: 1.8rem;
    font-weight: 700;
}

.form-group {
    margin-bottom: var(--spacing-lg);
}

.form-group label {
    display: block;
    margin-bottom: var(--spacing-xs);
    color: var(--text-secondary);
    font-weight: 500;
}

.form-group input {
    width: 100%;
    padding: var(--spacing-sm);
    background: var(--bg-glass);
    border: 1px solid rgba(0, 0, 0, 0.08);
    border-radius: var(--radius-sm);
    color: var(--text-primary);
    font-size: 1rem;
    transition: var(--transition-smooth);
}

.form-group input:focus {
    outline: none;
    border-color: var(--accent-primary);
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
    background: var(--bg-card);
}

.submit-btn {
    width: 100%;
    padding: var(--spacing-md);
    background: var(--primary-gradient);
    border: none;
    border-radius: var(--radius-sm);
    color: white;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition-smooth);
    box-shadow: var(--shadow-glow);
}

.submit-btn:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg), var(--shadow-glow);
}

.submit-btn:active {
    transform: translateY(0);
}

/* Responsive Design */
@media (max-width: 1200px) {
    .main-content {
        padding: var(--spacing-lg);
    }
    
    .products-grid {
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: var(--spacing-md);
    }
}

@media (max-width: 768px) {
    .menu-btn {
        display: block;
    }
    
    .stats-grid {
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: var(--spacing-md);
    }
    
    .quick-actions {
        justify-content: center;
    }
    
    .action-btn {
        flex: 1;
        justify-content: center;
        min-width: 120px;
    }
    
    .products-grid {
        grid-template-columns: 1fr;
    }
    
    .section-header {
        flex-direction: column;
        gap: var(--spacing-md);
        align-items: flex-start;
    }
    
    .product-details {
        grid-template-columns: 1fr;
        gap: var(--spacing-sm);
    }
    
    .detail-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        text-align: left;
    }
}

@media (max-width: 480px) {
    .main-content {
        padding: var(--spacing-md);
    }
    
    .page-title h1 {
        font-size: 1.5rem;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .quick-actions {
        flex-direction: column;
    }
    
    .action-btn {
        width: 100%;
    }
    
    .modal-content {
        margin: 10% auto;
        width: 95%;
        padding: var(--spacing-lg);
    }
}

/* Scrollbar Styling */
::-webkit-scrollbar {
    width: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f5f9;
}

::-webkit-scrollbar-thumb {
    background: var(--primary-gradient);
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: var(--accent-secondary);
}
    </style>


</head>
<body>


    <div class="app-container">
        <!-- Mobile Header -->
        <header class="mobile-header">
            <div class="header-left">
                <button class="menu-btn" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="page-title">
                    <h1>Inventory</h1>
                </div>
            </div>
            <div class="header-actions">
                <button class="search-btn" onclick="toggleSearch()">
                    <i class="fas fa-search"></i>
                </button>
                <button class="filter-btn" onclick="toggleFilter()">
                    <i class="fas fa-filter"></i>
                </button>
                <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=100&h=100&fit=crop&crop=face" alt="Admin" class="user-avatar">
            </div>
        </header>

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
                    <li><a href="#"><i class="fas fa-tags"></i> Categories</a></li>
                    <li><a href="#"><i class="fas fa-shopping-cart"></i> Sales</a></li>
                    <li><a href="#"><i class="fas fa-cog"></i> Settings</a></li>
                    <li><a href="#"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Stats Section -->
            <section class="stats-section">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-content">
                            <div class="stat-icon">
                                <i class="fas fa-cube"></i>
                            </div>
                            <div class="stat-number">247</div>
                            <div class="stat-label">Total Products</div>
                        </div>
                    </div>
                    <div class="stat-card warning">
                        <div class="stat-content">
                            <div class="stat-icon">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div class="stat-number">24</div>
                            <div class="stat-label">Low Stock</div>
                        </div>
                    </div>
                    <div class="stat-card danger">
                        <div class="stat-content">
                            <div class="stat-icon">
                                <i class="fas fa-times-circle"></i>
                            </div>
                            <div class="stat-number">6</div>
                            <div class="stat-label">Out of Stock</div>
                        </div>
                    </div>
                    <div class="stat-card success">
                        <div class="stat-content">
                            <div class="stat-icon">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                            <div class="stat-number">$52.8K</div>
                            <div class="stat-label">Total Value</div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="quick-actions">
                    <button class="action-btn primary" onclick="openAddModal()">
                        <div class="action-icon">
                            <i class="fas fa-plus"></i>
                        </div>
                        <span>Add</span>
                    </button>
                    <button class="action-btn">
                        <div class="action-icon">
                            <i class="fas fa-file-import"></i>
                        </div>
                        <span>Import</span>
                    </button>
                    <button class="action-btn">
                        <div class="action-icon">
                            <i class="fas fa-file-export"></i>
                        </div>
                        <span>Export</span>
                    </button>
                    <button class="action-btn">
                        <div class="action-icon">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <span>Reports</span>
                    </button>
                    <button class="action-btn">
                        <div class="action-icon">
                            <i class="fas fa-tags"></i>
                        </div>
                        <span>Categories</span>
                    </button>
                </div>
            </section>

            <!-- Products Section -->
            <section class="products-section">
                <div class="section-header">
                    <h2 class="section-title">Products</h2>
                    <div class="view-toggle">
                        <button class="view-btn active">
                            <i class="fas fa-th-large"></i>
                        </button>
                        <button class="view-btn">
                            <i class="fas fa-list"></i>
                        </button>
                    </div>
                </div>

                <div class="products-grid" id="products-grid">
                    <!-- Product Card 1 -->
                    <div class="product-card">
                        <div class="product-header">
                            <img src="https://images.unsplash.com/photo-1544787219-7f47ccb76574?w=100&h=100&fit=crop" alt="Coffee" class="product-image">
                            <div class="product-info">
                                <h3 class="product-name">Premium Coffee Beans</h3>
                                <div class="product-sku">CFE-001</div>
                                <span class="product-category">Beverages</span>
                            </div>
                        </div>
                        <div class="product-details">
                            <div class="detail-item">
                                <div class="detail-label">Price</div>
                                <div class="detail-value price">$24.99</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Stock</div>
                                <div class="detail-value">45</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Status</div>
                                <span class="stock-status in-stock">In Stock</span>
                            </div>
                        </div>
                        <div class="product-actions">
                            <button class="action-btn-sm btn-edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="action-btn-sm btn-delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Product Card 2 -->
                    <div class="product-card">
                        <div class="product-header">
                            <img src="https://images.unsplash.com/photo-1566478989037-eec170784d0b?w=100&h=100&fit=crop" alt="Green Tea" class="product-image">
                            <div class="product-info">
                                <h3 class="product-name">Organic Green Tea</h3>
                                <div class="product-sku">GRT-014</div>
                                <span class="product-category">Beverages</span>
                            </div>
                        </div>
                        <div class="product-details">
                            <div class="detail-item">
                                <div class="detail-label">Price</div>
                                <div class="detail-value price">$12.50</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Stock</div>
                                <div class="detail-value">20</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Status</div>
                                <span class="stock-status low-stock">Low Stock</span>
                            </div>
                            </div>
                        <div class="product-actions">
                            <button class="action-btn-sm btn-edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="action-btn-sm btn-delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Additional product cards can be added here -->

                </div>
            </section>
        </main>

        <!-- Add Product Modal -->
        <div class="modal" id="addModal">
            <div class="modal-content">
                <span class="close" onclick="closeAddModal()">&times;</span>
                <h2>Add New Product</h2>
                <form id="addProductForm">
                    <div class="form-group">
                        <label for="productName">Name</label>
                        <input type="text" id="productName" name="productName" required>
                    </div>
                    <div class="form-group">
                        <label for="productSKU">SKU</label>
                        <input type="text" id="productSKU" name="productSKU" required>
                    </div>
                    <div class="form-group">
                        <label for="productCategory">Category</label>
                        <input type="text" id="productCategory" name="productCategory" required>
                    </div>
                    <div class="form-group">
                        <label for="productPrice">Price</label>
                        <input type="number" step="0.01" id="productPrice" name="productPrice" required>
                    </div>
                    <div class="form-group">
                        <label for="productStock">Stock</label>
                        <input type="number" id="productStock" name="productStock" required>
                    </div>
                    <button type="submit" class="submit-btn">Add Product</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            document.body.classList.toggle('sidebar-open');
        }

        function toggleSearch() {
            alert('Search toggled (implement function)');
        }

        function toggleFilter() {
            alert('Filter toggled (implement function)');
        }

        function openAddModal() {
            document.getElementById('addModal').style.display = 'block';
        }

        function closeAddModal() {
            document.getElementById('addModal').style.display = 'none';
        }

        // Optional: close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('addModal');
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }

        // Handle product form submission
        document.getElementById('addProductForm').addEventListener('submit', function(event) {
            event.preventDefault();
            alert('Product added (hook this into your backend logic)');
            closeAddModal();
        });

        console.log(window.innerWidth);
    </script>
</body>
