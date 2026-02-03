<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistika - Hotel Management</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-building"></i> Hotel Management System
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="bi bi-list-ul"></i> Prikaz hotela
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="statistika.php">
                            <i class="bi bi-bar-chart"></i> Statistika
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2 class="mb-4">
            <i class="bi bi-bar-chart-fill"></i> Statistika lanca hotela
        </h2>

        <div class="row" id="statistikaCards">
            <!-- Statistika se učitava putem PHP -->
            <?php
            require_once 'config.php';
            
            $sql = "SELECT 
                    COUNT(*) as ukupan_broj_hotela,
                    SUM(broj_gostiju) as ukupan_broj_gostiju,
                    SUM(kapacitet) as ukupan_kapacitet,
                    SUM(kapacitet - broj_gostiju) as ukupan_broj_slobodnih
                    FROM hoteli";
            
            $result = $conn->query($sql);
            $stats = $result->fetch_assoc();
            
            // Dodatna statistika
            $sql2 = "SELECT 
                     AVG(broj_gostiju) as prosjecno_gostiju,
                     MAX(broj_gostiju) as max_gostiju,
                     MIN(broj_gostiju) as min_gostiju,
                     SUM(broj_soba) as ukupno_soba
                     FROM hoteli";
            $result2 = $conn->query($sql2);
            $stats2 = $result2->fetch_assoc();
            
            $popunjenost = ($stats['ukupan_kapacitet'] > 0) ? 
                           round(($stats['ukupan_broj_gostiju'] / $stats['ukupan_kapacitet']) * 100, 2) : 0;
            ?>
            
            <div class="col-md-3 mb-4">
                <div class="card text-white bg-primary">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Ukupan broj hotela</h6>
                                <h2 class="mb-0"><?php echo $stats['ukupan_broj_hotela']; ?></h2>
                            </div>
                            <div>
                                <i class="bi bi-building display-4 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-4">
                <div class="card text-white bg-success">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Ukupan broj gostiju</h6>
                                <h2 class="mb-0"><?php echo $stats['ukupan_broj_gostiju']; ?></h2>
                            </div>
                            <div>
                                <i class="bi bi-people-fill display-4 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-4">
                <div class="card text-white bg-info">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Ukupan kapacitet</h6>
                                <h2 class="mb-0"><?php echo $stats['ukupan_kapacitet']; ?></h2>
                            </div>
                            <div>
                                <i class="bi bi-box display-4 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-4">
                <div class="card text-white bg-warning">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Slobodna mjesta</h6>
                                <h2 class="mb-0"><?php echo $stats['ukupan_broj_slobodnih']; ?></h2>
                            </div>
                            <div>
                                <i class="bi bi-door-open display-4 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-table"></i> Detaljna statistika
                        </h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Opis</th>
                                    <th class="text-end">Vrijednost</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Ukupan broj hotela</strong></td>
                                    <td class="text-end"><?php echo $stats['ukupan_broj_hotela']; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Ukupan broj gostiju</strong></td>
                                    <td class="text-end"><?php echo $stats['ukupan_broj_gostiju']; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Ukupan kapacitet lanca hotela</strong></td>
                                    <td class="text-end"><?php echo $stats['ukupan_kapacitet']; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Ukupan broj slobodnih mjesta</strong></td>
                                    <td class="text-end"><?php echo $stats['ukupan_broj_slobodnih']; ?></td>
                                </tr>
                                <tr class="table-primary">
                                    <td><strong>Popunjenost (%)</strong></td>
                                    <td class="text-end"><strong><?php echo $popunjenost; ?>%</strong></td>
                                </tr>
                                <tr>
                                    <td><strong>Ukupan broj soba</strong></td>
                                    <td class="text-end"><?php echo $stats2['ukupno_soba']; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Prosječan broj gostiju po hotelu</strong></td>
                                    <td class="text-end"><?php echo round($stats2['prosjecno_gostiju'], 2); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Maksimalan broj gostiju u pojedinom hotelu</strong></td>
                                    <td class="text-end"><?php echo $stats2['max_gostiju']; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Minimalan broj gostiju u pojedinom hotelu</strong></td>
                                    <td class="text-end"><?php echo $stats2['min_gostiju']; ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-graph-up"></i> Popunjenost po hotelu
                        </h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Hotel</th>
                                    <th>Grad</th>
                                    <th class="text-end">Kapacitet</th>
                                    <th class="text-end">Gostiju</th>
                                    <th class="text-end">Slobodno</th>
                                    <th>Popunjenost</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql3 = "SELECT naziv, grad, kapacitet, broj_gostiju, 
                                        (kapacitet - broj_gostiju) as slobodno,
                                        ROUND((broj_gostiju / kapacitet * 100), 2) as popunjenost
                                        FROM hoteli 
                                        ORDER BY popunjenost DESC";
                                $result3 = $conn->query($sql3);
                                
                                while ($row = $result3->fetch_assoc()) {
                                    $popunjenost_hotel = $row['popunjenost'];
                                    $bar_class = 'bg-success';
                                    if ($popunjenost_hotel > 80) {
                                        $bar_class = 'bg-danger';
                                    } elseif ($popunjenost_hotel > 60) {
                                        $bar_class = 'bg-warning';
                                    }
                                    
                                    echo "<tr>";
                                    echo "<td><strong>{$row['naziv']}</strong></td>";
                                    echo "<td>{$row['grad']}</td>";
                                    echo "<td class='text-end'>{$row['kapacitet']}</td>";
                                    echo "<td class='text-end'>{$row['broj_gostiju']}</td>";
                                    echo "<td class='text-end'>{$row['slobodno']}</td>";
                                    echo "<td>";
                                    echo "<div class='progress' style='height: 25px;'>";
                                    echo "<div class='progress-bar {$bar_class}' role='progressbar' style='width: {$popunjenost_hotel}%' aria-valuenow='{$popunjenost_hotel}' aria-valuemin='0' aria-valuemax='100'>";
                                    echo "{$popunjenost_hotel}%";
                                    echo "</div>";
                                    echo "</div>";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                                
                                closeConnection();
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mt-4 mb-4">
            <a href="index.php" class="btn btn-primary btn-lg">
                <i class="bi bi-arrow-left"></i> Natrag na popis hotela
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
