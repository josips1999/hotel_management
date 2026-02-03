<?php
header('Content-Type: application/json');
require_once('../lib/db_connection.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Only POST method allowed']);
    exit();
}

$id = intval($_POST['id'] ?? 0);
$naziv = $_POST['naziv'] ?? '';
$adresa = $_POST['adresa'] ?? '';
$grad = $_POST['grad'] ?? '';
$zupanija = $_POST['zupanija'] ?? '';
$kapacitet = intval($_POST['kapacitet'] ?? 0);
$broj_soba = intval($_POST['broj_soba'] ?? 0);


if ($id <= 0 || empty($naziv) || empty($adresa)) {
    echo json_encode(['error' => 'Invalid input data']);
    exit();
}

$stmt = $connection->prepare("UPDATE hotels SET naziv=?, adresa=?, grad=?, zupanija=?, kapacitet=?, broj_soba=? WHERE id=?");
$stmt->bind_param("sssssii", $naziv, $adresa, $grad, $zupanija, $kapacitet, $broj_soba, $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Hotel updated successfully']);
} else {
    echo json_encode(['error' => 'Failed to update hotel']);
}

$stmt->close();
$connection->close();

?>