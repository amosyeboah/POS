<?php
session_start();

require_once '../../db.php';

// Check if a tenant ID is available in the session.
// If not, redirect or return an error.
if (!isset($_SESSION['tenant_id'])) {
    // For a real application, you'd handle this more gracefully,
    // like redirecting to a login page.
    die(json_encode(['error' => 'Authentication error: Tenant ID not found.']));
}

$tenant_id = $_SESSION['tenant_id'];
$conn = getConnection();

// Currency helper and tenant currency fetch
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
$currency_type = 'GHS';
if ($stmtCur = $conn->prepare('SELECT currency_type FROM tenants WHERE tenant_id = ?')) {
    $stmtCur->bind_param('i', $tenant_id);
    $stmtCur->execute();
    $stmtCur->bind_result($currency_type);
    $stmtCur->fetch();
    $stmtCur->close();
}
$currency_symbol = getCurrencySymbol($currency_type);

// --- 1. Fetch Categories for the current tenant ---
$categories_query = "SELECT category_id, name FROM categories WHERE status = 'active' AND tenant_id = ? ORDER BY name";
$categories_stmt = $conn->prepare($categories_query);
$categories_stmt->bind_param("i", $tenant_id);
$categories_stmt->execute();
$categories_result = $categories_stmt->get_result();

$categories = [];
while ($row = $categories_result->fetch_assoc()) {
    $categories[] = $row;
}
$categories_stmt->close();

// Inventory metrics
$total_items = 0;
if ($s = $conn->prepare('SELECT COUNT(*) FROM products WHERE tenant_id = ?')) {
    $s->bind_param('i', $tenant_id);
    $s->execute();
    $s->bind_result($total_items);
    $s->fetch();
    $s->close();
}

$low_stock_count = 0;
if ($s = $conn->prepare('SELECT COUNT(*) FROM products WHERE tenant_id = ? AND stock <= COALESCE(min_stock, 0)')) {
    $s->bind_param('i', $tenant_id);
    $s->execute();
    $s->bind_result($low_stock_count);
    $s->fetch();
    $s->close();
}

$out_of_stock_count = 0;
if ($s = $conn->prepare('SELECT COUNT(*) FROM products WHERE tenant_id = ? AND stock <= 0')) {
    $s->bind_param('i', $tenant_id);
    $s->execute();
    $s->bind_result($out_of_stock_count);
    $s->fetch();
    $s->close();
}

$categories_count = 0;
if ($s = $conn->prepare('SELECT COUNT(*) FROM categories WHERE tenant_id = ?')) {
    $s->bind_param('i', $tenant_id);
    $s->execute();
    $s->bind_result($categories_count);
    $s->fetch();
    $s->close();
}

$total_value = 0.0; // stock value = stock * (cost fallback price)
if ($s = $conn->prepare('SELECT COALESCE(SUM(stock * COALESCE(cost, price)),0) FROM products WHERE tenant_id = ?')) {
    $s->bind_param('i', $tenant_id);
    $s->execute();
    $s->bind_result($total_value);
    $s->fetch();
    $s->close();
}

// Expected profit on current stock: sum(stock * (price - cost))
$expected_profit = 0.0;
if ($s = $conn->prepare('SELECT COALESCE(SUM(stock * (price - COALESCE(cost, 0))),0) FROM products WHERE tenant_id = ?')) {
    $s->bind_param('i', $tenant_id);
    $s->execute();
    $s->bind_result($expected_profit);
    $s->fetch();
    $s->close();
}

// --- 2. Fetch Products for the current tenant with filters ---
$where_conditions = ["p.tenant_id = ?"]; // Show all statuses by default; filter via UI
$params = [$tenant_id];
$types = "i"; // 'i' for tenant_id

// Handle category filter
if (isset($_GET['category']) && !empty($_GET['category'])) {
    // Validate the input to ensure it's an integer
    if (filter_var($_GET['category'], FILTER_VALIDATE_INT)) {
        $where_conditions[] = "p.category_id = ?";
        $params[] = $_GET['category'];
        $types .= "i";
    }
}

// Handle status filter
if (isset($_GET['status']) && !empty($_GET['status'])) {
    // Validate input against allowed values to prevent injection
    $allowed_statuses = ['active', 'inactive', 'archived']; // Add all valid statuses
    if (in_array($_GET['status'], $allowed_statuses)) {
        $where_conditions[] = "p.status = ?";
        $params[] = $_GET['status'];
        $types .= "s";
    }
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
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

$stmt->close();
$conn->close();

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
        /* Compact stat cards */
        .stats-grid { display:grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 0.75rem; }
        .stat-card { padding: 0.75rem 0.9rem; min-height: auto; }
        .stat-card .stat-number { font-size: 1.25rem; line-height: 0.7; }
        .stat-card .stat-icon i { font-size: 1.5rem; }

        /* List view styles */
        #inventoryContainer.list-view { display: grid; grid-template-columns: 1fr; gap: 0.6rem; }
        #inventoryContainer.list-view .inventory-item { display: grid; grid-template-columns: 2fr 1fr 1fr 1fr 1fr auto; align-items: center; gap: 0.5rem; padding: 0.6rem 0.8rem; border: 1px solid #eef2f7; border-radius: 10px; background: #fff; }
        #inventoryContainer.list-view .item-header { margin: 0; }
        #inventoryContainer.list-view .item-header .item-status { margin-left: 0.5rem; }
        #inventoryContainer.list-view .item-details { display: contents; }
        #inventoryContainer.list-view .item-detail { display: block; }
        #inventoryContainer.list-view .item-detail .detail-label { display: none; }
        #inventoryContainer.list-view .item-detail .detail-value { display: block; color: #111827; font-weight: 500; }
        #inventoryContainer.list-view .item-actions { display: flex; justify-content: flex-end; gap: 0.4rem; }

        /* Ensure grid view remains as designed */
        #inventoryContainer:not(.list-view) { display: grid; }
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
                    <li><a href="../employee.php"><i class="fas fa-users"></i> Employees</a></li>
                    <li><a href="../report.php"><i class="fas fa-chart-line"></i> Reports</a></li>
                    <li><a href="../categories.php"><i class="fas fa-tags"></i> Categories</a></li>
                    <li><a href="../sales.php"><i class="fas fa-shopping-cart"></i> Sales</a></li>
                    <li><a href="../settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                    <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
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
                    <div class="stat-number"><?= number_format((int)$total_items) ?></div>
                    <div class="stat-label">Total Items</div>
                </div>
                <div class="stat-card low-stock">
                    <div class="stat-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-number"><?= number_format((int)$low_stock_count) ?></div>
                    <div class="stat-label">Low Stock</div>
                </div>
                <div class="stat-card out_of_stock" style="border-bottom-left-radius: red;">
                    <div class="stat-icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="stat-number"><?= number_format((int)$out_of_stock_count) ?></div>
                    <div class="stat-label">Out of Stock</div>
                </div>
                <!-- <div class="stat-card categories">
                    <div class="stat-icon">
                        <i class="fas fa-tags"></i>
                    </div>
                    <div class="stat-number"><?= number_format((int)$categories_count) ?></div>
                    <div class="stat-label">Categories</div>
                </div> -->
                <div class="stat-card value">
                    <div class="stat-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-number"><?= $currency_symbol . number_format((float)$total_value, 2) ?></div>
                    <div class="stat-label">Total Value</div>
                </div>
                <div class="stat-card profit">
                    <div class="stat-icon">
                        <i class="fas fa-coins"></i>
                    </div>
                    <div class="stat-number"><?= $currency_symbol . number_format((float)$expected_profit, 2) ?></div>
                    <div class="stat-label">Expected Profit</div>
                </div>
            </div>

            <!-- Controls Section -->
            <div class="controls-section">
    <form class="search-filters" method="GET" action="index.php" style="display:flex; gap:0.5rem; align-items:center;">
        <input type="text" class="search-input" name="search" placeholder="Search products..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
        <select class="filter-select" name="category">
            <option value="">All Categories</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= (int)$category['category_id'] ?>" <?= (isset($_GET['category']) && $_GET['category'] == $category['category_id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($category['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <select class="filter-select" name="status">
            <option value="">All Status</option>
            <option value="active" <?= (($_GET['status'] ?? '') === 'active') ? 'selected' : '' ?>>Active</option>
            <option value="inactive" <?= (($_GET['status'] ?? '') === 'inactive') ? 'selected' : '' ?>>Inactive</option>
            <option value="archived" <?= (($_GET['status'] ?? '') === 'archived') ? 'selected' : '' ?>>Archived</option>
        </select>
        <button type="submit" class="action-btn secondary">
            <i class="fas fa-filter"></i><span class="btn-text">Apply</span>
        </button>
        <a href="index.php" class="action-btn secondary">
            <i class="fas fa-times"></i><span class="btn-text">Clear</span>
        </a>
    </form>
    <div class="action-buttons">
        <!-- keep your existing action buttons (Add Product, Import, Export) here -->
                <!-- <a href="#" id="add-category-btn" class="action-btn">
                <i class="fas fa-plus"></i>
                <span class="btn-text">Add Category</span>
                </a> -->
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
                        <button class="view-btn active" data-view="grid" title="Grid view">
                            <i class="fas fa-th"></i>
                        </button>
                        <button class="view-btn" data-view="list" title="List view">
                            <i class="fas fa-list"></i>
                        </button>
                    </div>
                </div>

                <div class="inventory-grid" id="inventoryContainer">
    <?php foreach ($products as $product): ?>
        <div class="inventory-item">
            <div class="item-header">
                <h3 class="item-name"><?= htmlspecialchars($product['name']) ?></h3>
                <?php
                    $status_class = '';
                    if ($product['stock'] == 0) {
                        $status_class = 'status-out-stock';
                        $status_text = 'Out of Stock';
                    } elseif ($product['stock'] <= $product['min_stock']) {
                        $status_class = 'status-low-stock';
                        $status_text = 'Low Stock';
                    } else {
                        $status_class = 'status-in-stock';
                        $status_text = 'In Stock';
                    }
                ?>
                <span class="item-status <?= $status_class ?>"><?= $status_text ?></span>
            </div>
            <div class="item-details">
                <div class="item-detail">
                    <span class="detail-label">Stock</span>
                    <span class="detail-value"><?= $product['stock'] ?> units</span>
                </div>
                <div class="item-detail">
                    <span class="detail-label">Price</span>
                    <span class="detail-value"><?= $currency_symbol . number_format($product['price'], 2) ?></span>
                </div>
                <div class="item-detail">
                    <span class="detail-label">Category</span>
                    <span class="detail-value"><?= htmlspecialchars($product['category_name'] ?? 'Uncategorized') ?></span>
                </div>
                <div class="item-detail">
                    <span class="detail-label">SKU</span>
                    <span class="detail-value"><?= htmlspecialchars($product['barcode']) ?></span>
                </div>
            </div>
 <div class="item-actions">
    <button class="item-action-btn" onclick="editProduct(<?= $product['product_id']; ?>)">Edit</button>

    <button class="btn-icon delete-product" title="Delete" onclick="deleteProduct(<?= $product['product_id']; ?>)">
        <i class="fas fa-trash" style="color:tomato;"></i>
    </button>

    <button class="item-action-btn primary" onclick="restockProduct(<?= $product['product_id']; ?>)">Restock</button>
</div>

        </div>
    <?php endforeach; ?>
</div>

            </div>
        </main>
    </div>

        <!-- Product Modal -->
    <div id="product-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add New Product</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="product-form" action="save_product.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="product_id" id="product_id">
                    
                    <div class="form-row">
                        <div class="form-group" style="flex: 2;">
                            <label for="product-name">Product Name*</label>
                            <input type="text" id="product-name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="product-barcode">Barcode</label>
                            <div style="display: flex; gap: 0.5rem;">
                                <input type="text" id="product-barcode" name="barcode" style="flex: 1;">
                                <button type="button" class="btn-outline" id="scan-barcode" style="padding: 0 1rem;">
                                    <i class="fas fa-barcode"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="product-category">Category*</label>
                            <select id="product-category" name="category_id" required>
                                <option value="">Select category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= (int)$category['category_id'] ?>" <?= (isset($_GET['category']) && $_GET['category'] == $category['category_id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($category['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="product-price">Price*</label>
                            <input type="number" id="product-price" name="price" min="0" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label for="product-cost">Cost</label>
                            <input type="number" id="product-cost" name="cost" min="0" step="0.01">
                        </div>
                        <div class="form-group">
                            <label for="product-tax">Tax Rate (%)</label>
                            <input type="number" id="product-tax" name="tax_rate" min="0" max="100" step="0.1" value="0.0">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="product-stock">Current Stock</label>
                            <input type="number" id="product-stock" name="stock" min="0" value="0">
                        </div>
                        <div class="form-group">
                            <label for="product-min-stock">Min. Stock Level</label>
                            <input type="number" id="product-min-stock" name="min_stock" min="0" value="5">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="product-description">Description</label>
                        <textarea id="product-description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="product-image">Product Image</label>
                        <div class="image-upload" style="border: 2px dashed rgba(0,0,0,0.1); border-radius: var(--border-radius); padding: 1.5rem; text-align: center; cursor: pointer;">
                            <i class="fas fa-cloud-upload-alt" style="font-size: 2rem; color: var(--gray); margin-bottom: 0.5rem;"></i>
                            <div>Click to upload or drag and drop</div>
                            <input type="file" id="product-image" name="image" accept="image/*" style="display: none;">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="product-status">Status</label>
                        <select id="product-status" name="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-outline close-modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Category Modal -->
<div id="category-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add New Category</h3>
            <button class="close-modal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="category-form">
                <div class="form-group">
                    <label for="category-name">Category Name*</label>
                    <input type="text" id="category-name" required>
                </div>
                <div class="form-group">
                    <label for="parent-category">Parent Category</label>
                    <select id="parent-category">
                        <option value="">None</option>
                        <!-- Optionally populate with PHP if you want dynamic parents -->
                    </select>
                </div>
                <div class="form-group">
                    <label for="category-icon">Icon</label>
                    <select id="category-icon">
                        <option value="wine-bottle">Wine Bottle</option>
                        <option value="hamburger">Hamburger</option>
                        <option value="headphones">Headphones</option>
                        <option value="coffee">Coffee</option>
                        <option value="pizza-slice">Pizza</option>
                        <option value="mobile">Mobile</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="category-description">Description</label>
                    <textarea id="category-description" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label for="category-status">Status</label>
                    <select id="category-status">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-outline close-modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

    <div id="status-modal" class="modal">
  <div class="modal-content" style="max-width:400px;text-align:center;">
    <div class="modal-header">
      <h3>Status</h3>
      <button class="close-modal" onclick="closeStatusModal()">&times;</button>
    </div>
    <div class="modal-body" id="status-modal-message"></div>
    <div class="form-actions">
      <button class="btn btn-primary" onclick="closeStatusModal()">OK</button>
    </div>
  </div>
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

        // View toggle functionality (grid/list) with persistence
        const viewBtns = document.querySelectorAll('.view-btn');
        const inventoryContainer = document.getElementById('inventoryContainer');

        function applyView(view) {
            if (view === 'list') {
                inventoryContainer.classList.add('list-view');
            } else {
                inventoryContainer.classList.remove('list-view');
            }
            viewBtns.forEach(b => b.classList.toggle('active', b.dataset.view === view));
            try { localStorage.setItem('inventoryView', view); } catch (e) {}
        }

        // Initialize from localStorage (default: grid)
        let savedView = 'grid';
        try { savedView = localStorage.getItem('inventoryView') || 'grid'; } catch (e) {}
        applyView(savedView);

        // Handle clicks
        viewBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const v = this.dataset.view || 'grid';
                applyView(v);
            });
        });

    </script>
    <!-- <script src="../../assets/js/product-modal.js"></script>
    <script>
        function editProduct(id) {
            fetch(`../products/get_product.php?id=${id}`)
                .then(res => res.json())
                .then data => {
                    openProductModal(data);
                });
        }

        document.getElementById('add-product-btn').addEventListener('click', () => {
            openProductModal();
        });
    </script> -->
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Modal functionality
            const productModal = document.getElementById('product-modal');
            const addProductBtn = document.getElementById('add-product-btn');
            const closeModalBtns = document.querySelectorAll('.close-modal');
            
            // Open modal for new product
            addProductBtn.addEventListener('click', function() {
                productModal.classList.add('active');
                document.querySelector('#product-modal h3').textContent = 'Add New Product';
                document.getElementById('product-form').reset();
                document.getElementById('product_id').value = '';
            });
            
            // Close modals
            closeModalBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    productModal.classList.remove('active');
                });
            });
            
            // Close modal when clicking outside
            window.addEventListener('click', function(event) {
                if (event.target === productModal) {
                    productModal.classList.remove('active');
                }
            });
            
            // Image upload preview
            const imageUpload = document.querySelector('.image-upload');
            const imageInput = document.getElementById('product-image');
            
            imageUpload.addEventListener('click', () => imageInput.click());
            
            imageInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        imageUpload.innerHTML = `
                            <img src="${e.target.result}" style="max-width: 100%; max-height: 200px;">
                            <button type="button" class="btn-outline" onclick="event.stopPropagation(); this.parentElement.innerHTML = '<i class=\'fas fa-cloud-upload-alt\'></i><div>Click to upload or drag and drop</div>';">
                                Change Image
                            </button>
                        `;
                    };
                    reader.readAsDataURL(this.files[0]);
                }
            });
        });
        
        function editProduct(productId) {
            // Fetch product details and populate form
            fetch(`get_product.php?id=${productId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const product = data.product;
                        document.querySelector('#product-modal h3').textContent = 'Edit Product';
                        document.getElementById('product_id').value = product.product_id;
                        document.getElementById('product-name').value = product.name;
                        document.getElementById('product-barcode').value = product.barcode;
                        document.getElementById('product-category').value = product.category_id;
                        document.getElementById('product-price').value = product.price;
                        document.getElementById('product-cost').value = product.cost;
                        document.getElementById('product-tax').value = product.tax_rate;
                        document.getElementById('product-stock').value = product.stock;
                        document.getElementById('product-min-stock').value = product.min_stock;
                        document.getElementById('product-description').value = product.description;
                        document.getElementById('product-status').value = product.status;
                        
                        // Show existing image if available
                        if (product.image_path) {
                            const imageUpload = document.querySelector('.image-upload');
                            imageUpload.innerHTML = `
                                <img src="${product.image_path}" style="max-width: 100%; max-height: 200px;">
                                <button type="button" class="btn-outline" onclick="event.stopPropagation(); this.parentElement.innerHTML = '<i class=\'fas fa-cloud-upload-alt\'></i><div>Click to upload or drag and drop</div>';">
                                    Change Image
                                </button>
                            `;
                        }
                        
                        document.getElementById('product-modal').classList.add('active');
                    } else {
                        alert('Failed to load product details');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading product details');
                });
        }
        
        function deleteProduct(productId) {
            if (confirm('Are you sure you want to delete this product?')) {
                fetch('delete_product.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ product_id: productId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.error || 'Failed to delete product');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error deleting product');
                });
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
    // Modal functionality
    const productModal = document.getElementById('product-modal');
    const addProductBtn = document.getElementById('add-product-btn');
    const closeModalBtns = document.querySelectorAll('.close-modal');
    
    // Open modal for new product
    addProductBtn.addEventListener('click', function() {
        productModal.classList.add('active');
        document.querySelector('#product-modal h3').textContent = 'Add New Product';
        document.getElementById('product-form').reset();
        document.getElementById('product_id').value = '';
    });
    
    // Close modals
    closeModalBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            productModal.classList.remove('active');
        });
    });
    
    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        if (event.target === productModal) {
            productModal.classList.remove('active');
        }
    });
    
    // Image upload preview
    const imageUpload = document.querySelector('.image-upload');
    const imageInput = document.getElementById('product-image');
    
    imageUpload.addEventListener('click', () => imageInput.click());
    
    imageInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imageUpload.innerHTML = `
                    <img src="${e.target.result}" style="max-width: 100%; max-height: 200px;">
                    <button type="button" class="btn-outline" onclick="event.stopPropagation(); this.parentElement.innerHTML = '<i class=\'fas fa-cloud-upload-alt\'></i><div>Click to upload or drag and drop</div>';">
                        Change Image
                    </button>
                `;
            };
            reader.readAsDataURL(this.files[0]);
        }
    });
    
    // AJAX submit for product form
    const productForm = document.getElementById('product-form');
    productForm.addEventListener('submit', function(e) {
        e.preventDefault(); // Prevent normal form submit
        const formData = new FormData(productForm);
        fetch('save_product.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then (data => {
            document.getElementById('product-modal').classList.remove('active');
            showStatusModal(data.message || 'Product saved!');
        })
        .catch(() => {
            showStatusModal('An error occurred.');
        });
    });
});

// Modal show/hide functions
function showStatusModal(message) {
    document.getElementById('status-modal-message').textContent = message;
    document.getElementById('status-modal').classList.add('active');
}
function closeStatusModal() {
    document.getElementById('status-modal').classList.remove('active');
    location.reload(); // Reload to update product list
}

document.addEventListener('DOMContentLoaded', function() {
    // Category modal functionality
    const categoryModal = document.getElementById('category-modal');
    const addCategoryBtn = document.getElementById('add-category-btn');
    const closeModalBtns = document.querySelectorAll('.close-modal');

    // Open modal
    addCategoryBtn.addEventListener('click', function(e) {
        e.preventDefault();
        categoryModal.classList.add('active');
        document.querySelector('#category-modal h3').textContent = 'Add New Category';
        document.getElementById('category-form').reset();
    });

    // Close modals
    closeModalBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            categoryModal.classList.remove('active');
        });
    });

    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        if (event.target === categoryModal) {
            categoryModal.classList.remove('active');
        }
    });

    // Form submission (replace with AJAX for real saving)
    document.getElementById('category-form').addEventListener('submit', function(e) {
        e.preventDefault();
        alert('Category saved successfully!');
        categoryModal.classList.remove('active');
        // Optionally reload or update category list here
    });
});
    </script>
</body>
</html>