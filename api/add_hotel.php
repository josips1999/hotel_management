<?php
/**
 * API Endpoint: Add New Hotel
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
        'message' => 'Nevažeća metoda zahtjeva. Dozvoljene su samo POST metode.'
    ]);
    exit();
}

// CSRF Protection (Requirement 33)
CSRFToken::verifyPost();

// Create controller instance
$controller = new HotelController($connection);

// Call controller method to create hotel
$response = $controller->store($_POST);

// Output JSON response
echo json_encode($response);

// Clean up database connection
$connection->close();
?>
