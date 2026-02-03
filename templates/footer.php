    </div> <!-- End container -->
    
    <!-- Footer -->
    <footer class="mt-5 py-4" style="background-color: #2c3e50; color: #ecf0f1;">
        <div class="container-fluid">
            <div class="row justify-content-center text-center">
                <div class="col-md-3">
                    <h5><i class="bi bi-building"></i> Hotel Management</h5>
                    <p style="color: rgba(255,255,255,0.8);">Sustav za upravljanje hotelima</p>
                </div>
                <div class="col-md-3">
                    <h6>Tehnologije</h6>
                    <ul class="list-unstyled" style="color: rgba(255,255,255,0.8);">
                        <li><i class="bi bi-check-circle"></i> PHP 8.2</li>
                        <li><i class="bi bi-check-circle"></i> MySQL 8.0</li>
                        <li><i class="bi bi-check-circle"></i> Bootstrap 5</li>
                        <li><i class="bi bi-check-circle"></i> AJAX + JSON</li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h6>Značajke</h6>
                    <ul class="list-unstyled" style="color: rgba(255,255,255,0.8);">
                        <li><i class="bi bi-shield-check"></i> HTTPS Security</li>
                        <li><i class="bi bi-search"></i> Full-Text Search</li>
                        <li><i class="bi bi-lightning-charge"></i> AJAX Live Search</li>
                        <li><i class="bi bi-file-text"></i> Pagination</li>
                    </ul>
                </div>
            </div>
            <hr class="bg-secondary">
            <div class="text-center" style="color: rgba(255,255,255,0.6);">
                <small>&copy; <?php echo date('Y'); ?> Hotel Management System. All rights reserved.</small>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Cookie Manager -->
    <script src="assets/js/cookies.js"></script>
    
    <!-- Custom JS (if provided) -->
    <?php if (isset($customJS)): ?>
    <script><?php echo $customJS; ?></script>
    <?php endif; ?>
</body>
</html>

<?php
// Include cookie banner at the end of every page
include(__DIR__ . '/cookie-banner.php');
?>
