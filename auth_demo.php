<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>reCAPTCHA & Autentifikacija - Dokumentacija</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        body {
            background: #f5f5f5;
            padding: 50px 20px;
        }
        
        .demo-container {
            max-width: 1200px;
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
        
        .feature-box {
            border: 2px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            background: #f8f9fa;
        }
        
        .code-block {
            background: #282c34;
            color: #abb2bf;
            padding: 20px;
            border-radius: 8px;
            overflow-x: auto;
            margin: 15px 0;
            font-family: 'Courier New', monospace;
        }
        
        .badge-feature {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 14px;
            display: inline-block;
            margin: 5px;
        }
    </style>
</head>
<body>
    <div class="demo-container">
        <div class="demo-header">
            <h1><i class="bi bi-shield-check"></i> reCAPTCHA & Autentifikacija - Dokumentacija</h1>
            <p class="lead">Kompletan sustav prijave/odjave sa sesijama, kolačićima i reCAPTCHA zaštitom</p>
        </div>
        
        <!-- Overview -->
        <div class="alert alert-info">
            <h4><i class="bi bi-info-circle"></i> Što je implementirano?</h4>
            <p class="mb-0">
                Implementiran je <strong>kompletan sustav autentifikacije</strong> koji uključuje prijavu, odjavu, 
                upravljanje sesijama, "remember me" kolačiće, i <strong>Google reCAPTCHA v2</strong> zaštitu od botova.
            </p>
        </div>
        
        <!-- Main Features -->
        <h2 class="mt-5 mb-4">🎯 Glavne funkcionalnosti</h2>
        
        <div class="row">
            <div class="col-md-6">
                <div class="feature-box">
                    <h4><i class="bi bi-robot text-primary"></i> Google reCAPTCHA v2</h4>
                    <ul>
                        <li><strong>Checkbox reCAPTCHA</strong> - "I'm not a robot"</li>
                        <li><strong>Client-side validacija</strong> - JavaScript provjera</li>
                        <li><strong>Server-side verifikacija</strong> - PHP Google API call</li>
                        <li><strong>Rate limiting</strong> - Sprječava spam</li>
                        <li><strong>Test keys</strong> - Uključeni za development</li>
                    </ul>
                    <span class="badge-feature">✓ Implementirano</span>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="feature-box">
                    <h4><i class="bi bi-box-arrow-in-right text-success"></i> Prijava (Login)</h4>
                    <ul>
                        <li><strong>Username ili Email</strong> - Fleksibilna prijava</li>
                        <li><strong>Password verification</strong> - BCrypt hashing</li>
                        <li><strong>Remember Me</strong> - 30-dnevni kolačić</li>
                        <li><strong>Session management</strong> - Sigurne sesije</li>
                        <li><strong>Auto-login</strong> - Iz kolačića</li>
                    </ul>
                    <span class="badge-feature">✓ Implementirano</span>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="feature-box">
                    <h4><i class="bi bi-clock-history text-warning"></i> Session Management</h4>
                    <ul>
                        <li><strong>Session security</strong> - HttpOnly, SameSite</li>
                        <li><strong>Timeout</strong> - 30 min neaktivnosti</li>
                        <li><strong>Session fixation prevention</strong> - Regenerate ID</li>
                        <li><strong>Session hijacking protection</strong> - IP/UA check</li>
                        <li><strong>Auto-restore</strong> - Iz remember me</li>
                    </ul>
                    <span class="badge-feature">✓ Implementirano</span>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="feature-box">
                    <h4><i class="bi bi-box-arrow-right text-danger"></i> Odjava (Logout)</h4>
                    <ul>
                        <li><strong>Session destroy</strong> - Potpuno uništavanje</li>
                        <li><strong>Cookie deletion</strong> - Brisanje remember me</li>
                        <li><strong>Redirect</strong> - Na login stranicu</li>
                        <li><strong>Success message</strong> - Potvrda odjave</li>
                        <li><strong>Security cleanup</strong> - Clear $_SESSION</li>
                    </ul>
                    <span class="badge-feature">✓ Implementirano</span>
                </div>
            </div>
        </div>
        
        <!-- Architecture -->
        <h2 class="mt-5 mb-4">🏗️ Arhitektura sustava</h2>
        
        <div class="row">
            <div class="col-md-4 mb-3">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-file-earmark-code"></i> Frontend</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled">
                            <li>✓ <strong>login.php</strong> - Forma za prijavu</li>
                            <li>✓ <strong>register.php</strong> - Forma za registraciju</li>
                            <li>✓ <strong>logout.php</strong> - Odjava</li>
                            <li>✓ <strong>reCAPTCHA widget</strong> - Checkbox</li>
                            <li>✓ <strong>Bootstrap 5</strong> - UI dizajn</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-3">
                <div class="card h-100">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-server"></i> Backend</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled">
                            <li>✓ <strong>api/login.php</strong> - Login endpoint</li>
                            <li>✓ <strong>SessionManager.php</strong> - Session logic</li>
                            <li>✓ <strong>recaptcha_config.php</strong> - reCAPTCHA</li>
                            <li>✓ <strong>register_user.php</strong> - Registracija</li>
                            <li>✓ <strong>MySQL database</strong> - Users table</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-3">
                <div class="card h-100">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="bi bi-shield-lock"></i> Security</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled">
                            <li>✓ <strong>BCrypt hashing</strong> - Passwords</li>
                            <li>✓ <strong>Prepared statements</strong> - SQL</li>
                            <li>✓ <strong>CSRF protection</strong> - SameSite</li>
                            <li>✓ <strong>XSS prevention</strong> - htmlspecialchars</li>
                            <li>✓ <strong>Session security</strong> - HttpOnly</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- reCAPTCHA Integration -->
        <h2 class="mt-5 mb-4">🤖 Google reCAPTCHA v2 Integracija</h2>
        
        <h5>1. Client-side (HTML)</h5>
        <div class="code-block">
            <pre><code><span style="color: #7f848e">&lt;!-- Load reCAPTCHA API --&gt;</span>
<span style="color: #e06c75">&lt;script</span> <span style="color: #d19a66">src</span>=<span style="color: #98c379">"https://www.google.com/recaptcha/api.js"</span> <span style="color: #d19a66">async defer</span><span style="color: #e06c75">&gt;&lt;/script&gt;</span>

<span style="color: #7f848e">&lt;!-- reCAPTCHA widget --&gt;</span>
<span style="color: #e06c75">&lt;div</span> <span style="color: #d19a66">class</span>=<span style="color: #98c379">"g-recaptcha"</span> <span style="color: #d19a66">data-sitekey</span>=<span style="color: #98c379">"YOUR_SITE_KEY"</span><span style="color: #e06c75">&gt;&lt;/div&gt;</span></code></pre>
        </div>
        
        <h5 class="mt-4">2. JavaScript validation</h5>
        <div class="code-block">
            <pre><code><span style="color: #c678dd">const</span> <span style="color: #e06c75">recaptchaResponse</span> = <span style="color: #e5c07b">grecaptcha</span>.<span style="color: #61afef">getResponse</span>();

<span style="color: #c678dd">if</span> (!recaptchaResponse) {
    <span style="color: #7f848e">// Show error - user didn't complete reCAPTCHA</span>
    <span style="color: #61afef">alert</span>(<span style="color: #98c379">'Molimo potvrdite da niste robot'</span>);
    <span style="color: #c678dd">return</span> <span style="color: #d19a66">false</span>;
}</code></pre>
        </div>
        
        <h5 class="mt-4">3. Server-side verification (PHP)</h5>
        <div class="code-block">
            <pre><code><span style="color: #c678dd">function</span> <span style="color: #61afef">verifyRecaptchaCurl</span>(<span style="color: #e06c75">$response</span>, <span style="color: #e06c75">$remoteIp</span>) {
    <span style="color: #e5c07b">$postData</span> = [
        <span style="color: #98c379">'secret'</span> => <span style="color: #61afef">RECAPTCHA_SECRET_KEY</span>,
        <span style="color: #98c379">'response'</span> => <span style="color: #e5c07b">$response</span>,
        <span style="color: #98c379">'remoteip'</span> => <span style="color: #e5c07b">$remoteIp</span>
    ];
    
    <span style="color: #7f848e">// POST to Google API</span>
    <span style="color: #e5c07b">$ch</span> = <span style="color: #61afef">curl_init</span>(<span style="color: #98c379">'https://www.google.com/recaptcha/api/siteverify'</span>);
    <span style="color: #61afef">curl_setopt</span>(<span style="color: #e5c07b">$ch</span>, <span style="color: #61afef">CURLOPT_POSTFIELDS</span>, <span style="color: #61afef">http_build_query</span>(<span style="color: #e5c07b">$postData</span>));
    <span style="color: #e5c07b">$verify</span> = <span style="color: #61afef">curl_exec</span>(<span style="color: #e5c07b">$ch</span>);
    
    <span style="color: #e5c07b">$result</span> = <span style="color: #61afef">json_decode</span>(<span style="color: #e5c07b">$verify</span>);
    <span style="color: #c678dd">return</span> <span style="color: #e5c07b">$result</span>-><span style="color: #e06c75">success</span>;
}</code></pre>
        </div>
        
        <!-- Session Management Flow -->
        <h2 class="mt-5 mb-4">🔄 Session Management Flow</h2>
        
        <div class="alert alert-secondary">
            <h5>Login Process:</h5>
            <ol>
                <li><strong>User submits login form</strong> → username/email + password + reCAPTCHA</li>
                <li><strong>JavaScript validates</strong> → Checks all fields including reCAPTCHA response</li>
                <li><strong>AJAX sends to api/login.php</strong> → POST request with credentials</li>
                <li><strong>Server verifies reCAPTCHA</strong> → Google API validation</li>
                <li><strong>Database lookup</strong> → Find user by username/email</li>
                <li><strong>Password verification</strong> → password_verify() with BCrypt hash</li>
                <li><strong>Session creation</strong> → SessionManager->login()</li>
                <li><strong>Cookie creation (optional)</strong> → If "Remember Me" checked</li>
                <li><strong>Redirect to index.php</strong> → User is logged in</li>
            </ol>
        </div>
        
        <div class="alert alert-secondary mt-3">
            <h5>Session Restoration (Remember Me):</h5>
            <ol>
                <li><strong>User visits site</strong> → No active session</li>
                <li><strong>Check cookie</strong> → SessionManager->checkRememberMe()</li>
                <li><strong>Decrypt cookie data</strong> → Extract user_id and token</li>
                <li><strong>Verify expiry</strong> → Check if cookie expired (30 days)</li>
                <li><strong>Database lookup</strong> → Get user by ID</li>
                <li><strong>Restore session</strong> → Auto-login user</li>
                <li><strong>User is logged in</strong> → Without typing credentials</li>
            </ol>
        </div>
        
        <!-- Code Examples -->
        <h2 class="mt-5 mb-4">💻 Primjeri korištenja</h2>
        
        <h5>SessionManager - Login</h5>
        <div class="code-block">
            <pre><code><span style="color: #c678dd">require_once</span>(<span style="color: #98c379">'lib/SessionManager.php'</span>);

<span style="color: #e5c07b">$sessionManager</span> = <span style="color: #c678dd">new</span> <span style="color: #61afef">SessionManager</span>();

<span style="color: #7f848e">// Login user</span>
<span style="color: #e5c07b">$sessionManager</span>-><span style="color: #61afef">login</span>(
    <span style="color: #e5c07b">$userId</span>,        <span style="color: #7f848e">// int</span>
    <span style="color: #e5c07b">$username</span>,      <span style="color: #7f848e">// string</span>
    <span style="color: #e5c07b">$email</span>,         <span style="color: #7f848e">// string</span>
    <span style="color: #d19a66">true</span>           <span style="color: #7f848e">// bool - remember me</span>
);</code></pre>
        </div>
        
        <h5 class="mt-4">SessionManager - Check Login</h5>
        <div class="code-block">
            <pre><code><span style="color: #7f848e">// Check if user is logged in</span>
<span style="color: #c678dd">if</span> (<span style="color: #e5c07b">$sessionManager</span>-><span style="color: #61afef">isLoggedIn</span>()) {
    <span style="color: #e5c07b">$username</span> = <span style="color: #e5c07b">$sessionManager</span>-><span style="color: #61afef">getUsername</span>();
    <span style="color: #c678dd">echo</span> <span style="color: #98c379">"Dobrodošao, </span><span style="color: #e5c07b">$username</span><span style="color: #98c379">!"</span>;
} <span style="color: #c678dd">else</span> {
    <span style="color: #c678dd">header</span>(<span style="color: #98c379">'Location: login.php'</span>);
}</code></pre>
        </div>
        
        <h5 class="mt-4">SessionManager - Logout</h5>
        <div class="code-block">
            <pre><code><span style="color: #7f848e">// Logout and destroy everything</span>
<span style="color: #e5c07b">$sessionManager</span>-><span style="color: #61afef">logout</span>();

<span style="color: #7f848e">// Redirect to login</span>
<span style="color: #c678dd">header</span>(<span style="color: #98c379">'Location: login.php?message=Odjavljeni'</span>);</code></pre>
        </div>
        
        <!-- Security Features -->
        <h2 class="mt-5 mb-4">🔒 Sigurnosne mjere</h2>
        
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Feature</th>
                        <th>Implementacija</th>
                        <th>Zaštita od</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>reCAPTCHA v2</strong></td>
                        <td>Google Checkbox + Server verification</td>
                        <td>Bots, automated attacks, spam</td>
                    </tr>
                    <tr>
                        <td><strong>BCrypt Password Hashing</strong></td>
                        <td>password_hash() + password_verify()</td>
                        <td>Rainbow tables, dictionary attacks</td>
                    </tr>
                    <tr>
                        <td><strong>Prepared Statements</strong></td>
                        <td>MySQLi prepared queries</td>
                        <td>SQL injection</td>
                    </tr>
                    <tr>
                        <td><strong>HttpOnly Cookies</strong></td>
                        <td>session_set_cookie_params(['httponly' => true])</td>
                        <td>XSS attacks accessing cookies</td>
                    </tr>
                    <tr>
                        <td><strong>SameSite Cookies</strong></td>
                        <td>session_set_cookie_params(['samesite' => 'Lax'])</td>
                        <td>CSRF attacks</td>
                    </tr>
                    <tr>
                        <td><strong>Session Regeneration</strong></td>
                        <td>session_regenerate_id(true) on login</td>
                        <td>Session fixation attacks</td>
                    </tr>
                    <tr>
                        <td><strong>IP/User Agent Check</strong></td>
                        <td>Validate $_SERVER['REMOTE_ADDR'] & HTTP_USER_AGENT</td>
                        <td>Session hijacking</td>
                    </tr>
                    <tr>
                        <td><strong>Session Timeout</strong></td>
                        <td>30 minutes inactivity</td>
                        <td>Abandoned sessions</td>
                    </tr>
                    <tr>
                        <td><strong>Email Verification</strong></td>
                        <td>6-digit code sent to email</td>
                        <td>Fake registrations</td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Testing -->
        <h2 class="mt-5 mb-4">🧪 Testiranje</h2>
        
        <div class="alert alert-success">
            <h5><i class="bi bi-check-circle"></i> Kako testirati?</h5>
            <ol>
                <li><strong>Registracija:</strong>
                    <ul>
                        <li>Otvori <code>register.php</code></li>
                        <li>Ispuni formu (username, email, password)</li>
                        <li>Klikni reCAPTCHA checkbox</li>
                        <li>Klikni "Registriraj se"</li>
                        <li>Provjeri email za verifikacijski kod</li>
                        <li>Unesi kod na <code>verify.php</code></li>
                    </ul>
                </li>
                <li><strong>Prijava:</strong>
                    <ul>
                        <li>Otvori <code>login.php</code></li>
                        <li>Unesi username/email i lozinku</li>
                        <li>Označi "Zapamti me" (opcionalno)</li>
                        <li>Klikni reCAPTCHA checkbox</li>
                        <li>Klikni "Prijavi se"</li>
                        <li>Preusmjeren na <code>index.php</code></li>
                    </ul>
                </li>
                <li><strong>Remember Me:</strong>
                    <ul>
                        <li>Prijavi se s označenim "Zapamti me"</li>
                        <li>Zatvori browser</li>
                        <li>Otvori ponovo <code>index.php</code></li>
                        <li>Trebao bi biti automatski prijavljen</li>
                    </ul>
                </li>
                <li><strong>Odjava:</strong>
                    <ul>
                        <li>Klikni na dropdown s tvojim imenom</li>
                        <li>Klikni "Odjava"</li>
                        <li>Preusmjeren na <code>login.php</code></li>
                        <li>Vidi success poruku</li>
                    </ul>
                </li>
            </ol>
        </div>
        
        <!-- Files Created -->
        <h2 class="mt-5 mb-4">📁 Kreirane datoteke</h2>
        
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
                        <td><code>lib/recaptcha_config.php</code></td>
                        <td>reCAPTCHA konfiguracija i verifikacijske funkcije</td>
                    </tr>
                    <tr>
                        <td><code>lib/SessionManager.php</code></td>
                        <td>Klasa za upravljanje sesijama i kolačićima</td>
                    </tr>
                    <tr>
                        <td><code>login.php</code></td>
                        <td>Stranica za prijavu s reCAPTCHA</td>
                    </tr>
                    <tr>
                        <td><code>logout.php</code></td>
                        <td>Script za odjavu korisnika</td>
                    </tr>
                    <tr>
                        <td><code>api/login.php</code></td>
                        <td>API endpoint za autentifikaciju</td>
                    </tr>
                    <tr>
                        <td><code>register.php</code></td>
                        <td>Ažurirano - dodana reCAPTCHA</td>
                    </tr>
                    <tr>
                        <td><code>api/register_user.php</code></td>
                        <td>Ažurirano - reCAPTCHA verifikacija</td>
                    </tr>
                    <tr>
                        <td><code>index.php</code></td>
                        <td>Ažurirano - login/logout navbar, zaštita funkcija</td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- reCAPTCHA Keys -->
        <h2 class="mt-5 mb-4">🔑 reCAPTCHA Keys</h2>
        
        <div class="alert alert-warning">
            <h5><i class="bi bi-exclamation-triangle"></i> Test Keys (trenutno u upotrebi)</h5>
            <p><strong>Site Key:</strong> <code>6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI</code></p>
            <p><strong>Secret Key:</strong> <code>6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe</code></p>
            <p class="mb-0">Ovi ključevi su Google-ovi test ključevi koji <strong>uvijek prolaze</strong>. Za produkciju:</p>
            <ol>
                <li>Idi na <a href="https://www.google.com/recaptcha/admin" target="_blank">Google reCAPTCHA Admin</a></li>
                <li>Kreiraj novi site (reCAPTCHA v2 Checkbox)</li>
                <li>Kopiraj Site Key i Secret Key</li>
                <li>Zamijeni u <code>lib/recaptcha_config.php</code> i HTML formama</li>
            </ol>
        </div>
        
        <!-- Links -->
        <div class="text-center mt-5">
            <a href="login.php" class="btn btn-primary me-2">
                <i class="bi bi-box-arrow-in-right"></i> Prijava
            </a>
            <a href="register.php" class="btn btn-success me-2">
                <i class="bi bi-person-plus"></i> Registracija
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
