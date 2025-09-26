<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Reports | POS Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #eef2ff;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --danger: #f72585;
            --warning: #f8961e;
            --info: #4895ef;
            --dark: #212529;
            --light: #f8f9fa;
            --gray: #6c757d;
            --border-radius: 12px;
            --shadow: 0 4px 20px rgba(0,0,0,0.08);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        body {
            background-color: #f5f7fb;
            color: var(--dark);
            line-height: 1.6;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 1.5rem;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .header h1 {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--dark);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            border-radius: var(--border-radius);
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            font-size: 0.95rem;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--secondary);
            transform: translateY(-2px);
        }

        .btn-outline {
            background-color: transparent;
            border: 1px solid var(--primary);
            color: var(--primary);
        }

        .btn-outline:hover {
            background-color: var(--primary-light);
        }

        .btn-icon {
            background: none;
            border: none;
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
        }

        .btn-icon:hover {
            background-color: rgba(0,0,0,0.05);
        }

        .card {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .card-header {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .card-header h2 {
            font-size: 1.25rem;
            font-weight: 600;
        }

        .card-body {
            padding: 1.5rem;
        }

        .filter-bar {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 1.5rem;
        }

        .filter-group {
            position: relative;
            min-width: 200px;
        }

        .filter-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--gray);
        }

        .filter-group input,
        .filter-group select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid rgba(0,0,0,0.1);
            border-radius: var(--border-radius);
            font-size: 0.95rem;
            transition: var(--transition);
        }

        .filter-group input:focus,
        .filter-group select:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background-color: white;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            box-shadow: var(--shadow);
            text-align: center;
        }

        .stat-card .value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--primary);
        }

        .stat-card .label {
            font-size: 0.9rem;
            color: var(--gray);
        }

        .stat-card.today .value {
            color: var(--success);
        }

        .stat-card.week .value {
            color: var(--info);
        }

        .stat-card.month .value {
            color: var(--warning);
        }

        .chart-container {
            height: 350px;
            margin-bottom: 2rem;
        }

        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }

        th {
            text-align: left;
            padding: 1rem 0.75rem;
            font-weight: 600;
            color: var(--gray);
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }

        td {
            padding: 1rem 0.75rem;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            vertical-align: middle;
        }

        .badge {
            display: inline-block;
            padding: 0.35rem 0.75rem;
            border-radius: 100px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .badge.completed {
            background-color: rgba(16, 183, 89, 0.1);
            color: #10b759;
        }

        .badge.refunded {
            background-color: rgba(244, 67, 54, 0.1);
            color: #f44336;
        }

        .payment-method {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .payment-method i {
            color: var(--gray);
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 1rem;
            padding: 1.5rem;
            border-top: 1px solid rgba(0,0,0,0.05);
        }

        .export-options {
            display: flex;
            gap: 0.5rem;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .filter-bar {
                flex-direction: column;
                gap: 1rem;
            }
            
            .filter-group {
                width: 100%;
            }
            
            .stats-grid {
                grid-template-columns: 1fr 1fr;
            }
            
            .chart-container {
                height: 250px;
            }
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .export-options {
                flex-direction: column;
                width: 100%;
            }
            
            .export-options .btn {
                width: 100%;
            }
        }

        /* Animation for charts */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .chart-container {
            animation: fadeIn 0.5s ease forwards;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Sales Reports</h1>
            <div class="export-options">
                <button class="btn btn-outline">
                    <i class="fas fa-file-excel"></i> Export Excel
                </button>
                <button class="btn btn-outline">
                    <i class="fas fa-file-pdf"></i> Export PDF
                </button>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>Quick Stats</h2>
                <div class="filter-bar">
                    <div class="filter-group">
                        <label for="date-range">Date Range</label>
                        <input type="text" id="date-range" placeholder="Select date range" class="datepicker">
                    </div>
                    <div class="filter-group">
                        <label for="cashier-filter">Cashier</label>
                        <select id="cashier-filter">
                            <option value="">All Cashiers</option>
                            <option value="1">John Doe</option>
                            <option value="2">Jane Smith</option>
                            <option value="3">Mike Johnson</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="payment-filter">Payment Method</label>
                        <select id="payment-filter">
                            <option value="">All Methods</option>
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="mobile">Mobile</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="stats-grid">
                    <div class="stat-card today">
                        <div class="value">$1,245.80</div>
                        <div class="label">Today's Sales</div>
                        <div class="trend"><i class="fas fa-arrow-up"></i> 12% from yesterday</div>
                    </div>
                    <div class="stat-card week">
                        <div class="value">$8,752.40</div>
                        <div class="label">This Week</div>
                        <div class="trend"><i class="fas fa-arrow-up"></i> 5% from last week</div>
                    </div>
                    <div class="stat-card month">
                        <div class="value">$32,189.75</div>
                        <div class="label">This Month</div>
                        <div class="trend"><i class="fas fa-arrow-down"></i> 3% from last month</div>
                    </div>
                    <div class="stat-card">
                        <div class="value">214</div>
                        <div class="label">Total Transactions</div>
                        <div class="trend">Avg. $58.42 per sale</div>
                    </div>
                </div>

                <div class="chart-container">
                    <!-- This would be replaced with a real chart library like Chart.js -->
                    <div style="background-color: #f8f9fa; border-radius: var(--border-radius); height: 100%; display: flex; align-items: center; justify-content: center; color: var(--gray);">
                        [Sales Trend Chart Would Appear Here]
                    </div>
                </div>

                <div class="chart-container">
                    <div style="background-color: #f8f9fa; border-radius: var(--border-radius); height: 100%; display: flex; align-items: center; justify-content: center; color: var(--gray);">
                        [Payment Method Distribution Chart Would Appear Here]
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>Transaction Details</h2>
                <button class="btn btn-outline">
                    <i class="fas fa-filter"></i> Advanced Filters
                </button>
            </div>
            <div class="card-body">
                <div class="table-container">
                    <table id="transactions-table">
                        <thead>
                            <tr>
                                <th>Transaction ID</th>
                                <th>Date/Time</th>
                                <th>Cashier</th>
                                <th>Amount</th>
                                <th>Payment</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>#TRX-1001</td>
                                <td>2023-06-15 09:32 AM</td>
                                <td>John Doe</td>
                                <td>$58.75</td>
                                <td><span class="payment-method"><i class="fas fa-credit-card"></i> Card</span></td>
                                <td><span class="badge completed">Completed</span></td>
                                <td>
                                    <button class="btn-icon" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn-icon" title="Print Receipt">
                                        <i class="fas fa-print"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>#TRX-1002</td>
                                <td>2023-06-15 10:15 AM</td>
                                <td>Jane Smith</td>
                                <td>$124.30</td>
                                <td><span class="payment-method"><i class="fas fa-money-bill-wave"></i> Cash</span></td>
                                <td><span class="badge completed">Completed</span></td>
                                <td>
                                    <button class="btn-icon" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn-icon" title="Print Receipt">
                                        <i class="fas fa-print"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>#TRX-1003</td>
                                <td>2023-06-15 11:42 AM</td>
                                <td>Mike Johnson</td>
                                <td>$32.50</td>
                                <td><span class="payment-method"><i class="fas fa-mobile-alt"></i> Mobile</span></td>
                                <td><span class="badge completed">Completed</span></td>
                                <td>
                                    <button class="btn-icon" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn-icon" title="Print Receipt">
                                        <i class="fas fa-print"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>#TRX-1004</td>
                                <td>2023-06-14 03:22 PM</td>
                                <td>John Doe</td>
                                <td>$89.20</td>
                                <td><span class="payment-method"><i class="fas fa-credit-card"></i> Card</span></td>
                                <td><span class="badge refunded">Refunded</span></td>
                                <td>
                                    <button class="btn-icon" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn-icon" title="Print Receipt">
                                        <i class="fas fa-print"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>#TRX-1005</td>
                                <td>2023-06-14 05:10 PM</td>
                                <td>Jane Smith</td>
                                <td>$45.60</td>
                                <td><span class="payment-method"><i class="fas fa-money-bill-wave"></i> Cash</span></td>
                                <td><span class="badge completed">Completed</span></td>
                                <td>
                                    <button class="btn-icon" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn-icon" title="Print Receipt">
                                        <i class="fas fa-print"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="pagination">
                    <button class="btn-outline" disabled>
                        <i class="fas fa-chevron-left"></i> Previous
                    </button>
                    <span>Page 1 of 5</span>
                    <button class="btn-outline">
                        Next <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize date range picker
            flatpickr("#date-range", {
                mode: "range",
                dateFormat: "Y-m-d",
                defaultDate: [new Date().fp_incr(-7), new Date()],
                maxDate: "today"
            });

            // Filter functionality
            document.getElementById('cashier-filter').addEventListener('change', function(e) {
                console.log('Filter by cashier:', e.target.value);
                // In a real app, this would filter the data
            });

            document.getElementById('payment-filter').addEventListener('change', function(e) {
                console.log('Filter by payment method:', e.target.value);
                // In a real app, this would filter the data
            });

            // Export buttons
            document.querySelectorAll('.export-options .btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const type = this.querySelector('i').className.includes('excel') ? 'Excel' : 'PDF';
                    alert(`Exporting data to ${type} format`);
                    // In a real app, this would trigger a download
                });
            });

            // View details buttons
            document.querySelectorAll('.btn-icon .fa-eye').forEach(btn => {
                btn.addEventListener('click', function() {
                    const trxId = this.closest('tr').querySelector('td').textContent;
                    alert(`Viewing details for transaction ${trxId}`);
                    // In a real app, this would open a modal with details
                });
            });

            // Print receipt buttons
            document.querySelectorAll('.btn-icon .fa-print').forEach(btn => {
                btn.addEventListener('click', function() {
                    const trxId = this.closest('tr').querySelector('td').textContent;
                    alert(`Printing receipt for transaction ${trxId}`);
                    // In a real app, this would open print dialog
                });
            });
        });

       
    </script>
</body>
</html>