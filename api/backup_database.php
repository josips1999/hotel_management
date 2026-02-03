<?php
/**
 * API: Database Backup Operations
 * 
 * Actions:
 * - backup: Create SQL dump and download
 * - download: Download existing backup file
 * - delete: Delete backup file
 */

require_once('../lib/db_connection.php');
require_once('../lib/config.php');
require_once('../lib/SessionManager.php');

$sessionManager = new SessionManager($connection);
$sessionManager->checkRememberMe();

// Check if user is admin
if (!$sessionManager->isLoggedIn()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = $sessionManager->getUserId();
$stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$userRole = $result->fetch_assoc()['role'] ?? 'user';
$stmt->close();

if ($userRole !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Admin only']);
    exit;
}

// Get action
$action = $_REQUEST['action'] ?? 'backup';

// CSRF Protection for backup action (Requirement 33)
if ($action === 'backup' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRFToken::verifyPost();
}

// Backup directory
$backupDir = dirname(__DIR__) . '/backups';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

// MySQL binary path
$mysqlBinPath = defined('MYSQL_BIN_PATH') ? MYSQL_BIN_PATH : 'C:/xampp/mysql/bin/';
$mysqldumpPath = rtrim($mysqlBinPath, '/') . '/mysqldump.exe';

// ============================================================================
// ACTION: Create Backup
// ============================================================================
if ($action === 'backup') {
    $backupName = $_POST['backup_name'] ?? null;
    $includeData = isset($_POST['include_data']);
    
    // Generate filename
    if (empty($backupName)) {
        $backupName = 'backup_' . date('Y-m-d_H-i-s');
    }
    $backupName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $backupName);
    $backupFile = $backupDir . '/' . $backupName . '.sql';
    
    // Build mysqldump command
    $command = '"' . $mysqldumpPath . '" -u ' . DB_USER . ' -h ' . DB_HOST;
    
    if (!empty(DB_PASS)) {
        $command .= ' -p' . escapeshellarg(DB_PASS);
    }
    
    if (!$includeData) {
        $command .= ' --no-data';
    }
    
    $command .= ' ' . DB_NAME . ' > "' . $backupFile . '" 2>&1';
    
    // Execute backup
    exec($command, $output, $returnCode);
    
    if ($returnCode === 0 && file_exists($backupFile)) {
        // Log backup creation
        $stmt = $conn->prepare("INSERT INTO audit_log (user_id, username, table_name, action, record_id, details, ip_address, timestamp_unix) VALUES (?, ?, 'database', 'BACKUP', 0, ?, ?, ?)");
        $username = $sessionManager->getUsername();
        $details = "Created backup: $backupName.sql";
        $ip = $_SERVER['REMOTE_ADDR'];
        $timestamp = time();
        $stmt->bind_param("isssi", $userId, $username, $details, $ip, $timestamp);
        $stmt->execute();
        $stmt->close();
        
        // Download file
        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="' . $backupName . '.sql"');
        header('Content-Length: ' . filesize($backupFile));
        readfile($backupFile);
        exit;
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false, 
            'message' => 'Greška pri kreiranju backupa: ' . implode("\n", $output)
        ]);
        exit;
    }
}

// ============================================================================
// ACTION: Download Existing Backup
// ============================================================================
if ($action === 'download') {
    $filename = $_GET['file'] ?? '';
    $filename = basename($filename); // Security: prevent directory traversal
    $filepath = $backupDir . '/' . $filename;
    
    if (!file_exists($filepath) || !is_file($filepath)) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'File not found']);
        exit;
    }
    
    // Download file
    header('Content-Type: application/sql');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . filesize($filepath));
    readfile($filepath);
    exit;
}

// ============================================================================
// ACTION: Delete Backup
// ============================================================================
if ($action === 'delete') {
    $filename = $_GET['file'] ?? '';
    $filename = basename($filename); // Security: prevent directory traversal
    $filepath = $backupDir . '/' . $filename;
    
    if (!file_exists($filepath) || !is_file($filepath)) {
        echo json_encode(['success' => false, 'message' => 'File not found']);
        exit;
    }
    
    if (unlink($filepath)) {
        // Log deletion
        $stmt = $conn->prepare("INSERT INTO audit_log (user_id, username, table_name, action, record_id, details, ip_address, timestamp_unix) VALUES (?, ?, 'database', 'DELETE', 0, ?, ?, ?)");
        $username = $sessionManager->getUsername();
        $details = "Deleted backup: $filename";
        $ip = $_SERVER['REMOTE_ADDR'];
        $timestamp = time();
        $stmt->bind_param("isssi", $userId, $username, $details, $ip, $timestamp);
        $stmt->execute();
        $stmt->close();
        
        echo json_encode(['success' => true, 'message' => 'Backup deleted']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete file']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action']);
