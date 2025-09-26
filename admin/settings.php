<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../db_connect.php';

// Check if tenant is logged in
if (!isset($_SESSION['tenant_id'])) {
    header('Location: ../index.php');
    exit;
}

$tenant_id = $_SESSION['tenant_id']; // Use session instead of $_GET

// Initialize variables for form fields
$business_name = '';
$contact_name = '';
$contact_email = '';
$contact_phone = '';
$industry = ''; // Correct variable name for storage and display
$plan_type = '';
$currency_type = '';

// Message variables
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and get form data
    $business_name_post = htmlspecialchars(trim($_POST['business_name'] ?? ''));
    $contact_name_post = htmlspecialchars(trim($_POST['name'] ?? ''));
    $contact_email_post = htmlspecialchars(trim($_POST['email'] ?? ''));
    $contact_phone_post = htmlspecialchars(trim($_POST['phone'] ?? ''));
    
    // 💡 FIX 1: Change to correctly read from 'industry' POST field (matching the form name attribute)
    $industry_post = htmlspecialchars(trim($_POST['industry'] ?? '')); 
    
    $currency_type_post = htmlspecialchars(trim($_POST['currency_type'] ?? ''));

    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("UPDATE tenants SET
            business_name = ?,
            contact_name = ?,
            contact_email = ?,
            contact_phone = ?,
            industry = ?,
            currency_type = ?
            WHERE tenant_id = ?"
        );
        $stmt->execute([
            $business_name_post,
            $contact_name_post,
            $contact_email_post,
            $contact_phone_post,
            $industry_post, // Using the corrected POST variable
            $currency_type_post,
            $tenant_id
        ]);

        // Password update logic remains unchanged...
        if (!empty($current_password) && !empty($new_password) && !empty($confirm_password)) {
            // Check if new passwords match
            if ($new_password !== $confirm_password) {
                throw new Exception("New password and confirm password do not match.");
            }

            // Get current user's password hash from database
            $user_stmt = $pdo->prepare("SELECT password_hash FROM users WHERE tenant_id = ? AND role = 'admin'");
            $user_stmt->execute([$tenant_id]);
            $user_data = $user_stmt->fetch();

            if (!$user_data) {
                throw new Exception("User not found.");
            }

            // Verify current password
            if (!password_verify($current_password, $user_data['password_hash'])) {
                throw new Exception("Current password is incorrect.");
            }

            // Hash the new password
            $new_password_hash = password_hash($new_password, PASSWORD_BCRYPT);

            // Update password in users table
            $password_stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE tenant_id = ? AND role = 'admin'");
            $password_stmt->execute([$new_password_hash, $tenant_id]);

            $message = "Account and password updated successfully!";
            $message_type = "success";

        } elseif (!empty($current_password) || !empty($new_password) || !empty($confirm_password)) {
            // If any password field is filled but not all, show error
            $pdo->rollBack();
            $message = "To change password, all password fields must be filled.";
            $message_type = "error";
            return; // Exit early to avoid the commit below
        }

        $pdo->commit();
        // Only show this if no password was updated (password update has its own message)
        if (empty($current_password) && empty($new_password) && empty($confirm_password)) {
            $message = "Account updated successfully!";
            $message_type = "success";
        }

    } catch (Exception $e) {
        $pdo->rollBack();
        $message = "Error updating account: " . $e->getMessage();
        $message_type = "error";
    }
}

// Fetch tenant details based on session (remains correct)
try {
    $stmt = $pdo->prepare("SELECT business_name, contact_name, contact_email, contact_phone, industry, plan_type, currency_type FROM tenants WHERE tenant_id = ?");
    $stmt->execute([$tenant_id]);
    $tenant_data = $stmt->fetch();

    if ($tenant_data) {
        $business_name = htmlspecialchars($tenant_data['business_name'] ?? '');
        $contact_name = htmlspecialchars($tenant_data['contact_name'] ?? '');
        $contact_email = htmlspecialchars($tenant_data['contact_email'] ?? '');
        $contact_phone = htmlspecialchars($tenant_data['contact_phone'] ?? '');
        $industry = htmlspecialchars($tenant_data['industry'] ?? '');
        $plan_type = htmlspecialchars($tenant_data['plan_type'] ?? '');
        $currency_type = htmlspecialchars($tenant_data['currency_type'] ?? '');

    } else {
        $message = "No tenant data found.";
        $message_type = "error";
    }
} catch (PDOException $e) {
    $message = "Error fetching data: " . $e->getMessage();
    $message_type = "error";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings | POS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin.css">
    <style>
        /* Modern Account Page Styles */
        .account-settings-card {
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            padding: 30px;
            margin-top: 30px;
        }

        .account-settings-card h2 {
            font-size: 1.8rem;
            color: #333;
            margin-bottom: 25px;
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #555;
            font-size: 0.95rem;
        }

        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="password"],
        .form-group input[type="tel"],
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            color: #333;
            box-sizing: border-box; /* Ensures padding doesn't affect overall width */
            transition: border-color 0.3s ease;
        }

        .form-group input[type="text"]:focus,
        .form-group input[type="email"]:focus,
        .form-group input[type="password"]:focus,
        .form-group input[type="tel"]:focus,
        .form-group select:focus {
            outline: none;
            border-color: #007bff; /* Highlight on focus */
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.25);
        }

        .form-actions {
            margin-top: 30px;
            border-top: 1px solid #eee;
            padding-top: 25px;
            display: flex;
            justify-content: flex-end; /* Aligns buttons to the right */
            gap: 15px;
        }

        .btn {
            padding: 12px 25px;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .btn-primary {
            background-color: #007bff;
            color: #fff;
            border: none;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: #fff;
            border: none;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }

        .btn-danger {
            background-color: #dc3545;
            color: #fff;
            border: none;
        }

        .btn-danger:hover {
            background-color: #c82333;
        }

        .password-edit-section {
            margin-top: 35px;
            padding-top: 25px;
            border-top: 1px solid #eee;
        }

        .delete-account-section {
            margin-top: 40px;
            padding-top: 30px;
            border-top: 1px solid #ffcc00; /* A distinct border for this sensitive section */
            text-align: center;
        }

        .delete-account-section p {
            color: #666;
            margin-bottom: 20px;
            font-size: 0.95rem;
        }

        /* Message Box Styling */
        .message-box {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-weight: 500;
            display: none; /* Hidden by default */
        }

        .message-box.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message-box.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .message-box.info {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        } 
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="overlay" id="overlay"></div>

        <aside class="sidebar" id="sidebar">
            <div class="logo">
                <i class="fas fa-cash-register"></i>
                <span>POS Admin</span>
            </div>
            <nav>
                <ul>
                    <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="inventory/index.php"><i class="fas fa-boxes"></i> Inventory</a></li>
                    <li><a href="employees.php"><i class="fas fa-users"></i> Employees</a></li>
                    <li><a href="report.php"><i class="fas fa-chart-line"></i> Reports</a></li>
                    <li><a href="categories.php"><i class="fas fa-tags"></i> Categories</a></li>
                    <li><a href="sales.php"><i class="fas fa-shopping-cart"></i> Sales</a></li>
                    <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <header class="top-bar">
                <div class="page-title">
                    <h1>Account Settings</h1>
                    <p>Manage your business and personal information here.</p>
                </div>
                <button class="menu-toggle" id="menu-toggle">
                    <i class="fas fa-bars"></i>
                </button>
                <a href="account.php" style="text-decoration: none; color: inherit;">
                    <div class="user-profile">
                        <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=100&h=100&fit=crop&crop=face" alt="Admin">
                        <span>John Admin</span>
                    </div>
                </a>
            </header>

            <div class="account-settings-card">
                <?php if ($message): ?>
                    <div class="message-box <?php echo $message_type; ?>" style="display: block;">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <h2>Account Information</h2>
                <form action="settings.php<?php echo isset($_GET['tenant_id']) ? '?tenant_id=' . (int)$_GET['tenant_id'] : ''; ?>" method="POST">
                    <div class="form-group">
                        <label for="business_name">Business Name</label>
                        <input type="text" id="business_name" name="business_name" value="<?php echo $business_name; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="name">Your Name</label>
                        <input type="text" id="name" name="name" value="<?php echo $contact_name; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" value="<?php echo $contact_email; ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo $contact_phone; ?>">
                    </div>
                    <div class="form-group">
                        <label for="industry">Industry Type</label>
                        <select id="industry" name="industry" required>
                            <option value="">Select industry</option>
                            <option value="Restaurant" <?php echo ($industry == 'Restaurant') ? 'selected' : ''; ?>>Restaurant</option>
                            <option value="Retail Store" <?php echo ($industry == 'Retail Store') ? 'selected' : ''; ?>>Retail Store</option>
                            <option value="Café/Coffee Shop" <?php echo ($industry == 'Café/Coffee Shop') ? 'selected' : ''; ?>>Café/Coffee Shop</option>
                            <option value="Bar/Pub" <?php echo ($industry == 'Bar/Pub') ? 'selected' : ''; ?>>Bar/Pub</option>
                            <option value="Salon/Spa" <?php echo ($industry == 'Salon/Spa') ? 'selected' : ''; ?>>Salon/Spa</option>
                            <option value="Grocery Store" <?php echo ($industry == 'Grocery Store') ? 'selected' : ''; ?>>Grocery Store</option>
                            <option value="Fashion/Clothing" <?php echo ($industry == 'Fashion/Clothing') ? 'selected' : ''; ?>>Fashion/Clothing</option>
                            <option value="Electronics" <?php echo ($industry == 'Electronics') ? 'selected' : ''; ?>>Electronics</option>
                            <option value="Pharmacy" <?php echo ($industry == 'Pharmacy') ? 'selected' : ''; ?>>Pharmacy</option>
                            <option value="Service Business" <?php echo ($industry == 'Service Business') ? 'selected' : ''; ?>>Service Business</option>
                            <option value="Other" <?php echo ($industry == 'Other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="plan">Current Plan</label>
                        <input type="text" id="plan" name="plan" value="<?php echo $plan_type; ?>" readonly>
                        <small>Contact support to change your plan.</small>
                    </div>
                    <div class="form-group">
                        <label for="currency_type">Currency Type</label>
                        <select id="currency_type" name="currency_type">
                            <option value="GHS" <?php echo ($currency_type == 'GHS') ? 'selected' : ''; ?>>Ghana Cedi (GHS)</option>
                            <option value="USD" <?php echo ($currency_type == 'USD') ? 'selected' : ''; ?>>US Dollar (USD)</option>
                            <option value="EUR" <?php echo ($currency_type == 'EUR') ? 'selected' : ''; ?>>Euro (EUR)</option>
                            <option value="GBP" <?php echo ($currency_type == 'GBP') ? 'selected' : ''; ?>>British Pound (GBP)</option>
                            <option value="NGN" <?php echo ($currency_type == 'NGN') ? 'selected' : ''; ?>>Nigerian Naira (NGN)</option>
                        </select>
                    </div>

                    <div class="password-edit-section">
                        <h2>Change Password</h2>
                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <input type="password" id="current_password" name="current_password">
                        </div>
                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password">
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password">
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="reset" class="btn btn-secondary">Reset</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>

                <div class="delete-account-section">
                    <h2>Delete Account</h2>
                    <p>Permanently delete your account and all associated data. This action cannot be undone.</p>
                    <button type="button" class="btn btn-danger" onclick="showCustomConfirm('Are you sure you want to permanently delete your account? This action cannot be undone.', deleteAccount);">Delete My Account</button>
                </div>
            </div>

        </main>

        <button class="floating-menu" id="floating-menu">
            <i class="fas fa-bars"></i>
        </button>
    </div>

    <div id="customConfirmModal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.4);">
        <div style="background-color: #fefefe; margin: 15% auto; padding: 20px; border: 1px solid #888; width: 80%; max-width: 400px; border-radius: 10px; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.3);">
            <p id="confirmMessage" style="margin-bottom: 20px; font-size: 1.1rem;"></p>
            <button id="confirmYes" class="btn btn-danger" style="margin-right: 10px;">Yes</button>
            <button id="confirmNo" class="btn btn-secondary">No</button>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Mobile menu functionality (from your original code)
        const menuToggle = document.getElementById('menu-toggle');
        const floatingMenu = document.getElementById('floating-menu');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');

        function toggleSidebar() {
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        }

        function closeSidebar() {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
        }

        menuToggle?.addEventListener('click', toggleSidebar);
        floatingMenu?.addEventListener('click', toggleSidebar);
        overlay?.addEventListener('click', closeSidebar);

        // Close sidebar on window resize if screen becomes large
        window.addEventListener('resize', () => {
            if (window.innerWidth > 768) {
                closeSidebar();
            }
        });

        // Custom Confirmation Modal Logic
        let confirmCallback = null;

        function showCustomConfirm(message, callback) {
            document.getElementById('confirmMessage').innerText = message;
            document.getElementById('customConfirmModal').style.display = 'block';
            confirmCallback = callback;
        }

        document.getElementById('confirmYes').addEventListener('click', () => {
            document.getElementById('customConfirmModal').style.display = 'none';
            if (confirmCallback) {
                confirmCallback();
            }
        });

        document.getElementById('confirmNo').addEventListener('click', () => {
            document.getElementById('customConfirmModal').style.display = 'none';
            confirmCallback = null;
        });

        // Function to handle account deletion
        function deleteAccount() {
            // In a real application, you would make an AJAX call or submit a form to delete the account
            // Example:
            // fetch('delete_account.php', {
            //     method: 'POST',
            //     headers: {
            //         'Content-Type': 'application/json',
            //     },
            //     body: JSON.stringify({ tenant_id: <?php echo $tenant_id; ?> }) // Pass the tenant ID
            // })
            // .then(response => response.json())
            // .then(data => {
            //     if (data.success) {
            //         alert("Account deleted successfully!");
            //         window.location.href = 'logout.php'; // Redirect after deletion
            //     } else {
            //         alert("Error deleting account: " + data.message);
            //     }
            // })
            // .catch(error => {
            //     console.error('Error:', error);
            //     alert("An error occurred during deletion.");
            // });

            // For now, just a placeholder alert
            alert("Account deletion initiated. (This is a placeholder, real deletion logic needed)");
            // Example: window.location.href = 'logout.php'; // Redirect after "deletion"
        }
    </script>

</body>
</html>