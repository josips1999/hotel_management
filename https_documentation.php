<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HTTPS Implementacija - Dokumentacija</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            padding: 50px 20px;
            min-height: 100vh;
        }
        .docs-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .docs-header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }
        .docs-body {
            padding: 40px;
        }
        .feature-box {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s;
        }
        .feature-box:hover {
            border-color: #1e3c72;
            box-shadow: 0 5px 15px rgba(30, 60, 114, 0.2);
        }
        .code-block {
            background: #2d3436;
            color: #dfe6e9;
            padding: 20px;
            border-radius: 8px;
            overflow-x: auto;
            font-family: 'Courier New', monospace;
            margin: 15px 0;
        }
        .step-card {
            background: #f8f9fa;
            padding: 20px;
            border-left: 4px solid #1e3c72;
            margin-bottom: 15px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="docs-container">
        <div class="docs-header">
            <h1><i class="bi bi-shield-lock-fill"></i> HTTPS Implementacija</h1>
            <p class="lead mb-0">Sigurna prijava isključivo preko HTTPS protokola</p>
        </div>
        
        <div class="docs-body">
            <!-- Što je implementirano -->
            <div class="alert alert-success">
                <h4><i class="bi bi-check-circle-fill"></i> Implementacija Završena!</h4>
                <p class="mb-0">
                    Aplikacija sada **zahtijeva HTTPS** za sve autentifikacijske stranice (login, registracija).
                    Korisnici koji pokušaju pristupiti preko HTTP-a automatski se preusmjeravaju na HTTPS.
                </p>
            </div>
            
            <!-- Glavne Komponente -->
            <h2 class="mt-5 mb-4"><i class="bi bi-puzzle-fill"></i> Implementirane Komponente</h2>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="feature-box">
                        <h5><i class="bi bi-file-earmark-code text-primary"></i> .htaccess</h5>
                        <p>Apache konfiguracija za automatsko preusmjeravanje HTTP → HTTPS</p>
                        <ul class="mb-0">
                            <li>RewriteEngine za URL redirect</li>
                            <li>HSTS header (Strict-Transport-Security)</li>
                            <li>Security headers (XSS, Clickjacking, CSP)</li>
                        </ul>
                    </div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <div class="feature-box">
                        <h5><i class="bi bi-shield-check text-success"></i> HTTPSChecker.php</h5>
                        <p>PHP klasa za detekciju i forsiranje HTTPS protokola</p>
                        <ul class="mb-0">
                            <li><code>isHTTPS()</code> - Provjera protokola</li>
                            <li><code>forceHTTPS()</code> - Redirect na HTTPS</li>
                            <li><code>requireHTTPSForAuth()</code> - Auth zaštita</li>
                        </ul>
                    </div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <div class="feature-box">
                        <h5><i class="bi bi-lock-fill text-warning"></i> Secure Cookies</h5>
                        <p>SessionManager automatski koristi secure flag</p>
                        <ul class="mb-0">
                            <li>Secure flag: true na HTTPS</li>
                            <li>HttpOnly: Sprječava JS pristup</li>
                            <li>SameSite: Lax (CSRF zaštita)</li>
                        </ul>
                    </div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <div class="feature-box">
                        <h5><i class="bi bi-badge-vo text-info"></i> HTTPS Badge</h5>
                        <p>Vizualni indikator sigurnosti konekcije</p>
                        <ul class="mb-0">
                            <li>Zeleni badge: HTTPS aktivan</li>
                            <li>Žuti badge: HTTP (localhost dev)</li>
                            <li>Crveni badge: HTTP (produkcija)</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- .htaccess Pravila -->
            <h2 class="mt-5 mb-4"><i class="bi bi-file-earmark-arrow-up"></i> .htaccess Konfiguracija</h2>
            
            <div class="step-card">
                <h6>1. HTTP → HTTPS Preusmjeravanje</h6>
                <div class="code-block">
                    <pre>RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]</pre>
                </div>
                <p class="mb-0"><small>Svi HTTP zahtjevi preusmjeravaju se na HTTPS (301 Permanent Redirect)</small></p>
            </div>
            
            <div class="step-card">
                <h6>2. HSTS Header (Strict-Transport-Security)</h6>
                <div class="code-block">
                    <pre>Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains; preload"</pre>
                </div>
                <p class="mb-0"><small>Browser uvijek koristi HTTPS za ovu domenu (1 godina cache)</small></p>
            </div>
            
            <div class="step-card">
                <h6>3. Security Headers</h6>
                <div class="code-block">
                    <pre>Header always set X-Frame-Options "SAMEORIGIN"
Header always set X-XSS-Protection "1; mode=block"
Header always set X-Content-Type-Options "nosniff"
Header always set Content-Security-Policy "..."</pre>
                </div>
                <p class="mb-0"><small>Dodatna zaštita: Clickjacking, XSS, MIME Sniffing, CSP</small></p>
            </div>
            
            <!-- HTTPSChecker Klasa -->
            <h2 class="mt-5 mb-4"><i class="bi bi-code-slash"></i> HTTPSChecker API</h2>
            
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>Metoda</th>
                            <th>Opis</th>
                            <th>Return</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>isHTTPS()</code></td>
                            <td>Provjerava je li konekcija HTTPS</td>
                            <td>bool</td>
                        </tr>
                        <tr>
                            <td><code>forceHTTPS($permanent)</code></td>
                            <td>Redirect na HTTPS (301/302)</td>
                            <td>void</td>
                        </tr>
                        <tr>
                            <td><code>isLocalhost()</code></td>
                            <td>Provjerava je li localhost</td>
                            <td>bool</td>
                        </tr>
                        <tr>
                            <td><code>requireHTTPSForAuth()</code></td>
                            <td>Zahtijeva HTTPS za auth stranice</td>
                            <td>void</td>
                        </tr>
                        <tr>
                            <td><code>getProtocol()</code></td>
                            <td>Vraća 'http' ili 'https'</td>
                            <td>string</td>
                        </tr>
                        <tr>
                            <td><code>checkSSLStatus()</code></td>
                            <td>Status informacije</td>
                            <td>array</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Primjer Korištenja -->
            <h5 class="mt-4">Primjer Korištenja:</h5>
            <div class="code-block">
                <pre>&lt;?php
// Na početku login.php ili register.php
require_once('lib/https_checker.php');

// Forsiraj HTTPS (samo na produkciji, ne na localhost)
HTTPSChecker::requireHTTPSForAuth();

// Provjeri status
if (HTTPSChecker::isHTTPS()) {
    echo "Sigurna konekcija!";
}
?&gt;</pre>
            </div>
            
            <!-- SessionManager Izmjene -->
            <h2 class="mt-5 mb-4"><i class="bi bi-cookie"></i> Secure Cookies</h2>
            
            <p>SessionManager automatski detektira HTTPS i postavlja <code>secure</code> flag:</p>
            
            <div class="code-block">
                <pre>// Automatska detekcija HTTPS
$isHTTPS = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || 
           (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);

// Session cookies
session_set_cookie_params([
    'secure' => $isHTTPS,  // ✅ Automatski true na HTTPS
    'httponly' => true,
    'samesite' => 'Lax'
]);

// Remember Me cookies
setcookie('hotel_remember', $value, $expiry, '/', '', $isHTTPS, true);</pre>
            </div>
            
            <!-- Zaštićene Stranice -->
            <h2 class="mt-5 mb-4"><i class="bi bi-shield-fill-check"></i> Zaštićene Stranice</h2>
            
            <div class="row">
                <div class="col-md-6">
                    <h6>✅ Zahtijevaju HTTPS:</h6>
                    <ul class="list-group">
                        <li class="list-group-item"><i class="bi bi-check-circle text-success"></i> login.php</li>
                        <li class="list-group-item"><i class="bi bi-check-circle text-success"></i> register.php</li>
                        <li class="list-group-item"><i class="bi bi-check-circle text-success"></i> api/login.php</li>
                        <li class="list-group-item"><i class="bi bi-check-circle text-success"></i> api/register_user.php</li>
                    </ul>
                </div>
                
                <div class="col-md-6">
                    <h6>ℹ️ Opcionale (funkcioniraju i na HTTP):</h6>
                    <ul class="list-group">
                        <li class="list-group-item"><i class="bi bi-info-circle text-info"></i> index.php</li>
                        <li class="list-group-item"><i class="bi bi-info-circle text-info"></i> ssl_status.php</li>
                        <li class="list-group-item"><i class="bi bi-info-circle text-info"></i> security_sessions.php</li>
                        <li class="list-group-item"><i class="bi bi-info-circle text-info"></i> token_documentation.php</li>
                    </ul>
                </div>
            </div>
            
            <!-- Testiranje -->
            <h2 class="mt-5 mb-4"><i class="bi bi-check2-square"></i> Testiranje</h2>
            
            <div class="accordion" id="testAccordion">
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#test1">
                            <i class="bi bi-1-circle me-2"></i> Test: Automatic Redirect
                        </button>
                    </h2>
                    <div id="test1" class="accordion-collapse collapse show" data-bs-parent="#testAccordion">
                        <div class="accordion-body">
                            <ol>
                                <li>Otvori: <code>http://localhost/hotel_managment/login.php</code></li>
                                <li>Trebao bi biti automatski preusmjeren na: <code>https://localhost/hotel_managment/login.php</code></li>
                                <li>Provjeri URL bar - trebalo bi biti "https://"</li>
                            </ol>
                            <div class="alert alert-info mb-0">
                                <strong>Localhost:</strong> Redirect može biti disabled ako nemaš SSL certifikat. To je OK za development!
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#test2">
                            <i class="bi bi-2-circle me-2"></i> Test: Secure Cookies
                        </button>
                    </h2>
                    <div id="test2" class="accordion-collapse collapse" data-bs-parent="#testAccordion">
                        <div class="accordion-body">
                            <ol>
                                <li>Prijavi se preko HTTPS: <code>https://localhost/hotel_managment/login.php</code></li>
                                <li>Otvori Developer Tools (F12) → Application → Cookies</li>
                                <li>Pronađi <code>hotel_remember</code> kolačić</li>
                                <li>Provjeri da ima <strong>Secure</strong> i <strong>HttpOnly</strong> flagove ✅</li>
                            </ol>
                        </div>
                    </div>
                </div>
                
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#test3">
                            <i class="bi bi-3-circle me-2"></i> Test: HSTS Header
                        </button>
                    </h2>
                    <div id="test3" class="accordion-collapse collapse" data-bs-parent="#testAccordion">
                        <div class="accordion-body">
                            <ol>
                                <li>Otvori bilo koju stranicu na HTTPS</li>
                                <li>F12 → Network → Refresh page</li>
                                <li>Klikni na prvi request → Headers</li>
                                <li>Provjeri <strong>Response Headers</strong></li>
                                <li>Trebao bi vidjeti: <code>Strict-Transport-Security: max-age=31536000</code></li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Localhost Development -->
            <div class="alert alert-warning mt-5">
                <h5><i class="bi bi-exclamation-triangle"></i> Localhost Development</h5>
                <p>Za <strong>localhost bez SSL certifikata</strong>, aplikacija će raditi normalno ali:</p>
                <ul class="mb-0">
                    <li>⚠️ HTTPS redirect je onemogućen (automatski detektira localhost)</li>
                    <li>⚠️ Secure flag na kolačićima je false (HttpOnly je i dalje true)</li>
                    <li>⚠️ HTTPS badge će pokazati žuto upozorenje "HTTP (Dev Mode)"</li>
                    <li>✅ Sve funkcionalnosti rade normalno</li>
                </ul>
            </div>
            
            <!-- Produkcija -->
            <div class="alert alert-success mt-4">
                <h5><i class="bi bi-cloud-check"></i> Produkcijski Deployment</h5>
                <p><strong>Za produkciju</strong> obavezno:</p>
                <ol class="mb-0">
                    <li>Instaliraj SSL certifikat (Let's Encrypt, Cloudflare, itd.)</li>
                    <li>Testiranje: <a href="https://www.ssllabs.com/ssltest/" target="_blank">SSL Labs Test</a></li>
                    <li>Provjeri security headers: <a href="https://securityheaders.com/" target="_blank">Security Headers</a></li>
                    <li>Aktiviraj HSTS preload: <a href="https://hstspreload.org/" target="_blank">HSTS Preload</a></li>
                </ol>
            </div>
            
            <!-- Kreirana Datoteke -->
            <h2 class="mt-5 mb-4"><i class="bi bi-folder-fill"></i> Kreirane Datoteke</h2>
            
            <ul class="list-group">
                <li class="list-group-item">
                    <i class="bi bi-file-earmark-code text-primary"></i> 
                    <strong>.htaccess</strong> - Apache redirect + security headers
                </li>
                <li class="list-group-item">
                    <i class="bi bi-file-earmark-code text-primary"></i> 
                    <strong>lib/https_checker.php</strong> - HTTPS detection & enforcement
                </li>
                <li class="list-group-item">
                    <i class="bi bi-file-earmark-code text-primary"></i> 
                    <strong>ssl_status.php</strong> - SSL status dashboard
                </li>
                <li class="list-group-item">
                    <i class="bi bi-file-earmark-code text-primary"></i> 
                    <strong>components/https_badge.php</strong> - Visual HTTPS indicator
                </li>
                <li class="list-group-item">
                    <i class="bi bi-file-earmark-text text-secondary"></i> 
                    <strong>HTTPS_SETUP_GUIDE.md</strong> - XAMPP SSL setup instructions
                </li>
            </ul>
            
            <!-- Test Links -->
            <div class="text-center mt-5">
                <h5 class="mb-3">Pristupi Stranicama</h5>
                <a href="ssl_status.php" class="btn btn-primary me-2">
                    <i class="bi bi-shield-check"></i> SSL Status
                </a>
                <a href="login.php" class="btn btn-success me-2">
                    <i class="bi bi-box-arrow-in-right"></i> Login (HTTPS)
                </a>
                <a href="register.php" class="btn btn-warning me-2">
                    <i class="bi bi-person-plus"></i> Register (HTTPS)
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
