<?php
session_start();
require_once '../db.php';
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        throw new Exception('Invalid request method');
    }

    if (!isset($_SESSION['tenant_id'])) {
        throw new Exception('Unauthorized: Tenant not found');
    }

    $tenant_id = $_SESSION['tenant_id'];
    $query = isset($_GET['q']) ? trim($_GET['q']) : '';

    $conn = getConnection();

    $sql = "SELECT customer_id, name, phone, email 
            FROM customers 
            WHERE tenant_id = ? 
              AND (name LIKE ? OR phone LIKE ? OR email LIKE ?)
            ORDER BY name ASC
            LIMIT 20";
    $stmt = $conn->prepare($sql);

    $like = "%" . $query . "%";
    $stmt->bind_param("isss", $tenant_id, $like, $like, $like);
    $stmt->execute();
    $result = $stmt->get_result();

    $customers = [];
    while ($row = $result->fetch_assoc()) {
        $customers[] = $row;
    }

    echo json_encode(['success' => true, 'customers' => $customers]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} finally {
    if (isset($conn)) $conn->close();
}
