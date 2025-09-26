<?php
require_once '../../db.php';

// Fetch categories for the filter dropdown
$conn = getConnection();
$categories_query = "SELECT category_id, name FROM categories WHERE status = 'active' ORDER BY name";
$categories_result = $conn->query($categories_query);
$categories = [];
while ($row = $categories_result->fetch_assoc()) {
    $categories[] = $row;
}

// Fetch products with filters
$where_conditions = ["p.status = 'active'"];
$params = [];
$types = "";

// Handle category filter
if (isset($_GET['category']) && !empty($_GET['category'])) {
    $where_conditions[] = "p.category_id = ?";
    $params[] = $_GET['category'];
    $types .= "i";
}

// Handle status filter
if (isset($_GET['status']) && !empty($_GET['status'])) {
    $where_conditions[] = "p.status = ?";
    $params[] = $_GET['status'];
    $types .= "s";
}

// Handle search
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $where_conditions[] = "(p.name LIKE ? OR p.barcode LIKE ?)";
    $search_term = "%" . $_GET['search'] . "%";
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= "ss";
}

$where_clause = implode(" AND ", $where_conditions);

$sql = "SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.category_id 
        WHERE $where_clause 
        ORDER BY p.name ASC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Admin - Inventory</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/inventory.css">
    <link rel="stylesheet" href="../../assets/css/admin-products.css">
    <style>
        
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Overlay for mobile -->
        <div class="overlay" id="overlay"></div>

        <!-- Sidebar - Updated to match dashboard -->
        <aside class="sidebar" id="sidebar">
            <div class="logo">
                <i class="fas fa-cash-register"></i>
                <span>POS Admin</span>
            </div>
            <nav>
                <ul>
                    <li><a href="../index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li class="active"><a href="#"><i class="fas fa-boxes"></i> Inventory</a></li>
                    <li><a href="../employees.php"><i class="fas fa-users"></i> Employees</a></li>
                    <li><a href="../report.php"><i class="fas fa-chart-line"></i> Reports</a></li>
                    <li><a href="../categories.php"><i class="fas fa-tags"></i> Categories</a></li>
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
                <a href="#" id="add-product-btn" class="action-btn">
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
                             <button class="btn-icon delete-product" title="Delete" onclick="deleteProduct(<?php echo $product['product_id']; ?>)">
                            <i class="fas fa-delete"></i>
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
                </div>
            </div>
        </main>
    </div>
</body>
</html>