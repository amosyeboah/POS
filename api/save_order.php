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

    if (!$data || !isset($data['items']) || count($data['items']) === 0) {
        throw new Exception('No items in order');
    }

    // Get user_id and tenant_id from session
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['tenant_id'])) {
        throw new Exception('User not authenticated');
    }
    $user_id = $_SESSION['user_id'];
    $tenant_id = $_SESSION['tenant_id'];

    // Optional customer validation
    $customer_id = null;
    if (isset($data['customer_id']) && $data['customer_id']) {
        $candidate = (int)$data['customer_id'];
        $cust_stmt = $conn->prepare("SELECT customer_id FROM customers WHERE customer_id = ? AND tenant_id = ? LIMIT 1");
        $cust_stmt->bind_param('ii', $candidate, $tenant_id);
        $cust_stmt->execute();
        $cust_stmt->store_result();
        if ($cust_stmt->num_rows > 0) {
            $customer_id = $candidate;
        }
        $cust_stmt->close();
        // If not found for this tenant, we simply treat as walk-in (null)
    }

    $transaction_code = 'TXN' . date('Ymd') . rand(1000, 9999);

    $conn->begin_transaction();

    // Prepare statements
    $product_stmt = $conn->prepare("SELECT price, tax_rate, stock FROM products WHERE product_id = ? AND tenant_id = ?");
    $item_stmt = $conn->prepare("INSERT INTO sale_items (sale_id, tenant_id, product_id, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?, ?)");
    $stock_stmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE product_id = ?");

    // Running totals
    $subtotal = 0;
    $tax = 0;
    $total = 0;

    // First insert into sales (with dummy values, update later)
    if ($customer_id !== null) {
        $sale_stmt = $conn->prepare("INSERT INTO sales (user_id, tenant_id, customer_id, transaction_code, subtotal, tax_amount, total, payment_method, status) VALUES (?, ?, ?, ?, 0, 0, 0, ?, 'completed')");
        $sale_stmt->bind_param("iiiss", $user_id, $tenant_id, $customer_id, $transaction_code, $data['payment_method']);
    } else {
        $sale_stmt = $conn->prepare("INSERT INTO sales (user_id, tenant_id, transaction_code, subtotal, tax_amount, total, payment_method, status) VALUES (?, ?, ?, 0, 0, 0, ?, 'completed')");
        $sale_stmt->bind_param("iiss", $user_id, $tenant_id, $transaction_code, $data['payment_method']);
    }
    $sale_stmt->execute();
    $sale_id = $conn->insert_id;

    foreach ($data['items'] as $item) {
        $product_id = (int)$item['id'];
        $quantity = (int)$item['quantity'];

        // Fetch price, tax_rate, and stock from DB
        $product_stmt->bind_param("ii", $product_id, $tenant_id);
        $product_stmt->execute();
        $result = $product_stmt->get_result();
        $product = $result->fetch_assoc();

        if (!$product) {
            throw new Exception("Product not found: $product_id");
        }

        if ($product['stock'] < $quantity) {
            throw new Exception("Insufficient stock for product ID: $product_id");
        }

        $unit_price = (float)$product['price'];
        $tax_rate = (float)$product['tax_rate'];

        $line_subtotal = $unit_price * $quantity;
        $line_tax = $line_subtotal * ($tax_rate / 100);
        $line_total = $line_subtotal + $line_tax;

        $subtotal += $line_subtotal;
        $tax += $line_tax;
        $total += $line_total;

        // Insert sale item
        $item_stmt->bind_param("iiiidd", $sale_id, $tenant_id, $product_id, $quantity, $unit_price, $line_total);
        $item_stmt->execute();

        // Update stock
        $stock_stmt->bind_param("ii", $quantity, $product_id);
        $stock_stmt->execute();
    }

    // Update totals in sales table
    $update_sale = $conn->prepare("UPDATE sales SET subtotal = ?, tax_amount = ?, total = ? WHERE sale_id = ?");
    $update_sale->bind_param("dddi", $subtotal, $tax, $total, $sale_id);
    $update_sale->execute();

    $conn->commit();

    echo json_encode([
        'success' => true,
        'order_id' => $sale_id,
        'transaction_code' => $transaction_code,
        'subtotal' => $subtotal,
        'tax' => $tax,
        'total' => $total,
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
