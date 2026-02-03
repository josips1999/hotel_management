<?php
header('Content-Type: application/json');
require_once 'config.php';

$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');

switch($action) {
    case 'dohvati_hotele':
        dohvatiHotele();
        break;
    case 'dodaj_hotel':
        dodajHotel();
        break;
    case 'uredi_hotel':
        urediHotel();
        break;
    case 'obrisi_hotel':
        obrisiHotel();
        break;
    case 'dohvati_hotel':
        dohvatiHotel();
        break;
    case 'azuriraj_boravak':
        azurirajBoravak();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Nepoznata akcija']);
}

function dohvatiHotele() {
    global $conn;
    
    $sql = "SELECT id, naziv, adresa, grad, zupanija, kapacitet, broj_soba, broj_gostiju, 
            (kapacitet - broj_gostiju) as slobodno, latitude, longitude 
            FROM hoteli 
            ORDER BY naziv ASC";
    
    $result = $conn->query($sql);
    
    if ($result) {
        $hoteli = [];
        while ($row = $result->fetch_assoc()) {
            $hoteli[] = $row;
        }
        echo json_encode(['success' => true, 'data' => $hoteli]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Greška pri dohvaćanju hotela: ' . $conn->error]);
    }
}

function dodajHotel() {
    global $conn;
    
    $naziv = $conn->real_escape_string($_POST['naziv']);
    $adresa = $conn->real_escape_string($_POST['adresa']);
    $grad = $conn->real_escape_string($_POST['grad']);
    $zupanija = $conn->real_escape_string($_POST['zupanija']);
    $kapacitet = intval($_POST['kapacitet']);
    $broj_soba = intval($_POST['broj_soba']);
    $broj_gostiju = isset($_POST['broj_gostiju']) ? intval($_POST['broj_gostiju']) : 0;
    $latitude = isset($_POST['latitude']) && $_POST['latitude'] !== '' ? floatval($_POST['latitude']) : null;
    $longitude = isset($_POST['longitude']) && $_POST['longitude'] !== '' ? floatval($_POST['longitude']) : null;
    
    // Validacija
    if (empty($naziv) || empty($adresa) || empty($grad) || empty($zupanija)) {
        echo json_encode(['success' => false, 'message' => 'Sva polja su obavezna']);
        return;
    }
    
    if ($broj_gostiju > $kapacitet) {
        echo json_encode(['success' => false, 'message' => 'Broj gostiju ne može biti veći od kapaciteta']);
        return;
    }
    
    $sql = "INSERT INTO hoteli (naziv, adresa, grad, zupanija, kapacitet, broj_soba, broj_gostiju, latitude, longitude) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssiiids", $naziv, $adresa, $grad, $zupanija, $kapacitet, $broj_soba, $broj_gostiju, $latitude, $longitude);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Hotel uspješno dodan']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Greška pri dodavanju hotela: ' . $stmt->error]);
    }
    
    $stmt->close();
}

function urediHotel() {
    global $conn;
    
    $id = intval($_POST['id']);
    $naziv = $conn->real_escape_string($_POST['naziv']);
    $adresa = $conn->real_escape_string($_POST['adresa']);
    $grad = $conn->real_escape_string($_POST['grad']);
    $zupanija = $conn->real_escape_string($_POST['zupanija']);
    $kapacitet = intval($_POST['kapacitet']);
    $broj_soba = intval($_POST['broj_soba']);
    $broj_gostiju = intval($_POST['broj_gostiju']);
    $latitude = isset($_POST['latitude']) && $_POST['latitude'] !== '' ? floatval($_POST['latitude']) : null;
    $longitude = isset($_POST['longitude']) && $_POST['longitude'] !== '' ? floatval($_POST['longitude']) : null;
    
    // Validacija
    if (empty($naziv) || empty($adresa) || empty($grad) || empty($zupanija)) {
        echo json_encode(['success' => false, 'message' => 'Sva polja su obavezna']);
        return;
    }
    
    if ($broj_gostiju > $kapacitet) {
        echo json_encode(['success' => false, 'message' => 'Broj gostiju ne može biti veći od kapaciteta']);
        return;
    }
    
    $sql = "UPDATE hoteli 
            SET naziv=?, adresa=?, grad=?, zupanija=?, kapacitet=?, broj_soba=?, broj_gostiju=?, latitude=?, longitude=?
            WHERE id=?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssiiidsi", $naziv, $adresa, $grad, $zupanija, $kapacitet, $broj_soba, $broj_gostiju, $latitude, $longitude, $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Hotel uspješno ažuriran']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Greška pri ažuriranju hotela: ' . $stmt->error]);
    }
    
    $stmt->close();
}

function obrisiHotel() {
    global $conn;
    
    $id = intval($_POST['id']);
    
    $sql = "DELETE FROM hoteli WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Hotel uspješno obrisan']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Greška pri brisanju hotela: ' . $stmt->error]);
    }
    
    $stmt->close();
}

function dohvatiHotel() {
    global $conn;
    
    $id = intval($_GET['id']);
    
    $sql = "SELECT * FROM hoteli WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        echo json_encode(['success' => true, 'data' => $row]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Hotel nije pronađen']);
    }
    
    $stmt->close();
}

function azurirajBoravak() {
    global $conn;
    
    $id = intval($_POST['id']);
    $broj_gostiju = intval($_POST['broj_gostiju']);
    
    // Prvo dohvati kapacitet hotela
    $sql = "SELECT kapacitet FROM hoteli WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $hotel = $result->fetch_assoc();
    
    if (!$hotel) {
        echo json_encode(['success' => false, 'message' => 'Hotel nije pronađen']);
        return;
    }
    
    if ($broj_gostiju > $hotel['kapacitet']) {
        echo json_encode(['success' => false, 'message' => 'Broj gostiju ne može biti veći od kapaciteta hotela']);
        return;
    }
    
    // Ažuriraj broj gostiju
    $sql = "UPDATE hoteli SET broj_gostiju=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $broj_gostiju, $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Boravak uspješno ažuriran']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Greška pri ažuriranju boravka: ' . $stmt->error]);
    }
    
    $stmt->close();
}

closeConnection();
?>
