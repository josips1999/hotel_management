<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verifikacija - Demo</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        body {
            background: #f5f5f5;
            padding: 50px 20px;
        }
        
        .demo-container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .demo-header {
            border-bottom: 3px solid #667eea;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .feature-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s;
        }
        
        .feature-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        
        .flow-diagram {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 8px;
            margin: 30px 0;
        }
        
        .step {
            display: flex;
            align-items: center;
            margin: 15px 0;
        }
        
        .step-number {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 15px;
        }
        
        .code-snippet {
            background: #282c34;
            color: #abb2bf;
            padding: 20px;
            border-radius: 8px;
            overflow-x: auto;
            margin: 15px 0;
        }
        
        .badge-custom {
            background: #667eea;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="demo-container">
        <div class="demo-header">
            <h1><i class="bi bi-envelope-check-fill"></i> Email Verifikacija - Dokumentacija</h1>
            <p class="lead">Potvrda registracije 6-znamenkastim kodom poslana na email</p>
        </div>
        
        <!-- Description -->
        <div class="alert alert-info">
            <h5><i class="bi bi-info-circle"></i> Što je implementirano?</h5>
            <p class="mb-0">
                Kompletan sustav email verifikacije gdje korisnik nakon registracije dobiva <strong>6-znamenkasti kod</strong> 
                na svoj email. Kod mora unijeti unutar <strong>15 minuta</strong> kako bi aktivirao račun. 
                Sustav uključuje rate limiting, automatsko slanje emailova, i mogućnost ponovnog slanja koda.
            </p>
        </div>
        
        <!-- Features Grid -->
        <h3 class="mt-4 mb-3">🚀 Funkcionalnosti</h3>
        <div class="row">
            <div class="col-md-6">
                <div class="feature-card">
                    <h5><i class="bi bi-shield-check text-success"></i> Sigurnosne mjere</h5>
                    <ul>
                        <li>6-znamenkasti numerički kod</li>
                        <li>Kod vrijedi 15 minuta</li>
                        <li>Jedan kod po računu</li>
                        <li>Rate limiting (1 min između zahtjeva)</li>
                    </ul>
                </div>
            </div>
            <div class="col-md-6">
                <div class="feature-card">
                    <h5><i class="bi bi-envelope-fill text-primary"></i> Email funkcionalnosti</h5>
                    <ul>
                        <li>HTML formatiran email</li>
                        <li>Responsive dizajn</li>
                        <li>Brand styling (gradient)</li>
                        <li>Email logging za debug</li>
                    </ul>
                </div>
            </div>
            <div class="col-md-6">
                <div class="feature-card">
                    <h5><i class="bi bi-ui-checks text-warning"></i> UX optimizacije</h5>
                    <ul>
                        <li>Auto-focus na sljedeći digit</li>
                        <li>Paste support (kopiranje cijelog koda)</li>
                        <li>Backspace navigacija</li>
                        <li>Auto-submit kada se unesu svi digiti</li>
                    </ul>
                </div>
            </div>
            <div class="col-md-6">
                <div class="feature-card">
                    <h5><i class="bi bi-arrow-clockwise text-info"></i> Resend funkcionalnost</h5>
                    <ul>
                        <li>Ponovno slanje koda</li>
                        <li>60s cooldown timer</li>
                        <li>Generiranje novog koda</li>
                        <li>Visual countdown</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Flow Diagram -->
        <h3 class="mt-5 mb-3">📊 Proces verifikacije</h3>
        <div class="flow-diagram">
            <div class="step">
                <div class="step-number">1</div>
                <div>
                    <strong>Registracija</strong><br>
                    Korisnik ispuni formu na register.php
                </div>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <div>
                    <strong>Generiranje koda</strong><br>
                    Backend generira 6-znamenkasti kod i sprema u bazu
                </div>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <div>
                    <strong>Slanje emaila</strong><br>
                    EmailService šalje HTML email s kodom na korisnikov email
                </div>
            </div>
            <div class="step">
                <div class="step-number">4</div>
                <div>
                    <strong>Preusmjeravanje</strong><br>
                    Korisnik se automatski preusmjerava na verify.php
                </div>
            </div>
            <div class="step">
                <div class="step-number">5</div>
                <div>
                    <strong>Unos koda</strong><br>
                    Korisnik unosi 6 znamenki u odvojena polja
                </div>
            </div>
            <div class="step">
                <div class="step-number">6</div>
                <div>
                    <strong>Verifikacija</strong><br>
                    API provjerava kod i aktivira račun ako je ispravan
                </div>
            </div>
            <div class="step">
                <div class="step-number">7</div>
                <div>
                    <strong>Prijava</strong><br>
                    Korisnik je automatski prijavljen i preusmjeren na index.php
                </div>
            </div>
        </div>
        
        <!-- Database Structure -->
        <h3 class="mt-5 mb-3">🗄️ Struktura baze podataka</h3>
        <div class="code-snippet">
            <pre><code>CREATE TABLE users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    <span style="color: #61afef">verification_code VARCHAR(6) DEFAULT NULL</span>,
    <span style="color: #61afef">is_verified TINYINT(1) DEFAULT 0</span>,
    <span style="color: #61afef">verification_expires DATETIME DEFAULT NULL</span>,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);</code></pre>
        </div>
        
        <!-- API Endpoints -->
        <h3 class="mt-5 mb-3">🔌 API Endpoints</h3>
        
        <div class="accordion" id="apiAccordion">
            <!-- Register User -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#api1">
                        <span class="badge-custom me-2">POST</span> api/register_user.php
                    </button>
                </h2>
                <div id="api1" class="accordion-collapse collapse show" data-bs-parent="#apiAccordion">
                    <div class="accordion-body">
                        <p><strong>Opis:</strong> Registracija novog korisnika i slanje verifikacijskog emaila</p>
                        <p><strong>Parameters:</strong></p>
                        <ul>
                            <li><code>username</code> - Korisničko ime (3-30 znakova)</li>
                            <li><code>email</code> - Email adresa</li>
                            <li><code>password</code> - Lozinka (min 6 znakova)</li>
                            <li><code>confirmPassword</code> - Potvrda lozinke</li>
                        </ul>
                        <p><strong>Response:</strong></p>
                        <div class="code-snippet">
                            <pre><code>{
    "success": true,
    "message": "Registracija uspješna! Provjerite email...",
    "user_id": 1,
    "email": "user@example.com",
    "requires_verification": true
}</code></pre>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Verify Code -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#api2">
                        <span class="badge-custom me-2">POST</span> api/verify_code.php
                    </button>
                </h2>
                <div id="api2" class="accordion-collapse collapse" data-bs-parent="#apiAccordion">
                    <div class="accordion-body">
                        <p><strong>Opis:</strong> Verifikacija 6-znamenkastog koda i aktivacija računa</p>
                        <p><strong>Parameters:</strong></p>
                        <ul>
                            <li><code>email</code> - Email adresa korisnika</li>
                            <li><code>code</code> - 6-znamenkasti kod</li>
                        </ul>
                        <p><strong>Response (success):</strong></p>
                        <div class="code-snippet">
                            <pre><code>{
    "success": true,
    "message": "Račun uspješno verificiran!",
    "username": "ivan_horvat"
}</code></pre>
                        </div>
                        <p><strong>Response (error - expired):</strong></p>
                        <div class="code-snippet">
                            <pre><code>{
    "success": false,
    "message": "Verifikacijski kod je istekao",
    "expired": true
}</code></pre>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Resend Code -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#api3">
                        <span class="badge-custom me-2">POST</span> api/resend_code.php
                    </button>
                </h2>
                <div id="api3" class="accordion-collapse collapse" data-bs-parent="#apiAccordion">
                    <div class="accordion-body">
                        <p><strong>Opis:</strong> Ponovno slanje verifikacijskog koda</p>
                        <p><strong>Parameters:</strong></p>
                        <ul>
                            <li><code>email</code> - Email adresa korisnika</li>
                        </ul>
                        <p><strong>Rate Limiting:</strong> Max 1 zahtjev po minuti</p>
                        <p><strong>Response:</strong></p>
                        <div class="code-snippet">
                            <pre><code>{
    "success": true,
    "message": "Novi verifikacijski kod je poslan!",
    "expires_in": "15 minuta"
}</code></pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Files Created -->
        <h3 class="mt-5 mb-3">📁 Kreirane datoteke</h3>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Datoteka</th>
                        <th>Opis</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>lib/EmailService.php</code></td>
                        <td>Klasa za slanje verifikacijskih emailova</td>
                    </tr>
                    <tr>
                        <td><code>api/register_user.php</code></td>
                        <td>Ažurirano - generira kod i šalje email</td>
                    </tr>
                    <tr>
                        <td><code>api/verify_code.php</code></td>
                        <td>API za provjeru koda i aktivaciju računa</td>
                    </tr>
                    <tr>
                        <td><code>api/resend_code.php</code></td>
                        <td>API za ponovno slanje koda</td>
                    </tr>
                    <tr>
                        <td><code>verify.php</code></td>
                        <td>Stranica za unos verifikacijskog koda</td>
                    </tr>
                    <tr>
                        <td><code>register.php</code></td>
                        <td>Ažurirano - AJAX submit i redirect na verify.php</td>
                    </tr>
                    <tr>
                        <td><code>instalacija.php</code></td>
                        <td>Ažurirano - dodana polja za verifikaciju</td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Email Configuration -->
        <h3 class="mt-5 mb-3">📧 Konfiguracija emaila</h3>
        <div class="alert alert-warning">
            <h5><i class="bi bi-exclamation-triangle"></i> Važna napomena o slanju emailova</h5>
            <p>Trenutna implementacija koristi PHP <code>mail()</code> funkciju za demo svrhe. Za <strong>produkcijsko okruženje</strong>, preporučuje se:</p>
            <ol>
                <li><strong>PHPMailer</strong> - Install via Composer: <code>composer require phpmailer/phpmailer</code></li>
                <li><strong>SMTP konfiguracija</strong> - Koristite Gmail, SendGrid, Mailgun ili sl.</li>
                <li><strong>Email logging</strong> - Trenutno se logira u <code>logs/email_log.txt</code></li>
            </ol>
            <p class="mb-0">Za lokalno testiranje bez SMTP servera, možete koristiti <strong>MailHog</strong> ili <strong>Mailtrap</strong>.</p>
        </div>
        
        <!-- Testing Instructions -->
        <h3 class="mt-5 mb-3">🧪 Kako testirati?</h3>
        <div class="alert alert-success">
            <ol>
                <li>Pokrenite <code>instalacija.php</code> da ažurirate tablicu</li>
                <li>Otvorite <code>register.php</code> i registrirajte se</li>
                <li>Bit ćete preusmjereni na <code>verify.php</code></li>
                <li>Provjerite email (ili logs/email_log.txt ako mail() ne radi)</li>
                <li>Unesite 6-znamenkasti kod</li>
                <li>Račun će biti aktiviran i prijavljeni ste automatski</li>
            </ol>
        </div>
        
        <!-- Security Notes -->
        <h3 class="mt-5 mb-3">🔒 Sigurnosne napomene</h3>
        <ul>
            <li>✓ Kod vrijedi samo 15 minuta (sprječava replay attacks)</li>
            <li>✓ Rate limiting na resend (1 zahtjev/min)</li>
            <li>✓ Kod se briše nakon uspješne verifikacije</li>
            <li>✓ is_verified flag sprječava login neverificiranih korisnika</li>
            <li>✓ Prepared statements u svim upitima</li>
            <li>✓ Email adresa se htmlspecialchars() pri prikazu</li>
        </ul>
        
        <!-- Back Links -->
        <div class="text-center mt-5">
            <a href="register.php" class="btn btn-primary me-2">
                <i class="bi bi-person-plus"></i> Registracija
            </a>
            <a href="ajax_demo.php" class="btn btn-secondary me-2">
                <i class="bi bi-code-square"></i> AJAX Demo
            </a>
            <a href="index.php" class="btn btn-outline-secondary">
                <i class="bi bi-house"></i> Početna
            </a>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
