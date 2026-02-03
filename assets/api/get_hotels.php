<?php

header('Content-Type: application/json'); //  headers contain the metadata that tells the browser or server how to handle that content
require_once ('../lib/db_connection.php'); 

$sql = "SELECT * FROM hotels ORDER BY naziv ASC";
$result = $connection->query($sql);

$hotels = [];
while($row = $result->fetch_assoc()){
    $hotels[] = $row;
}

echo json_encode($hotels); // Convert the PHP array to JSON format and output it
// JSON allows other applications to fetch hotel data
$connection->close(); // Close the database connection

