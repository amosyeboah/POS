// assets/js/product-modal.js

document.addEventListener('DOMContentLoaded', function () {
    const productModal = document.getElementById('product-modal');
    const closeModalBtns = document.querySelectorAll('.close-modal');

    // Close modal
    closeModalBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            productModal.classList.remove('active');
        });
    });

    // Close on outside click
    window.addEventListener('click', function (event) {
        if (event.target === productModal) {
            productModal.classList.remove('active');
        }
    });

    // Open modal with optional prefill
    window.openProductModal = function (product = null) {
        productModal.classList.add('active');
        const form = document.getElementById('product-form');
        form.reset();
        document.querySelector('#product-modal h3').textContent = product ? 'Edit Product' : 'Add New Product';
        if (product) {
            for (let key in product) {
                if (form[key]) {
                    form[key].value = product[key];
                }
            }
        }
    };
});
