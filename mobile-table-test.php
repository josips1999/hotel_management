<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mobile Table Test - Hotel Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    <link rel="stylesheet" href="assets/css/mobile-tables.css">
    <style>
        body {
            padding: 2rem;
            background: #f8f9fa;
        }
        .test-section {
            margin-bottom: 3rem;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        h1, h2 {
            color: #667eea;
            margin-bottom: 1.5rem;
        }
        .screen-info {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            padding: 1rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
        }
        .info-box {
            flex: 1;
            text-align: center;
        }
        .info-value {
            font-size: 1.5rem;
            font-weight: bold;
        }
        .info-label {
            font-size: 0.85rem;
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <h1><i class="bi bi-phone"></i> Mobile Table Test Page</h1>
    
    <div class="screen-info">
        <div class="info-box">
            <div class="info-value" id="width">-</div>
            <div class="info-label">Width (px)</div>
        </div>
        <div class="info-box">
            <div class="info-value" id="height">-</div>
            <div class="info-label">Height (px)</div>
        </div>
        <div class="info-box">
            <div class="info-value" id="orientation">-</div>
            <div class="info-label">Orientation</div>
        </div>
        <div class="info-box">
            <div class="info-value" id="device">-</div>
            <div class="info-label">Device Type</div>
        </div>
    </div>

    <!-- Test 1: Stacked Cards (Best for mobile) -->
    <div class="test-section">
        <h2>Test 1: Stacked Cards (table-cards-mobile)</h2>
        <p><strong>Resize your browser to &lt; 768px to see mobile layout.</strong></p>
        
        <div class="responsive-table-wrapper table-cards-mobile">
            <table class="responsive-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Hotel Naziv</th>
                        <th>Grad</th>
                        <th>Kapacitet</th>
                        <th>Sobe</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td data-label="ID">1</td>
                        <td data-label="Hotel Naziv"><strong>Hotel Adriatic</strong></td>
                        <td data-label="Grad">Split</td>
                        <td data-label="Kapacitet">120</td>
                        <td data-label="Sobe">40</td>
                        <td data-label="Status"><span class="badge bg-success">Dostupno</span></td>
                    </tr>
                    <tr>
                        <td data-label="ID">2</td>
                        <td data-label="Hotel Naziv"><strong>Hotel Central</strong></td>
                        <td data-label="Grad">Zagreb</td>
                        <td data-label="Kapacitet">200</td>
                        <td data-label="Sobe">80</td>
                        <td data-label="Status"><span class="badge bg-danger">Popunjeno</span></td>
                    </tr>
                    <tr>
                        <td data-label="ID">3</td>
                        <td data-label="Hotel Naziv"><strong>Hotel Marina</strong></td>
                        <td data-label="Grad">Rijeka</td>
                        <td data-label="Kapacitet">90</td>
                        <td data-label="Sobe">30</td>
                        <td data-label="Status"><span class="badge bg-success">Dostupno</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Test 2: Grid Layout -->
    <div class="test-section">
        <h2>Test 2: Grid Layout (table-grid-mobile)</h2>
        <p><strong>Each row converts to 2-column grid on mobile.</strong></p>
        
        <div class="responsive-table-wrapper table-grid-mobile">
            <table class="responsive-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Naziv</th>
                        <th>Grad</th>
                        <th>Kapacitet</th>
                        <th>Akcije</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td data-label="ID">1</td>
                        <td data-label="Naziv"><strong>Hotel Adriatic</strong></td>
                        <td data-label="Grad">Split</td>
                        <td data-label="Kapacitet">120</td>
                        <td data-label="Akcije" class="actions-cell">
                            <button style="padding: 0.5rem 1rem; background: #667eea; color: white; border: none; border-radius: 5px;">
                                <i class="bi bi-eye"></i> Detalji
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td data-label="ID">2</td>
                        <td data-label="Naziv"><strong>Hotel Central</strong></td>
                        <td data-label="Grad">Zagreb</td>
                        <td data-label="Kapacitet">200</td>
                        <td data-label="Akcije" class="actions-cell">
                            <button style="padding: 0.5rem 1rem; background: #667eea; color: white; border: none; border-radius: 5px;">
                                <i class="bi bi-eye"></i> Detalji
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Test 3: Horizontal Scroll -->
    <div class="test-section">
        <h2>Test 3: Horizontal Scroll (table-scroll-mobile)</h2>
        <p><strong>Swipe left/right on mobile to see all columns.</strong></p>
        
        <div class="responsive-table-wrapper table-scroll-mobile" style="position: relative;">
            <table class="responsive-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Naziv</th>
                        <th>Adresa</th>
                        <th>Grad</th>
                        <th>Županija</th>
                        <th>Kapacitet</th>
                        <th>Sobe</th>
                        <th>Email</th>
                        <th>Telefon</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td><strong>Hotel Adriatic</strong></td>
                        <td>Obala Kneza Domagoja 1</td>
                        <td>Split</td>
                        <td>Splitsko-dalmatinska</td>
                        <td>120</td>
                        <td>40</td>
                        <td>info@adriatic.hr</td>
                        <td>+385 21 123 456</td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td><strong>Hotel Central</strong></td>
                        <td>Trg Bana Jelačića 10</td>
                        <td>Zagreb</td>
                        <td>Grad Zagreb</td>
                        <td>200</td>
                        <td>80</td>
                        <td>info@central.hr</td>
                        <td>+385 1 234 567</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Test 4: Hide columns on mobile -->
    <div class="test-section">
        <h2>Test 4: Conditional Column Visibility</h2>
        <p><strong>Some columns hidden on mobile/tablet using hide-mobile and hide-tablet classes.</strong></p>
        
        <div class="responsive-table-wrapper">
            <table class="responsive-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Naziv</th>
                        <th>Grad</th>
                        <th class="hide-mobile">Županija (hidden mobile)</th>
                        <th class="hide-tablet">Email (hidden tablet)</th>
                        <th>Kapacitet</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td><strong>Hotel Adriatic</strong></td>
                        <td>Split</td>
                        <td class="hide-mobile">Splitsko-dalmatinska</td>
                        <td class="hide-tablet">info@adriatic.hr</td>
                        <td>120</td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td><strong>Hotel Central</strong></td>
                        <td>Zagreb</td>
                        <td class="hide-mobile">Grad Zagreb</td>
                        <td class="hide-tablet">info@central.hr</td>
                        <td>200</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Test 5: Responsive utilities -->
    <div class="test-section">
        <h2>Test 5: Responsive Utility Classes</h2>
        
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            <div class="show-mobile" style="padding: 1rem; background: #10b981; color: white; border-radius: 5px;">
                <i class="bi bi-phone"></i> <strong>MOBILE VIEW</strong> - This is only visible on mobile (≤767px)
            </div>
            
            <div class="show-tablet" style="padding: 1rem; background: #f59e0b; color: white; border-radius: 5px;">
                <i class="bi bi-tablet"></i> <strong>TABLET VIEW</strong> - This is only visible on tablet (768px-991px)
            </div>
            
            <div class="show-desktop" style="padding: 1rem; background: #667eea; color: white; border-radius: 5px;">
                <i class="bi bi-display"></i> <strong>DESKTOP VIEW</strong> - This is only visible on desktop (≥992px)
            </div>
            
            <div class="hide-mobile" style="padding: 1rem; background: #6c757d; color: white; border-radius: 5px;">
                <i class="bi bi-eye-slash"></i> This is hidden on mobile
            </div>
        </div>
    </div>

    <script>
        function updateInfo() {
            const width = window.innerWidth;
            const height = window.innerHeight;
            const orientation = width > height ? 'Landscape' : 'Portrait';
            let device = 'Desktop';
            
            if (width <= 479) device = 'XS Phone';
            else if (width <= 767) device = 'Phone';
            else if (width <= 991) device = 'Tablet';
            else if (width <= 1199) device = 'Laptop';
            else if (width <= 1599) device = 'Desktop';
            else device = 'Large Screen';
            
            document.getElementById('width').textContent = width;
            document.getElementById('height').textContent = height;
            document.getElementById('orientation').textContent = orientation;
            document.getElementById('device').textContent = device;
        }
        
        updateInfo();
        window.addEventListener('resize', updateInfo);
        window.addEventListener('orientationchange', updateInfo);
    </script>
</body>
</html>
