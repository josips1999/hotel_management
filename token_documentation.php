<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Token-Based Remember Me - Dokumentacija</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 50px 20px;
        }
        .doc-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 0;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .doc-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }
        .doc-body {
            padding: 40px;
        }
        .feature-card {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        .feature-card:hover {
            border-color: #667eea;
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.2);
            transform: translateY(-5px);
        }
        .code-block {
            background: #2d3436;
            color: #dfe6e9;
            padding: 20px;
            border-radius: 8px;
            overflow-x: auto;
            margin: 15px 0;
            font-family: 'Courier New', monospace;
            font-size: 14px;
        }
        .badge-custom {
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 600;
            display: inline-block;
            margin: 5px;
        }
        .security-badge {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
        }
        .warning-badge {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }
        .info-badge {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
        }
        .comparison-table {
            margin: 30px 0;
        }
        .old-impl {
            background: #ffe8e8;
        }
        .new-impl {
            background: #e8ffe8;
        }
        .flow-diagram {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 10px;
            margin: 20px 0;
        }
        .step {
            background: white;
            border-left: 4px solid #667eea;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="doc-container">
        <div class="doc-header">
            <h1><i class="bi bi-shield-lock-fill"></i> Token-Based "Remember Me" Funkcionalnost</h1>
            <p class="lead mb-0">Sigurna autentifikacija s nasumično generiranim tokenima i pohranom u bazu podataka</p>
        </div>
        
        <div class="doc-body">
            <!-- Glavne karakteristike -->
            <div class="alert alert-success">
                <h4><i class="bi bi-check-circle-fill"></i> Što je implementirano?</h4>
                <p class="mb-0">
                    Kompletan <strong>token-based "Remember Me" sistem</strong> koji koristi 
                    <strong>split-token pristup</strong> (selector + validator), pohranu hashiranih tokena u bazu podataka, 
                    i <strong>NIKADA ne pohranjuje lozinku</strong> u kolačić. 
                    Vremenski period je <strong>potpuno podesiv</strong> u postavkama sustava.
                </p>
            </div>
            
            <!-- Sigurnosni Princip -->
            <h2 class="mt-5 mb-4"><i class="bi bi-lock-fill"></i> Sigurnosni Princip: Split-Token Approach</h2>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="feature-card">
                        <h5><i class="bi bi-key text-primary"></i> Selector (Javni)</h5>
                        <p><strong>32 hex znaka</strong> (16 bytes random)</p>
                        <ul class="mb-0">
                            <li>Pohranjuje se <strong>plain text</strong> u bazu</li>
                            <li>Omogućava brzo pronalaženje tokena</li>
                            <li>Pohranjuje se u kolačić</li>
                        </ul>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="feature-card">
                        <h5><i class="bi bi-shield-check text-success"></i> Validator (Tajni)</h5>
                        <p><strong>64 hex znaka</strong> (32 bytes random)</p>
                        <ul class="mb-0">
                            <li>Pohranjuje se <strong>hashiran</strong> u bazu (BCrypt)</li>
                            <li>Plain validator samo u kolačiću</li>
                            <li>Verificira se s password_verify()</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Flow Diagram -->
            <div class="flow-diagram mt-4">
                <h5 class="text-center mb-4">Token Lifecycle</h5>
                
                <div class="step">
                    <h6><i class="bi bi-1-circle-fill text-primary"></i> LOGIN (Remember Me = true)</h6>
                    <div class="code-block">
                        <pre>SessionManager->login($userId, $username, $email, $rememberMe = true)
├─ Generiraj selector: bin2hex(random_bytes(16)) → "a3f5e..." (32 chars)
├─ Generiraj validator: bin2hex(random_bytes(32)) → "9d2c1..." (64 chars)
├─ Hash validator: password_hash($validator, PASSWORD_DEFAULT)
├─ INSERT INTO remember_tokens (selector, hashed_validator, expires_at)
└─ setcookie('hotel_remember', 'selector:validator', time() + 30days)</pre>
                    </div>
                </div>
                
                <div class="step">
                    <h6><i class="bi bi-2-circle-fill text-success"></i> AUTO-LOGIN (Browser Reopen)</h6>
                    <div class="code-block">
                        <pre>SessionManager->checkRememberMe()
├─ Pročitaj kolačić: $_COOKIE['hotel_remember']
├─ Parsaj: explode(':', cookie) → [$selector, $validator]
├─ SELECT FROM remember_tokens WHERE selector = $selector
├─ Verificiraj: password_verify($validator, $hashed_validator)
└─ Ako OK → login($userId, $username, $email, false)</pre>
                    </div>
                </div>
                
                <div class="step">
                    <h6><i class="bi bi-3-circle-fill text-danger"></i> LOGOUT (Destroy)</h6>
                    <div class="code-block">
                        <pre>SessionManager->logout()
├─ DELETE FROM remember_tokens WHERE selector = $selector
├─ setcookie('hotel_remember', '', time() - 3600)
└─ session_destroy()</pre>
                    </div>
                </div>
            </div>
            
            <!-- Zašto je sigurno? -->
            <h2 class="mt-5 mb-4"><i class="bi bi-shield-check"></i> Zašto je ovo sigurno?</h2>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="card border-success h-100">
                        <div class="card-body">
                            <h6 class="card-title text-success">
                                <i class="bi bi-check-circle-fill"></i> Database Compromise
                            </h6>
                            <p class="card-text">
                                Čak i ako napadač ukrade cijelu bazu podataka, ne može kreirati valjan kolačić 
                                jer <strong>nema plain validator</strong> (pohranjeno samo hashirano).
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <div class="card border-success h-100">
                        <div class="card-body">
                            <h6 class="card-title text-success">
                                <i class="bi bi-check-circle-fill"></i> Timing Attacks
                            </h6>
                            <p class="card-text">
                                Koristi <code>password_verify()</code> koja je otporna na timing napade 
                                jer koristi timing-safe usporedbu.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <div class="card border-success h-100">
                        <div class="card-body">
                            <h6 class="card-title text-success">
                                <i class="bi bi-check-circle-fill"></i> Token Revocation
                            </h6>
                            <p class="card-text">
                                Svaki token se može <strong>individualno opozvati</strong> iz baze. 
                                Korisnik može vidjeti sve uređaje i opozvati pristup.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <div class="card border-success h-100">
                        <div class="card-body">
                            <h6 class="card-title text-success">
                                <i class="bi bi-check-circle-fill"></i> No Password Storage
                            </h6>
                            <p class="card-text">
                                Lozinka se <strong>NIKADA</strong> ne pohranjuje nigdje osim u 
                                <code>users.password</code> tablici (hashirana BCrypt).
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Struktura Baze -->
            <h2 class="mt-5 mb-4"><i class="bi bi-database-fill"></i> Struktura Baze: remember_tokens</h2>
            
            <div class="code-block">
                <pre>CREATE TABLE remember_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    selector VARCHAR(64) NOT NULL UNIQUE,       -- Javni identifikator (plain)
    hashed_validator VARCHAR(255) NOT NULL,     -- Tajni token (hashiran)
    expires_at DATETIME NOT NULL,               -- Datum isteka (konfigurabilan)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_used_at TIMESTAMP NULL,                -- Tracking posljednjeg korištenja
    ip_address VARCHAR(45) NULL,                -- IP adresa za security audit
    user_agent VARCHAR(255) NULL,               -- Browser info
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_selector (selector),              -- Brza pretraga
    INDEX idx_user_id (user_id),
    INDEX idx_expires_at (expires_at)
);</pre>
            </div>
            
            <div class="alert alert-info mt-3">
                <strong><i class="bi bi-info-circle"></i> Instalacija:</strong><br>
                Pokreni <code>database/install_remember_tokens.sql</code> u phpMyAdmin ili MySQL CLI.
            </div>
            
            <!-- Konfiguracija -->
            <h2 class="mt-5 mb-4"><i class="bi bi-gear-fill"></i> Konfiguracija (lib/config.php)</h2>
            
            <div class="code-block">
                <pre>// Remember Me Postavke
define('REMEMBER_ME_DURATION_DAYS', 30);      // ⚙️ PODESIVO: Trajanje tokena
define('REMEMBER_ME_COOKIE_NAME', 'hotel_remember');
define('SESSION_TIMEOUT_MINUTES', 30);

// Token Sigurnost
define('TOKEN_SELECTOR_BYTES', 16);            // 16 bytes = 32 hex chars
define('TOKEN_VALIDATOR_BYTES', 32);           // 32 bytes = 64 hex chars</pre>
            </div>
            
            <div class="alert alert-success">
                <h6><i class="bi bi-check-circle"></i> Kako promijeniti trajanje?</h6>
                <p class="mb-0">
                    Jednostavno promijeni <code>REMEMBER_ME_DURATION_DAYS</code> u <code>lib/config.php</code>:
                </p>
                <div class="code-block mt-2">
                    <pre>define('REMEMBER_ME_DURATION_DAYS', 7);   // 7 dana
define('REMEMBER_ME_DURATION_DAYS', 60);  // 2 mjeseca
define('REMEMBER_ME_DURATION_DAYS', 90);  // 3 mjeseca</pre>
                </div>
            </div>
            
            <!-- Usporedba: Stara vs Nova -->
            <h2 class="mt-5 mb-4"><i class="bi bi-arrow-left-right"></i> Usporedba: Stara vs Nova Implementacija</h2>
            
            <table class="table table-bordered comparison-table">
                <thead class="table-dark">
                    <tr>
                        <th>Feature</th>
                        <th>Stara Implementacija</th>
                        <th>Nova Implementacija</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Pohrana tokena</strong></td>
                        <td class="old-impl"><i class="bi bi-x-circle text-danger"></i> Enkriptirani kolačić (JSON)</td>
                        <td class="new-impl"><i class="bi bi-check-circle text-success"></i> Baza podataka + split-token</td>
                    </tr>
                    <tr>
                        <td><strong>Sigurnost</strong></td>
                        <td class="old-impl"><i class="bi bi-x-circle text-danger"></i> Osnovna (base64)</td>
                        <td class="new-impl"><i class="bi bi-check-circle text-success"></i> Visoka (BCrypt hashing)</td>
                    </tr>
                    <tr>
                        <td><strong>Token Revocation</strong></td>
                        <td class="old-impl"><i class="bi bi-x-circle text-danger"></i> Samo brisanje kolačića</td>
                        <td class="new-impl"><i class="bi bi-check-circle text-success"></i> Individualno iz baze</td>
                    </tr>
                    <tr>
                        <td><strong>Tracking</strong></td>
                        <td class="old-impl"><i class="bi bi-x-circle text-danger"></i> Nema</td>
                        <td class="new-impl"><i class="bi bi-check-circle text-success"></i> IP + User-Agent + last_used_at</td>
                    </tr>
                    <tr>
                        <td><strong>Vremenski period</strong></td>
                        <td class="old-impl"><i class="bi bi-x-circle text-danger"></i> Hardcoded u kodu</td>
                        <td class="new-impl"><i class="bi bi-check-circle text-success"></i> Konfigurabilan (config.php)</td>
                    </tr>
                    <tr>
                        <td><strong>Multiple Devices</strong></td>
                        <td class="old-impl"><i class="bi bi-x-circle text-danger"></i> Ne podržava</td>
                        <td class="new-impl"><i class="bi bi-check-circle text-success"></i> Podržava (svaki device = token)</td>
                    </tr>
                    <tr>
                        <td><strong>Security Dashboard</strong></td>
                        <td class="old-impl"><i class="bi bi-x-circle text-danger"></i> Nema</td>
                        <td class="new-impl"><i class="bi bi-check-circle text-success"></i> security_sessions.php</td>
                    </tr>
                    <tr>
                        <td><strong>Database Theft Protection</strong></td>
                        <td class="old-impl"><i class="bi bi-x-circle text-danger"></i> Lako kompromitirati</td>
                        <td class="new-impl"><i class="bi bi-check-circle text-success"></i> Otpornost na krađu baze</td>
                    </tr>
                </tbody>
            </table>
            
            <!-- Kreirana Datoteke -->
            <h2 class="mt-5 mb-4"><i class="bi bi-folder-fill"></i> Kreirane/Ažurirane Datoteke</h2>
            
            <div class="row">
                <div class="col-md-6">
                    <h5 class="text-success"><i class="bi bi-file-earmark-plus"></i> Nove Datoteke</h5>
                    <ul class="list-group">
                        <li class="list-group-item">
                            <i class="bi bi-file-code"></i> <code>lib/config.php</code>
                            <br><small class="text-muted">Globalne postavke sustava</small>
                        </li>
                        <li class="list-group-item">
                            <i class="bi bi-file-code"></i> <code>database/create_remember_tokens.sql</code>
                            <br><small class="text-muted">Struktura tablice</small>
                        </li>
                        <li class="list-group-item">
                            <i class="bi bi-file-code"></i> <code>database/install_remember_tokens.sql</code>
                            <br><small class="text-muted">Instalacijski SQL</small>
                        </li>
                        <li class="list-group-item">
                            <i class="bi bi-file-code"></i> <code>security_sessions.php</code>
                            <br><small class="text-muted">Security dashboard</small>
                        </li>
                        <li class="list-group-item">
                            <i class="bi bi-file-code"></i> <code>cron/clean_expired_tokens.php</code>
                            <br><small class="text-muted">Cron job za čišćenje</small>
                        </li>
                    </ul>
                </div>
                
                <div class="col-md-6">
                    <h5 class="text-primary"><i class="bi bi-file-earmark-arrow-up"></i> Ažurirane Datoteke</h5>
                    <ul class="list-group">
                        <li class="list-group-item">
                            <i class="bi bi-file-code"></i> <code>lib/SessionManager.php</code>
                            <br><small class="text-muted">Potpuna refaktoracija za token-based</small>
                        </li>
                        <li class="list-group-item">
                            <i class="bi bi-file-code"></i> <code>api/login.php</code>
                            <br><small class="text-muted">Koristi SessionManager($connection)</small>
                        </li>
                        <li class="list-group-item">
                            <i class="bi bi-file-code"></i> <code>index.php</code>
                            <br><small class="text-muted">checkRememberMe() + Sigurnost link</small>
                        </li>
                        <li class="list-group-item">
                            <i class="bi bi-file-code"></i> <code>logout.php</code>
                            <br><small class="text-muted">Briše token iz baze</small>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Test Scenariji -->
            <h2 class="mt-5 mb-4"><i class="bi bi-check2-square"></i> Test Scenariji</h2>
            
            <div class="accordion" id="testAccordion">
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#test1">
                            <i class="bi bi-1-circle me-2"></i> Test: Remember Me Funkcionalnost
                        </button>
                    </h2>
                    <div id="test1" class="accordion-collapse collapse show" data-bs-parent="#testAccordion">
                        <div class="accordion-body">
                            <ol>
                                <li>Otvori <code>register.php</code> → Registriraj se</li>
                                <li>Verifikuj email kod → <code>verify.php</code></li>
                                <li>Otvori <code>login.php</code> → Unesi credentials</li>
                                <li><strong>Označi "Zapamti me"</strong> → Klikni "Prijavi se"</li>
                                <li>Provjeri kolačić (F12 → Application → Cookies): <code>hotel_remember</code></li>
                                <li><strong>Zatvori browser potpuno</strong></li>
                                <li>Otvori ponovo: <code>http://localhost/hotel_managment/index.php</code></li>
                                <li>✅ <strong>Trebao bi biti automatski prijavljen!</strong></li>
                            </ol>
                        </div>
                    </div>
                </div>
                
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#test2">
                            <i class="bi bi-2-circle me-2"></i> Test: Multiple Devices & Revocation
                        </button>
                    </h2>
                    <div id="test2" class="accordion-collapse collapse" data-bs-parent="#testAccordion">
                        <div class="accordion-body">
                            <ol>
                                <li>Prijavi se na Chrome → Označi "Zapamti me"</li>
                                <li>Prijavi se na Firefox → Označi "Zapamti me"</li>
                                <li>Otvori <code>security_sessions.php</code></li>
                                <li>Trebao bi vidjeti <strong>2 aktivna tokena</strong></li>
                                <li>Klikni "Opozovi Pristup" na Firefox token</li>
                                <li>Zatvori i otvori Firefox → Više ne može auto-login</li>
                                <li>Chrome i dalje radi! ✅</li>
                            </ol>
                        </div>
                    </div>
                </div>
                
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#test3">
                            <i class="bi bi-3-circle me-2"></i> Test: Token Expiry
                        </button>
                    </h2>
                    <div id="test3" class="accordion-collapse collapse" data-bs-parent="#testAccordion">
                        <div class="accordion-body">
                            <ol>
                                <li>U <code>lib/config.php</code> postavi: <code>define('REMEMBER_ME_DURATION_DAYS', 1);</code></li>
                                <li>Prijavi se s "Zapamti me"</li>
                                <li>U bazi manuelno postavi: <code>UPDATE remember_tokens SET expires_at = NOW() - INTERVAL 1 DAY;</code></li>
                                <li>Zatvori browser i otvori ponovo</li>
                                <li>✅ Neće biti prijavljen (token istekao)</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Links -->
            <div class="text-center mt-5">
                <h4 class="mb-4">Pristupi Aplikaciji</h4>
                <a href="login.php" class="btn btn-primary btn-lg me-2">
                    <i class="bi bi-box-arrow-in-right"></i> Prijava
                </a>
                <a href="register.php" class="btn btn-success btn-lg me-2">
                    <i class="bi bi-person-plus"></i> Registracija
                </a>
                <a href="security_sessions.php" class="btn btn-warning btn-lg me-2">
                    <i class="bi bi-shield-lock"></i> Security Dashboard
                </a>
                <a href="index.php" class="btn btn-outline-secondary btn-lg">
                    <i class="bi bi-house"></i> Početna
                </a>
            </div>
            
            <!-- Footer -->
            <div class="alert alert-light mt-5 text-center">
                <p class="mb-0">
                    <i class="bi bi-github"></i> Pročitaj detaljnu dokumentaciju: 
                    <a href="REMEMBER_ME_DOCUMENTATION.md" target="_blank">REMEMBER_ME_DOCUMENTATION.md</a>
                </p>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
