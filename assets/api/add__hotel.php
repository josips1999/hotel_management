<?php
header('Content-Type: application/json');
/* Using prepared statements to prevent SQL injection attacks.*/
require_once('../lib/db_connection.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request method. Only POST requests are allowed.']);
    exit();
}

$naziv = $_POST['naziv'] ?? '';
$adresa = $_POST['adresa'] ?? '';
$grad = $_POST['grad'] ?? '';
$zupanija = $_POST['zupanija'] ?? '';
$kapacitet = intval($_POST['kapacitet']) ?? 0;
$broj_soba = intval($_POST['broj_soba']) ?? 0;
$telefon = $_POST['telefon'] ?? '';
$broj_gostiju = 0;
$slobodno_soba = $broj_soba;

if(empty($naziv) || empty($adresa) || empty($grad) || empty($zupanija) || $kapacitet <= 0 || $broj_soba <= 0){
    echo json_encode(['error' => 'All fields are required']);
    exit();
}

$stmt = $connection->prepare("INSERT INTO hotels (naziv, adresa, grad, zupanija, kapacitet, broj_soba, broj_gostiju, slobodno_soba) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssiii", $naziv, $adresa, $grad, $zupanija, $kapacitet, $broj_soba, $broj_gostiju, $slobodno_soba);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Hotel added successfully', 'id' => $connection->insert_id]);
} else {
    echo json_encode(['error' => 'Failed to add hotel']);
}

$stmt->close();
$connection->close();

?>



