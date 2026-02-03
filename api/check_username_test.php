<?php
// Temporary test file to debug the error
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: text/plain');

echo "Testing check_username.php...\n\n";

try {
    echo "1. Including db_connection.php...\n";
    require_once('../lib/db_connection.php');
    echo "   SUCCESS: Database connection loaded\n\n";
    
    echo "2. Testing database connection...\n";
    if ($connection->ping()) {
        echo "   SUCCESS: Database connection is alive\n\n";
    } else {
        echo "   ERROR: Database connection failed\n\n";
    }
    
    echo "3. Testing query...\n";
    $username = 'test';
    $stmt = $connection->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    echo "   SUCCESS: Query executed, found " . $result->num_rows . " rows\n\n";
    
    echo "All tests passed!\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
