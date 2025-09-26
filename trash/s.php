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
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --primary-light: #dbeafe;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --info: #3b82f6;
            --dark: #1f2937;
            --gray: #6b7280;
            --light-gray: #f9fafb;
            --border: #e5e7eb;
            --white: #ffffff;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --radius: 8px;
            --radius-lg: 12px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--light-gray);
            color: var(--dark);
            line-height: 1.5;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 1.5rem;
        }

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .header h1 {
            font-size: 1.875rem;
            font-weight: 700;
            color: var(--dark);
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.625rem 1rem;
            border-radius: var(--radius);
            font-weight: 500;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.15s ease;
            border: 1px solid transparent;
            text-decoration: none;
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .btn-primary {
            background-color: var(--primary);
            color: var(--white);
            border-color: var(--primary);
        }

        .btn-primary:hover:not(:disabled) {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }

        .btn-outline {
            background-color: var(--white);
            color: var(--gray);
            border-color: var(--border);
        }

        .btn-outline:hover:not(:disabled) {
            background-color: var(--light-gray);
            color: var(--dark);
        }

        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.8125rem;
        }

        .btn-icon {
            width: 2rem;
            height: 2rem;
            padding: 0;
            border-radius: 50%;
            justify-content: center;
        }

        /* Cards */
        .card {
            background-color: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow);
            margin-bottom: 1.5rem;
            overflow: hidden;
        }

        .card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: var(--white);
        }

        .card-header h2 {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--dark);
        }

        .card-body {
            padding: 1.5rem;
        }

        /* Filters */
        .filter-bar {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
        }

        .filter-group label {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--dark);
            margin-bottom: 0.375rem;
        }

        .filter-group input,
        .filter-group select {
            padding: 0.625rem 0.75rem;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            font-size: 0.875rem;
            background-color: var(--white);
            transition: all 0.15s ease;
        }

        .filter-group input:focus,
        .filter-group select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgb(37 99 235 / 0.1);
        }

        .filter-actions {
            display: flex;
            gap: 0.5rem;
            align-items: end;
        }

        /* Filter Tags */
        .filter-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .filter-tag {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.25rem 0.75rem;
            background-color: var(--primary-light);
            color: var(--primary-dark);
            border-radius: 9999px;
            font-size: 0.8125rem;
            font-weight: 500;
        }

        .filter-tag .remove {
            cursor: pointer;
            padding: 0.125rem;
            border-radius: 50%;
            width: 1rem;
            height: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
        }

        .filter-tag .remove:hover {
            background-color: rgba(0, 0, 0, 0.1);
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.25rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            padding: 1.5rem;
            background-color: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow);
            text-align: center;
            transition: all 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .stat-card .value {
            font-size: 1.875rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .stat-card .label {
            font-size: 0.875rem;
            color: var(--gray);
            font-weight: 500;
        }

        .stat-card .trend {
            font-size: 0.75rem;
            color: var(--gray);
            margin-top: 0.25rem;
        }

        .stat-card.today .value { color: var(--success); }
        .stat-card.yesterday .value { color: var(--info); }
        .stat-card.week .value { color: var(--warning); }
        .stat-card.month .value { color: var(--primary); }
        .stat-card.total .value { color: var(--primary-dark); }

        /* Table */
        .table-container {
            overflow-x: auto;
            border-radius: var(--radius);
            border: 1px solid var(--border);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: var(--white);
        }

        th {
            background-color: var(--light-gray);
            padding: 0.875rem 1rem;
            text-align: left;
            font-weight: 600;
            color: var(--dark);
            font-size: 0.8125rem;
            text-transform: uppercase;
            letter-spacing: 0.025em;
            border-bottom: 1px solid var(--border);
        }

        td {
            padding: 1rem;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }

        tbody tr:hover {
            background-color: rgba(37, 99, 235, 0.02);
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 0.25rem 0.625rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .badge.completed {
            background-color: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .badge.pending {
            background-color: rgba(245, 158, 11, 0.1);
            color: var(--warning);
        }

        .badge.refunded {
            background-color: rgba(239, 68, 68, 0.1);
            color: var(--danger);
        }

        /* Payment Methods */
        .payment-method {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .payment-method i {
            color: var(--gray);
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.25rem;
            border-top: 1px solid var(--border);
            background-color: var(--light-gray);
        }

        .pagination-info {
            font-size: 0.875rem;
            color: var(--gray);
        }

        .pagination-controls {
            display: flex;
            gap: 0.25rem;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: var(--white);
            border-radius: var(--radius-lg);
            width: 95%;
            max-width: 900px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: var(--shadow-lg);
        }

        .modal-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            font-size: 1.25rem;
            font-weight: 600;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--gray);
            padding: 0.25rem;
        }

        .modal-body {
            padding: 1.5rem;
        }

        /* Loading */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .header {
                flex-direction: column;
                align-items: stretch;
            }
            
            .header-actions {
                justify-content: center;
            }
            
            .filter-bar {
                grid-template-columns: 1fr;
            }
            
            .filter-actions {
                flex-direction: column;
            }
            
            .filter-actions .btn {
                width: 100%;
                justify-content: center;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .pagination {
                flex-direction: column;
                gap: 1rem;
            }
            
            th, td {
                padding: 0.75rem 0.5rem;
                font-size: 0.8125rem;
            }
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .header-actions {
                flex-direction: column;
                width: 100%;
            }
            
            .header-actions .btn {
                width: 100%;
                justify-content: center;
            }
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: var(--gray);
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.3;
        }

        .empty-state h3 {
            font-size: 1.125rem;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <a href="index.php" class="btn btn-outline btn-sm">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
                <h1>Sales Reports</h1>
            </div>
            <div class="header-actions">
                <button class="btn btn-outline btn-sm">
                    <i class="fas fa-file-excel"></i> Export Excel
                </button>
                <button class="btn btn-outline btn-sm">
                    <i class="fas fa-file-pdf"></i> Export PDF
                </button>
            </div>
        </div>

        <!-- Filter Tags -->
        <div class="filter-tags" id="filter-tags">
            <div class="filter-tag">
                Date: Today <span class="remove">&times;</span>
            </div>
            <div class="filter-tag">
                Cashier: John Doe <span class="remove">&times;</span>
            </div>
            <div class="filter-tag">
                Payment: Cash <span class="remove">&times;</span>
            </div>
        </div>

        <!-- Filters Card -->
        <div class="card">
            <div class="card-header">
                <h2>Filters & Quick Stats</h2>
                <button class="btn btn-outline btn-sm">
                    <i class="fas fa-filter"></i> Toggle Filters
                </button>
            </div>
            <div class="card-body">
                <!-- Filter Form -->
                <form class="filter-bar">
                    <div class="filter-group">
                        <label>Date Range</label>
                        <select>
                            <option>Today</option>
                            <option>Yesterday</option>
                            <option>This Week</option>
                            <option>This Month</option>
                            <option>Custom Range</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Cashier</label>
                        <select>
                            <option>All Cashiers</option>
                            <option>John Doe</option>
                            <option>Jane Smith</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Payment Method</label>
                        <select>
                            <option>All Methods</option>
                            <option>Cash</option>
                            <option>Card</option>
                            <option>Mobile</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Status</label>
                        <select>
                            <option>All Statuses</option>
                            <option>Completed</option>
                            <option>Pending</option>
                            <option>Refunded</option>
                        </select>
                    </div>
                    <div class="filter-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Apply
                        </button>
                        <button type="button" class="btn btn-outline">
                            <i class="fas fa-times"></i> Clear
                        </button>
                    </div>
                </form>

                <!-- Stats Grid -->
                <div class="stats-grid">
                    <div class="stat-card today">
                        <div class="value">₵2,450.00</div>
                        <div class="label">Today's Sales</div>
                    </div>
                    <div class="stat-card yesterday">
                        <div class="value">₵1,890.50</div>
                        <div class="label">Yesterday's Sales</div>
                    </div>
                    <div class="stat-card week">
                        <div class="value">₵12,350.75</div>
                        <div class="label">This Week</div>
                    </div>
                    <div class="stat-card month">
                        <div class="value">₵45,720.25</div>
                        <div class="label">This Month</div>
                    </div>
                    <div class="stat-card total">
                        <div class="value">₵125,480.00</div>
                        <div class="label">Total Sales</div>
                        <div class="trend">1,245 Transactions</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="card">
            <div class="card-header">
                <h2>Transaction Details</h2>
                <div class="pagination-info">Showing 20 of 1,245 transactions</div>
            </div>
            <div class="card-body" style="padding: 0;">
                <div class="table-container">
                    <table>
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
                                <td>#TXN-001234</td>
                                <td>2024-12-09 2:30 PM</td>
                                <td>John Doe</td>
                                <td>₵125.50</td>
                                <td>
                                    <span class="payment-method">
                                        <i class="fas fa-money-bill-wave"></i>
                                        Cash
                                    </span>
                                </td>
                                <td><span class="badge completed">Completed</span></td>
                                <td>
                                    <button class="btn-icon btn-outline" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn-icon btn-outline" title="Print Receipt">
                                        <i class="fas fa-print"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>#TXN-001233</td>
                                <td>2024-12-09 1:15 PM</td>
                                <td>Jane Smith</td>
                                <td>₵89.25</td>
                                <td>
                                    <span class="payment-method">
                                        <i class="fas fa-credit-card"></i>
                                        Card
                                    </span>
                                </td>
                                <td><span class="badge pending">Pending</span></td>
                                <td>
                                    <button class="btn-icon btn-outline">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn-icon btn-outline">
                                        <i class="fas fa-print"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>#TXN-001232</td>
                                <td>2024-12-09 12:45 PM</td>
                                <td>John Doe</td>
                                <td>₵245.75</td>
                                <td>
                                    <span class="payment-method">
                                        <i class="fas fa-mobile-alt"></i>
                                        Mobile
                                    </span>
                                </td>
                                <td><span class="badge completed">Completed</span></td>
                                <td>
                                    <button class="btn-icon btn-outline">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn-icon btn-outline">
                                        <i class="fas fa-print"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>#TXN-001231</td>
                                <td>2024-12-09 11:20 AM</td>
                                <td>Jane Smith</td>
                                <td>₵67.00</td>
                                <td>
                                    <span class="payment-method">
                                        <i class="fas fa-money-bill-wave"></i>
                                        Cash
                                    </span>
                                </td>
                                <td><span class="badge refunded">Refunded</span></td>
                                <td>
                                    <button class="btn-icon btn-outline">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn-icon btn-outline">
                                        <i class="fas fa-print"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="pagination">
                    <div class="pagination-info">
                        Page 1 of 63 • 1,245 total transactions
                    </div>
                    <div class="pagination-controls">
                        <button class="btn btn-outline btn-sm" disabled>
                            <i class="fas fa-chevron-left"></i> Previous
                        </button>
                        <button class="btn btn-primary btn-sm">1</button>
                        <button class="btn btn-outline btn-sm">2</button>
                        <button class="btn btn-outline btn-sm">3</button>
                        <span class="btn btn-outline btn-sm" style="border: none; background: transparent;">...</span>
                        <button class="btn btn-outline btn-sm">63</button>
                        <button class="btn btn-outline btn-sm">
                            Next <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal" id="details-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Transaction Details</h3>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <div style="text-align: center; padding: 2rem;">
                    <span class="loading"></span> Loading...
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        // Modal functionality
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('details-modal');
            const modalClose = modal.querySelector('.modal-close');
            
            // Open modal
            document.querySelectorAll('[title="View Details"]').forEach(btn => {
                btn.addEventListener('click', function() {
                    modal.style.display = 'flex';
                });
            });
            
            // Close modal
            modalClose.addEventListener('click', function() {
                modal.style.display = 'none';
            });
            
            // Close on outside click
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.style.display = 'none';
                }
            });
            
            // Remove filter tags
            document.querySelectorAll('.filter-tag .remove').forEach(btn => {
                btn.addEventListener('click', function() {
                    this.parentElement.remove();
                });
            });
        });
    </script>
</body>
</html>