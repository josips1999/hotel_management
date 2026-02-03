<?php
// Check session status
session_start();

echo "<h2>Session Debug Info</h2>";
echo "<pre>";
echo "Session ID: " . session_id() . "\n";
echo "Session Status: " . session_status() . " (1=disabled, 2=active)\n\n";
echo "Session Variables:\n";
print_r($_SESSION);
echo "\n\nCookies:\n";
print_r($_COOKIE);
echo "</pre>";

echo "<hr>";
echo "<a href='logout.php'>Logout</a> | ";
echo "<a href='login.php'>Login</a> | ";
echo "<a href='index.php'>Index</a>";
