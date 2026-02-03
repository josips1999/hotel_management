<?php
/**
 * API Endpoint: Resend Verification Code
 * Ponovno slanje verifikacijskog koda na email
 */

header('Content-Type: application/json');

// Include dependencies
require_once('../lib/db_connection.php');
require_once('../lib/EmailService.php');

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

// Validate input
if (empty($email)) {
    $response['message'] = 'Email adresa je obavezna';
    echo json_encode($response);
    exit;
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response['message'] = 'Nevažeća email adresa';
    echo json_encode($response);
    exit;
}

// Get user from database
$stmt = $connection->prepare("SELECT id, username, is_verified, verification_expires FROM users WHERE email = ?");
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

// Check rate limiting (prevent spam)
// Only allow resend if last code was sent more than 1 minute ago
if (!empty($user['verification_expires'])) {
    $lastSentTime = strtotime($user['verification_expires']) - (15 * 60); // subtract 15 minutes to get original send time
    $currentTime = time();
    $timeDifference = $currentTime - $lastSentTime;
    
    if ($timeDifference < 60) { // Less than 1 minute
        $waitTime = 60 - $timeDifference;
        $response['message'] = "Molimo pričekajte još {$waitTime} sekundi prije slanja novog koda";
        $response['wait_time'] = $waitTime;
        echo json_encode($response);
        exit;
    }
}

// Generate new 6-digit verification code
$verificationCode = sprintf("%06d", mt_rand(0, 999999));

// Set new verification expiry time (15 minutes from now)
$verificationExpires = date('Y-m-d H:i:s', strtotime('+15 minutes'));

// Update verification code in database
$stmt = $connection->prepare("UPDATE users SET verification_code = ?, verification_expires = ? WHERE id = ?");
$stmt->bind_param("ssi", $verificationCode, $verificationExpires, $user['id']);

if (!$stmt->execute()) {
    $response['message'] = 'Greška pri generiranju novog koda: ' . $stmt->error;
    echo json_encode($response);
    $stmt->close();
    exit;
}
$stmt->close();

// Send verification email
$emailService = new EmailService();
$emailSent = $emailService->sendVerificationEmail($email, $user['username'], $verificationCode);

if ($emailSent) {
    $response['success'] = true;
    $response['message'] = 'Novi verifikacijski kod je poslan na vaš email!';
    $response['expires_in'] = '15 minuta';
} else {
    $response['message'] = 'Kod je generiran, ali email nije poslan. Kontaktirajte administratora.';
    $response['email_error'] = true;
    
    // Log the error
    error_log("Failed to send verification email to: {$email}");
}

$connection->close();

echo json_encode($response);
?>
