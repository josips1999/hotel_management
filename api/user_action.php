<?php
/**
 * User Action API - Admin Only
 * 
 * Handles user management actions:
 * - toggle_active: Activate/deactivate user
 * - toggle_lock: Lock/unlock user
 * - change_role: Change user role
 * - reset_attempts: Reset failed login attempts
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

$action = $input['action'] ?? '';
$targetUserId = $input['user_id'] ?? 0;

if (!$action || !$targetUserId) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

try {
    switch ($action) {
        case 'toggle_active':
            $isActive = $input['is_active'] ?? 0;
            $stmt = $conn->prepare("UPDATE users SET is_active = ? WHERE id = ?");
            $stmt->bind_param("ii", $isActive, $targetUserId);
            $stmt->execute();
            $stmt->close();
            
            echo json_encode([
                'success' => true,
                'message' => $isActive ? 'Korisnik aktiviran' : 'Korisnik deaktiviran'
            ]);
            break;
            
        case 'toggle_lock':
            $isLocked = $input['is_locked'] ?? 0;
            $stmt = $conn->prepare("UPDATE users SET is_locked = ?, locked_until = NULL WHERE id = ?");
            $stmt->bind_param("ii", $isLocked, $targetUserId);
            $stmt->execute();
            $stmt->close();
            
            echo json_encode([
                'success' => true,
                'message' => $isLocked ? 'Korisnik zaključan' : 'Korisnik otključan'
            ]);
            break;
            
        case 'change_role':
            $role = $input['role'] ?? 'user';
            
            // Prevent changing own role
            if ($targetUserId == $userId) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Ne možete promijeniti vlastitu rolu!'
                ]);
                exit;
            }
            
            $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
            $stmt->bind_param("si", $role, $targetUserId);
            $stmt->execute();
            $stmt->close();
            
            echo json_encode([
                'success' => true,
                'message' => 'Rola promijenjena u ' . strtoupper($role)
            ]);
            break;
            
        case 'reset_attempts':
            $stmt = $conn->prepare("UPDATE users SET failed_login_attempts = 0, locked_until = NULL WHERE id = ?");
            $stmt->bind_param("i", $targetUserId);
            $stmt->execute();
            $stmt->close();
            
            echo json_encode([
                'success' => true,
                'message' => 'Neuspješni pokušaji prijave resetirani'
            ]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Unknown action']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

$conn->close();
?>
