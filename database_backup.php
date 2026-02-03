<?php
/**
 * Database Backup & Restore - Admin Only
 * 
 * Features:
 * - Download database backup (SQL dump)
 * - Restore database from SQL file
 * - View backup history
 */

// ============================================================================
// PHP CODE - Business Logic
// ============================================================================

require_once('lib/db_connection.php');
require_once('lib/config.php');
require_once('lib/SessionManager.php');
require_once('lib/CSRFToken.php');

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

// Redirect if not admin
if (!$isLoggedIn || !$isAdmin) {
    header('Location: index.php');
    exit;
}

// Get database size
$result = $conn->query("SELECT 
    SUM(data_length + index_length) as size 
    FROM information_schema.TABLES 
    WHERE table_schema = '" . DB_NAME . "'");
$dbSize = $result->fetch_assoc()['size'];
$dbSizeMB = round($dbSize / 1024 / 1024, 2);

// Get table statistics
$tablesResult = $conn->query("SELECT 
    table_name, 
    table_rows, 
    ROUND(((data_length + index_length) / 1024 / 1024), 2) as size_mb
    FROM information_schema.TABLES 
    WHERE table_schema = '" . DB_NAME . "'
    ORDER BY (data_length + index_length) DESC");
$tables = $tablesResult->fetch_all(MYSQLI_ASSOC);

// Check if backups directory exists
$backupDir = __DIR__ . '/backups';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

// Get existing backups
$backupFiles = glob($backupDir . '/*.sql');
$backups = [];
foreach ($backupFiles as $file) {
    $backups[] = [
        'name' => basename($file),
        'path' => $file,
        'size' => filesize($file),
        'date' => filemtime($file)
    ];
}
usort($backups, fn($a, $b) => $b['date'] - $a['date']);

// Page variables
$pageTitle = 'Database Backup & Restore - Admin Panel';
$currentPage = 'database_backup';

$customCSS = "
    .backup-card {
        border: none;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }
    .table-stat {
        padding: 10px;
        border-bottom: 1px solid #e9ecef;
    }
    .table-stat:last-child {
        border-bottom: none;
    }
    .backup-item {
        padding: 15px;
        border: 1px solid #dee2e6;
        border-radius: 5px;
        margin-bottom: 10px;
        transition: all 0.3s;
    }
    .backup-item:hover {
        background: #f8f9fa;
        border-color: #667eea;
    }
    .danger-zone {
        border: 2px solid #dc3545;
        border-radius: 10px;
        padding: 20px;
        background: #fff5f5;
    }
";

?>
<?php include 'templates/header.php'; ?>

<div class="container py-4">
    <!-- Admin Header -->
    <div class="alert alert-danger mb-4">
        <h4 class="alert-heading">
            <i class="bi bi-database-fill-gear"></i> Admin Panel - Database Backup & Restore
        </h4>
        <p class="mb-0"><strong>UPOZORENJE:</strong> Ove operacije direktno utječu na bazu podataka. Budite oprezni!</p>
    </div>

    <!-- Alert Container -->
    <div id="alertContainer"></div>

    <!-- Database Info -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card backup-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <div class="card-body text-center">
                    <i class="bi bi-database-fill" style="font-size: 3rem;"></i>
                    <h3 class="mt-3"><?php echo DB_NAME; ?></h3>
                    <p class="mb-0">Database Name</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card backup-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
                <div class="card-body text-center">
                    <i class="bi bi-hdd-fill" style="font-size: 3rem;"></i>
                    <h3 class="mt-3"><?php echo $dbSizeMB; ?> MB</h3>
                    <p class="mb-0">Total Size</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card backup-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white;">
                <div class="card-body text-center">
                    <i class="bi bi-table" style="font-size: 3rem;"></i>
                    <h3 class="mt-3"><?php echo count($tables); ?></h3>
                    <p class="mb-0">Tables</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Create Backup -->
        <div class="col-md-6 mb-4">
            <div class="card backup-card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-download"></i> Kreiranje Sigurnosne Kopije</h5>
                </div>
                <div class="card-body">
                    <p>Kreira SQL dump datoteku koja sadrži sve podatke i strukturu baze.</p>
                    <form action="api/backup_database.php" method="POST">
                        
                        <!-- CSRF Token (Requirement 33) -->
                        <?php echo CSRFToken::getField(); ?>
                        
                        <input type="hidden" name="action" value="backup">
                        <div class="mb-3">
                            <label class="form-label">Naziv backupa (opcionalno):</label>
                            <input type="text" name="backup_name" class="form-control" placeholder="backup_<?php echo date('Y-m-d_H-i-s'); ?>">
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="include_data" value="1" checked id="includeData">
                            <label class="form-check-label" for="includeData">
                                Uključi podatke (ne samo strukturu)
                            </label>
                        </div>
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-download"></i> Preuzmi Backup
                        </button>
                    </form>
                </div>
            </div>

            <!-- Tables Info -->
            <div class="card backup-card">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="bi bi-table"></i> Tablice u Bazi</h6>
                </div>
                <div class="card-body p-0">
                    <?php foreach ($tables as $table): ?>
                    <div class="table-stat">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?php echo htmlspecialchars($table['table_name']); ?></strong><br>
                                <small class="text-muted"><?php echo number_format($table['table_rows']); ?> redova</small>
                            </div>
                            <span class="badge bg-info"><?php echo $table['size_mb']; ?> MB</span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Restore Backup -->
        <div class="col-md-6 mb-4">
            <div class="card backup-card danger-zone">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="bi bi-upload"></i> Vraćanje iz Sigurnosne Kopije</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle-fill"></i> <strong>OPASNOST:</strong> 
                        Vraćanje backupa će <strong>prebrisati sve trenutne podatke</strong>!
                    </div>
                    <form id="restoreForm" enctype="multipart/form-data">
                        
                        <!-- CSRF Token (Requirement 33) -->
                        <?php echo CSRFToken::getField(); ?>
                        
                        <div class="mb-3">
                            <label class="form-label">Odaberite SQL datoteku:</label>
                            <input type="file" name="backup_file" class="form-control" accept=".sql" required>
                            <small class="text-muted">Maksimalna veličina: 50 MB</small>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="confirmRestore" required>
                            <label class="form-check-label" for="confirmRestore">
                                <strong>Razumijem da će ova akcija prebrisati sve podatke</strong>
                            </label>
                        </div>
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="bi bi-upload"></i> Vrati Backup (OPASNO!)
                        </button>
                    </form>
                </div>
            </div>

            <!-- Existing Backups -->
            <div class="card backup-card">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="bi bi-clock-history"></i> Postojeći Backupi (<?php echo count($backups); ?>)</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($backups)): ?>
                        <p class="text-muted text-center">Nema spremljenih backupa</p>
                    <?php else: ?>
                        <?php foreach ($backups as $backup): ?>
                        <div class="backup-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong><?php echo htmlspecialchars($backup['name']); ?></strong><br>
                                    <small class="text-muted">
                                        <?php echo date('d.m.Y H:i:s', $backup['date']); ?> - 
                                        <?php echo round($backup['size'] / 1024 / 1024, 2); ?> MB
                                    </small>
                                </div>
                                <div class="btn-group btn-group-sm">
                                    <a href="api/backup_database.php?action=download&file=<?php echo urlencode($backup['name']); ?>" 
                                       class="btn btn-primary" title="Preuzmi">
                                        <i class="bi bi-download"></i>
                                    </a>
                                    <button class="btn btn-danger" 
                                            onclick="deleteBackup('<?php echo htmlspecialchars($backup['name']); ?>')"
                                            title="Obriši">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Info -->
    <div class="alert alert-info">
        <h6 class="alert-heading"><i class="bi bi-info-circle"></i> Važne Informacije</h6>
        <ul class="mb-0">
            <li><strong>Backup:</strong> Kreira SQL datoteku s kompletnom bazom podataka</li>
            <li><strong>Restore:</strong> Učitava SQL datoteku i vraća podatke (prebrisuje postojeće!)</li>
            <li><strong>Automatski backup:</strong> Preporučujemo stvaranje backupa prije većih promjena</li>
            <li><strong>Sigurnost:</strong> Backupi se spremaju u <code>/backups</code> direktorij na serveru</li>
            <li><strong>MySQL putanja:</strong> <?php echo defined('MYSQL_BIN_PATH') ? MYSQL_BIN_PATH : 'C:/xampp/mysql/bin/'; ?></li>
        </ul>
    </div>
</div>

<!-- JavaScript -->
<script>
document.getElementById('restoreForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    if (!confirm('POSLJEDNJE UPOZORENJE: Jeste li potpuno sigurni da želite vratiti backup? SVE trenutne podatke će biti PREBRISANI!')) {
        return;
    }
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Vraćam backup...';
    
    try {
        const response = await fetch('api/restore_database.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert('success', data.message);
            setTimeout(() => location.reload(), 3000);
        } else {
            showAlert('danger', 'Greška: ' + data.message);
        }
    } catch (error) {
        showAlert('danger', 'Greška pri vraćanju backupa: ' + error.message);
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="bi bi-upload"></i> Vrati Backup (OPASNO!)';
    }
});

function deleteBackup(filename) {
    if (!confirm('Jeste li sigurni da želite obrisati backup: ' + filename + '?')) {
        return;
    }
    
    fetch('api/backup_database.php?action=delete&file=' + encodeURIComponent(filename))
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', 'Backup obrisan');
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert('danger', 'Greška: ' + data.message);
            }
        });
}

function showAlert(type, message) {
    const alertHTML = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    document.getElementById('alertContainer').innerHTML = alertHTML;
}
</script>

<?php include 'templates/footer.php'; ?>
