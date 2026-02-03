<?php
/**
 * API Endpoint: Get Single Hotel
 * Uses HotelController (MVC pattern)
 */

// Set JSON response header
header('Content-Type: application/json');

// Include dependencies
require_once(__DIR__ . '/../lib/db_connection.php');
require_once(__DIR__ . '/../app/controllers/HotelController.php');

// Create controller instance with database connection
$controller = new HotelController($connection);

// Get hotel ID from query string
$id = $_GET['id'] ?? null;

// Call controller method to get hotel
$response = $controller->show($id);

// Output JSON response
echo json_encode($response);

// Clean up database connection
$connection->close();
?>
