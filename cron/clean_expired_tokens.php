<?php
/**
 * Cron Job - Clean Expired Remember Tokens
 * Pokreće se periodično (npr. svaki dan) za brisanje isteklih tokena
 * 
 * Setup (Linux):
 * crontab -e
 * 0 3 * * * /usr/bin/php /path/to/hotel_managment/cron/clean_expired_tokens.php
 * 
 * Setup (Windows Task Scheduler):
 * Program: C:\xampp\php\php.exe
 * Arguments: C:\xampp\htdocs\hotel_managment\cron\clean_expired_tokens.php
 * Schedule: Daily at 3:00 AM
 */

// Only allow CLI execution (security)
if (php_sapi_name() !== 'cli') {
    die('This script can only be run from command line');
}

// Include dependencies
require_once(__DIR__ . '/../lib/db_connection.php');
require_once(__DIR__ . '/../lib/SessionManager.php');

// Log start
$logMessage = "[" . date('Y-m-d H:i:s') . "] Starting cleanup of expired remember tokens...\n";
echo $logMessage;

// Initialize session manager
$sessionManager = new SessionManager($connection);

// Clean expired tokens
$deletedCount = $sessionManager->cleanExpiredTokens();

// Log result
$logMessage = "[" . date('Y-m-d H:i:s') . "] Cleanup complete. Deleted $deletedCount expired token(s).\n";
echo $logMessage;

// Optional: Write to log file
$logFile = __DIR__ . '/cleanup.log';
file_put_contents($logFile, $logMessage, FILE_APPEND);

// Close connection
$connection->close();

?>
