<?php
/**
 * Full-Text Search Page
 * Advanced search with natural and boolean modes
 */

// ============================================================================
// PHP CODE - Business Logic (prije HTML-a)
// ============================================================================

require_once('lib/db_connection.php');
require_once('lib/config.php');
require_once('lib/Pagination.php');
require_once('lib/SearchEngine.php');
require_once('lib/SessionManager.php');
mysqli_select_db($connection,'hotel_management');

// Session
$sessionManager = new SessionManager($connection);
$sessionManager->checkRememberMe();
$isLoggedIn = $sessionManager->isLoggedIn();
$username = $sessionManager->getUsername();

// Guest limitations (unregistered users)
$isGuest = !$isLoggedIn;
$guestMaxResults = 5; // Limit to 5 results for guests
$guestHiddenColumns = ['email', 'telefon', 'broj_gostiju', 'zupanija'];

// Search engine
$searchEngine = new SearchEngine($connection);
$stats = $searchEngine->getSearchStats();
$suggestions = $searchEngine->getSearchSuggestions();

// Get current page
$currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

// Execute search if form submitted
$searchResults = null;
$searchTerm = '';
$pagination = null;

if (isset($_GET['q']) && !empty(trim($_GET['q']))) {
    $searchTerm = trim($_GET['q']);
    $searchMode = isset($_GET['mode']) && $_GET['mode'] === 'boolean' 
        ? SearchEngine::MODE_BOOLEAN 
        : SearchEngine::MODE_NATURAL;
    
    // For guests, limit results
    $itemsPerPage = $isGuest ? $guestMaxResults : ITEMS_PER_PAGE;
    
    $searchResults = $searchEngine->search($searchTerm, $searchMode, $currentPage, $itemsPerPage);
    
    // For guests, limit to first N results and force page 1
    if ($isGuest && $searchResults['success'] && isset($searchResults['results'])) {
        if (count($searchResults['results']) > $guestMaxResults) {
            $searchResults['results'] = array_slice($searchResults['results'], 0, $guestMaxResults);
        }
        $currentPage = 1;
    }
    
    // Create pagination instance
    if ($searchResults['success']) {
        $totalResults = $searchResults['pagination']['total_items'] ?? 0;
        $pagination = new Pagination($totalResults, $currentPage, $itemsPerPage);
    }
}

// Page-specific variables for template
$pageTitle = 'Full-Text Pretraga - Hotel Management';
$currentPage = 'search';

// Custom CSS for this page
$customCSS = "
    * { box-sizing: border-box; }
    body {
        margin: 0;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    }
    .container { max-width: 1200px; margin: 0 auto; padding: 0 15px; }
    .search-container { max-width: 1000px; margin: 0 auto; }
    .search-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        padding: 40px;
        margin-bottom: 30px;
    }
    .search-input-group { position: relative; margin-bottom: 30px; }
    .search-input, .form-input {
        width: 100%;
        height: 60px;
        font-size: 1.2rem;
        border-radius: 30px;
        padding: 0 30px 0 60px;
        border: 2px solid #677ae6;
        outline: none;
    }
    .search-input:focus, .form-input:focus {
        border-color: #764ba2;
        box-shadow: 0 0 0 0.25rem rgba(118, 75, 162, 0.25);
    }
    .search-icon {
        position: absolute;
        left: 25px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 1.5rem;
        color: #667eea;
        pointer-events: none;
    }
    .btn {
        display: inline-block;
        padding: 12px 30px;
        font-size: 1rem;
        border: none;
        border-radius: 30px;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.3s;
        text-align: center;
    }
    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4); }
    .btn-outline-primary {
        background: transparent;
        border: 2px solid #667eea;
        color: #667eea;
    }
    .btn-outline-primary:hover { background: #667eea; color: white; }
    .btn-outline-light {
        background: transparent;
        border: 2px solid white;
        color: white;
    }
    .btn-outline-light:hover { background: white; color: #667eea; }
    .btn-lg { padding: 15px 40px; font-size: 1.1rem; }
    .search-btn {
        width: 100%;
        height: 60px;
        border-radius: 30px;
        padding: 0 40px;
        font-size: 1.1rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        color: white;
        cursor: pointer;
    }
    .stats-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 30px;
    }
    .row { display: flex; flex-wrap: wrap; margin: 0 -15px; }
    .col { flex: 1; padding: 0 15px; }
    .col-3 { flex: 0 0 25%; max-width: 25%; padding: 0 15px; }
    .col-4 { flex: 0 0 33.333%; max-width: 33.333%; padding: 0 15px; }
    .col-6 { flex: 0 0 50%; max-width: 50%; padding: 0 15px; }
    .col-9 { flex: 0 0 75%; max-width: 75%; padding: 0 15px; }
    .col-12 { flex: 0 0 100%; max-width: 100%; padding: 0 15px; }
    @media (max-width: 768px) {
        .col-3, .col-4, .col-6, .col-9, .col-12 { flex: 0 0 100%; max-width: 100%; }
    }
    .text-center { text-align: center; }
    .text-white { color: white; }
    .text-muted { color: #6c757d; }
    .text-primary { color: #667eea; }
    .text-success { color: #28a745; }
    .mb-0 { margin-bottom: 0; }
    .mb-1 { margin-bottom: 0.25rem; }
    .mb-2 { margin-bottom: 0.5rem; }
    .mb-3 { margin-bottom: 1rem; }
    .mb-4 { margin-bottom: 1.5rem; }
    .mt-3 { margin-top: 1rem; }
    .mt-4 { margin-top: 1.5rem; }
    .me-2 { margin-right: 0.5rem; }
    .d-flex { display: flex; }
    .justify-content-between { justify-content: space-between; }
    .align-items-start { align-items: flex-start; }
    .align-items-center { align-items: center; }
    .badge {
        display: inline-block;
        padding: 0.35em 0.65em;
        font-size: 0.875rem;
        font-weight: 600;
        line-height: 1;
        color: white;
        text-align: center;
        white-space: nowrap;
        border-radius: 0.375rem;
    }
    .badge-primary { background-color: #667eea; }
    .badge-secondary { background-color: #6c757d; }
    .badge-success { background-color: #28a745; }
    .alert {
        padding: 1rem;
        margin-bottom: 1rem;
        border-radius: 0.375rem;
        border: 1px solid transparent;
    }
    .alert-warning {
        color: #856404;
        background-color: #fff3cd;
        border-color: #ffeeba;
    }
    .alert-info {
        color: #0c5460;
        background-color: #d1ecf1;
        border-color: #bee5eb;
    }
    .alert-danger {
        color: #721c24;
        background-color: #f8d7da;
        border-color: #f5c6cb;
    }
    .alert a { color: inherit; font-weight: 600; text-decoration: underline; }
    .result-item {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 15px;
        border-left: 4px solid #667eea;
        transition: all 0.3s;
    }
    .result-item:hover {
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        transform: translateX(5px);
    }
    .result-type-badge {
        font-size: 0.8rem;
        padding: 4px 12px;
        display: inline-block;
        border-radius: 4px;
        color: white;
        font-weight: 600;
    }
    .relevance-score {
        font-weight: bold;
        color: #667eea;
    }
    mark {
        background-color: #ffd700;
        padding: 2px 4px;
        border-radius: 3px;
        font-weight: 600;
    }
    .suggestion-chip {
        display: inline-block;
        background: white;
        color: #667eea;
        padding: 8px 16px;
        border-radius: 20px;
        margin: 5px;
        cursor: pointer;
        transition: all 0.2s;
        border: 2px solid #667eea;
    }
    .suggestion-chip:hover {
        background: #667eea;
        color: white;
        transform: scale(1.05);
    }
    .no-results {
        text-align: center;
        padding: 60px 20px;
        color: #6c757d;
    }
    .no-results i {
        font-size: 4rem;
        margin-bottom: 20px;
        opacity: 0.3;
    }
    .radio-group { margin-bottom: 1rem; }
    .radio-inline {
        display: inline-block;
        margin-right: 15px;
    }
    .radio-inline input[type=\"radio\"] {
        margin-right: 5px;
    }
    .radio-inline label {
        cursor: pointer;
        user-select: none;
    }
    hr {
        border: 0;
        border-top: 1px solid #dee2e6;
        margin: 1rem 0;
    }
    h1, h2, h3, h4, h5, h6 {
        margin-top: 0;
        margin-bottom: 0.5rem;
        font-weight: 600;
    }
    .display-4 { font-size: 3rem; }
    .fw-bold { font-weight: 700; }
    .lead { font-size: 1.25rem; font-weight: 300; }
    ul { margin: 0.5rem 0; padding-left: 20px; }
    p { margin: 0 0 1rem; }
    small { font-size: 0.875rem; }
    code {
        padding: 2px 6px;
        background: #f8f9fa;
        border-radius: 3px;
        font-family: 'Courier New', monospace;
    }
";

// ============================================================================
// HTML OUTPUT
// ============================================================================
?>
<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <style>
        <?php echo $customCSS; ?>
    </style>
</head>
<body>
    <div class="container search-container">
        <!-- Header -->
        <div class="text-center text-white mb-4">
            <h1 class="display-4 fw-bold">
                <i class="bi bi-search"></i> Full-Text Pretraga
            </h1>
            <p class="lead">Pretraživanje hotela i korisnika</p>
        </div>
        
        <!-- Statistics Card -->
        <div class="stats-card">
            <div class="row text-center">
                <div class="col-3">
                    <h3><?php echo $stats['hotels_count']; ?></h3>
                    <p class="mb-0"><i class="bi bi-building"></i> Hotela</p>
                </div>
                <div class="col-3">
                    <h3><?php echo $stats['users_count']; ?></h3>
                    <p class="mb-0"><i class="bi bi-people"></i> Korisnika</p>
                </div>
                <div class="col-3">
                    <h3><?php echo $stats['hotels_ft_indexed'] ? '✅' : '❌'; ?></h3>
                    <p class="mb-0">Hotels FT Index</p>
                </div>
                <div class="col-3">
                    <h3><?php echo $stats['users_ft_indexed'] ? '✅' : '❌'; ?></h3>
                    <p class="mb-0">Users FT Index</p>
                </div>
            </div>
        </div>
        
        <!-- Search Form -->
        <div class="search-card">
            <form method="GET" action="search.php" id="searchForm">
                <div class="row">
                    <div class="col-9">
                        <div class="search-input-group">
                            <i class="bi bi-search search-icon"></i>
                            <input 
                                type="text" 
                                name="q" 
                                class="search-input" 
                                placeholder="Pretraži hotele i korisnike..."
                                value="<?php echo htmlspecialchars($searchTerm); ?>"
                                autocomplete="off"
                                autofocus
                            >
                        </div>
                    </div>
                    <div class="col-3">
                        <button type="submit" class="search-btn">
                            <i class="bi bi-search"></i> Pretraži
                        </button>
                    </div>
                </div>
                
                <!-- Search Mode Options -->
                <div class="row mb-3">
                    <div class="col-12">
                        <small class="text-muted">Search Mode:</small>
                        <div class="radio-group">
                            <div class="radio-inline">
                                <input type="radio" name="mode" id="modeNatural" value="natural" checked>
                                <label for="modeNatural">Natural</label>
                            </div>
                            <div class="radio-inline">
                                <input type="radio" name="mode" id="modeBoolean" value="boolean">
                                <label for="modeBoolean">Boolean (+word -word)</label>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            
            <!-- Suggestions -->
            <?php if (empty($searchTerm)): ?>
            <div class="mt-4">
                <h6 class="text-muted mb-3">Prijedlozi za pretragu:</h6>
                <?php foreach ($suggestions as $category => $terms): ?>
                    <div class="mb-2">
                        <small class="text-muted"><?php echo $category; ?>:</small><br>
                        <?php foreach ($terms as $term): ?>
                            <span class="suggestion-chip" onclick="searchFor('<?php echo $term; ?>')">
                                <?php echo $term; ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
                
                <div class="alert alert-info mt-3">
                    <small>
                        <i class="bi bi-lightbulb"></i> 
                        <strong>Tip:</strong> Možete pretražiti naziv hotela, adresu, grad, korisničko ime ili email.
                    </small>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Search Results -->
        <?php if ($searchResults): ?>
        <div class="search-card">
            <?php if ($searchResults['success']): ?>
                <!-- Guest Limitation Alert -->
                <?php if ($isGuest): ?>
                    <div class="alert alert-warning mb-4">
                        <i class="bi bi-eye-slash"></i> <strong>Ograničen pregled:</strong> 
                        Prikazano je samo <strong><?php echo $guestMaxResults; ?></strong> rezultata 
                        s ograničenim podacima. 
                        <a href="login.php"><strong>Prijavite se</strong></a> 
                        za potpuni pristup svim rezultatima i detaljnim informacijama.
                    </div>
                <?php endif; ?>
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4>
                        Rezultati za: <strong><?php echo htmlspecialchars($searchResults['search_term']); ?></strong>
                    </h4>
                    <span class="badge badge-primary" style="font-size: 1rem;">
                        <?php echo $searchResults['total_results']; ?> rezultata
                        <?php if ($isGuest): ?>
                            (prikazano <?php echo min($guestMaxResults, $searchResults['total_results']); ?>)
                        <?php endif; ?>
                    </span>
                </div>
                
                <?php if ($searchResults['total_results'] > 0): ?>
                    <!-- Hotels Results -->
                    <?php if (!empty($searchResults['hotels'])): ?>
                    <h5 class="mb-3">
                        <i class="bi bi-building text-primary"></i> Hoteli 
                        <span class="badge badge-secondary"><?php echo count($searchResults['hotels']); ?></span>
                    </h5>
                    <?php foreach ($searchResults['hotels'] as $hotel): ?>
                    <div class="result-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div style="flex: 1;">
                                <div class="mb-2">
                                    <span class="result-type-badge badge-primary me-2">Hotel</span>
                                    <span class="relevance-score">Relevance: <?php echo $hotel['relevance']; ?></span>
                                </div>
                                <h5><i class="bi bi-building"></i> <?php echo $hotel['naziv_highlighted']; ?></h5>
                                <p class="mb-2">
                                    <i class="bi bi-geo-alt"></i> <?php echo $hotel['adresa_highlighted']; ?>, 
                                    <?php echo $hotel['grad_highlighted']; ?>
                                </p>
                                <div class="row">
                                    <div class="col-4">
                                        <small class="text-muted">
                                            <i class="bi bi-door-open"></i> Sobe: <strong><?php echo $hotel['broj_soba']; ?></strong>
                                        </small>
                                    </div>
                                    <div class="col-4">
                                        <small class="text-muted">
                                            <i class="bi bi-people"></i> Kapacitet: <strong><?php echo $hotel['kapacitet']; ?></strong>
                                        </small>
                                    </div>
                                    <div class="col-4">
                                        <small class="text-muted">
                                            <i class="bi bi-check-circle"></i> Slobodno: <strong><?php echo $hotel['slobodno_soba']; ?></strong>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                    
                    <!-- Users Results -->
                    <?php if (!empty($searchResults['users']) && !$isGuest): ?>
                    <h5 class="mb-3 mt-4">
                        <i class="bi bi-people text-success"></i> Korisnici 
                        <span class="badge badge-secondary"><?php echo count($searchResults['users']); ?></span>
                    </h5>
                    <?php foreach ($searchResults['users'] as $user): ?>
                    <div class="result-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div style="flex: 1;">
                                <div class="mb-2">
                                    <span class="result-type-badge badge-success me-2">Korisnik</span>
                                    <span class="relevance-score">Relevance: <?php echo $user['relevance']; ?></span>
                                </div>
                                <h5>
                                    <i class="bi bi-person-circle"></i> <?php echo $user['username_highlighted']; ?>
                                </h5>
                                <p class="mb-1">
                                    <i class="bi bi-envelope"></i> <?php echo $user['email_highlighted']; ?>
                                </p>
                                <small class="text-muted">
                                    <i class="bi bi-person"></i> 
                                    <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                                </small>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                    
                    <!-- Pagination -->
                    <?php if ($pagination && $pagination->getTotalPages() > 1): ?>
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div>
                            <?php echo $pagination->renderInfo(); ?>
                        </div>
                        <div>
                            <?php echo $pagination->render(); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                <?php else: ?>
                    <!-- No Results -->
                    <div class="no-results">
                        <i class="bi bi-search"></i>
                        <h4>Nema rezultata</h4>
                        <p>Pokušajte sa drugim pojmom za pretragu.</p>
                        <button class="btn btn-outline-primary" onclick="document.querySelector('.search-input').focus()">
                            Nova pretraga
                        </button>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <!-- Error Message -->
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i> 
                    <?php echo htmlspecialchars($searchResults['error']); ?>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <!-- Info Card -->
        <div class="search-card">
            <h5><i class="bi bi-info-circle"></i> Informacije o Full-Text pretrazi</h5>
            <hr>
            <div class="row">
                <div class="col-6">
                    <h6>Pretražuje se u tablicama:</h6>
                    <ul>
                        <li><strong>hotels</strong>: naziv, adresa, grad (3 stupca)</li>
                        <li><strong>users</strong>: username, email (2 stupca)</li>
                    </ul>
                    <p class="mb-0"><small class="text-muted">Ukupno: 5 stupaca u 2 tablice</small></p>
                </div>
                <div class="col-6">
                    <h6>Boolean Mode sintaksa:</h6>
                    <ul>
                        <li><code>+word</code> - mora sadržavati</li>
                        <li><code>-word</code> - ne smije sadržavati</li>
                        <li><code>"phrase"</code> - točna fraza</li>
                    </ul>
                </div>
            </div>
            <div class="alert alert-info mt-3 mb-0">
                <small>
                    <i class="bi bi-lightbulb"></i> 
                    <strong>Napomena:</strong> Minimalna duljina pojma za pretragu je 3 znaka. 
                    Rezultati su rangirani po relevantnosti (MATCH AGAINST score).
                </small>
            </div>
        </div>
        
        <!-- Back Button -->
        <div class="text-center">
            <a href="index.php" class="btn btn-outline-light btn-lg">
                <i class="bi bi-house"></i> Povratak na početnu
            </a>
        </div>

<!-- Page-specific JavaScript -->
<script>
    function searchFor(term) {
        document.querySelector('.search-input').value = term;
        document.getElementById('searchForm').submit();
    }
</script>

<?php // Note: search.php uses custom CSS without Bootstrap ?>
</body>
</html>
<?php $connection->close(); ?>
