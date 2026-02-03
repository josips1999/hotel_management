<?php
/**
 * SEO URL Helper Functions
 * 
 * Provides functions for creating SEO-friendly URLs (slugs)
 * Converts Croatian text to URL-safe format
 */

class SEOHelper {
    /**
     * Convert text to SEO-friendly slug
     * Handles Croatian characters (č, ć, š, ž, đ)
     * 
     * @param string $text Text to convert
     * @param string $separator Separator character (default: -)
     * @return string SEO-friendly slug
     */
    public static function createSlug($text, $separator = '-') {
        // Croatian character mapping
        $croatianMap = [
            'č' => 'c', 'ć' => 'c', 'š' => 's', 'ž' => 'z', 'đ' => 'd',
            'Č' => 'C', 'Ć' => 'C', 'Š' => 'S', 'Ž' => 'Z', 'Đ' => 'D'
        ];
        
        // Replace Croatian characters
        $text = strtr($text, $croatianMap);
        
        // Convert to lowercase
        $text = strtolower($text);
        
        // Replace non-alphanumeric characters with separator
        $text = preg_replace('/[^a-z0-9]+/', $separator, $text);
        
        // Remove leading/trailing separators
        $text = trim($text, $separator);
        
        // Remove duplicate separators
        $text = preg_replace('/' . preg_quote($separator, '/') . '+/', $separator, $text);
        
        return $text;
    }
    
    /**
     * Generate hotel URL
     * 
     * @param int $id Hotel ID
     * @param string $name Hotel name
     * @return string SEO-friendly URL
     */
    public static function hotelUrl($id, $name) {
        $slug = self::createSlug($name);
        return "/hotel/$id/$slug";
    }
    
    /**
     * Generate absolute hotel URL
     * 
     * @param int $id Hotel ID
     * @param string $name Hotel name
     * @return string Full URL
     */
    public static function hotelUrlFull($id, $name) {
        $baseUrl = self::getBaseUrl();
        $slug = self::createSlug($name);
        return "$baseUrl/hotel/$id/$slug";
    }
    
    /**
     * Get base URL of the application
     * 
     * @return string Base URL
     */
    public static function getBaseUrl() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $folder = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        return "$protocol://$host$folder";
    }
    
    /**
     * Generate pagination URL
     * 
     * @param int $page Page number
     * @param array $params Additional query parameters
     * @return string URL
     */
    public static function paginationUrl($page, $params = []) {
        $params['page'] = $page;
        $query = http_build_query($params);
        return '?' . $query;
    }
    
    /**
     * Sanitize output for HTML (XSS protection)
     * 
     * @param string $text Text to sanitize
     * @return string Sanitized text
     */
    public static function escape($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Create meta description from text
     * 
     * @param string $text Text to convert
     * @param int $maxLength Maximum length (default: 160)
     * @return string Meta description
     */
    public static function createMetaDescription($text, $maxLength = 160) {
        // Remove HTML tags
        $text = strip_tags($text);
        
        // Remove extra whitespace
        $text = preg_replace('/\s+/', ' ', trim($text));
        
        // Truncate to max length
        if (mb_strlen($text) > $maxLength) {
            $text = mb_substr($text, 0, $maxLength - 3) . '...';
        }
        
        return self::escape($text);
    }
    
    /**
     * Create page title for SEO
     * 
     * @param string $title Page title
     * @param bool $includeAppName Include application name
     * @return string Full page title
     */
    public static function createPageTitle($title, $includeAppName = true) {
        $appName = 'Hotel Management System';
        
        if ($includeAppName) {
            return self::escape($title) . ' | ' . $appName;
        }
        
        return self::escape($title);
    }
}
