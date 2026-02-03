<?php
/**
 * API Endpoint: Get All Hotels
 * Uses HotelController (MVC pattern)
 */

// Set JSON response header
header('Content-Type: application/json');

// Include dependencies
require_once(__DIR__ . '/../lib/db_connection.php');
require_once(__DIR__ . '/../app/controllers/HotelController.php');

// Create controller instance with database connection
$controller = new HotelController($connection);

// Call controller method to get all hotels
$response = $controller->index();

// Output JSON response
echo json_encode($response);

// Clean up database connection
$connection->close();
?>
