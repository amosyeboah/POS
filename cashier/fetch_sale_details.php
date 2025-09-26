<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ... rest of your code
session_start();
require_once '../db.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if a sale ID is provided and the user is authenticated
if (!isset($_GET['sale_id']) || !isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Invalid request.']);
    exit;
}

$conn = getConnection();
$sale_id = $_GET['sale_id'];
$tenant_id = $_SESSION['tenant_id'];

// SQL to fetch sale items, including product name and tax rate
$sql = "SELECT si.quantity, si.unit_price, si.tax_rate, p.name AS product_name
        FROM sale_items si
        JOIN products p ON si.product_id = p.product_id
        JOIN sales s ON si.sale_id = s.sale_id
        WHERE si.sale_id = ? AND s.tenant_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $sale_id, $tenant_id);

if (!$stmt->execute()) {
    echo json_encode(['error' => 'Database query failed: ' . $stmt->error]);
    $stmt->close();
    $conn->close();
    exit;
}

$result = $stmt->get_result();

$items = [];
while ($row = $result->fetch_assoc()) {
    $items[] = $row;
}

$stmt->close();
$conn->close();

echo json_encode($items);
?>