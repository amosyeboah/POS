<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['tenant_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}
require_once '../db.php';

// Ensure tenant_id is set
if (!isset($_SESSION['tenant_id'])) {
    die('Tenant not set.');
}
$tenant_id = $_SESSION['tenant_id'];

$conn = getConnection();

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

// Fetch categories for this tenant
$sql = "SELECT c.category_id, c.name, c.description, c.status, c.parent_id, p.name AS parent_name
        FROM categories c
        LEFT JOIN categories p ON c.parent_id = p.category_id
        WHERE c.tenant_id = ?
        ORDER BY c.name ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $tenant_id);
$stmt->execute();
$result = $stmt->get_result();

$categories = [];
while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}
$stmt->close();
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
    <link rel="stylesheet" href="../assets/css/admin-categories.css">
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
                    <li class="active"><a href="#"><i class="fas fa-tags"></i> Categories</a></li>
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
                    <h1>Category Page</h1>
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

              <div class="container">
        <div class="header">
            <h1>Category Management</h1>
            <button id="add-category-btn" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Category
            </button>
        </div>

        <div class="card">
            <div class="toolbar">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="category-search" placeholder="Search categories...">
                </div>
                <div class="filter-options">
                    <select id="status-filter" class="btn-outline">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>

            <div class="table-container">
                <table id="categories-table">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Parent</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
<?php foreach ($categories as $cat): ?>
    <tr>
        <td>
            <div class="category-info">
                <div class="category-icon">
                    <i class="fas fa-tags"></i>
                </div>
                <div>
                    <strong><?= htmlspecialchars($cat['name']) ?></strong>
                    <div><?= htmlspecialchars($cat['description']) ?></div>
                </div>
            </div>
        </td>
        <td><?= $cat['parent_name'] ? htmlspecialchars($cat['parent_name']) : '-' ?></td>
        <td>
            <span class="status-badge <?= $cat['status'] === 'active' ? 'active' : 'inactive' ?>">
                <?= ucfirst($cat['status']) ?>
            </span>
        </td>
        <td>
            <div class="actions">
                <button class="btn-icon edit-category" 
                        data-id="<?= $cat['category_id'] ?>" 
                        data-name="<?= htmlspecialchars($cat['name']) ?>" 
                        data-description="<?= htmlspecialchars($cat['description']) ?>" 
                        data-status="<?= $cat['status'] ?>" 
                        data-parent-id="<?= htmlspecialchars($cat['parent_id'] ?? '') ?>"
                        title="Edit">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn-icon danger delete-category" data-id="<?= $cat['category_id'] ?>" title="Delete">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </td>
    </tr>
<?php endforeach; ?>
</tbody>
                </table>
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
                        <input type="text" id="category-name" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="parent-category">Parent Category</label>
                        <select id="parent-category" name="parent_id">
                            <option value="">None</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['category_id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="category-description">Description</label>
                        <textarea id="category-description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="category-status">Status</label>
                        <select id="category-status" name="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-outline close-modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="category-submit">Save Category</button>
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


                document.addEventListener('DOMContentLoaded', function() {
            // Modal functionality
            const categoryModal = document.getElementById('category-modal');
            const addCategoryBtn = document.getElementById('add-category-btn');
            const closeModalBtns = document.querySelectorAll('.close-modal');
            
            // Open modal
            addCategoryBtn.addEventListener('click', function() {
                categoryModal.classList.add('active');
                document.querySelector('#category-modal h3').textContent = 'Add New Category';
                const form = document.getElementById('category-form');
                form.reset();
                delete form.dataset.editing;
                delete form.dataset.categoryId;
                document.getElementById('category-submit').textContent = 'Save Category';
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
            
            // Add Category
            document.getElementById('category-form').addEventListener('submit', function(e) {
                const name = document.getElementById('category-name').value.trim();
                if (!name) {
                    alert('Category name cannot be empty.');
                    e.preventDefault();
                    return false;
                }
                e.preventDefault();
                const formData = new FormData(this);
                formData.append('action', this.dataset.editing ? 'edit' : 'add');
                if (this.dataset.editing) {
                    formData.append('category_id', this.dataset.categoryId);
                }
                fetch('inventory/category_actions.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    alert(data.message);
                    location.reload();
                });
            });

            // Edit Category
            document.querySelectorAll('.edit-category').forEach(btn => {
                btn.addEventListener('click', function() {
                    const row = this.closest('tr');
                    const catId = this.dataset.id;
                    const form = document.getElementById('category-form');
                    form.dataset.editing = 'true';
                    form.dataset.categoryId = catId;

                    // Populate form fields
                    document.getElementById('category-name').value = this.dataset.name || '';
                    document.getElementById('category-description').value = this.dataset.description || '';
                    document.getElementById('category-status').value = this.dataset.status || 'active';
                    document.getElementById('parent-category').value = this.dataset.parentId || '';

                    // Update modal labels
                    document.querySelector('#category-modal h3').textContent = 'Edit Category';
                    document.getElementById('category-submit').textContent = 'Update Category';

                    // Open modal
                    document.getElementById('category-modal').classList.add('active');
                });
            });

            // Delete Category
            document.querySelectorAll('.delete-category').forEach(btn => {
                btn.addEventListener('click', function() {
                    if (!confirm('Delete this category?')) return;
                    const catId = this.dataset.id;
                    const formData = new FormData();
                    formData.append('action', 'delete');
                    formData.append('category_id', catId);
                    fetch('inventory/category_actions.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        alert(data.message);
                        location.reload();
                    });
                });
            });
            
            // Search functionality
            document.getElementById('category-search').addEventListener('input', function(e) {
                const searchTerm = e.target.value.toLowerCase();
                const rows = document.querySelectorAll('#categories-table tbody tr');
                
                rows.forEach(row => {
                    const categoryName = row.cells[0].textContent.toLowerCase();
                    if (categoryName.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
            
            // Filter functionality
            document.getElementById('status-filter').addEventListener('change', function(e) {
                const filterValue = e.target.value.toLowerCase();
                const rows = document.querySelectorAll('#categories-table tbody tr');
                
                if (!filterValue) {
                    rows.forEach(row => row.style.display = '');
                    return;
                }
                
                rows.forEach(row => {
                    const status = row.cells[2].textContent.toLowerCase();
                    if (status.includes(filterValue)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        });

    </script>
</body>
</html>
