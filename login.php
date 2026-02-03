<?php
/**
 * Login Page - Hotel Management System
 * User authentication with reCAPTCHA
 * Requires HTTPS for secure authentication
 * Protected with CSRF token (Requirement 33)
 */

// Start session FIRST
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once('lib/https_checker.php');
require_once('lib/CSRFToken.php');


// Force HTTPS for login page (on production)
HTTPSChecker::requireHTTPSForAuth();
?>
<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prijava - Hotel Management</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Google reCAPTCHA v2 -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    
    <style>
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 40px;
            max-width: 450px;
            width: 100%;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-header i {
            font-size: 64px;
            color: #677ae6;
            margin-bottom: 20px;
        }
        
        .login-header h2 {
            color: #333;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .login-header p {
            color: #666;
            margin: 0;
        }
        
        .form-label {
            font-weight: 500;
            color: #555;
            margin-bottom: 8px;
        }
        
        .form-control:focus {
            border-color: #677ae6;
            box-shadow: 0 0 0 0.2rem rgba(103, 122, 230, 0.25);
        }
        
        .btn-login {
            background: #677ae6;
            border: none;
            padding: 12px;
            font-weight: 500;
            transition: transform 0.2s;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .divider {
            text-align: center;
            margin: 20px 0;
            color: #999;
            position: relative;
        }
        
        .divider::before,
        .divider::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 45%;
            height: 1px;
            background: #ddd;
        }
        
        .divider::before {
            left: 0;
        }
        
        .divider::after {
            right: 0;
        }
        
        .links-section {
            text-align: center;
            margin-top: 20px;
        }
        
        .links-section a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }
        
        .links-section a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <?php
    // Check for logout message
    $message = isset($_GET['message']) ? htmlspecialchars($_GET['message']) : '';
    ?>
    
    <!-- HTTPS Status Badge -->
    <?php include('components/https_badge.php'); ?>
    
    <div class="login-card">
        <div class="login-header">
            <i class="bi bi-box-arrow-in-right"></i>
            <h2>Dobrodošli natrag!</h2>
            <p>Prijavite se u svoj račun</p>
        </div>
        
        <!-- Alert messages -->
        <div id="alertContainer">
            <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Login Form -->
        <form id="loginForm">
            
            <!-- CSRF Token (Requirement 33) -->
            <?php echo CSRFToken::getField(); ?>
            
            <!-- Username/Email Field -->
            <div class="mb-3">
                <label for="usernameOrEmail" class="form-label">
                    <i class="bi bi-person-circle"></i> Korisničko ime ili Email
                </label>
                <input 
                    type="text" 
                    class="form-control" 
                    id="usernameOrEmail" 
                    name="usernameOrEmail" 
                    placeholder="npr. ivan_horvat ili ivan@example.com"
                    required
                    autocomplete="username"
                >
                <div class="invalid-feedback">
                    Unesite korisničko ime ili email adresu
                </div>
            </div>
            
            <!-- Password Field -->
            <div class="mb-3">
                <label for="password" class="form-label">
                    <i class="bi bi-lock-fill"></i> Lozinka
                </label>
                <div class="input-group">
                    <input 
                        type="password" 
                        class="form-control" 
                        id="password" 
                        name="password" 
                        placeholder="Vaša lozinka"
                        required
                        autocomplete="current-password"
                    >
                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                        <i class="bi bi-eye" id="toggleIcon"></i>
                    </button>
                </div>
                <div class="invalid-feedback">
                    Unesite lozinku
                </div>
            </div>
            
            <!-- Remember Me & Forgot Password -->
            <div class="mb-3 d-flex justify-content-between align-items-center">
                <div class="form-check">
                    <input 
                        type="checkbox" 
                        class="form-check-input" 
                        id="rememberMe" 
                        name="rememberMe"
                    >
                    <label class="form-check-label" for="rememberMe">
                        Zapamti me
                    </label>
                </div>
                <a href="#" class="text-muted" style="font-size: 14px;">
                    Zaboravili lozinku?
                </a>
            </div>
            
            <!-- Google reCAPTCHA v2 -->
            <div class="mb-3">
                <div class="g-recaptcha" data-sitekey="6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI"></div>
                <div id="recaptchaFeedback" class="invalid-feedback" style="display: none;">
                    Molimo potvrdite da niste robot
                </div>
            </div>
            
            <!-- Submit Button -->
            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-login" id="loginBtn">
                    <span id="btnText"><i class="bi bi-box-arrow-in-right"></i> Prijavi se</span>
                    <span id="btnSpinner" class="d-none">
                        <span class="spinner-border spinner-border-sm" role="status"></span> Prijavljujem...
                    </span>
                </button>
            </div>
        </form>
        
        <div class="divider">ili</div>
        
        <div class="links-section">
            <p class="mb-2">Nemate račun?</p>
            <a href="register.php">
                <i class="bi bi-person-plus"></i> Registrirajte se besplatno
            </a>
            <div class="mt-3">
                <a href="index.php" class="text-muted">
                    <i class="bi bi-house"></i> Povratak na početnu stranicu
                </a>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Login Script -->
    <script>
        const loginForm = document.getElementById('loginForm');
        const loginBtn = document.getElementById('loginBtn');
        const btnText = document.getElementById('btnText');
        const btnSpinner = document.getElementById('btnSpinner');
        const alertContainer = document.getElementById('alertContainer');
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('toggleIcon');
        
        // Toggle password visibility
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.type === 'password' ? 'text' : 'password';
            passwordInput.type = type;
            
            if (type === 'password') {
                toggleIcon.classList.remove('bi-eye-slash');
                toggleIcon.classList.add('bi-eye');
            } else {
                toggleIcon.classList.remove('bi-eye');
                toggleIcon.classList.add('bi-eye-slash');
            }
        });
        
        // Form submission
        loginForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Clear previous errors
            const inputs = loginForm.querySelectorAll('.form-control');
            inputs.forEach(input => {
                input.classList.remove('is-invalid');
            });
            alertContainer.innerHTML = '';
            
            // Validate inputs
            const usernameOrEmail = document.getElementById('usernameOrEmail').value.trim();
            const password = passwordInput.value;
            const rememberMe = document.getElementById('rememberMe').checked;
            const recaptchaResponse = grecaptcha.getResponse();
            
            let isValid = true;
            
            if (!usernameOrEmail) {
                document.getElementById('usernameOrEmail').classList.add('is-invalid');
                isValid = false;
            }
            
            if (!password) {
                passwordInput.classList.add('is-invalid');
                isValid = false;
            }
            
            if (!recaptchaResponse) {
                document.getElementById('recaptchaFeedback').style.display = 'block';
                isValid = false;
            } else {
                document.getElementById('recaptchaFeedback').style.display = 'none';
            }
            
            if (!isValid) {
                return;
            }
            
            // Disable button and show spinner
            loginBtn.disabled = true;
            btnText.classList.add('d-none');
            btnSpinner.classList.remove('d-none');
            
            // Submit login request
            try {
                const formData = new URLSearchParams();
                formData.append('usernameOrEmail', usernameOrEmail);
                formData.append('password', password);
                formData.append('rememberMe', rememberMe ? '1' : '0');
                formData.append('g-recaptcha-response', recaptchaResponse);
                
                // Add CSRF token
                const csrfToken = document.querySelector('input[name="csrf_token"]').value;
                formData.append('csrf_token', csrfToken);
                
                const response = await fetch('api/login.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: formData.toString()
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showAlert(data.message, 'success');
                    // Redirect to index page after 1 second
                    setTimeout(() => {
                        window.location.href = 'index.php';
                    }, 1000);
                } else {
                    showAlert(data.message, 'danger');
                    // Reset reCAPTCHA
                    grecaptcha.reset();
                }
            } catch (error) {
                showAlert('Greška pri prijavi. Pokušajte ponovno.', 'danger');
                grecaptcha.reset();
            } finally {
                // Re-enable button
                loginBtn.disabled = false;
                btnText.classList.remove('d-none');
                btnSpinner.classList.add('d-none');
            }
        });
        
        // Show alert message
        function showAlert(message, type) {
            const alert = document.createElement('div');
            alert.className = `alert alert-${type} alert-dismissible fade show`;
            alert.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            alertContainer.innerHTML = '';
            alertContainer.appendChild(alert);
        }
    </script>
</body>
</html>
