<?php
// Check if password matches the hash in database
require_once('lib/db_connection.php');
mysqli_select_db($connection, 'hotel_management');

$username = $_GET['username'] ?? '';
$testPassword = $_GET['password'] ?? '';

$stmt = $connection->prepare("SELECT username, password FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $storedHash = $user['password'];
    
    echo "Username: " . $user['username'] . "\n";
    echo "Stored Hash: " . substr($storedHash, 0, 60) . "...\n";
    echo "Test Password: " . $testPassword . "\n\n";
    
    if (password_verify($testPassword, $storedHash)) {
        echo "✓ Password MATCHES!\n";
    } else {
        echo "✗ Password DOES NOT MATCH!\n";
        echo "\nPossible reasons:\n";
        echo "- Wrong password entered during registration\n";
        echo "- Password was changed\n";
        echo "- Hash algorithm mismatch\n";
    }
} else {
    echo "User not found: " . $username;
}

$stmt->close();
$connection->close();
