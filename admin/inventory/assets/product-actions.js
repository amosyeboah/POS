// product-actions.js

let pendingDeleteId = null;

export function deleteProduct(productId) {
    pendingDeleteId = productId;
    document.getElementById('confirm-dialog').style.display = 'flex';
}

window.confirmDelete = function (confirmed) {
    document.getElementById('confirm-dialog').style.display = 'none';
    if (confirmed && pendingDeleteId !== null) {
        fetch('delete_product.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ product_id: pendingDeleteId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Product deleted successfully!', 'success');
                // Option 1: Reload page to reflect changes
                setTimeout(() => location.reload(), 1000);

                // Option 2: Or remove from DOM instantly (for Ajax-style feel)
                // const item = document.querySelector(`[data-product-id="${pendingDeleteId}"]`);
                // if (item) item.remove();
            } else {
                showToast(data.error || 'Failed to delete product', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error deleting product', 'error');
        });

        pendingDeleteId = null;
    }
}

export function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    toast.textContent = message;
    toast.className = `toast ${type}`;
    toast.style.display = 'block';

    setTimeout(() => {
        toast.style.display = 'none';
    }, 3000);
}
