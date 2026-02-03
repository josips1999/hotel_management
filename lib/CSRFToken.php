<?php
/**
 * CSRF Token Protection Helper
 * 
 * Generates and validates CSRF tokens to prevent Cross-Site Request Forgery attacks
 * All forms must include a CSRF token that is validated on submission
 */

class CSRFToken {
    /**
     * Ensure session is started
     */
    private static function ensureSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Generate a new CSRF token and store it in session
     * 
     * @return string The generated token
     */
    public static function generate() {
        self::ensureSession();
        
        // Generate random token (32 bytes = 64 hex characters)
        $token = bin2hex(random_bytes(32));
        
        // Store in session
        $_SESSION['csrf_token'] = $token;
        $_SESSION['csrf_token_time'] = time();
        
        return $token;
    }
    
    /**
     * Get the current CSRF token (or generate new one if doesn't exist)
     * 
     * @return string The CSRF token
     */
    public static function get() {
        self::ensureSession();
        
        // Check if token exists and is not expired (1 hour expiry)
        if (isset($_SESSION['csrf_token']) && isset($_SESSION['csrf_token_time'])) {
            $tokenAge = time() - $_SESSION['csrf_token_time'];
            
            // Token valid for 1 hour
            if ($tokenAge < 3600) {
                return $_SESSION['csrf_token'];
            }
        }
        
        // Generate new token if doesn't exist or expired
        return self::generate();
    }
    
    /**
     * Validate submitted CSRF token
     * 
     * @param string $token The token to validate
     * @return bool True if valid, false otherwise
     */
    public static function validate($token) {
        self::ensureSession();
        
        // Check if session token exists
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }
        
        // Check if token is expired (1 hour)
        if (isset($_SESSION['csrf_token_time'])) {
            $tokenAge = time() - $_SESSION['csrf_token_time'];
            if ($tokenAge >= 3600) {
                return false;
            }
        }
        
        // Compare tokens using hash_equals to prevent timing attacks
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Get HTML hidden input field with CSRF token
     * 
     * @return string HTML input field
     */
    public static function getField() {
        $token = self::get();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }
    
    /**
     * Verify CSRF token from POST request
     * Dies with error message if validation fails
     * 
     * @return bool True if valid
     */
    public static function verifyPost() {
        $token = $_POST['csrf_token'] ?? '';
        
        if (!self::validate($token)) {
            http_response_code(403);
            die(json_encode([
                'success' => false,
                'message' => 'CSRF token validation failed. Please refresh the page and try again.'
            ]));
        }
        
        return true;
    }
    
    /**
     * Verify CSRF token from any request method
     * 
     * @return bool True if valid
     */
    public static function verify() {
        $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
        
        if (!self::validate($token)) {
            http_response_code(403);
            die(json_encode([
                'success' => false,
                'message' => 'CSRF token validation failed. Please refresh the page and try again.'
            ]));
        }
        
        return true;
    }
}
