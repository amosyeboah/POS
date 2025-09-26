<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS - Returns</title>
    <link rel="stylesheet" href="../assets/css/cashier-returns.css">
    <!-- <link rel="stylesheet" href="../assets/css/cashier.css"> -->

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="pos-container">
        <!-- Header Section -->
        <header class="pos-header">
            <button class="btn-back" onclick="window.history.back()">←</button>
            <h1>Process Return</h1>
            <button class="btn-help">?</button>
        </header>

        <!-- Return Search Options -->
        <div class="return-options">
            <div class="option-tabs">
                <button class="option-tab active" data-tab="receipt">By Receipt</button>
                <button class="option-tab" data-tab="transaction">By Transaction #</button>
                <button class="option-tab" data-tab="manual">Manual Entry</button>
            </div>
            
            <div class="option-content active" id="receipt-tab">
                <div class="search-container">
                    <input type="text" id="receipt-search" placeholder="Enter receipt number...">
                    <button id="scan-receipt" class="btn-scan">📷</button>
                </div>
            </div>
            
            <div class="option-content" id="transaction-tab">
                <div class="search-container">
                    <input type="text" id="transaction-search" placeholder="Enter transaction #...">
                </div>
            </div>
            
            <div class="option-content" id="manual-tab">
                <div class="search-container">
                    <input type="text" id="product-search" placeholder="Search product to return...">
                    <button id="barcode-scan" class="btn-scan">📷</button>
                </div>
            </div>
        </div>

        <!-- Original Transaction Details -->
        <div class="transaction-details" id="transaction-details">
            <div class="details-header">
                <h3>Transaction #<span id="original-transaction">10045</span></h3>
                <span class="transaction-date">Today, 12:05 PM</span>
            </div>
            <div class="transaction-items" id="original-items">
                <!-- Items will be loaded here -->
                <div class="transaction-item">
                    <div class="item-info">
                        <span class="item-name">Soda</span>
                        <span class="item-price">$1.50</span>
                    </div>
                    <div class="item-actions">
                        <button class="btn-qty minus">-</button>
                        <span class="item-qty">1</span>
                        <button class="btn-qty plus">+</button>
                    </div>
                </div>
                <div class="transaction-item">
                    <div class="item-info">
                        <span class="item-name">Chips</span>
                        <span class="item-price">$2.00</span>
                    </div>
                    <div class="item-actions">
                        <button class="btn-qty minus">-</button>
                        <span class="item-qty">2</span>
                        <button class="btn-qty plus">+</button>
                    </div>
                </div>
            </div>
            <div class="transaction-totals">
                <div class="total-row">
                    <span>Original Total:</span>
                    <span id="original-total">$5.50</span>
                </div>
                <div class="total-row">
                    <span>Tax:</span>
                    <span id="original-tax">$0.41</span>
                </div>
            </div>
        </div>

        <!-- Return Summary -->
        <div class="return-summary">
            <div class="summary-header">
                <h3>Return Summary</h3>
            </div>
            <div class="return-items" id="return-items">
                <!-- Return items will be added here -->
                <div class="empty-return">No items selected for return</div>
            </div>
            <div class="return-totals">
                <div class="total-row">
                    <span>Subtotal:</span>
                    <span id="return-subtotal">$0.00</span>
                </div>
                <div class="total-row">
                    <span>Tax:</span>
                    <span id="return-tax">$0.00</span>
                </div>
                <div class="total-row grand-total">
                    <span>Refund Total:</span>
                    <span id="return-total">$0.00</span>
                </div>
            </div>
        </div>

        <!-- Return Reason -->
        <div class="return-reason">
            <select id="return-reason">
                <option value="">Select return reason...</option>
                <option value="defective">Defective/Damaged</option>
                <option value="wrong-item">Wrong Item Received</option>
                <option value="no-longer-needed">No Longer Needed</option>
                <option value="other">Other</option>
            </select>
            <textarea id="return-notes" placeholder="Additional notes..." rows="2"></textarea>
        </div>

        <!-- Action Buttons -->
        <div class="return-actions">
            <button id="btn-cancel" class="btn-cancel">Cancel</button>
            <button id="btn-process" class="btn-process" disabled>Process Refund</button>
        </div>
    </div>

    <!-- Product Selection Modal (for manual returns) -->
    <div id="product-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Select Product</h3>
                <span class="close-modal">&times;</span>
            </div>
            <div class="modal-body" id="product-list">
                <!-- Products will be loaded here -->
            </div>
        </div>
    </div>

    <script src="../assets/js/utils.js"></script>
    <script src="../assets/js/cashier-returns.js"></script>
</body>
</html>