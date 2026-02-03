<?php
require_once 'lib/db_connection.php';
require_once 'lib/functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = intval($_POST['id']);
    
    if (deleteHotel($conn, $id)) {
        header("Location: index.php?success=deleted");
    } else {
        header("Location: index.php?error=delete_failed");
    }
} else {
    header("Location: index.php");
}
exit();
?>
