<?php
/**
 * CookieManager - Helper class for managing cookies
 * Handles terms acceptance, privacy settings, and cookie consent
 */

class CookieManager {
    
    // Cookie names
    const TERMS_ACCEPTED = 'hotel_terms_accepted';
    const COOKIE_CONSENT = 'hotel_cookie_consent';
    const USER_PREFERENCES = 'hotel_user_preferences';
    
    // Cookie durations (in seconds)
    const ONE_YEAR = 31536000; // 365 days
    const ONE_MONTH = 2592000; // 30 days
    const ONE_WEEK = 604800;   // 7 days
    
    /**
     * Set a cookie with proper security settings
     */
    public static function setCookie($name, $value, $duration = self::ONE_YEAR, $httpOnly = true, $secure = false) {
        $options = [
            'expires' => time() + $duration,
            'path' => '/',
            'domain' => '', // Current domain
            'secure' => $secure, // HTTPS only if true
            'httponly' => $httpOnly, // Not accessible via JavaScript if true
            'samesite' => 'Lax' // CSRF protection
        ];
        
        return setcookie($name, $value, $options);
    }
    
    /**
     * Get cookie value
     */
    public static function getCookie($name, $default = null) {
        return $_COOKIE[$name] ?? $default;
    }
    
    /**
     * Check if cookie exists
     */
    public static function hasCookie($name) {
        return isset($_COOKIE[$name]);
    }
    
    /**
     * Delete cookie
     */
    public static function deleteCookie($name) {
        if (self::hasCookie($name)) {
            setcookie($name, '', time() - 3600, '/');
            unset($_COOKIE[$name]);
            return true;
        }
        return false;
    }
    
    /**
     * Set terms accepted cookie
     */
    public static function setTermsAccepted($version = '1.0') {
        $data = json_encode([
            'version' => $version,
            'accepted_at' => time(),
            'ip_address' => self::getClientIP()
        ]);
        
        return self::setCookie(self::TERMS_ACCEPTED, $data, self::ONE_YEAR, false);
    }
    
    /**
     * Check if terms are accepted
     */
    public static function hasAcceptedTerms($requiredVersion = '1.0') {
        if (!self::hasCookie(self::TERMS_ACCEPTED)) {
            return false;
        }
        
        $data = json_decode(self::getCookie(self::TERMS_ACCEPTED), true);
        
        if (!$data || !isset($data['version'])) {
            return false;
        }
        
        // Check if accepted version matches required version
        return version_compare($data['version'], $requiredVersion, '>=');
    }
    
    /**
     * Get terms acceptance info
     */
    public static function getTermsInfo() {
        if (!self::hasCookie(self::TERMS_ACCEPTED)) {
            return null;
        }
        
        return json_decode(self::getCookie(self::TERMS_ACCEPTED), true);
    }
    
    /**
     * Set cookie consent (for GDPR compliance)
     */
    public static function setCookieConsent($analytical = false, $marketing = false) {
        $data = json_encode([
            'essential' => true, // Always true
            'analytical' => $analytical,
            'marketing' => $marketing,
            'timestamp' => time()
        ]);
        
        return self::setCookie(self::COOKIE_CONSENT, $data, self::ONE_YEAR, false);
    }
    
    /**
     * Check if user has given cookie consent
     */
    public static function hasCookieConsent() {
        return self::hasCookie(self::COOKIE_CONSENT);
    }
    
    /**
     * Get cookie consent preferences
     */
    public static function getCookieConsent() {
        if (!self::hasCookie(self::COOKIE_CONSENT)) {
            return null;
        }
        
        return json_decode(self::getCookie(self::COOKIE_CONSENT), true);
    }
    
    /**
     * Check if specific cookie type is allowed
     */
    public static function isAllowed($type) {
        $consent = self::getCookieConsent();
        
        if (!$consent) {
            return false;
        }
        
        return $consent[$type] ?? false;
    }
    
    /**
     * Save user preferences (theme, language, etc.)
     */
    public static function setPreferences($preferences) {
        $data = json_encode($preferences);
        return self::setCookie(self::USER_PREFERENCES, $data, self::ONE_MONTH, false);
    }
    
    /**
     * Get user preferences
     */
    public static function getPreferences() {
        if (!self::hasCookie(self::USER_PREFERENCES)) {
            return [];
        }
        
        return json_decode(self::getCookie(self::USER_PREFERENCES), true) ?? [];
    }
    
    /**
     * Get client IP address (handles proxies)
     */
    private static function getClientIP() {
        $ipHeaders = [
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'REMOTE_ADDR'
        ];
        
        foreach ($ipHeaders as $header) {
            if (isset($_SERVER[$header]) && filter_var($_SERVER[$header], FILTER_VALIDATE_IP)) {
                return $_SERVER[$header];
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
    
    /**
     * Clear all application cookies
     */
    public static function clearAll() {
        $cookies = [
            self::TERMS_ACCEPTED,
            self::COOKIE_CONSENT,
            self::USER_PREFERENCES
        ];
        
        foreach ($cookies as $cookie) {
            self::deleteCookie($cookie);
        }
        
        return true;
    }
    
    /**
     * Get all cookie information for debugging
     */
    public static function getAllInfo() {
        return [
            'terms_accepted' => self::getTermsInfo(),
            'cookie_consent' => self::getCookieConsent(),
            'user_preferences' => self::getPreferences(),
            'has_terms' => self::hasAcceptedTerms(),
            'has_consent' => self::hasCookieConsent()
        ];
    }
}
