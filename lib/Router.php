<?php
/**
 * Simple Router for SEO-Friendly URLs
 * 
 * Handles URL routing and parameter extraction
 * Supports patterns like: /hotel/123/naziv-hotela
 */

class Router {
    private $routes = [];
    
    /**
     * Add a route
     * 
     * @param string $pattern URL pattern (e.g., /hotel/{id}/{slug})
     * @param string $file PHP file to execute
     * @param array $defaults Default parameter values
     */
    public function addRoute($pattern, $file, $defaults = []) {
        $this->routes[] = [
            'pattern' => $pattern,
            'file' => $file,
            'defaults' => $defaults
        ];
    }
    
    /**
     * Match URL to a route
     * 
     * @param string $url Current URL
     * @return array|null Route data or null if no match
     */
    public function match($url) {
        // Remove query string
        $url = strtok($url, '?');
        
        // Remove leading/trailing slashes
        $url = trim($url, '/');
        
        foreach ($this->routes as $route) {
            $pattern = trim($route['pattern'], '/');
            
            // Convert pattern to regex
            $regex = $this->patternToRegex($pattern);
            
            if (preg_match($regex, $url, $matches)) {
                // Extract parameters
                $params = array_merge($route['defaults'], $this->extractParams($pattern, $matches));
                
                return [
                    'file' => $route['file'],
                    'params' => $params
                ];
            }
        }
        
        return null;
    }
    
    /**
     * Convert URL pattern to regex
     * 
     * @param string $pattern URL pattern
     * @return string Regex pattern
     */
    private function patternToRegex($pattern) {
        // Replace {param} with named capture group
        $regex = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $pattern);
        
        // Escape slashes
        $regex = str_replace('/', '\/', $regex);
        
        return '/^' . $regex . '$/';
    }
    
    /**
     * Extract parameters from URL match
     * 
     * @param string $pattern URL pattern
     * @param array $matches Regex matches
     * @return array Parameters
     */
    private function extractParams($pattern, $matches) {
        $params = [];
        
        // Extract named parameters
        foreach ($matches as $key => $value) {
            if (is_string($key)) {
                $params[$key] = $value;
            }
        }
        
        return $params;
    }
    
    /**
     * Dispatch the current request
     * 
     * @param string $url Current URL
     * @return bool True if route found and executed
     */
    public function dispatch($url) {
        $route = $this->match($url);
        
        if ($route) {
            // Set parameters in $_GET for backward compatibility
            $_GET = array_merge($_GET, $route['params']);
            
            // Include the target file
            if (file_exists($route['file'])) {
                require $route['file'];
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get current request URL
     * 
     * @return string Current URL path
     */
    public static function getCurrentUrl() {
        $url = $_SERVER['REQUEST_URI'];
        
        // Remove query string
        $url = strtok($url, '?');
        
        // Remove base folder from URL
        $scriptName = dirname($_SERVER['SCRIPT_NAME']);
        if ($scriptName !== '/') {
            $url = substr($url, strlen($scriptName));
        }
        
        return $url;
    }
}
