<?php
require_once 'lib/db_connection.php';
require_once 'lib/functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = intval($_POST['id']);
    $naziv = $_POST['naziv'];
    $adresa = $_POST['adresa'];
    $grad = $_POST['grad'];
    $zupanija = $_POST['zupanija'];
    $kapacitet = intval($_POST['kapacitet']);
    $broj_soba = intval($_POST['broj_soba']);
    
    if (updateHotel($conn, $id, $naziv, $adresa, $grad, $zupanija, $kapacitet, $broj_soba)) {
        header("Location: index.php?success=updated");
    } else {
        header("Location: index.php?error=update_failed");
    }
} else {
    header("Location: index.php");
}
exit();
?>
