<?php
/**
 * HTTPS Security Checker
 * Ensures that authentication pages are accessed only via HTTPS
 */

class HTTPSChecker {
    
    /**
     * Check if current connection is HTTPS
     * @return bool - True if HTTPS, false if HTTP
     */
    public static function isHTTPS() {
        // Check various server variables for HTTPS
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            return true;
        }
        
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            return true;
        }
        
        if (isset($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on') {
            return true;
        }
        
        if (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Force HTTPS - Redirect to HTTPS if not already
     * @param bool $permanentRedirect - Use 301 (permanent) or 302 (temporary) redirect
     */
    public static function forceHTTPS($permanentRedirect = true) {
        // Skip redirect for localhost development
        if (self::isLocalhost()) {
            return;
        }
        
        if (!self::isHTTPS()) {
            $redirectURL = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            $statusCode = $permanentRedirect ? 301 : 302;
            
            header("Location: $redirectURL", true, $statusCode);
            exit;
        }
    }
    
    /**
     * Check if running on localhost
     * @return bool
     */
    public static function isLocalhost() {
        $localhostIPs = ['127.0.0.1', '::1', 'localhost'];
        $serverName = $_SERVER['SERVER_NAME'] ?? '';
        $remoteAddr = $_SERVER['REMOTE_ADDR'] ?? '';
        
        return in_array($serverName, $localhostIPs) || 
               in_array($remoteAddr, $localhostIPs) ||
               strpos($serverName, 'localhost') !== false;
    }
    
    /**
     * Require HTTPS for specific pages (login, register, etc.)
     * Displays error message if accessed via HTTP on production
     */
    public static function requireHTTPSForAuth() {
        if (self::isLocalhost()) {
            // Show warning on localhost but don't block
            if (!self::isHTTPS()) {
                $_SESSION['https_warning'] = 'Upozorenje: Za produkciju omogući HTTPS! Trenutno koristiš HTTP.';
            }
            return;
        }
        
        if (!self::isHTTPS()) {
            // On production, force redirect
            self::forceHTTPS();
        }
    }
    
    /**
     * Get protocol (http or https)
     * @return string
     */
    public static function getProtocol() {
        return self::isHTTPS() ? 'https' : 'http';
    }
    
    /**
     * Get full URL with protocol
     * @param string $path - Path to append
     * @return string - Full URL
     */
    public static function getFullURL($path = '') {
        $protocol = self::getProtocol();
        $host = $_SERVER['HTTP_HOST'];
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        
        return $protocol . '://' . $host . $basePath . '/' . ltrim($path, '/');
    }
    
    /**
     * Check if HTTPS is properly configured with valid certificate
     * @return array - Status information
     */
    public static function checkSSLStatus() {
        $status = [
            'https_enabled' => self::isHTTPS(),
            'localhost' => self::isLocalhost(),
            'protocol' => self::getProtocol(),
            'port' => $_SERVER['SERVER_PORT'] ?? 'unknown',
            'recommendation' => ''
        ];
        
        if (!$status['https_enabled'] && !$status['localhost']) {
            $status['recommendation'] = 'HTTPS nije omogućen. Omogući SSL certifikat za sigurnu autentifikaciju.';
        } elseif (!$status['https_enabled'] && $status['localhost']) {
            $status['recommendation'] = 'Localhost development mode. Za produkciju obavezno omogući HTTPS.';
        } else {
            $status['recommendation'] = 'HTTPS je pravilno konfiguriran. Sigurna konekcija aktivna.';
        }
        
        return $status;
    }
}