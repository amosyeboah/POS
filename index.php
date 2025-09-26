<?php
session_start();
require_once 'd.php'; // Your DB connection (with $pdo)

$error = '';
$message = '';
$action = $_GET['action'] ?? 'login';

// Redirect if user is already logged in
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    redirectBasedOnRole($_SESSION['role']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['login'])) {
        // === LOGIN LOGIC ===
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'] ?? '';

        if ($email && $password) {
            try {
                // Get user with tenant information - FIXED COLUMN NAMES
                $stmt = $pdo->prepare("
                    SELECT u.user_id, u.username, u.email, u.full_name, u.password_hash, u.role, u.tenant_id,
                           t.business_name, t.setup_complete, t.status as tenant_status 
                    FROM users u 
                    JOIN tenants t ON u.tenant_id = t.tenant_id 
                    WHERE u.email = ? AND u.status = 'active' 
                    LIMIT 1
                ");
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user && password_verify($password, $user['password_hash'])) {
                    // Check if tenant is active
                    if ($user['tenant_status'] !== 'active') {
                        $error = "Your account has been suspended. Please contact support.";
                    } else {
                        // Set session variables - FIXED COLUMN NAMES
                        $_SESSION['user_id'] = $user['user_id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['full_name'] = $user['full_name'];
                        $_SESSION['email'] = $user['email'];
                        $_SESSION['tenant_id'] = $user['tenant_id'];
                        $_SESSION['role'] = $user['role'];
                        $_SESSION['business_name'] = $user['business_name'];
                        $_SESSION['setup_complete'] = $user['setup_complete'];

                        // Update last login - FIXED COLUMN NAME
                        $updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
                        $updateStmt->execute([$user['user_id']]);

                        // Redirect based on setup status and role
                        if ($user['setup_complete'] == 0) {
                            // Only admins can complete setup
                            if ($user['role'] === 'admin') {
                                header('Location: onboarding.php');
                                exit;
                            } else {
                                $error = "Setup is not complete. Please contact your administrator.";
                            }
                        } else {
                            redirectBasedOnRole($user['role']);
                        }
                    }
                } else {
                    $error = "Invalid email or password.";
                }
            } catch (Exception $e) {
                error_log("Login error: " . $e->getMessage());
                $error = "Login failed. Please try again.";
            }
        } else {
            $error = "Please fill in both email and password.";
        }
    } elseif (isset($_POST['signup'])) {
        // === SIGNUP LOGIC ===
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Enhanced validation
        if ($email && $password && $confirm_password) {
            // Password strength validation
            if (strlen($password) < 8) {
                $error = "Password must be at least 8 characters long.";
            } elseif ($password !== $confirm_password) {
                $error = "Passwords do not match.";
            } else {
                try {
                    // Check for duplicate email
                    $check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
                    $check->execute([$email]);
                    if ($check->fetchColumn() > 0) {
                        $error = "Email is already registered.";
                    } else {
                        $pdo->beginTransaction();
                        
                        // Insert tenant with default values - USING AUTO_INCREMENT
                        $default_business_name = "New Business";
                        $stmt = $pdo->prepare("
                            INSERT INTO tenants (business_name, contact_email, setup_complete, status) 
                            VALUES (?, ?, 0, 'active')
                        ");
                        $stmt->execute([$default_business_name, $email]);
                        $tenant_id = $pdo->lastInsertId(); // This will be the AUTO_INCREMENT ID
                        
                        // Create admin user - FIXED COLUMN NAMES
                        $hashed = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                        $username = extractUsernameFromEmail($email); // Create username from email
                        $full_name = "Administrator"; // Default name, can be updated in onboarding
                        
                        $stmt = $pdo->prepare("
                            INSERT INTO users (tenant_id, username, email, full_name, password_hash, role, status) 
                            VALUES (?, ?, ?, ?, ?, 'admin', 'active')
                        ");
                        $stmt->execute([$tenant_id, $username, $email, $full_name, $hashed]);
                        $user_id = $pdo->lastInsertId();
                        
                        $pdo->commit();
                        
                        // Auto login after signup
                        $_SESSION['user_id'] = $user_id;
                        $_SESSION['username'] = $username;
                        $_SESSION['full_name'] = $full_name;
                        $_SESSION['email'] = $email;
                        $_SESSION['tenant_id'] = $tenant_id;
                        $_SESSION['role'] = 'admin';
                        $_SESSION['business_name'] = $default_business_name;
                        $_SESSION['setup_complete'] = 0;
                        
                        header('Location: onboarding.php');
                        exit;
                    }
                } catch (Exception $e) {
                    $pdo->rollBack();
                    error_log("Signup error: " . $e->getMessage());
                    $error = "Signup failed. Please try again.";
                }
            }
        } else {
            $error = "All fields are required.";
        }
    }
}

/**
 * Extract username from email
 */
function extractUsernameFromEmail($email) {
    $username = strtolower(explode('@', $email)[0]);
    // Remove any non-alphanumeric characters except underscore
    $username = preg_replace('/[^a-z0-9_]/', '', $username);
    return substr($username, 0, 50); // Limit to 50 chars as per schema
}

/**
 * Redirect user based on their role
 */
function redirectBasedOnRole($role) {
    switch (strtolower($role)) {
        case 'admin':
            header('Location: admin/index.php');
            exit;
        case 'cashier':
            header('Location: cashier/dashboard.php');
            exit;
        default:
            // Unknown role, logout and redirect to login
            session_destroy();
            header('Location: index.php?error=invalid_role');
            exit;
    }
}

/**
 * Check if user has permission to access a role-specific area
 */
function checkPermission($required_role) {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        header('Location: ../index.php');
        exit;
    }
    
    $user_role = strtolower($_SESSION['role']);
    $required = strtolower($required_role);
    
    // Admin can access everything
    if ($user_role === 'admin') {
        return true;
    }
    
    // Others can only access their specific areas
    if ($user_role !== $required) {
        header('Location: ../index.php?error=access_denied');
        exit;
    }
    
    return true;
}

// Handle error messages from redirects
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'access_denied':
            $error = "You don't have permission to access that area.";
            break;
        case 'invalid_role':
            $error = "Invalid user role. Please contact support.";
            break;
        case 'session_expired':
            $error = "Your session has expired. Please login again.";
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS System - Login</title>
    <link rel="stylesheet" href="css/index.css">
    <style>

    </style>
</head>
<body>
    <div class="auth-container">
        <div class="logo">
            <h1>POS System</h1>
            <p>Point of Sale Management</p>
        </div>

        <div class="form-container">
            <!-- Display PHP errors/messages -->
            <?php if ($error): ?>
                <div class="php-message error">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if ($message): ?>
                <div class="php-message success">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form method="POST" id="loginForm" class="form <?= $action === 'signup' ? 'hidden' : '' ?>">
                <div class="form-group">
                    <label for="loginEmail">Email Address</label>
                    <input type="email" id="loginEmail" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    <div class="error-message" id="loginEmailError"></div>
                </div>

                <div class="form-group">
                    <label for="loginPassword">Password</label>
                    <input type="password" id="loginPassword" name="password" required>
                    <div class="error-message" id="loginPasswordError"></div>
                </div>

                <button type="submit" name="login" class="submit-btn">
                    <span class="spinner"></span>
                    <span class="btn-text">Sign In</span>
                </button>

                <div class="form-switch">
                    Don't have an account? <button type="button" onclick="switchToSignup()">Create Account</button>
                </div>
            </form>

            <!-- Signup Form -->
            <form method="POST" id="signupForm" class="form <?= $action === 'login' ? 'hidden' : '' ?>">
                <div class="form-group">
                    <label for="signupEmail">Email Address</label>
                    <input type="email" id="signupEmail" name="email" required value="<?= $action === 'signup' ? htmlspecialchars($_POST['email'] ?? '') : '' ?>">
                    <div class="error-message" id="signupEmailError"></div>
                </div>

                <div class="form-group">
                    <label for="signupPassword">Password</label>
                    <input type="password" id="signupPassword" name="password" required>
                    <div class="password-strength" id="passwordStrength">
                        <div class="password-strength-bar" id="passwordStrengthBar"></div>
                    </div>
                    <div class="password-strength-text" id="passwordStrengthText"></div>
                    <div class="error-message" id="signupPasswordError"></div>
                </div>

                <div class="form-group">
                    <label for="confirmPassword">Confirm Password</label>
                    <input type="password" id="confirmPassword" name="confirm_password" required>
                    <div class="error-message" id="confirmPasswordError"></div>
                </div>

                <button type="submit" name="signup" class="submit-btn">
                    <span class="spinner"></span>
                    <span class="btn-text">Create Account</span>
                </button>

                <div class="form-switch">
                    Already have an account? <button type="button" onclick="switchToLogin()">Sign In</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Form switching functions
        function switchToLogin() {
            document.getElementById('loginForm').classList.remove('hidden');
            document.getElementById('signupForm').classList.add('hidden');
            document.getElementById('loginEmail').focus();
        }

        function switchToSignup() {
            document.getElementById('signupForm').classList.remove('hidden');
            document.getElementById('loginForm').classList.add('hidden');
            document.getElementById('signupEmail').focus();
        }

        // Email validation helper
        function validateEmail(email) {
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        }

        // Password strength checker
        const signupPassword = document.getElementById('signupPassword');
        const passwordStrength = document.getElementById('passwordStrength');
        const passwordStrengthBar = document.getElementById('passwordStrengthBar');
        const passwordStrengthText = document.getElementById('passwordStrengthText');
        const confirmPassword = document.getElementById('confirmPassword');

        signupPassword.addEventListener('input', (e) => {
            const password = e.target.value;
            if (password.length > 0) {
                passwordStrength.classList.add('show');
                passwordStrengthText.classList.add('show');
                
                const strength = getPasswordStrength(password);
                updatePasswordStrength(strength);
            } else {
                passwordStrength.classList.remove('show');
                passwordStrengthText.classList.remove('show');
            }
        });

        // Password confirmation validation
        confirmPassword.addEventListener('input', validatePasswordMatch);
        signupPassword.addEventListener('input', validatePasswordMatch);

        function validatePasswordMatch() {
            const password = signupPassword.value;
            const confirm = confirmPassword.value;
            const errorElement = document.getElementById('confirmPasswordError');
            
            if (confirm.length > 0 && password !== confirm) {
                showError(confirmPassword, errorElement, 'Passwords do not match');
            } else {
                clearError(confirmPassword, errorElement);
            }
        }

        function getPasswordStrength(password) {
            let score = 0;

            if (password.length >= 8) score++;
            if (password.match(/[a-z]/)) score++;
            if (password.match(/[A-Z]/)) score++;
            if (password.match(/[0-9]/)) score++;
            if (password.match(/[^a-zA-Z0-9]/)) score++;

            switch (score) {
                case 0:
                case 1:
                    return { level: 'weak', text: 'Weak password', class: 'strength-weak' };
                case 2:
                    return { level: 'fair', text: 'Fair password', class: 'strength-fair' };
                case 3:
                case 4:
                    return { level: 'good', text: 'Good password', class: 'strength-good' };
                case 5:
                    return { level: 'strong', text: 'Strong password', class: 'strength-strong' };
                default:
                    return { level: 'weak', text: 'Weak password', class: 'strength-weak' };
            }
        }

        function updatePasswordStrength(strength) {
            passwordStrengthBar.className = `password-strength-bar ${strength.class}`;
            passwordStrengthText.textContent = strength.text;
            passwordStrengthText.style.color = getStrengthColor(strength.level);
        }

        function getStrengthColor(level) {
            const colors = {
                weak: '#ef4444',
                fair: '#f59e0b',
                good: '#3b82f6',
                strong: '#10b981'
            };
            return colors[level];
        }

        function showError(input, errorElement, message) {
            input.classList.add('error');
            errorElement.textContent = message;
            errorElement.classList.add('show');
        }

        function clearError(input, errorElement) {
            input.classList.remove('error');
            errorElement.classList.remove('show');
        }

        // Create floating elements for background animation
        function createFloatingElements() {
            const body = document.body;
            for (let i = 0; i < 15; i++) {
                const element = document.createElement('div');
                element.style.cssText = `
                    position: fixed;
                    width: ${Math.random() * 6 + 4}px;
                    height: ${Math.random() * 6 + 4}px;
                    background: rgba(255, 255, 255, 0.1);
                    border-radius: 50%;
                    left: ${Math.random() * 100}%;
                    top: ${Math.random() * 100}%;
                    animation: float ${Math.random() * 3 + 4}s ease-in-out infinite;
                    animation-delay: ${Math.random() * 2}s;
                    pointer-events: none;
                    z-index: -1;
                `;
                body.appendChild(element);
            }
        }

        // Add floating animation keyframes
        const style = document.createElement('style');
        style.textContent = `
            @keyframes float {
                0%, 100% { transform: translateY(0px) rotate(0deg); }
                50% { transform: translateY(-20px) rotate(180deg); }
            }
        `;
        document.head.appendChild(style);

        // Initialize page
        document.addEventListener('DOMContentLoaded', () => {
            createFloatingElements();
            
            // Focus first visible input
            const firstInput = document.querySelector('.form:not(.hidden) input[type="email"]');
            if (firstInput) firstInput.focus();

            // Add loading state to forms on submit
            document.querySelectorAll('form').forEach(form => {
                form.addEventListener('submit', function() {
                    const submitBtn = this.querySelector('.submit-btn');
                    submitBtn.classList.add('loading');
                });
            });

            // Auto-hide PHP messages after 5 seconds
            const phpMessages = document.querySelectorAll('.php-message');
            phpMessages.forEach(message => {
                setTimeout(() => {
                    message.style.opacity = '0';
                    message.style.transform = 'translateY(-10px)';
                    setTimeout(() => message.remove(), 300);
                }, 5000);
            });
        });
    </script>
</body>
</html>