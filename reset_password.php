<?php
// Reset password for user
require_once('lib/db_connection.php');
mysqli_select_db($connection, 'hotel_management');

$username = 'josip_skoko';
$newPassword = 'password123'; // Change this to whatever you want

$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

$stmt = $connection->prepare("UPDATE users SET password = ? WHERE username = ?");
$stmt->bind_param("ss", $hashedPassword, $username);

if ($stmt->execute()) {
    echo "✓ Password updated successfully!\n\n";
    echo "Username: $username\n";
    echo "Email: jskoko53@gmail.com\n";
    echo "New Password: $newPassword\n\n";
    echo "You can now login with these credentials.";
} else {
    echo "✗ Error: " . $stmt->error;
}

$stmt->close();
$connection->close();
