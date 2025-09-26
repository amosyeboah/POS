<?php
session_start();
require_once '../db.php';
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    if (!isset($_SESSION['tenant_id'])) {
        throw new Exception('Unauthorized: Tenant not found');
    }

    $tenant_id = $_SESSION['tenant_id'];
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data || !isset($data['name']) || trim($data['name']) === '') {
        throw new Exception('Customer name is required');
    }

    $name = trim($data['name']);
    $phone = isset($data['phone']) ? trim($data['phone']) : null;
    $email = isset($data['email']) ? trim($data['email']) : null;
    $address = isset($data['address']) ? trim($data['address']) : null;

    $conn = getConnection();

    $stmt = $conn->prepare("INSERT INTO customers (tenant_id, name, phone, email, address) 
                            VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $tenant_id, $name, $phone, $email, $address);

    if (!$stmt->execute()) {
        throw new Exception("Error adding customer: " . $stmt->error);
    }

    $customer_id = $conn->insert_id;

    echo json_encode([
        'success' => true,
        'message' => 'Customer added successfully',
        'customer' => [
            'customer_id' => $customer_id,
            'name' => $name,
            'phone' => $phone,
            'email' => $email,
            'address' => $address
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} finally {
    if (isset($conn)) $conn->close();
}
