<?php
// cashiers.php - Tenant admin can register and manage cashiers
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../db_connect.php';

// Check if tenant admin is logged in
if (!isset($_SESSION['tenant_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

$tenant_id = $_SESSION['tenant_id'];
$message = '';
$message_type = '';

// Handle cashier registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_cashier'])) {
    $cashier_name = trim($_POST['cashier_name'] ?? '');
    $cashier_email = filter_var(trim($_POST['cashier_email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $cashier_phone = trim($_POST['cashier_phone'] ?? '');
    $cashier_password = $_POST['cashier_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $shift_type = $_POST['shift_type'] ?? '';
    $hourly_rate = floatval($_POST['hourly_rate'] ?? 0);

    // Validation
    if (!$cashier_name || !$cashier_email || !$cashier_password || !$confirm_password) {
        $message = "All required fields must be filled.";
        $message_type = "error";
    } elseif ($cashier_password !== $confirm_password) {
        $message = "Passwords do not match.";
        $message_type = "error";
    } elseif (strlen($cashier_password) < 6) {
        $message = "Password must be at least 6 characters long.";
        $message_type = "error";
    } else {
        try {
            $pdo->beginTransaction();

            // Check if email already exists
            $check_email = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $check_email->execute([$cashier_email]);
            
            if ($check_email->fetchColumn() > 0) {
                throw new Exception("Email is already registered.");
            }

            // Generate employee ID
            $employee_id = 'EMP' . $tenant_id . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

            // Hash password
            $password_hash = password_hash($cashier_password, PASSWORD_BCRYPT);

            // Insert into users table
            $user_stmt = $pdo->prepare("
                INSERT INTO users (tenant_id, email, password_hash, role, full_name, phone, employee_id, shift_type, hourly_rate, status, created_at) 
                VALUES (?, ?, ?, 'cashier', ?, ?, ?, ?, ?, 'active', NOW())
            ");
            $user_stmt->execute([
                $tenant_id, 
                $cashier_email, 
                $password_hash, 
                $cashier_name, 
                $cashier_phone, 
                $employee_id, 
                $shift_type, 
                $hourly_rate
            ]);

            $pdo->commit();
            $message = "Cashier registered successfully! Employee ID: " . $employee_id;
            $message_type = "success";

            // Clear form data
            $cashier_name = $cashier_email = $cashier_phone = $shift_type = $hourly_rate = '';

        } catch (Exception $e) {
            $pdo->rollBack();
            $message = "Error registering cashier: " . $e->getMessage();
            $message_type = "error";
        }
    }
}

// Handle cashier status update (activate/deactivate)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $user_id = intval($_POST['user_id']);
    $new_status = $_POST['new_status'] === 'active' ? 'active' : 'inactive';

    try {
        $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE user_id = ? AND tenant_id = ? AND role = 'cashier'");
        $stmt->execute([$new_status, $user_id, $tenant_id]);
        
        $message = "Cashier status updated successfully.";
        $message_type = "success";
    } catch (Exception $e) {
        $message = "Error updating status: " . $e->getMessage();
        $message_type = "error";
    }
}

// Fetch all cashiers for this tenant
try {
    $cashiers_stmt = $pdo->prepare("
        SELECT user_id, full_name, email, phone, employee_id, shift_type, hourly_rate, status, created_at 
        FROM users 
        WHERE tenant_id = ? AND role = 'cashier' 
        ORDER BY created_at DESC
    ");
    $cashiers_stmt->execute([$tenant_id]);
    $cashiers = $cashiers_stmt->fetchAll();
} catch (Exception $e) {
    $cashiers = [];
    $message = "Error fetching cashiers: " . $e->getMessage();
    $message_type = "error";
}

// Get tenant business name
try {
    $tenant_stmt = $pdo->prepare("SELECT business_name FROM tenants WHERE tenant_id = ?");
    $tenant_stmt->execute([$tenant_id]);
    $tenant_data = $tenant_stmt->fetch();
    $business_name = $tenant_data['business_name'] ?? 'Business';
} catch (Exception $e) {
    $business_name = 'Business';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cashier Management | <?php echo htmlspecialchars($business_name); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .cashier-grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 30px;
            margin-top: 20px;
        }
        
        .registration-form {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            height: fit-content;
        }
        
        .cashiers-list {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .cashier-card {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }
        
        .cashier-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        
        .cashier-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .cashier-info {
            flex: 1;
        }
        
        .cashier-actions {
            display: flex;
            gap: 10px;
        }
        
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-active {
            background: #d1fae5;
            color: #065f46;
        }
        
        .status-inactive {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        @media (max-width: 768px) {
            .cashier-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .cashier-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .cashier-actions {
                align-self: stretch;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar (your existing sidebar code) -->
        <aside class="sidebar" id="sidebar">
            <div class="logo">
                <i class="fas fa-cash-register"></i>
                <span>POS Admin</span>
            </div>
            <nav>
                <ul>
                    <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="inventory/index.php"><i class="fas fa-boxes"></i> Inventory</a></li>
                    <li><a href="cashiers.php" class="active"><i class="fas fa-users"></i> Cashiers</a></li>
                    <li><a href="report.php"><i class="fas fa-chart-line"></i> Reports</a></li>
                    <li><a href="categories.php"><i class="fas fa-tags"></i> Categories</a></li>
                    <li><a href="sales.php"><i class="fas fa-shopping-cart"></i> Sales</a></li>
                    <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                    <li><a href="#"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Bar -->
            <header class="top-bar">
                <div class="page-title">
                    <h1>Cashier Management</h1>
                    <p>Register and manage cashiers for your business</p>
                </div>
                <button class="menu-toggle" id="menu-toggle">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="user-profile">
                    <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=100&h=100&fit=crop&crop=face" alt="Admin">
                    <span>Admin</span>
                </div>
            </header>

            <?php if ($message): ?>
                <div class="message-box <?php echo $message_type; ?>" style="margin-bottom: 20px;">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <div class="cashier-grid">
                <!-- Registration Form -->
                <div class="registration-form">
                    <h2><i class="fas fa-user-plus"></i> Register New Cashier</h2>
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="cashier_name">Full Name *</label>
                            <input type="text" id="cashier_name" name="cashier_name" 
                                   value="<?php echo htmlspecialchars($cashier_name ?? ''); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="cashier_email">Email Address *</label>
                            <input type="email" id="cashier_email" name="cashier_email" 
                                   value="<?php echo htmlspecialchars($cashier_email ?? ''); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="cashier_phone">Phone Number</label>
                            <input type="tel" id="cashier_phone" name="cashier_phone" 
                                   value="<?php echo htmlspecialchars($cashier_phone ?? ''); ?>">
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="shift_type">Shift Type</label>
                                <select id="shift_type" name="shift_type">
                                    <option value="morning" <?php echo (($shift_type ?? '') === 'morning') ? 'selected' : ''; ?>>Morning</option>
                                    <option value="afternoon" <?php echo (($shift_type ?? '') === 'afternoon') ? 'selected' : ''; ?>>Afternoon</option>
                                    <option value="evening" <?php echo (($shift_type ?? '') === 'evening') ? 'selected' : ''; ?>>Evening</option>
                                    <option value="full_time" <?php echo (($shift_type ?? '') === 'full_time') ? 'selected' : ''; ?>>Full Time</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="hourly_rate">Hourly Rate (Optional)</label>
                                <input type="number" id="hourly_rate" name="hourly_rate" step="0.01" min="0"
                                       value="<?php echo htmlspecialchars($hourly_rate ?? ''); ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="cashier_password">Password *</label>
                            <input type="password" id="cashier_password" name="cashier_password" required minlength="6">
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Confirm Password *</label>
                            <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                        </div>

                        <button type="submit" name="register_cashier" class="btn btn-primary">
                            <i class="fas fa-user-plus"></i> Register Cashier
                        </button>
                    </form>
                </div>

                <!-- Cashiers List -->
                <div class="cashiers-list">
                    <h2><i class="fas fa-users"></i> Current Cashiers (<?php echo count($cashiers); ?>)</h2>
                    
                    <?php if (empty($cashiers)): ?>
                        <div style="text-align: center; padding: 40px; color: #6b7280;">
                            <i class="fas fa-users" style="font-size: 48px; margin-bottom: 15px; opacity: 0.5;"></i>
                            <p>No cashiers registered yet.</p>
                            <p>Register your first cashier using the form on the left.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($cashiers as $cashier): ?>
                            <div class="cashier-card">
                                <div class="cashier-header">
                                    <div class="cashier-info">
                                        <h3><?php echo htmlspecialchars($cashier['full_name']); ?></h3>
                                        <p style="color: #6b7280; margin: 5px 0;">
                                            <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($cashier['email']); ?>
                                        </p>
                                        <?php if ($cashier['phone']): ?>
                                            <p style="color: #6b7280; margin: 5px 0;">
                                                <i class="fas fa-phone"></i> <?php echo htmlspecialchars($cashier['phone']); ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="cashier-actions">
                                        <span class="status-badge status-<?php echo $cashier['status']; ?>">
                                            <?php echo $cashier['status']; ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-top: 15px; padding-top: 15px; border-top: 1px solid #e5e7eb;">
                                    <div>
                                        <small style="color: #6b7280;">Employee ID</small>
                                        <p style="font-weight: 600; margin: 2px 0;"><?php echo htmlspecialchars($cashier['employee_id']); ?></p>
                                    </div>
                                    <div>
                                        <small style="color: #6b7280;">Shift</small>
                                        <p style="font-weight: 600; margin: 2px 0; text-transform: capitalize;">
                                            <?php echo htmlspecialchars($cashier['shift_type'] ?: 'Not set'); ?>
                                        </p>
                                    </div>
                                    <div>
                                        <small style="color: #6b7280;">Hourly Rate</small>
                                        <p style="font-weight: 600; margin: 2px 0;">
                                            <?php echo $cashier['hourly_rate'] ? '$' . number_format($cashier['hourly_rate'], 2) : 'Not set'; ?>
                                        </p>
                                    </div>
                                    <div>
                                        <small style="color: #6b7280;">Registered</small>
                                        <p style="font-weight: 600; margin: 2px 0;">
                                            <?php echo date('M j, Y', strtotime($cashier['created_at'])); ?>
                                        </p>
                                    </div>
                                </div>

                                <div style="margin-top: 15px; display: flex; gap: 10px;">
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="user_id" value="<?php echo $cashier['user_id']; ?>">
                                        <input type="hidden" name="new_status" value="<?php echo $cashier['status'] === 'active' ? 'inactive' : 'active'; ?>">
                                        <button type="submit" name="update_status" 
                                                class="btn <?php echo $cashier['status'] === 'active' ? 'btn-warning' : 'btn-success'; ?>" 
                                                style="font-size: 14px; padding: 8px 16px;">
                                            <i class="fas <?php echo $cashier['status'] === 'active' ? 'fa-pause' : 'fa-play'; ?>"></i>
                                            <?php echo $cashier['status'] === 'active' ? 'Deactivate' : 'Activate'; ?>
                                        </button>
                                    </form>
                                    
                                    <button type="button" class="btn btn-secondary" style="font-size: 14px; padding: 8px 16px;">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Mobile menu functionality
        const menuToggle = document.getElementById('menu-toggle');
        const sidebar = document.getElementById('sidebar');

        menuToggle?.addEventListener('click', () => {
            sidebar.classList.toggle('active');
        });

        // Auto-hide success messages
        setTimeout(() => {
            const successMessage = document.querySelector('.message-box.success');
            if (successMessage) {
                successMessage.style.opacity = '0';
                setTimeout(() => successMessage.remove(), 300);
            }
        }, 5000);
    </script>
</body>
</html>