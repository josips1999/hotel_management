<?php
/**
 * AJAX Endpoint: Check Username Availability
 * 
 * Provjera postoji li već korisničko ime u bazi podataka
 * Returns JSON: {"available": true/false, "message": "..."}
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

// Get username from GET or POST request
$username = isset($_GET['username']) ? trim($_GET['username']) : (isset($_POST['username']) ? trim($_POST['username']) : '');

// Response array
$response = [
    'available' => false,
    'message' => '',
    'valid' => false
];

// Validate input
if (empty($username)) {
    $response['message'] = 'Korisničko ime ne može biti prazno';
    ob_clean();
    echo json_encode($response);
    exit;
}

// Validate username length (3-30 characters)
if (strlen($username) < 3) {
    $response['message'] = 'Korisničko ime mora imati minimalno 3 znaka';
    ob_clean();
    echo json_encode($response);
    exit;
}

if (strlen($username) > 30) {
    $response['message'] = 'Korisničko ime može imati maksimalno 30 znakova';
    ob_clean();
    echo json_encode($response);
    exit;
}

// Validate username format (alphanumeric + underscore + space)
if (!preg_match('/^[a-zA-Z0-9_ ]+$/', $username)) {
    $response['message'] = 'Korisničko ime može sadržavati samo slova, brojeve, razmak i donju crtu (_)';
    ob_clean();
    echo json_encode($response);
    exit;
}

// Username format is valid
$response['valid'] = true;

// Check if username exists in database using prepared statement
$stmt = $connection->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Username already exists
    $response['available'] = false;
    $response['message'] = 'Korisničko ime "' . htmlspecialchars($username) . '" je već zauzeto';
} else {
    // Username is available
    $response['available'] = true;
    $response['message'] = 'Korisničko ime "' . htmlspecialchars($username) . '" je dostupno';
}

$stmt->close();
$connection->close();

// Return JSON response
ob_clean();
echo json_encode($response);
?>
