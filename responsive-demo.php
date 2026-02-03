<?php
/**
 * Responsive Design Demo Page
 * Demonstrates @media queries, mobile table layouts, and screen adaptations
 */

// Start session and check authentication
require_once('lib/config.php');
require_once('lib/db_connection.php');
require_once('lib/SessionManager.php');

$sessionManager = new SessionManager($connection);
$isLoggedIn = $sessionManager->isLoggedIn();
$username = $isLoggedIn ? $sessionManager->getUsername() : null;

// Template variables
$pageTitle = 'Responsive Design Demo';
$currentPage = 'responsive_demo';

// Sample hotel data for table demos
$sampleHotels = [
    ['id' => 1, 'naziv' => 'Hotel Adriatic', 'adresa' => 'Obala Kneza Domagoja 1', 'grad' => 'Split', 'kapacitet' => 120, 'broj_soba' => 40, 'slobodno' => 15],
    ['id' => 2, 'naziv' => 'Hotel Central', 'adresa' => 'Trg Bana Jelačića 10', 'grad' => 'Zagreb', 'kapacitet' => 200, 'broj_soba' => 80, 'slobodno' => 0],
    ['id' => 3, 'naziv' => 'Hotel Marina', 'adresa' => 'Riječka obala 25', 'grad' => 'Rijeka', 'kapacitet' => 90, 'broj_soba' => 30, 'slobodno' => 8],
    ['id' => 4, 'naziv' => 'Hotel Panorama', 'adresa' => 'Europska avenija 108', 'grad' => 'Osijek', 'kapacitet' => 150, 'broj_soba' => 50, 'slobodno' => 22],
];

?>
<?php include 'templates/header.php'; ?>

<style>
/* Demo-specific styles */
.demo-hero {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 3rem 2rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 15px;
    margin-bottom: 2rem;
    text-align: center;
}

.demo-hero h1 {
    font-size: 2.5rem;
    margin-bottom: 1rem;
}

.screen-info {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    justify-content: center;
    margin-top: 1.5rem;
}

.screen-badge {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 1rem 1.5rem;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 10px;
    backdrop-filter: blur(10px);
}

.screen-badge-value {
    font-size: 1.5rem;
    font-weight: bold;
}

.screen-badge-label {
    font-size: 0.85rem;
    opacity: 0.9;
    margin-top: 0.25rem;
}

.demo-section {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    padding: 2rem;
    background: white;
    border-radius: 15px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    margin-bottom: 2rem;
}

.section-title {
    display: flex;
    align-items: center;
    gap: 1rem;
    font-size: 1.75rem;
    font-weight: bold;
    color: #667eea;
    border-bottom: 3px solid #667eea;
    padding-bottom: 0.75rem;
}

.breakpoint-demo {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.breakpoint-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 1.5rem;
    background: #f8f9fa;
    border-radius: 10px;
    border: 3px solid transparent;
}

.breakpoint-card.active {
    border-color: #667eea;
    background: #e0e7ff;
}

.breakpoint-icon {
    font-size: 2.5rem;
    margin-bottom: 0.5rem;
    color: #667eea;
}

.device-preview {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    justify-content: center;
    margin: 1.5rem 0;
}

.device-frame {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
}

.device-screen {
    border: 3px solid #333;
    border-radius: 10px;
    background: white;
    padding: 0.5rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.phone-screen {
    width: 120px;
    height: 200px;
}

.tablet-screen {
    width: 200px;
    height: 150px;
}

.desktop-screen {
    width: 300px;
    height: 180px;
}

.device-label {
    font-weight: 600;
    color: #495057;
}

@media print {
    .demo-hero,
    .breakpoint-demo,
    .device-preview,
    .no-print {
        display: none !important;
    }
    
    .demo-section {
        page-break-inside: avoid;
    }
}
</style>

<!-- Hero Section with Screen Info -->
<div class="demo-hero">
    <h1><i class="bi bi-phone"></i> Responsive Design Demo</h1>
    <p style="font-size: 1.1rem; max-width: 800px;">
        Demonstrates CSS @media queries for different screen resolutions, widths, orientations, 
        and mobile-friendly table layouts. <span class="orientation-indicator">Resize your browser or rotate your device to see changes</span>.
    </p>
    
    <div class="screen-info">
        <div class="screen-badge">
            <div class="screen-badge-value"><i class="bi bi-arrows-angle-expand"></i></div>
            <div class="screen-badge-label" id="screenWidth">Width: calculating...</div>
        </div>
        <div class="screen-badge">
            <div class="screen-badge-value"><i class="bi bi-arrows-vertical"></i></div>
            <div class="screen-badge-label" id="screenHeight">Height: calculating...</div>
        </div>
        <div class="screen-badge">
            <div class="screen-badge-value"><i class="bi bi-phone-landscape"></i></div>
            <div class="screen-badge-label" id="screenOrientation">Orientation: calculating...</div>
        </div>
        <div class="screen-badge">
            <div class="screen-badge-value"><i class="bi bi-display"></i></div>
            <div class="screen-badge-label" id="deviceType">Device: calculating...</div>
        </div>
    </div>
</div>

<!-- Breakpoint Visualization -->
<div class="demo-section">
    <h2 class="section-title">
        <i class="bi bi-tablet"></i>
        Active Breakpoint Detection
    </h2>
    <p>Current active breakpoint based on screen width:</p>
    
    <div class="breakpoint-demo">
        <div class="breakpoint-card" id="bp-mobile">
            <div class="breakpoint-icon"><i class="bi bi-phone"></i></div>
            <strong>Mobile</strong>
            <small>≤ 767px</small>
        </div>
        <div class="breakpoint-card" id="bp-tablet">
            <div class="breakpoint-icon"><i class="bi bi-tablet"></i></div>
            <strong>Tablet</strong>
            <small>768px - 991px</small>
        </div>
        <div class="breakpoint-card" id="bp-laptop">
            <div class="breakpoint-icon"><i class="bi bi-laptop"></i></div>
            <strong>Laptop</strong>
            <small>992px - 1199px</small>
        </div>
        <div class="breakpoint-card" id="bp-desktop">
            <div class="breakpoint-icon"><i class="bi bi-display"></i></div>
            <strong>Desktop</strong>
            <small>1200px - 1599px</small>
        </div>
        <div class="breakpoint-card" id="bp-large">
            <div class="breakpoint-icon"><i class="bi bi-tv"></i></div>
            <strong>Large Screen</strong>
            <small>≥ 1600px</small>
        </div>
    </div>
</div>

<!-- Device Preview -->
<div class="demo-section no-print">
    <h2 class="section-title">
        <i class="bi bi-devices"></i>
        Multi-Device Preview
    </h2>
    <p>Visual representation of how content adapts to different device sizes:</p>
    
    <div class="device-preview">
        <div class="device-frame">
            <div class="device-screen phone-screen">
                <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                    <div style="height: 15px; background: #667eea; border-radius: 3px;"></div>
                    <div style="height: 40px; background: #e0e7ff; border-radius: 3px;"></div>
                    <div style="height: 40px; background: #e0e7ff; border-radius: 3px;"></div>
                    <div style="height: 40px; background: #e0e7ff; border-radius: 3px;"></div>
                </div>
            </div>
            <div class="device-label"><i class="bi bi-phone"></i> Phone (Portrait)</div>
        </div>
        
        <div class="device-frame">
            <div class="device-screen tablet-screen">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.25rem; height: 100%;">
                    <div style="background: #667eea; border-radius: 3px; grid-column: 1/-1;"></div>
                    <div style="background: #e0e7ff; border-radius: 3px;"></div>
                    <div style="background: #e0e7ff; border-radius: 3px;"></div>
                </div>
            </div>
            <div class="device-label"><i class="bi bi-tablet"></i> Tablet (2 cols)</div>
        </div>
        
        <div class="device-frame">
            <div class="device-screen desktop-screen">
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.25rem; height: 100%;">
                    <div style="background: #667eea; border-radius: 3px; grid-column: 1/-1;"></div>
                    <div style="background: #e0e7ff; border-radius: 3px;"></div>
                    <div style="background: #e0e7ff; border-radius: 3px;"></div>
                    <div style="background: #e0e7ff; border-radius: 3px;"></div>
                    <div style="background: #e0e7ff; border-radius: 3px;"></div>
                </div>
            </div>
            <div class="device-label"><i class="bi bi-display"></i> Desktop (4 cols)</div>
        </div>
    </div>
</div>

<!-- Table Approach 1: Horizontal Scroll -->
<div class="demo-section">
    <h2 class="section-title">
        <i class="bi bi-arrow-left-right"></i>
        Table Approach 1: Horizontal Scroll
    </h2>
    <p><strong>Best for:</strong> Tables with many columns that must all be visible. <strong>Mobile:</strong> Swipe to scroll horizontally.</p>
    
    <div class="responsive-table-wrapper table-scroll-mobile">
        <table class="responsive-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Naziv Hotela</th>
                    <th>Adresa</th>
                    <th>Grad</th>
                    <th>Kapacitet</th>
                    <th>Broj Soba</th>
                    <th>Slobodno</th>
                    <th>Akcije</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sampleHotels as $hotel): ?>
                <tr>
                    <td><?php echo $hotel['id']; ?></td>
                    <td><strong><?php echo htmlspecialchars($hotel['naziv']); ?></strong></td>
                    <td><?php echo htmlspecialchars($hotel['adresa']); ?></td>
                    <td><?php echo htmlspecialchars($hotel['grad']); ?></td>
                    <td><?php echo $hotel['kapacitet']; ?></td>
                    <td><?php echo $hotel['broj_soba']; ?></td>
                    <td><?php echo $hotel['slobodno']; ?></td>
                    <td class="no-print">
                        <div class="btn-group">
                            <button class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.85rem;">
                                <i class="bi bi-eye"></i> Detalji
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <p class="show-mobile" style="display: none; color: #10b981; font-weight: 600; margin-top: 1rem;">
        <i class="bi bi-hand-index"></i> Swipe left/right to see all columns
    </p>
</div>

<!-- Table Approach 2: Stacked Cards -->
<div class="demo-section">
    <h2 class="section-title">
        <i class="bi bi-stack"></i>
        Table Approach 2: Stacked Cards
    </h2>
    <p><strong>Best for:</strong> Tables with moderate data that needs full visibility on mobile. <strong>Mobile:</strong> Each row becomes a card.</p>
    
    <div class="responsive-table-wrapper">
        <table class="responsive-table table-cards-mobile">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Naziv</th>
                    <th>Grad</th>
                    <th>Kapacitet</th>
                    <th>Sobe</th>
                    <th>Akcije</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sampleHotels as $hotel): ?>
                <tr>
                    <td data-label="ID"><?php echo $hotel['id']; ?></td>
                    <td data-label="Naziv"><strong><?php echo htmlspecialchars($hotel['naziv']); ?></strong></td>
                    <td data-label="Grad"><?php echo htmlspecialchars($hotel['grad']); ?></td>
                    <td data-label="Kapacitet"><?php echo $hotel['kapacitet']; ?></td>
                    <td data-label="Broj Soba"><?php echo $hotel['broj_soba']; ?></td>
                    <td data-label="Akcije" class="no-print">
                        <div class="btn-group">
                            <button class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.85rem;">
                                <i class="bi bi-eye"></i> Detalji
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Table Approach 3: Grid Layout -->
<div class="demo-section">
    <h2 class="section-title">
        <i class="bi bi-grid-3x3"></i>
        Table Approach 3: Grid Layout
    </h2>
    <p><strong>Best for:</strong> Tables with few columns. <strong>Mobile:</strong> 2-column grid within each card.</p>
    
    <div class="responsive-table-wrapper">
        <table class="responsive-table table-grid-mobile">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Naziv</th>
                    <th>Grad</th>
                    <th>Kapacitet</th>
                    <th>Slobodno</th>
                    <th>Akcije</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sampleHotels as $hotel): ?>
                <tr>
                    <td data-label="ID"><?php echo $hotel['id']; ?></td>
                    <td data-label="Naziv"><strong><?php echo htmlspecialchars($hotel['naziv']); ?></strong></td>
                    <td data-label="Grad"><?php echo htmlspecialchars($hotel['grad']); ?></td>
                    <td data-label="Kapacitet"><?php echo $hotel['kapacitet']; ?></td>
                    <td data-label="Slobodno"><?php echo $hotel['slobodno']; ?></td>
                    <td data-label="Akcije" class="actions-cell no-print">
                        <div class="btn-group">
                            <button class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.85rem;">
                                <i class="bi bi-eye"></i> Detalji
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Media Query Reference -->
<div class="demo-section">
    <h2 class="section-title">
        <i class="bi bi-code-square"></i>
        Implemented @media Queries
    </h2>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
        <div style="display: flex; flex-direction: column; padding: 1.5rem; background: #f8f9fa; border-radius: 10px; border-left: 4px solid #667eea;">
            <h3 style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
                <i class="bi bi-phone"></i> Screen Width
            </h3>
            <ul style="display: flex; flex-direction: column; gap: 0.5rem; list-style: none; padding: 0;">
                <li><code>max-width: 479px</code> - Extra small phones</li>
                <li><code>480px - 767px</code> - Phones</li>
                <li><code>768px - 991px</code> - Tablets</li>
                <li><code>992px - 1199px</code> - Laptops</li>
                <li><code>1200px - 1599px</code> - Desktops</li>
                <li><code>min-width: 1600px</code> - Large screens</li>
            </ul>
        </div>
        
        <div style="display: flex; flex-direction: column; padding: 1.5rem; background: #f8f9fa; border-radius: 10px; border-left: 4px solid #10b981;">
            <h3 style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
                <i class="bi bi-phone-landscape"></i> Orientation
            </h3>
            <ul style="display: flex; flex-direction: column; gap: 0.5rem; list-style: none; padding: 0;">
                <li><code>orientation: portrait</code> - Vertical</li>
                <li><code>orientation: landscape</code> - Horizontal</li>
                <li>Dynamic text indicators</li>
                <li>Layout adjustments per orientation</li>
            </ul>
        </div>
        
        <div style="display: flex; flex-direction: column; padding: 1.5rem; background: #f8f9fa; border-radius: 10px; border-left: 4px solid #f59e0b;">
            <h3 style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
                <i class="bi bi-stars"></i> Special Features
            </h3>
            <ul style="display: flex; flex-direction: column; gap: 0.5rem; list-style: none; padding: 0;">
                <li><code>@media print</code> - Print optimization</li>
                <li><code>prefers-color-scheme: dark</code> - Dark mode</li>
                <li><code>prefers-reduced-motion</code> - Accessibility</li>
                <li><code>prefers-contrast: high</code> - High contrast</li>
            </ul>
        </div>
    </div>
</div>

<!-- Print Preview Note -->
<div class="demo-section" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white;">
    <h2 style="display: flex; align-items: center; gap: 1rem; color: white; border-color: white; padding-bottom: 0.75rem; border-bottom: 3px solid white;">
        <i class="bi bi-printer"></i>
        Print Optimization
    </h2>
    <p style="font-size: 1.1rem;">
        Try printing this page (Ctrl+P or Cmd+P) to see print-specific @media query styles:
    </p>
    <ul style="display: flex; flex-direction: column; gap: 0.5rem; margin-left: 1.5rem;">
        <li>Navigation and buttons are hidden</li>
        <li>Single column layout for clarity</li>
        <li>Black borders for tables</li>
        <li>Optimized typography (12pt)</li>
        <li>Page break controls</li>
    </ul>
</div>

<script>
// Update screen information in real-time
function updateScreenInfo() {
    const width = window.innerWidth;
    const height = window.innerHeight;
    const orientation = width > height ? 'Landscape' : 'Portrait';
    
    let deviceType = 'Desktop';
    if (width <= 479) deviceType = 'Extra Small Phone';
    else if (width <= 767) deviceType = 'Phone';
    else if (width <= 991) deviceType = 'Tablet';
    else if (width <= 1199) deviceType = 'Laptop';
    else if (width <= 1599) deviceType = 'Desktop';
    else deviceType = 'Large Screen';
    
    document.getElementById('screenWidth').textContent = `Width: ${width}px`;
    document.getElementById('screenHeight').textContent = `Height: ${height}px`;
    document.getElementById('screenOrientation').textContent = `Orientation: ${orientation}`;
    document.getElementById('deviceType').textContent = `Device: ${deviceType}`;
    
    // Update breakpoint cards
    document.querySelectorAll('.breakpoint-card').forEach(card => card.classList.remove('active'));
    
    if (width <= 767) {
        document.getElementById('bp-mobile').classList.add('active');
    } else if (width <= 991) {
        document.getElementById('bp-tablet').classList.add('active');
    } else if (width <= 1199) {
        document.getElementById('bp-laptop').classList.add('active');
    } else if (width <= 1599) {
        document.getElementById('bp-desktop').classList.add('active');
    } else {
        document.getElementById('bp-large').classList.add('active');
    }
}

// Update on load and resize
updateScreenInfo();
window.addEventListener('resize', updateScreenInfo);
window.addEventListener('orientationchange', updateScreenInfo);

// Accordion table functionality
document.querySelectorAll('.table-accordion-mobile tr').forEach(row => {
    const firstCell = row.querySelector('td:first-child');
    if (firstCell) {
        firstCell.addEventListener('click', () => {
            row.classList.toggle('expanded');
        });
    }
});
</script>

<?php include 'templates/footer.php'; ?>
