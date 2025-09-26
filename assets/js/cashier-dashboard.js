document.addEventListener('DOMContentLoaded', function() {
    const recentTransactionsList = document.getElementById('recent-transactions');

    function fetchRecentTransactions() {
        // Use the same backend script as the history page, but with a limit
        const params = new URLSearchParams({
            limit: 5,
            offset: 0
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
                    recentTransactionsList.innerHTML = `<p>${data.error}</p>`;
                    return;
                }

                if (data.transactions.length === 0) {
                    recentTransactionsList.innerHTML = `<p>No recent transactions found.</p>`;
                    return;
                }

                // Clear any existing placeholders
                recentTransactionsList.innerHTML = '';

                data.transactions.forEach(transaction => {
                    const transactionItem = document.createElement('div');
                    transactionItem.classList.add('transaction-item');
                    
                    const transactionTime = new Date(transaction.created_at).toLocaleTimeString('en-US', {
                        hour: '2-digit',
                        minute: '2-digit'
                    });

                    transactionItem.innerHTML = `
                        <div class="transaction-info">
                            <span class="transaction-id">#${transaction.transaction_code}</span>
                            <span class="transaction-time">${transactionTime}</span>
                        </div>
                        <div class="transaction-amount">₵${parseFloat(transaction.total).toFixed(2)}</div>
                    `;
                    recentTransactionsList.appendChild(transactionItem);
                });
            })
            .catch(error => {
                console.error('Error fetching recent transactions:', error);
                recentTransactionsList.innerHTML = `<p>Failed to load recent transactions.</p>`;
            });
    }

    // Call the function to load the transactions when the page loads
    fetchRecentTransactions();
});