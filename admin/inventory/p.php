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
    <title>Admin Dashboard | POS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <meta name="apple-mobile-web-app-capable" content="yes">
<meta name="mobile-web-app-capable" content="yes">
<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, viewport-fit=cover">
<link rel="apple-touch-icon" href="/path/to/apple-touch-icon.png">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../../assets/css/admin-products.css">



    <style>
        /* Mobile-first responsive styles */
@media (max-width: 768px) {
  /* General mobile adjustments */
  body {
    font-size: 14px;
  }
  
  .btn, button, select {
    padding: 8px 12px;
    font-size: 14px;
  }
  
  /* Header adjustments */
  .header {
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
  }
  
  .header h1 {
    font-size: 1.5rem;
  }
  
  /* Toolbar adjustments */
  .toolbar {
    flex-direction: column;
    gap: 10px;
  }
  
  .search-box {
    width: 100%;
  }
  
  .filter-options {
    width: 100%;
    display: flex;
    gap: 8px;
  }
  
  .filter-options select {
    flex: 1;
    min-width: 0;
  }
  
  /* Table adjustments */
  .table-container {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
  }
  
  #products-table {
    min-width: 600px; /* Allows horizontal scrolling */
  }
  
  #products-table th, 
  #products-table td {
    padding: 8px 6px;
  }
  
  /* Modal adjustments */
  .modal-content {
    width: 95%;
    margin: 10px auto;
  }
  
  .form-row {
    flex-direction: column;
  }
  
  .form-group {
    width: 100%;
    margin-bottom: 10px;
  }
  
  /* Product image in table */
  .product-image {
    width: 40px;
    height: 40px;
  }
  
  /* Actions buttons */
  .actions {
    display: flex;
    gap: 5px;
  }
  
  .btn-icon {
    width: 30px;
    height: 30px;
    font-size: 12px;
  }
  
  /* Stock level indicator */
  .stock-level {
    min-width: 60px;
  }
  
  /* Top bar adjustments */
  .top-bar {
    padding: 10px;
  }
  
  .page-title h1 {
    font-size: 1.3rem;
  }
  
  .page-title p {
    display: none;
  }
  
  .user-profile span {
    display: none;
  }
  
  .user-profile img {
    width: 30px;
    height: 30px;
  }
}

/* Very small screens (e.g., iPhone SE) */
@media (max-width: 375px) {
  .filter-options {
    flex-direction: column;
  }
  
  .modal-content {
    padding: 15px;
  }
  
  .form-actions {
    flex-direction: column-reverse;
    gap: 8px;
  }
  
  .form-actions .btn {
    width: 100%;
  }
}

/* Mobile-specific enhancements */
@media (hover: none) {
  /* Make sure tap targets are large enough */
  a, button, .btn, .btn-icon {
    min-height: 44px;
    min-width: 44px;
  }
  
  /* Prevent zoom on form inputs */
  select, input, textarea {
    font-size: 16px;
  }
  
  /* Improve dropdown select appearance */
  select {
    appearance: none;
    background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 8px center;
    background-size: 14px;
    padding-right: 25px;
  }
}

/* Sidebar mobile adjustments */
@media (max-width: 768px) {
  .sidebar {
    width: 75%;
    transform: translateX(-100%);
    z-index: 1000;
  }
  
  .sidebar.active {
    transform: translateX(0);
  }
  
  .overlay.active {
    display: block;
    opacity: 1;
  }
  
  .floating-menu {
    display: block;
  }
  
  .main-content {
    margin-left: 0;
  }
}

/* Add to your CSS file */
/* Touch feedback */
.btn-icon:active, .btn-icon.active {
    transform: scale(0.95);
    opacity: 0.9;
}

/* Row touch feedback */
#products-table tbody tr.row-touch {
    background-color: rgba(0, 0, 0, 0.05);
}

/* Better scrolling on mobile */
html {
    -webkit-overflow-scrolling: touch;
}

/* Prevent long presses from bringing up context menu */
a, button {
    -webkit-touch-callout: none;
    -webkit-user-select: none;
    user-select: none;
}

/* Status badges - make them more visible on mobile */
.status-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
}

/* Image upload area - make it more touch-friendly */
.image-upload {
    padding: 20px;
    transition: all 0.2s;
}

.image-upload:active {
    background-color: rgba(0, 0, 0, 0.05);
}

/* Modal form inputs - better mobile experience */
input, select, textarea {
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    border-radius: 4px;
    padding: 10px 12px;
    border: 1px solid #ddd;
    width: 100%;
    box-sizing: border-box;
}

/* Stock bar - make it more visible */
.stock-bar {
    height: 4px;
    border-radius: 2px;
    margin-top: 4px;
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
                    <li><a href="employees.php"><i class="fas fa-users"></i> Employees</a></li>
                    <li><a href="report.php"><i class="fas fa-chart-line"></i> Reports</a></li>
                    <li><a href="#"><i class="fas fa-tags"></i> Categories</a></li>
                     <li class="active"><a href="#"><i class="fas fa-tags"></i>Products</a></li>
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
                    <h1>Product Page</h1>
                    <p>Welcome back! Here's what's happening today.</p>
                </div>
                <button class="menu-toggle" id="menu-toggle">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="user-profile">
                    <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=100&h=100&fit=crop&crop=face" alt="Admin">
                    <span>John Admin</span>
                </div>
            </header>

           <div class="container">
        <div class="header">
            <h1>Product Inventory</h1>
            <button id="add-product-btn" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Product
            </button>
        </div>

        <div class="card">
            <div class="toolbar">
                <form method="GET" class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" placeholder="Search products..." 
                           value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                </form>
                <div class="filter-options">
                    <select name="category" class="btn-outline" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['category_id']; ?>" 
                                    <?php echo (isset($_GET['category']) && $_GET['category'] == $category['category_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <select name="status" class="btn-outline" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="active" <?php echo (isset($_GET['status']) && $_GET['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo (isset($_GET['status']) && $_GET['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
            </div>

            <div class="table-container">
                <table id="products-table">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($products)): ?>
                            <tr>
                                <td colspan="7" class="text-center">No products found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td>
                                        <div class="product-image">
                                            <?php if (!empty($product['image_path'])): ?>
                                                <img src="<?php echo htmlspecialchars($product['image_path']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                            <?php else: ?>
                                                <i class="fas fa-box"></i>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                                        <div class="text-muted">SKU: <?php echo htmlspecialchars($product['barcode']); ?></div>
                                    </td>
                                    <td><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></td>
                                    <td>$<?php echo number_format($product['price'], 2); ?></td>
                                    <td>
                                        <div class="stock-level">
                                            <span><?php echo $product['stock']; ?></span>
                                            <?php
                                            $stock_percentage = ($product['stock'] / $product['min_stock']) * 100;
                                            $stock_color = $stock_percentage > 50 ? '#10b759' : ($stock_percentage > 20 ? '#f8961e' : '#f72585');
                                            ?>
                                            <div class="stock-bar" style="width: <?php echo min($stock_percentage, 100); ?>%; background-color: <?php echo $stock_color; ?>;"></div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="status-badge <?php echo $product['status']; ?>">
                                            <?php echo ucfirst($product['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="actions">
                                            <button class="btn-icon edit-product" title="Edit" onclick="editProduct(<?php echo $product['product_id']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn-icon danger delete-product" title="Delete" onclick="deleteProduct(<?php echo $product['product_id']; ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
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
                                    <option value="<?php echo $category['category_id']; ?>">
                                        <?php echo htmlspecialchars($category['name']); ?>
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
                            <input type="number" id="product-tax" name="tax_rate" min="0" max="100" step="0.1" value="7.5">
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

    </script>

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
    </script>
    <script>
        // Add this to your existing script
document.addEventListener('DOMContentLoaded', function() {
    // Prevent form zoom on focus (iOS)
    document.addEventListener('focusin', function(e) {
        if (window.innerWidth <= 768 && 
            (e.target.matches('input') || e.target.matches('select') || e.target.matches('textarea'))) {
            setTimeout(() => {
                e.target.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }, 100);
        }
    });
    
    // Handle touch events for better mobile support
    document.querySelectorAll('.btn-icon').forEach(btn => {
        btn.addEventListener('touchstart', function() {
            this.classList.add('active');
        });
        
        btn.addEventListener('touchend', function() {
            this.classList.remove('active');
        });
    });
    
    // Make the entire table row tappable on mobile
    if (window.innerWidth <= 768) {
        document.querySelectorAll('#products-table tbody tr').forEach(row => {
            row.addEventListener('click', function(e) {
                // Don't trigger if clicking on a button
                if (!e.target.closest('.btn-icon') && !e.target.closest('a')) {
                    const editBtn = this.querySelector('.edit-product');
                    if (editBtn) editBtn.click();
                }
            });
            
            // Visual feedback for touch
            row.addEventListener('touchstart', function() {
                this.classList.add('row-touch');
            });
            
            row.addEventListener('touchend', function() {
                this.classList.remove('row-touch');
            });
        });
    }
});
    </script>
</body>
</html>
