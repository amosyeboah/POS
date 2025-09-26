<?php
// filepath: c:\xampp\htdocs\mobile-pos\admin\get_sale_details.php
session_start();
require_once '../db.php';

header('Content-Type: text/html; charset=UTF-8');

// Ensure user is logged in and tenant_id is set
if (!isset($_SESSION['user_id']) || !isset($_SESSION['tenant_id'])) {
    http_response_code(401);
    echo '<div style="color:#b91c1c;">Unauthorized. Please log in.</div>';
    exit;
}
$tenant_id = (int)$_SESSION['tenant_id'];

$sale_id = isset($_GET['sale_id']) ? (int)$_GET['sale_id'] : 0;
if ($sale_id <= 0) {
    http_response_code(400);
    echo '<div style="color:#b91c1c;">Invalid sale ID.</div>';
    exit;
}

function h($str) { return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8'); }
function getCurrencySymbol($currencyType) {
    switch (strtoupper($currencyType)) {
        case 'GHS': return '₵';
        case 'USD': return '$';
        case 'EUR': return '€';
        case 'GBP': return '£';
        case 'NGN': return '₦';
        default: return $currencyType;
    }
}

$conn = getConnection();

// Fetch tenant currency
$currency_type = 'GHS';
$cur_stmt = $conn->prepare('SELECT currency_type FROM tenants WHERE tenant_id = ?');
if ($cur_stmt) {
    $cur_stmt->bind_param('i', $tenant_id);
    $cur_stmt->execute();
    $cur_stmt->bind_result($currency_type);
    $cur_stmt->fetch();
    $cur_stmt->close();
}
$currency_symbol = getCurrencySymbol($currency_type);

// Fetch sale header (scoped by tenant)
$header_sql = "
SELECT s.sale_id,
       s.transaction_code,
       s.created_at,
       s.subtotal,
       s.tax_amount,
       s.discount_amount,
       s.total,
       s.payment_method,
       s.payment_details,
       s.status,
       s.notes,
       u.full_name AS cashier,
       c.name AS customer_name,
       c.phone AS customer_phone,
       c.email AS customer_email
FROM sales s
JOIN users u ON s.user_id = u.user_id
LEFT JOIN customers c ON s.customer_id = c.customer_id
WHERE s.sale_id = ? AND s.tenant_id = ?
LIMIT 1
";

$header = null;
$header_stmt = $conn->prepare($header_sql);
if (!$header_stmt) {
    http_response_code(500);
    echo '<div style="color:#b91c1c;">Failed to prepare sale details.</div>';
    $conn->close();
    exit;
}
$header_stmt->bind_param('ii', $sale_id, $tenant_id);
$header_stmt->execute();
$header_res = $header_stmt->get_result();
if ($header_res && $header_res->num_rows > 0) {
    $header = $header_res->fetch_assoc();
} else {
    http_response_code(404);
    echo '<div style="color:#b91c1c;">Sale not found.</div>';
    $header_stmt->close();
    $conn->close();
    exit;
}
$header_stmt->close();

// Fetch sale items (ensure tenant ownership via join to sales)
$items_sql = "
SELECT si.sale_item_id,
       si.quantity,
       si.unit_price,
       si.discount,
       si.tax_rate,
       si.total_price,
       p.name AS product_name,
       p.barcode
FROM sale_items si
JOIN products p ON si.product_id = p.product_id
JOIN sales s ON si.sale_id = s.sale_id
WHERE si.sale_id = ? AND s.tenant_id = ?
ORDER BY si.sale_item_id ASC
";

$items = [];
$items_stmt = $conn->prepare($items_sql);
if ($items_stmt) {
    $items_stmt->bind_param('ii', $sale_id, $tenant_id);
    $items_stmt->execute();
    $res = $items_stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $items[] = $row;
    }
    $items_stmt->close();
}

$conn->close();

// Render HTML for modal body
?>
<style>
    .details-toolbar { display:flex; justify-content:flex-end; gap:0.5rem; margin-bottom:0.75rem; position:sticky; top:0; background:#fff; padding-top:0.25rem; z-index:3; }
    .btn-mini { border:1px solid #e5e7eb; background:#fff; color:#334155; padding:0.35rem 0.6rem; border-radius:8px; font-size:0.85rem; cursor:pointer; }
    .btn-mini:hover { background:#f3f4f6; }
    .details-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
    .details-section { background:#f8fafc; border-radius:10px; padding:1rem; }
    .details-title { font-weight:600; margin-bottom:0.5rem; color:#334155; }
    .kv { display:flex; justify-content:space-between; margin:0.25rem 0; font-size:0.95rem; }
    .kv .k { color:#64748b; }
    .kv .v { color:#111827; font-weight:500; }
    .items-table { width:100%; border-collapse:collapse; margin-top:1rem; }
    .items-table thead th { position:sticky; top:0; background:#fff; box-shadow:0 1px 0 #e5e7eb; }
    .items-table th, .items-table td { padding:0.6rem; border-bottom:1px solid #e5e7eb; text-align:left; }
    .items-table th { color:#6b7280; font-size:0.85rem; text-transform:uppercase; letter-spacing:0.03em; }
    .items-table tbody tr:nth-child(even) { background:#fafafa; }
    .summary { margin-top:1rem; display:grid; gap:0.25rem; max-width:360px; margin-left:auto; }
    .summary .row { display:flex; justify-content:space-between; }
    .summary .total { font-weight:800; font-size:1.15rem; color:#111827; }
    .badge { display:inline-block; padding:0.2rem 0.5rem; border-radius:999px; font-size:0.8rem; }
    .badge.completed { background:rgba(16,183,89,0.1); color:#10b759; }
    .badge.refunded { background:rgba(244,67,54,0.1); color:#f44336; }
    .badge.cancelled { background:rgba(247,144,9,0.1); color:#f79009; }

    @media (max-width: 768px) {
        .details-grid { grid-template-columns: 1fr; }
        .kv { flex-direction: column; gap:0.15rem; }
        .kv .k { font-size:0.85rem; }
    }
</style>

<div class="details-toolbar">
    <button id="btn-copy-code" class="btn-mini" title="Copy Transaction Code" data-code="#<?= h($header['transaction_code']) ?>">Copy Code</button>
    <button id="btn-print" class="btn-mini" title="Print">Print</button>
</div>

<div class="details-grid">
    <div class="details-section">
        <div class="details-title">Transaction</div>
        <div class="kv"><span class="k">Code</span><span class="v">#<?= h($header['transaction_code']) ?></span></div>
        <div class="kv"><span class="k">Date/Time</span><span class="v"><?php echo h(date('Y-m-d h:i A', strtotime($header['created_at']))); ?></span></div>
        <div class="kv"><span class="k">Cashier</span><span class="v"><?= h($header['cashier']) ?></span></div>
        <div class="kv"><span class="k">Payment</span><span class="v"><?= h(ucfirst($header['payment_method'])) ?></span></div>
        <?php if (!empty($header['status'])): ?>
            <div class="kv"><span class="k">Status</span><span class="v"><span class="badge <?= h($header['status']) ?>"><?= h(ucfirst($header['status'])) ?></span></span></div>
        <?php endif; ?>
        <?php if (!empty($header['notes'])): ?>
            <div class="kv"><span class="k">Notes</span><span class="v"><?= h($header['notes']) ?></span></div>
        <?php endif; ?>
    </div>
    <div class="details-section">
        <div class="details-title">Customer</div>
        <div class="kv"><span class="k">Name</span><span class="v"><?= h($header['customer_name'] ?: 'Walk-in') ?></span></div>
        <?php if (!empty($header['customer_phone'])): ?>
            <div class="kv"><span class="k">Phone</span><span class="v"><?= h($header['customer_phone']) ?></span></div>
        <?php endif; ?>
        <?php if (!empty($header['customer_email'])): ?>
            <div class="kv"><span class="k">Email</span><span class="v"><?= h($header['customer_email']) ?></span></div>
        <?php endif; ?>
    </div>
</div>

<table class="items-table">
    <thead>
        <tr>
            <th>Item</th>
            <th>Barcode</th>
            <th>Qty</th>
            <th>Unit Price</th>
            <th>Discount</th>
            <th>Tax %</th>
            <th>Line Total</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($items) > 0): ?>
            <?php foreach ($items as $it): ?>
                <tr>
                    <td><?= h($it['product_name']) ?></td>
                    <td><?= h($it['barcode']) ?></td>
                    <td><?= h($it['quantity']) ?></td>
                    <td><?= $currency_symbol . number_format((float)$it['unit_price'], 2) ?></td>
                    <td><?= $currency_symbol . number_format((float)$it['discount'], 2) ?></td>
                    <td><?= number_format((float)$it['tax_rate'], 2) ?>%</td>
                    <td><?= $currency_symbol . number_format((float)$it['total_price'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="7" style="text-align:center; color:#6b7280; padding:1rem;">No items found for this sale.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<div class="summary">
    <div class="row"><span>Subtotal</span><span><?= $currency_symbol . number_format((float)$header['subtotal'], 2) ?></span></div>
    <div class="row"><span>Tax</span><span><?= $currency_symbol . number_format((float)$header['tax_amount'], 2) ?></span></div>
    <div class="row"><span>Discount</span><span>-<?= $currency_symbol . number_format((float)$header['discount_amount'], 2) ?></span></div>
    <div class="row total"><span>Total</span><span><?= $currency_symbol . number_format((float)$header['total'], 2) ?></span></div>
</div>