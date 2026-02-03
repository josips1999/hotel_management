<?php
/**
 * API Endpoint: Verify Email Code
 * Verifikacija korisničkog računa pomoću 6-znamenkastog koda
 */

header('Content-Type: application/json');

// Include dependencies
require_once('../lib/db_connection.php');

// Select database
mysqli_select_db($connection, 'hotel_management');

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

// Get POST data
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$code = isset($_POST['code']) ? trim($_POST['code']) : '';

// Validate input
if (empty($email) || empty($code)) {
    $response['message'] = 'Email i verifikacijski kod su obavezni';
    echo json_encode($response);
    exit;
}

// Validate code format (6 digits)
if (!preg_match('/^[0-9]{6}$/', $code)) {
    $response['message'] = 'Nevažeći format koda. Kod mora imati 6 znamenki.';
    echo json_encode($response);
    exit;
}

// Get user from database
$stmt = $connection->prepare("SELECT id, username, verification_code, verification_expires, is_verified FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $response['message'] = 'Korisnik s ovom email adresom ne postoji';
    echo json_encode($response);
    $stmt->close();
    exit;
}

$user = $result->fetch_assoc();
$stmt->close();

// Check if already verified
if ($user['is_verified'] == 1) {
    $response['message'] = 'Ovaj račun je već verificiran. Možete se prijaviti.';
    $response['already_verified'] = true;
    echo json_encode($response);
    exit;
}

// Check if verification code exists
if (empty($user['verification_code'])) {
    $response['message'] = 'Verifikacijski kod nije pronađen. Zatražite novi kod.';
    echo json_encode($response);
    exit;
}

// Check if code has expired
$currentTime = date('Y-m-d H:i:s');
if ($currentTime > $user['verification_expires']) {
    $response['message'] = 'Verifikacijski kod je istekao. Zatražite novi kod.';
    $response['expired'] = true;
    echo json_encode($response);
    exit;
}

// Check if code matches
if ($user['verification_code'] !== $code) {
    $response['message'] = 'Nevažeći verifikacijski kod. Provjerite i pokušajte ponovno.';
    $response['invalid_code'] = true;
    echo json_encode($response);
    exit;
}

// Code is valid - activate account
$stmt = $connection->prepare("UPDATE users SET is_verified = 1, verification_code = NULL, verification_expires = NULL WHERE id = ?");
$stmt->bind_param("i", $user['id']);

if ($stmt->execute()) {
    $response['success'] = true;
    $response['message'] = 'Račun uspješno verificiran! Preusmjeravam...';
    $response['username'] = $user['username'];
    
    // Optional: Automatically log in the user
    session_start();
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $email;
    $_SESSION['is_verified'] = true;
    
    // Clear pending verification data
    unset($_SESSION['pending_verification_email']);
    unset($_SESSION['pending_verification_username']);
    
} else {
    $response['message'] = 'Greška pri verifikaciji računa: ' . $stmt->error;
}

$stmt->close();
$connection->close();

echo json_encode($response);
?>
