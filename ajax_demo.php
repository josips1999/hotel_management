<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AJAX Username Provjera - Demo</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        body {
            background: #f5f5f5;
            padding: 50px 20px;
        }
        
        .demo-container {
            max-width: 800px;
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
        
        .code-block {
            background: #282c34;
            color: #abb2bf;
            padding: 20px;
            border-radius: 8px;
            overflow-x: auto;
            margin: 20px 0;
        }
        
        .code-block code {
            font-family: 'Courier New', monospace;
            font-size: 14px;
            line-height: 1.6;
        }
        
        .test-section {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 8px;
            margin: 30px 0;
        }
        
        .feature-list {
            list-style: none;
            padding: 0;
        }
        
        .feature-list li {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        
        .feature-list li:last-child {
            border-bottom: none;
        }
        
        .badge-custom {
            background: #667eea;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="demo-container">
        <div class="demo-header">
            <h1><i class="bi bi-code-square"></i> AJAX Provjera Korisničkog Imena</h1>
            <p class="lead">Demonstracija real-time validacije s bazom podataka</p>
        </div>
        
        <!-- Description -->
        <div class="alert alert-info">
            <h5><i class="bi bi-info-circle"></i> Što je implementirano?</h5>
            <p class="mb-0">
                Ova implementacija pokazuje kako koristiti AJAX za provjeru dostupnosti korisničkog imena u bazi podataka 
                <strong>prije</strong> nego što korisnik submitta formu. Provjera se događa u realnom vremenu kada korisnik 
                napusti polje (blur event).
            </p>
        </div>
        
        <!-- Features -->
        <h3 class="mt-4 mb-3">🚀 Implementirane funkcionalnosti</h3>
        <ul class="feature-list">
            <li>
                <span class="badge-custom">✓</span>
                <strong>Real-time AJAX validacija</strong> - Provjera korisničkog imena bez reload stranice
            </li>
            <li>
                <span class="badge-custom">✓</span>
                <strong>Server-side provjera</strong> - API endpoint (api/check_username.php) provjerava bazu
            </li>
            <li>
                <span class="badge-custom">✓</span>
                <strong>Client-side validacija</strong> - Format provjera prije slanja AJAX zahtjeva
            </li>
            <li>
                <span class="badge-custom">✓</span>
                <strong>Visual feedback</strong> - Zeleno/crveno označavanje dostupnosti
            </li>
            <li>
                <span class="badge-custom">✓</span>
                <strong>Loading indicator</strong> - "Provjeravam..." poruka tijekom provjere
            </li>
            <li>
                <span class="badge-custom">✓</span>
                <strong>Prepared statements</strong> - SQL injection zaštita
            </li>
            <li>
                <span class="badge-custom">✓</span>
                <strong>JSON response</strong> - Strukturirani odgovori {available, valid, message}
            </li>
        </ul>
        
        <!-- Test Section -->
        <div class="test-section">
            <h4 class="mb-4"><i class="bi bi-pencil-square"></i> Testirajte AJAX provjeru</h4>
            
            <div class="mb-3">
                <label for="testUsername" class="form-label">Korisničko ime</label>
                <input 
                    type="text" 
                    class="form-control" 
                    id="testUsername" 
                    placeholder="Unesite korisničko ime..."
                >
                <div id="testUsernameFeedback" class="form-text"></div>
            </div>
            
            <div class="alert alert-warning">
                <strong>Napomena:</strong> Prvo morate pokrenuti <code>instalacija.php</code> da kreirate <code>users</code> tablicu u bazi.
            </div>
        </div>
        
        <!-- Code Examples -->
        <h3 class="mt-5 mb-3">💻 Primjeri koda</h3>
        
        <!-- 1. JavaScript AJAX Function -->
        <h5 class="mt-4">1. JavaScript AJAX funkcija</h5>
        <div class="code-block">
            <code>
<span style="color: #c678dd">function</span> <span style="color: #61afef">checkUsernameAvailability</span>(<span style="color: #e06c75">username</span>, <span style="color: #e06c75">callback</span>) {<br>
&nbsp;&nbsp;<span style="color: #7f848e">// Create XMLHttpRequest</span><br>
&nbsp;&nbsp;<span style="color: #c678dd">const</span> <span style="color: #e06c75">xhr</span> = <span style="color: #c678dd">new</span> <span style="color: #61afef">XMLHttpRequest</span>();<br>
&nbsp;&nbsp;<br>
&nbsp;&nbsp;<span style="color: #7f848e">// Configure GET request</span><br>
&nbsp;&nbsp;xhr.<span style="color: #61afef">open</span>(<span style="color: #98c379">'GET'</span>, <span style="color: #98c379">'api/check_username.php?username='</span> + <span style="color: #61afef">encodeURIComponent</span>(username), <span style="color: #d19a66">true</span>);<br>
&nbsp;&nbsp;<br>
&nbsp;&nbsp;<span style="color: #7f848e">// Handle response</span><br>
&nbsp;&nbsp;xhr.<span style="color: #e06c75">onreadystatechange</span> = <span style="color: #c678dd">function</span>() {<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: #c678dd">if</span> (xhr.<span style="color: #e06c75">readyState</span> === <span style="color: #d19a66">4</span> && xhr.<span style="color: #e06c75">status</span> === <span style="color: #d19a66">200</span>) {<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: #c678dd">const</span> <span style="color: #e06c75">response</span> = <span style="color: #61afef">JSON.parse</span>(xhr.<span style="color: #e06c75">responseText</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: #61afef">callback</span>(response);<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
&nbsp;&nbsp;};<br>
&nbsp;&nbsp;<br>
&nbsp;&nbsp;<span style="color: #7f848e">// Send request</span><br>
&nbsp;&nbsp;xhr.<span style="color: #61afef">send</span>();<br>
}
            </code>
        </div>
        
        <!-- 2. PHP API Endpoint -->
        <h5 class="mt-4">2. PHP API endpoint (api/check_username.php)</h5>
        <div class="code-block">
            <code>
<span style="color: #c678dd">&lt;?php</span><br>
<span style="color: #61afef">require_once</span>(<span style="color: #98c379">'../lib/db_connection.php'</span>);<br>
<br>
<span style="color: #e5c07b">$username</span> = <span style="color: #61afef">trim</span>(<span style="color: #e5c07b">$_GET</span>[<span style="color: #98c379">'username'</span>]);<br>
<br>
<span style="color: #7f848e">// Check in database using prepared statement</span><br>
<span style="color: #e5c07b">$stmt</span> = <span style="color: #e5c07b">$connection</span>-><span style="color: #61afef">prepare</span>(<span style="color: #98c379">"SELECT id FROM users WHERE username = ?"</span>);<br>
<span style="color: #e5c07b">$stmt</span>-><span style="color: #61afef">bind_param</span>(<span style="color: #98c379">"s"</span>, <span style="color: #e5c07b">$username</span>);<br>
<span style="color: #e5c07b">$stmt</span>-><span style="color: #61afef">execute</span>();<br>
<span style="color: #e5c07b">$result</span> = <span style="color: #e5c07b">$stmt</span>-><span style="color: #61afef">get_result</span>();<br>
<br>
<span style="color: #7f848e">// Return JSON response</span><br>
<span style="color: #e5c07b">$response</span> = [<br>
&nbsp;&nbsp;<span style="color: #98c379">'available'</span> => <span style="color: #e5c07b">$result</span>-><span style="color: #e06c75">num_rows</span> === <span style="color: #d19a66">0</span>,<br>
&nbsp;&nbsp;<span style="color: #98c379">'message'</span> => <span style="color: #e5c07b">$result</span>-><span style="color: #e06c75">num_rows</span> === <span style="color: #d19a66">0</span> ? <span style="color: #98c379">'Dostupno'</span> : <span style="color: #98c379">'Zauzeto'</span><br>
];<br>
<br>
<span style="color: #c678dd">echo</span> <span style="color: #61afef">json_encode</span>(<span style="color: #e5c07b">$response</span>);<br>
<span style="color: #c678dd">?&gt;</span>
            </code>
        </div>
        
        <!-- 3. HTML Integration -->
        <h5 class="mt-4">3. HTML integracija</h5>
        <div class="code-block">
            <code>
<span style="color: #7f848e">&lt;!-- HTML Input Field --&gt;</span><br>
<span style="color: #e06c75">&lt;input</span> <br>
&nbsp;&nbsp;<span style="color: #d19a66">type</span>=<span style="color: #98c379">"text"</span> <br>
&nbsp;&nbsp;<span style="color: #d19a66">id</span>=<span style="color: #98c379">"username"</span> <br>
&nbsp;&nbsp;<span style="color: #d19a66">class</span>=<span style="color: #98c379">"form-control"</span><br>
<span style="color: #e06c75">&gt;</span><br>
<span style="color: #e06c75">&lt;div</span> <span style="color: #d19a66">id</span>=<span style="color: #98c379">"usernameFeedback"</span><span style="color: #e06c75">&gt;&lt;/div&gt;</span><br>
<br>
<span style="color: #7f848e">&lt;!-- JavaScript Event Listener --&gt;</span><br>
<span style="color: #e06c75">&lt;script&gt;</span><br>
<span style="color: #c678dd">const</span> <span style="color: #e06c75">usernameInput</span> = <span style="color: #e5c07b">document</span>.<span style="color: #61afef">getElementById</span>(<span style="color: #98c379">'username'</span>);<br>
<span style="color: #c678dd">const</span> <span style="color: #e06c75">feedback</span> = <span style="color: #e5c07b">document</span>.<span style="color: #61afef">getElementById</span>(<span style="color: #98c379">'usernameFeedback'</span>);<br>
<br>
<span style="color: #7f848e">// Call AJAX validation on blur</span><br>
usernameInput.<span style="color: #61afef">addEventListener</span>(<span style="color: #98c379">'blur'</span>, <span style="color: #c678dd">function</span>() {<br>
&nbsp;&nbsp;<span style="color: #61afef">validateUsernameWithAjax</span>(usernameInput, feedback);<br>
});<br>
<span style="color: #e06c75">&lt;/script&gt;</span>
            </code>
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
                        <td><code>api/check_username.php</code></td>
                        <td>API endpoint za AJAX provjeru dostupnosti username-a</td>
                    </tr>
                    <tr>
                        <td><code>api/register_user.php</code></td>
                        <td>API endpoint za registraciju novog korisnika</td>
                    </tr>
                    <tr>
                        <td><code>js/client_validation.js</code></td>
                        <td>JavaScript funkcije za AJAX i validaciju</td>
                    </tr>
                    <tr>
                        <td><code>register.php</code></td>
                        <td>Registracijska forma s real-time AJAX provjerom</td>
                    </tr>
                    <tr>
                        <td><code>instalacija.php</code></td>
                        <td>Ažurirano - dodana <code>users</code> tablica</td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Testing Instructions -->
        <div class="alert alert-success mt-4">
            <h5><i class="bi bi-check-circle"></i> Kako testirati?</h5>
            <ol>
                <li>Pokrenite <code>instalacija.php</code> da kreirate <code>users</code> tablicu</li>
                <li>Otvorite <code>register.php</code> u browseru</li>
                <li>Unesite korisničko ime i napustite polje (kliknite negdje drugo)</li>
                <li>Vidjet ćete poruku "Provjeravam dostupnost..."</li>
                <li>Zatim će se prikazati da li je username dostupan ili zauzet</li>
            </ol>
        </div>
        
        <!-- Back Link -->
        <div class="text-center mt-4">
            <a href="register.php" class="btn btn-primary">
                <i class="bi bi-person-plus"></i> Otvori registracijsku formu
            </a>
            <a href="index.php" class="btn btn-secondary ms-2">
                <i class="bi bi-house"></i> Početna stranica
            </a>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Client Validation JS -->
    <script src="js/client_validation.js"></script>
    
    <!-- Demo Test Script -->
    <script>
        const testUsernameInput = document.getElementById('testUsername');
        const testFeedback = document.getElementById('testUsernameFeedback');
        
        testUsernameInput.addEventListener('blur', function() {
            validateUsernameWithAjax(testUsernameInput, testFeedback);
        });
    </script>
</body>
</html>
