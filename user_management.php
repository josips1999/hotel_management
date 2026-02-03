<?php
/**
 * User Management Page - Admin Only
 * 
 * View all user accounts and manage:
 * - Activate/Deactivate accounts
 * - Lock/Unlock accounts
 * - View user details
 * - Change user roles
 */

// ============================================================================
// PHP CODE - Business Logic
// ============================================================================

require_once('lib/db_connection.php');
require_once('lib/config.php');
require_once('lib/SessionManager.php');
require_once('lib/CSRFToken.php');
mysqli_select_db($connection,'hotel_management');

$sessionManager = new SessionManager($connection);
$sessionManager->checkRememberMe();
$isLoggedIn = $sessionManager->isLoggedIn();
$username = $sessionManager->getUsername();
$userId = $sessionManager->getUserId();

// Check if user is admin
$stmt = $connection->prepare("SELECT role FROM users WHERE id = ?");
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

// Get all users
$sql = "SELECT id, username, email, is_verified, is_active, is_locked, failed_login_attempts, 
        locked_until, role, created_at 
        FROM users 
        ORDER BY created_at DESC";
$result = $conn->query($sql);
$users = $result->fetch_all(MYSQLI_ASSOC);

// Count statistics
$totalUsers = count($users);
$activeUsers = count(array_filter($users, fn($u) => $u['is_active'] == 1));
$lockedUsers = count(array_filter($users, fn($u) => $u['is_locked'] == 1));
$verifiedUsers = count(array_filter($users, fn($u) => $u['is_verified'] == 1));

// Page variables
$pageTitle = 'User Management - Admin Panel';
$currentPage = 'user_management';

$customCSS = "
    .user-card {
        border: none;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        margin-bottom: 15px;
        transition: all 0.3s;
    }
    .user-card:hover {
        box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        transform: translateY(-2px);
    }
    .stat-card {
        border-radius: 10px;
        padding: 20px;
        text-align: center;
        color: white;
    }
    .stat-card h3 {
        font-size: 2.5rem;
        font-weight: 700;
        margin: 10px 0;
    }
    .status-badge {
        font-size: 0.75rem;
        padding: 4px 8px;
    }
    .admin-badge {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }
";

?>
<?php include 'templates/header.php'; ?>

<div class="container py-4">
    <!-- Admin Header -->
    <div class="alert alert-danger mb-4">
        <h4 class="alert-heading">
            <i class="bi bi-shield-fill-exclamation"></i> Admin Panel - User Management
        </h4>
        <p class="mb-0">Ovdje možete upravljati svim korisničkim računima. Pažnja: Promjene stupaju na snagu odmah!</p>
    </div>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <i class="bi bi-people-fill" style="font-size: 2rem;"></i>
                <h3><?php echo $totalUsers; ?></h3>
                <div>Ukupno Korisnika</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                <i class="bi bi-check-circle-fill" style="font-size: 2rem;"></i>
                <h3><?php echo $activeUsers; ?></h3>
                <div>Aktivni</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                <i class="bi bi-lock-fill" style="font-size: 2rem;"></i>
                <h3><?php echo $lockedUsers; ?></h3>
                <div>Zaključani</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <i class="bi bi-patch-check-fill" style="font-size: 2rem;"></i>
                <h3><?php echo $verifiedUsers; ?></h3>
                <div>Verificirani</div>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-people"></i> Svi Korisnički Računi</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Korisnik</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Rola</th>
                            <th>Neuspješne Prijave</th>
                            <th>Datum Kreiranja</th>
                            <th class="text-center">Akcije</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                                <?php if ($user['role'] === 'admin'): ?>
                                <span class="badge admin-badge ms-2">ADMIN</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <?php if ($user['is_verified']): ?>
                                <span class="badge bg-success status-badge">
                                    <i class="bi bi-patch-check"></i> Verificiran
                                </span>
                                <?php else: ?>
                                <span class="badge bg-warning status-badge">
                                    <i class="bi bi-clock"></i> Neverificiran
                                </span>
                                <?php endif; ?>
                                
                                <?php if ($user['is_active']): ?>
                                <span class="badge bg-success status-badge">
                                    <i class="bi bi-check-circle"></i> Aktivan
                                </span>
                                <?php else: ?>
                                <span class="badge bg-secondary status-badge">
                                    <i class="bi bi-x-circle"></i> Neaktivan
                                </span>
                                <?php endif; ?>
                                
                                <?php if ($user['is_locked']): ?>
                                <span class="badge bg-danger status-badge">
                                    <i class="bi bi-lock"></i> Zaključan
                                </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <select class="form-select form-select-sm" onchange="changeRole(<?php echo $user['id']; ?>, this.value)">
                                    <option value="user" <?php echo $user['role'] === 'user' ? 'selected' : ''; ?>>User</option>
                                    <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                </select>
                            </td>
                            <td>
                                <?php if ($user['failed_login_attempts'] > 0): ?>
                                <span class="badge bg-warning">
                                    <?php echo $user['failed_login_attempts']; ?>x
                                </span>
                                <?php else: ?>
                                <span class="text-muted">0</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <small><?php echo date('d.m.Y H:i', strtotime($user['created_at'])); ?></small>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <!-- Activate/Deactivate -->
                                    <?php if ($user['is_active']): ?>
                                    <button class="btn btn-outline-secondary" 
                                            onclick="toggleActive(<?php echo $user['id']; ?>, 0)"
                                            title="Deaktiviraj">
                                        <i class="bi bi-x-circle"></i>
                                    </button>
                                    <?php else: ?>
                                    <button class="btn btn-outline-success" 
                                            onclick="toggleActive(<?php echo $user['id']; ?>, 1)"
                                            title="Aktiviraj">
                                        <i class="bi bi-check-circle"></i>
                                    </button>
                                    <?php endif; ?>
                                    
                                    <!-- Lock/Unlock -->
                                    <?php if ($user['is_locked']): ?>
                                    <button class="btn btn-outline-warning" 
                                            onclick="toggleLock(<?php echo $user['id']; ?>, 0)"
                                            title="Otključaj">
                                        <i class="bi bi-unlock"></i>
                                    </button>
                                    <?php else: ?>
                                    <button class="btn btn-outline-danger" 
                                            onclick="toggleLock(<?php echo $user['id']; ?>, 1)"
                                            title="Zaključaj">
                                        <i class="bi bi-lock"></i>
                                    </button>
                                    <?php endif; ?>
                                    
                                    <!-- Reset Password -->
                                    <button class="btn btn-outline-info" 
                                            onclick="resetFailedAttempts(<?php echo $user['id']; ?>)"
                                            title="Resetiraj neuspješne pokušaje">
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script>
// Get CSRF token
const csrfToken = '<?php echo CSRFToken::get(); ?>';

function toggleActive(userId, status) {
    if (!confirm(`Jeste li sigurni da želite ${status ? 'aktivirati' : 'deaktivirati'} ovog korisnika?`)) {
        return;
    }
    
    fetch('api/user_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ 
            action: 'toggle_active', 
            user_id: userId, 
            is_active: status,
            csrf_token: csrfToken
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Greška: ' + data.message);
        }
    });
}

function toggleLock(userId, status) {
    if (!confirm(`Jeste li sigurni da želite ${status ? 'zaključati' : 'otključati'} ovog korisnika?`)) {
        return;
    }
    
    fetch('api/user_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ 
            action: 'toggle_lock', 
            user_id: userId, 
            is_locked: status,
            csrf_token: csrfToken
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Greška: ' + data.message);
        }
    });
}

function changeRole(userId, role) {
    if (!confirm(`Jeste li sigurni da želite promijeniti rolu korisnika u ${role.toUpperCase()}?`)) {
        location.reload();
        return;
    }
    
    fetch('api/user_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ 
            action: 'change_role', 
            user_id: userId, 
            role: role,
            csrf_token: csrfToken
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Greška: ' + data.message);
            location.reload();
        }
    });
}

function resetFailedAttempts(userId) {
    if (!confirm('Jeste li sigurni da želite resetirati broj neuspješnih pokušaja prijave?')) {
        return;
    }
    
    fetch('api/user_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ 
            action: 'reset_attempts', 
            user_id: userId,
            csrf_token: csrfToken
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Greška: ' + data.message);
        }
    });
}
</script>

<?php include 'templates/footer.php'; ?>
