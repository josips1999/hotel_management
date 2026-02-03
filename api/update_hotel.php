<?php
/**
 * API Endpoint: Update Hotel
 * Uses HotelController (MVC pattern)
 */

// Set JSON response header
header('Content-Type: application/json');

// Include dependencies
require_once(__DIR__ . '/../lib/db_connection.php');
require_once(__DIR__ . '/../app/controllers/HotelController.php');
require_once(__DIR__ . '/../lib/CSRFToken.php');

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'error' => true,
        'message' => 'Only POST method allowed'
    ]);
    exit();
}

// CSRF Protection (Requirement 33)
CSRFToken::verifyPost();

// Create controller instance
$controller = new HotelController($connection);

// Get hotel ID from POST data
$id = $_POST['id'] ?? 0;

// Call controller method to update hotel
$response = $controller->update($id, $_POST);

// Output JSON response
echo json_encode($response);

// Clean up database connection
$connection->close();
?>
