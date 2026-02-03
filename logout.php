<?php
/**
 * Logout Script
 * Odjava korisnika iz sustava
 */

// Start session FIRST before logout
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include dependencies
require_once('lib/db_connection.php');
require_once('lib/SessionManager.php');
mysqli_select_db($connection,'hotel_management');

// Initialize session manager with database connection
$sessionManager = new SessionManager($connection);

// Logout user (deletes session and remember token)
$sessionManager->logout();

// Close database connection
$connection->close();

// Redirect to login page with success message
header('Location: login.php?message=Uspješno ste se odjavili');
exit;
?>
