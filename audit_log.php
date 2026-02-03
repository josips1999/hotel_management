<?php
/**
 * Audit Log Viewer Page
 * Displays all data changes with Unix timestamps
 */

// ============================================================================
// PHP CODE - Business Logic (prije HTML-a)
// ============================================================================

require_once('lib/db_connection.php');
require_once('lib/config.php');
require_once('lib/SessionManager.php');
require_once('lib/AuditLogger.php');

// Session
$sessionManager = new SessionManager($connection);
$sessionManager->checkRememberMe();
$isLoggedIn = $sessionManager->isLoggedIn();
$username = $sessionManager->getUsername();

// Only logged-in users can view audit log
if (!$isLoggedIn) {
    header('Location: login.php');
    exit();
}

// Create AuditLogger instance
$auditLogger = new AuditLogger($connection);

// Get filters
$filterTable = isset($_GET['table']) ? trim($_GET['table']) : null;
$filterAction = isset($_GET['action']) ? trim($_GET['action']) : null;
$filterUser = isset($_GET['user']) ? intval($_GET['user']) : null;
$filterDateFrom = isset($_GET['date_from']) ? trim($_GET['date_from']) : null;
$filterDateTo = isset($_GET['date_to']) ? trim($_GET['date_to']) : null;
$filterSearch = isset($_GET['search']) ? trim($_GET['search']) : null;
$limit = isset($_GET['limit']) ? min(intval($_GET['limit']), 500) : 100;

// Build custom query with all filters
$whereClauses = [];
$params = [];
$types = '';

if ($filterTable) {
    $whereClauses[] = "table_name = ?";
    $params[] = $filterTable;
    $types .= 's';
}

if ($filterAction) {
    $whereClauses[] = "action = ?";
    $params[] = $filterAction;
    $types .= 's';
}

if ($filterUser) {
    $whereClauses[] = "user_id = ?";
    $params[] = $filterUser;
    $types .= 'i';
}

if ($filterDateFrom) {
    $timestampFrom = strtotime($filterDateFrom . ' 00:00:00');
    $whereClauses[] = "timestamp_unix >= ?";
    $params[] = $timestampFrom;
    $types .= 'i';
}

if ($filterDateTo) {
    $timestampTo = strtotime($filterDateTo . ' 23:59:59');
    $whereClauses[] = "timestamp_unix <= ?";
    $params[] = $timestampTo;
    $types .= 'i';
}

if ($filterSearch) {
    $whereClauses[] = "(username LIKE ? OR ip_address LIKE ? OR CAST(record_id AS CHAR) LIKE ?)";
    $searchParam = "%$filterSearch%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= 'sss';
}

// Execute query
if (!empty($whereClauses)) {
    $sql = "SELECT * FROM audit_log WHERE " . implode(' AND ', $whereClauses) . " ORDER BY timestamp_unix DESC LIMIT ?";
    $params[] = $limit;
    $types .= 'i';
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $logs = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    // Get audit log entries
    if ($filterUser) {
        $logs = $auditLogger->getUserActivity($filterUser, $limit);
    } elseif ($filterTable) {
        $logs = $auditLogger->getRecentChanges($limit, $filterTable);
    } else {
        $logs = $auditLogger->getRecentChanges($limit);
    }
}

// Get all users for dropdown
$usersResult = $conn->query("SELECT id, username FROM users ORDER BY username");
$allUsers = $usersResult->fetch_all(MYSQLI_ASSOC);

// Get statistics
$stats = $auditLogger->getStatistics(30); // Last 30 days

// Page-specific variables for template
$pageTitle = 'Audit Log - Hotel Management';
$currentPage = 'audit_log';

// ============================================================================
// HTML TEMPLATE
// ============================================================================
?>
<?php include 'templates/header.php'; ?>

<div class="row mb-4">
    <div class="col">
        <h2><i class="bi bi-journal-text"></i> Audit Log - Povijest Promjena</h2>
        <p class="text-muted">Sve promjene podataka bilježe se s Unix timestampom</p>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card" style="background-color: #677ae6; color: white;">
            <div class="card-body">
                <h6><i class="bi bi-activity"></i> Ukupno Promjena (30 dana)</h6>
                <h3><?php echo number_format($stats['total_changes']); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h6><i class="bi bi-plus-circle"></i> INSERT</h6>
                <h3><?php echo number_format($stats['inserts']); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <h6><i class="bi bi-pencil"></i> UPDATE</h6>
                <h3><?php echo number_format($stats['updates']); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <h6><i class="bi bi-trash"></i> DELETE</h6>
                <h3><?php echo number_format($stats['deletes']); ?></h3>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h6 class="mb-0"><i class="bi bi-funnel-fill"></i> Napredna Pretraga i Filtriranje</h6>
    </div>
    <div class="card-body">
        <form method="GET" action="audit_log.php" class="row g-3">
            <div class="col-md-2">
                <label class="form-label">Tablica:</label>
                <select name="table" class="form-select">
                    <option value="">Sve tablice</option>
                    <option value="hotels" <?php echo $filterTable === 'hotels' ? 'selected' : ''; ?>>hotels</option>
                    <option value="users" <?php echo $filterTable === 'users' ? 'selected' : ''; ?>>users</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Akcija:</label>
                <select name="action" class="form-select">
                    <option value="">Sve akcije</option>
                    <option value="INSERT" <?php echo $filterAction === 'INSERT' ? 'selected' : ''; ?>>INSERT</option>
                    <option value="UPDATE" <?php echo $filterAction === 'UPDATE' ? 'selected' : ''; ?>>UPDATE</option>
                    <option value="DELETE" <?php echo $filterAction === 'DELETE' ? 'selected' : ''; ?>>DELETE</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Korisnik:</label>
                <select name="user" class="form-select">
                    <option value="">Svi korisnici</option>
                    <?php foreach ($allUsers as $user): ?>
                    <option value="<?php echo $user['id']; ?>" <?php echo $filterUser == $user['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($user['username']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Od datuma:</label>
                <input type="date" name="date_from" class="form-control" value="<?php echo htmlspecialchars($filterDateFrom ?? ''); ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Do datuma:</label>
                <input type="date" name="date_to" class="form-control" value="<?php echo htmlspecialchars($filterDateTo ?? ''); ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Broj zapisa:</label>
                <select name="limit" class="form-select">
                    <option value="50" <?php echo $limit === 50 ? 'selected' : ''; ?>>50</option>
                    <option value="100" <?php echo $limit === 100 ? 'selected' : ''; ?>>100</option>
                    <option value="250" <?php echo $limit === 250 ? 'selected' : ''; ?>>250</option>
                    <option value="500" <?php echo $limit === 500 ? 'selected' : ''; ?>>500</option>
                </select>
            </div>
            <div class="col-md-8">
                <label class="form-label">Pretraga (username, IP, record ID):</label>
                <input type="text" name="search" class="form-control" placeholder="Pretraži..." value="<?php echo htmlspecialchars($filterSearch ?? ''); ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Pretraži
                </button>
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <a href="audit_log.php" class="btn btn-secondary w-100">
                    <i class="bi bi-x-circle"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Audit Log Table -->
<div class="card">
    <div class="card-body">
        <div class="responsive-table-wrapper table-cards-mobile">
            <table class="responsive-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Vrijeme</th>
                        <th class="hide-mobile">Unix Timestamp</th>
                        <th>Tablica</th>
                        <th class="hide-mobile">Record ID</th>
                        <th>Akcija</th>
                        <th class="hide-tablet">Korisnik</th>
                        <th class="hide-mobile">IP Adresa</th>
                        <th class="no-print">Detalji</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <i class="bi bi-inbox"></i> Nema podataka
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                        <tr>
                            <td data-label="ID"><?php echo htmlspecialchars($log['id']); ?></td>
                            <td data-label="Vrijeme">
                                <small>
                                    <?php echo AuditLogger::formatTimestamp($log['changed_at']); ?><br>
                                    <span class="text-muted"><?php echo AuditLogger::timeAgo($log['changed_at']); ?></span>
                                </small>
                            </td>
                            <td data-label="Unix Timestamp" class="hide-mobile">
                                <code><?php echo $log['changed_at']; ?></code>
                            </td>
                            <td data-label="Tablica">
                                <span class="badge bg-secondary"><?php echo htmlspecialchars($log['table_name']); ?></span>
                            </td>
                            <td data-label="Record ID" class="hide-mobile"><?php echo htmlspecialchars($log['record_id']); ?></td>
                            <td data-label="Akcija">
                                <?php
                                $badgeClass = match($log['action']) {
                                    'INSERT' => 'bg-success',
                                    'UPDATE' => 'bg-warning',
                                    'DELETE' => 'bg-danger',
                                    default => 'bg-secondary'
                                };
                                ?>
                                <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($log['action']); ?></span>
                            </td>
                            <td data-label="Korisnik" class="hide-tablet">
                                <?php if ($log['username']): ?>
                                    <i class="bi bi-person"></i> <?php echo htmlspecialchars($log['username']); ?>
                                <?php else: ?>
                                    <span class="text-muted">System</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="IP Adresa" class="hide-mobile"><small><?php echo htmlspecialchars($log['ip_address'] ?? 'N/A'); ?></small></td>
                            <td data-label="Detalji" class="no-print">
                                <button class="btn btn-sm btn-info" onclick="showDetails(<?php echo $log['id']; ?>, <?php echo htmlspecialchars(json_encode($log)); ?>)">
                                    <i class="bi bi-eye"></i> <span class="hide-mobile">Detalji</span>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-info-circle"></i> Detalji Promjene</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Tablica:</strong> <span id="detail-table"></span>
                    </div>
                    <div class="col-md-6">
                        <strong>Record ID:</strong> <span id="detail-record-id"></span>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Akcija:</strong> <span id="detail-action"></span>
                    </div>
                    <div class="col-md-6">
                        <strong>Vrijeme:</strong> <span id="detail-time"></span>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Unix Timestamp:</strong> <code id="detail-timestamp"></code>
                    </div>
                    <div class="col-md-6">
                        <strong>Korisnik:</strong> <span id="detail-user"></span>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>IP Adresa:</strong> <span id="detail-ip"></span>
                    </div>
                    <div class="col-md-6">
                        <strong>User Agent:</strong> <small id="detail-ua"></small>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <h6>Stari Podaci:</h6>
                        <pre id="detail-old-data" class="bg-light p-3 rounded" style="max-height: 300px; overflow-y: auto;"></pre>
                    </div>
                    <div class="col-md-6">
                        <h6>Novi Podaci:</h6>
                        <pre id="detail-new-data" class="bg-light p-3 rounded" style="max-height: 300px; overflow-y: auto;"></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Page-specific JavaScript -->
<script>
    function showDetails(id, log) {
        // Basic info
        document.getElementById('detail-table').textContent = log.table_name;
        document.getElementById('detail-record-id').textContent = log.record_id;
        document.getElementById('detail-action').innerHTML = '<span class="badge bg-' + getActionBadge(log.action) + '">' + log.action + '</span>';
        document.getElementById('detail-time').textContent = new Date(log.changed_at * 1000).toLocaleString('hr-HR');
        document.getElementById('detail-timestamp').textContent = log.changed_at;
        document.getElementById('detail-user').textContent = log.username || 'System';
        document.getElementById('detail-ip').textContent = log.ip_address || 'N/A';
        document.getElementById('detail-ua').textContent = log.user_agent || 'N/A';
        
        // Data
        try {
            const oldData = log.old_data ? JSON.parse(log.old_data) : null;
            const newData = log.new_data ? JSON.parse(log.new_data) : null;
            
            document.getElementById('detail-old-data').textContent = oldData ? JSON.stringify(oldData, null, 2) : 'N/A';
            document.getElementById('detail-new-data').textContent = newData ? JSON.stringify(newData, null, 2) : 'N/A';
        } catch (e) {
            document.getElementById('detail-old-data').textContent = log.old_data || 'N/A';
            document.getElementById('detail-new-data').textContent = log.new_data || 'N/A';
        }
        
        // Show modal
        new bootstrap.Modal(document.getElementById('detailsModal')).show();
    }
    
    function getActionBadge(action) {
        switch(action) {
            case 'INSERT': return 'success';
            case 'UPDATE': return 'warning';
            case 'DELETE': return 'danger';
            default: return 'secondary';
        }
    }
</script>

<?php include 'templates/footer.php'; ?>
<?php $connection->close(); ?>
