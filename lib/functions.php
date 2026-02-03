<?php
// Helper functions for hotel management

function getAllHotels($conn) {
    $sql = "SELECT id, naziv, adresa, grad, zupanija, kapacitet, broj_soba, broj_gostiju, 
            (kapacitet - broj_gostiju) as slobodno 
            FROM hoteli 
            ORDER BY naziv ASC";
    $result = $conn->query($sql);
    return $result;
}

function getHotelById($conn, $id) {
    $sql = "SELECT * FROM hoteli WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function addHotel($conn, $naziv, $adresa, $grad, $zupanija, $kapacitet, $broj_soba) {
    $sql = "INSERT INTO hoteli (naziv, adresa, grad, zupanija, kapacitet, broj_soba, broj_gostiju) 
            VALUES (?, ?, ?, ?, ?, ?, 0)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssii", $naziv, $adresa, $grad, $zupanija, $kapacitet, $broj_soba);
    return $stmt->execute();
}

function updateHotel($conn, $id, $naziv, $adresa, $grad, $zupanija, $kapacitet, $broj_soba) {
    $sql = "UPDATE hoteli 
            SET naziv = ?, adresa = ?, grad = ?, zupanija = ?, kapacitet = ?, broj_soba = ? 
            WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssiii", $naziv, $adresa, $grad, $zupanija, $kapacitet, $broj_soba, $id);
    return $stmt->execute();
}

function deleteHotel($conn, $id) {
    $sql = "DELETE FROM hoteli WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

function updateBoravak($conn, $id, $broj_gostiju) {
    // First check if broj_gostiju doesn't exceed kapacitet
    $hotel = getHotelById($conn, $id);
    if ($broj_gostiju > $hotel['kapacitet']) {
        return false;
    }
    
    $sql = "UPDATE hoteli SET broj_gostiju = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $broj_gostiju, $id);
    return $stmt->execute();
}

function getStatistics($conn) {
    $sql = "SELECT 
            COUNT(*) as ukupno_hotela,
            SUM(broj_gostiju) as ukupno_gostiju,
            SUM(kapacitet) as ukupan_kapacitet,
            SUM(kapacitet - broj_gostiju) as slobodna_mjesta
            FROM hoteli";
    $result = $conn->query($sql);
    return $result->fetch_assoc();
}
?>
