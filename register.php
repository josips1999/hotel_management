<?php
/**
 * Registration Page - Hotel Management System
 * Allows new users to register with email verification
 * Requires HTTPS for secure data transmission
 */

// Start session FIRST
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once('lib/db_connection.php');
require_once('lib/https_checker.php');
require_once('lib/CSRFToken.php');

// Check if user is already logged in
if (isset($_SESSION['user_id']) && isset($_SESSION['username'])) {
    // User is already logged in, redirect to dashboard
    header('Location: index.php');
    exit;
}

// Force HTTPS for registration page (on production)
HTTPSChecker::requireHTTPSForAuth();
?>
<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registracija korisnika - Hotel Management</title>
    
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
        
        .register-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 40px;
            max-width: 500px;
            width: 100%;
        }
        
        .register-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .register-header h2 {
            color: #333;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .register-header p {
            color: #666;
            margin: 0;
        }
        
        .form-label {
            font-weight: 500;
            color: #555;
            margin-bottom: 8px;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-register {
            background: #677ae6;
            border: none;
            padding: 12px;
            font-weight: 500;
            transition: transform 0.2s;
        }
        
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .back-link a {
            color: #677ae6;
            text-decoration: none;
            font-weight: 500;
        }
        
        .back-link a:hover {
            text-decoration: underline;
        }
        
        /* Username feedback styling */
        #usernameFeedback {
            font-size: 0.875rem;
            margin-top: 5px;
        }
        
        /* Email feedback styling */
        #emailFeedback {
            font-size: 0.875rem;
            margin-top: 5px;
        }
        
        .checking-username, .checking-email {
            color: #6c757d;
        }
        
        .email-available, .username-available {
            color: #28a745;
        }
        
        .email-taken, .username-taken {
            color: #dc3545;
        }
        
        /* Password strength indicator */
        .password-strength {
            height: 5px;
            border-radius: 3px;
            margin-top: 5px;
            transition: all 0.3s;
        }
        
        .strength-weak { background: #dc3545; width: 33%; }
        .strength-medium { background: #ffc107; width: 66%; }
        .strength-strong { background: #28a745; width: 100%; }
    </style>
</head>
<body>
    <!-- HTTPS Status Badge -->
    <?php include('components/https_badge.php'); ?>
    
    <div class="register-card">
        <div class="register-header">
            <h2><i class="bi bi-person-plus-fill"></i> Registracija</h2>
            <p>Kreirajte novi korisnički račun</p>
        </div>
        
        <form id="registerForm" method="POST" action="api/register_user.php" novalidate>
            
            <!-- CSRF Token (Requirement 33) -->
            <?php echo CSRFToken::getField(); ?>
            
            <!-- Username Field with AJAX Validation -->
            <div class="mb-3">
                <label for="username" class="form-label">
                    <i class="bi bi-person"></i> Korisničko ime *
                </label>
                <input 
                    type="text" 
                    class="form-control" 
                    id="username" 
                    name="username" 
                    placeholder="npr. ivan_horvat"
                    required
                >
                <div id="usernameFeedback" class="form-text"></div>
                <small class="form-text text-muted">
                    3-30 znakova: slova, brojevi i donja crta (_)
                </small>
            </div>
            
            <!-- Email Field with AJAX Validation -->
            <div class="mb-3">
                <label for="email" class="form-label">
                    <i class="bi bi-envelope"></i> Email adresa *
                </label>
                <input 
                    type="email" 
                    class="form-control" 
                    id="email" 
                    name="email" 
                    placeholder="npr. ivan@example.com"
                    required
                >
                <div id="emailFeedback" class="form-text"></div>
                <small class="form-text text-muted">
                    Unesite važeću email adresu
                </small>
            </div>
            
            <!-- Password Field -->
            <div class="mb-3">
                <label for="password" class="form-label">
                    <i class="bi bi-lock"></i> Lozinka *
                </label>
                <input 
                    type="password" 
                    class="form-control" 
                    id="password" 
                    name="password" 
                    placeholder="Minimalno 6 znakova"
                    required
                >
                <div class="password-strength" id="passwordStrength"></div>
                <div id="passwordFeedback" class="invalid-feedback">
                    Lozinka mora imati minimalno 6 znakova
                </div>
            </div>
            
            <!-- Confirm Password Field -->
            <div class="mb-3">
                <label for="confirmPassword" class="form-label">
                    <i class="bi bi-lock-fill"></i> Potvrda lozinke *
                </label>
                <input 
                    type="password" 
                    class="form-control" 
                    id="confirmPassword" 
                    name="confirmPassword" 
                    placeholder="Ponovite lozinku"
                    required
                >
                <div id="confirmPasswordFeedback" class="invalid-feedback">
                    Lozinke se ne podudaraju
                </div>
            </div>
            
            <!-- Terms and Conditions -->
            <div class="mb-3 form-check">
                <input 
                    type="checkbox" 
                    class="form-check-input" 
                    id="terms" 
                    name="terms"
                    required
                >
                <label class="form-check-label" for="terms">
                    Prihvaćam <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">uvjete korištenja</a>
                </label>
                <div id="termsFeedback" class="invalid-feedback">
                    Morate prihvatiti uvjete korištenja
                </div>
            </div>
            
            <!-- Google reCAPTCHA v2 -->
            <div class="mb-3">
                <div class="g-recaptcha" data-sitekey="6LcDoV8sAAAAAH1LE1C9G4VG8Am6qwFc9CUo7aXI"></div>
                <div id="recaptchaFeedback" class="invalid-feedback" style="display: none;">
                    Molimo potvrdite da niste robot
                </div>
            </div>
            
            <!-- Submit Button -->
            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-register">
                    <i class="bi bi-check-circle"></i> Registriraj se
                </button>
            </div>
        </form>
        
        <div class="back-link">
            <a href="index.php">
                <i class="bi bi-arrow-left"></i> Povratak na početnu stranicu
            </a>
            <span class="mx-2">|</span>
            <a href="login.php">
                <i class="bi bi-box-arrow-in-right"></i> Već imate račun? Prijavite se
            </a>
        </div>
    </div>
    
    <!-- Terms Modal -->
    <div class="modal fade" id="termsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Uvjeti korištenja</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Ovo je primjer uvjeta korištenja za Hotel Management System.</p>
                    <p>Korisnik se obvezuje da će...</p>
                    <ul>
                        <li>Koristiti sustav u skladu sa zakonom</li>
                        <li>Čuvati svoje pristupne podatke</li>
                        <li>Ne zloupotrebljavati sustav</li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zatvori</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Client Validation JS -->
    <script src="js/client_validation.js"></script>
    
    <!-- Registration Form Handler -->
    <script>
        // Get form elements
        const usernameInput = document.getElementById('username');
        const usernameFeedback = document.getElementById('usernameFeedback');
        const emailInput = document.getElementById('email');
        const emailFeedback = document.getElementById('emailFeedback');
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirmPassword');
        const registerForm = document.getElementById('registerForm');
        
        // Username validation with AJAX (on blur - when user leaves the field)
        usernameInput.addEventListener('blur', function() {
            validateUsernameWithAjax(usernameInput, usernameFeedback);
        });
        
        // Email validation with AJAX
        emailInput.addEventListener('blur', function() {
            validateEmailWithAjax(emailInput, emailFeedback);
        });
        
        // Optional: Real-time username validation (on input - as user types)
        // Uncomment to enable typing validation with debounce
        /*
        let usernameTimeout;
        usernameInput.addEventListener('input', function() {
            clearTimeout(usernameTimeout);
            usernameTimeout = setTimeout(function() {
                validateUsernameWithAjax(usernameInput, usernameFeedback);
            }, 500); // Wait 500ms after user stops typing
        });
        */
        
        // Optional: Real-time email validation with debounce
        let emailTimeout;
        emailInput.addEventListener('input', function() {
            clearTimeout(emailTimeout);
            emailTimeout = setTimeout(function() {
                validateEmailWithAjax(emailInput, emailFeedback);
            }, 600); // Wait 600ms after user stops typing
        });
        
        // Password strength indicator
        passwordInput.addEventListener('input', function() {
            const password = passwordInput.value;
            const strengthBar = document.getElementById('passwordStrength');
            
            if (password.length === 0) {
                strengthBar.className = 'password-strength';
            } else if (password.length < 6) {
                strengthBar.className = 'password-strength strength-weak';
            } else if (password.length < 10) {
                strengthBar.className = 'password-strength strength-medium';
            } else {
                strengthBar.className = 'password-strength strength-strong';
            }
        });
        
        // Password validation
        passwordInput.addEventListener('blur', function() {
            if (!validateMinLength(passwordInput.value, 6, 'Lozinka')) {
                passwordInput.classList.add('is-invalid');
            } else {
                passwordInput.classList.remove('is-invalid');
                passwordInput.classList.add('is-valid');
            }
        });
        
        // Confirm password validation
        confirmPasswordInput.addEventListener('blur', function() {
            if (!validatePasswordMatch(passwordInput.value, confirmPasswordInput.value)) {
                confirmPasswordInput.classList.add('is-invalid');
            } else {
                confirmPasswordInput.classList.remove('is-invalid');
                confirmPasswordInput.classList.add('is-valid');
            }
        });
        
        // Form submission validation
        registerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Reset validation states
            const inputs = registerForm.querySelectorAll('.form-control');
            inputs.forEach(input => {
                input.classList.remove('is-invalid', 'is-valid');
            });
            
            let isValid = true;
            
            // Validate all fields
            if (!validateUsernameFormat(usernameInput.value)) {
                usernameInput.classList.add('is-invalid');
                usernameFeedback.textContent = '✗ Nevažeće korisničko ime';
                usernameFeedback.className = 'text-danger';
                usernameFeedback.style.display = 'block';
                isValid = false;
            }
            
            if (!validateEmail(emailInput.value)) {
                emailInput.classList.add('is-invalid');
                emailFeedback.textContent = '✗ Nevažeća email adresa';
                emailFeedback.className = 'text-danger';
                emailFeedback.style.display = 'block';
                isValid = false;
            }
            
            if (!validateMinLength(passwordInput.value, 6, 'Lozinka')) {
                passwordInput.classList.add('is-invalid');
                isValid = false;
            }
            
            if (!validatePasswordMatch(passwordInput.value, confirmPasswordInput.value)) {
                confirmPasswordInput.classList.add('is-invalid');
                isValid = false;
            }
            
            if (!document.getElementById('terms').checked) {
                document.getElementById('terms').classList.add('is-invalid');
                document.getElementById('termsFeedback').style.display = 'block';
                isValid = false;
            }
            
            // Validate reCAPTCHA
            const recaptchaResponse = grecaptcha.getResponse();
            const recaptchaFeedback = document.getElementById('recaptchaFeedback');
            if (!recaptchaResponse) {
                recaptchaFeedback.style.display = 'block';
                isValid = false;
            } else {
                recaptchaFeedback.style.display = 'none';
            }
            
            // If all validations pass, check username and email availability one more time
            if (isValid) {
                // Check both username and email
                Promise.all([
                    checkEmailAvailability(emailInput.value),
                    checkUsernameAvailabilityPromise(usernameInput.value)
                ]).then(results => {
                    const emailResponse = results[0];
                    const usernameResponse = results[1];
                    
                    let canSubmit = true;
                    
                    if (!emailResponse.available || !emailResponse.valid) {
                        emailInput.classList.add('is-invalid');
                        emailFeedback.textContent = '✗ ' + emailResponse.message;
                        emailFeedback.className = 'text-danger';
                        emailFeedback.style.display = 'block';
                        canSubmit = false;
                    }
                    
                    if (!usernameResponse.available || !usernameResponse.valid) {
                        usernameInput.classList.add('is-invalid');
                        usernameFeedback.textContent = '✗ ' + usernameResponse.message;
                        usernameFeedback.className = 'text-danger';
                        usernameFeedback.style.display = 'block';
                        canSubmit = false;
                    }
                    
                    if (canSubmit) {
                        submitRegistrationForm();
                    }
                }).catch(error => {
                    alert('Greška pri provjeri podataka. Pokušajte ponovno.');
                });
            }
        });
        
        // Submit registration form via AJAX
        async function submitRegistrationForm() {
            const formData = new FormData(registerForm);
            
            // Disable submit button
            const submitBtn = registerForm.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Registriram...';
            
            try {
                const response = await fetch('api/register_user.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Show success message
                    alert(data.message);
                    
                    // Redirect to verification page
                    window.location.href = 'verify.php';
                } else {
                    // Show error
                    alert(data.message + '\n' + (data.errors ? data.errors.join('\n') : ''));
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            } catch (error) {
                alert('Greška pri registraciji. Pokušajte ponovno.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        }
        
        // Email AJAX validation function
        async function validateEmailWithAjax(emailInput, feedbackDiv) {
            const email = emailInput.value.trim();
            
            // Show checking message
            feedbackDiv.textContent = '⟳ Provjeravam...';
            feedbackDiv.className = 'form-text checking-email';
            feedbackDiv.style.display = 'block';
            emailInput.classList.remove('is-valid', 'is-invalid');
            
            if (email === '') {
                feedbackDiv.style.display = 'none';
                return;
            }
            
            try {
                const response = await fetch(`api/check_email.php?email=${encodeURIComponent(email)}`);
                const data = await response.json();
                
                if (data.valid) {
                    if (data.available) {
                        // Email is available
                        feedbackDiv.textContent = '✓ ' + data.message;
                        feedbackDiv.className = 'form-text email-available';
                        emailInput.classList.remove('is-invalid');
                        emailInput.classList.add('is-valid');
                    } else {
                        // Email already taken
                        feedbackDiv.textContent = '✗ ' + data.message;
                        feedbackDiv.className = 'form-text email-taken';
                        emailInput.classList.remove('is-valid');
                        emailInput.classList.add('is-invalid');
                    }
                } else {
                    // Invalid email format
                    feedbackDiv.textContent = '✗ ' + data.message;
                    feedbackDiv.className = 'form-text email-taken';
                    emailInput.classList.remove('is-valid');
                    emailInput.classList.add('is-invalid');
                }
            } catch (error) {
                feedbackDiv.textContent = '✗ Greška pri provjeri email adrese';
                feedbackDiv.className = 'form-text text-danger';
            }
        }
        
        // Check email availability (returns Promise for form submission)
        function checkEmailAvailability(email) {
            return fetch(`api/check_email.php?email=${encodeURIComponent(email)}`)
                .then(response => response.json());
        }
        
        // Wrapper for checkUsernameAvailability to return Promise
        function checkUsernameAvailabilityPromise(username) {
            return new Promise((resolve, reject) => {
                checkUsernameAvailability(username, (response) => {
                    resolve(response);
                });
            });
        }
    </script>
</body>
</html>
