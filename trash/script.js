// Sales Performance Line Chart
const salesCtx = document.getElementById('salesChart').getContext('2d');
new Chart(salesCtx, {
    type: 'line',
    data: {
        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
        datasets: [{
            label: 'Sales ($)',
            data: [6200, 7300, 5400, 8200, 6700, 9100, 8600],
            backgroundColor: 'rgba(75, 192, 192, 0.1)',
            borderColor: 'rgba(75, 192, 192, 1)',
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } }
    }
});

// Top Selling Products Bar Chart
const topProductsCtx = document.getElementById('topProductsChart').getContext('2d');
new Chart(topProductsCtx, {
    type: 'bar',
    data: {
        labels: ['Bread', 'Milk', 'Eggs', 'Cereal', 'Soap'],
        datasets: [{
            label: 'Units Sold',
            data: [320, 280, 250, 230, 180],
            backgroundColor: '#36A2EB'
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        indexAxis: 'y'
    }
});

// Revenue by Category Pie Chart
const categoryCtx = document.getElementById('categoryChart').getContext('2d');
new Chart(categoryCtx, {
    type: 'pie',
    data: {
        labels: ['Grocery', 'Beverages', 'Household', 'Personal Care'],
        datasets: [{
            data: [4500, 3200, 2900, 2100],
            backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0']
        }]
    }
});

// Inventory Analysis Doughnut Chart
const inventoryCtx = document.getElementById('inventoryChart').getContext('2d');
new Chart(inventoryCtx, {
    type: 'doughnut',
    data: {
        labels: ['In Stock', 'Low Stock', 'Out of Stock'],
        datasets: [{
            data: [120, 30, 10],
            backgroundColor: ['#4CAF50', '#FFC107', '#F44336']
        }]
    }
});

// Payment Methods Pie Chart
const paymentCtx = document.getElementById('paymentChart').getContext('2d');
new Chart(paymentCtx, {
    type: 'pie',
    data: {
        labels: ['Cash', 'Mobile Money', 'Card'],
        datasets: [{
            data: [4000, 3200, 2000],
            backgroundColor: ['#2196F3', '#8BC34A', '#FF9800']
        }]
    }
});
