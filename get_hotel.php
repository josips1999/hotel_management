<?php
require_once 'lib/db_connection.php';
require_once 'lib/functions.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $hotel = getHotelById($conn, $id);
    
    header('Content-Type: application/json');
    echo json_encode($hotel);
} else {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => 'ID not provided']);
}
exit();
?>
