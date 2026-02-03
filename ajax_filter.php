<?php
/**
 * AJAX Hotel Filter Page
 * Filter hotels by city without page refresh using JSON
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
$guestMaxResults = 5;

// Page-specific variables for template
$pageTitle = 'AJAX Hotel Filter - Hotel Management';
$currentPage = 'ajax_filter';

// Custom CSS for this page
$customCSS = "
    .filter-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        padding: 30px;
        margin-bottom: 20px;
    }
    .hotel-card {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 15px;
        border-left: 4px solid #677ae6;
        transition: all 0.3s;
        animation: slideIn 0.3s;
    }
    .hotel-card:hover {
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }
    @keyframes slideIn {
        from { opacity: 0; transform: translateX(-20px); }
        to { opacity: 1; transform: translateX(0); }
    }
    .ajax-badge {
        background: #677ae6;
        color: white;
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 0.8rem;
    }
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }
    .filter-active {
        background: #677ae6;
        color: white;
    }
";

// ============================================================================
// HTML TEMPLATE
// ============================================================================
?>
<?php include 'templates/header.php'; ?>

<div class="text-center text-white mb-4">
    <h1 class="display-4 fw-bold">
        <i class="bi bi-funnel-fill"></i> AJAX Hotel Filter
    </h1>
    <p class="lead">Filtriranje bez osvježavanja stranice</p>
    <span class="ajax-badge">
        <i class="bi bi-gear-fill"></i> AJAX + JSON
    </span>
</div>

<!-- Filters -->
<div class="filter-card">
    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i> 
        <strong>AJAX Filter:</strong> Kliknite na grad ili županiju za filtriranje. 
        Rezultati se dohvaćaju u <strong>JSON formatu</strong> bez osvježavanja stranice.
    </div>
    
    <div class="row mb-3">
        <div class="col-md-4">
            <label class="form-label fw-bold">Filter po Gradu:</label>
            <div class="btn-group-vertical w-100" role="group">
                <button class="btn btn-outline-primary filter-btn" data-filter="grad" data-value="">
                    <i class="bi bi-x-circle"></i> Svi gradovi
                </button>
                <button class="btn btn-outline-primary filter-btn" data-filter="grad" data-value="Zagreb">
                    Zagreb
                </button>
                <button class="btn btn-outline-primary filter-btn" data-filter="grad" data-value="Split">
                    Split
                </button>
                <button class="btn btn-outline-primary filter-btn" data-filter="grad" data-value="Rijeka">
                    Rijeka
                </button>
                <button class="btn btn-outline-primary filter-btn" data-filter="grad" data-value="Osijek">
                    Osijek
                </button>
            </div>
        </div>
        
        <div class="col-md-4">
            <label class="form-label fw-bold">Sortiranje:</label>
            <select class="form-select" id="sortSelect">
                <option value="naziv_asc">Naziv (A-Z)</option>
                <option value="naziv_desc">Naziv (Z-A)</option>
                <option value="kapacitet_desc">Kapacitet (Najviše)</option>
                <option value="kapacitet_asc">Kapacitet (Najniže)</option>
                <option value="grad_asc">Grad (A-Z)</option>
            </select>
        </div>
        
        <div class="col-md-4">
            <label class="form-label fw-bold">Statistika:</label>
            <div class="card bg-light">
                <div class="card-body">
                    <p class="mb-1">
                        <i class="bi bi-building"></i> 
                        Hoteli: <strong id="totalCount">0</strong>
                    </p>
                    <p class="mb-0">
                        <i class="bi bi-clock"></i> 
                        Vrijeme: <strong id="loadTime">0</strong>ms
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <small class="text-muted" id="activeFilters">
                <i class="bi bi-filter"></i> Aktivni filteri: Nema
            </small>
        </div>
        <button class="btn btn-sm btn-secondary" id="resetBtn">
            <i class="bi bi-arrow-clockwise"></i> Reset
        </button>
    </div>
</div>

<!-- Results -->
<div class="filter-card">
    <h5 class="mb-3">
        <i class="bi bi-list-ul"></i> Rezultati
    </h5>
    <div id="results-container">
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    </div>
</div>

<div class="text-center">
    <a href="index.php" class="btn btn-outline-light btn-lg">
        <i class="bi bi-house"></i> Povratak
    </a>
</div>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="spinner-border text-light" style="width: 3rem; height: 3rem;" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
</div>

<!-- Page-specific JavaScript -->
<script>
    // Current filters
    let currentFilters = {
        grad: '',
        zupanija: '',
        sort: 'naziv_asc'
    };
    
    // Load hotels on page load
    document.addEventListener('DOMContentLoaded', function() {
        loadHotels();
    });
    
    // Filter buttons click
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const filterType = this.dataset.filter;
            const filterValue = this.dataset.value;
            
            // Update active state
            document.querySelectorAll(`[data-filter="${filterType}"]`).forEach(b => {
                b.classList.remove('filter-active');
            });
            this.classList.add('filter-active');
            
            // Update filter
            currentFilters[filterType] = filterValue;
            
            // Load hotels
            loadHotels();
        });
    });
    
    // Sort select change
    document.getElementById('sortSelect').addEventListener('change', function() {
        currentFilters.sort = this.value;
        loadHotels();
    });
    
    // Reset button
    document.getElementById('resetBtn').addEventListener('click', function() {
        currentFilters = { grad: '', zupanija: '', sort: 'naziv_asc' };
        document.getElementById('sortSelect').value = 'naziv_asc';
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.classList.remove('filter-active');
        });
        loadHotels();
    });
    
    // AJAX Load Hotels
    async function loadHotels() {
        const resultsContainer = document.getElementById('results-container');
        const loadingOverlay = document.getElementById('loadingOverlay');
        const totalCount = document.getElementById('totalCount');
        const loadTime = document.getElementById('loadTime');
        const activeFilters = document.getElementById('activeFilters');
        
        // Show loading
        loadingOverlay.style.display = 'flex';
        
        try {
            const startTime = performance.now();
            
            // Build query string
            const params = new URLSearchParams();
            if (currentFilters.grad) params.append('grad', currentFilters.grad);
            if (currentFilters.zupanija) params.append('zupanija', currentFilters.zupanija);
            params.append('sort', currentFilters.sort);
            
            // AJAX Request
            const response = await fetch(`api/ajax_filter.php?${params.toString()}`);
            const data = await response.json();
            
            const endTime = performance.now();
            const duration = Math.round(endTime - startTime);
            
            // Update stats
            totalCount.textContent = data.total || 0;
            loadTime.textContent = duration;
            
            // Update active filters text
            let filtersText = [];
            if (currentFilters.grad) filtersText.push(`Grad: ${currentFilters.grad}`);
            if (currentFilters.zupanija) filtersText.push(`Županija: ${currentFilters.zupanija}`);
            activeFilters.innerHTML = `<i class="bi bi-filter"></i> Aktivni filteri: ${filtersText.length > 0 ? filtersText.join(', ') : 'Nema'}`;
            
            // Render results
            renderHotels(data.hotels);
            
        } catch (error) {
            console.error('Filter error:', error);
            resultsContainer.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i> 
                    Greška: ${error.message}
                </div>
            `;
        } finally {
            loadingOverlay.style.display = 'none';
        }
    }
    
    // Render hotels
    function renderHotels(hotels) {
        const resultsContainer = document.getElementById('results-container');
        const isGuest = <?php echo $isGuest ? 'true' : 'false'; ?>;
        const maxGuestResults = <?php echo $guestMaxResults; ?>;
        
        if (!hotels || hotels.length === 0) {
            resultsContainer.innerHTML = `
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-inbox" style="font-size: 3rem; opacity: 0.3;"></i>
                    <p class="mt-3">Nema rezultata</p>
                </div>
            `;
            return;
        }
        
        let html = '';
        
        // Guest limitation warning
        if (isGuest && hotels.length >= maxGuestResults) {
            html += `
                <div class="alert alert-warning alert-dismissible fade show mb-3">
                    <i class="bi bi-eye-slash"></i> <strong>Ograničen pregled:</strong> 
                    Prikazano je samo <strong>${maxGuestResults}</strong> hotela. 
                    <a href="login.php" class="alert-link"><strong>Prijavite se</strong></a> 
                    za potpuni pristup.
                </div>
            `;
        }
        
        hotels.forEach(hotel => {
            html += `
                <div class="hotel-card">
                    <div class="row">
                        <div class="col-md-8">
                            <h5><i class="bi bi-building"></i> ${escapeHtml(hotel.naziv)}</h5>
                            <p class="mb-2">
                                <i class="bi bi-geo-alt"></i> ${escapeHtml(hotel.adresa)}, ${escapeHtml(hotel.grad)}
                            </p>
                            <small class="text-muted">
                                <i class="bi bi-map"></i> ${escapeHtml(hotel.zupanija)}
                            </small>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="mb-2">
                                <span class="badge bg-primary">
                                    <i class="bi bi-door-open"></i> ${hotel.broj_soba} soba
                                </span>
                            </div>
                            <div class="mb-2">
                                <span class="badge bg-success">
                                    <i class="bi bi-people"></i> ${hotel.kapacitet}
                                </span>
                            </div>
                            <div>
                                <span class="badge bg-info">
                                    <i class="bi bi-check-circle"></i> ${hotel.slobodno_soba} slobodno
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        
        resultsContainer.innerHTML = html;
    }
    
    // Escape HTML
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
</script>

<?php include 'templates/footer.php'; ?>
<?php $connection->close(); ?>
