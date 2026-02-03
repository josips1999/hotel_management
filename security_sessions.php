<?php
/**
 * Security Dashboard - Active Sessions
 * Prikazuje aktivne "Remember Me" tokene za trenutno prijavljenog korisnika
 */

require_once('lib/db_connection.php');
require_once('lib/SessionManager.php');
mysqli_select_db($connection,'hotel_management');

// Initialize session manager
$sessionManager = new SessionManager($connection);

// Check if user is logged in
if (!$sessionManager->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Get current user info
$username = $sessionManager->getUsername();
$userId = $sessionManager->getUserId();

// Get active tokens
$activeTokens = $sessionManager->getUserActiveTokens();

// Handle token revocation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['revoke_token_id'])) {
    $tokenId = (int)$_POST['revoke_token_id'];
    if ($sessionManager->revokeToken($tokenId)) {
        $successMessage = "Token uspješno opozvan!";
        // Refresh token list
        $activeTokens = $sessionManager->getUserActiveTokens();
    } else {
        $errorMessage = "Greška pri opozivanju tokena.";
    }
}

// Get browser/device info from user agent
function getBrowserInfo($userAgent) {
    if (strpos($userAgent, 'Chrome') !== false) return 'Chrome';
    if (strpos($userAgent, 'Firefox') !== false) return 'Firefox';
    if (strpos($userAgent, 'Safari') !== false) return 'Safari';
    if (strpos($userAgent, 'Edge') !== false) return 'Edge';
    if (strpos($userAgent, 'Opera') !== false) return 'Opera';
    return 'Nepoznati Browser';
}

function getDeviceInfo($userAgent) {
    if (strpos($userAgent, 'Mobile') !== false) return 'Mobilni Uređaj';
    if (strpos($userAgent, 'Tablet') !== false) return 'Tablet';
    return 'Desktop';
}
?>
<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aktivne Sesije - Hotel Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        .token-card {
            transition: all 0.3s ease;
        }
        .token-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        .current-device {
            border-left: 4px solid #28a745 !important;
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-building"></i> Hotel Management
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text text-white me-3">
                    <i class="bi bi-person-circle"></i> <?= htmlspecialchars($username) ?>
                </span>
                <a href="index.php" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-arrow-left"></i> Povratak
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row">
            <div class="col-lg-10 mx-auto">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="bi bi-shield-lock"></i> Sigurnost - Aktivne Sesije
                        </h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($successMessage)): ?>
                            <div class="alert alert-success alert-dismissible fade show">
                                <i class="bi bi-check-circle"></i> <?= $successMessage ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($errorMessage)): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <i class="bi bi-exclamation-triangle"></i> <?= $errorMessage ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            <strong>Što su "Remember Me" tokeni?</strong><br>
                            Ovi tokeni omogućavaju automatsku prijavu bez ponovnog unosa lozinke. 
                            Svaki token predstavlja jedan uređaj na kojem ste označili "Zapamti me" pri prijavi.
                            Možete opozvati pristup za bilo koji uređaj ako sumnjate na neovlašteni pristup.
                        </div>
                        
                        <?php if (empty($activeTokens)): ?>
                            <div class="text-center py-5">
                                <i class="bi bi-inbox" style="font-size: 4rem; color: #ccc;"></i>
                                <h5 class="mt-3 text-muted">Nema aktivnih "Remember Me" tokena</h5>
                                <p class="text-muted">Označite "Zapamti me" pri sljedećoj prijavi da kreirate token.</p>
                            </div>
                        <?php else: ?>
                            <h5 class="mb-4">
                                <i class="bi bi-devices"></i> Aktivni uređaji (<?= count($activeTokens) ?>)
                            </h5>
                            
                            <div class="row">
                                <?php 
                                $currentUserAgent = $_SERVER['HTTP_USER_AGENT'];
                                $currentIP = $_SERVER['REMOTE_ADDR'];
                                
                                foreach ($activeTokens as $token): 
                                    $isCurrentDevice = ($token['ip_address'] === $currentIP && $token['user_agent'] === $currentUserAgent);
                                    $browser = getBrowserInfo($token['user_agent']);
                                    $device = getDeviceInfo($token['user_agent']);
                                ?>
                                <div class="col-md-6 mb-3">
                                    <div class="card token-card <?= $isCurrentDevice ? 'current-device' : '' ?>">
                                        <div class="card-body">
                                            <?php if ($isCurrentDevice): ?>
                                                <span class="badge bg-success float-end">
                                                    <i class="bi bi-check-circle"></i> Trenutni Uređaj
                                                </span>
                                            <?php endif; ?>
                                            
                                            <h6 class="card-title">
                                                <i class="bi bi-<?= $device === 'Mobilni Uređaj' ? 'phone' : ($device === 'Tablet' ? 'tablet' : 'laptop') ?>"></i>
                                                <?= htmlspecialchars($device) ?>
                                            </h6>
                                            
                                            <p class="mb-2">
                                                <small class="text-muted">
                                                    <i class="bi bi-browser-chrome"></i> <?= htmlspecialchars($browser) ?>
                                                </small>
                                            </p>
                                            
                                            <p class="mb-2">
                                                <small class="text-muted">
                                                    <i class="bi bi-geo-alt"></i> IP: <?= htmlspecialchars($token['ip_address']) ?>
                                                </small>
                                            </p>
                                            
                                            <p class="mb-2">
                                                <small class="text-muted">
                                                    <i class="bi bi-clock"></i> Kreirano: <?= date('d.m.Y H:i', strtotime($token['created_at'])) ?>
                                                </small>
                                            </p>
                                            
                                            <?php if ($token['last_used_at']): ?>
                                                <p class="mb-2">
                                                    <small class="text-muted">
                                                        <i class="bi bi-clock-history"></i> Zadnje korišteno: <?= date('d.m.Y H:i', strtotime($token['last_used_at'])) ?>
                                                    </small>
                                                </p>
                                            <?php endif; ?>
                                            
                                            <p class="mb-3">
                                                <small class="text-muted">
                                                    <i class="bi bi-calendar-x"></i> Ističe: <?= date('d.m.Y H:i', strtotime($token['expires_at'])) ?>
                                                </small>
                                            </p>
                                            
                                            <form method="POST" onsubmit="return confirm('Jeste li sigurni da želite opozvati pristup ovom uređaju?');">
                                                <input type="hidden" name="revoke_token_id" value="<?= $token['id'] ?>">
                                                <button type="submit" class="btn btn-danger btn-sm w-100">
                                                    <i class="bi bi-trash"></i> Opozovi Pristup
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="alert alert-warning mt-4">
                                <i class="bi bi-exclamation-triangle"></i>
                                <strong>Sigurnosni savjet:</strong> Ako vidite uređaj koji ne prepoznajete, 
                                odmah opozvite pristup i promijenite lozinku.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Session Info Card -->
                <div class="card shadow-sm mt-4">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-info-circle"></i> Trenutna Sesija
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php $sessionInfo = $sessionManager->getSessionInfo(); ?>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Korisnik:</strong> <?= htmlspecialchars($sessionInfo['username']) ?></p>
                                <p><strong>Email:</strong> <?= htmlspecialchars($sessionInfo['email']) ?></p>
                                <p><strong>Status:</strong> 
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle"></i> Prijavljen
                                    </span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Prijava:</strong> <?= $sessionInfo['login_time'] ?></p>
                                <p><strong>Zadnja Aktivnost:</strong> <?= $sessionInfo['last_activity'] ?></p>
                                <p><strong>Remember Me:</strong> 
                                    <?php if ($sessionInfo['remember_me_active']): ?>
                                        <span class="badge bg-success">Aktivan</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Neaktivan</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Configuration Info -->
                <div class="card shadow-sm mt-4 mb-5">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-gear"></i> Postavke Sustava
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Remember Me Trajanje:</strong> <?= REMEMBER_ME_DURATION_DAYS ?> dana</p>
                                <p><strong>Session Timeout:</strong> <?= SESSION_TIMEOUT_MINUTES ?> minuta</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Token Selector:</strong> <?= TOKEN_SELECTOR_BYTES * 2 ?> znakova (<?= TOKEN_SELECTOR_BYTES ?> bytes)</p>
                                <p><strong>Token Validator:</strong> <?= TOKEN_VALIDATOR_BYTES * 2 ?> znakova (<?= TOKEN_VALIDATOR_BYTES ?> bytes)</p>
                            </div>
                        </div>
                        
                        <div class="alert alert-info mb-0 mt-3">
                            <small>
                                <i class="bi bi-info-circle"></i>
                                Ove postavke mogu se mijenjati u <code>lib/config.php</code> datoteci.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
