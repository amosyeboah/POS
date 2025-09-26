   <?php
// Fetch categories for the dropdown
if (!isset($categories)) {
    $conn = getConnection();
    $categories_query = "SELECT category_id, name FROM categories WHERE status = 'active' ORDER BY name";
    $categories_result = $conn->query($categories_query);
    $categories = [];
    while ($row = $categories_result->fetch_assoc()) {
        $categories[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    
</head>
<body>
<!-- Add Product Modal -->
<div id="add-product-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add New Product</h3>
            <button class="close-modal" onclick="closeAddModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="add-product-form" action="save_product.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add">
                
                <div class="form-row">
                    <div class="form-group" style="flex: 2;">
                        <label for="add-product-name">Product Name*</label>
                        <input type="text" id="add-product-name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="add-product-barcode">Barcode</label>
                        <div style="display: flex; gap: 0.5rem;">
                            <input type="text" id="add-product-barcode" name="barcode" style="flex: 1;">
                            <button type="button" class="btn-outline" id="add-scan-barcode" style="padding: 0 1rem;">
                                <i class="fas fa-barcode"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="add-product-category">Category*</label>
                        <select id="add-product-category" name="category_id" required>
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
                        <label for="add-product-price">Price*</label>
                        <input type="number" id="add-product-price" name="price" min="0" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="add-product-cost">Cost</label>
                        <input type="number" id="add-product-cost" name="cost" min="0" step="0.01">
                    </div>
                    <div class="form-group">
                        <label for="add-product-tax">Tax Rate (%)</label>
                        <input type="number" id="add-product-tax" name="tax_rate" min="0" max="100" step="0.1" value="7.5">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="add-product-stock">Current Stock</label>
                        <input type="number" id="add-product-stock" name="stock" min="0" value="0">
                    </div>
                    <div class="form-group">
                        <label for="add-product-min-stock">Min. Stock Level</label>
                        <input type="number" id="add-product-min-stock" name="min_stock" min="0" value="5">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="add-product-description">Description</label>
                    <textarea id="add-product-description" name="description" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="add-product-image">Product Image</label>
                    <div class="add-image-upload" style="border: 2px dashed rgba(0,0,0,0.1); border-radius: var(--border-radius); padding: 1.5rem; text-align: center; cursor: pointer;">
                        <i class="fas fa-cloud-upload-alt" style="font-size: 2rem; color: var(--gray); margin-bottom: 0.5rem;"></i>
                        <div>Click to upload or drag and drop</div>
                        <input type="file" id="add-product-image" name="image" accept="image/*" style="display: none;">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="add-product-status">Status</label>
                    <select id="add-product-status" name="status">
                        <option value="active" selected>Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-outline" onclick="closeAddModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('add-product-modal').classList.add('active');
    document.getElementById('add-product-form').reset();
    
    // Reset image upload area
    const imageUpload = document.querySelector('.add-image-upload');
    imageUpload.innerHTML = `
        <i class="fas fa-cloud-upload-alt" style="font-size: 2rem; color: var(--gray); margin-bottom: 0.5rem;"></i>
        <div>Click to upload or drag and drop</div>
    `;
}

function closeAddModal() {
    document.getElementById('add-product-modal').classList.remove('active');
}

// Initialize add modal functionality
document.addEventListener('DOMContentLoaded', function() {
    // Image upload functionality for add modal
    const addImageUpload = document.querySelector('.add-image-upload');
    const addImageInput = document.getElementById('add-product-image');
    
    if (addImageUpload && addImageInput) {
        addImageUpload.addEventListener('click', () => addImageInput.click());
        
        addImageInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    addImageUpload.innerHTML = `
                        <img src="${e.target.result}" style="max-width: 100%; max-height: 200px;">
                        <button type="button" class="btn-outline" onclick="event.stopPropagation(); resetAddImageUpload();">
                            Change Image
                        </button>
                    `;
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    }
    
    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        const addModal = document.getElementById('add-product-modal');
        if (event.target === addModal) {
            closeAddModal();
        }
    });
});

function resetAddImageUpload() {
    const imageUpload = document.querySelector('.add-image-upload');
    imageUpload.innerHTML = `
        <i class="fas fa-cloud-upload-alt" style="font-size: 2rem; color: var(--gray); margin-bottom: 0.5rem;"></i>
        <div>Click to upload or drag and drop</div>
    `;
    document.getElementById('add-product-image').value = '';
}
</script>
</body>
</html>