<?php
/**
 * User Dashboard - Overview of User Capabilities
 * 
 * Displays all available features and quick actions for registered users
 * Shows statistics and recent activity
 */

// ============================================================================
// PHP CODE - Business Logic
// ============================================================================

require_once('lib/db_connection.php');
require_once('lib/config.php');
require_once('lib/SessionManager.php');
require_once('lib/SEOHelper.php');

$sessionManager = new SessionManager($connection);
$sessionManager->checkRememberMe();
$isLoggedIn = $sessionManager->isLoggedIn();
$username = $sessionManager->getUsername();
$userId = $sessionManager->getUserId();

// Check if user is admin
$stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$userRole = $result->fetch_assoc()['role'] ?? 'user';
$stmt->close();

$isAdmin = ($userRole === 'admin');

// Redirect if not logged in
if (!$isLoggedIn) {
    header('Location: login.php?redirect=dashboard.php');
    exit;
}

// Get user statistics
$stats = [];

// Total hotels
$result = $conn->query("SELECT COUNT(*) as total FROM hotels");
$stats['total_hotels'] = $result->fetch_assoc()['total'];

// Hotels with availability
$result = $conn->query("SELECT COUNT(*) as total FROM hotels WHERE slobodno_soba > 0");
$stats['available_hotels'] = $result->fetch_assoc()['total'];

// Total capacity
$result = $conn->query("SELECT SUM(kapacitet) as total FROM hotels");
$stats['total_capacity'] = $result->fetch_assoc()['total'] ?? 0;

// Total rooms
$result = $conn->query("SELECT SUM(broj_soba) as total FROM hotels");
$stats['total_rooms'] = $result->fetch_assoc()['total'] ?? 0;

// User's recent activity (from audit log if exists)
$recentActivity = [];
$checkAuditTable = $conn->query("SHOW TABLES LIKE 'audit_log'");
if ($checkAuditTable && $checkAuditTable->num_rows > 0) {
    $stmt = $conn->prepare("
        SELECT action, table_name, record_id, timestamp_unix 
        FROM audit_log 
        WHERE user_id = ? 
        ORDER BY timestamp_unix DESC 
        LIMIT 10
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $recentActivity = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Recent hotels (last 5 added)
$recentHotels = [];
$result = $conn->query("SELECT id, naziv, grad, kapacitet, slobodno_soba FROM hotels ORDER BY id DESC LIMIT 5");
if ($result) {
    $recentHotels = $result->fetch_all(MYSQLI_ASSOC);
}

// Cities with most hotels
$citiesStats = [];
$result = $conn->query("SELECT grad, COUNT(*) as count FROM hotels GROUP BY grad ORDER BY count DESC LIMIT 5");
if ($result) {
    $citiesStats = $result->fetch_all(MYSQLI_ASSOC);
}

// Page variables
$pageTitle = 'Dashboard - Hotel Management';
$currentPage = 'dashboard';

$customCSS = "
    .dashboard-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        transition: all 0.3s;
        height: 100%;
    }
    .dashboard-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    }
    .stat-card {
        background: #677ae6;
        color: white;
    }
    .stat-card-2 {
        background: #6c757d;
        color: white;
    }
    .stat-card-3 {
        background: #5a6c7d;
        color: white;
    }
    .stat-card-4 {
        background: #4a5568;
        color: white;
    }
    .stat-value {
        font-size: 2.5rem;
        font-weight: 700;
        margin: 10px 0;
    }
    .action-card {
        background: white;
        cursor: pointer;
        text-decoration: none;
        color: inherit;
        display: block;
    }
    .action-card:hover {
        color: #667eea;
    }
    .action-icon {
        font-size: 3rem;
        margin-bottom: 15px;
        color: #677ae6;
    }
    .activity-item {
        padding: 10px;
        border-left: 3px solid #677ae6;
        margin-bottom: 10px;
        background: #f8f9fa;
        border-radius: 5px;
    }
    .activity-badge {
        font-size: 0.75rem;
    }
";

?>
<?php include 'templates/header.php'; ?>

<div class="container py-4">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-success">
                <h4 class="alert-heading">
                    <i class="bi bi-speedometer2"></i> Dobrodošli, <?php echo htmlspecialchars($username ?? 'Korisnik'); ?>!
                </h4>
                <p class="mb-0">Ovo je vaš kontrolni panel. Odavde možete upravljati hotelima, pregledavati statistike i pristupiti svim funkcijama aplikacije.</p>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card dashboard-card stat-card">
                <div class="card-body text-center">
                    <i class="bi bi-building-fill" style="font-size: 2rem;"></i>
                    <div class="stat-value"><?php echo $stats['total_hotels']; ?></div>
                    <div>Ukupno Hotela</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card dashboard-card stat-card-2">
                <div class="card-body text-center">
                    <i class="bi bi-door-open-fill" style="font-size: 2rem;"></i>
                    <div class="stat-value"><?php echo $stats['available_hotels']; ?></div>
                    <div>Hoteli sa Slobodnim Sobama</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card dashboard-card stat-card-3">
                <div class="card-body text-center">
                    <i class="bi bi-people-fill" style="font-size: 2rem;"></i>
                    <div class="stat-value"><?php echo number_format($stats['total_capacity']); ?></div>
                    <div>Ukupan Kapacitet</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card dashboard-card stat-card-4">
                <div class="card-body text-center">
                    <i class="bi bi-door-closed-fill" style="font-size: 2rem;"></i>
                    <div class="stat-value"><?php echo number_format($stats['total_rooms']); ?></div>
                    <div>Ukupno Soba</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="mb-3"><i class="bi bi-lightning-charge-fill"></i> Brze Akcije</h4>
        </div>
        <div class="col-md-3 mb-3">
            <a href="index.php" class="card dashboard-card action-card">
                <div class="card-body text-center">
                    <i class="bi bi-building-fill-add action-icon"></i>
                    <h6>Dodaj Hotel</h6>
                    <p class="text-muted small mb-0">Unos novog hotela u sustav</p>
                </div>
            </a>
        </div>
        <div class="col-md-3 mb-3">
            <a href="index.php" class="card dashboard-card action-card">
                <div class="card-body text-center">
                    <i class="bi bi-list-ul action-icon"></i>
                    <h6>Pregled Hotela</h6>
                    <p class="text-muted small mb-0">Lista svih hotela</p>
                </div>
            </a>
        </div>
        <div class="col-md-3 mb-3">
            <a href="search.php" class="card dashboard-card action-card">
                <div class="card-body text-center">
                    <i class="bi bi-search action-icon"></i>
                    <h6>Pretraživanje</h6>
                    <p class="text-muted small mb-0">Full-text pretraga</p>
                </div>
            </a>
        </div>
        <div class="col-md-3 mb-3">
            <a href="statistics.php" class="card dashboard-card action-card">
                <div class="card-body text-center">
                    <i class="bi bi-graph-up-arrow action-icon"></i>
                    <h6>Statistika</h6>
                    <p class="text-muted small mb-0">Grafovi i analize</p>
                </div>
            </a>
        </div>
        <div class="col-md-3 mb-3">
            <a href="update_boravak.php" class="card dashboard-card action-card">
                <div class="card-body text-center">
                    <i class="bi bi-calendar-check action-icon"></i>
                    <h6>Ažuriraj Boravak</h6>
                    <p class="text-muted small mb-0">Check-in/Check-out</p>
                </div>
            </a>
        </div>
        <div class="col-md-3 mb-3">
            <a href="audit_log.php" class="card dashboard-card action-card">
                <div class="card-body text-center">
                    <i class="bi bi-journal-text action-icon"></i>
                    <h6>Audit Log</h6>
                    <p class="text-muted small mb-0">Povijest aktivnosti</p>
                </div>
            </a>
        </div>
        <div class="col-md-3 mb-3">
            <a href="ajax_search.php" class="card dashboard-card action-card">
                <div class="card-body text-center">
                    <i class="bi bi-lightning-charge action-icon"></i>
                    <h6>AJAX Pretraga</h6>
                    <p class="text-muted small mb-0">Live search</p>
                </div>
            </a>
        </div>
        <div class="col-md-3 mb-3">
            <a href="contact.php" class="card dashboard-card action-card">
                <div class="card-body text-center">
                    <i class="bi bi-envelope-heart action-icon"></i>
                    <h6>Kontakt</h6>
                    <p class="text-muted small mb-0">Pošalji poruku</p>
                </div>
            </a>
        </div>
        <?php if ($isAdmin): ?>
        <div class="col-md-3 mb-3">
            <a href="database_backup.php" class="card dashboard-card action-card">
                <div class="card-body text-center">
                    <i class="bi bi-database-fill-gear action-icon"></i>
                    <h6>Database Backup</h6>
                    <p class="text-muted small mb-0">Backup & Restore</p>
                </div>
            </a>
        </div>
        <?php endif; ?>
    </div>

    <div class="row">
        <!-- Recent Hotels -->
        <div class="col-md-6 mb-4">
            <div class="card dashboard-card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> Nedavno Dodani Hoteli</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($recentHotels)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($recentHotels as $hotel): 
                                $seoUrl = SEOHelper::hotelUrl($hotel['id'], $hotel['naziv']);
                            ?>
                            <a href="<?php echo $seoUrl; ?>" class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($hotel['naziv']); ?></h6>
                                        <small class="text-muted">
                                            <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($hotel['grad']); ?>
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <div><span class="badge bg-info"><?php echo $hotel['kapacitet']; ?> osoba</span></div>
                                        <small class="text-success"><?php echo $hotel['slobodno_soba']; ?> slobodno</small>
                                    </div>
                                </div>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center py-3">Nema hotela</p>
                    <?php endif; ?>
                </div>
                <div class="card-footer text-center">
                    <a href="index.php" class="btn btn-sm btn-primary">
                        <i class="bi bi-list"></i> Svi Hoteli
                    </a>
                </div>
            </div>
        </div>

        <!-- Cities Statistics -->
        <div class="col-md-6 mb-4">
            <div class="card dashboard-card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-bar-chart-fill"></i> Top 5 Gradova</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($citiesStats)): ?>
                        <?php foreach ($citiesStats as $city): ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span><i class="bi bi-geo-alt-fill"></i> <?php echo htmlspecialchars($city['grad']); ?></span>
                                <span class="badge bg-success"><?php echo $city['count']; ?> hotela</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-success" style="width: <?php echo ($city['count'] / $stats['total_hotels']) * 100; ?>%"></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted text-center py-3">Nema podataka</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <?php if (!empty($recentActivity)): ?>
        <div class="col-12 mb-4">
            <div class="card dashboard-card">
                <div class="card-header bg-warning">
                    <h5 class="mb-0"><i class="bi bi-activity"></i> Vaša Nedavna Aktivnost</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach (array_slice($recentActivity, 0, 6) as $activity): ?>
                        <div class="col-md-6 mb-2">
                            <div class="activity-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="badge activity-badge 
                                            <?php echo $activity['action'] === 'INSERT' ? 'bg-success' : ($activity['action'] === 'UPDATE' ? 'bg-warning' : 'bg-danger'); ?>">
                                            <?php echo $activity['action']; ?>
                                        </span>
                                        <strong><?php echo $activity['table_name']; ?></strong> #<?php echo $activity['record_id']; ?>
                                    </div>
                                    <small class="text-muted">
                                        <?php echo date('d.m.Y H:i', $activity['timestamp_unix']); ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="card-footer text-center">
                    <a href="audit_log.php" class="btn btn-sm btn-warning">
                        <i class="bi bi-journal-text"></i> Potpuna Povijest
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Capabilities Overview -->
    <div class="row">
        <div class="col-12">
            <div class="card dashboard-card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-shield-check"></i> Vaše Ovlasti kao Registrirani Korisnik</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <h6 class="text-success"><i class="bi bi-check-circle-fill"></i> Pregled Podataka</h6>
                            <ul>
                                <li>Pregled svih hotela bez ograničenja</li>
                                <li>Pristup svim detaljima (email, telefon, županija)</li>
                                <li>Neograničena paginacija</li>
                                <li>Full-text pretraga</li>
                                <li>AJAX live search i filter</li>
                                <li>Statistike i grafovi</li>
                            </ul>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-primary"><i class="bi bi-plus-circle-fill"></i> Unos Podataka</h6>
                            <ul>
                                <li>Dodavanje novih hotela</li>
                                <li>Unos svih detalja (naziv, adresa, kontakt)</li>
                                <li>Postavljanje kapaciteta i broja soba</li>
                                <li>Automatska validacija podataka</li>
                                <li>AJAX validacija u realnom vremenu</li>
                            </ul>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-warning"><i class="bi bi-pencil-fill"></i> Ažuriranje i Brisanje</h6>
                            <ul>
                                <li>Uređivanje postojećih hotela</li>
                                <li>Ažuriranje boravka (check-in/out)</li>
                                <li>Brisanje hotela</li>
                                <li>Pregled audit loga (povijest promjena)</li>
                                <li>Praćenje vlastite aktivnosti</li>
                            </ul>
                        </div>
                    </div>
                    <hr>
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle"></i> <strong>Napomena:</strong> Sve operacije (INSERT, UPDATE, DELETE) automatski se bilježe u audit log sustav s Unix timestamp-om i informacijama o korisniku.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'templates/footer.php'; ?>
