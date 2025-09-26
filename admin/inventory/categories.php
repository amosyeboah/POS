<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Category Management | POS Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/admin-categories.css">
    <style>
         
    </style>
</head>
<body>
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
                        <tr>
                            <td>
                                <div class="category-info">
                                    <div class="category-icon">
                                        <i class="fas fa-wine-bottle"></i>
                                    </div>
                                    <div>
                                        <strong>Beverages</strong>
                                        <div>12 products</div>
                                    </div>
                                </div>
                            </td>
                            <td>-</td>
                            <td><span class="status-badge active">Active</span></td>
                            <td>
                                <div class="actions">
                                    <button class="btn-icon edit-category" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn-icon danger delete-category" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="category-info">
                                    <div class="category-icon">
                                        <i class="fas fa-hamburger"></i>
                                    </div>
                                    <div>
                                        <strong>Food</strong>
                                        <div>24 products</div>
                                    </div>
                                </div>
                            </td>
                            <td>-</td>
                            <td><span class="status-badge active">Active</span></td>
                            <td>
                                <div class="actions">
                                    <button class="btn-icon edit-category" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn-icon danger delete-category" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="category-info">
                                    <div class="category-icon">
                                        <i class="fas fa-headphones"></i>
                                    </div>
                                    <div>
                                        <strong>Electronics</strong>
                                        <div>8 products</div>
                                    </div>
                                </div>
                            </td>
                            <td>-</td>
                            <td><span class="status-badge active">Active</span></td>
                            <td>
                                <div class="actions">
                                    <button class="btn-icon edit-category" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn-icon danger delete-category" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="category-info" style="margin-left: 2rem;">
                                    <div class="category-icon" style="background-color: #e6f7ee; color: #10b759;">
                                        <i class="fas fa-coffee"></i>
                                    </div>
                                    <div>
                                        <strong>Coffee</strong>
                                        <div>5 products</div>
                                    </div>
                                </div>
                            </td>
                            <td>Beverages</td>
                            <td><span class="status-badge active">Active</span></td>
                            <td>
                                <div class="actions">
                                    <button class="btn-icon edit-category" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn-icon danger delete-category" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
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
                        <input type="text" id="category-name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="parent-category">Parent Category</label>
                        <select id="parent-category">
                            <option value="">None</option>
                            <option value="1">Beverages</option>
                            <option value="2">Food</option>
                            <option value="3">Electronics</option>
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Modal functionality
            const categoryModal = document.getElementById('category-modal');
            const addCategoryBtn = document.getElementById('add-category-btn');
            const closeModalBtns = document.querySelectorAll('.close-modal');
            
            // Open modal
            addCategoryBtn.addEventListener('click', function() {
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
            
            // Form submission
            document.getElementById('category-form').addEventListener('submit', function(e) {
                e.preventDefault();
                alert('Category saved successfully!');
                categoryModal.classList.remove('active');
            });
            
            // Edit category buttons
            document.querySelectorAll('.edit-category').forEach(btn => {
                btn.addEventListener('click', function() {
                    categoryModal.classList.add('active');
                    document.querySelector('#category-modal h3').textContent = 'Edit Category';
                    // In a real app, populate form with existing data
                });
            });
            
            // Delete category buttons
            document.querySelectorAll('.delete-category').forEach(btn => {
                btn.addEventListener('click', function() {
                    if (confirm('Are you sure you want to delete this category?')) {
                        // In a real app, send delete request
                        alert('Category deleted successfully!');
                    }
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