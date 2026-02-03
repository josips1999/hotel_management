<?php
/**
 * Contact Form Page
 * 
 * Contact form where users can send email to the application author
 * Features: Name, Email, Subject, Message, reCAPTCHA validation
 */

// ============================================================================
// PHP CODE - Business Logic (prije HTML-a)
// ============================================================================

require_once('lib/config.php');
require_once('lib/db_connection.php');
require_once('lib/SessionManager.php');
require_once('lib/CSRFToken.php');

$sessionManager = new SessionManager($connection);
$sessionManager->checkRememberMe();
$isLoggedIn = $sessionManager->isLoggedIn();
$username = $sessionManager->getUsername();

// Generate CSRF token before any output (prevents "headers already sent" error)
CSRFToken::generate();

// Page-specific variables for template
$pageTitle = 'Kontakt - Hotel Management';
$currentPage = 'contact';

// Custom CSS for this page
$customCSS = "
    .contact-container {
        max-width: 800px;
        margin: 0 auto;
    }
    .contact-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        padding: 40px;
        margin-bottom: 30px;
    }
    .contact-header {
        text-align: center;
        margin-bottom: 30px;
        color: #667eea;
    }
    .contact-header i {
        font-size: 4rem;
        margin-bottom: 20px;
        color: #677ae6;
    }
    .contact-info {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 30px;
    }
    .contact-info-item {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
    }
    .contact-info-item i {
        font-size: 1.5rem;
        color: #667eea;
        margin-right: 15px;
        width: 30px;
        text-align: center;
    }
    .form-label {
        font-weight: 600;
        color: #495057;
    }
    .form-control:focus {
        border-color: #764ba2;
        box-shadow: 0 0 0 0.25rem rgba(118, 75, 162, 0.25);
    }
    .btn-send {
        background: #677ae6;
        border: none;
        padding: 12px 40px;
        font-size: 1.1rem;
        transition: all 0.3s;
    }
    .btn-send:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }
    .alert-message {
        display: none;
        animation: slideDown 0.3s ease-out;
    }
    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .char-counter {
        font-size: 0.875rem;
        color: #6c757d;
        text-align: right;
        margin-top: 5px;
    }
    .char-counter.warning {
        color: #ffc107;
        font-weight: 600;
    }
    .char-counter.danger {
        color: #dc3545;
        font-weight: 600;
    }
";

// ============================================================================
// HTML OUTPUT - Template (nakon PHP logike)
// ============================================================================
?>
<?php include 'templates/header.php'; ?>

<div class="contact-container">
    <!-- Contact Header -->
    <div class="contact-card">
        <div class="contact-header">
            <i class="bi bi-envelope-heart"></i>
            <h2>Kontaktirajte Nas</h2>
            <p class="text-muted">Imate pitanje ili sugestiju? Pošaljite nam poruku!</p>
        </div>
        
        <!-- Contact Information -->
        <div class="contact-info">
            <h5 class="mb-3"><i class="bi bi-info-circle"></i> Kontakt Informacije</h5>
            <div class="contact-info-item">
                <i class="bi bi-person-circle"></i>
                <div>
                    <strong>Autor:</strong> Hotel Management Team
                </div>
            </div>
            <div class="contact-info-item">
                <i class="bi bi-envelope"></i>
                <div>
                    <strong>Email:</strong> <a href="mailto:<?php echo SMTP_FROM; ?>"><?php echo SMTP_FROM; ?></a>
                </div>
            </div>
            <div class="contact-info-item">
                <i class="bi bi-clock"></i>
                <div>
                    <strong>Radno vrijeme:</strong> Pon-Pet 9:00-17:00
                </div>
            </div>
            <div class="contact-info-item">
                <i class="bi bi-globe"></i>
                <div>
                    <strong>Website:</strong> <a href="index.php" target="_blank">Hotel Management System</a>
                </div>
            </div>
        </div>
        
        <!-- Alert Container -->
        <div id="alertContainer"></div>
        
        <!-- Contact Form -->
        <form id="contactForm" method="POST">
            
            <!-- CSRF Token (Requirement 33) -->
            <?php echo CSRFToken::getField(); ?>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">
                        <i class="bi bi-person"></i> Ime i Prezime *
                    </label>
                    <input 
                        type="text" 
                        class="form-control" 
                        id="name" 
                        name="name" 
                        placeholder="Vaše ime i prezime"
                        required
                        minlength="3"
                        maxlength="100"
                    >
                    <div class="invalid-feedback">
                        Molimo unesite ime i prezime (minimalno 3 znaka).
                    </div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">
                        <i class="bi bi-envelope"></i> Email Adresa *
                    </label>
                    <input 
                        type="email" 
                        class="form-control" 
                        id="email" 
                        name="email" 
                        placeholder="vas.email@example.com"
                        required
                    >
                    <div class="invalid-feedback">
                        Molimo unesite ispravnu email adresu.
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="subject" class="form-label">
                    <i class="bi bi-chat-left-text"></i> Predmet *
                </label>
                <input 
                    type="text" 
                    class="form-control" 
                    id="subject" 
                    name="subject" 
                    placeholder="Ukratko opišite razlog kontakta"
                    required
                    minlength="5"
                    maxlength="200"
                >
                <div class="invalid-feedback">
                    Molimo unesite predmet (minimalno 5 znakova).
                </div>
            </div>
            
            <div class="mb-3">
                <label for="message" class="form-label">
                    <i class="bi bi-card-text"></i> Poruka *
                </label>
                <textarea 
                    class="form-control" 
                    id="message" 
                    name="message" 
                    rows="8"
                    placeholder="Vaša poruka..."
                    required
                    minlength="20"
                    maxlength="2000"
                ></textarea>
                <div class="char-counter">
                    <span id="charCount">0</span> / 2000 znakova
                </div>
                <div class="invalid-feedback">
                    Molimo unesite poruku (minimalno 20 znakova).
                </div>
            </div>
            
            <!-- reCAPTCHA -->
            <div class="mb-3">
                <div class="g-recaptcha" data-sitekey="<?php echo RECAPTCHA_SITE_KEY; ?>"></div>
                <div id="recaptchaError" class="text-danger small mt-2" style="display: none;">
                    Molimo potvrdite da niste robot.
                </div>
            </div>
            
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    <i class="bi bi-shield-check"></i> Vaši podaci su zaštićeni
                </small>
                <button type="submit" class="btn btn-primary btn-send" id="submitBtn">
                    <i class="bi bi-send"></i> Pošalji Poruku
                </button>
            </div>
        </form>
        
        <!-- Additional Information -->
        <div class="mt-4 pt-4 border-top">
            <h6 class="mb-3"><i class="bi bi-question-circle"></i> Često Postavljana Pitanja</h6>
            <div class="accordion" id="faqAccordion">
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                            Koliko dugo traje dobivanje odgovora?
                        </button>
                    </h2>
                    <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Trudimo se odgovoriti na sve upite u roku od 24-48 sati radnim danima.
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                            Mogu li priložiti datoteke?
                        </button>
                    </h2>
                    <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Trenutno prilaganje datoteka nije podržano. Molimo uključite sve potrebne informacije u tekst poruke.
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                            Kako mogu predložiti novu funkcionalnost?
                        </button>
                    </h2>
                    <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Slobodno pošaljite svoje prijedloge kroz ovaj kontakt obrazac. Cijenimo sve sugestije korisnika!
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="text-center">
        <a href="index.php" class="btn btn-outline-light btn-lg">
            <i class="bi bi-house"></i> Povratak
        </a>
    </div>
</div>

<!-- Page-specific JavaScript -->
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script>
    const form = document.getElementById('contactForm');
    const submitBtn = document.getElementById('submitBtn');
    const messageField = document.getElementById('message');
    const charCount = document.getElementById('charCount');
    const alertContainer = document.getElementById('alertContainer');
    
    // Character counter
    messageField.addEventListener('input', function() {
        const length = this.value.length;
        charCount.textContent = length;
        
        const counterDiv = charCount.parentElement;
        counterDiv.classList.remove('warning', 'danger');
        
        if (length > 1800) {
            counterDiv.classList.add('danger');
        } else if (length > 1500) {
            counterDiv.classList.add('warning');
        }
    });
    
    // Form submission
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Validate form
        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            return;
        }
        
        // Check reCAPTCHA
        const recaptchaResponse = grecaptcha.getResponse();
        if (!recaptchaResponse) {
            document.getElementById('recaptchaError').style.display = 'block';
            return;
        }
        
        // Show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Šaljem...';
        
        // Prepare form data
        const formData = new FormData(form);
        formData.append('g-recaptcha-response', recaptchaResponse);
        
        try {
            // Send AJAX request
            const response = await fetch('api/contact_submit.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            // Show message
            showAlert(data.success ? 'success' : 'danger', data.message);
            
            if (data.success) {
                // Reset form
                form.reset();
                form.classList.remove('was-validated');
                grecaptcha.reset();
                charCount.textContent = '0';
                
                // Scroll to alert
                alertContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
            
        } catch (error) {
            console.error('Error:', error);
            showAlert('danger', 'Došlo je do greške pri slanju poruke. Molimo pokušajte ponovo.');
        } finally {
            // Restore button
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-send"></i> Pošalji Poruku';
            document.getElementById('recaptchaError').style.display = 'none';
        }
    });
    
    // Show alert function
    function showAlert(type, message) {
        const alertHTML = `
            <div class="alert alert-${type} alert-dismissible fade show alert-message" role="alert">
                <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        alertContainer.innerHTML = alertHTML;
        
        // Trigger animation
        setTimeout(() => {
            const alert = alertContainer.querySelector('.alert-message');
            if (alert) alert.style.display = 'block';
        }, 10);
        
        // Auto-hide success alerts after 5 seconds
        if (type === 'success') {
            setTimeout(() => {
                const alert = alertContainer.querySelector('.alert');
                if (alert) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }
            }, 5000);
        }
    }
</script>

<?php include 'templates/footer.php'; ?>
