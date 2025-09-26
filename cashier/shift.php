<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS - My Shift</title>
    <link rel="stylesheet" href="../assets/css/cashier.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="shift-container">
        <!-- Header Section -->
        <header class="shift-header">
            <button class="btn-back" onclick="window.history.back()">←</button>
            <h1>My Shift</h1>
            <div class="shift-status" id="shift-status">Active</div>
        </header>

        <!-- Shift Summary -->
        <div class="shift-summary">
            <div class="summary-row">
                <span>Shift Start:</span>
                <span id="shift-start">Today, 8:00 AM</span>
            </div>
            <div class="summary-row">
                <span>Duration:</span>
                <span id="shift-duration">4h 22m</span>
            </div>
            <div class="summary-row">
                <span>Transactions:</span>
                <span id="shift-transactions">24</span>
            </div>
            <div class="summary-row total">
                <span>Total Sales:</span>
                <span id="shift-sales">$1,245.60</span>
            </div>
        </div>

        <!-- Cash Drawer -->
        <div class="cash-drawer">
            <h2>Cash Drawer</h2>
            <div class="drawer-summary">
                <div class="drawer-amount">
                    <span>Current Amount:</span>
                    <span id="drawer-amount">$350.25</span>
                </div>
                <div class="drawer-buttons">
                    <button id="btn-add-cash" class="btn-drawer">Add Cash</button>
                    <button id="btn-remove-cash" class="btn-drawer">Remove Cash</button>
                </div>
            </div>
            
            <div class="cash-breakdown">
                <div class="denomination-row">
                    <span>$100 ×</span>
                    <input type="number" min="0" value="2" class="denomination-input">
                    <span class="denomination-total">$200.00</span>
                </div>
                <div class="denomination-row">
                    <span>$50 ×</span>
                    <input type="number" min="0" value="2" class="denomination-input">
                    <span class="denomination-total">$100.00</span>
                </div>
                <div class="denomination-row">
                    <span>$20 ×</span>
                    <input type="number" min="0" value="2" class="denomination-input">
                    <span class="denomination-total">$40.00</span>
                </div>
                <div class="denomination-row">
                    <span>$10 ×</span>
                    <input type="number" min="0" value="1" class="denomination-input">
                    <span class="denomination-total">$10.00</span>
                </div>
                <div class="denomination-row">
                    <span>$5 ×</span>
                    <input type="number" min="0" value="0" class="denomination-input">
                    <span class="denomination-total">$0.00</span>
                </div>
                <div class="denomination-row">
                    <span>$1 ×</span>
                    <input type="number" min="0" value="0" class="denomination-input">
                    <span class="denomination-total">$0.00</span>
                </div>
                <div class="denomination-row coins">
                    <span>Coins:</span>
                    <input type="number" min="0" step="0.01" value="0.25" class="denomination-input">
                </div>
            </div>
        </div>

        <!-- Shift Actions -->
        <div class="shift-actions">
            <button id="btn-start-break" class="btn-action">
                <i class="fas fa-coffee"></i> Start Break
            </button>
            <button id="btn-end-shift" class="btn-action primary">
                <i class="fas fa-sign-out-alt"></i> End Shift
            </button>
        </div>

        <!-- Recent Transactions -->
        <div class="shift-transactions">
            <h2>Recent Transactions</h2>
            <div class="transactions-list" id="shift-transactions-list">
                <div class="transaction-item">
                    <div class="transaction-time">12:05 PM</div>
                    <div class="transaction-type">Sale</div>
                    <div class="transaction-amount">$5.91</div>
                </div>
                <div class="transaction-item">
                    <div class="transaction-time">11:30 AM</div>
                    <div class="transaction-type">Sale</div>
                    <div class="transaction-amount">$12.45</div>
                </div>
                <div class="transaction-item">
                    <div class="transaction-time">10:15 AM</div>
                    <div class="transaction-type">Sale</div>
                    <div class="transaction-amount">$8.75</div>
                </div>
                <div class="transaction-item">
                    <div class="transaction-time">09:45 AM</div>
                    <div class="transaction-type">Sale</div>
                    <div class="transaction-amount">$15.20</div>
                </div>
                <div class="transaction-item">
                    <div class="transaction-time">09:15 AM</div>
                    <div class="transaction-type">Sale</div>
                    <div class="transaction-amount">$7.50</div>
                </div>
            </div>
        </div>

        <!-- Cash Adjustment Modal -->
        <div id="cash-modal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 id="cash-modal-title">Add Cash to Drawer</h3>
                    <span class="close-modal">&times;</span>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="cash-amount">Amount</label>
                        <input type="number" id="cash-amount" min="0" step="0.01" placeholder="0.00">
                    </div>
                    <div class="form-group">
                        <label for="cash-reason">Reason (Optional)</label>
                        <input type="text" id="cash-reason" placeholder="e.g. Change fund">
                    </div>
                </div>
                <div class="modal-actions">
                    <button class="btn-cancel">Cancel</button>
                    <button class="btn-confirm" id="btn-confirm-cash">Confirm</button>
                </div>
            </div>
        </div>

        <!-- End Shift Modal -->
        <div id="end-shift-modal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>End Shift</h3>
                    <span class="close-modal">&times;</span>
                </div>
                <div class="modal-body">
                    <div class="end-shift-summary">
                        <div class="summary-row">
                            <span>Shift Duration:</span>
                            <span id="modal-shift-duration">4h 22m</span>
                        </div>
                        <div class="summary-row">
                            <span>Total Transactions:</span>
                            <span id="modal-shift-transactions">24</span>
                        </div>
                        <div class="summary-row">
                            <span>Total Sales:</span>
                            <span id="modal-shift-sales">$1,245.60</span>
                        </div>
                        <div class="summary-row total">
                            <span>Cash in Drawer:</span>
                            <span id="modal-drawer-amount">$350.25</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="end-shift-notes">Notes (Optional)</label>
                        <textarea id="end-shift-notes" rows="3" placeholder="Any shift notes..."></textarea>
                    </div>
                </div>
                <div class="modal-actions">
                    <button class="btn-cancel">Cancel</button>
                    <button class="btn-confirm" id="btn-confirm-end">Confirm End Shift</button>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/utils.js"></script>
    <script src="../assets/js/cashier-shift.js"></script>
</body>
</html>