<?php
/**
 * Cookie Test Page
 * Test cookie functionality and display cookie information
 */

require_once('lib/CookieManager.php');

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'accept_terms':
            CookieManager::setTermsAccepted('1.0');
            $message = 'Uvjeti prihvaćeni! ✓';
            break;
            
        case 'set_consent':
            $analytical = isset($_POST['analytical']);
            $marketing = isset($_POST['marketing']);
            CookieManager::setCookieConsent($analytical, $marketing);
            $message = 'Cookie postavke spremljene! ✓';
            break;
            
        case 'clear_all':
            CookieManager::clearAll();
            $message = 'Svi kolačići obrisani!';
            break;
    }
    
    // Redirect to prevent form resubmission
    header('Location: cookie-test.php?msg=' . urlencode($message));
    exit;
}

$message = $_GET['msg'] ?? null;
$cookieInfo = CookieManager::getAllInfo();
$hasTerms = CookieManager::hasAcceptedTerms();
$hasConsent = CookieManager::hasCookieConsent();
$termsInfo = CookieManager::getTermsInfo();
$consentInfo = CookieManager::getCookieConsent();
?>
<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cookie Test - Hotel Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            padding: 2rem;
            background: #f8f9fa;
        }
        .test-card {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
        }
        .status-yes {
            background: #d4edda;
            color: #155724;
        }
        .status-no {
            background: #f8d7da;
            color: #721c24;
        }
        .cookie-data {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 1rem;
            border-radius: 5px;
            font-family: monospace;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">
            <i class="bi bi-cookie"></i> Cookie Test Page
        </h1>

        <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <strong><i class="bi bi-check-circle"></i></strong> <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Status Overview -->
        <div class="test-card">
            <h2><i class="bi bi-info-circle"></i> Cookie Status</h2>
            <div class="row mt-3">
                <div class="col-md-6">
                    <strong>Terms Accepted:</strong>
                    <span class="status-badge <?php echo $hasTerms ? 'status-yes' : 'status-no'; ?>">
                        <i class="bi bi-<?php echo $hasTerms ? 'check-circle' : 'x-circle'; ?>"></i>
                        <?php echo $hasTerms ? 'Yes' : 'No'; ?>
                    </span>
                </div>
                <div class="col-md-6">
                    <strong>Cookie Consent:</strong>
                    <span class="status-badge <?php echo $hasConsent ? 'status-yes' : 'status-no'; ?>">
                        <i class="bi bi-<?php echo $hasConsent ? 'check-circle' : 'x-circle'; ?>"></i>
                        <?php echo $hasConsent ? 'Yes' : 'No'; ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Terms Information -->
        <?php if ($termsInfo): ?>
        <div class="test-card">
            <h2><i class="bi bi-file-text"></i> Terms Information</h2>
            <div class="cookie-data">
                <strong>Version:</strong> <?php echo htmlspecialchars($termsInfo['version']); ?><br>
                <strong>Accepted At:</strong> <?php echo date('Y-m-d H:i:s', $termsInfo['accepted_at']); ?><br>
                <strong>IP Address:</strong> <?php echo htmlspecialchars($termsInfo['ip_address']); ?><br>
                <strong>Unix Timestamp:</strong> <?php echo $termsInfo['accepted_at']; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Consent Information -->
        <?php if ($consentInfo): ?>
        <div class="test-card">
            <h2><i class="bi bi-shield-check"></i> Cookie Consent</h2>
            <div class="cookie-data">
                <strong>Essential:</strong> <span class="badge bg-success"><?php echo $consentInfo['essential'] ? 'Yes' : 'No'; ?></span><br>
                <strong>Analytical:</strong> <span class="badge bg-<?php echo $consentInfo['analytical'] ? 'success' : 'secondary'; ?>"><?php echo $consentInfo['analytical'] ? 'Yes' : 'No'; ?></span><br>
                <strong>Marketing:</strong> <span class="badge bg-<?php echo $consentInfo['marketing'] ? 'success' : 'secondary'; ?>"><?php echo $consentInfo['marketing'] ? 'Yes' : 'No'; ?></span><br>
                <strong>Timestamp:</strong> <?php echo date('Y-m-d H:i:s', $consentInfo['timestamp']); ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Actions -->
        <div class="test-card">
            <h2><i class="bi bi-gear"></i> Test Actions</h2>
            <div class="row g-3 mt-2">
                <div class="col-md-4">
                    <form method="POST">
                        <input type="hidden" name="action" value="accept_terms">
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-check-circle"></i> Accept Terms
                        </button>
                    </form>
                </div>
                <div class="col-md-4">
                    <form method="POST">
                        <input type="hidden" name="action" value="set_consent">
                        <div class="mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="analytical" id="analytical" checked>
                                <label class="form-check-label" for="analytical">Analytical</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="marketing" id="marketing">
                                <label class="form-check-label" for="marketing">Marketing</label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-gear"></i> Set Consent
                        </button>
                    </form>
                </div>
                <div class="col-md-4">
                    <form method="POST" onsubmit="return confirm('Clear all cookies?')">
                        <input type="hidden" name="action" value="clear_all">
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="bi bi-trash"></i> Clear All
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Raw Cookie Data -->
        <div class="test-card">
            <h2><i class="bi bi-code-square"></i> Raw Cookie Data</h2>
            <div class="cookie-data">
                <pre><?php echo json_encode($cookieInfo, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); ?></pre>
            </div>
        </div>

        <!-- Back to Home -->
        <div class="text-center">
            <a href="index.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Home
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
