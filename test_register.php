<?php
// Test registration - check what's happening
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Testing Registration Process</h2>";

// Test 1: Check database connection
echo "<h3>1. Database Connection</h3>";
require_once('lib/db_connection.php');
if ($connection) {
    echo "✓ Database connected<br>";
    mysqli_select_db($connection, 'hotel_management');
    echo "✓ Database selected<br>";
} else {
    echo "✗ Database connection failed<br>";
}

// Test 2: Check if users table exists
echo "<h3>2. Users Table</h3>";
$result = mysqli_query($connection, "SHOW TABLES LIKE 'users'");
if (mysqli_num_rows($result) > 0) {
    echo "✓ Users table exists<br>";
    
    // Check table structure
    $result = mysqli_query($connection, "DESCRIBE users");
    echo "<pre>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }
    echo "</pre>";
} else {
    echo "✗ Users table doesn't exist<br>";
}

// Test 3: Check reCAPTCHA config
echo "<h3>3. reCAPTCHA Configuration</h3>";
require_once('lib/recaptcha_config.php');
echo "Site Key: " . RECAPTCHA_SITE_KEY . "<br>";
echo "Secret Key: " . substr(RECAPTCHA_SECRET_KEY, 0, 10) . "...<br>";

// Test 4: Check if email class works
echo "<h3>4. Email Service</h3>";
require_once('lib/EmailService.php');
echo "✓ EmailService loaded<br>";

// Test 5: Check validator
echo "<h3>5. Validator</h3>";
require_once('lib/Validator.php');
$validator = new Validator();
echo "✓ Validator loaded<br>";

echo "<h3>All checks complete!</h3>";

