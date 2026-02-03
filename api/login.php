<?php
/**
 * API Endpoint: User Login
 * Prijava korisnika u sustav
 * Protected with CSRF token (Requirement 33)
 */

// Start output buffering FIRST
ob_start();

// Suppress errors
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);

// Start session BEFORE any output
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

// THEN set headers
header('Content-Type: application/json');

// Include dependencies
require_once('../lib/db_connection.php');
require_once('../lib/SessionManager.php');
require_once('../lib/recaptcha_config.php');
require_once('../lib/CSRFToken.php');

// Select database
mysqli_select_db($connection, 'hotel_management');

// Initialize session manager with database connection
$sessionManager = new SessionManager($connection);

// Response array
$response = [
    'success' => false,
    'message' => ''
];

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Metoda zahtjeva mora biti POST';
    echo json_encode($response);
    exit;
}

// CSRF Protection (Requirement 33)
CSRFToken::verifyPost();

// Get POST data
$usernameOrEmail = isset($_POST['usernameOrEmail']) ? trim($_POST['usernameOrEmail']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$rememberMe = isset($_POST['rememberMe']) && $_POST['rememberMe'] === '1';
$recaptchaResponse = isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '';

// Validate input
if (empty($usernameOrEmail) || empty($password)) {
    $response['message'] = 'Korisničko ime/email i lozinka su obavezni';
    echo json_encode($response);
    exit;
}

// Verify reCAPTCHA
$recaptchaResult = verifyRecaptchaCurl($recaptchaResponse, $_SERVER['REMOTE_ADDR']);
if (!$recaptchaResult['success']) {
    $response['message'] = 'reCAPTCHA verifikacija neuspješna: ' . $recaptchaResult['error'];
    echo json_encode($response);
    exit;
}

// Determine if input is email or username
$isEmail = filter_var($usernameOrEmail, FILTER_VALIDATE_EMAIL);

// Prepare SQL query
if ($isEmail) {
    $sql = "SELECT id, username, email, password, is_verified FROM users WHERE email = ?";
} else {
    $sql = "SELECT id, username, email, password, is_verified FROM users WHERE username = ?";
}

$stmt = $connection->prepare($sql);
$stmt->bind_param("s", $usernameOrEmail);
$stmt->execute();
$result = $stmt->get_result();

// Check if user exists
if ($result->num_rows === 0) {
    $response['message'] = 'Nevažeće korisničko ime/email ili lozinka';
    echo json_encode($response);
    $stmt->close();
    exit;
}

$user = $result->fetch_assoc();
$stmt->close();

// Check if user is verified
if ($user['is_verified'] == 0) {
    $response['message'] = 'Račun nije verificiran. Molimo provjerite svoj email.';
    $response['requires_verification'] = true;
    $response['email'] = $user['email'];
    echo json_encode($response);
    exit;
}

// Verify password
if (!password_verify($password, $user['password'])) {
    $response['message'] = 'Nevažeće korisničko ime/email ili lozinka';
    echo json_encode($response);
    exit;
}

// Password is correct - Create session (SessionManager already initialized with connection)
$sessionManager->login($user['id'], $user['username'], $user['email'], $rememberMe);

// Update last login time (optional)
$stmt = $connection->prepare("UPDATE users SET created_at = created_at WHERE id = ?");
$stmt->bind_param("i", $user['id']);
$stmt->execute();
$stmt->close();

// Success response
$response['success'] = true;
$response['message'] = 'Prijava uspješna! Preusmjeravam...';
$response['user'] = [
    'id' => $user['id'],
    'username' => $user['username'],
    'email' => $user['email']
];

$connection->close();

echo json_encode($response);
?>
