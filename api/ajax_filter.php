<?php
/**
 * AJAX Filter API Endpoint
 * 
 * Filters hotels by city and county with AJAX (JSON response)
 * 
 * Method: GET
 * Parameters: grad, zupanija, sort
 * Response: JSON
 */

header('Content-Type: application/json');

require_once('../lib/db_connection.php');
require_once('../lib/config.php');
require_once('../lib/SessionManager.php');

// Check if user is logged in
$sessionManager = new SessionManager($connection);
$sessionManager->checkRememberMe();
$isLoggedIn = $sessionManager->isLoggedIn();

// Guest limitations
$isGuest = !$isLoggedIn;
$guestMaxResults = 5;

// Get filter parameters
$grad = isset($_GET['grad']) ? trim($_GET['grad']) : '';
$zupanija = isset($_GET['zupanija']) ? trim($_GET['zupanija']) : '';
$sort = isset($_GET['sort']) ? trim($_GET['sort']) : 'naziv_asc';

// Build WHERE clause
$whereConditions = [];
$params = [];
$types = '';

if (!empty($grad)) {
    $whereConditions[] = "grad = ?";
    $params[] = $grad;
    $types .= 's';
}

if (!empty($zupanija)) {
    $whereConditions[] = "zupanija = ?";
    $params[] = $zupanija;
    $types .= 's';
}

$whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

// Build ORDER BY clause
$orderBy = match($sort) {
    'naziv_asc' => 'naziv ASC',
    'naziv_desc' => 'naziv DESC',
    'kapacitet_asc' => 'kapacitet ASC',
    'kapacitet_desc' => 'kapacitet DESC',
    'grad_asc' => 'grad ASC',
    'grad_desc' => 'grad DESC',
    default => 'naziv ASC'
};

try {
    // Get total count
    $countSql = "SELECT COUNT(*) as total FROM hotels $whereClause";
    if (!empty($params)) {
        $stmt = $conn->prepare($countSql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $totalCount = $result->fetch_assoc()['total'];
        $stmt->close();
    } else {
        $result = $conn->query($countSql);
        $totalCount = $result->fetch_assoc()['total'];
    }
    
    // Get hotels
    $resultLimit = $isGuest ? $guestMaxResults : 20;
    $sql = "SELECT * FROM hotels $whereClause ORDER BY $orderBy LIMIT $resultLimit";
    
    if (!empty($params)) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $hotels = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    } else {
        $result = $conn->query($sql);
        $hotels = $result->fetch_all(MYSQLI_ASSOC);
    }
    
    // Return JSON
    echo json_encode([
        'success' => true,
        'hotels' => $hotels,
        'total' => $totalCount,
        'filters' => [
            'grad' => $grad,
            'zupanija' => $zupanija,
            'sort' => $sort
        ]
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'hotels' => [],
        'total' => 0
    ]);
}

$conn->close();
