<?php
require_once 'lib/db_connection.php';
require_once 'lib/functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $naziv = $_POST['naziv'];
    $adresa = $_POST['adresa'];
    $grad = $_POST['grad'];
    $zupanija = $_POST['zupanija'];
    $kapacitet = intval($_POST['kapacitet']);
    $broj_soba = intval($_POST['broj_soba']);
    
    if (addHotel($conn, $naziv, $adresa, $grad, $zupanija, $kapacitet, $broj_soba)) {
        header("Location: index.php?success=added");
    } else {
        header("Location: index.php?error=add_failed");
    }
} else {
    header("Location: index.php");
}
exit();
?>
