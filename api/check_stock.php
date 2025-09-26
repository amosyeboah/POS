<?php
session_start();
require_once '../db.php';
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        throw new Exception('Invalid request method');
    }

    // Ensure tenant in session
    if (!isset($_SESSION['tenant_id'])) {
        throw new Exception('Unauthorized: Tenant not found');
    }
    $tenant_id = (int)$_SESSION['tenant_id'];

    // Validate product_id
    if (!isset($_GET['product_id'])) {
        throw new Exception('Missing product_id');
    }
    $product_id = (int)$_GET['product_id'];
    if ($product_id <= 0) {
        throw new Exception('Invalid product_id');
    }

    $conn = getConnection();

    $stmt = $conn->prepare("SELECT stock FROM products WHERE product_id = ? AND tenant_id = ? LIMIT 1");
    $stmt->bind_param('ii', $product_id, $tenant_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if (!$row) {
        throw new Exception('Product not found');
    }

    echo json_encode([
        'success' => true,
        'stock' => (int)$row['stock']
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} finally {
    if (isset($conn)) $conn->close();
}
