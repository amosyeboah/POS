<?php
session_start();
require_once '../db.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not authenticated.']);
    exit;
}

$conn = getConnection();
$tenant_id = $_SESSION['tenant_id'];

// Get and sanitize filter values
$date_range = $_GET['range'] ?? 'today';
$payment_method = $_GET['payment'] ?? 'all';
$min_amount = floatval($_GET['min_amount'] ?? 0);
$limit = intval($_GET['limit'] ?? 10);
$offset = intval($_GET['offset'] ?? 0);
$start_date = $_GET['start_date'] ?? null;
$end_date = $_GET['end_date'] ?? null;

// ====================================================================
// Build the Main Transactions Query
// ====================================================================
$sql = "SELECT s.sale_id, s.transaction_code, s.total, s.payment_method, s.created_at,
               SUM(si.subtotal) as subtotal, SUM(si.tax_amount) as tax_amount
        FROM sales s
        LEFT JOIN (
            SELECT sale_id, 
                   (quantity * unit_price) AS subtotal,
                   (quantity * unit_price * tax_rate / 100) AS tax_amount
            FROM sale_items
        ) si ON s.sale_id = si.sale_id
        WHERE s.tenant_id = ? AND s.status = 'completed'";

$params = [$tenant_id];
$types = "i";

// Add date range filter
switch ($date_range) {
    case 'today':
        $sql .= " AND DATE(s.created_at) = CURDATE()";
        break;
    case 'yesterday':
        $sql .= " AND DATE(s.created_at) = CURDATE() - INTERVAL 1 DAY";
        break;
    case 'week':
        $sql .= " AND YEARWEEK(s.created_at, 1) = YEARWEEK(CURDATE(), 1)";
        break;
    case 'month':
        $sql .= " AND MONTH(s.created_at) = MONTH(CURDATE()) AND YEAR(s.created_at) = YEAR(CURDATE())";
        break;
    case 'custom':
        if ($start_date && $end_date) {
            $sql .= " AND s.created_at BETWEEN ? AND ?";
            $params[] = $start_date . ' 00:00:00';
            $params[] = $end_date . ' 23:59:59';
            $types .= "ss";
        }
        break;
}

// Add other filters
if ($payment_method !== 'all') {
    $sql .= " AND s.payment_method = ?";
    $params[] = $payment_method;
    $types .= "s";
}
if ($min_amount > 0) {
    $sql .= " AND s.total >= ?";
    $params[] = $min_amount;
    $types .= "d";
}

$sql .= " GROUP BY s.sale_id ORDER BY s.created_at DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= "ii";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$transactions = [];
while ($row = $result->fetch_assoc()) {
    $transactions[] = $row;
}
$stmt->close();

// ====================================================================
// Build the Summary Query (to get total sales and transactions count)
// ====================================================================
$sql_summary = "SELECT COUNT(*) AS total_transactions, COALESCE(SUM(total), 0) AS total_sales 
                FROM sales 
                WHERE tenant_id = ? AND status = 'completed'";

$summary_params = [$tenant_id];
$summary_types = "i";

// Apply the same date filters as the main query
switch ($date_range) {
    case 'today':
        $sql_summary .= " AND DATE(created_at) = CURDATE()";
        break;
    case 'yesterday':
        $sql_summary .= " AND DATE(created_at) = CURDATE() - INTERVAL 1 DAY";
        break;
    case 'week':
        $sql_summary .= " AND YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)";
        break;
    case 'month':
        $sql_summary .= " AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())";
        break;
    case 'custom':
        if ($start_date && $end_date) {
            $sql_summary .= " AND created_at BETWEEN ? AND ?";
            $summary_params[] = $start_date . ' 00:00:00';
            $summary_params[] = $end_date . ' 23:59:59';
            $summary_types .= "ss";
        }
        break;
}

// Apply other filters
if ($payment_method !== 'all') {
    $sql_summary .= " AND payment_method = ?";
    $summary_params[] = $payment_method;
    $summary_types .= "s";
}
if ($min_amount > 0) {
    $sql_summary .= " AND total >= ?";
    $summary_params[] = $min_amount;
    $summary_types .= "d";
}

$stmt_summary = $conn->prepare($sql_summary);
$stmt_summary->bind_param($summary_types, ...$summary_params);
$stmt_summary->execute();
$summary_result = $stmt_summary->get_result()->fetch_assoc();
$stmt_summary->close();

$conn->close();

echo json_encode(['transactions' => $transactions, 'summary' => $summary_result]);