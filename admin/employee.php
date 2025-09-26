<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mobpos";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Check if user is logged in and is admin
// if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
//     header("Location: index.php");
//     exit();
// }

if (!isset($_SESSION['user_id']) || !isset($_SESSION['tenant_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$tenant_id = $_SESSION['tenant_id'];

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'add_employee':
            try {
                $stmt = $pdo->prepare("INSERT INTO users (tenant_id, username, password_hash, role, full_name, email, phone, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'active')");
                $password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $stmt->execute([
                    $tenant_id,
                    $_POST['username'],
                    $password_hash,
                    strtolower($_POST['role']),
                    $_POST['full_name'],
                    $_POST['email'],
                    $_POST['phone']
                ]);
                echo json_encode(['success' => true, 'message' => 'Employee added successfully']);
            } catch(PDOException $e) {
                echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            }
            exit();
            
        case 'register_cashier':
            try {
                $stmt = $pdo->prepare("INSERT INTO users (tenant_id, username, password_hash, role, full_name, email, phone, status) VALUES (?, ?, ?, 'cashier', ?, ?, ?, 'active')");
                $password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $stmt->execute([
                    $tenant_id,
                    $_POST['username'],
                    $password_hash,
                    $_POST['full_name'],
                    $_POST['email'],
                    $_POST['phone']
                ]);
                echo json_encode(['success' => true, 'message' => 'Cashier registered successfully']);
            } catch(PDOException $e) {
                echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            }
            exit();
            
        case 'update_employee':
            try {
                $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, phone = ?, role = ? WHERE user_id = ? AND tenant_id = ?");
                $stmt->execute([
                    $_POST['full_name'],
                    $_POST['email'],
                    $_POST['phone'],
                    strtolower($_POST['role']),
                    $_POST['user_id'],
                    $tenant_id
                ]);
                echo json_encode(['success' => true, 'message' => 'Employee updated successfully']);
            } catch(PDOException $e) {
                echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            }
            exit();
            
        case 'toggle_status':
            try {
                $stmt = $pdo->prepare("UPDATE users SET status = CASE WHEN status = 'active' THEN 'inactive' ELSE 'active' END WHERE user_id = ? AND tenant_id = ?");
                $stmt->execute([$_POST['user_id'], $tenant_id]);
                echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
            } catch(PDOException $e) {
                echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            }
            exit();
            
        case 'delete_employee':
            try {
                $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ? AND tenant_id = ?");
                $stmt->execute([$_POST['user_id'], $tenant_id]);
                echo json_encode(['success' => true, 'message' => 'Employee deleted successfully']);
            } catch(PDOException $e) {
                echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            }
            exit();
    }
}

// Fetch employees data
try {
    $stmt = $pdo->prepare("SELECT user_id, username, full_name, email, phone, role, status, last_login, created_at FROM users WHERE tenant_id = ? ORDER BY created_at DESC");
    $stmt->execute([$tenant_id]);
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get statistics
    $total_employees = count($employees);
    $active_employees = count(array_filter($employees, function($emp) { return $emp['status'] === 'active'; }));
    $cashiers = count(array_filter($employees, function($emp) { return $emp['role'] === 'cashier'; }));
    
} catch(PDOException $e) {
    $employees = [];
    $total_employees = $active_employees = $cashiers = 0;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Management | POS Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- <link rel="stylesheet" href="css/admin.css"> -->
    <link rel="stylesheet" href="css/employee.css">

    <style>

    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Dark Overlay for Mobile -->
        <div class="overlay" id="overlay"></div>

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
                    <li class="active"><a href="#"><i class="fas fa-users"></i> Employees</a></li>
                    <li><a href="report.php"><i class="fas fa-chart-line"></i> Reports</a></li>
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
                    <p>Manage your team and track performance</p>
                </div>
                <button class="menu-toggle" id="menu-toggle">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="user-profile">
                    <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=100&h=100&fit=crop&crop=face" alt="Admin">
                    <span>John Admin</span>
                </div>
            </header>

            <!-- Employee Stats -->
                <div class="employee-stats">
                    <div class="stat-card">
                        <i class="fas fa-users"></i>
                        <h3><?php echo $total_employees; ?></h3>
                        <p>Total Employees</p>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-user-check"></i>
                        <h3><?php echo $active_employees; ?></h3>
                        <p>Active Employees</p>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-cash-register"></i>
                        <h3><?php echo $cashiers; ?></h3>
                        <p>Cashiers</p>
                    </div>
                </div>

            <!-- Employee Controls -->
            <div class="employee-controls">
                <button class="btn btn-primary" onclick="openModal('addEmployeeModal')">
                    <i class="fas fa-plus"></i>
                    Add New Employee
                </button>
                <button class="btn btn-secondary" onclick="openModal('registerCashierModal')">
                    <i class="fas fa-cash-register"></i>
                    Register Cashier
                </button>
                <div class="search-filter">
                    <input type="text" class="search-input" placeholder="Search employees..." id="searchInput">
                    <select class="filter-select" id="statusFilter">
                        <option value="all">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>

            <!-- Employee Table -->
            <div class="employee-table-container">
                <table class="employee-table" id="employeeTable">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Last Login</th>
                            <th>Toggle Active</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="employeeTableBody">
                        <!-- Employee rows will be populated by JavaScript -->
                    </tbody>
                </table>
            </div>
        </main>

        <!-- Floating Menu Button for Mobile -->
        <button class="floating-menu" id="floating-menu">
            <i class="fas fa-bars"></i>
        </button>
    </div>

    <!-- Add Employee Modal -->
    <div class="modal" id="addEmployeeModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New Employee</h2>
                <button class="close-btn" onclick="closeModal('addEmployeeModal')">&times;</button>
            </div>
            <form id="addEmployeeForm">
                <div class="form-group">
                    <label for="employeeName">Full Name</label>
                    <input type="text" class="form-input" id="employeeName" required>
                </div>
                <div class="form-group">
                    <label for="employeeEmail">Email Address</label>
                    <input type="email" class="form-input" id="employeeEmail" required>
                </div>
                <div class="form-group">
                    <label for="employeePhone">Phone Number</label>
                    <input type="tel" class="form-input" id="employeePhone" required>
                </div>
                 <div class="form-group">
                    <label for="cashierUsername">Username</label>
                    <input type="text" class="form-input" id="cashierUsername" required>
                </div>
                <div class="form-group">
                    <label for="cashierPassword">Password</label>
                    <input type="password" class="form-input" id="cashierPassword" required>
                </div>
                <div class="form-group">
                    <label for="employeeRole">Role</label>
                    <select class="form-select" id="employeeRole" required>
                        <option value="">Select Role</option>
                        <option value="admin">Admin</option>
                        <option value="cashier">Cashier</option>
                    </select>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addEmployeeModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Employee</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Register Cashier Modal -->
    <div class="modal" id="registerCashierModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Register New Cashier</h2>
                <button class="close-btn" onclick="closeModal('registerCashierModal')">&times;</button>
            </div>
            <form id="registerCashierForm">
                <div class="form-group">
                    <label for="cashierName">Full Name</label>
                    <input type="text" class="form-input" id="cashierName" required>
                </div>
                <div class="form-group">
                    <label for="cashierEmail">Email Address</label>
                    <input type="email" class="form-input" id="cashierEmail" required>
                </div>
                <div class="form-group">
                    <label for="cashierPhone">Phone Number</label>
                    <input type="tel" class="form-input" id="cashierPhone" required>
                </div>
                <div class="form-group">
                    <label for="cashierUsername">Username</label>
                    <input type="text" class="form-input" id="cashierUsername" required>
                </div>
                <div class="form-group">
                    <label for="cashierPassword">Password</label>
                    <input type="password" class="form-input" id="cashierPassword" required>
                </div>
                <!-- <div class="form-group">
                    <label for="cashierShift">Shift</label>
                    <select class="form-select" id="cashierShift" required>
                        <option value="">Select Shift</option>
                        <option value="Morning">Morning (6AM - 2PM)</option>
                        <option value="Afternoon">Afternoon (2PM - 10PM)</option>
                        <option value="Night">Night (10PM - 6AM)</option>
                    </select>
                </div> -->
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('registerCashierModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Register Cashier</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Employee Modal -->
    <div class="modal" id="editEmployeeModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Employee</h2>
                <button class="close-btn" onclick="closeModal('editEmployeeModal')">&times;</button>
            </div>
            <form id="editEmployeeForm">
                <div class="form-group">
                    <label for="editEmployeeName">Full Name</label>
                    <input type="text" class="form-input" id="editEmployeeName" required>
                </div>
                <div class="form-group">
                    <label for="editEmployeeEmail">Email Address</label>
                    <input type="email" class="form-input" id="editEmployeeEmail" required>
                </div>
                <div class="form-group">
                    <label for="editEmployeePhone">Phone Number</label>
                    <input type="tel" class="form-input" id="editEmployeePhone" required>
                </div>
                <div class="form-group">
                    <label for="editEmployeeRole">Role</label>
                    <select class="form-select" id="editEmployeeRole" required>
                        <option value="">Select Role</option>
                        <option value="admin">Admin</option>
                        <option value="cashier">Cashier</option>
                    </select>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editEmployeeModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Employee</button>
                </div>
            </form>
        </div>
    </div>

    <script>

        // Mobile menu functionality
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

        // Modal functionality
        function openModal(modalId) {
            document.getElementById(modalId).classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
            document.body.style.overflow = 'auto';
        }

        // Close modal when clicking outside
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    closeModal(modal.id);
                }
            });
        });


        // PHP data passed to JavaScript
            const employees = <?php echo json_encode(array_map(function($emp) {
                return [
                    'id' => $emp['user_id'],
                    'name' => $emp['full_name'],
                    'email' => $emp['email'],
                    'phone' => $emp['phone'],
                    'role' => ucfirst($emp['role']),
                    'status' => $emp['status'],
                    'lastLogin' => $emp['last_login'] ? date('M j, Y g:i A', strtotime($emp['last_login'])) : 'Never',
                    'avatar' => 'https://images.unsplash.com/photo-' . (rand(1,5) == 1 ? '1472099645785-5658abf4ff4e' : '1507003211169-0a1dd7228f2d') . '?w=100&h=100&fit=crop&crop=face'
                ];
            }, $employees)); ?>;
        // Employee table functionality
        function renderEmployees(employeeList = employees) {
            const tbody = document.getElementById('employeeTableBody');
            tbody.innerHTML = '';

            employeeList.forEach(employee => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>
                        <div class="employee-info">
                            <img src="${employee.avatar}" alt="${employee.name}" class="employee-avatar">
                            <div class="employee-details">
                                <h4>${employee.name}</h4>
                                <span>${employee.email}</span>
                            </div>
                        </div>
                    </td>
                    <td>${employee.role}</td>
                    <td>
                        <span class="status-badge status-${employee.status}">
                            ${employee.status}
                        </span>
                    </td>
                    <td>${employee.lastLogin}</td>
                    <td>
                        <div class="toggle-switch ${employee.status === 'active' ? 'active' : ''}" 
                             onclick="toggleEmployeeStatus(${employee.id})">
                        </div>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="action-btn btn-edit" onclick="editEmployee(${employee.id})" title="Edit Employee">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="action-btn btn-delete" onclick="deleteEmployee(${employee.id})" title="Delete Employee">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        // Toggle employee status
                        function toggleEmployeeStatus(employeeId) {
                        const formData = new FormData();
                        formData.append('action', 'toggle_status');
                        formData.append('user_id', employeeId);

                        fetch('', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                location.reload();
                            } else {
                                showNotification(data.message, 'error');
                            }
                        });
            }


            // Edit employee
                    function editEmployee(employeeId) {
                        const employee = employees.find(emp => emp.id === employeeId);
                        if (employee) {
                            document.getElementById('editEmployeeName').value = employee.name;
                            document.getElementById('editEmployeeEmail').value = employee.email;
                            document.getElementById('editEmployeePhone').value = employee.phone;
                            document.getElementById('editEmployeeRole').value = employee.role.toLowerCase();
                            
                            // Store the employee ID for updating
                            document.getElementById('editEmployeeForm').dataset.employeeId = employeeId;
                            
                            openModal('editEmployeeModal');
                        }
                    }



        // Delete employee
                function deleteEmployee(employeeId) {
            if (confirm('Are you sure you want to delete this employee?')) {
                const formData = new FormData();
                formData.append('action', 'delete_employee');
                formData.append('user_id', employeeId);

                fetch('', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification(data.message, 'success');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showNotification(data.message, 'error');
                    }
                });
            }
        }

        // Update statistics
        function updateStats() {
            const totalEmployees = employees.length;
            const activeEmployees = employees.filter(emp => emp.status === 'active').length;
            const cashiers = employees.filter(emp => emp.role === 'Cashier').length;
            
            document.querySelector('.stat-card:nth-child(1) h3').textContent = totalEmployees;
            document.querySelector('.stat-card:nth-child(2) h3').textContent = activeEmployees;
            document.querySelector('.stat-card:nth-child(3) h3').textContent = cashiers;
        }

        // Search and filter functionality
        document.getElementById('searchInput').addEventListener('input', filterEmployees);
        document.getElementById('statusFilter').addEventListener('change', filterEmployees);

        function filterEmployees() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const statusFilter = document.getElementById('statusFilter').value;

            let filteredEmployees = employees.filter(employee => {
                const matchesSearch = employee.name.toLowerCase().includes(searchTerm) ||
                                    employee.email.toLowerCase().includes(searchTerm) ||
                                    employee.role.toLowerCase().includes(searchTerm);
                
                const matchesStatus = statusFilter === 'all' || employee.status === statusFilter;
                
                return matchesSearch && matchesStatus;
            });

            renderEmployees(filteredEmployees);
        }

        // Form submissions
                    document.getElementById('addEmployeeForm').addEventListener('submit', function(e) {
                        e.preventDefault();
                        
                        const formData = new FormData();
                        formData.append('action', 'add_employee');
                        formData.append('full_name', document.getElementById('employeeName').value);
                        formData.append('email', document.getElementById('employeeEmail').value);
                        formData.append('phone', document.getElementById('employeePhone').value);
                        formData.append('username', document.getElementById('cashierUsername').value);
                        formData.append('password', document.getElementById('cashierPassword').value);
                        formData.append('role', document.getElementById('employeeRole').value);

                        fetch('', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showNotification(data.message, 'success');
                                closeModal('addEmployeeModal');
                                this.reset();
                                setTimeout(() => location.reload(), 1000);
                            } else {
                                showNotification(data.message, 'error');
                            }
                        });
                    });

        document.getElementById('registerCashierForm').addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData();
                    formData.append('action', 'register_cashier');
                    formData.append('full_name', document.getElementById('cashierName').value);
                    formData.append('email', document.getElementById('cashierEmail').value);
                    formData.append('phone', document.getElementById('cashierPhone').value);
                    formData.append('username', document.getElementById('cashierUsername').value);
                    formData.append('password', document.getElementById('cashierPassword').value);

                    fetch('', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showNotification(data.message, 'success');
                            closeModal('registerCashierModal');
                            this.reset();
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            showNotification(data.message, 'error');
                        }
                    });
                });

        document.getElementById('editEmployeeForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const employeeId = parseInt(this.dataset.employeeId);
            const employee = employees.find(emp => emp.id === employeeId);
            
            if (employee) {
                employee.name = document.getElementById('editEmployeeName').value;
                employee.email = document.getElementById('editEmployeeEmail').value;
                employee.phone = document.getElementById('editEmployeePhone').value;
                employee.role = document.getElementById('editEmployeeRole').value;
                // employee.salary = parseFloat(document.getElementById('editEmployeeSalary').value);
                
                renderEmployees();
                updateStats();
                closeModal('editEmployeeModal');
                
                // Show success message
                showNotification('Employee updated successfully!', 'success');
            }
        });

        // Notification system
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `notification notification-${type}`;
            notification.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check' : 'info'}-circle"></i>
                <span>${message}</span>
            `;
            
            // Add notification styles
            notification.style.cssText = `
                position: fixed;
                top: 2rem;
                right: 2rem;
                background: ${type === 'success' ? 'rgba(34, 197, 94, 0.95)' : type === 'error' ? 'rgba(239, 68, 68, 0.95)' : 'rgba(59, 130, 246, 0.95)'};
                color: white;
                padding: 1rem 1.5rem;
                border-radius: 12px;
                display: flex;
                align-items: center;
                gap: 0.5rem;
                z-index: 1001;
                backdrop-filter: blur(10px);
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
                animation: slideIn 0.3s ease;
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 300);
            }, 3000);
        }

        // Add CSS animations
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            
            @keyframes slideOut {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(100%);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);

        // Initialize the page
        document.addEventListener('DOMContentLoaded', function() {
            renderEmployees();
            updateStats();
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Escape key to close modals
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal.active').forEach(modal => {
                    closeModal(modal.id);
                });
            }
            
            // Ctrl/Cmd + N to add new employee
            if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
                e.preventDefault();
                openModal('addEmployeeModal');
            }
        });

        // Auto-refresh last login times (simulated)
        setInterval(() => {
            const now = new Date();
            const timeString = now.toLocaleString('en-US', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                hour12: true
            });
            
            // Randomly update some employees' last login
            employees.forEach(employee => {
                if (employee.status === 'active' && Math.random() < 0.1) {
                    employee.lastLogin = timeString;
                }
            });
            
            // Re-render if no modals are open
            if (!document.querySelector('.modal.active')) {
                renderEmployees();
            }
        }, 30000); // Update every 30 seconds
    </script>
</body>
</html>