<?php
/**
 * AuditLogger Class
 * 
 * Reusable logger for tracking all data changes in the database.
 * Records table name, action type, old/new data, user info, and Unix timestamp.
 * 
 * Usage:
 *   $logger = new AuditLogger($connection, $userId);
 *   $logger->log('hotels', $hotelId, 'INSERT', null, $newData);
 *   $logger->log('hotels', $hotelId, 'UPDATE', $oldData, $newData);
 *   $logger->log('hotels', $hotelId, 'DELETE', $oldData, null);
 */

class AuditLogger
{
    private $db;
    private $userId;
    private $ipAddress;
    private $userAgent;
    
    /**
     * Constructor
     * 
     * @param mysqli $db Database connection
     * @param int|null $userId ID of the user making the change (null for system actions)
     */
    public function __construct($db, $userId = null)
    {
        $this->db = $db;
        $this->userId = $userId;
        $this->ipAddress = $this->getClientIP();
        $this->userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;
    }
    
    /**
     * Log a data change
     * 
     * @param string $tableName Name of the table where change occurred
     * @param int $recordId ID of the record that was changed
     * @param string $action Action type: 'INSERT', 'UPDATE', or 'DELETE'
     * @param array|null $oldData Old data before change (for UPDATE/DELETE)
     * @param array|null $newData New data after change (for INSERT/UPDATE)
     * @return bool Success status
     */
    public function log($tableName, $recordId, $action, $oldData = null, $newData = null)
    {
        // Validate action type
        $validActions = ['INSERT', 'UPDATE', 'DELETE'];
        if (!in_array($action, $validActions)) {
            error_log("Invalid audit action: {$action}");
            return false;
        }
        
        // Convert data to JSON
        $oldDataJson = $oldData ? json_encode($oldData, JSON_UNESCAPED_UNICODE) : null;
        $newDataJson = $newData ? json_encode($newData, JSON_UNESCAPED_UNICODE) : null;
        
        // Unix timestamp (current time)
        $timestamp = time();
        
        // Prepare SQL statement
        $sql = "INSERT INTO audit_log 
                (table_name, record_id, action, old_data, new_data, changed_by, changed_at, ip_address, user_agent) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        
        if (!$stmt) {
            error_log("Audit log prepare failed: " . $this->db->error);
            return false;
        }
        
        $stmt->bind_param(
            "sisssiiis",
            $tableName,
            $recordId,
            $action,
            $oldDataJson,
            $newDataJson,
            $this->userId,
            $timestamp,
            $this->ipAddress,
            $this->userAgent
        );
        
        $success = $stmt->execute();
        
        if (!$success) {
            error_log("Audit log insert failed: " . $stmt->error);
        }
        
        $stmt->close();
        
        return $success;
    }
    
    /**
     * Log INSERT action (new record created)
     * 
     * @param string $tableName Table name
     * @param int $recordId Record ID
     * @param array $newData New data
     * @return bool Success status
     */
    public function logInsert($tableName, $recordId, $newData)
    {
        return $this->log($tableName, $recordId, 'INSERT', null, $newData);
    }
    
    /**
     * Log UPDATE action (existing record modified)
     * 
     * @param string $tableName Table name
     * @param int $recordId Record ID
     * @param array $oldData Old data before update
     * @param array $newData New data after update
     * @return bool Success status
     */
    public function logUpdate($tableName, $recordId, $oldData, $newData)
    {
        return $this->log($tableName, $recordId, 'UPDATE', $oldData, $newData);
    }
    
    /**
     * Log DELETE action (record deleted)
     * 
     * @param string $tableName Table name
     * @param int $recordId Record ID
     * @param array $oldData Data before deletion
     * @return bool Success status
     */
    public function logDelete($tableName, $recordId, $oldData)
    {
        return $this->log($tableName, $recordId, 'DELETE', $oldData, null);
    }
    
    /**
     * Get audit history for specific record
     * 
     * @param string $tableName Table name
     * @param int $recordId Record ID
     * @param int $limit Maximum number of records to return
     * @return array Array of audit log entries
     */
    public function getHistory($tableName, $recordId, $limit = 50)
    {
        $sql = "SELECT al.*, u.username 
                FROM audit_log al
                LEFT JOIN users u ON al.changed_by = u.id
                WHERE al.table_name = ? AND al.record_id = ?
                ORDER BY al.changed_at DESC
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("sii", $tableName, $recordId, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $history = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        return $history;
    }
    
    /**
     * Get recent changes (last N actions)
     * 
     * @param int $limit Number of recent changes
     * @param string|null $tableName Filter by table name (optional)
     * @return array Array of audit log entries
     */
    public function getRecentChanges($limit = 100, $tableName = null)
    {
        if ($tableName) {
            $sql = "SELECT al.*, u.username 
                    FROM audit_log al
                    LEFT JOIN users u ON al.changed_by = u.id
                    WHERE al.table_name = ?
                    ORDER BY al.changed_at DESC
                    LIMIT ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("si", $tableName, $limit);
        } else {
            $sql = "SELECT al.*, u.username 
                    FROM audit_log al
                    LEFT JOIN users u ON al.changed_by = u.id
                    ORDER BY al.changed_at DESC
                    LIMIT ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("i", $limit);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $changes = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        return $changes;
    }
    
    /**
     * Get user's activity log
     * 
     * @param int $userId User ID
     * @param int $limit Maximum number of records
     * @return array Array of audit log entries
     */
    public function getUserActivity($userId, $limit = 100)
    {
        $sql = "SELECT al.*, u.username 
                FROM audit_log al
                LEFT JOIN users u ON al.changed_by = u.id
                WHERE al.changed_by = ?
                ORDER BY al.changed_at DESC
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ii", $userId, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $activity = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        return $activity;
    }
    
    /**
     * Get statistics about changes
     * 
     * @param int|null $days Number of days to look back (null for all time)
     * @return array Statistics array
     */
    public function getStatistics($days = null)
    {
        $whereClause = '';
        if ($days !== null) {
            $timestamp = time() - ($days * 86400); // 86400 seconds in a day
            $whereClause = "WHERE changed_at >= {$timestamp}";
        }
        
        $sql = "SELECT 
                    COUNT(*) as total_changes,
                    SUM(CASE WHEN action = 'INSERT' THEN 1 ELSE 0 END) as inserts,
                    SUM(CASE WHEN action = 'UPDATE' THEN 1 ELSE 0 END) as updates,
                    SUM(CASE WHEN action = 'DELETE' THEN 1 ELSE 0 END) as deletes,
                    COUNT(DISTINCT changed_by) as unique_users,
                    COUNT(DISTINCT table_name) as affected_tables
                FROM audit_log
                {$whereClause}";
        
        $result = $this->db->query($sql);
        $stats = $result->fetch_assoc();
        
        return $stats;
    }
    
    /**
     * Get client IP address (handles proxies)
     * 
     * @return string|null IP address
     */
    private function getClientIP()
    {
        $ipKeys = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];
        
        foreach ($ipKeys as $key) {
            if (isset($_SERVER[$key]) && !empty($_SERVER[$key])) {
                $ips = explode(',', $_SERVER[$key]);
                $ip = trim($ips[0]);
                
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        
        return null;
    }
    
    /**
     * Format Unix timestamp to human-readable date
     * 
     * @param int $timestamp Unix timestamp
     * @param string $format Date format (default: 'Y-m-d H:i:s')
     * @return string Formatted date
     */
    public static function formatTimestamp($timestamp, $format = 'Y-m-d H:i:s')
    {
        return date($format, $timestamp);
    }
    
    /**
     * Get time ago string (e.g., "5 minutes ago")
     * 
     * @param int $timestamp Unix timestamp
     * @return string Time ago string
     */
    public static function timeAgo($timestamp)
    {
        $diff = time() - $timestamp;
        
        if ($diff < 60) {
            return 'prije ' . $diff . ' sekundi';
        } elseif ($diff < 3600) {
            $minutes = floor($diff / 60);
            return 'prije ' . $minutes . ' minuta';
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return 'prije ' . $hours . ' sati';
        } elseif ($diff < 604800) {
            $days = floor($diff / 86400);
            return 'prije ' . $days . ' dana';
        } else {
            return date('d.m.Y H:i', $timestamp);
        }
    }
}
