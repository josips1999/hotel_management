<?php
/**
 * Session Manager Class
 * Manages user sessions and secure token-based "Remember Me" functionality
 * 
 * Uses split-token approach for security:
 * - Selector: Public identifier (plain in DB and cookie)
 * - Validator: Secret token (hashed in DB, plain in cookie)
 * - Cookie format: selector:validator
 */

require_once(__DIR__ . '/config.php');

class SessionManager {
    
    private $sessionName = 'HOTEL_MANAGEMENT_SESSION';
    private $cookieName;
    private $cookieExpiry; // Duration in seconds
    private $connection;
    
    /**
     * Constructor - Initialize session
     * @param object $connection - Database connection (optional)
     */
    public function __construct($connection = null) {
        $this->connection = $connection;
        $this->cookieName = REMEMBER_ME_COOKIE_NAME;
        $this->cookieExpiry = REMEMBER_ME_DURATION_DAYS * 24 * 60 * 60; // Convert days to seconds
        
        if (session_status() === PHP_SESSION_NONE) {
            session_name($this->sessionName);
            
            // Check if HTTPS is enabled
            $isHTTPS = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || 
                       (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
            
            // Secure session configuration
            session_set_cookie_params([
                'lifetime' => 0, // Session cookie (expires when browser closes)
                'path' => '/',
                'domain' => '',
                'secure' => $isHTTPS, // Use secure cookies only on HTTPS
                'httponly' => true, // Prevent JavaScript access
                'samesite' => 'Lax' // CSRF protection
            ]);
            
            session_start();
            
            // Regenerate session ID to prevent session fixation
            if (!isset($_SESSION['initiated'])) {
                session_regenerate_id(true);
                $_SESSION['initiated'] = true;
            }
        }
    }
    
    /**
     * Login user - Create session and optional remember me token
     * @param int $userId - User ID
     * @param string $username - Username
     * @param string $email - Email
     * @param bool $rememberMe - Create remember me token
     * @return bool - Success status
     */
    public function login($userId, $username, $email, $rememberMe = false) {
        // Regenerate session ID for security
        session_regenerate_id(true);
        
        // Store user data in session
        $_SESSION['user_id'] = $userId;
        $_SESSION['username'] = $username;
        $_SESSION['email'] = $email;
        $_SESSION['is_logged_in'] = true;
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        
        // Create remember me token if requested
        if ($rememberMe && $this->connection) {
            $this->createRememberToken($userId);
        }
        
        return true;
    }
    
    /**
     * Logout user - Destroy session and delete remember token
     * @return bool - Success status
     */
    public function logout() {
        // Delete remember token from database and cookie
        $this->deleteRememberToken();
        
        // Clear session variables
        $_SESSION = [];
        
        // Delete session cookie
        if (isset($_COOKIE[$this->sessionName])) {
            setcookie($this->sessionName, '', time() - 3600, '/');
        }
        
        // Destroy session
        session_destroy();
        
        return true;
    }
    
    /**
     * Check if user is logged in
     * @return bool - True if logged in
     */
    public function isLoggedIn() {
        // Check session
        if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
            return false;
        }
        
        // Check session timeout (configurable minutes of inactivity)
        if (isset($_SESSION['last_activity'])) {
            $inactiveTime = time() - $_SESSION['last_activity'];
            $timeoutSeconds = SESSION_TIMEOUT_MINUTES * 60;
            if ($inactiveTime > $timeoutSeconds) {
                $this->logout();
                return false;
            }
        }
        
        // Update last activity time
        $_SESSION['last_activity'] = time();
        
        // Validate IP and User Agent to prevent session hijacking
        if (isset($_SESSION['ip_address']) && $_SESSION['ip_address'] !== $_SERVER['REMOTE_ADDR']) {
            $this->logout();
            return false;
        }
        
        if (isset($_SESSION['user_agent']) && $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
            $this->logout();
            return false;
        }
        
        return true;
    }
    
    /**
     * Get current user ID
     * @return int|null - User ID or null if not logged in
     */
    public function getUserId() {
        return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    }
    
    /**
     * Get current username
     * @return string|null - Username or null if not logged in
     */
    public function getUsername() {
        return isset($_SESSION['username']) ? $_SESSION['username'] : null;
    }
    
    /**
     * Get current user email
     * @return string|null - Email or null if not logged in
     */
    public function getEmail() {
        return isset($_SESSION['email']) ? $_SESSION['email'] : null;
    }
    
    /**
     * Create remember token - Secure split-token approach
     * @param int $userId - User ID
     * @return bool - Success status
     */
    private function createRememberToken($userId) {
        if (!$this->connection) {
            return false;
        }
        
        try {
            // Delete any existing tokens for this user (single device login)
            $this->deleteUserRememberTokens($userId);
            
            // Generate selector (public identifier - 16 bytes = 32 hex chars)
            $selector = bin2hex(random_bytes(TOKEN_SELECTOR_BYTES));
            
            // Generate validator (secret token - 32 bytes = 64 hex chars)
            $validator = bin2hex(random_bytes(TOKEN_VALIDATOR_BYTES));
            
            // Hash validator for database storage (never store plain validator)
            $hashedValidator = password_hash($validator, PASSWORD_DEFAULT);
            
            // Calculate expiry datetime
            $expiresAt = date('Y-m-d H:i:s', time() + $this->cookieExpiry);
            
            // Get client info for security tracking
            $ipAddress = $_SERVER['REMOTE_ADDR'];
            $userAgent = substr($_SERVER['HTTP_USER_AGENT'], 0, 255); // Limit length
            
            // Insert token into database
            $stmt = $this->connection->prepare(
                "INSERT INTO remember_tokens (user_id, selector, hashed_validator, expires_at, ip_address, user_agent) 
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param("isssss", $userId, $selector, $hashedValidator, $expiresAt, $ipAddress, $userAgent);
            $stmt->execute();
            $stmt->close();
            
            // Create cookie with selector:validator (plain)
            $cookieValue = $selector . ':' . $validator;
            
            // Check if HTTPS is enabled for secure cookie
            $isHTTPS = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || 
                       (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
            
            setcookie(
                $this->cookieName,
                $cookieValue,
                time() + $this->cookieExpiry,
                '/',
                '',
                $isHTTPS, // secure - use true on HTTPS, false on HTTP (localhost)
                true   // httponly - prevent JavaScript access
            );
            
            return true;
            
        } catch (Exception $e) {
            error_log("Create remember token error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete remember token from database and cookie
     */
    private function deleteRememberToken() {
        // Delete cookie
        if (isset($_COOKIE[$this->cookieName])) {
            setcookie($this->cookieName, '', time() - 3600, '/');
            
            // Delete token from database
            if ($this->connection) {
                try {
                    $parts = explode(':', $_COOKIE[$this->cookieName]);
                    if (count($parts) === 2) {
                        $selector = $parts[0];
                        
                        $stmt = $this->connection->prepare("DELETE FROM remember_tokens WHERE selector = ?");
                        $stmt->bind_param("s", $selector);
                        $stmt->execute();
                        $stmt->close();
                    }
                } catch (Exception $e) {
                    error_log("Delete remember token error: " . $e->getMessage());
                }
            }
            
            unset($_COOKIE[$this->cookieName]);
        }
    }
    
    /**
     * Delete all remember tokens for a specific user
     * @param int $userId - User ID
     */
    private function deleteUserRememberTokens($userId) {
        if (!$this->connection) {
            return;
        }
        
        try {
            $stmt = $this->connection->prepare("DELETE FROM remember_tokens WHERE user_id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $stmt->close();
        } catch (Exception $e) {
            error_log("Delete user remember tokens error: " . $e->getMessage());
        }
    }
    
    /**
     * Check and restore session from remember token
     * Uses secure split-token validation
     * @return bool - True if session restored
     */
    public function checkRememberMe() {
        // If already logged in, skip
        if ($this->isLoggedIn()) {
            return true;
        }
        
        // Check if database connection exists
        if (!$this->connection) {
            return false;
        }
        
        // Check if remember me cookie exists
        if (!isset($_COOKIE[$this->cookieName])) {
            return false;
        }
        
        try {
            // Parse cookie value (format: selector:validator)
            $cookieParts = explode(':', $_COOKIE[$this->cookieName]);
            
            if (count($cookieParts) !== 2) {
                $this->deleteRememberToken();
                return false;
            }
            
            list($selector, $validator) = $cookieParts;
            
            // Get token from database using selector
            $stmt = $this->connection->prepare(
                "SELECT user_id, hashed_validator, expires_at 
                 FROM remember_tokens 
                 WHERE selector = ? AND expires_at > NOW()"
            );
            $stmt->bind_param("s", $selector);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows !== 1) {
                $stmt->close();
                $this->deleteRememberToken();
                return false;
            }
            
            $tokenData = $result->fetch_assoc();
            $stmt->close();
            
            // Verify validator using timing-safe comparison
            if (!password_verify($validator, $tokenData['hashed_validator'])) {
                // Invalid validator - possible attack, delete all tokens for this user
                $this->deleteUserRememberTokens($tokenData['user_id']);
                $this->deleteRememberToken();
                return false;
            }
            
            // Get user data
            $stmt = $this->connection->prepare(
                "SELECT id, username, email, is_verified 
                 FROM users 
                 WHERE id = ? AND is_verified = 1"
            );
            $stmt->bind_param("i", $tokenData['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows !== 1) {
                $stmt->close();
                $this->deleteRememberToken();
                return false;
            }
            
            $user = $result->fetch_assoc();
            $stmt->close();
            
            // Update last_used_at timestamp
            $this->updateTokenLastUsed($selector);
            
            // Restore session (without creating new remember token)
            $this->login($user['id'], $user['username'], $user['email'], false);
            
            // Optionally rotate token for better security (uncomment to enable)
            // $this->createRememberToken($user['id']);
            
            return true;
            
        } catch (Exception $e) {
            error_log("Check remember token error: " . $e->getMessage());
            $this->deleteRememberToken();
            return false;
        }
    }
    
    /**
     * Update token last_used_at timestamp
     * @param string $selector - Token selector
     */
    private function updateTokenLastUsed($selector) {
        if (!$this->connection) {
            return;
        }
        
        try {
            $stmt = $this->connection->prepare(
                "UPDATE remember_tokens SET last_used_at = NOW() WHERE selector = ?"
            );
            $stmt->bind_param("s", $selector);
            $stmt->execute();
            $stmt->close();
        } catch (Exception $e) {
            error_log("Update token last used error: " . $e->getMessage());
        }
    }
    
    /**
     * Require login - Redirect to login page if not logged in
     * @param string $redirectUrl - URL to redirect after login
     */
    public function requireLogin($redirectUrl = 'login.php') {
        if (!$this->isLoggedIn()) {
            header('Location: ' . $redirectUrl);
            exit;
        }
    }
    
    /**
     * Get session info for debugging
     * @return array - Session information
     */
    public function getSessionInfo() {
        return [
            'is_logged_in' => $this->isLoggedIn(),
            'user_id' => $this->getUserId(),
            'username' => $this->getUsername(),
            'email' => $this->getEmail(),
            'login_time' => isset($_SESSION['login_time']) ? date('Y-m-d H:i:s', $_SESSION['login_time']) : null,
            'last_activity' => isset($_SESSION['last_activity']) ? date('Y-m-d H:i:s', $_SESSION['last_activity']) : null,
            'remember_me_active' => isset($_COOKIE[$this->cookieName])
        ];
    }
    
    /**
     * Clean expired tokens from database (maintenance task)
     * Should be called periodically (e.g., cron job)
     * @return int - Number of tokens deleted
     */
    public function cleanExpiredTokens() {
        if (!$this->connection) {
            return 0;
        }
        
        try {
            $stmt = $this->connection->prepare("DELETE FROM remember_tokens WHERE expires_at < NOW()");
            $stmt->execute();
            $affectedRows = $stmt->affected_rows;
            $stmt->close();
            
            return $affectedRows;
        } catch (Exception $e) {
            error_log("Clean expired tokens error: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get all active remember tokens for current user (for security dashboard)
     * @return array - List of active tokens
     */
    public function getUserActiveTokens() {
        if (!$this->connection || !$this->isLoggedIn()) {
            return [];
        }
        
        try {
            $userId = $this->getUserId();
            $stmt = $this->connection->prepare(
                "SELECT id, created_at, last_used_at, expires_at, ip_address, user_agent 
                 FROM remember_tokens 
                 WHERE user_id = ? AND expires_at > NOW() 
                 ORDER BY created_at DESC"
            );
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $tokens = [];
            while ($row = $result->fetch_assoc()) {
                $tokens[] = $row;
            }
            
            $stmt->close();
            return $tokens;
            
        } catch (Exception $e) {
            error_log("Get user active tokens error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Revoke specific token by ID (for user-initiated logout of specific device)
     * @param int $tokenId - Token ID
     * @return bool - Success status
     */
    public function revokeToken($tokenId) {
        if (!$this->connection || !$this->isLoggedIn()) {
            return false;
        }
        
        try {
            $userId = $this->getUserId();
            $stmt = $this->connection->prepare(
                "DELETE FROM remember_tokens WHERE id = ? AND user_id = ?"
            );
            $stmt->bind_param("ii", $tokenId, $userId);
            $stmt->execute();
            $success = $stmt->affected_rows > 0;
            $stmt->close();
            
            return $success;
            
        } catch (Exception $e) {
            error_log("Revoke token error: " . $e->getMessage());
            return false;
        }
    }
}