document.addEventListener('DOMContentLoaded', function() {
    // DOM Element selections
    const transactionList = document.getElementById('transaction-list');
    const totalTransactionsSpan = document.getElementById('total-transactions');
    const totalSalesSpan = document.getElementById('total-sales');
    const loadMoreContainer = document.getElementById('load-more');
    const btnLoadMore = document.getElementById('btn-load-more');
    const filterOverlay = document.getElementById('filter-overlay');
    const btnFilter = document.getElementById('btn-filter');
    const closeFilterBtn = document.querySelector('.close-filter');
    const btnCancelFilter = document.getElementById('btn-cancel-filter');
    const btnApplyFilter = document.getElementById('btn-apply-filter');
    const dateRangeSelect = document.getElementById('date-range');
    const customRangeDiv = document.getElementById('custom-range');
    
    // State variables for pagination and filtering
    let offset = 0;
    const limit = 10;
    let isLoading = false;
    let currentFilters = {};

    /**
     * Fetches and renders transaction data based on current filters and offset.
     */
    function fetchTransactions() {
        if (isLoading) return;
        isLoading = true;
        btnLoadMore.textContent = 'Loading...';

        const params = new URLSearchParams({
            limit: limit,
            offset: offset,
            ...currentFilters
        });

        fetch(`fetch_transactions.php?${params.toString()}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.error) {
                    transactionList.innerHTML = `<p>${data.error}</p>`;
                    return;
                }
                
                // Update summary cards
                totalTransactionsSpan.textContent = data.summary.total_transactions;
                totalSalesSpan.textContent = `$${parseFloat(data.summary.total_sales).toFixed(2)}`;

                // Render each new transaction
                data.transactions.forEach(transaction => {
                    const transactionItem = renderTransaction(transaction);
                    transactionList.appendChild(transactionItem);
                });

                offset += data.transactions.length;

                // Hide "Load More" button if no more data is available
                if (data.transactions.length < limit) {
                    btnLoadMore.style.display = 'none';
                } else {
                    btnLoadMore.style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Error fetching transactions:', error);
                // Display a user-friendly message for a failed fetch
                btnLoadMore.textContent = 'Failed to load';
            })
            .finally(() => {
                // This block runs regardless of success or failure
                isLoading = false;
            });
    }

    /**
     * Renders a single transaction into an HTML element.
     * @param {object} transaction - The transaction data from the server.
     * @returns {HTMLElement} The created transaction element.
     */
    function renderTransaction(transaction) {
        const transactionItem = document.createElement('div');
        transactionItem.classList.add('transaction-item');
        transactionItem.innerHTML = `
            <div class="transaction-main">
                <div class="transaction-id">#${transaction.transaction_code}</div>
                <div class="transaction-time">${formatDate(transaction.created_at)}</div>
                <div class="transaction-amount">$${parseFloat(transaction.total).toFixed(2)}</div>
            </div>
            <div class="transaction-details">
                <div class="payment-method">
                    <i class="fas fa-${getPaymentIcon(transaction.payment_method)}"></i> ${capitalizeFirstLetter(transaction.payment_method)}
                </div>
                <button class="btn-view-details" data-sale-id="${transaction.sale_id}">View Details</button>
            </div>
            <div class="transaction-items" style="display: none;">
                <p>Loading details...</p>
            </div>
        `;

        // Attach event listener for the "View Details" button
        const btnViewDetails = transactionItem.querySelector('.btn-view-details');
        btnViewDetails.addEventListener('click', function() {
            const saleId = this.dataset.saleId;
            const transactionItemsDiv = transactionItem.querySelector('.transaction-items');
            
            if (transactionItemsDiv.style.display === 'none') {
                transactionItemsDiv.style.display = 'block';
                // Fetch details only if they haven't been loaded yet
                if (transactionItemsDiv.innerHTML === '<p>Loading details...</p>') {
                    fetchTransactionItems(saleId, transactionItemsDiv);
                }
            } else {
                transactionItemsDiv.style.display = 'none';
            }
        });

        return transactionItem;
    }

    /**
     * Fetches and renders the detailed items for a single transaction.
     * @param {number} saleId - The ID of the sale to fetch.
     * @param {HTMLElement} container - The container to render the items into.
     */
    function fetchTransactionItems(saleId, container) {
        // Change the text to reflect the loading state
        container.innerHTML = '<p>Loading details...</p>';
        fetch(`fetch_sale_details.php?sale_id=${saleId}`)
            .then(response => response.json())
            .then(items => {
                let itemsHtml = '';
                let subtotal = 0;
                let tax = 0;
                
                items.forEach(item => {
                    const itemTotal = item.quantity * item.unit_price;
                    subtotal += item.quantity * item.unit_price;
                    tax += itemTotal * (item.tax_rate / 100);
                    itemsHtml += `
                        <div class="item-row">
                            <span class="item-name">${item.product_name}</span>
                            <span class="item-qty">${item.quantity} × $${parseFloat(item.unit_price).toFixed(2)}</span>
                            <span class="item-total">$${itemTotal.toFixed(2)}</span>
                        </div>
                    `;
                });

                const total = subtotal + tax;
                container.innerHTML = `
                    ${itemsHtml}
                    <div class="item-totals">
                        <div class="total-row">
                            <span>Subtotal:</span>
                            <span>$${subtotal.toFixed(2)}</span>
                        </div>
                        <div class="total-row">
                            <span>Tax:</span>
                            <span>$${tax.toFixed(2)}</span>
                        </div>
                        <div class="total-row grand-total">
                            <span>Total:</span>
                            <span>$${total.toFixed(2)}</span>
                        </div>
                    </div>
                `;
            })
            .catch(error => {
                console.error('Error fetching transaction items:', error);
                container.innerHTML = `<p>Error loading details.</p>`;
            });
    }

    // Helper functions
    function getPaymentIcon(method) {
        switch (method) {
            case 'cash': return 'money-bill-wave';
            case 'card': return 'credit-card';
            case 'mobile': return 'mobile-alt';
            default: return 'credit-card';
        }
    }

    function capitalizeFirstLetter(string) {
        if (typeof string !== 'string' || string.length === 0) {
            return '';
        }
        return string.charAt(0).toUpperCase() + string.slice(1);
    }
    
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleString();
    }
    
    // Event Listeners for filters and buttons
    btnFilter.addEventListener('click', () => filterOverlay.style.display = 'flex');
    closeFilterBtn.addEventListener('click', () => filterOverlay.style.display = 'none');
    btnCancelFilter.addEventListener('click', () => filterOverlay.style.display = 'none');

    dateRangeSelect.addEventListener('change', (e) => {
        if (e.target.value === 'custom') {
            customRangeDiv.style.display = 'block';
        } else {
            customRangeDiv.style.display = 'none';
        }
    });
    
    btnLoadMore.addEventListener('click', fetchTransactions);

    btnApplyFilter.addEventListener('click', () => {
        const dateRange = dateRangeSelect.value;
        const paymentType = document.getElementById('payment-type').value;
        const minAmount = document.getElementById('min-amount').value;
        const startDate = document.getElementById('start-date').value;
        const endDate = document.getElementById('end-date').value;

        currentFilters = {
            range: dateRange,
            payment: paymentType,
            min_amount: minAmount
        };
        if (dateRange === 'custom') {
            currentFilters.start_date = startDate;
            currentFilters.end_date = endDate;
        }

        // Clear the list and reset offset for a new fetch
        transactionList.innerHTML = '';
        offset = 0;
        fetchTransactions();
        filterOverlay.style.display = 'none';
    });

    // Initial load of transactions
    fetchTransactions();
});