<?php
/**
 * Statistics Page
 * 
 * Display various statistics with filtering:
 * - Date range filter
 * - User filter
 * - Hotel statistics
 * - Activity charts
 */

// ============================================================================
// PHP CODE - Business Logic
// ============================================================================

require_once('lib/db_connection.php');
require_once('lib/config.php');
require_once('lib/SessionManager.php');

$sessionManager = new SessionManager($connection);
$sessionManager->checkRememberMe();
$isLoggedIn = $sessionManager->isLoggedIn();
$username = $sessionManager->getUsername();
$userId = $sessionManager->getUserId();

// Only logged-in users
if (!$isLoggedIn) {
    header('Location: login.php');
    exit;
}

// Get filters
$filterDateFrom = isset($_GET['date_from']) ? trim($_GET['date_from']) : date('Y-m-01'); // First day of month
$filterDateTo = isset($_GET['date_to']) ? trim($_GET['date_to']) : date('Y-m-d'); // Today
$filterUser = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;

// Convert dates to Unix timestamps
$timestampFrom = strtotime($filterDateFrom . ' 00:00:00');
$timestampTo = strtotime($filterDateTo . ' 23:59:59');

// Get hotel statistics
$hotelStats = [
    'total' => 0,
    'total_capacity' => 0,
    'total_rooms' => 0,
    'total_guests' => 0,
    'available_rooms' => 0,
    'occupancy_rate' => 0
];

$result = $conn->query("SELECT COUNT(*) as total, 
                        SUM(kapacitet) as total_capacity,
                        SUM(broj_soba) as total_rooms,
                        SUM(broj_gostiju) as total_guests,
                        SUM(slobodno_soba) as available_rooms
                        FROM hotels");
if ($row = $result->fetch_assoc()) {
    $hotelStats = $row;
    $hotelStats['occupancy_rate'] = $hotelStats['total_rooms'] > 0 
        ? round((($hotelStats['total_rooms'] - $hotelStats['available_rooms']) / $hotelStats['total_rooms']) * 100, 2)
        : 0;
}

// Get activity statistics (from audit log) with filters
$whereClauses = ["timestamp_unix BETWEEN ? AND ?"];
$params = [$timestampFrom, $timestampTo];
$types = 'ii';

if ($filterUser) {
    $whereClauses[] = "user_id = ?";
    $params[] = $filterUser;
    $types .= 'i';
}

$whereClause = implode(' AND ', $whereClauses);

// Total activity
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM audit_log WHERE $whereClause");
$stmt->bind_param($types, ...$params);
$stmt->execute();
$totalActivity = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Activity by action
$stmt = $conn->prepare("SELECT action, COUNT(*) as count FROM audit_log WHERE $whereClause GROUP BY action");
$stmt->bind_param($types, ...$params);
$stmt->execute();
$activityByAction = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$activityStats = [
    'INSERT' => 0,
    'UPDATE' => 0,
    'DELETE' => 0
];
foreach ($activityByAction as $action) {
    $activityStats[$action['action']] = $action['count'];
}

// Activity by date (last 7 days in range)
$stmt = $conn->prepare("SELECT DATE(FROM_UNIXTIME(timestamp_unix)) as date, COUNT(*) as count 
                        FROM audit_log 
                        WHERE $whereClause
                        GROUP BY DATE(FROM_UNIXTIME(timestamp_unix)) 
                        ORDER BY date DESC 
                        LIMIT 7");
$stmt->bind_param($types, ...$params);
$stmt->execute();
$activityByDate = array_reverse($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
$stmt->close();

// Top users by activity
$stmt = $conn->prepare("SELECT u.username, COUNT(a.id) as count 
                        FROM audit_log a 
                        JOIN users u ON a.user_id = u.id 
                        WHERE a.timestamp_unix BETWEEN ? AND ?
                        GROUP BY u.id 
                        ORDER BY count DESC 
                        LIMIT 5");
$stmt->bind_param('ii', $timestampFrom, $timestampTo);
$stmt->execute();
$topUsers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Cities statistics
$citiesStats = $conn->query("SELECT grad, COUNT(*) as count, SUM(kapacitet) as capacity 
                             FROM hotels 
                             GROUP BY grad 
                             ORDER BY count DESC 
                             LIMIT 10")->fetch_all(MYSQLI_ASSOC);

// Counties statistics
$countiesStats = $conn->query("SELECT zupanija, COUNT(*) as count 
                               FROM hotels 
                               GROUP BY zupanija 
                               ORDER BY count DESC 
                               LIMIT 10")->fetch_all(MYSQLI_ASSOC);

// Get all users for filter
$allUsers = $conn->query("SELECT id, username FROM users ORDER BY username")->fetch_all(MYSQLI_ASSOC);

// Page variables
$pageTitle = 'Statistika - Hotel Management';
$currentPage = 'statistics';

$customCSS = "
    .stat-card {
        border: none;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        padding: 20px;
        text-align: center;
        margin-bottom: 20px;
        transition: all 0.3s;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 20px rgba(0,0,0,0.15);
    }
    .stat-value {
        font-size: 2.5rem;
        font-weight: 700;
        margin: 10px 0;
    }
    .chart-container {
        position: relative;
        height: 300px;
    }
    .progress-label {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 5px;
    }
";

?>
<?php include 'templates/header.php'; ?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col">
            <h2><i class="bi bi-graph-up-arrow"></i> Statistika</h2>
            <p class="text-muted">Pregled statistike hotela i aktivnosti korisnika</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h6 class="mb-0"><i class="bi bi-funnel-fill"></i> Filtriranje Podataka</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="statistics.php" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Od datuma:</label>
                    <input type="date" name="date_from" class="form-control" value="<?php echo htmlspecialchars($filterDateFrom); ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Do datuma:</label>
                    <input type="date" name="date_to" class="form-control" value="<?php echo htmlspecialchars($filterDateTo); ?>" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Korisnik:</label>
                    <select name="user_id" class="form-select">
                        <option value="">Svi korisnici</option>
                        <?php foreach ($allUsers as $user): ?>
                        <option value="<?php echo $user['id']; ?>" <?php echo $filterUser == $user['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($user['username']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Filtriraj
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Hotel Statistics -->
    <h4 class="mb-3"><i class="bi bi-building-fill"></i> Statistika Hotela</h4>
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <i class="bi bi-building-fill" style="font-size: 2rem;"></i>
                <div class="stat-value"><?php echo $hotelStats['total']; ?></div>
                <div>Ukupno Hotela</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
                <i class="bi bi-people-fill" style="font-size: 2rem;"></i>
                <div class="stat-value"><?php echo number_format($hotelStats['total_capacity']); ?></div>
                <div>Kapacitet</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white;">
                <i class="bi bi-door-open-fill" style="font-size: 2rem;"></i>
                <div class="stat-value"><?php echo number_format($hotelStats['total_rooms']); ?></div>
                <div>Ukupno Soba</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white;">
                <i class="bi bi-person-fill" style="font-size: 2rem;"></i>
                <div class="stat-value"><?php echo number_format($hotelStats['total_guests']); ?></div>
                <div>Trenutni Gosti</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white;">
                <i class="bi bi-check-circle-fill" style="font-size: 2rem;"></i>
                <div class="stat-value"><?php echo number_format($hotelStats['available_rooms']); ?></div>
                <div>Slobodne Sobe</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card" style="background: linear-gradient(135deg, #30cfd0 0%, #330867 100%); color: white;">
                <i class="bi bi-percent" style="font-size: 2rem;"></i>
                <div class="stat-value"><?php echo $hotelStats['occupancy_rate']; ?>%</div>
                <div>Popunjenost</div>
            </div>
        </div>
    </div>

    <!-- Activity Statistics -->
    <h4 class="mb-3"><i class="bi bi-activity"></i> Aktivnost (<?php echo date('d.m.Y', $timestampFrom); ?> - <?php echo date('d.m.Y', $timestampTo); ?>)</h4>
    <?php if ($filterUser): ?>
    <div class="alert alert-info">
        <i class="bi bi-filter-circle"></i> Prikazana aktivnost za korisnika: 
        <strong><?php echo htmlspecialchars(array_filter($allUsers, fn($u) => $u['id'] == $filterUser)[0]['username'] ?? 'Unknown'); ?></strong>
    </div>
    <?php endif; ?>
    
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card" style="background: #6c757d; color: white;">
                <i class="bi bi-activity" style="font-size: 2rem;"></i>
                <div class="stat-value"><?php echo $totalActivity; ?></div>
                <div>Ukupno Promjena</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="background: #28a745; color: white;">
                <i class="bi bi-plus-circle-fill" style="font-size: 2rem;"></i>
                <div class="stat-value"><?php echo $activityStats['INSERT']; ?></div>
                <div>INSERT</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="background: #ffc107; color: white;">
                <i class="bi bi-pencil-fill" style="font-size: 2rem;"></i>
                <div class="stat-value"><?php echo $activityStats['UPDATE']; ?></div>
                <div>UPDATE</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="background: #dc3545; color: white;">
                <i class="bi bi-trash-fill" style="font-size: 2rem;"></i>
                <div class="stat-value"><?php echo $activityStats['DELETE']; ?></div>
                <div>DELETE</div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Activity by Date -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="bi bi-calendar3"></i> Aktivnost po Danima (Zadnjih 7)</h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($activityByDate)): ?>
                        <?php 
                        $maxCount = max(array_column($activityByDate, 'count'));
                        foreach ($activityByDate as $day): 
                            $percentage = $maxCount > 0 ? ($day['count'] / $maxCount) * 100 : 0;
                        ?>
                        <div class="mb-3">
                            <div class="progress-label">
                                <span><?php echo date('d.m.Y', strtotime($day['date'])); ?></span>
                                <span class="badge bg-info"><?php echo $day['count']; ?> promjena</span>
                            </div>
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar bg-info" style="width: <?php echo $percentage; ?>%"></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted text-center">Nema aktivnosti u odabranom periodu</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Top Users -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="bi bi-people-fill"></i> Top 5 Najaktivnijih Korisnika</h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($topUsers)): ?>
                        <?php 
                        $maxCount = max(array_column($topUsers, 'count'));
                        foreach ($topUsers as $user): 
                            $percentage = $maxCount > 0 ? ($user['count'] / $maxCount) * 100 : 0;
                        ?>
                        <div class="mb-3">
                            <div class="progress-label">
                                <span><i class="bi bi-person"></i> <?php echo htmlspecialchars($user['username']); ?></span>
                                <span class="badge bg-success"><?php echo $user['count']; ?> akcija</span>
                            </div>
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar bg-success" style="width: <?php echo $percentage; ?>%"></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted text-center">Nema podataka</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Cities Statistics -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="bi bi-geo-alt-fill"></i> Top 10 Gradova</h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($citiesStats)): ?>
                        <?php 
                        $maxCount = max(array_column($citiesStats, 'count'));
                        foreach ($citiesStats as $city): 
                            $percentage = $maxCount > 0 ? ($city['count'] / $maxCount) * 100 : 0;
                        ?>
                        <div class="mb-2">
                            <div class="progress-label">
                                <span><?php echo htmlspecialchars($city['grad']); ?></span>
                                <span><span class="badge bg-primary"><?php echo $city['count']; ?></span> <small class="text-muted">(<?php echo number_format($city['capacity']); ?> kapacitet)</small></span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar" style="width: <?php echo $percentage; ?>%"></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted text-center">Nema podataka</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Counties Statistics -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-warning">
                    <h6 class="mb-0"><i class="bi bi-map-fill"></i> Top 10 Županija</h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($countiesStats)): ?>
                        <?php 
                        $maxCount = max(array_column($countiesStats, 'count'));
                        foreach ($countiesStats as $county): 
                            $percentage = $maxCount > 0 ? ($county['count'] / $maxCount) * 100 : 0;
                        ?>
                        <div class="mb-2">
                            <div class="progress-label">
                                <span><?php echo htmlspecialchars($county['zupanija']); ?></span>
                                <span class="badge bg-warning text-dark"><?php echo $county['count']; ?></span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-warning" style="width: <?php echo $percentage; ?>%"></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted text-center">Nema podataka</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'templates/footer.php'; ?>
