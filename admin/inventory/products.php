
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Inventory | POS Admin</title>
    <link rel="stylesheet" href="../../assets/css/admin-products.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
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
</body>
</html>