<?php
require_once 'lib/db_connection.php';
require_once 'lib/functions.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $hotel_id = intval($_POST['hotel_id']);
    $broj_gostiju = intval($_POST['broj_gostiju']);
    
    // Validate
    $hotel = getHotelById($conn, $hotel_id);
    
    if ($broj_gostiju < 0) {
        $error = 'Broj gostiju ne može biti negativan!';
    } elseif ($broj_gostiju > $hotel['kapacitet']) {
        $error = 'Broj gostiju (' . $broj_gostiju . ') premašuje kapacitet hotela (' . $hotel['kapacitet'] . ')!';
    } else {
        if (updateBoravak($conn, $hotel_id, $broj_gostiju)) {
            $message = 'Boravak uspješno ažuriran!';
        } else {
            $error = 'Greška prilikom ažuriranja boravka.';
        }
    }
}

$hoteli = getAllHotels($conn);
?>
<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ažuriranje Boravka - Lanac Hotela</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-building"></i> Lanac Hotela
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Hoteli</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="statistics.php">Statistika</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="update_boravak.php">Ažuriranje Boravka</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <h1 class="mb-4"><i class="bi bi-calendar-check"></i> Ažuriranje Boravka</h1>

        <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i> <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-pencil-square"></i> Unos Boravka</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="update_boravak.php" id="boravakForm">
                            <div class="mb-4">
                                <label for="hotel_id" class="form-label"><strong>Odaberite Hotel</strong></label>
                                <select class="form-select form-select-lg" name="hotel_id" id="hotel_id" required onchange="updateHotelInfo()">
                                    <option value="">-- Odaberite hotel --</option>
                                    <?php 
                                    $hoteli->data_seek(0); // Reset pointer
                                    while($hotel = $hoteli->fetch_assoc()): 
                                    ?>
                                    <option value="<?php echo $hotel['id']; ?>" 
                                            data-kapacitet="<?php echo $hotel['kapacitet']; ?>"
                                            data-trenutno="<?php echo $hotel['broj_gostiju']; ?>"
                                            data-slobodno="<?php echo $hotel['slobodno']; ?>">
                                        <?php echo htmlspecialchars($hotel['naziv']) . ' - ' . htmlspecialchars($hotel['grad']); ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div id="hotelInfo" style="display: none;">
                                <div class="alert alert-info">
                                    <h6><i class="bi bi-info-circle"></i> Informacije o Hotelu:</h6>
                                    <ul class="mb-0">
                                        <li>Kapacitet: <strong id="info_kapacitet">0</strong></li>
                                        <li>Trenutno gostiju: <strong id="info_trenutno">0</strong></li>
                                        <li>Slobodno: <strong id="info_slobodno">0</strong></li>
                                    </ul>
                                </div>

                                <div class="mb-4">
                                    <label for="broj_gostiju" class="form-label"><strong>Broj Gostiju</strong></label>
                                    <input type="number" class="form-control form-control-lg" 
                                           name="broj_gostiju" id="broj_gostiju" 
                                           min="0" max="0" required 
                                           placeholder="Unesite broj gostiju">
                                    <div class="form-text">Maksimalno možete unijeti broj do kapaciteta hotela.</div>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="bi bi-save"></i> Ažuriraj Boravak
                                    </button>
                                    <a href="index.php" class="btn btn-outline-secondary">
                                        <i class="bi bi-arrow-left"></i> Odustani
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow">
                    <div class="card-header bg-dark text-white">
                        <h6 class="mb-0"><i class="bi bi-list-check"></i> Pregled Svih Hotela</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <?php 
                            $hoteli->data_seek(0); // Reset pointer
                            while($hotel = $hoteli->fetch_assoc()): 
                            ?>
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($hotel['naziv']); ?></h6>
                                    <small><?php echo htmlspecialchars($hotel['grad']); ?></small>
                                </div>
                                <div class="d-flex justify-content-between mt-2">
                                    <span class="badge bg-info"><?php echo $hotel['broj_gostiju']; ?>/<?php echo $hotel['kapacitet']; ?></span>
                                    <span class="badge bg-success"><?php echo $hotel['slobodno']; ?> slobodno</span>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function updateHotelInfo() {
            const select = document.getElementById('hotel_id');
            const selectedOption = select.options[select.selectedIndex];
            const hotelInfo = document.getElementById('hotelInfo');
            
            if (selectedOption.value) {
                const kapacitet = selectedOption.getAttribute('data-kapacitet');
                const trenutno = selectedOption.getAttribute('data-trenutno');
                const slobodno = selectedOption.getAttribute('data-slobodno');
                
                document.getElementById('info_kapacitet').textContent = kapacitet;
                document.getElementById('info_trenutno').textContent = trenutno;
                document.getElementById('info_slobodno').textContent = slobodno;
                
                const brojGostijuInput = document.getElementById('broj_gostiju');
                brojGostijuInput.max = kapacitet;
                brojGostijuInput.value = trenutno;
                
                hotelInfo.style.display = 'block';
            } else {
                hotelInfo.style.display = 'none';
            }
        }
    </script>
</body>
</html>
