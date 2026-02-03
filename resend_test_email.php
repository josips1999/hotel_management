<?php
// Test email sending for specific user
require_once('lib/db_connection.php');
require_once('lib/EmailService.php');

mysqli_select_db($connection, 'hotel_management');

$email = 'ivanaviigo@gmail.com';

// Get user info
$stmt = $connection->prepare("SELECT username, verification_code FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $username = $user['username'];
    $code = $user['verification_code'];
    
    echo "Šaljem email za korisnika: $username\n";
    echo "Email: $email\n";
    echo "Verifikacijski kod: $code\n\n";
    
    // Send email
    $emailService = new EmailService();
    $sent = $emailService->sendVerificationEmail($email, $username, $code);
    
    if ($sent) {
        echo "✓ Email uspješno poslan!\n";
        echo "Provjerite Papercut SMTP na http://localhost:8080\n";
    } else {
        echo "✗ Greška pri slanju emaila\n";
        $error = error_get_last();
        if ($error) {
            echo "PHP Error: " . print_r($error, true) . "\n";
        }
    }
} else {
    echo "Korisnik nije pronađen\n";
}

$stmt->close();
$connection->close();
