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
                    <li><a href="#"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
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
                <h2>Account Information</h2>
                <form action="update_account.php" method="POST">
                    <div class="form-group">
                        <label for="business_name">Business Name</label>
                        <input type="text" id="business_name" name="business_name" value="John's Emporium" required>
                    </div>
                    <div class="form-group">
                        <label for="name">Your Name</label>
                        <input type="text" id="name" name="name" value="John Admin" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" value="john.admin@example.com" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" value="+233 55 123 4567">
                    </div>
                    <div class="form-group">
                        <label for="industry_type">Industry Type</label>
                        <select id="industry_type" name="industry_type">
                            <option value="retail" selected>Retail</option>
                            <option value="restaurant">Restaurant</option>
                            <option value="service">Service</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="plan">Current Plan</label>
                        <input type="text" id="plan" name="plan" value="Premium - Annual" readonly>
                        <small>Contact support to change your plan.</small>
                    </div>
                    <div class="form-group">
                        <label for="currency_type">Currency Type</label>
                        <select id="currency_type" name="currency_type">
                            <option value="GHS" selected>Ghana Cedi (GHS)</option>
                            <option value="USD">US Dollar (USD)</option>
                            <option value="EUR">Euro (EUR)</option>
                            <option value="GBP">British Pound (GBP)</option>
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
                    <button type="button" class="btn btn-danger" onclick="confirmAccountDeletion()">Delete My Account</button>
                </div>
            </div>

        </main>

        <button class="floating-menu" id="floating-menu">
            <i class="fas fa-bars"></i>
        </button>
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

        // Function to confirm account deletion
        function confirmAccountDeletion() {
            if (confirm("Are you sure you want to permanently delete your account? This action cannot be undone.")) {
                // In a real application, you would make an AJAX call or submit a form to delete the account
                alert("Account deletion initiated. (This is a placeholder, real deletion logic needed)");
                // Example: window.location.href = 'delete_account.php';
            }
        }
    </script>
</body>
</html>