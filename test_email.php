<?php
// Test email sending with Papercut SMTP
ini_set('SMTP', 'localhost');
ini_set('smtp_port', '2525');

$to = 'test@example.com';
$subject = 'Test Email - Hotel Management';
$message = 'Ovo je test email poruka. Ako vidite ovu poruku u Papercut-u, email konfiguracija radi!';
$headers = 'From: noreply@hotelmanagement.com' . "\r\n" .
           'Reply-To: noreply@hotelmanagement.com' . "\r\n" .
           'X-Mailer: PHP/' . phpversion();

if (mail($to, $subject, $message, $headers)) {
    echo "✓ Email je uspješno poslan!\n";
    echo "Provjerite Papercut SMTP aplikaciju (localhost:8080 ili desktop aplikaciju)\n";
} else {
    echo "✗ Greška pri slanju emaila\n";
    echo "Error: " . error_get_last()['message'] ?? 'Unknown error';
}
