<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Full-Text Search - Test Demonstracija</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 30px 0;
            min-height: 100vh;
        }
        .demo-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .demo-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 40px;
            margin-bottom: 30px;
        }
        .test-case {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 5px;
        }
        .test-url {
            background: #2d3436;
            color: #dfe6e9;
            padding: 10px 15px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            margin: 10px 0;
            word-break: break-all;
        }
        .sql-block {
            background: #2d3436;
            color: #00ff00;
            padding: 15px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            margin: 10px 0;
            overflow-x: auto;
        }
        .success-badge {
            background: #10ac84;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            display: inline-block;
            margin: 5px;
        }
        .info-box {
            background: #e8f5e9;
            border: 2px solid #4caf50;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container demo-container">
        <div class="text-center text-white mb-4">
            <h1 class="display-4 fw-bold">
                <i class="bi bi-check-circle-fill"></i> Full-Text Search
            </h1>
            <p class="lead">Test Demonstracija - Vlastiti PHP & SQL</p>
        </div>
        
        <!-- Zahtjevi -->
        <div class="demo-card">
            <h3><i class="bi bi-clipboard-check text-success"></i> Ispunjeni Zahtjevi</h3>
            <hr>
            <div class="row">
                <div class="col-md-6">
                    <div class="info-box">
                        <h5>✅ Minimalno 2 stupca podataka</h5>
                        <ul class="mb-0">
                            <li><strong>hotels:</strong> naziv + adresa + grad = <span class="success-badge">3 stupca</span></li>
                            <li><strong>users:</strong> username + email = <span class="success-badge">2 stupca</span></li>
                            <li><strong>UKUPNO:</strong> <span class="success-badge">5 stupaca</span></li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-box">
                        <h5>✅ Minimalno 2 različite tablice</h5>
                        <ul class="mb-0">
                            <li><strong>hotels</strong> tablica <span class="success-badge">✓</span></li>
                            <li><strong>users</strong> tablica <span class="success-badge">✓</span></li>
                            <li>Full-Text indeksi instalirani <span class="success-badge">✓</span></li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="alert alert-success mt-3">
                <h5><i class="bi bi-code-square"></i> Vlastiti PHP & SQL Kod</h5>
                <ul class="mb-0">
                    <li><strong>SearchEngine.php</strong> - Vlastita PHP klasa (200+ linija)</li>
                    <li><strong>SQL MATCH() AGAINST()</strong> - Full-Text Search query</li>
                    <li><strong>NE koristi DataTables</strong> - 100% custom kod</li>
                    <li><strong>NE koristi gotove JS alate</strong> - Vanilla PHP/SQL</li>
                </ul>
            </div>
        </div>
        
        <!-- SQL Implementacija -->
        <div class="demo-card">
            <h3><i class="bi bi-database-fill text-primary"></i> SQL Full-Text Implementacija</h3>
            <hr>
            
            <h5>1. Full-Text Indeksi</h5>
            <div class="sql-block">
-- Hotels tablica (3 stupca)<br>
ALTER TABLE hotels ADD FULLTEXT INDEX ft_hotels_search (naziv, adresa, grad);<br>
<br>
-- Users tablica (2 stupca)<br>
ALTER TABLE users ADD FULLTEXT INDEX ft_users_search (username, email);
            </div>
            
            <h5 class="mt-4">2. Search Query (hotels)</h5>
            <div class="sql-block">
SELECT <br>
&nbsp;&nbsp;id, naziv, adresa, grad, kapacitet,<br>
&nbsp;&nbsp;<strong style="color: yellow;">MATCH(naziv, adresa, grad) AGAINST(? IN NATURAL LANGUAGE MODE) AS relevance</strong><br>
FROM hotels<br>
WHERE <strong style="color: yellow;">MATCH(naziv, adresa, grad) AGAINST(? IN NATURAL LANGUAGE MODE)</strong><br>
ORDER BY relevance DESC<br>
LIMIT 20;
            </div>
            
            <h5 class="mt-4">3. Search Query (users)</h5>
            <div class="sql-block">
SELECT <br>
&nbsp;&nbsp;id, username, email,<br>
&nbsp;&nbsp;<strong style="color: yellow;">MATCH(username, email) AGAINST(? IN NATURAL LANGUAGE MODE) AS relevance</strong><br>
FROM users<br>
WHERE <strong style="color: yellow;">MATCH(username, email) AGAINST(? IN NATURAL LANGUAGE MODE)</strong><br>
ORDER BY relevance DESC<br>
LIMIT 20;
            </div>
            
            <div class="alert alert-info mt-3">
                <strong><i class="bi bi-lightbulb"></i> Napomena:</strong> 
                MATCH() AGAINST() je MySQL Full-Text Search funkcija koja koristi indekse za brzu pretragu 
                i automatski rangira rezultate po relevantnosti.
            </div>
        </div>
        
        <!-- Test Cases -->
        <div class="demo-card">
            <h3><i class="bi bi-bug-fill text-warning"></i> Test Cases</h3>
            <hr>
            
            <div class="test-case">
                <h5>Test 1: Pretraga po gradu "Zagreb"</h5>
                <p>Traži hotele koji imaju "Zagreb" u nazivu, adresi ili gradu + korisnike sa "zagreb" u username/email</p>
                <div class="test-url">search.php?q=Zagreb</div>
                <a href="search.php?q=Zagreb" class="btn btn-primary" target="_blank">
                    <i class="bi bi-play-fill"></i> Testiraj
                </a>
                <strong class="text-success ms-3">Očekivano: 4+ hotela + 1 korisnik</strong>
            </div>
            
            <div class="test-case">
                <h5>Test 2: Pretraga po gradu "Split"</h5>
                <p>Traži hotele sa "Split" + korisnike sa "split"</p>
                <div class="test-url">search.php?q=Split</div>
                <a href="search.php?q=Split" class="btn btn-primary" target="_blank">
                    <i class="bi bi-play-fill"></i> Testiraj
                </a>
                <strong class="text-success ms-3">Očekivano: 3+ hotela + 1 korisnik</strong>
            </div>
            
            <div class="test-case">
                <h5>Test 3: Pretraga po nazivu "Hotel"</h5>
                <p>Traži sve hotele sa riječi "Hotel" u nazivu + korisnike sa "hotel" u emailu</p>
                <div class="test-url">search.php?q=Hotel</div>
                <a href="search.php?q=Hotel" class="btn btn-primary" target="_blank">
                    <i class="bi bi-play-fill"></i> Testiraj
                </a>
                <strong class="text-success ms-3">Očekivano: 10+ hotela + 2+ korisnika</strong>
            </div>
            
            <div class="test-case">
                <h5>Test 4: Email pretraga "@gmail"</h5>
                <p>Traži korisnike sa Gmail adresom</p>
                <div class="test-url">search.php?q=gmail</div>
                <a href="search.php?q=gmail" class="btn btn-primary" target="_blank">
                    <i class="bi bi-play-fill"></i> Testiraj
                </a>
                <strong class="text-success ms-3">Očekivano: 1+ korisnika</strong>
            </div>
            
            <div class="test-case">
                <h5>Test 5: Boolean Search "+Zagreb -Hotel"</h5>
                <p>Mora sadržavati "Zagreb" ali NE smije "Hotel"</p>
                <div class="test-url">search.php?q=+Zagreb -Hotel&mode=boolean</div>
                <a href="search.php?q=%2BZagreb%20-Hotel&mode=boolean" class="btn btn-primary" target="_blank">
                    <i class="bi bi-play-fill"></i> Testiraj
                </a>
                <strong class="text-success ms-3">Očekivano: Korisnici sa "zagreb", nema hotela</strong>
            </div>
            
            <div class="test-case">
                <h5>Test 6: Pretraga po adresi "Ilica"</h5>
                <p>Traži hotele na adresi Ilica (Zagreb)</p>
                <div class="test-url">search.php?q=Ilica</div>
                <a href="search.php?q=Ilica" class="btn btn-primary" target="_blank">
                    <i class="bi bi-play-fill"></i> Testiraj
                </a>
                <strong class="text-success ms-3">Očekivano: 1 hotel (Jägerhorn Zagreb)</strong>
            </div>
        </div>
        
        <!-- PHP Implementacija -->
        <div class="demo-card">
            <h3><i class="bi bi-file-earmark-code text-danger"></i> PHP Kod Struktura</h3>
            <hr>
            
            <div class="row">
                <div class="col-md-6">
                    <h5>Kreirane Datoteke</h5>
                    <ul class="list-group">
                        <li class="list-group-item">
                            <i class="bi bi-file-earmark-code"></i> 
                            <strong>lib/SearchEngine.php</strong>
                            <span class="badge bg-success float-end">200+ linija</span>
                        </li>
                        <li class="list-group-item">
                            <i class="bi bi-file-earmark"></i> 
                            <strong>search.php</strong>
                            <span class="badge bg-info float-end">300+ linija</span>
                        </li>
                        <li class="list-group-item">
                            <i class="bi bi-file-earmark"></i> 
                            <strong>api/search.php</strong>
                            <span class="badge bg-warning float-end">API Endpoint</span>
                        </li>
                        <li class="list-group-item">
                            <i class="bi bi-database"></i> 
                            <strong>create_fulltext_indexes.sql</strong>
                            <span class="badge bg-primary float-end">SQL</span>
                        </li>
                    </ul>
                </div>
                
                <div class="col-md-6">
                    <h5>Glavne Metode</h5>
                    <ul class="list-group">
                        <li class="list-group-item">
                            <code>search($term, $mode)</code>
                            <span class="badge bg-success float-end">Main</span>
                        </li>
                        <li class="list-group-item">
                            <code>searchHotels($term)</code>
                            <span class="badge bg-primary float-end">3 stupca</span>
                        </li>
                        <li class="list-group-item">
                            <code>searchUsers($term)</code>
                            <span class="badge bg-info float-end">2 stupca</span>
                        </li>
                        <li class="list-group-item">
                            <code>highlightText($text)</code>
                            <span class="badge bg-warning float-end">Highlight</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="alert alert-warning mt-3">
                <strong><i class="bi bi-shield-check"></i> Sigurnost:</strong>
                <ul class="mb-0">
                    <li>Prepared statements (SQL injection zaštita)</li>
                    <li>Input sanitizacija (real_escape_string)</li>
                    <li>HTML escaping (htmlspecialchars)</li>
                    <li>Relevance score validacija</li>
                </ul>
            </div>
        </div>
        
        <!-- Performance -->
        <div class="demo-card">
            <h3><i class="bi bi-speedometer2 text-info"></i> Performance & Prednosti</h3>
            <hr>
            
            <div class="row">
                <div class="col-md-6">
                    <h5>Full-Text Prednosti</h5>
                    <ul>
                        <li>⚡ <strong>Brže od LIKE %term%</strong> - koristi indekse</li>
                        <li>📊 <strong>Relevance ranking</strong> - automatski score</li>
                        <li>🔍 <strong>Natural language</strong> - inteligentna pretraga</li>
                        <li>📈 <strong>Scalable</strong> - radi sa milijunima zapisa</li>
                        <li>🚫 <strong>Stop words</strong> - automatski filtering</li>
                    </ul>
                </div>
                
                <div class="col-md-6">
                    <h5>Search Modes</h5>
                    <ul>
                        <li><strong>NATURAL LANGUAGE</strong> - Default (relevance ranking)</li>
                        <li><strong>BOOLEAN MODE</strong> - +word -word "phrase"</li>
                        <li><strong>QUERY EXPANSION</strong> - Proširena pretraga</li>
                    </ul>
                    
                    <h5 class="mt-3">Limitacije</h5>
                    <ul class="mb-0">
                        <li>Min 3 znaka (ft_min_word_len)</li>
                        <li>InnoDB/MyISAM tablice</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Dokumentacija -->
        <div class="demo-card">
            <h3><i class="bi bi-book text-secondary"></i> Dokumentacija</h3>
            <hr>
            <p>Za detaljne upute i API reference, pogledaj:</p>
            <a href="FULLTEXT_SEARCH_DOCS.md" class="btn btn-outline-primary" target="_blank">
                <i class="bi bi-file-earmark-text"></i> FULLTEXT_SEARCH_DOCS.md
            </a>
        </div>
        
        <!-- Action Buttons -->
        <div class="text-center">
            <a href="search.php" class="btn btn-primary btn-lg me-2">
                <i class="bi bi-search"></i> Otvori Full-Text Pretragu
            </a>
            <a href="index.php" class="btn btn-outline-light btn-lg">
                <i class="bi bi-house"></i> Povratak na početnu
            </a>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
