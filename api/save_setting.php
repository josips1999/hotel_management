<?php
/**
 * Save Setting API - Admin Only
 * 
 * Updates or adds system settings
 */

header('Content-Type: application/json');

require_once('../lib/db_connection.php');
require_once('../lib/SessionManager.php');
require_once('../lib/CSRFToken.php');

$sessionManager = new SessionManager($connection);
$sessionManager->checkRememberMe();
$isLoggedIn = $sessionManager->isLoggedIn();
$userId = $sessionManager->getUserId();

// Check if user is admin
$stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$userRole = $result->fetch_assoc()['role'] ?? 'user';
$stmt->close();

if (!$isLoggedIn || $userRole !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// CSRF Protection (Requirement 33)
$csrfToken = $input['csrf_token'] ?? '';
if (!CSRFToken::validate($csrfToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'CSRF token validation failed']);
    exit;
}

$action = $input['action'] ?? 'update';
$key = $input['key'] ?? '';
$value = $input['value'] ?? '';

if (!$key || !$value) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

try {
    if ($action === 'add') {
        $category = $input['category'] ?? 'other';
        $description = $input['description'] ?? '';
        
        $stmt = $conn->prepare("INSERT INTO system_settings (setting_key, setting_value, category, description) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $key, $value, $category, $description);
        $stmt->execute();
        $stmt->close();
        
        echo json_encode(['success' => true, 'message' => 'Setting added']);
    } else {
        $stmt = $conn->prepare("UPDATE system_settings SET setting_value = ? WHERE setting_key = ?");
        $stmt->bind_param("ss", $value, $key);
        $stmt->execute();
        $stmt->close();
        
        echo json_encode(['success' => true, 'message' => 'Setting updated']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>
