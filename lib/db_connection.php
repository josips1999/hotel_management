<?php
/**
 * Database Connection
 * Establishes MySQL connection for the application
 */

// Database configuration
$server = 'localhost';
$username = 'root';
$password = '';
$database = '';

// Define constants for backup operations
define('DB_HOST', $server);
define('DB_USER', $username);
define('DB_PASS', $password);
define('DB_NAME', $database);

// Create connection
$connection = new mysqli($server, $username, $password, $database);

// Check connection
if ($connection->connect_error) {
    die(json_encode([
        'error' => true,
        'message' => 'Database connection failed: ' . $connection->connect_error
    ]));
}

// Set charset for Croatian characters (č, ć, š, ž, đ)
$connection->set_charset("utf8mb4");