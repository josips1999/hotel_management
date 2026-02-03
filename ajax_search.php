<?php
/**
 * AJAX Live Search Page
 * Real-time search without page refresh using JSON
 */

// ============================================================================
// PHP CODE - Business Logic (prije HTML-a)
// ============================================================================

require_once('lib/SessionManager.php');
require_once('lib/db_connection.php');

$sessionManager = new SessionManager($connection);
$sessionManager->checkRememberMe();
$isLoggedIn = $sessionManager->isLoggedIn();
$username = $sessionManager->getUsername();

// Guest limitations (unregistered users)
$isGuest = !$isLoggedIn;
$guestMaxResults = 5; // Limit to 5 results for guests

// Page-specific variables for template
$pageTitle = 'AJAX Live Search - Hotel Management';
$currentPage = 'ajax_search';

// Custom CSS for this page
$customCSS = "
    .search-container {
        max-width: 1000px;
        margin: 0 auto;
    }
    .search-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        padding: 40px;
        margin-bottom: 30px;
    }
    .search-input-group {
        position: relative;
        margin-bottom: 30px;
    }
    .search-input {
        height: 60px;
        font-size: 1.2rem;
        border-radius: 30px;
        padding: 0 30px 0 60px;
        border: 2px solid #677ae6;
    }
    .search-input:focus {
        border-color: #677ae6;
        box-shadow: 0 0 0 0.25rem rgba(103, 122, 230, 0.25);
    }
    .search-icon {
        position: absolute;
        left: 25px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 1.5rem;
        color: #677ae6;
        pointer-events: none;
    }
    .loading-spinner {
        position: absolute;
        right: 25px;
        top: 50%;
        transform: translateY(-50%);
        display: none;
    }
    .result-item {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 15px;
        border-left: 4px solid #677ae6;
        transition: all 0.3s;
        animation: fadeIn 0.3s;
    }
    .result-item:hover {
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        transform: translateX(5px);
    }
    mark {
        background-color: #ffd700;
        padding: 2px 4px;
        border-radius: 3px;
        font-weight: 600;
    }
    .ajax-badge {
        background: #677ae6;
        color: white;
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 0.8rem;
        display: inline-block;
        margin-bottom: 20px;
    }
    .no-results {
        text-align: center;
        padding: 40px;
        color: #6c757d;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .typing-indicator {
        color: #677ae6;
        font-size: 0.9rem;
        margin-top: 10px;
        display: none;
    }
    #results-container {
        min-height: 200px;
    }
";

// ============================================================================
// HTML TEMPLATE
// ============================================================================
?>
<?php include 'templates/header.php'; ?>

<div class="text-center text-white mb-4">
    <h1 class="display-4 fw-bold">
        <i class="bi bi-lightning-charge-fill"></i> AJAX Live Search
    </h1>
    <p class="lead">Pretraga bez osvježavanja stranice</p>
    <span class="ajax-badge">
        <i class="bi bi-gear-fill"></i> AJAX + JSON
    </span>
</div>

<div class="search-card">
    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i> 
        <strong>AJAX Live Search:</strong> Rezultati se dohvaćaju u realnom vremenu dok pišete, bez osvježavanja stranice. 
        Podaci se prenose u <strong>JSON formatu</strong>.
    </div>
    
    <div class="search-input-group">
        <i class="bi bi-search search-icon"></i>
        <input 
            type="text" 
            id="liveSearchInput"
            class="form-control search-input" 
            placeholder="Počnite tipkati za pretragu..."
            autocomplete="off"
            autofocus
        >
        <div class="loading-spinner">
            <div class="spinner-border spinner-border-sm text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    </div>
    
    <div class="typing-indicator" id="typingIndicator">
        <i class="bi bi-three-dots"></i> Pretraživanje...
    </div>
    
    <div id="searchStats" class="mb-3" style="display: none;">
        <small class="text-muted">
            <i class="bi bi-clock"></i> Vrijeme: <span id="searchTime">0</span>ms |
            <i class="bi bi-database"></i> Rezultati: <span id="resultCount">0</span>
        </small>
    </div>
    
    <div id="results-container">
        <div class="text-center text-muted py-5">
            <i class="bi bi-search" style="font-size: 3rem; opacity: 0.3;"></i>
            <p class="mt-3">Unesite minimalno 3 znaka za pretragu</p>
        </div>
    </div>
</div>

<div class="search-card">
    <h5><i class="bi bi-info-circle"></i> Tehnički Detalji</h5>
    <hr>
    <div class="row">
        <div class="col-md-6">
            <h6>AJAX Karakteristike:</h6>
            <ul>
                <li><strong>Fetch API</strong> - Modern JavaScript</li>
                <li><strong>JSON format</strong> - Server response</li>
                <li><strong>Debouncing</strong> - 500ms delay</li>
                <li><strong>Real-time</strong> - Bez refresh</li>
                <li><strong>Loading indicator</strong> - User feedback</li>
            </ul>
        </div>
        <div class="col-md-6">
            <h6>API Endpoint:</h6>
            <code>GET api/ajax_search.php?q=term</code>
            <h6 class="mt-3">Response Format:</h6>
            <pre class="bg-dark text-light p-2 rounded" style="font-size: 0.8rem;">
{
  "success": true,
  "hotels": [...],
  "users": [...],
  "total": 10,
  "time_ms": 45
}</pre>
        </div>
    </div>
</div>

<div class="text-center">
    <a href="index.php" class="btn btn-outline-light btn-lg">
        <i class="bi bi-house"></i> Povratak na početnu
    </a>
</div>

<!-- Page-specific JavaScript -->
<script>
    // AJAX Live Search Implementation
    const searchInput = document.getElementById('liveSearchInput');
    const resultsContainer = document.getElementById('results-container');
    const loadingSpinner = document.querySelector('.loading-spinner');
    const typingIndicator = document.getElementById('typingIndicator');
    const searchStats = document.getElementById('searchStats');
    const searchTime = document.getElementById('searchTime');
    const resultCount = document.getElementById('resultCount');
    
    let debounceTimer;
    let currentRequest = null;
    
    // Debounced search function (waits 500ms after user stops typing)
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.trim();
        
        // Clear previous timer
        clearTimeout(debounceTimer);
        
        // Show typing indicator
        if (searchTerm.length >= 3) {
            typingIndicator.style.display = 'block';
        } else {
            typingIndicator.style.display = 'none';
        }
        
        // Set new timer
        debounceTimer = setTimeout(() => {
            performSearch(searchTerm);
        }, 500); // 500ms delay
    });
    
    // Perform AJAX search
    async function performSearch(searchTerm) {
        typingIndicator.style.display = 'none';
        
        // Validate minimum length
        if (searchTerm.length < 3) {
            resultsContainer.innerHTML = `
                <div class="text-center text-muted py-5">
                    <i class="bi bi-search" style="font-size: 3rem; opacity: 0.3;"></i>
                    <p class="mt-3">Unesite minimalno 3 znaka za pretragu</p>
                </div>
            `;
            searchStats.style.display = 'none';
            return;
        }
        
        // Cancel previous request if exists
        if (currentRequest) {
            currentRequest.abort();
        }
        
        // Create new AbortController for this request
        const controller = new AbortController();
        currentRequest = controller;
        
        // Show loading
        loadingSpinner.style.display = 'block';
        searchStats.style.display = 'none';
        
        try {
            const startTime = performance.now();
            
            // AJAX Request (Fetch API)
            const response = await fetch(`api/ajax_search.php?q=${encodeURIComponent(searchTerm)}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json'
                },
                signal: controller.signal
            });
            
            // Parse JSON response
            const data = await response.json();
            
            const endTime = performance.now();
            const duration = Math.round(endTime - startTime);
            
            // Update stats
            searchTime.textContent = duration;
            resultCount.textContent = data.total || 0;
            searchStats.style.display = 'block';
            
            // Render results
            renderResults(data, searchTerm);
            
        } catch (error) {
            if (error.name === 'AbortError') {
                console.log('Request cancelled');
            } else {
                console.error('Search error:', error);
                resultsContainer.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i> 
                        Greška prilikom pretrage: ${error.message}
                    </div>
                `;
            }
        } finally {
            loadingSpinner.style.display = 'none';
            currentRequest = null;
        }
    }
    
    // Render search results
    function renderResults(data, searchTerm) {
        if (!data.success) {
            resultsContainer.innerHTML = `
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-circle"></i> ${data.error || 'Greška'}
                </div>
            `;
            return;
        }
        
        if (data.total === 0) {
            resultsContainer.innerHTML = `
                <div class="no-results">
                    <i class="bi bi-search" style="font-size: 3rem; opacity: 0.3;"></i>
                    <h4>Nema rezultata</h4>
                    <p>Pokušajte s drugim pojmom</p>
                </div>
            `;
            return;
        }
        
        let html = '';
        
        // Hotels
        if (data.hotels && data.hotels.length > 0) {
            html += `
                <h5 class="mb-3">
                    <i class="bi bi-building text-primary"></i> Hoteli 
                    <span class="badge bg-secondary">${data.hotels.length}</span>
                </h5>
            `;
            
            data.hotels.forEach(hotel => {
                html += `
                    <div class="result-item">
                        <div class="mb-2">
                            <span class="badge bg-primary me-2">Hotel</span>
                            <span class="text-muted">Relevance: ${hotel.relevance}</span>
                        </div>
                        <h5><i class="bi bi-building"></i> ${hotel.naziv_highlighted}</h5>
                        <p class="mb-2">
                            <i class="bi bi-geo-alt"></i> ${hotel.adresa_highlighted}, ${hotel.grad_highlighted}
                        </p>
                        <div class="row">
                            <div class="col-md-4">
                                <small class="text-muted">
                                    <i class="bi bi-door-open"></i> Sobe: <strong>${hotel.broj_soba}</strong>
                                </small>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted">
                                    <i class="bi bi-people"></i> Kapacitet: <strong>${hotel.kapacitet}</strong>
                                </small>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted">
                                    <i class="bi bi-check-circle"></i> Slobodno: <strong>${hotel.slobodno_soba}</strong>
                                </small>
                            </div>
                        </div>
                    </div>
                `;
            });
        }
        
        // Users
        if (data.users && data.users.length > 0 && !isGuest) {
            html += `
                <h5 class="mb-3 mt-4">
                    <i class="bi bi-people text-success"></i> Korisnici 
                    <span class="badge bg-secondary">${data.users.length}</span>
                </h5>
            `;
            
            data.users.forEach(user => {
                html += `
                    <div class="result-item">
                        <div class="mb-2">
                            <span class="badge bg-success me-2">Korisnik</span>
                            <span class="text-muted">Relevance: ${user.relevance}</span>
                        </div>
                        <h5>
                            <i class="bi bi-person-circle"></i> ${user.username_highlighted}
                        </h5>
                        <p class="mb-1">
                            <i class="bi bi-envelope"></i> ${user.email_highlighted}
                        </p>
                    </div>
                `;
            });
        }
        
        resultsContainer.innerHTML = html;
    }
    
    // Auto-focus on load
    searchInput.focus();
</script>

<?php include 'templates/footer.php'; ?>
<?php $connection->close(); ?>
