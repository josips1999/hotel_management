<?php

//NOTE - Run this script only once to create the database,tables and insert seed data
/* Create database hotel_management if it does not exist
 For any query involving user input (like a search bar or login), 
you must use prepared statements to prevent SQL injection attacks. 
This method separates the SQL logic from the data. 
query */

require_once ('lib/db_connection.php'); // Include the database connection file beacuse there is an existing connection -object $connection

$sql = "CREATE DATABASE IF NOT EXISTS hotel_management";



if($connection->query($sql) === TRUE){
    echo "Database created successfully or already exists.";   // Success message on creating database
} else {
    echo "Error creating database: " . $connection->error;
}

$connection->select_db("hotel_management"); // Select the database to use, 

$sql = "CREATE TABLE IF NOT EXISTS hotels (
    id int(11) auto_increment not null PRIMARY KEY,
    naziv varchar(255) not null,
    adresa varchar(255) not null,
    grad varchar(100) not null,
    zupanija varchar(255) not null,
    kapacitet int(11) not null,
    broj_soba int(11) not null,
    broj_gostiju int(11) not null,
    slobodno_soba int(11) not null
    )";


if ($connection->query($sql) ===TRUE){
    echo "Table hotels created successfully or already exists.";  // Success message on creating table
} else {
    echo "Error creating table: " . $connection->error;
}

// Create users table for registration/login functionality
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT(11) AUTO_INCREMENT NOT NULL PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    verification_code VARCHAR(6) DEFAULT NULL,
    is_verified TINYINT(1) DEFAULT 0,
    verification_expires DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($connection->query($sql) === TRUE) {
    echo "<br>Table users created successfully or already exists.";
} else {
    echo "<br>Error creating users table: " . $connection->error;
}

$sql = "INSERT INTO hotels( naziv, adresa, grad, zupanija, kapacitet, broj_soba, broj_gostiju, slobodno_soba)
VALUES ('Hotel by Marriott Split', 'Domovinskog Rata 61A', 'Split', 'Splitsko-dalmatinska', 200, 100, 80, 20)
";

if ($connection->query($sql) === TRUE){
    echo "New hotel record created successfully, ";  // Success message on inserting record
}
else {
    echo "Error inserting record: " . $sql . "<br>" . $connection->error;
}
       
$connection->close(); // Close the database connection