<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once './db_connect.php';

$error = '';
$success = '';

// Redirect if already logged in
// if (isset($_SESSION['user_id'])) {
//     if ($_SESSION['role'] === 'admin') {
//         header('Location: admin/index.php');
//     } else {
//         header('Location: cashier/pos.php');
//     }
//     exit;
// }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $tenant_code = trim($_POST['tenant_code']);
    
    if (empty($username) || empty($password) || empty($tenant_code)) {
        $error = "All fields are required.";
    } else {
        try {
            // First, find the tenant by business name or a unique code
            $tenant_stmt = $pdo->prepare("SELECT tenant_id, business_name FROM tenants WHERE business_name = ? OR tenant_id = ? AND status = 'active'");
            $tenant_stmt->execute([$tenant_code, $tenant_code]);
            $tenant = $tenant_stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$tenant) {
                $error = "Invalid business code.";
            } else {
                // Now check for user credentials within this tenant
                $user_stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND tenant_id = ? AND status = 'active'");
                $user_stmt->execute([$username, $tenant['tenant_id']]);
                $user = $user_stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user && password_verify($password, $user['password_hash'])) {
                    // Login successful
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['tenant_id'] = $user['tenant_id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['business_name'] = $tenant['business_name'];
                    
                    // Update last login
                    $update_login = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
                    $update_login->execute([$user['user_id']]);
                    
                    // Redirect based on role
                    if ($user['role'] === 'admin') {
                        header('Location: admin/index.php');
                    } else {
                        header('Location: cashier/pos.php');
                    }
                    exit;
                } else {
                    $error = "Invalid username or password.";
                }
            }
        } catch (PDOException $e) {
            $error = "Login failed. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cashier Login | POS System</title>
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
            background: linear-gradient(135deg, #dddfe8ff 0%, #19171bff 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            width: 100%;
            max-width: 900px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            min-height: 600px;
        }

        .login-left {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .login-left::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            animation: float 20s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        .brand {
            position: relative;
            z-index: 1;
        }

        .brand-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            display: block;
        }

        .brand h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .brand p {
            font-size: 1.1rem;
            opacity: 0.9;
            line-height: 1.6;
        }

        .features {
            margin-top: 40px;
            position: relative;
            z-index: 1;
        }

        .feature {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            font-size: 1rem;
        }

        .feature i {
            margin-right: 12px;
            font-size: 1.2rem;
        }

        .login-right {
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .login-header h2 {
            color: #2c3e50;
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .login-header p {
            color: #7f8c8d;
            font-size: 1rem;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .form-control {
            width: 100%;
            padding: 15px 20px;
            padding-left: 50px;
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background-color: #f8f9fa;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
            background-color: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #7f8c8d;
            font-size: 1.1rem;
        }

        .form-group.has-icon {
            position: relative;
        }

        .form-group.has-icon .form-icon {
            top: calc(50% + 12px);
        }

        .btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }

        .btn:active {
            transform: translateY(0);
        }

        .alert {
            padding: 15px 20px;
            margin-bottom: 25px;
            border-radius: 10px;
            font-size: 0.9rem;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .login-footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 30px;
            border-top: 1px solid #e1e5e9;
        }

        .login-footer p {
            color: #7f8c8d;
            font-size: 0.9rem;
        }

        .login-footer a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }

        .login-footer a:hover {
            text-decoration: underline;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .login-container {
                grid-template-columns: 1fr;
                max-width: 400px;
            }

            .login-left {
                padding: 40px 30px;
                text-align: center;
            }

            .login-right {
                padding: 40px 30px;
            }

            .brand h1 {
                font-size: 2rem;
            }

            .brand-icon {
                font-size: 3rem;
            }

            .features {
                display: none;
            }
        }

        /* Loading animation */
        .btn.loading {
            position: relative;
            color: transparent;
        }

        .btn.loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            top: 50%;
            left: 50%;
            margin-left: -10px;
            margin-top: -10px;
            border: 2px solid transparent;
            border-top-color: #ffffff;
            border-radius: 50%;
            animation: spin 1s ease infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Left Side - Branding -->
        <div class="login-left">
            <div class="brand">
                <i class="fas fa-cash-register brand-icon"></i>
                <h1>MobPOS</h1>
                <p>Modern Point of Sale System for your business. Fast, reliable, and easy to use.</p>
            </div>
            
            <div class="features">
                <div class="feature">
                    <i class="fas fa-lightning-bolt"></i>
                    <span>Lightning Fast Transactions</span>
                </div>
                <div class="feature">
                    <i class="fas fa-chart-line"></i>
                    <span>Real-time Sales Analytics</span>
                </div>
                <div class="feature">
                    <i class="fas fa-mobile-alt"></i>
                    <span>Mobile Responsive Design</span>
                </div>
                <div class="feature">
                    <i class="fas fa-shield-alt"></i>
                    <span>Secure & Reliable</span>
                </div>
            </div>
        </div>

        <!-- Right Side - Login Form -->
        <div class="login-right">
            <div class="login-header">
                <h2>Welcome Back</h2>
                <p>Sign in to access your POS system</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <form method="POST" id="loginForm">
                <div class="form-group has-icon">
                    <label for="tenant_code">Business Code</label>
                    <i class="fas fa-building form-icon"></i>
                    <input type="text" 
                           class="form-control" 
                           id="tenant_code" 
                           name="tenant_code" 
                           placeholder="Enter your business code"
                           value="<?php echo htmlspecialchars($_POST['tenant_code'] ?? ''); ?>" 
                           required>
                </div>

                <div class="form-group has-icon">
                    <label for="username">Username</label>
                    <i class="fas fa-user form-icon"></i>
                    <input type="text" 
                           class="form-control" 
                           id="username" 
                           name="username" 
                           placeholder="Enter your username"
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" 
                           required>
                </div>

                <div class="form-group has-icon">
                    <label for="password">Password</label>
                    <i class="fas fa-lock form-icon"></i>
                    <input type="password" 
                           class="form-control" 
                           id="password" 
                           name="password" 
                           placeholder="Enter your password" 
                           required>
                </div>

                <button type="submit" class="btn" id="loginBtn">
                    <i class="fas fa-sign-in-alt"></i> Sign In
                </button>
            </form>

            <div class="login-footer">
                <p>Need help? Contact your system administrator</p>
                <p><a href="admin_login.php">Admin Login</a> | <a href="tenant_register.php">Register Business</a></p>
            </div>
        </div>
    </div>

    <script>
        // Form submission with loading state
        document.getElementById('loginForm').addEventListener('submit', function() {
            const btn = document.getElementById('loginBtn');
            btn.classList.add('loading');
            btn.disabled = true;
        });

        // Auto-focus first empty field
        window.addEventListener('load', function() {
            const fields = ['tenant_code', 'username', 'password'];
            for (let field of fields) {
                const element = document.getElementById(field);
                if (!element.value) {
                    element.focus();
                    break;
                }
            }
        });

        // Enter key navigation between fields
        document.querySelectorAll('.form-control').forEach((input, index, inputs) => {
            input.addEventListener('keypress', function(e) {
                if (e.key === 'Enter' && index < inputs.length - 1) {
                    e.preventDefault();
                    inputs[index + 1].focus();
                }
            });
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

        // Add some visual feedback for form interactions
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'translateY(-2px)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'translateY(0)';
            });
        });
    </script>
</body>
</html>