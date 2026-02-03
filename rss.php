<?php
/**
 * RSS Feed - Hotel Management System
 * 
 * Displays the most important application data in RSS 2.0 format
 * Shows latest hotels with details
 * 
 * URL: http://localhost/hotel_managment/rss.php
 * Format: RSS 2.0 (XML)
 */

// Set XML content type
header('Content-Type: application/xml; charset=utf-8');

require_once('lib/db_connection.php');
require_once('lib/config.php');
mysqli_select_db($connection,'hotel_management');

// Get base URL
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$baseUrl = $protocol . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']);

// Get latest hotels (limit to 50 for RSS feed)
$sql = "SELECT * FROM hotels ORDER BY id DESC LIMIT 50";
$result = $conn->query($sql);

// Get total count
$countSql = "SELECT COUNT(*) as total FROM hotels";
$countResult = $conn->query($countSql);
$totalHotels = $countResult->fetch_assoc()['total'];

// Build date for last update (use current time or latest hotel modification)
$lastBuildDate = date(DATE_RFC2822);

// Start XML output
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <title>Hotel Management System - Hotels Feed</title>
        <link><?php echo htmlspecialchars($baseUrl); ?>/index.php</link>
        <description>RSS feed sa najnovijim hotelima iz Hotel Management System aplikacije. Ukupno hotela: <?php echo $totalHotels; ?></description>
        <language>hr</language>
        <copyright>Copyright <?php echo date('Y'); ?> Hotel Management System</copyright>
        <lastBuildDate><?php echo $lastBuildDate; ?></lastBuildDate>
        <generator>Hotel Management System RSS Generator v1.0</generator>
        <atom:link href="<?php echo htmlspecialchars($baseUrl); ?>/rss.php" rel="self" type="application/rss+xml" />
        
        <!-- Hotel Items -->
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($hotel = $result->fetch_assoc()): ?>
        <item>
            <title><?php echo htmlspecialchars($hotel['naziv']); ?></title>
            <link><?php echo htmlspecialchars($baseUrl); ?>/index.php?hotel_id=<?php echo $hotel['id']; ?></link>
            <description><![CDATA[
                <h3><?php echo htmlspecialchars($hotel['naziv']); ?></h3>
                <p><strong>Adresa:</strong> <?php echo htmlspecialchars($hotel['adresa']); ?>, <?php echo htmlspecialchars($hotel['grad']); ?></p>
                <p><strong>Županija:</strong> <?php echo htmlspecialchars($hotel['zupanija']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($hotel['email']); ?></p>
                <p><strong>Telefon:</strong> <?php echo htmlspecialchars($hotel['telefon']); ?></p>
                <hr>
                <p><strong>Kapacitet:</strong> <?php echo $hotel['kapacitet']; ?> osoba</p>
                <p><strong>Broj soba:</strong> <?php echo $hotel['broj_soba']; ?></p>
                <p><strong>Broj gostiju:</strong> <?php echo $hotel['broj_gostiju']; ?></p>
                <p><strong>Slobodne sobe:</strong> <?php echo $hotel['slobodno_soba']; ?></p>
                <hr>
                <p><em>Hotel ID: <?php echo $hotel['id']; ?></em></p>
            ]]></description>
            <guid isPermaLink="false">hotel-<?php echo $hotel['id']; ?>-<?php echo md5($hotel['naziv'] . $hotel['adresa']); ?></guid>
            <pubDate><?php echo date(DATE_RFC2822, strtotime('-' . ($result->num_rows - $result->current_field) . ' days')); ?></pubDate>
            <category>Hotels</category>
            <category><?php echo htmlspecialchars($hotel['grad']); ?></category>
            <category><?php echo htmlspecialchars($hotel['zupanija']); ?></category>
        </item>
            <?php endwhile; ?>
        <?php else: ?>
        <item>
            <title>Nema hotela</title>
            <link><?php echo htmlspecialchars($baseUrl); ?>/index.php</link>
            <description>Trenutno nema hotela u bazi podataka.</description>
            <pubDate><?php echo $lastBuildDate; ?></pubDate>
        </item>
        <?php endif; ?>
    </channel>
</rss>
<?php
$conn->close();
?>
