<?php
/**
 * System Settings Page - Admin Only
 * 
 * Configure system-wide settings:
 * - Items per page (pagination)
 * - Max login attempts
 * - Lockout duration
 * - Session lifetime
 * - Remember Me duration
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

// Get all settings
$sql = "SELECT * FROM system_settings ORDER BY category, id";
$result = $conn->query($sql);
$settings = $result->fetch_all(MYSQLI_ASSOC);

// Group by category
$groupedSettings = [];
foreach ($settings as $setting) {
    $category = $setting['category'] ?: 'other';
    $groupedSettings[$category][] = $setting;
}

// Page variables
$pageTitle = 'System Settings - Admin Panel';
$currentPage = 'system_settings';

$customCSS = "
    .settings-card {
        border: none;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }
    .settings-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px 10px 0 0;
        padding: 20px;
    }
    .setting-item {
        padding: 20px;
        border-bottom: 1px solid #e9ecef;
        transition: background 0.3s;
    }
    .setting-item:hover {
        background: #f8f9fa;
    }
    .setting-item:last-child {
        border-bottom: none;
    }
    .setting-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 5px;
    }
    .setting-description {
        font-size: 0.875rem;
        color: #6c757d;
    }
    .setting-input {
        max-width: 200px;
    }
    .save-indicator {
        display: none;
        color: #28a745;
        font-size: 0.875rem;
    }
";

?>
<?php include 'templates/header.php'; ?>

<div class="container py-4">
    <!-- Admin Header -->
    <div class="alert alert-danger mb-4">
        <h4 class="alert-heading">
            <i class="bi bi-gear-fill"></i> Admin Panel - System Settings
        </h4>
        <p class="mb-0">Konfigurirajte postavke sustava. Promjene se primjenjuju odmah.</p>
    </div>

    <!-- Alert Container -->
    <div id="alertContainer"></div>

    <!-- General Settings -->
    <?php if (isset($groupedSettings['general'])): ?>
    <div class="settings-card">
        <div class="settings-header">
            <h5 class="mb-0"><i class="bi bi-sliders"></i> Opće Postavke</h5>
        </div>
        <div class="card-body p-0">
            <?php foreach ($groupedSettings['general'] as $setting): ?>
            <div class="setting-item">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="setting-label"><?php echo ucfirst(str_replace('_', ' ', $setting['setting_key'])); ?></div>
                        <div class="setting-description"><?php echo htmlspecialchars($setting['description']); ?></div>
                    </div>
                    <div class="col-md-4">
                        <input 
                            type="number" 
                            class="form-control setting-input" 
                            value="<?php echo htmlspecialchars($setting['setting_value']); ?>"
                            onchange="saveSetting('<?php echo $setting['setting_key']; ?>', this.value, this)"
                            min="1"
                            max="100"
                        >
                    </div>
                    <div class="col-md-2">
                        <span class="save-indicator">
                            <i class="bi bi-check-circle-fill"></i> Spremljeno
                        </span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Security Settings -->
    <?php if (isset($groupedSettings['security'])): ?>
    <div class="settings-card">
        <div class="settings-header">
            <h5 class="mb-0"><i class="bi bi-shield-lock-fill"></i> Sigurnosne Postavke</h5>
        </div>
        <div class="card-body p-0">
            <?php foreach ($groupedSettings['security'] as $setting): ?>
            <div class="setting-item">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="setting-label"><?php echo ucfirst(str_replace('_', ' ', $setting['setting_key'])); ?></div>
                        <div class="setting-description"><?php echo htmlspecialchars($setting['description']); ?></div>
                    </div>
                    <div class="col-md-4">
                        <input 
                            type="number" 
                            class="form-control setting-input" 
                            value="<?php echo htmlspecialchars($setting['setting_value']); ?>"
                            onchange="saveSetting('<?php echo $setting['setting_key']; ?>', this.value, this)"
                            min="1"
                        >
                    </div>
                    <div class="col-md-2">
                        <span class="save-indicator">
                            <i class="bi bi-check-circle-fill"></i> Spremljeno
                        </span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Add New Setting -->
    <div class="card settings-card">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Dodaj Novu Postavku</h5>
        </div>
        <div class="card-body">
            <form id="addSettingForm">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Ključ Postavke *</label>
                        <input type="text" class="form-control" id="new_key" required placeholder="npr. max_upload_size">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Vrijednost *</label>
                        <input type="text" class="form-control" id="new_value" required placeholder="npr. 10">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Kategorija *</label>
                        <select class="form-select" id="new_category" required>
                            <option value="general">General</option>
                            <option value="security">Security</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-plus-circle"></i> Dodaj
                        </button>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Opis</label>
                    <textarea class="form-control" id="new_description" rows="2"></textarea>
                </div>
            </form>
        </div>
    </div>

    <!-- Info Card -->
    <div class="alert alert-info">
        <h6 class="alert-heading"><i class="bi bi-info-circle"></i> Napomene</h6>
        <ul class="mb-0">
            <li><strong>Items Per Page:</strong> Broj hotela prikazanih po stranici (paginator)</li>
            <li><strong>Max Login Attempts:</strong> Broj dozvoljenih neuspješnih prijava prije zaključavanja računa</li>
            <li><strong>Lockout Duration:</strong> Koliko minuta račun ostaje zaključan nakon max pokušaja</li>
            <li><strong>Session Lifetime:</strong> Trajanje sesije u sekundama (3600 = 1 sat)</li>
            <li><strong>Remember Me Days:</strong> Trajanje "Zapamti me" kolačića u danima</li>
        </ul>
    </div>
</div>

<!-- JavaScript -->
<script>
// Get CSRF token
const csrfToken = '<?php echo CSRFToken::get(); ?>';

function saveSetting(key, value, inputElement) {
    const indicator = inputElement.parentElement.nextElementSibling.querySelector('.save-indicator');
    
    fetch('api/save_setting.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ 
            key: key, 
            value: value,
            csrf_token: csrfToken
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            indicator.style.display = 'inline';
            setTimeout(() => {
                indicator.style.display = 'none';
            }, 2000);
            
            // Update config.php if needed
            if (key === 'items_per_page') {
                showAlert('success', 'Postavka spremljena! Možda će biti potrebno osvježiti stranicu.');
            }
        } else {
            alert('Greška: ' + data.message);
        }
    });
}

document.getElementById('addSettingForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const key = document.getElementById('new_key').value;
    const value = document.getElementById('new_value').value;
    const category = document.getElementById('new_category').value;
    const description = document.getElementById('new_description').value;
    
    fetch('api/save_setting.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ 
            action: 'add',
            key: key, 
            value: value,
            category: category,
            description: description,
            csrf_token: csrfToken
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', 'Nova postavka dodana!');
            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert('danger', 'Greška: ' + data.message);
        }
    });
});

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
