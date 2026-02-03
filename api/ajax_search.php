<?php
/**
 * AJAX Search API Endpoint
 * 
 * Returns search results in JSON format without page refresh.
 * Used by ajax_search.php (Live Search)
 * 
 * Method: GET
 * Parameters: q (search term)
 * Response: JSON
 */

header('Content-Type: application/json');

require_once('../lib/db_connection.php');
require_once('../lib/SearchEngine.php');
require_once('../lib/SessionManager.php');

// Check if user is logged in
$sessionManager = new SessionManager($connection);
$sessionManager->checkRememberMe();
$isLoggedIn = $sessionManager->isLoggedIn();

// Guest limitations
$isGuest = !$isLoggedIn;
$guestMaxResults = 5;

// Start timer for performance tracking
$start_time = microtime(true);

// Get search term
$searchTerm = isset($_GET['q']) ? trim($_GET['q']) : '';

// Validate
if (empty($searchTerm)) {
    echo json_encode([
        'success' => false,
        'error' => 'Search term is required',
        'hotels' => [],
        'users' => [],
        'total' => 0
    ]);
    exit;
}

if (strlen($searchTerm) < 3) {
    echo json_encode([
        'success' => false,
        'error' => 'Minimalno 3 znaka potrebno',
        'hotels' => [],
        'users' => [],
        'total' => 0
    ]);
    exit;
}

try {
    // Create SearchEngine instance
    $searchEngine = new SearchEngine($conn);
    
    // Determine result limit based on user status
    $resultLimit = $isGuest ? $guestMaxResults : 10;
    
    // Perform search
    $results = $searchEngine->search($searchTerm, SearchEngine::MODE_NATURAL, 1, $resultLimit);
    
    // For guests, hide user results completely
    if ($isGuest && isset($results['users'])) {
        $results['users'] = [];
    }
    
    // Calculate execution time
    $end_time = microtime(true);
    $execution_time = round(($end_time - $start_time) * 1000); // Convert to milliseconds
    
    // Return JSON response
    if ($results['success']) {
        echo json_encode([
            'success' => true,
            'hotels' => $results['hotels'],
            'users' => $results['users'],
            'total' => count($results['hotels']) + count($results['users']),
            'time_ms' => $execution_time,
            'search_term' => $searchTerm
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            'success' => false,
            'error' => $results['error'],
            'hotels' => [],
            'users' => [],
            'total' => 0
        ], JSON_UNESCAPED_UNICODE);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Server error: ' . $e->getMessage(),
        'hotels' => [],
        'users' => [],
        'total' => 0
    ]);
}

$conn->close();
