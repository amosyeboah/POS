<?php
session_start();
require_once 'd.php'; // Make sure this sets up $pdo

// Redirect to login if tenant not logged in
if (!isset($_SESSION['tenant_id'])) {
    header('Location: index.php');
    exit;
}

$tenant_id = $_SESSION['tenant_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $business_name = trim($_POST['business_name']);
    $contact_name = trim($_POST['contact_name']);
    $contact_phone = trim($_POST['contact_phone']);
    $currency_type = $_POST['currency_type'] ?? 'GHS';
    $address = trim($_POST['address']);
    $industry = $_POST['industry'];
    $plan_type = $_POST['plan_type'];
    $setup_complete = 1;

    // Basic validation
    if (!empty($business_name) && !empty($contact_name) && !empty($contact_phone) && !empty($address) && !empty($industry) && !empty($plan_type)) {
        try {
            $stmt = $pdo->prepare("UPDATE tenants SET 
                business_name = ?, 
                contact_name = ?, 
                contact_phone = ?, 
                currency_type = ?, 
                address = ?, 
                industry = ?, 
                plan_type = ?, 
                setup_complete = ?
                WHERE tenant_id = ?");
            
            $stmt->execute([
                $business_name,
                $contact_name,
                $contact_phone,
                $currency_type,
                $address,
                $industry,
                $plan_type,
                $setup_complete,
                $tenant_id
            ]);

            header("Location: ./admin/index.php");
            exit;
        } catch (PDOException $e) {
            $error = "Error saving setup: " . $e->getMessage();
        }
    } else {
        $error = "Please fill in all required fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Your Setup - POS System</title>
   <link rel="stylesheet" href="css/setup.css">
</head>
<body>
    <div class="floating-elements"></div>
    
    <div class="container">
        <div class="header">
            <h1>Complete Your Setup</h1>
            <p>Just a few more details to get you started</p>
        </div>

        <div class="form-container">
            <div class="progress-bar">
                <div class="progress-fill"></div>
            </div>

            <form id="onboardingForm" method="POST" action="onboarding.php">
                <div class="form-group">
                    <label for="businessName">Business Name *</label>
                    <input type="text" id="businessName" name="business_name" maxlength="100" required>
                </div>

                <div class="form-group">
                    <label for="contactName">Contact Person Name *</label>
                    <input type="text" id="contactName" name="contact_name" maxlength="100" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="contactPhone">Contact Phone *</label>
                        <input type="tel" id="contactPhone" name="contact_phone" maxlength="20" required>
                    </div>

                    <div class="form-group">
                        <label for="currencyType">Currency</label>
                        <select id="currencyType" name="currency_type">
                            <option value="GHS" selected>GHS (Ghana Cedi)</option>
                            <option value="USD">USD (US Dollar)</option>
                            <option value="EUR">EUR (Euro)</option>
                            <option value="GBP">GBP (British Pound)</option>
                            <option value="NGN">NGN (Nigerian Naira)</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="address">Business Address *</label>
                    <textarea id="address" name="address" placeholder="Enter your complete business address" required></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="industry">Industry *</label>
                        <select id="industry" name="industry" required>
                            <option value="">Select industry</option>
                            <option value="Restaurant">Restaurant</option>
                            <option value="Retail">Retail Store</option>
                            <option value="Café/Coffee Shop">Café/Coffee Shop</option>
                            <option value="Bar/Pub">Bar/Pub</option>
                            <option value="Salon/Spa">Salon/Spa</option>
                            <option value="Grocery Store">Grocery Store</option>
                            <option value="Fashion/Clothing">Fashion/Clothing</option>
                            <option value="Electronics">Electronics</option>
                            <option value="Pharmacy">Pharmacy</option>
                            <option value="Service Business">Service Business</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="planType">Plan Type *</label>
                        <select id="planType" name="plan_type" required>
                            <option value="free" selected>Free Plan</option>
                            <option value="basic">Basic Plan</option>
                            <option value="pro">Pro Plan</option>
                        </select>
                    </div>
                </div>

                <div class="checkbox-group">
                    <input type="checkbox" id="terms" name="terms" required>
                    <label for="terms">I agree to the Terms of Service and Privacy Policy</label>
                </div>

                <div class="checkbox-group">
                    <input type="checkbox" id="marketing" name="marketing">
                    <label for="marketing">Send me updates about new features and promotions</label>
                </div>

                <button type="submit" class="submit-btn">
                    <span class="btn-text">Complete Setup</span>
                </button>
            </form>
        </div>
    </div>

    <script>
        // Create floating elements
        function createFloatingElements() {
            const container = document.querySelector('.floating-elements');
            for (let i = 0; i < 20; i++) {
                const element = document.createElement('div');
                element.className = 'floating-element';
                element.style.left = Math.random() * 100 + '%';
                element.style.top = Math.random() * 100 + '%';
                element.style.animationDelay = Math.random() * 6 + 's';
                element.style.animationDuration = (Math.random() * 3 + 4) + 's';
                container.appendChild(element);
            }
        }

        // Progress bar animation
        function updateProgress() {
            const form = document.getElementById('onboardingForm');
            const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
            const progressFill = document.querySelector('.progress-fill');
            
            let filledInputs = 0;
            inputs.forEach(input => {
                if (input.type === 'checkbox') {
                    if (input.checked) filledInputs++;
                } else {
                    if (input.value.trim() !== '') filledInputs++;
                }
            });
            
            const progress = (filledInputs / inputs.length) * 100;
            progressFill.style.width = progress + '%';
        }

        // Form validation and submission
        // function handleSubmit(e) {
        //     e.preventDefault();
            
        //     const submitBtn = document.querySelector('.submit-btn');
        //     const btnText = submitBtn.querySelector('.btn-text');
            
        //     // Add loading state
        //     submitBtn.classList.add('loading');
        //     btnText.textContent = 'Setting up...';
            
        //     // Simulate API call
        //     setTimeout(() => {
        //         // Remove loading state
        //         submitBtn.classList.remove('loading');
        //         btnText.textContent = 'Setup Complete!';
                
        //         // Success feedback
        //         setTimeout(() => {
        //             alert('Onboarding completed successfully! Welcome to your POS system.');
        //             // Here you would typically redirect or show success page
        //         }, 500);
        //     }, 2000);
        //     // Submit form to PHP backend
        //     form.submit();
        // }

        function handleSubmit(e) {
    e.preventDefault();
    
    const form = document.getElementById('onboardingForm');
    const submitBtn = document.querySelector('.submit-btn');
    const btnText = submitBtn.querySelector('.btn-text');
    
    // Add loading state
    submitBtn.classList.add('loading');
    btnText.textContent = 'Setting up...';
    
    // Simulate delay, then submit
    setTimeout(() => {
        submitBtn.classList.remove('loading');
        btnText.textContent = 'Setup Complete!';
        
        // Submit form to PHP backend
        form.submit(); // This submits to PHP and triggers the redirect
    }, 2000);
}


        // Real-time validation
        function validateField(field) {
            const value = field.value.trim();
            const isValid = field.checkValidity();
            
            if (field.hasAttribute('required') && !value) {
                field.style.borderColor = '#ef4444';
                return false;
            } else if (!isValid) {
                field.style.borderColor = '#ef4444';
                return false;
            } else {
                field.style.borderColor = '#10b981';
                return true;
            }
        }

        // Phone number formatting
        function formatPhoneNumber(input) {
            let value = input.value.replace(/\D/g, '');
            if (value.length >= 6) {
                value = value.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
            } else if (value.length >= 3) {
                value = value.replace(/(\d{3})(\d{1,3})/, '($1) $2');
            }
            input.value = value;
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            createFloatingElements();
            
            const form = document.getElementById('onboardingForm');
            const phoneInput = document.getElementById('contactPhone');
            
            // Add event listeners
            form.addEventListener('submit', handleSubmit);
            
            // Progress tracking
            form.addEventListener('input', updateProgress);
            form.addEventListener('change', updateProgress);
            
            // Field validation
            form.querySelectorAll('input, select, textarea').forEach(field => {
                field.addEventListener('blur', () => validateField(field));
                field.addEventListener('input', () => {
                    // Reset border color on input
                    field.style.borderColor = '#e5e7eb';
                });
            });
            
            // Phone formatting
            phoneInput.addEventListener('input', () => formatPhoneNumber(phoneInput));
            
            // Initial progress update
            updateProgress();
        });

        // Smooth animations on scroll (for mobile)
        window.addEventListener('scroll', function() {
            const container = document.querySelector('.container');
            const scrolled = window.pageYOffset;
            const parallax = scrolled * 0.5;
            
            container.style.transform = `translateY(${parallax}px)`;
        });
    </script>
</body>
</html>