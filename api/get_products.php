<?php
// Include database connection
require_once '../db.php';

// Set header to return JSON
header('Content-Type: application/json');

try {
    $conn = getConnection();

    // Get tenant ID from request, default to 0 if not provided
    $tenant_id = isset($_GET['tenant_id']) ? intval($_GET['tenant_id']) : 0;
    if ($tenant_id <= 0) {
        throw new Exception('Invalid or missing tenant_id.');
    }

    // Get category filter if provided
    $category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : null;

    // Base query - Added tax_rate
    $sql = "SELECT 
                p.product_id as id, 
                p.name, 
                p.price, 
                p.stock, 
                p.barcode, 
                p.tax_rate, 
                c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.category_id 
            WHERE p.status = 'active' AND p.tenant_id = ?";
    $params = [$tenant_id];
    $types = "i";

    // Add category filter if provided
    if ($category_id) {
        $sql .= " AND p.category_id = ?";
        $params[] = $category_id;
        $types .= "i";
    }

    $sql .= " ORDER BY p.name ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }

    echo json_encode([
        'success' => true,
        'products' => $products
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
