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

$tenant_id = $_SESSION['tenant_id'];
$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_employee':
                $username = trim($_POST['username']);
                $password = $_POST['password'];
                $confirm_password = $_POST['confirm_password'];
                $full_name = trim($_POST['full_name']);
                $email = trim($_POST['email']);
                $phone = trim($_POST['phone']);
                $role = $_POST['role'];
                
                // Validation
                if (empty($username) || empty($password) || empty($full_name)) {
                    $error = "Username, password, and full name are required.";
                } elseif ($password !== $confirm_password) {
                    $error = "Passwords do not match.";
                } elseif (strlen($password) < 6) {
                    $error = "Password must be at least 6 characters long.";
                } else {
                    // Check if username already exists for this tenant
                    $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE tenant_id = ? AND username = ?");
                    $check_stmt->execute([$tenant_id, $username]);
                    
                    if ($check_stmt->fetchColumn() > 0) {
                        $error = "Username already exists.";
                    } else {
                        // Insert new employee
                        $password_hash = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("INSERT INTO users (tenant_id, username, password_hash, role, full_name, email, phone) VALUES (?, ?, ?, ?, ?, ?, ?)");
                        
                        if ($stmt->execute([$tenant_id, $username, $password_hash, $role, $full_name, $email, $phone])) {
                            $message = "Employee added successfully!";
                        } else {
                            $error = "Failed to add employee.";
                        }
                    }
                }
                break;
                
            case 'update_status':
                $user_id = $_POST['user_id'];
                $status = $_POST['status'];
                
                $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE user_id = ? AND tenant_id = ?");
                if ($stmt->execute([$status, $user_id, $tenant_id])) {
                    $message = "Employee status updated successfully!";
                } else {
                    $error = "Failed to update employee status.";
                }
                break;
                
            case 'delete_employee':
                $user_id = $_POST['user_id'];
                
                // Check if employee has any sales records
                $check_sales = $pdo->prepare("SELECT COUNT(*) FROM sales WHERE user_id = ?");
                $check_sales->execute([$user_id]);
                
                if ($check_sales->fetchColumn() > 0) {
                    $error = "Cannot delete employee with existing sales records. You can deactivate instead.";
                } else {
                    $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ? AND tenant_id = ?");
                    if ($stmt->execute([$user_id, $tenant_id])) {
                        $message = "Employee deleted successfully!";
                    } else {
                        $error = "Failed to delete employee.";
                    }
                }
                break;
        }
    }
}

// Fetch all employees for this tenant
$employees_stmt = $pdo->prepare("SELECT * FROM users WHERE tenant_id = ? ORDER BY created_at DESC");
$employees_stmt->execute([$tenant_id]);
$employees = $employees_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get tenant info for display
$tenant_stmt = $pdo->prepare("SELECT business_name FROM tenants WHERE tenant_id = ?");
$tenant_stmt->execute([$tenant_id]);
$tenant_info = $tenant_stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Management | POS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f5f5f5;
            color: #333;
        }

        .admin-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 250px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            transition: transform 0.3s ease;
            z-index: 1000;
        }

        .logo {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .logo i {
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .sidebar nav ul {
            list-style: none;
            padding: 20px 0;
        }

        .sidebar nav li {
            margin: 5px 0;
        }

        .sidebar nav a {
            color: white;
            text-decoration: none;
            padding: 12px 20px;
            display: block;
            transition: background-color 0.3s;
        }

        .sidebar nav a:hover,
        .sidebar nav .active a {
            background-color: rgba(255,255,255,0.1);
        }

        .sidebar nav i {
            margin-right: 10px;
            width: 20px;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 250px;
            background-color: #f8f9fa;
        }

        .top-bar {
            background: white;
            padding: 20px 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-title h1 {
            color: #2c3e50;
            font-size: 1.8rem;
            margin-bottom: 5px;
        }

        .page-title p {
            color: #7f8c8d;
            font-size: 0.9rem;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-profile img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        /* Content Area */
        .content {
            padding: 30px;
        }

        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .btn-success {
            background-color: #28a745;
            color: white;
        }

        .btn-warning {
            background-color: #ffc107;
            color: #212529;
        }

        .btn-danger {
            background-color: #dc3545;
            color: white;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 0.875rem;
        }

        /* Cards */
        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            font-weight: 600;
        }

        .card-body {
            padding: 20px;
        }

        /* Form Styles */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #555;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            border: 2px solid #e1e5e9;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        /* Table Styles */
        .table-container {
            overflow-x: auto;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        .table th,
        .table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e1e5e9;
        }

        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #555;
        }

        .table tbody tr:hover {
            background-color: #f8f9fa;
        }

        /* Status badges */
        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .badge-success {
            background-color: #d4edda;
            color: #155724;
        }

        .badge-danger {
            background-color: #f8d7da;
            color: #721c24;
        }

        /* Alert messages */
        .alert {
            padding: 12px 16px;
            margin-bottom: 20px;
            border-radius: 6px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-dialog {
            position: relative;
            width: 90%;
            max-width: 500px;
            margin: 50px auto;
        }

        .modal-content {
            background: white;
            border-radius: 10px;
            overflow: hidden;
        }

        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-body {
            padding: 20px;
        }

        .close {
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .menu-toggle {
                display: block;
                background: none;
                border: none;
                font-size: 1.2rem;
                color: #333;
                cursor: pointer;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (min-width: 769px) {
            .menu-toggle {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="logo">
                <i class="fas fa-cash-register"></i>
                <span>POS Admin</span>
            </div>
            <nav>
                <ul>
                    <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="inventory/index.php"><i class="fas fa-boxes"></i> Inventory</a></li>
                    <li class="active"><a href="employees.php"><i class="fas fa-users"></i> Employees</a></li>
                    <li><a href="reports.php"><i class="fas fa-chart-line"></i> Reports</a></li>
                    <li><a href="categories.php"><i class="fas fa-tags"></i> Categories</a></li>
                    <li><a href="sales.php"><i class="fas fa-shopping-cart"></i> Sales</a></li>
                    <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Bar -->
            <header class="top-bar">
                <div class="page-title">
                    <h1>Employee Management</h1>
                    <p>Manage cashiers and admin users for <?php echo htmlspecialchars($tenant_info['business_name'] ?? 'Your Business'); ?></p>
                </div>
                <button class="menu-toggle" id="menu-toggle">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="user-profile">
                    <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=100&h=100&fit=crop&crop=face" alt="Admin">
                    <span><?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></span>
                </div>
            </header>

            <!-- Content -->
            <div class="content">
                <!-- Messages -->
                <?php if ($message): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <!-- Content Header -->
                <div class="content-header">
                    <h2>All Employees (<?php echo count($employees); ?>)</h2>
                    <button class="btn btn-primary" onclick="openModal('addEmployeeModal')">
                        <i class="fas fa-plus"></i> Add New Employee
                    </button>
                </div>

                <!-- Employees Table -->
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-users"></i> Employee List
                    </div>
                    <div class="card-body">
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Full Name</th>
                                        <th>Username</th>
                                        <th>Role</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Status</th>
                                        <th>Last Login</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($employees)): ?>
                                        <tr>
                                            <td colspan="8" style="text-align: center; padding: 40px;">
                                                <i class="fas fa-users" style="font-size: 3rem; color: #ccc; margin-bottom: 10px;"></i>
                                                <p>No employees found. Add your first employee to get started!</p>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($employees as $employee): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($employee['full_name']); ?></strong>
                                                </td>
                                                <td><?php echo htmlspecialchars($employee['username']); ?></td>
                                                <td>
                                                    <span class="badge <?php echo $employee['role'] === 'admin' ? 'badge-danger' : 'badge-success'; ?>">
                                                        <?php echo ucfirst($employee['role']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($employee['email'] ?? 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars($employee['phone'] ?? 'N/A'); ?></td>
                                                <td>
                                                    <span class="badge <?php echo $employee['status'] === 'active' ? 'badge-success' : 'badge-danger'; ?>">
                                                        <?php echo ucfirst($employee['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php 
                                                    echo $employee['last_login'] 
                                                        ? date('M j, Y g:i A', strtotime($employee['last_login']))
                                                        : 'Never';
                                                    ?>
                                                </td>
                                                <td>
                                                    <div style="display: flex; gap: 5px;">
                                                        <!-- Toggle Status -->
                                                        <form method="POST" style="display: inline;">
                                                            <input type="hidden" name="action" value="update_status">
                                                            <input type="hidden" name="user_id" value="<?php echo $employee['user_id']; ?>">
                                                            <input type="hidden" name="status" value="<?php echo $employee['status'] === 'active' ? 'inactive' : 'active'; ?>">
                                                            <button type="submit" class="btn btn-sm <?php echo $employee['status'] === 'active' ? 'btn-warning' : 'btn-success'; ?>" 
                                                                    title="<?php echo $employee['status'] === 'active' ? 'Deactivate' : 'Activate'; ?>">
                                                                <i class="fas fa-<?php echo $employee['status'] === 'active' ? 'pause' : 'play'; ?>"></i>
                                                            </button>
                                                        </form>
                                                        
                                                        <!-- Delete Employee -->
                                                        <form method="POST" style="display: inline;" 
                                                              onsubmit="return confirm('Are you sure you want to delete this employee? This action cannot be undone.')">
                                                            <input type="hidden" name="action" value="delete_employee">
                                                            <input type="hidden" name="user_id" value="<?php echo $employee['user_id']; ?>">
                                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete Employee">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Add Employee Modal -->
    <div class="modal" id="addEmployeeModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Add New Employee</h3>
                    <button type="button" class="close" onclick="closeModal('addEmployeeModal')">&times;</button>
                </div>
                <div class="modal-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="add_employee">
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="full_name">Full Name *</label>
                                <input type="text" class="form-control" id="full_name" name="full_name" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="username">Username *</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                        </div>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="password">Password *</label>
                                <input type="password" class="form-control" id="password" name="password" required minlength="6">
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password">Confirm Password *</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                        </div>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" id="email" name="email">
                            </div>
                            
                            <div class="form-group">
                                <label for="phone">Phone</label>
                                <input type="tel" class="form-control" id="phone" name="phone">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="role">Role *</label>
                            <select class="form-control" id="role" name="role" required>
                                <option value="cashier">Cashier</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        
                        <div style="text-align: right; margin-top: 20px;">
                            <button type="button" class="btn" onclick="closeModal('addEmployeeModal')" style="margin-right: 10px;">Cancel</button>
                            <button type="submit" class="btn btn-primary">Add Employee</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Modal functions
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
        }

        // Mobile menu functionality
        const menuToggle = document.getElementById('menu-toggle');
        const sidebar = document.getElementById('sidebar');

        function toggleSidebar() {
            sidebar.classList.toggle('active');
        }

        menuToggle?.addEventListener('click', toggleSidebar);

        // Password confirmation validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (password !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);
    </script>
</body>
</html>