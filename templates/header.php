<?php
/**
 * Header Template
 * 
 * Includes: HTML head, navigation, common CSS
 * Variables required:
 * - $pageTitle (string) - Page title
 * - $currentPage (string) - Current page identifier for active nav
 * - $isLoggedIn (bool) - User login status
 * - $username (string|null) - Logged in username
 */

if (!isset($pageTitle)) $pageTitle = 'Hotel Management System';
if (!isset($currentPage)) $currentPage = '';
if (!isset($isLoggedIn)) $isLoggedIn = false;
if (!isset($username)) $username = null;
?>
<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Custom CSS Grid & Flexbox Layout -->
    <link rel="stylesheet" href="assets/css/grid-flexbox.css">
    <link rel="stylesheet" href="assets/css/hotel-cards.css">
    
    <!-- Responsive Design with @media queries -->
    <link rel="stylesheet" href="assets/css/responsive.css">
    <link rel="stylesheet" href="assets/css/mobile-tables.css">
    
    <!-- Cookie Banner & Terms Styles -->
    <link rel="stylesheet" href="assets/css/cookies.css">
    
    <!-- Custom CSS (if provided) -->
    <?php if (isset($customCSS)): ?>
    <style><?php echo $customCSS; ?></style>
    <?php endif; ?>
    
    <style>
        /* Custom Navbar Styling */
        .navbar {
            background-color: #ffffff !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .navbar-brand,
        .navbar-nav .nav-link,
        .navbar-nav .dropdown-toggle {
            color: #4a4a4a !important;
        }
        .navbar-nav .nav-link:hover,
        .navbar-nav .dropdown-toggle:hover {
            color: #677ae6 !important;
        }
        .navbar-nav .nav-link.active {
            color: #677ae6 !important;
            font-weight: 500;
        }
        .navbar-toggler {
            border-color: #4a4a4a;
        }
        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(74, 74, 74, 0.75)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }
        .dropdown-menu .dropdown-item {
            color: #4a4a4a;
        }
        .dropdown-menu .dropdown-item:hover {
            background-color: #f8f9fa;
            color: #677ae6;
        }
        body {
            background-color: #ffffff;
        }
        .container {
            background-color: #ffffff;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container-fluid px-4">
            <a class="navbar-brand" href="index.php">
                Hotel Management
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage === 'index' ? 'active' : ''; ?>" href="index.php">
                            Hoteli
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage === 'ajax_search' ? 'active' : ''; ?>" href="ajax_search.php">
                            AJAX Search
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage === 'ajax_filter' ? 'active' : ''; ?>" href="ajax_filter.php">
                            AJAX Filter
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage === 'responsive_demo' ? 'active' : ''; ?>" href="responsive-demo.php">
                            Responsive
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage === 'responsive_demo' ? 'active' : ''; ?>" href="responsive-demo.php">
                            Responsive
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage === 'search' ? 'active' : ''; ?>" href="search.php">
                            Pretraga
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage === 'statistics' ? 'active' : ''; ?>" href="statistics.php">
                            Statistika
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage === 'contact' ? 'active' : ''; ?>" href="contact.php">
                            Kontakt
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="rss.php" target="_blank" title="RSS Feed">
                            RSS
                        </a>
                    </li>
                    <?php if ($isLoggedIn): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage === 'audit_log' ? 'active' : ''; ?>" href="audit_log.php">
                            Audit Log
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage === 'update_boravak' ? 'active' : ''; ?>" href="update_boravak.php">
                            Ažuriranje Boravka
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <!-- Documentation Links -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="docsDropdown" role="button" data-bs-toggle="dropdown">
                            Docs
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="dokumentacija.html">Dokumentacija</a></li>
                            <li><a class="dropdown-item" href="autor.html">O Autoru</a></li>
                            <li><a class="dropdown-item" href="security_report.html">Sigurnost</a></li>
                        </ul>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <?php if ($isLoggedIn): ?>
                        <li class="nav-item d-flex flex-column align-items-end">
                            <span class="navbar-text text-muted small">
                                Admin: <?php echo htmlspecialchars($username); ?>
                            </span>
                            <a class="nav-link py-0 small" href="logout.php">
                                Logout
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">
                                Prijava
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">
                                Registracija
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Main Content Container -->
    <div class="container mt-4">
