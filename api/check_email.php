<?php
/**
 * AJAX Endpoint: Check Email Availability
 * 
 * Provjera postoji li već email u bazi podataka
 * Returns JSON: {"available": true/false, "message": "...", "valid": true/false}
 */

// Start output buffering FIRST
ob_start();

// Suppress errors but allow script to continue
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);

// Start session if needed
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

header('Content-Type: application/json');

// Include database connection
require_once('../lib/db_connection.php');
mysqli_select_db($connection, 'hotel_management');

// Get email from GET or POST request
$email = isset($_GET['email']) ? trim($_GET['email']) : (isset($_POST['email']) ? trim($_POST['email']) : '');

// Response array
$response = [
    'available' => false,
    'message' => '',
    'valid' => false
];

// Validate input
if (empty($email)) {
    $response['message'] = 'Email adresa ne može biti prazna';
    ob_clean();
    echo json_encode($response);
    exit;
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response['message'] = 'Nevažeća email adresa';
    ob_clean();
    echo json_encode($response);
    exit;
}

// Validate email length
if (strlen($email) > 100) {
    $response['message'] = 'Email adresa može imati maksimalno 100 znakova';
    ob_clean();
    echo json_encode($response);
    exit;
}

// Email format is valid
$response['valid'] = true;

// Check if email exists in database using prepared statement
$stmt = $connection->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Email already exists
    $response['available'] = false;
    $response['message'] = 'Email adresa je već registrirana';
} else {
    // Email is available
    $response['available'] = true;
    $response['message'] = 'Email adresa je dostupna';
}

$stmt->close();
$connection->close();

// Return JSON response
ob_clean();
echo json_encode($response);
?>
