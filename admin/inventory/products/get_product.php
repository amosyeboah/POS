<?php
require_once '../../db.php';
$conn = getConnection();

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'No product ID']);
    exit;
}

$id = (int)$_GET['id'];
$stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

echo json_encode($product);
