<?php
session_start();
require_once '../../db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['tenant_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}
$tenant_id = $_SESSION['tenant_id'];
$conn = getConnection();

$action = $_POST['action'] ?? '';

try {
    if ($action === 'add') {
        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            echo json_encode(['success' => false, 'message' => 'Category name cannot be empty.']);
            exit;
        }
        $description = trim($_POST['description'] ?? '');
        $parent_id = !empty($_POST['parent_id']) ? intval($_POST['parent_id']) : null;
        $status = $_POST['status'] ?? 'active';

        $stmt = $conn->prepare("INSERT INTO categories (name, description, parent_id, status, tenant_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssisi", $name, $description, $parent_id, $status, $tenant_id);
        $stmt->execute();
        echo json_encode(['success' => true, 'message' => 'Category added']);
    } elseif ($action === 'edit') {
        $category_id = intval($_POST['category_id']);
        $name = trim($_POST['name']);
        $description = trim($_POST['description'] ?? '');
        $parent_id = !empty($_POST['parent_id']) ? intval($_POST['parent_id']) : null;
        $status = $_POST['status'] ?? 'active';

        $stmt = $conn->prepare("UPDATE categories SET name=?, description=?, parent_id=?, status=? WHERE category_id=? AND tenant_id=?");
        $stmt->bind_param("ssisii", $name, $description, $parent_id, $status, $category_id, $tenant_id);
        $stmt->execute();
        echo json_encode(['success' => true, 'message' => 'Category updated']);
    } elseif ($action === 'delete') {
        $category_id = intval($_POST['category_id']);
        $stmt = $conn->prepare("DELETE FROM categories WHERE category_id=? AND tenant_id=?");
        $stmt->bind_param("ii", $category_id, $tenant_id);
        $stmt->execute();
        echo json_encode(['success' => true, 'message' => 'Category deleted']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    $conn->close();
}