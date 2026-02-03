<?php
/**
 * Contact Form Submission API
 * 
 * Handles contact form submissions and sends email to the application author
 * 
 * Method: POST
 * Parameters: name, email, subject, message, g-recaptcha-response
 * Response: JSON
 */

// Start session FIRST before any output
session_start();

// THEN set headers
header('Content-Type: application/json');

require_once('../lib/db_connection.php');
require_once('../lib/config.php');
require_once('../lib/RecaptchaValidator.php');
require_once('../lib/CSRFToken.php');

// Function to send email
function sendContactEmail($name, $email, $subject, $message) {
    $to = "jskoko53@gmail.com"; // Application author email
    $fromEmail = $email;
    $fromName = $name;
    
    // Email subject
    $emailSubject = "[Hotel Management Contact] " . $subject;
    
    // Email body (HTML)
    $emailBody = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 10px 10px 0 0; }
            .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
            .info-box { background: white; padding: 15px; margin: 15px 0; border-left: 4px solid #667eea; border-radius: 5px; }
            .footer { text-align: center; padding: 20px; color: #6c757d; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>📧 Nova Poruka s Kontakt Obrasca</h2>
            </div>
            <div class='content'>
                <div class='info-box'>
                    <p><strong>👤 Ime:</strong> " . htmlspecialchars($name) . "</p>
                    <p><strong>📧 Email:</strong> <a href='mailto:" . htmlspecialchars($email) . "'>" . htmlspecialchars($email) . "</a></p>
                    <p><strong>📌 Predmet:</strong> " . htmlspecialchars($subject) . "</p>
                    <p><strong>🕐 Datum/Vrijeme:</strong> " . date('d.m.Y H:i:s') . "</p>
                </div>
                
                <h3>💬 Poruka:</h3>
                <div class='info-box'>
                    <p>" . nl2br(htmlspecialchars($message)) . "</p>
                </div>
                
                <p style='margin-top: 30px; padding-top: 20px; border-top: 1px solid #dee2e6;'>
                    <small>Ova poruka je poslana s kontakt obrasca Hotel Management System aplikacije.</small>
                </p>
            </div>
            <div class='footer'>
                <p>Hotel Management System &copy; " . date('Y') . "</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    // Email headers
    $headers = "From: " . $fromName . " <" . $fromEmail . ">\r\n";
    $headers .= "Reply-To: " . $fromEmail . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
    
    // Send email
    return mail($to, $emailSubject, $emailBody, $headers);
}

// Function to save contact message to database
function saveContactMessage($conn, $name, $email, $subject, $message, $ipAddress) {
    // Check if contact_messages table exists, if not create it
    $checkTableSql = "SHOW TABLES LIKE 'contact_messages'";
    $result = $conn->query($checkTableSql);
    
    if ($result->num_rows == 0) {
        // Create table
        $createTableSql = "
        CREATE TABLE contact_messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(255) NOT NULL,
            subject VARCHAR(200) NOT NULL,
            message TEXT NOT NULL,
            ip_address VARCHAR(45),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            status ENUM('new', 'read', 'replied') DEFAULT 'new',
            INDEX idx_status (status),
            INDEX idx_created (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        
        if (!$conn->query($createTableSql)) {
            error_log("Failed to create contact_messages table: " . $conn->error);
            return false;
        }
    }
    
    // Insert message
    $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, subject, message, ip_address) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $name, $email, $subject, $message, $ipAddress);
    $success = $stmt->execute();
    $stmt->close();
    
    return $success;
}

// ============================================================================
// MAIN LOGIC
// ============================================================================

// Check if POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
    exit;
}

// CSRF Protection (Requirement 33)
CSRFToken::verifyPost();

// Get form data
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
$message = isset($_POST['message']) ? trim($_POST['message']) : '';
$recaptchaResponse = isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '';

// Validate required fields
if (empty($name) || empty($email) || empty($subject) || empty($message)) {
    echo json_encode([
        'success' => false,
        'message' => 'Sva polja su obavezna.'
    ]);
    exit;
}

// Validate name length
if (strlen($name) < 3 || strlen($name) > 100) {
    echo json_encode([
        'success' => false,
        'message' => 'Ime mora biti između 3 i 100 znakova.'
    ]);
    exit;
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => false,
        'message' => 'Neispravna email adresa.'
    ]);
    exit;
}

// Validate subject length
if (strlen($subject) < 5 || strlen($subject) > 200) {
    echo json_encode([
        'success' => false,
        'message' => 'Predmet mora biti između 5 i 200 znakova.'
    ]);
    exit;
}

// Validate message length
if (strlen($message) < 20 || strlen($message) > 2000) {
    echo json_encode([
        'success' => false,
        'message' => 'Poruka mora biti između 20 i 2000 znakova.'
    ]);
    exit;
}

// Validate reCAPTCHA
$recaptchaValidator = new RecaptchaValidator();
if (!$recaptchaValidator->verify($recaptchaResponse)) {
    echo json_encode([
        'success' => false,
        'message' => 'reCAPTCHA validacija neuspješna. Molimo pokušajte ponovo.'
    ]);
    exit;
}

// Get IP address
$ipAddress = $_SERVER['REMOTE_ADDR'];
if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
} elseif (isset($_SERVER['HTTP_X_REAL_IP'])) {
    $ipAddress = $_SERVER['HTTP_X_REAL_IP'];
}

try {
    // Save to database
    $dbSaved = saveContactMessage($conn, $name, $email, $subject, $message, $ipAddress);
    
    // Send email
    $emailSent = sendContactEmail($name, $email, $subject, $message);
    
    if ($emailSent) {
        // Log success
        error_log("Contact form submitted successfully from: $email");
        
        echo json_encode([
            'success' => true,
            'message' => '✅ Poruka uspješno poslana! Odgovorit ćemo vam u najkraćem mogućem roku.'
        ]);
    } else {
        // Email failed but DB saved
        error_log("Failed to send contact email from: $email");
        
        echo json_encode([
            'success' => $dbSaved,
            'message' => $dbSaved 
                ? '⚠️ Poruka je spremljena, ali slanje emaila nije uspjelo. Kontaktirat ćemo vas uskoro.'
                : '❌ Došlo je do greške pri slanju poruke. Molimo pokušajte ponovo.'
        ]);
    }
    
} catch (Exception $e) {
    error_log("Contact form error: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => '❌ Greška: ' . $e->getMessage()
    ]);
}

$conn->close();
?>
