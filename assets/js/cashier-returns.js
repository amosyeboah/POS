document.addEventListener('DOMContentLoaded', function() {
    // DOM Elements
    const optionTabs = document.querySelectorAll('.option-tab');
    const optionContents = document.querySelectorAll('.option-content');
    const transactionDetails = document.getElementById('transaction-details');
    const originalItems = document.getElementById('original-items');
    const returnItems = document.getElementById('return-items');
    const returnSubtotal = document.getElementById('return-subtotal');
    const returnTax = document.getElementById('return-tax');
    const returnTotal = document.getElementById('return-total');
    const btnProcess = document.getElementById('btn-process');
    const productModal = document.getElementById('product-modal');
    const productSearch = document.getElementById('product-search');
    
    // Current return state
    let currentReturn = {
        originalTransaction: null,
        items: [],
        subtotal: 0,
        tax: 0,
        total: 0,
        taxRate: 0.075 // 7.5%
    };
    
    // Initialize the returns interface
    initReturns();
    
    function initReturns() {
        // Tab switching functionality
        optionTabs.forEach(tab => {
            tab.addEventListener('click', function() {
                const tabId = this.getAttribute('data-tab');
                
                // Update active tab
                optionTabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                
                // Show corresponding content
                optionContents.forEach(content => {
                    content.classList.remove('active');
                    if (content.id === `${tabId}-tab`) {
                        content.classList.add('active');
                    }
                });
                
                // For manual tab, show product search
                if (tabId === 'manual') {
                    transactionDetails.style.display = 'none';
                } else {
                    transactionDetails.style.display = 'block';
                }
            });
        });
        
        // Sample transaction data (will be replaced with actual API calls)
        loadSampleTransaction();
        
        // Quantity button handlers for original items
        document.querySelectorAll('.transaction-item .btn-qty').forEach(btn => {
            btn.addEventListener('click', function() {
                const itemEl = this.closest('.transaction-item');
                const itemName = itemEl.querySelector('.item-name').textContent;
                const itemPrice = parseFloat(itemEl.querySelector('.item-price').textContent.replace('$', ''));
                const qtyEl = itemEl.querySelector('.item-qty');
                let qty = parseInt(qtyEl.textContent);
                
                if (this.classList.contains('plus')) {
                    qty++;
                } else if (this.classList.contains('minus') && qty > 0) {
                    qty--;
                }
                
                qtyEl.textContent = qty;
                
                // Update return items
                updateReturnItems(itemName, itemPrice, qty);
            });
        });
        
        // Product search for manual returns
        productSearch.addEventListener('focus', showProductModal);
        
        // Process return button
        btnProcess.addEventListener('click', processReturn);
        
        // Cancel button
        document.getElementById('btn-cancel').addEventListener('click', function() {
            if (confirm('Are you sure you want to cancel this return?')) {
                window.location.href = 'dashboard.php';
            }
        });
        
        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target === productModal) {
                closeModal();
            }
        });
        
        // Close button for modal
        document.querySelector('.close-modal').addEventListener('click', closeModal);
    }
    
    function loadSampleTransaction() {
        // In a real app, this would be an API call based on receipt/transaction #
        currentReturn.originalTransaction = {
            id: '10045',
            date: 'Today, 12:05 PM',
            items: [
                { name: 'Soda', price: 1.50, originalQty: 1 },
                { name: 'Chips', price: 2.00, originalQty: 2 }
            ],
            subtotal: 5.50,
            tax: 0.41,
            total: 5.91
        };
        
        // Update UI with transaction details
        document.getElementById('original-transaction').textContent = currentReturn.originalTransaction.id;
        document.getElementById('original-total').textContent = `$${currentReturn.originalTransaction.subtotal.toFixed(2)}`;
        document.getElementById('original-tax').textContent = `$${currentReturn.originalTransaction.tax.toFixed(2)}`;
        document.querySelector('.transaction-date').textContent = currentReturn.originalTransaction.date;
        
        // Clear and reload original items
        originalItems.innerHTML = '';
        
        currentReturn.originalTransaction.items.forEach(item => {
            const itemEl = document.createElement('div');
            itemEl.className = 'transaction-item';
            itemEl.innerHTML = `
                <div class="item-info">
                    <span class="item-name">${item.name}</span>
                    <span class="item-price">$${item.price.toFixed(2)}</span>
                </div>
                <div class="item-actions">
                    <button class="btn-qty minus">-</button>
                    <span class="item-qty">${item.originalQty}</span>
                    <button class="btn-qty plus">+</button>
                </div>
            `;
            
            // Add event listeners to quantity buttons
            const minusBtn = itemEl.querySelector('.minus');
            const plusBtn = itemEl.querySelector('.plus');
            
            minusBtn.addEventListener('click', function() {
                let qty = parseInt(itemEl.querySelector('.item-qty').textContent);
                if (qty > 0) {
                    qty--;
                    itemEl.querySelector('.item-qty').textContent = qty;
                    updateReturnItems(item.name, item.price, qty);
                }
            });
            
            plusBtn.addEventListener('click', function() {
                let qty = parseInt(itemEl.querySelector('.item-qty').textContent);
                if (qty < item.originalQty) {
                    qty++;
                    itemEl.querySelector('.item-qty').textContent = qty;
                    updateReturnItems(item.name, item.price, qty);
                }
            });
            
            originalItems.appendChild(itemEl);
        });
    }
    
    function updateReturnItems(name, price, qty) {
        // Find if item already exists in return
        const existingItemIndex = currentReturn.items.findIndex(item => item.name === name);
        
        if (existingItemIndex >= 0) {
            if (qty > 0) {
                // Update quantity
                currentReturn.items[existingItemIndex].qty = qty;
            } else {
                // Remove item if quantity is 0
                currentReturn.items.splice(existingItemIndex, 1);
            }
        } else if (qty > 0) {
            // Add new item to return
            currentReturn.items.push({
                name: name,
                price: price,
                qty: qty
            });
        }
        
        // Update return display
        updateReturnDisplay();
    }
    
    function updateReturnDisplay() {
        // Clear return items display
        returnItems.innerHTML = '';
        
        if (currentReturn.items.length === 0) {
            returnItems.innerHTML = '<div class="empty-return">No items selected for return</div>';
            btnProcess.disabled = true;
        } else {
            // Calculate totals
            currentReturn.subtotal = currentReturn.items.reduce((sum, item) => sum + (item.price * item.qty), 0);
            currentReturn.tax = currentReturn.subtotal * currentReturn.taxRate;
            currentReturn.total = currentReturn.subtotal + currentReturn.tax;
            
            // Add items to display
            currentReturn.items.forEach(item => {
                const itemEl = document.createElement('div');
                itemEl.className = 'return-item';
                itemEl.innerHTML = `
                    <div class="return-item-info">
                        <span class="return-item-name">${item.name}</span>
                        <span class="return-item-qty">${item.qty} @ $${item.price.toFixed(2)}</span>
                    </div>
                    <div class="return-item-price">$${(item.price * item.qty).toFixed(2)}</div>
                `;
                returnItems.appendChild(itemEl);
            });
            
            // Update totals display
            returnSubtotal.textContent = `$${currentReturn.subtotal.toFixed(2)}`;
            returnTax.textContent = `$${currentReturn.tax.toFixed(2)}`;
            returnTotal.textContent = `$${currentReturn.total.toFixed(2)}`;
            
            // Enable process button if there's a reason selected
            const returnReason = document.getElementById('return-reason').value;
            btnProcess.disabled = !returnReason;
        }
        
        // Enable/disable process button based on return reason
        document.getElementById('return-reason').addEventListener('change', function() {
            btnProcess.disabled = currentReturn.items.length === 0 || !this.value;
        });
    }
    
    function showProductModal() {
        productModal.style.display = 'block';
        productSearch.blur();
        
        // Load products (in a real app, this would be an API call)
        const productList = document.getElementById('product-list');
        productList.innerHTML = '';
        
        const sampleProducts = [
            { id: 1, name: 'Soda', price: 1.50, stock: 10 },
            { id: 2, name: 'Chips', price: 2.00, stock: 5 },
            { id: 3, name: 'Candy', price: 1.25, stock: 8 },
            { id: 4, name: 'Beer', price: 4.50, stock: 12 },
            { id: 5, name: 'Coffee', price: 2.50, stock: 15 },
            { id: 6, name: 'Water', price: 1.00, stock: 20 }
        ];
        
        sampleProducts.forEach(product => {
            const productEl = document.createElement('div');
            productEl.className = 'product-item';
            productEl.innerHTML = `
                <div>
                    <div class="product-name">${product.name}</div>
                    <div class="product-price">$${product.price.toFixed(2)}</div>
                </div>
                <div class="product-stock">${product.stock} in stock</div>
            `;
            
            productEl.addEventListener('click', () => {
                addManualReturnItem(product);
                closeModal();
            });
            
            productList.appendChild(productEl);
        });
    }
    
    function addManualReturnItem(product) {
        // In a real app, this would check if the product was in the original transaction
        // For this demo, we'll just add it directly to the return
        
        const existingItemIndex = currentReturn.items.findIndex(item => item.name === product.name);
        
        if (existingItemIndex >= 0) {
            currentReturn.items[existingItemIndex].qty += 1;
        } else {
            currentReturn.items.push({
                name: product.name,
                price: product.price,
                qty: 1
            });
        }
        
        updateReturnDisplay();
    }
    
    function closeModal() {
        productModal.style.display = 'none';
    }
    
    function processReturn() {
        const returnReason = document.getElementById('return-reason').value;
        const returnNotes = document.getElementById('return-notes').value;
        
        if (currentReturn.items.length === 0) {
            alert('Please select items to return');
            return;
        }
        
        if (!returnReason) {
            alert('Please select a return reason');
            return;
        }
        
        // In a real app, this would send data to the server
        const returnData = {
            originalTransaction: currentReturn.originalTransaction.id,
            items: currentReturn.items,
            subtotal: currentReturn.subtotal,
            tax: currentReturn.tax,
            total: currentReturn.total,
            reason: returnReason,
            notes: returnNotes
        };
        
        console.log('Processing return:', returnData);
        
        // Show success message
        alert(`Return processed successfully! Refund total: $${currentReturn.total.toFixed(2)}`);
        
        // Redirect to dashboard
        window.location.href = 'dashboard.php';
    }
});