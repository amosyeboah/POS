// Cashier System JavaScript (v1.2) - for the 'cashier/sales.php'
// This script handles the cashier system for a point of sale application.
// It includes functionalities for adding products to an order, calculating totals, and processing payments.

// BASE_API_URL is defined in the HTML file

// Global variables
let currentOrder = {
    items: [],
    subtotal: 0,
    tax: 0,
    total: 0
};
let selectedCustomer = null;

// DOM Elements
const productSearch = document.getElementById('product-search');
const categoriesContainer = document.querySelector('.categories-scroll');
const orderItemsContainer = document.getElementById('order-items');
const subtotalElement = document.getElementById('subtotal');
const taxElement = document.getElementById('tax');
const totalElement = document.getElementById('total');
const productModal = document.getElementById('product-modal');
const productList = document.getElementById('product-list');
const clearOrderBtn = document.getElementById('clear-order');
const loadingSpinner = document.getElementById('loading-spinner');

// Initialize the application
document.addEventListener('DOMContentLoaded', () => {
    loadCategories();
    setupEventListeners();

    // Add test button (temporary)
    const testButton = document.createElement('button');
    testButton.textContent = 'Test Notification';
    testButton.style.position = 'fixed';
    testButton.style.bottom = '10px';
    testButton.style.right = '10px';
    testButton.onclick = testNotification;
    document.body.appendChild(testButton);
});

// Load categories from API
async function loadCategories() {
    try {
        const response = await fetch(`${BASE_API_URL}/get_categories.php?tenant_id=${tenant_id}`);
        const data = await response.json();
        if (data.success) {
            renderCategories(data.categories);
        } else {
            showError('Failed to load categories');
        }
    } catch (error) {
        showError('Error loading categories');
    }
}

// Render categories in the UI
function renderCategories(categories) {
    console.log('Rendering categories:', categories);
    const categoriesContainer = document.querySelector('.categories-scroll');
    console.log('Categories container:', categoriesContainer);
    
    if (!categoriesContainer) {
        console.error('Categories container not found!');
        return;
    }
    
    categoriesContainer.innerHTML = '';
    
    // Add "All" category
    const allCategory = document.createElement('div');
    allCategory.className = 'category active';
    allCategory.textContent = 'All';
    allCategory.onclick = () => loadProducts();
    categoriesContainer.appendChild(allCategory);
    console.log('Added "All" category');
    
    // Add other categories
    categories.forEach(category => {
        const categoryElement = document.createElement('div');
        categoryElement.className = 'category';
        categoryElement.textContent = category.name;
        categoryElement.onclick = () => loadProducts(category.id);
        categoriesContainer.appendChild(categoryElement);
        console.log('Added category:', category.name);
    });
}

// Load products from API
async function loadProducts(categoryId = null) {
    try {
        let url = `${BASE_API_URL}/get_products.php?tenant_id=${tenant_id}`;
        if (categoryId) {
            url += `&category_id=${categoryId}`;
        }
        const response = await fetch(url);
        const data = await response.json();
        if (data.success) {
            renderProducts(data.products);
            showProductModal();
        } else {
            showError('Failed to load products');
        }
    } catch (error) {
        showError('Error loading products');
    }
}

// Render products in the UI
function renderProducts(products) {
    console.log('Rendering products:', products);
    const productList = document.getElementById('product-list');
    console.log('Product list container:', productList);
    
    if (!productList) {
        console.error('Product list container not found!');
        return;
    }
    
    productList.innerHTML = '';
    
    if (!products || products.length === 0) {
        productList.innerHTML = '<div class="no-products">No products found</div>';
        return;
    }
    
    products.forEach(product => {
        const productElement = document.createElement('div');
        productElement.className = 'product-item';
        productElement.innerHTML = `
            <div class="product-name">${product.name}</div>
            <div class="product-price">$${parseFloat(product.price).toFixed(2)}</div>
            <div class="product-stock">Stock: ${product.stock}</div>
        `;
        productElement.onclick = () => addToOrder(product);
        productList.appendChild(productElement);
        console.log('Added product:', product.name);
    });
}

// Add product to current order
function addToOrder(product) {
    const existingItem = currentOrder.items.find(item => item.id === product.id);
    
    if (existingItem) {
        if (existingItem.quantity < product.stock) {
            existingItem.quantity++;
        } else {
            showError('Not enough stock available');
            return;
        }
    } else {
        if (product.stock > 0) {
            currentOrder.items.push({
                id: product.id,
                name: product.name,
                price: parseFloat(product.price),
                quantity: 1,
                stock: parseInt(product.stock, 10),
                tax_rate: parseFloat(product.tax_rate) || 0 // include tax_rate from backend
            });
        } else {
            showError('Product out of stock');
            return;
        }
    }
    
    updateOrderDisplay();
}

// Update order display
function updateOrderDisplay() {
    // Reset totals
    currentOrder.subtotal = 0;
    currentOrder.tax = 0;
    currentOrder.total = 0;

    // Calculate totals per item
    currentOrder.items.forEach(item => {
        const lineSubtotal = item.price * item.quantity;
        const lineTax = lineSubtotal * (item.tax_rate / 100);
        currentOrder.subtotal += lineSubtotal;
        currentOrder.tax += lineTax;
        currentOrder.total += lineSubtotal + lineTax;
    });

    // Update UI
    orderItemsContainer.innerHTML = '';
    
    if (currentOrder.items.length === 0) {
        orderItemsContainer.innerHTML = '<div class="empty-cart">No items added yet</div>';
    } else {
        currentOrder.items.forEach((item, index) => {
            const lineSubtotal = item.price * item.quantity;
            const lineTax = lineSubtotal * (item.tax_rate / 100);

            const itemElement = document.createElement('div');
            itemElement.className = 'order-item';
            itemElement.innerHTML = `
                <div class="item-details">
                    <div class="item-name">${item.name}</div>
                    <div class="item-price">
                        $${(lineSubtotal + lineTax).toFixed(2)} 
                        <small>(incl. ${item.tax_rate}% tax)</small>
                    </div>
                </div>
                <div class="item-quantity-control">
                    <button class="qty-btn minus" ${item.quantity <= 1 ? 'disabled' : ''}>-</button>
                    <span class="item-quantity">${item.quantity}</span>
                    <button class="qty-btn plus">+</button>
                    <button class="btn-remove">×</button>
                </div>
            `;

            // Add event listeners for quantity buttons
            const minusBtn = itemElement.querySelector('.minus');
            const plusBtn = itemElement.querySelector('.plus');
            const removeBtn = itemElement.querySelector('.btn-remove');

            minusBtn.addEventListener('click', () => updateItemQuantity(index, -1));
            plusBtn.addEventListener('click', () => updateItemQuantity(index, 1));
            removeBtn.addEventListener('click', () => removeItem(index));

            orderItemsContainer.appendChild(itemElement);
        });
    }
    
    // Update totals display
    subtotalElement.textContent = `$${currentOrder.subtotal.toFixed(2)}`;
    taxElement.textContent = `$${currentOrder.tax.toFixed(2)}`;
    totalElement.textContent = `$${currentOrder.total.toFixed(2)}`;
}

// Function to update item quantity
async function updateItemQuantity(index, change) {
    const item = currentOrder.items[index];
    const newQuantity = item.quantity + change;

    // On increment, verify live stock from DB before proceeding
    if (change > 0) {
        try {
            const res = await fetch(`${BASE_API_URL}/check_stock.php?product_id=${encodeURIComponent(item.id)}`);
            const data = await res.json();
            if (!data.success) {
                showError(data.error || 'Failed to verify stock');
                return;
            }
            const liveStock = parseInt(data.stock, 10);
            // Update in-memory stock snapshot for transparency
            item.stock = liveStock;
            if (liveStock <= 0) {
                showError('Product out of stock');
                return;
            }
            if (newQuantity > liveStock) {
                showError('Not enough stock available');
                return;
            }
        } catch (e) {
            showError('Error checking stock');
            return;
        }
    }

    if (newQuantity > 0) {
        item.quantity = newQuantity;
        updateOrderDisplay();
    }
}

// Function to remove item
function removeItem(index) {
    currentOrder.items.splice(index, 1);
    updateOrderDisplay();
}

// Save order
async function saveOrder(paymentMethod) {
    if (currentOrder.items.length === 0) {
        showError('No items in order');
        return;
    }
    
    try {
        const payload = {
            items: currentOrder.items,
            total: currentOrder.total,
            payment_method: paymentMethod
        };
        if (selectedCustomer && selectedCustomer.customer_id) {
            payload.customer_id = selectedCustomer.customer_id;
        }
        const response = await fetch(`${BASE_API_URL}/save_order.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        });
        
        const data = await response.json();
        
        if (data.success) {
            showSuccess('Order saved successfully');
            clearOrder();
        } else {
            showError(data.error || 'Failed to save order');
        }
    } catch (error) {
        showError('Error saving order');
    }
}

// Clear current order
function clearOrder() {
    currentOrder = {
        items: [],
        subtotal: 0,
        tax: 0,
        total: 0
    };
    // Reset customer to walk-in for new order
    selectedCustomer = null;
    const sc = document.getElementById('selected-customer');
    if (sc) sc.textContent = 'Walk-in';
    updateOrderDisplay();
}

// Setup event listeners
function setupEventListeners() {
    // Search functionality
    productSearch.addEventListener('input', debounce(() => {
        const searchTerm = productSearch.value.trim();
        if (searchTerm) {
            loadProducts(null, searchTerm);
        } else {
            loadProducts();
        }
    }, 300));
    
    // Payment buttons
    document.getElementById('btn-cash').addEventListener('click', () => processPayment('cash'));
    document.getElementById('btn-card').addEventListener('click', () => processPayment('card'));
    document.getElementById('btn-mobile').addEventListener('click', () => processPayment('mobile'));
    
    // Clear order button
    document.getElementById('clear-order').onclick = clearOrder;
}

// Utility functions
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Show notification modal
function showNotification(message, type = 'success') {
    console.log('Showing notification:', message, type); // Debug log

    const modal = document.getElementById('notification-modal');
    console.log('Modal element:', modal); // Debug log

    if (!modal) {
        console.error('Notification modal not found!');
        alert(message); // Fallback to alert if modal not found
        return;
    }

    const content = modal.querySelector('.notification-content');
    const messageEl = modal.querySelector('.notification-message');
    const successIcon = modal.querySelector('.success-icon');
    const errorIcon = modal.querySelector('.error-icon');

    // Set message
    messageEl.textContent = message;

    // Show/hide appropriate icon
    successIcon.style.display = type === 'success' ? 'block' : 'none';
    errorIcon.style.display = type === 'error' ? 'block' : 'none';

    // Show modal with debug
    console.log('Displaying modal...'); // Debug log
    modal.style.display = 'flex';
    modal.style.opacity = '1'; // Add this line
    modal.classList.add('show');

    // Setup close button
    const closeBtn = modal.querySelector('.notification-close');
    if (closeBtn) {
        closeBtn.onclick = () => closeNotification();
    }

    // Close on background click
    modal.onclick = (e) => {
        if (e.target === modal) {
            closeNotification();
        }
    };
}

// Close notification modal
function closeNotification() {
    console.log('Closing notification...'); // Debug log
    const modal = document.getElementById('notification-modal');
    if (modal) {
        modal.classList.remove('show');
        modal.style.opacity = '0';
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300);
    }
}

// Show success message
function showSuccess(message) {
    showNotification(message, 'success');
}

// Show error message
function showError(message) {
    showNotification(message, 'error');
}

function showLoading() {
    loadingSpinner.style.display = 'flex';
}

function hideLoading() {
    loadingSpinner.style.display = 'none';
}

function showProductModal() {
    const modal = document.getElementById('product-modal');
    if (modal) {
        modal.style.display = 'block';
    }
}

function closeProductModal() {
    const modal = document.getElementById('product-modal');
    if (modal) {
        modal.style.display = 'none';
    }
}

// Close modal when clicking outside
window.addEventListener('click', function(event) {
    if (event.target === productModal) {
        closeProductModal();
    }
});

// Close button for modal
document.querySelector('.close-modal').addEventListener('click', closeProductModal);

// Process payment
async function processPayment(paymentMethod) {
    // Check if cart is empty
    if (currentOrder.items.length === 0) {
        showError('Cannot process payment: Cart is empty');
        return;
    }

    try {
        showLoading();

        const orderData = {
            items: currentOrder.items,
            subtotal: currentOrder.subtotal,
            tax: currentOrder.tax,
            total: currentOrder.total,
            payment_method: paymentMethod
        };
        if (selectedCustomer && selectedCustomer.customer_id) {
            orderData.customer_id = selectedCustomer.customer_id;
        }

        console.log('Processing order:', orderData);

        const response = await fetch(`${BASE_API_URL}/save_order.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(orderData)
        });

        const result = await response.json();
        console.log('Order result:', result);

        if (result.success) {
            showSuccess(`Payment successful! Order #${result.order_id} completed`);
            // Clear the cart after successful payment (also resets customer to Walk-in)
            clearOrder();
            // You might want to print the receipt here
            printReceipt(result.order_id);
        } else {
            showError('Payment failed: ' + (result.error || 'Unknown error'));
        }
    } catch (error) {
        console.error('Payment error:', error);
        showError('Payment processing failed');
    } finally {
        hideLoading();
    }
}

// Optional: Print receipt function
function printReceipt(orderId) {
    // You can implement receipt printing here
    // For now, we'll just log it
    console.log(`Printing receipt for order #${orderId}`);
}

// Test function - you can remove this later
function testNotification() {
    showSuccess('Test success message!');
}

///////////////////////////////

// Open modal
document.getElementById('btn-customer').onclick = () => {
    document.getElementById('customer-modal').style.display = 'block';
};

// Close modal
document.querySelector('.close-customer').onclick = () => {
    document.getElementById('customer-modal').style.display = 'none';
};

// Search customers
document.getElementById('customer-search').addEventListener('input', async function () {
    const query = this.value.trim();
    if (!query) {
        document.getElementById('customer-results').innerHTML = '';
        return;
    }

    const res = await fetch(`${BASE_API_URL}/search_customers.php?tenant_id=${tenant_id}&q=${query}`);
    const data = await res.json();
    
    if (data.success) {
        const resultsDiv = document.getElementById('customer-results');
        resultsDiv.innerHTML = '';
        data.customers.forEach(cust => {
            const div = document.createElement('div');
            div.textContent = `${cust.name} (${cust.phone || 'No phone'})`;
            div.onclick = () => selectCustomer(cust);
            resultsDiv.appendChild(div);
        });
    }
});

// Add new customer
document.getElementById('new-customer-form').onsubmit = async (e) => {
    e.preventDefault();
    const name = document.getElementById('cust-name').value;
    const phone = document.getElementById('cust-phone').value;
    const email = document.getElementById('cust-email').value;

    const res = await fetch(`${BASE_API_URL}/add_customer.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ tenant_id, name, phone, email })
    });
    const data = await res.json();

    if (data.success) {
        selectCustomer(data.customer);
    } else {
        alert(data.error);
    }
};

// Select customer
function selectCustomer(cust) {
    selectedCustomer = cust;
    document.getElementById('selected-customer').textContent = cust.name;
    document.getElementById('customer-modal').style.display = 'none';
}
