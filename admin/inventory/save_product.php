<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['tenant_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}
require_once '../../db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

try {
    $conn = getConnection();
    
    // Handle file upload
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../../uploads/products/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (!in_array($file_extension, $allowed_extensions)) {
            throw new Exception('Invalid file type. Allowed types: ' . implode(', ', $allowed_extensions));
        }
        
        $file_name = uniqid() . '.' . $file_extension;
        $target_path = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
            $image_path = 'uploads/products/' . $file_name;
        } else {
            throw new Exception('Failed to upload image');
        }
    }
    
    // Prepare data
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : null;
    $name = $_POST['name'];
    $barcode = $_POST['barcode'];
    $category_id = intval($_POST['category_id']);
    $price = floatval($_POST['price']);
    $cost = isset($_POST['cost']) ? floatval($_POST['cost']) : null;
    $tax_rate = isset($_POST['tax_rate']) ? floatval($_POST['tax_rate']) : 0;
    $stock = intval($_POST['stock']);
    $min_stock = intval($_POST['min_stock']);
    $description = $_POST['description'] ?? null;
    $status = $_POST['status'] ?? 'active';

        // Check if the tenant_id exists in the session
    if (!isset($_SESSION['tenant_id'])) {
        throw new Exception('Tenant ID is not set in the session.');
    }
    
    // Get the tenant_id from the session
    $tenant_id = $_SESSION['tenant_id'];
    
    if ($product_id) {
        // Update existing product
        $sql = "UPDATE products SET 
                name = ?, barcode = ?, category_id = ?, price = ?, cost = ?, 
                tax_rate = ?, stock = ?, min_stock = ?, description = ?, status = ?";
        
        $params = [$name, $barcode, $category_id, $price, $cost, $tax_rate, 
                  $stock, $min_stock, $description, $status];
        $types = "ssidddiiss";
        
        if ($image_path) {
            $sql .= ", image_path = ?";
            $params[] = $image_path;
            $types .= "s";
        }
        
        $sql .= " WHERE product_id = ? AND tenant_id = ?";
        $params[] = $product_id;
        $params[] = $tenant_id;
        $types .= "ii";
        
    } else {
    // Insert new product
    $sql = "INSERT INTO products (name, barcode, category_id, price, cost, 
                 tax_rate, stock, min_stock, description, status, image_path, tenant_id) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $params = [$name, $barcode, $category_id, $price, $cost, $tax_rate, 
               $stock, $min_stock, $description, $status, $image_path, $tenant_id];
    $types = "ssidddiisssi";
}
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    
 if (!$product_id) {
        $product_id = $conn->insert_id;
        $message = "Product created successfully!";
    } else {
        $message = "Product updated successfully!";
    }
    
    echo json_encode([
        'success' => true,
        'message' => $message,
        'product_id' => $product_id
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage() // Use 'message' key for consistency
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}

?>