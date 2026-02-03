<?php
/**
 * API Endpoint: Delete Hotel
 * Uses HotelController (MVC pattern)
 * Protected with CSRF (Requirement 33)
 */

// Start output buffering
ob_start();

// Suppress errors
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);

// Start session
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

// Set JSON response header
header('Content-Type: application/json');

// Include dependencies
require_once(__DIR__ . '/../lib/db_connection.php');
require_once(__DIR__ . '/../app/controllers/HotelController.php');
require_once(__DIR__ . '/../lib/CSRFToken.php');

// Select database
mysqli_select_db($connection, 'hotel_management');

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_clean();
    echo json_encode([
        'error' => true,
        'message' => 'Only POST method allowed for delete operations'
    ]);
    exit();
}

// CSRF Protection (Requirement 33)
CSRFToken::verifyPost();

// Get hotel ID from POST data
$id = $_POST['id'] ?? 0;

// Create controller instance
$controller = new HotelController($connection);

// Call controller method to delete hotel
$response = $controller->destroy($id);

// Output JSON response
ob_clean();
echo json_encode($response);

// Clean up database connection
$connection->close();
?>
