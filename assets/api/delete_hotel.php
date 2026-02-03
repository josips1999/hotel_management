<?php
header('Content-Type: application/json');
require_once('../lib/db_connection.php');

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'Hotel ID is required']);
    exit();
}

$id = intval($_GET['id']);

$stmt = $connection->prepare("DELETE FROM hotels WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Hotel deleted successfully']);
    } else {
        echo json_encode(['error' => 'Hotel not found']);
    }
} else {
    echo json_encode(['error' => 'Failed to delete hotel']);
}

$stmt->close();
$connection->close();
?>