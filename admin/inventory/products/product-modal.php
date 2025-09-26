
    <style>
:root {
    --primary: #4361ee;
    --primary-light: #eef2ff;
    --secondary: #3f37c9;
    --danger: #f72585;
    --dark: #212529;
    --gray: #6c757d;
    --border-radius: 12px;
    --transition: all 0.3s ease;
}

.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1.5rem;
    z-index: 1000;
    opacity: 0;
    visibility: hidden;
    transition: var(--transition);
}

.modal.active {
    opacity: 1;
    visibility: visible;
}

.modal-content {
    background-color: white;
    border-radius: var(--border-radius);
    width: 100%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
    transform: translateY(20px);
    transition: var(--transition);
}

.modal.active .modal-content {
    transform: translateY(0);
}

.modal-header {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid rgba(0,0,0,0.05);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    font-size: 1.25rem;
    font-weight: 600;
}

.close-modal {
    font-size: 1.5rem;
    cursor: pointer;
    color: var(--gray);
    background: none;
    border: none;
    width: 2.5rem;
    height: 2.5rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: var(--transition);
}

.close-modal:hover {
    background-color: rgba(0,0,0,0.05);
    color: var(--dark);
}

.modal-body {
    padding: 1.5rem;
}

.form-group {
    margin-bottom: 1.25rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    font-size: 0.95rem;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 1px solid rgba(0,0,0,0.1);
    border-radius: var(--border-radius);
    font-size: 0.95rem;
    transition: var(--transition);
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    border-color: var(--primary);
    outline: none;
    box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
}

.form-row {
    display: flex;
    gap: 1rem;
}

.form-row .form-group {
    flex: 1;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    margin-top: 1.5rem;
}

@media (max-width: 768px) {
    .form-row {
        flex-direction: column;
        gap: 1rem;
    }
}

/* #product-modal {
      visibility: visible;
      opacity: 1;
    } */
</style>

   </style>
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
  const openModalBtn = document.getElementById('openModalBtn');
  const modal = document.getElementById('productModal');
  const closeBtn = modal.querySelector('.close-modal');

  // Open modal
  openModalBtn.addEventListener('click', (e) => {
    e.preventDefault(); // prevent anchor jump
    modal.classList.add('active');
  });

  // Close modal
  closeBtn.addEventListener('click', () => {
    modal.classList.remove('active');
  });

  // Optional: click outside modal-content to close
  modal.addEventListener('click', (e) => {
    if (e.target === modal) {
      modal.classList.remove('active');
    }
  });
</script>

