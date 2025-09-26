document.addEventListener('DOMContentLoaded', function() {
    // DOM Elements
    const btnAddCash = document.getElementById('btn-add-cash');
    const btnRemoveCash = document.getElementById('btn-remove-cash');
    const btnStartBreak = document.getElementById('btn-start-break');
    const btnEndShift = document.getElementById('btn-end-shift');
    const cashModal = document.getElementById('cash-modal');
    const endShiftModal = document.getElementById('end-shift-modal');
    const denominationInputs = document.querySelectorAll('.denomination-input');
    
    // Shift state
    let shiftState = {
        status: 'active', // active/break/ended
        startTime: new Date('2023-05-15T08:00:00'),
        breakStart: null,
        totalBreakTime: 0,
        cashDrawer: 350.25,
        transactions: 24,
        totalSales: 1245.60
    };
    
    // Initialize the shift interface
    initShift();
    
    function initShift() {
        // Update shift timer
        updateShiftTimer();
        setInterval(updateShiftTimer, 60000);
        
        // Cash denomination calculations
        denominationInputs.forEach(input => {
            input.addEventListener('input', calculateDrawerTotal);
        });
        
        // Event listeners
        btnAddCash.addEventListener('click', () => showCashModal('add'));
        btnRemoveCash.addEventListener('click', () => showCashModal('remove'));
        btnStartBreak.addEventListener('click', toggleBreak);
        btnEndShift.addEventListener('click', showEndShiftModal);
        
        // Modal close buttons
        document.querySelectorAll('.close-modal').forEach(btn => {
            btn.addEventListener('click', () => {
                cashModal.style.display = 'none';
                endShiftModal.style.display = 'none';
            });
        });
        
        // Cancel buttons
        document.querySelectorAll('.btn-cancel').forEach(btn => {
            btn.addEventListener('click', () => {
                cashModal.style.display = 'none';
                endShiftModal.style.display = 'none';
            });
        });
        
        // Confirm cash adjustment
        document.getElementById('btn-confirm-cash').addEventListener('click', processCashAdjustment);
        
        // Confirm end shift
        document.getElementById('btn-confirm-end').addEventListener('click', endShift);
    }
    
    function updateShiftTimer() {
        const now = new Date();
        let durationMs = now - shiftState.startTime - shiftState.totalBreakTime;
        
        // Subtract current break time if on break
        if (shiftState.breakStart) {
            durationMs -= (now - shiftState.breakStart);
        }
        
        const durationHours = Math.floor(durationMs / (1000 * 60 * 60));
        const durationMinutes = Math.floor((durationMs % (1000 * 60 * 60)) / (1000 * 60));
        
        document.getElementById('shift-duration').textContent = 
            `${durationHours}h ${durationMinutes}m`;
        
        // Update modal duration as well
        document.getElementById('modal-shift-duration').textContent = 
            `${durationHours}h ${durationMinutes}m`;
    }
    
    function calculateDrawerTotal() {
        let total = 0;
        
        document.querySelectorAll('.denomination-row:not(.coins)').forEach(row => {
            const denomination = parseFloat(row.querySelector('span').textContent.replace('$', ''));
            const qty = parseInt(row.querySelector('input').value) || 0;
            const rowTotal = denomination * qty;
            
            row.querySelector('.denomination-total').textContent = `$${rowTotal.toFixed(2)}`;
            total += rowTotal;
        });
        
        // Add coins
        const coins = parseFloat(document.querySelector('.denomination-row.coins input').value) || 0;
        total += coins;
        
        // Update drawer total
        shiftState.cashDrawer = total;
        document.getElementById('drawer-amount').textContent = `$${total.toFixed(2)}`;
        document.getElementById('modal-drawer-amount').textContent = `$${total.toFixed(2)}`;
    }
    
    function showCashModal(action) {
        const modalTitle = document.getElementById('cash-modal-title');
        modalTitle.textContent = action === 'add' 
            ? 'Add Cash to Drawer' 
            : 'Remove Cash from Drawer';
        
        document.getElementById('cash-amount').value = '';
        document.getElementById('cash-reason').value = '';
        cashModal.style.display = 'block';
    }
    
    function processCashAdjustment() {
        const amount = parseFloat(document.getElementById('cash-amount').value);
        const reason = document.getElementById('cash-reason').value;
        const action = document.getElementById('cash-modal-title').textContent.includes('Add') ? 'add' : 'remove';
    
        if (isNaN(amount)) { 
            showToast('Please enter a valid amount', 'error');
            return;
        }
    
        // In a real app, this would be an API call
        if (action === 'add') {
            shiftState.cashDrawer += amount;
        } else {
            if (amount > shiftState.cashDrawer) {
                showToast('Cannot remove more cash than is in drawer', 'error');
                return;
            }
            shiftState.cashDrawer -= amount;
        }
    
        // Update UI
        document.getElementById('drawer-amount').textContent = `$${shiftState.cashDrawer.toFixed(2)}`;
        document.getElementById('modal-drawer-amount').textContent = `$${shiftState.cashDrawer.toFixed(2)}`;
    
        showToast(
            `${action === 'add' ? 'Added' : 'Removed'} $${amount.toFixed(2)} ${reason ? `(${reason})` : ''}`,
            'success'
        );
    
        cashModal.style.display = 'none';
    }
    
    function toggleBreak() {
        const btnBreak = document.getElementById('btn-start-break');
        const shiftStatus = document.getElementById('shift-status');
        
        if (shiftState.status === 'active') {
            // Start break
            shiftState.status = 'break';
            shiftState.breakStart = new Date();
            btnBreak.innerHTML = '<i class="fas fa-running"></i> End Break';
            shiftStatus.textContent = 'On Break';
            shiftStatus.classList.remove('active');
            shiftStatus.classList.add('break');
        } else {
            // End break
            shiftState.status = 'active';
            shiftState.totalBreakTime += (new Date() - shiftState.breakStart);
            shiftState.breakStart = null;
            btnBreak.innerHTML = '<i class="fas fa-coffee"></i> Start Break';
            shiftStatus.textContent = 'Active';
            shiftStatus.classList.remove('break');
            shiftStatus.classList.add('active');
        }
    }
    
    function showEndShiftModal() {
        if (shiftState.status === 'break') {
            showToast('Please end your break before ending shift', 'error');
            return;
        }
        
        // Update modal with current data
        document.getElementById('modal-shift-transactions').textContent = shiftState.transactions;
        document.getElementById('modal-shift-sales').textContent = `$${shiftState.totalSales.toFixed(2)}`;
        document.getElementById('end-shift-notes').value = '';
        
        endShiftModal.style.display = 'block';
    }
    
    function endShift() {
        const notes = document.getElementById('end-shift-notes').value;
        
        // In a real app, this would be an API call
        shiftState.status = 'ended';
        
        // Update UI
        document.getElementById('shift-status').textContent = 'Ended';
        document.getElementById('shift-status').classList.remove('active');
        document.getElementById('shift-status').classList.add('ended');
        document.getElementById('btn-start-break').disabled = true;
        document.getElementById('btn-end-shift').disabled = true;
        
        showToast('Shift ended successfully', 'success');
        endShiftModal.style.display = 'none';
        
        // In a real app, would redirect to dashboard or print summary
    }
    
    // Sample transactions for the shift
    function loadShiftTransactions() {
        // In a real app, this would be an API call filtered by shift
        const transactions = [
            { time: '12:05 PM', type: 'Sale', amount: 5.91 },
            { time: '11:30 AM', type: 'Sale', amount: 12.45 },
            { time: '10:15 AM', type: 'Sale', amount: 8.75 },
            { time: '09:45 AM', type: 'Sale', amount: 15.20 },
            { time: '09:15 AM', type: 'Sale', amount: 7.50 }
        ];
        
        const container = document.getElementById('shift-transactions-list');
        container.innerHTML = '';
        
        transactions.forEach(tx => {
            const txEl = document.createElement('div');
            txEl.className = 'transaction-item';
            txEl.innerHTML = `
                <div class="transaction-time">${tx.time}</div>
                <div class="transaction-type">${tx.type}</div>
                <div class="transaction-amount">$${tx.amount.toFixed(2)}</div>
            `;
            container.appendChild(txEl);
        });
    }
    
    // Initialize data
    loadShiftTransactions();
    calculateDrawerTotal();
});