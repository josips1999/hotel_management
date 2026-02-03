<?php
/**
 * PHP Configuration Check
 * Checks if mysqli extension is loaded
 */

echo "<h2>PHP Configuration Check</h2>";

echo "<h3>PHP Version:</h3>";
echo "<p>" . phpversion() . "</p>";

echo "<h3>mysqli Extension Status:</h3>";
if (extension_loaded('mysqli')) {
    echo "<p style='color: green;'>✓ mysqli extension is ENABLED</p>";
} else {
    echo "<p style='color: red;'>✗ mysqli extension is DISABLED</p>";
    echo "<p><strong>To fix this:</strong></p>";
    echo "<ol>";
    echo "<li>Open: C:\\xampp\\php\\php.ini</li>";
    echo "<li>Find: ;extension=mysqli</li>";
    echo "<li>Change to: extension=mysqli (remove semicolon)</li>";
    echo "<li>Restart Apache in XAMPP Control Panel</li>";
    echo "</ol>";
}

echo "<h3>Loaded Extensions:</h3>";
echo "<pre>";
print_r(get_loaded_extensions());
echo "</pre>";

echo "<h3>php.ini Location:</h3>";
echo "<p>" . php_ini_loaded_file() . "</p>";
?>
