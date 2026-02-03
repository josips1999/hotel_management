<?php
/**
 * Hotel Details Page
 * Displays detailed information about a single hotel
 * SEO-friendly URL support: /hotel/123/naziv-hotela
 */

require_once('lib/db_connection.php');
require_once('lib/SessionManager.php');
require_once('lib/SEOHelper.php');

$sessionManager = new SessionManager($connection);
$sessionManager->checkRememberMe();
$isLoggedIn = $sessionManager->isLoggedIn();
$username = $sessionManager->getUsername();

// Get hotel ID from URL
$hotelId = $_GET['id'] ?? 0;

if (!$hotelId) {
    header('Location: index.php');
    exit;
}

// Fetch hotel details
$stmt = $conn->prepare("SELECT * FROM hotels WHERE id = ?");
$stmt->bind_param("i", $hotelId);
$stmt->execute();
$result = $stmt->get_result();
$hotel = $result->fetch_assoc();
$stmt->close();

if (!$hotel) {
    header('Location: index.php?error=not_found');
    exit;
}

// Page title and meta
$pageTitle = SEOHelper::createPageTitle($hotel['naziv']);
$metaDescription = SEOHelper::createMetaDescription("Hotel " . $hotel['naziv'] . " u gradu " . $hotel['grad'] . ". Kapacitet: " . $hotel['kapacitet'] . " osoba, Broj soba: " . $hotel['broj_soba']);
$canonicalUrl = SEOHelper::hotelUrlFull($hotel['id'], $hotel['naziv']);

?>
<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <meta name="description" content="<?php echo $metaDescription; ?>">
    <link rel="canonical" href="<?php echo $canonicalUrl; ?>">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        .hotel-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 0;
            margin-bottom: 40px;
        }
        .detail-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .detail-label {
            font-weight: 600;
            color: #666;
            margin-bottom: 5px;
        }
        .detail-value {
            font-size: 1.1rem;
            color: #333;
        }
        .stat-box {
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 20px;
        }
        .stat-box i {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        .stat-box .number {
            font-size: 2rem;
            font-weight: bold;
        }
        .stat-box .label {
            color: #666;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

<?php include 'templates/header.php'; ?>

<!-- Hotel Header -->
<div class="hotel-header">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb text-white">
                <li class="breadcrumb-item"><a href="index.php" class="text-white">Hoteli</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">
                    <?php echo SEOHelper::escape($hotel['naziv']); ?>
                </li>
            </ol>
        </nav>
        <h1 class="display-4 mb-3">
            <i class="bi bi-building"></i> <?php echo SEOHelper::escape($hotel['naziv']); ?>
        </h1>
        <p class="lead">
            <i class="bi bi-geo-alt"></i> <?php echo SEOHelper::escape($hotel['grad']); ?>, 
            <?php echo SEOHelper::escape($hotel['zupanija']); ?>
        </p>
    </div>
</div>

<div class="container pb-5">
    <div class="row">
        <!-- Statistics -->
        <div class="col-md-3">
            <div class="stat-box bg-light">
                <i class="bi bi-people-fill text-primary"></i>
                <div class="number"><?php echo $hotel['kapacitet']; ?></div>
                <div class="label">Kapacitet</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-box bg-light">
                <i class="bi bi-door-closed-fill text-info"></i>
                <div class="number"><?php echo $hotel['broj_soba']; ?></div>
                <div class="label">Broj soba</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-box bg-light">
                <i class="bi bi-person-check-fill text-warning"></i>
                <div class="number"><?php echo $hotel['broj_gostiju']; ?></div>
                <div class="label">Gostiju</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-box bg-light">
                <i class="bi bi-door-open-fill text-success"></i>
                <div class="number"><?php echo $hotel['slobodno_soba']; ?></div>
                <div class="label">Slobodno</div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <!-- Main Information -->
        <div class="col-md-8">
            <div class="card detail-card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Osnovne Informacije</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="detail-label">Naziv hotela:</div>
                            <div class="detail-value"><?php echo SEOHelper::escape($hotel['naziv']); ?></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="detail-label">Adresa:</div>
                            <div class="detail-value"><?php echo SEOHelper::escape($hotel['adresa']); ?></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="detail-label">Grad:</div>
                            <div class="detail-value"><?php echo SEOHelper::escape($hotel['grad']); ?></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="detail-label">Županija:</div>
                            <div class="detail-value"><?php echo SEOHelper::escape($hotel['zupanija']); ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card detail-card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-telephone"></i> Kontakt Informacije</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="detail-label">Kontakt osoba:</div>
                            <div class="detail-value"><?php echo SEOHelper::escape($hotel['kontakt_ime']); ?></div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="detail-label">Telefon:</div>
                            <div class="detail-value">
                                <a href="tel:<?php echo SEOHelper::escape($hotel['kontakt_tel']); ?>">
                                    <?php echo SEOHelper::escape($hotel['kontakt_tel']); ?>
                                </a>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="detail-label">Email:</div>
                            <div class="detail-value">
                                <a href="mailto:<?php echo SEOHelper::escape($hotel['kontakt_email']); ?>">
                                    <?php echo SEOHelper::escape($hotel['kontakt_email']); ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Side Information -->
        <div class="col-md-4">
            <div class="card detail-card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-bar-chart"></i> Status</h5>
                </div>
                <div class="card-body">
                    <?php
                    $occupancyRate = ($hotel['broj_soba'] > 0) 
                        ? round((($hotel['broj_soba'] - $hotel['slobodno_soba']) / $hotel['broj_soba']) * 100, 1) 
                        : 0;
                    ?>
                    <div class="mb-3">
                        <div class="detail-label">Popunjenost:</div>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar <?php echo $occupancyRate > 80 ? 'bg-danger' : ($occupancyRate > 50 ? 'bg-warning' : 'bg-success'); ?>" 
                                 style="width: <?php echo $occupancyRate; ?>%">
                                <?php echo $occupancyRate; ?>%
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2 mt-4">
                        <a href="index.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Povratak na listu
                        </a>
                        <?php if ($isLoggedIn): ?>
                        <a href="index.php?edit=<?php echo $hotel['id']; ?>" class="btn btn-warning">
                            <i class="bi bi-pencil"></i> Uredi hotel
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- SEO Information (for demo) -->
            <div class="card detail-card">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0"><i class="bi bi-search"></i> SEO Info</h6>
                </div>
                <div class="card-body">
                    <small class="text-muted">
                        <strong>Canonical URL:</strong><br>
                        <code style="font-size: 0.75rem; word-break: break-all;">
                            <?php echo htmlspecialchars($canonicalUrl); ?>
                        </code>
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'templates/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
