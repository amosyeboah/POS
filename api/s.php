<?php
session_start();
require_once '../db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

try {
    $conn = getConnection();
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data) {
        throw new Exception('Invalid input data');
    }

    $conn->begin_transaction();

    // Get user_id and tenant_id from session
    $user_id = $_SESSION['user_id'];
    $tenant_id = $_SESSION['tenant_id'];

    $transaction_code = 'TXN' . date('Ymd') . rand(1000, 9999);

    // Insert into sales table with tenant_id
    $stmt = $conn->prepare("INSERT INTO sales (user_id, tenant_id, transaction_code, subtotal, tax_amount, total, payment_method, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'completed')");
    $stmt->bind_param("iisddds", $user_id, $tenant_id, $transaction_code, $data['subtotal'], $data['tax'], $data['total'], $data['payment_method']);
    $stmt->execute();

    $sale_id = $conn->insert_id;

    $stmt = $conn->prepare("INSERT INTO sale_items (sale_id, tenant_id, product_id, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($data['items'] as $item) {
        $total_price = $item['price'] * $item['quantity'];
        $stmt->bind_param("iiiidd", $sale_id, $tenant_id, $item['id'], $item['quantity'], $item['price'], $total_price);
        $stmt->execute();

        $update_stmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE product_id = ?");
        $update_stmt->bind_param("ii", $item['quantity'], $item['id']);
        $update_stmt->execute();
    }

    $conn->commit();

    echo json_encode([
        'success' => true,
        'order_id' => $sale_id,
        'transaction_code' => $transaction_code,
        'message' => 'Order saved successfully'
    ]);

} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollback();
    }
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}