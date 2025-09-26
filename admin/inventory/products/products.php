<?php include 'product-modal.php'; ?>
<script src="../../assets/js/product-modal.js"></script>
<script>
    function editProduct(id) {
        // Fetch product details via AJAX
        fetch(`get_product.php?id=${id}`)
            .then(res => res.json())
            .then(data => {
                openProductModal(data);
            });
    }

    document.getElementById('add-product-btn').addEventListener('click', () => {
        openProductModal();
    });
</script>
