<?php
/**
 * SSL/HTTPS Status Dashboard
 * Prikazuje trenutni status HTTPS konfiguracije
 */

require_once('lib/https_checker.php');

$sslStatus = HTTPSChecker::checkSSLStatus();
?>
<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HTTPS Status - Hotel Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 50px 20px;
        }
        .status-card {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .status-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .status-body {
            padding: 40px;
        }
        .status-badge {
            font-size: 3rem;
            margin-bottom: 20px;
        }
        .info-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin: 15px 0;
        }
        .secure-connection {
            color: #28a745;
        }
        .insecure-connection {
            color: #dc3545;
        }
        .warning-connection {
            color: #ffc107;
        }
    </style>
</head>
<body>
    <div class="status-card">
        <div class="status-header">
            <h1><i class="bi bi-shield-lock-fill"></i> HTTPS Security Status</h1>
            <p class="mb-0">Provjera sigurnosti konekcije</p>
        </div>
        
        <div class="status-body">
            <!-- Current Status -->
            <div class="text-center mb-4">
                <?php if ($sslStatus['https_enabled']): ?>
                    <div class="status-badge secure-connection">
                        <i class="bi bi-shield-fill-check"></i>
                    </div>
                    <h2 class="secure-connection">Sigurna Konekcija (HTTPS)</h2>
                    <p class="text-muted">Tvoja konekcija je šifrirana SSL/TLS protokolom</p>
                <?php elseif ($sslStatus['localhost']): ?>
                    <div class="status-badge warning-connection">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                    <h2 class="warning-connection">Localhost Development Mode</h2>
                    <p class="text-muted">HTTPS nije omogućen (development environment)</p>
                <?php else: ?>
                    <div class="status-badge insecure-connection">
                        <i class="bi bi-shield-fill-x"></i>
                    </div>
                    <h2 class="insecure-connection">Nesigurna Konekcija (HTTP)</h2>
                    <p class="text-muted">Tvoja konekcija NIJE šifrirana!</p>
                <?php endif; ?>
            </div>
            
            <!-- Status Details -->
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="info-box">
                        <h5><i class="bi bi-info-circle"></i> Protokol</h5>
                        <p class="mb-0">
                            <strong><?= strtoupper($sslStatus['protocol']) ?></strong>
                            <?php if ($sslStatus['https_enabled']): ?>
                                <span class="badge bg-success ms-2">Sigurno</span>
                            <?php else: ?>
                                <span class="badge bg-danger ms-2">Nesigurno</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <div class="info-box">
                        <h5><i class="bi bi-hdd-network"></i> Port</h5>
                        <p class="mb-0">
                            <strong><?= htmlspecialchars($sslStatus['port']) ?></strong>
                            <?php if ($sslStatus['port'] == 443): ?>
                                <span class="badge bg-success ms-2">HTTPS Port</span>
                            <?php elseif ($sslStatus['port'] == 80): ?>
                                <span class="badge bg-danger ms-2">HTTP Port</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <div class="info-box">
                        <h5><i class="bi bi-laptop"></i> Environment</h5>
                        <p class="mb-0">
                            <?php if ($sslStatus['localhost']): ?>
                                <strong>Localhost Development</strong>
                                <span class="badge bg-warning ms-2">Dev</span>
                            <?php else: ?>
                                <strong>Production Server</strong>
                                <span class="badge bg-primary ms-2">Prod</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <div class="info-box">
                        <h5><i class="bi bi-server"></i> Server</h5>
                        <p class="mb-0">
                            <strong><?= htmlspecialchars($_SERVER['SERVER_NAME']) ?></strong>
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Recommendation -->
            <div class="alert <?= $sslStatus['https_enabled'] ? 'alert-success' : ($sslStatus['localhost'] ? 'alert-warning' : 'alert-danger') ?> mt-4">
                <h5><i class="bi bi-lightbulb"></i> Preporuka:</h5>
                <p class="mb-0"><?= htmlspecialchars($sslStatus['recommendation']) ?></p>
            </div>
            
            <!-- Security Features -->
            <h4 class="mt-5 mb-3"><i class="bi bi-shield-check"></i> HTTPS Sigurnosne Značajke</h4>
            
            <div class="row">
                <div class="col-md-6">
                    <ul class="list-group">
                        <li class="list-group-item">
                            <i class="bi bi-check-circle text-success"></i> 
                            <strong>Šifriranje</strong><br>
                            <small>Svi podaci šifrirani SSL/TLS protokolom</small>
                        </li>
                        <li class="list-group-item">
                            <i class="bi bi-check-circle text-success"></i> 
                            <strong>HSTS Header</strong><br>
                            <small>Strict-Transport-Security (force HTTPS)</small>
                        </li>
                        <li class="list-group-item">
                            <i class="bi bi-check-circle text-success"></i> 
                            <strong>Secure Cookies</strong><br>
                            <small>HttpOnly + Secure flags na kolačićima</small>
                        </li>
                    </ul>
                </div>
                
                <div class="col-md-6">
                    <ul class="list-group">
                        <li class="list-group-item">
                            <i class="bi bi-check-circle text-success"></i> 
                            <strong>XSS Protection</strong><br>
                            <small>X-XSS-Protection header aktivan</small>
                        </li>
                        <li class="list-group-item">
                            <i class="bi bi-check-circle text-success"></i> 
                            <strong>Clickjacking Prevention</strong><br>
                            <small>X-Frame-Options: SAMEORIGIN</small>
                        </li>
                        <li class="list-group-item">
                            <i class="bi bi-check-circle text-success"></i> 
                            <strong>MIME Sniffing Block</strong><br>
                            <small>X-Content-Type-Options: nosniff</small>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Setup Instructions -->
            <?php if (!$sslStatus['https_enabled']): ?>
            <div class="card mt-5">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-gear"></i> Kako Omogućiti HTTPS na XAMPP-u?</h5>
                </div>
                <div class="card-body">
                    <h6>1. Generiraj SSL Certifikat</h6>
                    <div class="bg-dark text-light p-3 rounded mb-3">
                        <code>cd C:\xampp\apache<br>
makecert.bat</code>
                    </div>
                    
                    <h6>2. Uredi httpd-ssl.conf</h6>
                    <p>Otvori: <code>C:\xampp\apache\conf\extra\httpd-ssl.conf</code></p>
                    <p>Promijeni:</p>
                    <div class="bg-dark text-light p-3 rounded mb-3">
                        <code>DocumentRoot "C:/xampp/htdocs/hotel_managment"<br>
ServerName localhost:443</code>
                    </div>
                    
                    <h6>3. Omogući SSL Modul</h6>
                    <p>Otvori: <code>C:\xampp\apache\conf\httpd.conf</code></p>
                    <p>Odkomentiraj (makni #):</p>
                    <div class="bg-dark text-light p-3 rounded mb-3">
                        <code>LoadModule ssl_module modules/mod_ssl.so<br>
Include conf/extra/httpd-ssl.conf</code>
                    </div>
                    
                    <h6>4. Restartuj Apache</h6>
                    <p>XAMPP Control Panel → Apache → Stop → Start</p>
                    
                    <h6>5. Pristup</h6>
                    <p>Otvori: <a href="https://localhost/hotel_managment/" target="_blank">https://localhost/hotel_managment/</a></p>
                    <p><small class="text-muted">Note: Browser će prikazati upozorenje o self-signed certifikatu (normalno za development)</small></p>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Test Links -->
            <div class="text-center mt-5">
                <h5 class="mb-3">Test Stranice</h5>
                <a href="login.php" class="btn btn-primary me-2">
                    <i class="bi bi-box-arrow-in-right"></i> Login
                </a>
                <a href="register.php" class="btn btn-success me-2">
                    <i class="bi bi-person-plus"></i> Registracija
                </a>
                <a href="index.php" class="btn btn-outline-secondary">
                    <i class="bi bi-house"></i> Početna
                </a>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
