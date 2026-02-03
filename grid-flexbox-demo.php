<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSS Grid & Flexbox Demo - Hotel Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/grid-flexbox.css">
    <link rel="stylesheet" href="assets/css/hotel-cards.css">
    <style>
        /* Demo-specific styles */
        .demo-section {
            display: flex;
            flex-direction: column;
            gap: 2rem;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            margin-bottom: 2rem;
        }
        
        .demo-title {
            display: flex;
            align-items: center;
            gap: 1rem;
            font-size: 1.5rem;
            font-weight: bold;
            color: #667eea;
            border-bottom: 3px solid #667eea;
            padding-bottom: 0.5rem;
        }
        
        .code-block {
            display: flex;
            flex-direction: column;
            background: #2d3748;
            color: #e2e8f0;
            padding: 1.5rem;
            border-radius: 10px;
            overflow-x: auto;
        }
        
        .code-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: bold;
            color: #fbbf24;
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body>
    <!-- Navigation with Flexbox -->
    <nav class="navbar">
        <a href="index.php" class="navbar-brand">
            <i class="bi bi-building"></i>
            Hotel Management
        </a>
        <ul class="navbar-menu">
            <li><a href="index.php"><i class="bi bi-house"></i> Početna</a></li>
            <li><a href="grid-flexbox-demo.php" class="active"><i class="bi bi-grid-3x3"></i> Grid & Flexbox Demo</a></li>
        </ul>
    </nav>

    <div class="main-container">
        <!-- Header -->
        <div class="demo-section">
            <h1 class="demo-title">
                <i class="bi bi-grid-3x3-gap"></i>
                CSS Grid & Flexbox Layout System
            </h1>
            <p style="display: flex; flex-direction: column; gap: 0.5rem;">
                <strong>Implementirano:</strong> Moderan layout sustav baziran isključivo na CSS Grid i Flexbox konceptima.
                <strong>Pravilo:</strong> <code style="display: flex; background: #f0f0f0; padding: 0.25rem 0.5rem; border-radius: 3px;">display: inline</code> svojstva nisu korištena nigdje u projektu.
            </p>
        </div>

        <!-- Stats Grid Demo -->
        <div class="demo-section">
            <h2 class="demo-title"><i class="bi bi-bar-chart"></i> CSS Grid - Stats Cards</h2>
            
            <div class="stats-grid">
                <div class="stat-card" style="border-left: 4px solid #667eea;">
                    <div class="stat-card-header">
                        <span style="font-weight: 600;">Ukupno Hotela</span>
                        <div class="stat-card-icon" style="background: #e0e7ff; color: #667eea;">
                            <i class="bi bi-building"></i>
                        </div>
                    </div>
                    <div class="stat-card-value">24</div>
                    <small style="color: #6c757d;">Grid: repeat(auto-fit, minmax(250px, 1fr))</small>
                </div>
                
                <div class="stat-card" style="border-left: 4px solid #10b981;">
                    <div class="stat-card-header">
                        <span style="font-weight: 600;">Slobodne Sobe</span>
                        <div class="stat-card-icon" style="background: #d1fae5; color: #10b981;">
                            <i class="bi bi-door-open"></i>
                        </div>
                    </div>
                    <div class="stat-card-value" style="color: #10b981;">156</div>
                    <small style="color: #6c757d;">Responsive auto-fit grid</small>
                </div>
                
                <div class="stat-card" style="border-left: 4px solid #f59e0b;">
                    <div class="stat-card-header">
                        <span style="font-weight: 600;">Aktivni Korisnici</span>
                        <div class="stat-card-icon" style="background: #fef3c7; color: #f59e0b;">
                            <i class="bi bi-people"></i>
                        </div>
                    </div>
                    <div class="stat-card-value" style="color: #f59e0b;">42</div>
                    <small style="color: #6c757d;">Gap: 1.5rem</small>
                </div>
                
                <div class="stat-card" style="border-left: 4px solid #ef4444;">
                    <div class="stat-card-header">
                        <span style="font-weight: 600;">Promjene (24h)</span>
                        <div class="stat-card-icon" style="background: #fee2e2; color: #ef4444;">
                            <i class="bi bi-activity"></i>
                        </div>
                    </div>
                    <div class="stat-card-value" style="color: #ef4444;">87</div>
                    <small style="color: #6c757d;">From audit_log table</small>
                </div>
            </div>
            
            <div class="code-block">
                <span class="code-label"><i class="bi bi-code-slash"></i> CSS Code:</span>
                <pre style="margin: 0; font-family: 'Courier New', monospace;">
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.stat-card {
    display: flex;
    flex-direction: column;
    /* Flexbox for internal layout */
}</pre>
            </div>
        </div>

        <!-- Flexbox Navigation Demo -->
        <div class="demo-section">
            <h2 class="demo-title"><i class="bi bi-list"></i> Flexbox - Navigation</h2>
            
            <div style="display: flex; flex-direction: column; gap: 1rem; padding: 1rem; background: #f8f9fa; border-radius: 10px;">
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 5px;">
                    <div style="display: flex; align-items: center; gap: 0.5rem; font-weight: bold;">
                        <i class="bi bi-building"></i>
                        Brand
                    </div>
                    <div style="display: flex; gap: 1rem;">
                        <a href="#" style="display: flex; align-items: center; gap: 0.5rem; color: white; text-decoration: none;">
                            <i class="bi bi-house"></i> Home
                        </a>
                        <a href="#" style="display: flex; align-items: center; gap: 0.5rem; color: white; text-decoration: none;">
                            <i class="bi bi-search"></i> Search
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="code-block">
                <span class="code-label"><i class="bi bi-code-slash"></i> CSS Code:</span>
                <pre style="margin: 0; font-family: 'Courier New', monospace;">
.navbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.navbar-menu {
    display: flex;
    gap: 1rem;
}</pre>
            </div>
        </div>

        <!-- Hotel Cards Grid Demo -->
        <div class="demo-section">
            <h2 class="demo-title"><i class="bi bi-grid"></i> Grid - Hotel Cards</h2>
            
            <div class="hotels-grid">
                <div class="hotel-card">
                    <div class="hotel-card-header">
                        <div class="hotel-card-title">
                            <i class="bi bi-building"></i>
                            Hotel Adriatic
                        </div>
                        <div class="hotel-card-id">#1</div>
                    </div>
                    <div class="hotel-card-body">
                        <div class="hotel-info-row">
                            <div class="hotel-info-icon">
                                <i class="bi bi-geo-alt"></i>
                            </div>
                            <div class="hotel-info-text">
                                <span class="hotel-info-label">Lokacija</span>
                                <span class="hotel-info-value">Obala Kneza Domagoja 1, Split</span>
                            </div>
                        </div>
                        <div class="hotel-stats">
                            <div class="hotel-stat-item">
                                <div class="hotel-stat-value">120</div>
                                <span class="hotel-stat-label">Kapacitet</span>
                            </div>
                            <div class="hotel-stat-item">
                                <div class="hotel-stat-value">40</div>
                                <span class="hotel-stat-label">Sobe</span>
                            </div>
                            <div class="hotel-stat-item">
                                <div class="hotel-stat-value">15</div>
                                <span class="hotel-stat-label">Slobodno</span>
                            </div>
                        </div>
                    </div>
                    <div class="hotel-card-footer">
                        <div class="availability-badge available">
                            <i class="bi bi-check-circle"></i>
                            Dostupno
                        </div>
                        <div class="hotel-actions">
                            <button class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                                <i class="bi bi-eye"></i> Detalji
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="hotel-card">
                    <div class="hotel-card-header">
                        <div class="hotel-card-title">
                            <i class="bi bi-building"></i>
                            Hotel Central
                        </div>
                        <div class="hotel-card-id">#2</div>
                    </div>
                    <div class="hotel-card-body">
                        <div class="hotel-info-row">
                            <div class="hotel-info-icon">
                                <i class="bi bi-geo-alt"></i>
                            </div>
                            <div class="hotel-info-text">
                                <span class="hotel-info-label">Lokacija</span>
                                <span class="hotel-info-value">Trg Bana Jelačića 10, Zagreb</span>
                            </div>
                        </div>
                        <div class="hotel-stats">
                            <div class="hotel-stat-item">
                                <div class="hotel-stat-value">200</div>
                                <span class="hotel-stat-label">Kapacitet</span>
                            </div>
                            <div class="hotel-stat-item">
                                <div class="hotel-stat-value">80</div>
                                <span class="hotel-stat-label">Sobe</span>
                            </div>
                            <div class="hotel-stat-item">
                                <div class="hotel-stat-value">0</div>
                                <span class="hotel-stat-label">Slobodno</span>
                            </div>
                        </div>
                    </div>
                    <div class="hotel-card-footer">
                        <div class="availability-badge full">
                            <i class="bi bi-x-circle"></i>
                            Popunjeno
                        </div>
                        <div class="hotel-actions">
                            <button class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                                <i class="bi bi-eye"></i> Detalji
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="code-block">
                <span class="code-label"><i class="bi bi-code-slash"></i> CSS Code:</span>
                <pre style="margin: 0; font-family: 'Courier New', monospace;">
.hotels-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 2rem;
}

.hotel-card {
    display: flex;
    flex-direction: column;
    /* Each card uses flexbox internally */
}

.hotel-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
}</pre>
            </div>
        </div>

        <!-- Form Grid Demo -->
        <div class="demo-section">
            <h2 class="demo-title"><i class="bi bi-file-text"></i> Grid - Form Layout</h2>
            
            <form class="form-grid">
                <div class="form-group">
                    <label class="form-label">Naziv Hotela</label>
                    <input type="text" class="form-control" placeholder="Unesite naziv...">
                </div>
                <div class="form-group">
                    <label class="form-label">Grad</label>
                    <input type="text" class="form-control" placeholder="Unesite grad...">
                </div>
                <div class="form-group">
                    <label class="form-label">Kapacitet</label>
                    <input type="number" class="form-control" placeholder="100">
                </div>
                <div class="form-group">
                    <label class="form-label">Broj Soba</label>
                    <input type="number" class="form-control" placeholder="50">
                </div>
            </form>
            
            <div class="code-block">
                <span class="code-label"><i class="bi bi-code-slash"></i> CSS Code:</span>
                <pre style="margin: 0; font-family: 'Courier New', monospace;">
.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}</pre>
            </div>
        </div>

        <!-- Features Summary -->
        <div class="demo-section" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
            <h2 class="demo-title" style="color: white; border-color: white;">
                <i class="bi bi-check-circle"></i> Implementirane Značajke
            </h2>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
                <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                    <h3 style="display: flex; align-items: center; gap: 0.5rem;">
                        <i class="bi bi-grid-3x3"></i> CSS Grid
                    </h3>
                    <ul style="display: flex; flex-direction: column; gap: 0.5rem; list-style: none;">
                        <li style="display: flex; align-items: center; gap: 0.5rem;">
                            <i class="bi bi-check"></i> Stats cards layout
                        </li>
                        <li style="display: flex; align-items: center; gap: 0.5rem;">
                            <i class="bi bi-check"></i> Hotel cards grid
                        </li>
                        <li style="display: flex; align-items: center; gap: 0.5rem;">
                            <i class="bi bi-check"></i> Form layouts
                        </li>
                        <li style="display: flex; align-items: center; gap: 0.5rem;">
                            <i class="bi bi-check"></i> Footer sections
                        </li>
                    </ul>
                </div>
                
                <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                    <h3 style="display: flex; align-items: center; gap: 0.5rem;">
                        <i class="bi bi-arrows-expand"></i> Flexbox
                    </h3>
                    <ul style="display: flex; flex-direction: column; gap: 0.5rem; list-style: none;">
                        <li style="display: flex; align-items: center; gap: 0.5rem;">
                            <i class="bi bi-check"></i> Navigation bars
                        </li>
                        <li style="display: flex; align-items: center; gap: 0.5rem;">
                            <i class="bi bi-check"></i> Card components
                        </li>
                        <li style="display: flex; align-items: center; gap: 0.5rem;">
                            <i class="bi bi-check"></i> Button groups
                        </li>
                        <li style="display: flex; align-items: center; gap: 0.5rem;">
                            <i class="bi bi-check"></i> Modal layouts
                        </li>
                    </ul>
                </div>
                
                <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                    <h3 style="display: flex; align-items: center; gap: 0.5rem;">
                        <i class="bi bi-phone"></i> Responsive
                    </h3>
                    <ul style="display: flex; flex-direction: column; gap: 0.5rem; list-style: none;">
                        <li style="display: flex; align-items: center; gap: 0.5rem;">
                            <i class="bi bi-check"></i> Mobile-first approach
                        </li>
                        <li style="display: flex; align-items: center; gap: 0.5rem;">
                            <i class="bi bi-check"></i> Auto-fit/auto-fill grids
                        </li>
                        <li style="display: flex; align-items: center; gap: 0.5rem;">
                            <i class="bi bi-check"></i> Flex-wrap for mobile
                        </li>
                        <li style="display: flex; align-items: center; gap: 0.5rem;">
                            <i class="bi bi-check"></i> Media queries
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer with Grid -->
    <footer class="footer">
        <div class="footer-section">
            <h3 class="footer-title">CSS Grid</h3>
            <p>Grid template columns, auto-fit, minmax, gap properties</p>
        </div>
        <div class="footer-section">
            <h3 class="footer-title">Flexbox</h3>
            <p>Justify-content, align-items, flex-direction, gap</p>
        </div>
        <div class="footer-section">
            <h3 class="footer-title">No Inline</h3>
            <p>Isključivo Grid i Flexbox - bez display:inline</p>
        </div>
    </footer>
    
    <div class="footer-bottom">
        <p>&copy; 2026 Hotel Management - Modern CSS Layout</p>
    </div>
</body>
</html>
