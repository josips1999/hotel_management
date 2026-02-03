<?php
/**
 * Security Component - HTTPS Badge
 * Prikazuje vizualni indikator HTTPS statusa
 */

require_once(__DIR__ . '/../lib/https_checker.php');

$isHTTPS = HTTPSChecker::isHTTPS();
$isLocalhost = HTTPSChecker::isLocalhost();
?>

<!-- HTTPS Status Badge -->
<div class="https-status-badge position-fixed" style="top: 10px; right: 10px; z-index: 9999;">
    <?php if ($isHTTPS): ?>
        <span class="badge bg-success" style="font-size: 0.9rem; padding: 8px 12px;">
            <i class="bi bi-shield-fill-check"></i> 
            Sigurna Konekcija (HTTPS)
        </span>
    <?php elseif ($isLocalhost): ?>
        <span class="badge bg-warning text-dark" style="font-size: 0.9rem; padding: 8px 12px;" 
              data-bs-toggle="tooltip" 
              title="Za produkciju omogući HTTPS!">
            <i class="bi bi-exclamation-triangle-fill"></i> 
            HTTP (Dev Mode)
        </span>
    <?php else: ?>
        <span class="badge bg-danger" style="font-size: 0.9rem; padding: 8px 12px;">
            <i class="bi bi-shield-fill-x"></i> 
            Nesigurna Konekcija!
        </span>
    <?php endif; ?>
    
    <a href="ssl_status.php" class="badge bg-secondary ms-2" style="font-size: 0.9rem; padding: 8px 12px; text-decoration: none;">
        <i class="bi bi-info-circle"></i> Status
    </a>
</div>

<!-- Tooltip Initialization -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

<style>
.https-status-badge {
    animation: fadeIn 0.5s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.https-status-badge .badge {
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    border: 2px solid rgba(255,255,255,0.3);
}

.https-status-badge .badge:hover {
    transform: scale(1.05);
    transition: transform 0.2s ease;
}
</style>
