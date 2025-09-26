<?php
// Include database connection
require_once '../db.php';

// Set header to return JSON
header('Content-Type: application/json');

try {
    $conn = getConnection();

    // Get tenant_id from GET or session
    $tenant_id = isset($_GET['tenant_id']) ? intval($_GET['tenant_id']) : 0;
    if ($tenant_id <= 0) {
        throw new Exception('Invalid or missing tenant_id.');
    }

    // Query to get categories for this tenant
    $sql = "SELECT category_id as id, name FROM categories WHERE tenant_id = ? ORDER BY name ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $tenant_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $categories = [];
    while($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }

    echo json_encode([
        'success' => true,
        'categories' => $categories
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}