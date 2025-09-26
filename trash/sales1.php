<?php
// Database connection
$host = 'localhost';
$dbname = 'mobpos';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Fetch all active categories
function getCategories($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE status = 'active'");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch products by category (or all if no category specified)
function getProducts($pdo, $category_id = null) {
    $sql = "SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.category_id 
            WHERE p.status = 'active'";
    
    $params = [];
    
    if ($category_id) {
        $sql .= " AND p.category_id = ?";
        $params[] = $category_id;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Handle AJAX requests
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    try {
        switch ($_GET['action']) {
            case 'get_categories':
                echo json_encode(getCategories($pdo));
                break;
                
            case 'get_products':
                $category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : null;
                echo json_encode(getProducts($pdo, $category_id));
                break;
                
            case 'search_products':
                $search = isset($_GET['search']) ? $_GET['search'] : '';
                $stmt = $pdo->prepare("SELECT * FROM products WHERE status = 'active' AND (name LIKE ? OR barcode = ?)");
                $stmt->execute(["%$search%", $search]);
                echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
                break;
                
            default:
                echo json_encode(['error' => 'Invalid action']);
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS - New Sale</title>
    <link rel="stylesheet" href="../assets/css/cashier-sales1.css">
    <link rel="stylesheet" href="../assets/css/cashier-sales.css">

</head>
<body>
    <div class="pos-container">
        <!-- Header Section -->
        <header class="pos-header">
            <button class="btn-back" onclick="window.history.back()">←</button>
            <h1>New Sale</h1>
            <button class="btn-menu">☰</button>
        </header>

        <!-- Product Search -->
        <div class="search-container">
            <input type="text" id="product-search" placeholder="Search products..." autocomplete="off">
            <button id="barcode-scan" class="btn-scan">📷</button>
        </div>

        <!-- Categories Filter -->
        <div class="categories-scroll">
            <div class="category active">All</div>
            <div class="category">Beverages</div>
            <div class="category">Snacks</div>
            <div class="category">Alcohol</div>
            <!-- More categories will be loaded dynamically -->
        </div>

        <!-- Current Order -->
        <div class="order-summary">
            <div class="order-header">
                <span>Order #<span id="order-number">10045</span></span>
                <button id="clear-order" class="btn-text">Clear</button>
            </div>
            
            <div class="order-items" id="order-items">
                <!-- Items will be added here dynamically -->
                <div class="empty-cart">No items added yet</div>
            </div>
            
            <div class="order-totals">
                <div class="total-row">
                    <span>Subtotal:</span>
                    <span id="subtotal">$0.00</span>
                </div>
                <div class="total-row">
                    <span>Tax:</span>
                    <span id="tax">$0.00</span>
                </div>
                <div class="total-row grand-total">
                    <span>Total:</span>
                    <span id="total">$0.00</span>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <button id="btn-discount" class="btn-action">Discount</button>
            <button id="btn-customer" class="btn-action">Customer</button>
            <button id="btn-hold" class="btn-action">Hold</button>
        </div>

        <!-- Payment Buttons -->
        <div class="payment-buttons">
            <button id="btn-cash" class="btn-payment cash">Cash</button>
            <button id="btn-card" class="btn-payment card">Card</button>
            <button id="btn-mobile" class="btn-payment mobile">Mobile</button>
        </div>
    </div>

    <!-- Product Selection Modal -->
    <div id="product-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Select Product amos</h3>
                <span class="close-modal">&times;</span>
            </div>
            <div class="modal-body" id="product-list">
                <!-- Products will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Discount Modal -->
    <div id="discount-modal" class="modal">
        <!-- Discount form would go here -->
    </div>

    <script src="../assets/js/utils.js"></script>
    <script src="../assets/js/cashier1.js"></script>
</body>
</html>