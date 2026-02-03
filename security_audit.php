<?php
/**
 * Security Audit Helper
 * 
 * Scans PHP files for potential security issues:
 * - Missing htmlspecialchars() on echo/print statements
 * - Forms without CSRF tokens
 * - SQL queries without prepared statements
 */

class SecurityAudit {
    private $results = [];
    private $basePath;
    
    public function __construct($basePath) {
        $this->basePath = rtrim($basePath, '/');
    }
    
    /**
     * Scan directory for PHP files
     */
    public function scanDirectory($dir = '') {
        $scanPath = $this->basePath . ($dir ? '/' . $dir : '');
        $files = glob($scanPath . '/*.php');
        
        foreach ($files as $file) {
            $this->scanFile($file);
        }
        
        // Scan subdirectories
        $dirs = glob($scanPath . '/*', GLOB_ONLYDIR);
        foreach ($dirs as $subdir) {
            if (basename($subdir) !== 'vendor' && basename($subdir) !== 'node_modules') {
                $this->scanDirectory(str_replace($this->basePath . '/', '', $subdir));
            }
        }
    }
    
    /**
     * Scan single file for security issues
     */
    private function scanFile($file) {
        $content = file_get_contents($file);
        $lines = file($file);
        $relativePath = str_replace($this->basePath . '/', '', $file);
        
        $issues = [
            'xss' => [],
            'csrf' => [],
            'sql' => []
        ];
        
        // Check for potential XSS vulnerabilities
        foreach ($lines as $lineNum => $line) {
            $lineNum++; // 1-indexed
            
            // Check for echo/print without htmlspecialchars
            if (preg_match('/echo\s+\$[\w\[\]\'\"]+(?!\s*;)/i', $line) && 
                !preg_match('/htmlspecialchars|htmlentities|h\(|escape/i', $line)) {
                $issues['xss'][] = [
                    'line' => $lineNum,
                    'code' => trim($line),
                    'type' => 'Unescaped echo statement'
                ];
            }
            
            // Check for <?= without escaping
            if (preg_match('/<\?=\s*\$[\w\[\]\'\"]+/i', $line) && 
                !preg_match('/htmlspecialchars|htmlentities|escape/i', $line)) {
                $issues['xss'][] = [
                    'line' => $lineNum,
                    'code' => trim($line),
                    'type' => 'Unescaped short echo'
                ];
            }
        }
        
        // Check for forms without CSRF token
        if (preg_match_all('/<form[^>]*>/i', $content, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[0] as $match) {
                $formStart = $match[1];
                $formEnd = strpos($content, '</form>', $formStart);
                $formContent = substr($content, $formStart, $formEnd - $formStart);
                
                if (!preg_match('/csrf_token|CSRFToken/i', $formContent)) {
                    $lineNum = substr_count(substr($content, 0, $formStart), "\n") + 1;
                    $issues['csrf'][] = [
                        'line' => $lineNum,
                        'code' => 'Form without CSRF token',
                        'type' => 'Missing CSRF protection'
                    ];
                }
            }
        }
        
        // Check for SQL queries without prepared statements
        foreach ($lines as $lineNum => $line) {
            $lineNum++; // 1-indexed
            
            if (preg_match('/->query\s*\(\s*["\'].*\$|mysqli_query\s*\(.*\$/', $line)) {
                $issues['sql'][] = [
                    'line' => $lineNum,
                    'code' => trim($line),
                    'type' => 'Potential SQL injection (variable in query)'
                ];
            }
        }
        
        // Store results if issues found
        if (!empty($issues['xss']) || !empty($issues['csrf']) || !empty($issues['sql'])) {
            $this->results[$relativePath] = $issues;
        }
    }
    
    /**
     * Get scan results
     */
    public function getResults() {
        return $this->results;
    }
    
    /**
     * Generate HTML report
     */
    public function generateReport() {
        $totalIssues = 0;
        foreach ($this->results as $file => $issues) {
            $totalIssues += count($issues['xss']) + count($issues['csrf']) + count($issues['sql']);
        }
        
        echo "<h1>Security Audit Report</h1>\n";
        echo "<p>Total files with issues: " . count($this->results) . "</p>\n";
        echo "<p>Total issues found: $totalIssues</p>\n";
        
        foreach ($this->results as $file => $issues) {
            echo "<h2>$file</h2>\n";
            
            if (!empty($issues['xss'])) {
                echo "<h3 style='color: red;'>XSS Vulnerabilities (" . count($issues['xss']) . ")</h3>\n";
                echo "<ul>\n";
                foreach ($issues['xss'] as $issue) {
                    echo "<li>Line {$issue['line']}: {$issue['type']}<br><code>" . htmlspecialchars($issue['code']) . "</code></li>\n";
                }
                echo "</ul>\n";
            }
            
            if (!empty($issues['csrf'])) {
                echo "<h3 style='color: orange;'>CSRF Vulnerabilities (" . count($issues['csrf']) . ")</h3>\n";
                echo "<ul>\n";
                foreach ($issues['csrf'] as $issue) {
                    echo "<li>Line {$issue['line']}: {$issue['type']}</li>\n";
                }
                echo "</ul>\n";
            }
            
            if (!empty($issues['sql'])) {
                echo "<h3 style='color: red;'>SQL Injection Risks (" . count($issues['sql']) . ")</h3>\n";
                echo "<ul>\n";
                foreach ($issues['sql'] as $issue) {
                    echo "<li>Line {$issue['line']}: {$issue['type']}<br><code>" . htmlspecialchars($issue['code']) . "</code></li>\n";
                }
                echo "</ul>\n";
            }
        }
    }
}

// Run audit if called directly
if (basename($_SERVER['PHP_SELF']) === 'security_audit.php') {
    $audit = new SecurityAudit(__DIR__);
    $audit->scanDirectory();
    
    echo "<!DOCTYPE html>
    <html lang='hr'>
    <head>
        <meta charset='UTF-8'>
        <title>Security Audit Report</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            h1 { color: #333; }
            h2 { color: #666; border-bottom: 2px solid #ddd; padding-bottom: 5px; }
            h3 { margin-top: 20px; }
            code { background: #f4f4f4; padding: 2px 5px; border-radius: 3px; }
            ul { line-height: 1.8; }
        </style>
    </head>
    <body>";
    
    $audit->generateReport();
    
    echo "</body>
    </html>";
}
