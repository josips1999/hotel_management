<?php
/**
 * API Endpoint: User Registration
 * Registracija novog korisnika u sustav
 */

// Start output buffering FIRST to catch any output
ob_start();

// Suppress errors but allow script to continue
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);

// Start session - before anything else
if (session_status() === PHP_SESSION_NONE) {
    @session_start(); // @ suppresses warnings
}

// Set headers
header('Content-Type: application/json');

// Include dependencies
require_once('../lib/db_connection.php');
require_once('../lib/Validator.php');
require_once('../lib/EmailService.php');
require_once('../lib/recaptcha_config.php');
require_once('../lib/CSRFToken.php');
mysqli_select_db($connection,'hotel_management');

// Response array
$response = [
    'success' => false,
    'message' => '',
    'errors' => []
];

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Metoda zahtjeva mora biti POST';
    ob_clean();
    echo json_encode($response);
    exit;
}

// CSRF Protection (Requirement 33)
CSRFToken::verifyPost();

// Get POST data
$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$confirmPassword = isset($_POST['confirmPassword']) ? $_POST['confirmPassword'] : '';
$recaptchaResponse = isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '';

// Verify reCAPTCHA first
$recaptchaResult = verifyRecaptchaCurl($recaptchaResponse, $_SERVER['REMOTE_ADDR']);
if (!$recaptchaResult['success']) {
    $response['message'] = 'reCAPTCHA verifikacija neuspješna';
    $response['errors'][] = $recaptchaResult['error'];
    ob_clean();
    echo json_encode($response);
    exit;
}

// Initialize validator
$validator = new Validator();

// Validate username
$validator->validateRequired($username, 'Korisničko ime');
$validator->validateMinLength($username, 3, 'Korisničko ime');

// Validate username format (alphanumeric + underscore + space)
if (!preg_match('/^[a-zA-Z0-9_ ]{3,30}$/', $username)) {
    $validator->getErrors()[] = 'Korisničko ime može sadržavati samo slova, brojeve, razmak i donju crtu (_)';
}

// Validate email
$validator->validateRequired($email, 'Email');
$validator->validateEmail($email);

// Validate password
$validator->validateRequired($password, 'Lozinka');
$validator->validateMinLength($password, 6, 'Lozinka');

// Validate password match
if ($password !== $confirmPassword) {
    $validator->getErrors()[] = 'Lozinke se ne podudaraju';
}

// Check if validation passed
if (!$validator->isValid()) {
    $response['errors'] = $validator->getErrors();
    $response['message'] = 'Podaci nisu valjani';
    ob_clean();
    echo json_encode($response);
    exit;
}

// Check if username or email already exists (generic message to prevent user enumeration)
$stmt = $connection->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
$stmt->bind_param("ss", $username, $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Generic error message - don't reveal if username or email exists (security)
    $response['message'] = 'Korisničko ime ili email adresa već postoji';
    $response['errors'][] = 'Odabrano korisničko ime ili email adresa je već registrirana u sustavu';
    ob_clean();
    echo json_encode($response);
    $stmt->close();
    exit;
}
$stmt->close();

// Hash password (security best practice)
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Generate 6-digit verification code (cryptographically secure)
try {
    $verificationCode = sprintf("%06d", random_int(0, 999999));
} catch (Exception $e) {
    // Fallback to mt_rand if random_int fails (very rare)
    error_log('random_int() failed: ' . $e->getMessage());
    $verificationCode = sprintf("%06d", mt_rand(0, 999999));
}

// Set verification expiry time (15 minutes from now)
$verificationExpires = date('Y-m-d H:i:s', strtotime('+15 minutes'));

// Insert new user into database with verification fields
$stmt = $connection->prepare("INSERT INTO users (username, email, password, verification_code, verification_expires, is_verified) VALUES (?, ?, ?, ?, ?, 0)");
$stmt->bind_param("sssss", $username, $email, $hashedPassword, $verificationCode, $verificationExpires);

if ($stmt->execute()) {
    $userId = $stmt->insert_id;
    
    // Send verification email
    $emailService = new EmailService();
    $emailSent = $emailService->sendVerificationEmail($email, $username, $verificationCode);
    
    if ($emailSent) {
        $response['success'] = true;
        $response['message'] = 'Registracija uspješna! Provjerite svoj email za verifikacijski kod.';
        $response['email'] = $email;
        $response['requires_verification'] = true;
        
        // Store email in session for verification page
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['pending_verification_email'] = $email;
        $_SESSION['pending_verification_username'] = $username;
    } else {
        $response['success'] = true;
        $response['message'] = 'Registracija uspješna, ali email nije poslan. Kontaktirajte administratora.';
        $response['email_error'] = true;
    }
    
} else {
    $response['message'] = 'Greška pri registraciji: ' . $stmt->error;
    $response['errors'][] = 'Pokušajte ponovno kasnije';
}

$stmt->close();
$connection->close();

// Clear any buffered output and send clean JSON
ob_clean();
echo json_encode($response);
exit;
