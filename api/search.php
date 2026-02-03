<?php
/**
 * API Endpoint: Full-Text Search
 * 
 * Pretraživanje preko hotels (name, description) i users (username, email)
 */

header('Content-Type: application/json');

require_once('../lib/db_connection.php');
require_once('../lib/SearchEngine.php');

// Provjeri da li je search term poslan
if (!isset($_GET['q']) || empty(trim($_GET['q']))) {
    echo json_encode([
        'success' => false,
        'error' => 'Unesite pojam za pretragu.'
    ]);
    exit;
}

// Dobij search mode (default: NATURAL)
$searchMode = SearchEngine::MODE_NATURAL;
if (isset($_GET['mode'])) {
    switch ($_GET['mode']) {
        case 'boolean':
            $searchMode = SearchEngine::MODE_BOOLEAN;
            break;
        case 'expansion':
            $searchMode = SearchEngine::MODE_QUERY_EXPANSION;
            break;
    }
}

// Kreiraj SearchEngine instancu
$searchEngine = new SearchEngine($conn);

// Izvrši pretragu
$results = $searchEngine->search($_GET['q'], $searchMode);

// Vrati rezultate
echo json_encode($results, JSON_UNESCAPED_UNICODE);

$conn->close();
