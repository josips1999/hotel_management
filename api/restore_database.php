<?php
/**
 * API: Database Restore Operations
 * 
 * Restores database from uploaded SQL file
 * WARNING: This will overwrite all current data!
 */

require_once('../lib/db_connection.php');
require_once('../lib/config.php');
require_once('../lib/SessionManager.php');

header('Content-Type: application/json');

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

// Check if file was uploaded
if (!isset($_FILES['backup_file']) || $_FILES['backup_file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error']);
    exit;
}

$file = $_FILES['backup_file'];

// Validate file extension
$filename = $file['name'];
$extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
if ($extension !== 'sql') {
    echo json_encode(['success' => false, 'message' => 'Only .sql files are allowed']);
    exit;
}

// Validate file size (max 50 MB)
$maxSize = 50 * 1024 * 1024; // 50 MB
if ($file['size'] > $maxSize) {
    echo json_encode(['success' => false, 'message' => 'File too large (max 50 MB)']);
    exit;
}

// Read SQL content
$sqlContent = file_get_contents($file['tmp_name']);
if ($sqlContent === false) {
    echo json_encode(['success' => false, 'message' => 'Failed to read file']);
    exit;
}

// MySQL binary path
$mysqlBinPath = defined('MYSQL_BIN_PATH') ? MYSQL_BIN_PATH : 'C:/xampp/mysql/bin/';
$mysqlPath = rtrim($mysqlBinPath, '/') . '/mysql.exe';

// Save uploaded file temporarily
$tempFile = sys_get_temp_dir() . '/restore_' . time() . '.sql';
if (!move_uploaded_file($file['tmp_name'], $tempFile)) {
    echo json_encode(['success' => false, 'message' => 'Failed to save uploaded file']);
    exit;
}

try {
    // Method 1: Using mysql command (preferred for large files)
    if (file_exists($mysqlPath)) {
        $command = '"' . $mysqlPath . '" -u ' . DB_USER . ' -h ' . DB_HOST;
        
        if (!empty(DB_PASS)) {
            $command .= ' -p' . escapeshellarg(DB_PASS);
        }
        
        $command .= ' ' . DB_NAME . ' < "' . $tempFile . '" 2>&1';
        
        exec($command, $output, $returnCode);
        
        if ($returnCode === 0) {
            // Log restore
            $stmt = $conn->prepare("INSERT INTO audit_log (user_id, username, table_name, action, record_id, details, ip_address, timestamp_unix) VALUES (?, ?, 'database', 'RESTORE', 0, ?, ?, ?)");
            $username = $sessionManager->getUsername();
            $details = "Restored database from: $filename";
            $ip = $_SERVER['REMOTE_ADDR'];
            $timestamp = time();
            $stmt->bind_param("isssi", $userId, $username, $details, $ip, $timestamp);
            $stmt->execute();
            $stmt->close();
            
            // Clean up
            unlink($tempFile);
            
            echo json_encode(['success' => true, 'message' => 'Database successfully restored']);
            exit;
        } else {
            throw new Exception('MySQL command failed: ' . implode("\n", $output));
        }
    }
    
    // Method 2: Using mysqli_multi_query (fallback for smaller files)
    // Disable foreign key checks temporarily
    $conn->query("SET FOREIGN_KEY_CHECKS = 0");
    
    // Split SQL into statements
    $statements = array_filter(
        array_map('trim', preg_split('/;\s*$/m', $sqlContent)),
        function($stmt) { return !empty($stmt) && substr($stmt, 0, 2) !== '--'; }
    );
    
    $successCount = 0;
    $errorCount = 0;
    $lastError = '';
    
    foreach ($statements as $statement) {
        if (empty($statement)) continue;
        
        if ($conn->query($statement)) {
            $successCount++;
        } else {
            $errorCount++;
            $lastError = $conn->error;
        }
    }
    
    // Re-enable foreign key checks
    $conn->query("SET FOREIGN_KEY_CHECKS = 1");
    
    // Clean up
    unlink($tempFile);
    
    if ($errorCount === 0) {
        // Log restore
        $stmt = $conn->prepare("INSERT INTO audit_log (user_id, username, table_name, action, record_id, details, ip_address, timestamp_unix) VALUES (?, ?, 'database', 'RESTORE', 0, ?, ?, ?)");
        $username = $sessionManager->getUsername();
        $details = "Restored database from: $filename ($successCount statements)";
        $ip = $_SERVER['REMOTE_ADDR'];
        $timestamp = time();
        $stmt->bind_param("isssi", $userId, $username, $details, $ip, $timestamp);
        $stmt->execute();
        $stmt->close();
        
        echo json_encode([
            'success' => true, 
            'message' => "Database successfully restored ($successCount statements executed)"
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => "Partial restore: $successCount successful, $errorCount failed. Last error: $lastError"
        ]);
    }
    
} catch (Exception $e) {
    // Clean up on error
    if (file_exists($tempFile)) {
        unlink($tempFile);
    }
    
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
