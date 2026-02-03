<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikacija računa - Hotel Management</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .verify-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 40px;
            max-width: 500px;
            width: 100%;
        }
        
        .verify-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .verify-header i {
            font-size: 64px;
            color: #667eea;
            margin-bottom: 20px;
        }
        
        .verify-header h2 {
            color: #333;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .verify-header p {
            color: #666;
            margin: 0;
        }
        
        .code-input-group {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin: 30px 0;
        }
        
        .code-digit {
            width: 50px;
            height: 60px;
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            border: 2px solid #ddd;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .code-digit:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
            outline: none;
        }
        
        .code-digit.filled {
            border-color: #28a745;
            background: #f0fff4;
        }
        
        .btn-verify {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px;
            font-weight: 500;
            transition: transform 0.2s;
        }
        
        .btn-verify:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-verify:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .resend-section {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .resend-timer {
            color: #999;
            font-size: 14px;
            margin: 10px 0;
        }
        
        .alert-custom {
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
            border-width: 0.15em;
        }
    </style>
</head>
<body>
    <?php
    session_start();
    
    // Check if user came from registration
    if (!isset($_SESSION['pending_verification_email'])) {
        // Redirect to register page if no pending verification
        header('Location: register.php');
        exit;
    }
    
    $email = $_SESSION['pending_verification_email'];
    $username = $_SESSION['pending_verification_username'] ?? 'Korisnik';
    ?>
    
    <div class="verify-card">
        <div class="verify-header">
            <i class="bi bi-envelope-check"></i>
            <h2>Verifikacija računa</h2>
            <p>Unesite 6-znamenkasti kod poslan na:<br><strong><?php echo htmlspecialchars($email); ?></strong></p>
        </div>
        
        <!-- Alert messages -->
        <div id="alertContainer"></div>
        
        <!-- Verification Code Form -->
        <form id="verifyForm">
            <div class="code-input-group">
                <input type="text" class="form-control code-digit" id="digit1" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off" autofocus>
                <input type="text" class="form-control code-digit" id="digit2" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off">
                <input type="text" class="form-control code-digit" id="digit3" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off">
                <input type="text" class="form-control code-digit" id="digit4" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off">
                <input type="text" class="form-control code-digit" id="digit5" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off">
                <input type="text" class="form-control code-digit" id="digit6" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off">
            </div>
            
            <input type="hidden" id="email" value="<?php echo htmlspecialchars($email); ?>">
            
            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-verify" id="verifyBtn">
                    <span id="btnText"><i class="bi bi-check-circle"></i> Potvrdi kod</span>
                    <span id="btnSpinner" class="d-none">
                        <span class="spinner-border spinner-border-sm" role="status"></span> Provjeravam...
                    </span>
                </button>
            </div>
        </form>
        
        <!-- Resend Section -->
        <div class="resend-section">
            <p class="text-muted mb-2">Niste primili kod?</p>
            <button class="btn btn-link" id="resendBtn">
                <i class="bi bi-arrow-clockwise"></i> Pošalji novi kod
            </button>
            <div class="resend-timer" id="resendTimer"></div>
        </div>
        
        <div class="text-center mt-3">
            <a href="register.php" class="text-muted">
                <i class="bi bi-arrow-left"></i> Povratak na registraciju
            </a>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Verification Script -->
    <script>
        // Get all digit inputs
        const digitInputs = [
            document.getElementById('digit1'),
            document.getElementById('digit2'),
            document.getElementById('digit3'),
            document.getElementById('digit4'),
            document.getElementById('digit5'),
            document.getElementById('digit6')
        ];
        
        const verifyForm = document.getElementById('verifyForm');
        const verifyBtn = document.getElementById('verifyBtn');
        const btnText = document.getElementById('btnText');
        const btnSpinner = document.getElementById('btnSpinner');
        const resendBtn = document.getElementById('resendBtn');
        const resendTimer = document.getElementById('resendTimer');
        const alertContainer = document.getElementById('alertContainer');
        
        let resendCooldown = 60; // 60 seconds cooldown
        let cooldownInterval;
        
        // Auto-focus next input when digit is entered
        digitInputs.forEach((input, index) => {
            input.addEventListener('input', function(e) {
                // Only allow numbers
                this.value = this.value.replace(/[^0-9]/g, '');
                
                if (this.value.length === 1) {
                    this.classList.add('filled');
                    // Move to next input
                    if (index < digitInputs.length - 1) {
                        digitInputs[index + 1].focus();
                    } else {
                        // All digits entered, submit form
                        verifyForm.dispatchEvent(new Event('submit'));
                    }
                } else {
                    this.classList.remove('filled');
                }
            });
            
            // Handle backspace
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace' && this.value === '' && index > 0) {
                    digitInputs[index - 1].focus();
                    digitInputs[index - 1].value = '';
                    digitInputs[index - 1].classList.remove('filled');
                }
            });
            
            // Handle paste
            input.addEventListener('paste', function(e) {
                e.preventDefault();
                const pastedData = e.clipboardData.getData('text').replace(/[^0-9]/g, '');
                
                if (pastedData.length === 6) {
                    for (let i = 0; i < 6; i++) {
                        digitInputs[i].value = pastedData[i];
                        digitInputs[i].classList.add('filled');
                    }
                    // Auto-submit after paste
                    setTimeout(() => {
                        verifyForm.dispatchEvent(new Event('submit'));
                    }, 300);
                }
            });
        });
        
        // Form submission
        verifyForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Get verification code
            const code = digitInputs.map(input => input.value).join('');
            
            if (code.length !== 6) {
                showAlert('Molimo unesite svih 6 znamenki', 'warning');
                return;
            }
            
            // Disable button and show spinner
            verifyBtn.disabled = true;
            btnText.classList.add('d-none');
            btnSpinner.classList.remove('d-none');
            
            // Send verification request
            try {
                const response = await fetch('api/verify_code.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `email=${encodeURIComponent(document.getElementById('email').value)}&code=${code}`
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showAlert(data.message, 'success');
                    // Redirect to login or index page after 2 seconds
                    setTimeout(() => {
                        window.location.href = 'index.php';
                    }, 2000);
                } else {
                    showAlert(data.message, 'danger');
                    // Clear inputs
                    digitInputs.forEach(input => {
                        input.value = '';
                        input.classList.remove('filled');
                    });
                    digitInputs[0].focus();
                }
            } catch (error) {
                showAlert('Greška pri verifikaciji. Pokušajte ponovno.', 'danger');
            } finally {
                // Re-enable button
                verifyBtn.disabled = false;
                btnText.classList.remove('d-none');
                btnSpinner.classList.add('d-none');
            }
        });
        
        // Resend code
        resendBtn.addEventListener('click', async function() {
            if (resendBtn.disabled) return;
            
            resendBtn.disabled = true;
            resendBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Šaljem...';
            
            try {
                const response = await fetch('api/resend_code.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `email=${encodeURIComponent(document.getElementById('email').value)}`
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showAlert(data.message, 'success');
                    startResendCooldown();
                } else {
                    showAlert(data.message, 'danger');
                    resendBtn.disabled = false;
                    resendBtn.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Pošalji novi kod';
                }
            } catch (error) {
                showAlert('Greška pri slanju koda. Pokušajte ponovno.', 'danger');
                resendBtn.disabled = false;
                resendBtn.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Pošalji novi kod';
            }
        });
        
        // Start resend cooldown
        function startResendCooldown() {
            resendCooldown = 60;
            resendBtn.disabled = true;
            resendBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Pričekajte...';
            
            cooldownInterval = setInterval(() => {
                resendCooldown--;
                resendTimer.textContent = `Novi kod možete zatražiti za ${resendCooldown}s`;
                
                if (resendCooldown <= 0) {
                    clearInterval(cooldownInterval);
                    resendBtn.disabled = false;
                    resendBtn.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Pošalji novi kod';
                    resendTimer.textContent = '';
                }
            }, 1000);
        }
        
        // Show alert message
        function showAlert(message, type) {
            const alert = document.createElement('div');
            alert.className = `alert alert-${type} alert-custom alert-dismissible fade show`;
            alert.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            alertContainer.innerHTML = '';
            alertContainer.appendChild(alert);
        }
        
        // Auto-focus first input on load
        digitInputs[0].focus();
    </script>
</body>
</html>
