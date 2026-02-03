<?php
/**
 * Hotel Management System - Main Index Page
 * Displays list of all hotels with CRUD operations and pagination
 */

// ============================================================================
// PHP CODE - Business Logic (prije HTML-a)
// ============================================================================

// Start session FIRST
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once('lib/db_connection.php');
require_once('lib/config.php');
require_once('lib/SessionManager.php');
require_once('lib/Pagination.php');
require_once('lib/CSRFToken.php');
require_once('lib/SEOHelper.php');
require_once('app/controllers/HotelController.php');
mysqli_select_db($connection,'hotel_management');

// Initialize session manager with database connection
$sessionManager = new SessionManager($connection);

// Check remember me token from cookie
$sessionManager->checkRememberMe();

// Check if user is logged in
$isLoggedIn = $sessionManager->isLoggedIn();
$username = $sessionManager->getUsername();
$userId = $sessionManager->getUserId();

// Guest limitations (unregistered users)
$isGuest = !$isLoggedIn;
$guestMaxResults = 5; // Limit to 5 results for guests
$guestHiddenColumns = ['email', 'telefon', 'broj_gostiju', 'zupanija']; // Hidden columns for guests

// Get current page from URL (default: 1)
$currentPageNum = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

// For guests, always show page 1 with limited results
if ($isGuest) {
    $currentPageNum = 1;
    $itemsPerPage = $guestMaxResults;
} else {
    $itemsPerPage = ITEMS_PER_PAGE;
}

// Create controller instance
$controller = new HotelController($connection);

// Get hotels with pagination
$result = $controller->index($currentPageNum, $itemsPerPage);
$hotels = $result['data'] ?? [];
$paginationData = $result['pagination'] ?? [];

// For guests, limit to first N results
if ($isGuest && count($hotels) > $guestMaxResults) {
    $hotels = array_slice($hotels, 0, $guestMaxResults);
}

// Create Pagination instance
$pagination = new Pagination(
    $paginationData['total_items'] ?? 0,
    $currentPageNum,
    ITEMS_PER_PAGE
);

// Page-specific variables for template
$pageTitle = 'Hotel Management System';
$currentPage = 'index';

// ============================================================================
// HTML TEMPLATE
// ============================================================================
?>
<?php include 'templates/header.php'; ?>

<!-- Page-specific content -->
        <?php if ($isGuest): ?>
            <div class="alert alert-warning alert-dismissible fade show">
                <i class="bi bi-eye-slash"></i> <strong>Ograničen pregled:</strong> Prikazano je samo <strong><?php echo $guestMaxResults; ?></strong> hotela s ograničenim podacima. 
                <a href="login.php" class="alert-link"><strong>Prijavite se</strong></a> za potpuni pristup svim hotelima i detaljnim informacijama.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif ($isLoggedIn): ?>
            <div class="alert alert-info alert-dismissible fade show">
                <i class="bi bi-info-circle"></i> Prijavljeni ste kao <strong><?php echo htmlspecialchars($username ?? ''); ?></strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 style="color: #2d3748; font-weight: 600;">Lista Hotela</h2>
            <?php if ($isLoggedIn): ?>
                <button class="btn" data-bs-toggle="modal" data-bs-target="#addHotelModal" style="background-color: #2d3748; color: white; border: none; padding: 0.5rem 1.5rem; border-radius: 8px; font-weight: 500;">
                    Dodaj Hotel
                </button>
            <?php else: ?>
                <a href="login.php" class="btn" style="background-color: #2d3748; color: white; border: none; padding: 0.5rem 1.5rem; border-radius: 8px; font-weight: 500; text-decoration: none;">
                    Prijavite se za dodavanje hotela
                </a>
            <?php endif; ?>
        </div>

        <!-- Hotels Table -->
        <div class="responsive-table-wrapper table-cards-mobile">
            <table class="responsive-table hotels-table-mobile">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Naziv</th>
                                <th>Adresa</th>
                                <th>Grad</th>
                                <?php if (!$isGuest): ?>
                                <th class="hide-mobile">Županija</th>
                                <?php endif; ?>
                                <th>Kapacitet</th>
                                <th class="hide-mobile">Broj soba</th>
                                <?php if (!$isGuest): ?>
                                <th class="hide-mobile">Broj gostiju</th>
                                <?php endif; ?>
                                <th>Slobodno</th>
                                <?php if ($isLoggedIn): ?>
                                <th class="text-end no-print" style="width: 140px;">Akcije</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($hotels)): ?>
                                <?php foreach ($hotels as $hotel): ?>
                                <tr>
                                    <td data-label="ID"><?php echo $hotel['id']; ?></td>
                                    <td data-label="Naziv"><strong><?php echo htmlspecialchars($hotel['naziv']); ?></strong></td>
                                    <td data-label="Adresa"><?php echo htmlspecialchars($hotel['adresa']); ?></td>
                                    <td data-label="Grad"><?php echo htmlspecialchars($hotel['grad']); ?></td>
                                    <?php if (!$isGuest): ?>
                                    <td data-label="Županija" class="hide-mobile"><?php echo htmlspecialchars($hotel['zupanija']); ?></td>
                                    <?php endif; ?>
                                    <td data-label="Kapacitet"><?php echo $hotel['kapacitet']; ?></td>
                                    <td data-label="Broj soba" class="hide-mobile"><?php echo $hotel['broj_soba']; ?></td>
                                    <?php if (!$isGuest): ?>
                                    <td data-label="Broj gostiju" class="hide-mobile"><span style="background-color: #e2e8f0; color: #4a5568; padding: 0.25rem 0.75rem; border-radius: 6px; font-size: 0.875rem; font-weight: 500;"><?php echo $hotel['broj_gostiju']; ?></span></td>
                                    <?php endif; ?>
                                    <td data-label="Slobodno"><span style="background-color: #e2e8f0; color: #4a5568; padding: 0.25rem 0.75rem; border-radius: 6px; font-size: 0.875rem; font-weight: 500;"><?php echo $hotel['slobodno_soba']; ?></span></td>
                                    <?php if ($isLoggedIn): ?>
                                    <td class="text-end no-print">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button class="btn" onclick="editHotel(<?php echo $hotel['id']; ?>)" title="Uredi hotel" style="background-color: #f7fafc; color: #4a5568; border: 1px solid #e2e8f0; padding: 0.25rem 0.75rem; border-radius: 6px; font-size: 0.875rem; margin-right: 0.5rem;">
                                                Uredi
                                            </button>
                                            <button class="btn" onclick="deleteHotel(<?php echo $hotel['id']; ?>)" title="Obriši hotel" style="background-color: #f7fafc; color: #e53e3e; border: 1px solid #e2e8f0; padding: 0.25rem 0.75rem; border-radius: 6px; font-size: 0.875rem;">
                                                Obriši
                                            </button>
                                        </div>
                                    </td>
                                    <?php endif; ?>
        
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="<?php echo $isGuest ? '8' : '10'; ?>" class="text-center">Nema hotela u bazi podataka</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    
                    <!-- Pagination Info -->
                    <div class="d-flex justify-content-between align-items-center mt-3 px-3">
                        <div>
                            <?php echo $pagination->renderInfo(); ?>
                        </div>
                        <div>
                            <!-- Pagination Controls -->
                            <?php echo $pagination->render('', 'end'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div id="toastContainer" style="position: fixed; top: 20px; right: 20px; z-index: 9999;"></div>

    <!-- Add Hotel Modal -->
    <div class="modal fade" id="addHotelModal" tabindex="-1" aria-labelledby="addHotelModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 800px;">
            <div class="modal-content" style="border-radius: 12px; overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.15); border: 1px solid #e2e8f0;">
                <div style="background: #f7fafc; color: #2d3748; padding: 1.5rem 2rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0;">
                    <h5 style="font-weight: 600; font-size: 1.25rem; margin: 0; color: #2d3748;">
                        Dodaj Novi Hotel
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="background: transparent; border: none; color: #4a5568; font-size: 1.5rem; cursor: pointer; padding: 0; width: 1.5rem; height: 1.5rem; opacity: 0.6;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.6'">×</button>
                </div>
                <form id="addHotelForm" method="POST" action="api/add_hotel.php">
                    
                    <!-- CSRF Token (Requirement 33) -->
                    <?php echo CSRFToken::getField(); ?>
                    
                    <div style="padding: 2rem; background-color: #ffffff;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                            <div>
                                <label style="display: block; font-weight: 500; color: #4a5568; margin-bottom: 0.5rem; font-size: 0.875rem;">Naziv hotela *</label>
                                <input type="text" id="add_naziv" name="naziv" required 
                                       style="width: 100%; padding: 0.625rem 0.875rem; border: 1px solid #e2e8f0; border-radius: 6px; background-color: #ffffff; color: #2d3748; font-size: 0.9375rem; transition: all 0.2s ease; box-sizing: border-box; font-family: inherit;"
                                       onfocus="this.style.borderColor='#cbd5e0'; this.style.boxShadow='0 0 0 3px rgba(226,232,240,0.4)'"
                                       onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'">
                            </div>
                            <div>
                                <label style="display: block; font-weight: 500; color: #4a5568; margin-bottom: 0.5rem; font-size: 0.875rem;">Županija *</label>
                                <select id="add_zupanija" name="zupanija" required 
                                        style="width: 100%; padding: 0.625rem 0.875rem; border: 1px solid #e2e8f0; border-radius: 6px; background-color: #ffffff; color: #2d3748; font-size: 0.9375rem; transition: all 0.2s ease; box-sizing: border-box; font-family: inherit;"
                                        onfocus="this.style.borderColor='#cbd5e0'; this.style.boxShadow='0 0 0 3px rgba(226,232,240,0.4)'"
                                        onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'">
                                    <option value="">-- Odaberite županiju --</option>
                                    <option value="Grad Zagreb">Grad Zagreb</option>
                                    <option value="Zagrebačka">Zagrebačka</option>
                                    <option value="Splitsko-dalmatinska">Splitsko-dalmatinska</option>
                                    <option value="Primorsko-goranska">Primorsko-goranska</option>
                                    <option value="Istarska">Istarska</option>
                                    <option value="Zadarska">Zadarska</option>
                                    <option value="Dubrovačko-neretvanska">Dubrovačko-neretvanska</option>
                                </select>
                            </div>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                            <div>
                                <label style="display: block; font-weight: 500; color: #4a5568; margin-bottom: 0.5rem; font-size: 0.875rem;">Adresa *</label>
                                <input type="text" id="add_adresa" name="adresa" required 
                                       style="width: 100%; padding: 0.625rem 0.875rem; border: 1px solid #e2e8f0; border-radius: 6px; background-color: #ffffff; color: #2d3748; font-size: 0.9375rem; transition: all 0.2s ease; box-sizing: border-box; font-family: inherit;"
                                       onfocus="this.style.borderColor='#cbd5e0'; this.style.boxShadow='0 0 0 3px rgba(226,232,240,0.4)'"
                                       onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'">
                            </div>
                            <div>
                                <label style="display: block; font-weight: 500; color: #4a5568; margin-bottom: 0.5rem; font-size: 0.875rem;">Grad *</label>
                                <input type="text" id="add_grad" name="grad" required 
                                       style="width: 100%; padding: 0.625rem 0.875rem; border: 1px solid #e2e8f0; border-radius: 6px; background-color: #ffffff; color: #2d3748; font-size: 0.9375rem; transition: all 0.2s ease; box-sizing: border-box; font-family: inherit;"
                                       onfocus="this.style.borderColor='#cbd5e0'; this.style.boxShadow='0 0 0 3px rgba(226,232,240,0.4)'"
                                       onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'">
                            </div>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                            <div>
                                <label style="display: block; font-weight: 500; color: #4a5568; margin-bottom: 0.5rem; font-size: 0.875rem;">Kapacitet *</label>
                                <input type="number" id="add_kapacitet" name="kapacitet" min="10" max="10000" required 
                                       style="width: 100%; padding: 0.625rem 0.875rem; border: 1px solid #e2e8f0; border-radius: 6px; background-color: #ffffff; color: #2d3748; font-size: 0.9375rem; transition: all 0.2s ease; box-sizing: border-box; font-family: inherit;"
                                       onfocus="this.style.borderColor='#cbd5e0'; this.style.boxShadow='0 0 0 3px rgba(226,232,240,0.4)'"
                                       onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'">
                            </div>
                            <div>
                                <label style="display: block; font-weight: 500; color: #4a5568; margin-bottom: 0.5rem; font-size: 0.875rem;">Broj soba *</label>
                                <input type="number" id="add_broj_soba" name="broj_soba" min="5" max="5000" required 
                                       style="width: 100%; padding: 0.625rem 0.875rem; border: 1px solid #e2e8f0; border-radius: 6px; background-color: #ffffff; color: #2d3748; font-size: 0.9375rem; transition: all 0.2s ease; box-sizing: border-box; font-family: inherit;"
                                       onfocus="this.style.borderColor='#cbd5e0'; this.style.boxShadow='0 0 0 3px rgba(226,232,240,0.4)'"
                                       onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'">
                            </div>
                        </div>
                    </div>
                    <div style="padding: 1.25rem 2rem; background-color: #f7fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 0.75rem;">
                        <button type="button" data-bs-dismiss="modal" 
                                style="padding: 0.625rem 1.5rem; border-radius: 6px; font-weight: 500; border: 1px solid #e2e8f0; background-color: #ffffff; color: #4a5568; cursor: pointer; transition: all 0.2s ease; font-family: inherit; font-size: 0.9375rem;"
                                onmouseover="this.style.backgroundColor='#f7fafc'; this.style.borderColor='#cbd5e0'"
                                onmouseout="this.style.backgroundColor='#ffffff'; this.style.borderColor='#e2e8f0'">
                            Odustani
                        </button>
                        <button type="submit" 
                                style="padding: 0.625rem 1.5rem; border-radius: 6px; font-weight: 500; background: #2d3748; border: none; color: white; cursor: pointer; transition: all 0.2s ease; font-family: inherit; font-size: 0.9375rem;"
                                onmouseover="this.style.backgroundColor='#1a202c'"
                                onmouseout="this.style.backgroundColor='#2d3748'">
                            Spremi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Hotel Modal -->
    <div class="modal fade" id="editHotelModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 800px;">
            <div class="modal-content" style="border-radius: 12px; overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.15); border: 1px solid #e2e8f0;">
                <div style="background: #f7fafc; color: #2d3748; padding: 1.5rem 2rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0;">
                    <h5 style="font-weight: 600; font-size: 1.25rem; margin: 0; color: #2d3748;">
                        Uredi Hotel
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="background: transparent; border: none; color: #4a5568; font-size: 1.5rem; cursor: pointer; padding: 0; width: 1.5rem; height: 1.5rem; opacity: 0.6;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.6'">×</button>
                </div>
                <form method="POST" action="api/update_hotel.php">
                    
                    <!-- CSRF Token (Requirement 33) -->
                    <?php echo CSRFToken::getField(); ?>
                    
                    <input type="hidden" name="id" id="edit_id">
                    <div style="padding: 2rem; background-color: #ffffff;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                            <div>
                                <label style="display: block; font-weight: 500; color: #4a5568; margin-bottom: 0.5rem; font-size: 0.875rem;">Naziv *</label>
                                <input type="text" name="naziv" id="edit_naziv" required 
                                       style="width: 100%; padding: 0.625rem 0.875rem; border: 1px solid #e2e8f0; border-radius: 6px; background-color: #ffffff; color: #2d3748; font-size: 0.9375rem; transition: all 0.2s ease; box-sizing: border-box; font-family: inherit;"
                                       onfocus="this.style.borderColor='#cbd5e0'; this.style.boxShadow='0 0 0 3px rgba(226,232,240,0.4)'"
                                       onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'">
                            </div>
                            <div>
                                <label style="display: block; font-weight: 500; color: #4a5568; margin-bottom: 0.5rem; font-size: 0.875rem;">Županija *</label>
                                <select name="zupanija" id="edit_zupanija" required 
                                        style="width: 100%; padding: 0.625rem 0.875rem; border: 1px solid #e2e8f0; border-radius: 6px; background-color: #ffffff; color: #2d3748; font-size: 0.9375rem; transition: all 0.2s ease; box-sizing: border-box; font-family: inherit;"
                                        onfocus="this.style.borderColor='#cbd5e0'; this.style.boxShadow='0 0 0 3px rgba(226,232,240,0.4)'"
                                        onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'">
                                    <option value="Grad Zagreb">Grad Zagreb</option>
                                    <option value="Zagrebačka">Zagrebačka</option>
                                    <option value="Splitsko-dalmatinska">Splitsko-dalmatinska</option>
                                    <option value="Primorsko-goranska">Primorsko-goranska</option>
                                    <option value="Istarska">Istarska</option>
                                    <option value="Zadarska">Zadarska</option>
                                    <option value="Dubrovačko-neretvanska">Dubrovačko-neretvanska</option>
                                </select>
                            </div>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                            <div>
                                <label style="display: block; font-weight: 500; color: #4a5568; margin-bottom: 0.5rem; font-size: 0.875rem;">Adresa *</label>
                                <input type="text" name="adresa" id="edit_adresa" required 
                                       style="width: 100%; padding: 0.625rem 0.875rem; border: 1px solid #e2e8f0; border-radius: 6px; background-color: #ffffff; color: #2d3748; font-size: 0.9375rem; transition: all 0.2s ease; box-sizing: border-box; font-family: inherit;"
                                       onfocus="this.style.borderColor='#cbd5e0'; this.style.boxShadow='0 0 0 3px rgba(226,232,240,0.4)'"
                                       onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'">
                            </div>
                            <div>
                                <label style="display: block; font-weight: 500; color: #4a5568; margin-bottom: 0.5rem; font-size: 0.875rem;">Grad *</label>
                                <input type="text" name="grad" id="edit_grad" required 
                                       style="width: 100%; padding: 0.625rem 0.875rem; border: 1px solid #e2e8f0; border-radius: 6px; background-color: #ffffff; color: #2d3748; font-size: 0.9375rem; transition: all 0.2s ease; box-sizing: border-box; font-family: inherit;"
                                       onfocus="this.style.borderColor='#cbd5e0'; this.style.boxShadow='0 0 0 3px rgba(226,232,240,0.4)'"
                                       onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'">
                            </div>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                            <div>
                                <label style="display: block; font-weight: 500; color: #4a5568; margin-bottom: 0.5rem; font-size: 0.875rem;">Kapacitet *</label>
                                <input type="number" name="kapacitet" id="edit_kapacitet" required 
                                       style="width: 100%; padding: 0.625rem 0.875rem; border: 1px solid #e2e8f0; border-radius: 6px; background-color: #ffffff; color: #2d3748; font-size: 0.9375rem; transition: all 0.2s ease; box-sizing: border-box; font-family: inherit;"
                                       onfocus="this.style.borderColor='#cbd5e0'; this.style.boxShadow='0 0 0 3px rgba(226,232,240,0.4)'"
                                       onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'">
                            </div>
                            <div>
                                <label style="display: block; font-weight: 500; color: #4a5568; margin-bottom: 0.5rem; font-size: 0.875rem;">Broj soba *</label>
                                <input type="number" name="broj_soba" id="edit_broj_soba" required 
                                       style="width: 100%; padding: 0.625rem 0.875rem; border: 1px solid #e2e8f0; border-radius: 6px; background-color: #ffffff; color: #2d3748; font-size: 0.9375rem; transition: all 0.2s ease; box-sizing: border-box; font-family: inherit;"
                                       onfocus="this.style.borderColor='#cbd5e0'; this.style.boxShadow='0 0 0 3px rgba(226,232,240,0.4)'"
                                       onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'">
                            </div>
                        </div>
                    </div>
                    <div style="padding: 1.25rem 2rem; background-color: #f7fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 0.75rem;">
                        <button type="button" data-bs-dismiss="modal" 
                                style="padding: 0.625rem 1.5rem; border-radius: 6px; font-weight: 500; border: 1px solid #e2e8f0; background-color: #ffffff; color: #4a5568; cursor: pointer; transition: all 0.2s ease; font-family: inherit; font-size: 0.9375rem;"
                                onmouseover="this.style.backgroundColor='#f7fafc'; this.style.borderColor='#cbd5e0'"
                                onmouseout="this.style.backgroundColor='#ffffff'; this.style.borderColor='#e2e8f0'">
                            Odustani
                        </button>
                        <button type="submit" 
                                style="padding: 0.625rem 1.5rem; border-radius: 6px; font-weight: 500; background: #2d3748; border: none; color: white; cursor: pointer; transition: all 0.2s ease; font-family: inherit; font-size: 0.9375rem;"
                                onmouseover="this.style.backgroundColor='#1a202c'"
                                onmouseout="this.style.backgroundColor='#2d3748'">
                            Ažuriraj
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Escape HTML karakteri za sigurnost
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        // Toast Notification System
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.style.cssText = `
                background: ${type === 'success' ? '#2d3748' : '#e53e3e'};
                color: white;
                padding: 1rem 1.5rem;
                border-radius: 8px;
                margin-bottom: 0.75rem;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                display: flex;
                align-items: flex-start;
                gap: 0.75rem;
                min-width: 300px;
                max-width: 450px;
                font-size: 0.9375rem;
                font-weight: 500;
                animation: slideIn 0.3s ease-out;
                position: relative;
                overflow: hidden;
                line-height: 1.5;
            `;
            
            // Add icon
            const icon = type === 'success' ? '✓' : '✕';
            toast.innerHTML = `
                <span style="font-size: 1.25rem; font-weight: 600; flex-shrink: 0;">${icon}</span>
                <span style="flex: 1;">${message}</span>
            `;
            
            document.getElementById('toastContainer').appendChild(toast);
            
            // Auto remove after 2 seconds
            setTimeout(() => {
                toast.style.animation = 'slideOut 0.3s ease-out';
                setTimeout(() => toast.remove(), 300);
            }, 2000);
        }
        
        // Add CSS animations
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from {
                    transform: translateY(-20px);
                    opacity: 0;
                }
                to {
                    transform: translateY(0);
                    opacity: 1;
                }
            }
            @keyframes slideOut {
                from {
                    transform: translateY(0);
                    opacity: 1;
                }
                to {
                    transform: translateY(-20px);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);

        function validateAddForm() {
            // Basic client-side validation
            return true;
        }

        function editHotel(id) {
            fetch(`api/get_hotel.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const hotel = data.data;
                        document.getElementById('edit_id').value = hotel.id;
                        document.getElementById('edit_naziv').value = hotel.naziv;
                        document.getElementById('edit_adresa').value = hotel.adresa;
                        document.getElementById('edit_grad').value = hotel.grad;
                        document.getElementById('edit_zupanija').value = hotel.zupanija;
                        document.getElementById('edit_kapacitet').value = hotel.kapacitet;
                        document.getElementById('edit_broj_soba').value = hotel.broj_soba;
                        
                        new bootstrap.Modal(document.getElementById('editHotelModal')).show();
                    } else {
                        showToast(data.message || 'Greška pri učitavanju hotela', 'error');
                    }
                })
                .catch(error => showToast('Greška: ' + error, 'error'));
        }

        function deleteHotel(id) {
            if (confirm('Jeste li sigurni da želite obrisati ovaj hotel?')) {
                // Get CSRF token
                const csrfToken = document.querySelector('input[name="csrf_token"]')?.value || '';
                
                // Send POST request
                fetch('api/delete_hotel.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `id=${id}&csrf_token=${csrfToken}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message || 'Hotel uspješno obrisan!', 'success');
                        
                        // Ukloni red iz tablice
                        const rows = document.querySelectorAll('.hotels-table-mobile tbody tr');
                        rows.forEach(row => {
                            if (row.cells[0] && row.cells[0].textContent == id) {
                                row.remove();
                            }
                        });
                        
                        // Ako nema više hotela, prikaži "Nema hotela"
                        const tbody = document.querySelector('.hotels-table-mobile tbody');
                        if (tbody.rows.length === 0) {
                            const emptyRow = tbody.insertRow();
                            emptyRow.innerHTML = '<td colspan="<?php echo $isGuest ? "8" : "10"; ?>" class="text-center">Nema hotela u bazi podataka</td>';
                        }
                    } else {
                        showToast(data.message || 'Greška pri brisanju hotela', 'error');
                    }
                })
                .catch(error => {
                    showToast('Greška pri brisanju: ' + error, 'error');
                });
            }
        }

        // Client-side validation function
        function validateAddHotelForm() {
            const naziv = document.getElementById('add_naziv').value.trim();
            const adresa = document.getElementById('add_adresa').value.trim();
            const grad = document.getElementById('add_grad').value.trim();
            const zupanija = document.getElementById('add_zupanija').value;
            const kapacitet = parseInt(document.getElementById('add_kapacitet').value);
            const brojSoba = parseInt(document.getElementById('add_broj_soba').value);
            
            const errors = [];
            
            // 1. Provjera minimalnog broja znakova - naziv (min 3 znaka)
            if (naziv.length < 3) {
                errors.push('Naziv hotela mora imati minimalno 3 znaka');
                document.getElementById('add_naziv').style.borderColor = '#e53e3e';
            } else {
                document.getElementById('add_naziv').style.borderColor = '#e2e8f0';
            }
            
            // 2. Provjera minimalnog broja znakova - adresa (min 5 znakova)
            if (adresa.length < 5) {
                errors.push('Adresa mora imati minimalno 5 znakova');
                document.getElementById('add_adresa').style.borderColor = '#e53e3e';
            } else {
                document.getElementById('add_adresa').style.borderColor = '#e2e8f0';
            }
            
            // 3. Provjera minimalnog broja znakova - grad (min 2 znaka)
            if (grad.length < 2) {
                errors.push('Grad mora imati minimalno 2 znaka');
                document.getElementById('add_grad').style.borderColor = '#e53e3e';
            } else {
                document.getElementById('add_grad').style.borderColor = '#e2e8f0';
            }
            
            // 4. Provjera da je županija odabrana
            if (!zupanija) {
                errors.push('Morate odabrati županiju');
                document.getElementById('add_zupanija').style.borderColor = '#e53e3e';
            } else {
                document.getElementById('add_zupanija').style.borderColor = '#e2e8f0';
            }
            
            // 5. Provjera raspona brojeva - kapacitet (10-10000)
            if (isNaN(kapacitet) || kapacitet < 10 || kapacitet > 10000) {
                errors.push('Kapacitet mora biti između 10 i 10000');
                document.getElementById('add_kapacitet').style.borderColor = '#e53e3e';
            } else {
                document.getElementById('add_kapacitet').style.borderColor = '#e2e8f0';
            }
            
            // 6. Provjera raspona brojeva - broj soba (5-5000)
            if (isNaN(brojSoba) || brojSoba < 5 || brojSoba > 5000) {
                errors.push('Broj soba mora biti između 5 i 5000');
                document.getElementById('add_broj_soba').style.borderColor = '#e53e3e';
            } else {
                document.getElementById('add_broj_soba').style.borderColor = '#e2e8f0';
            }
            
            // 7. Logička provjera - kapacitet mora biti veći od broja soba
            if (!isNaN(kapacitet) && !isNaN(brojSoba) && kapacitet < brojSoba) {
                errors.push('Kapacitet mora biti veći ili jednak broju soba');
            }
            
            return errors;
        }
        
        // Real-time validation on input
        ['add_naziv', 'add_adresa', 'add_grad', 'add_zupanija', 'add_kapacitet', 'add_broj_soba'].forEach(id => {
            const element = document.getElementById(id);
            if (element) {
                element.addEventListener('blur', function() {
                    validateAddHotelForm();
                });
            }
        });
        
        // Handle add hotel form submission
        document.getElementById('addHotelForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Client-side validation
            const errors = validateAddHotelForm();
            
            if (errors.length > 0) {
                showToast(errors.join('<br>'), 'error');
                return;
            }
            
            const formData = new FormData(this);
            
            fetch('api/add_hotel.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Close modal immediately
                    const modal = bootstrap.Modal.getInstance(document.getElementById('addHotelModal'));
                    if (modal) modal.hide();
                    
                    showToast(data.message || 'Hotel uspješno dodan!', 'success');
                    setTimeout(() => location.reload(), 300);
                } else {
                    const messages = data.messages || [data.message] || ['Greška pri dodavanju hotela'];
                    showToast(messages.join(', '), 'error');
                }
            })
            .catch(error => {
                showToast('Greška pri dodavanju hotela: ' + error, 'error');
            });
        });

        // Handle edit hotel form submission
        document.querySelector('#editHotelModal form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('api/update_hotel.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Close modal immediately
                    const modal = bootstrap.Modal.getInstance(document.getElementById('editHotelModal'));
                    if (modal) modal.hide();
                    
                    showToast(data.message || 'Hotel uspješno ažuriran!', 'success');
                    setTimeout(() => location.reload(), 300);
                } else {
                    showToast(data.message || 'Greška pri ažuriranju hotela', 'error');
                }
            })
            .catch(error => {
                showToast('Greška pri ažuriranju: ' + error, 'error');
            });
        });
    </script>

<?php include 'templates/footer.php'; ?>
<?php $connection->close(); ?>
