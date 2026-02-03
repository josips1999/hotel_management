// Globalne varijable
let map;
let markers = [];
let hoteli = [];

// Učitaj hotele pri učitavanju stranice
$(document).ready(function() {
    ucitajHotele();
});

// Funkcija za učitavanje hotela
function ucitajHotele() {
    $.ajax({
        url: 'api.php',
        type: 'POST',
        data: { action: 'dohvati_hotele' },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                hoteli = response.data;
                prikaziHotele(hoteli);
            } else {
                alert('Greška pri učitavanju hotela: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX greška:', error);
            alert('Došlo je do greške pri učitavanju podataka');
        }
    });
}

// Funkcija za prikaz hotela u tablici
function prikaziHotele(data) {
    let html = '';
    
    data.forEach(function(hotel) {
        html += `
            <tr>
                <td>${hotel.id}</td>
                <td><strong>${hotel.naziv}</strong></td>
                <td>${hotel.adresa}</td>
                <td>${hotel.grad}</td>
                <td>${hotel.zupanija}</td>
                <td class="text-center">${hotel.kapacitet}</td>
                <td class="text-center">${hotel.broj_soba}</td>
                <td class="text-center">${hotel.broj_gostiju}</td>
                <td class="text-center">
                    <span class="badge bg-${hotel.slobodno > 0 ? 'success' : 'danger'}">
                        ${hotel.slobodno}
                    </span>
                </td>
                <td>
                    <div class="btn-group" role="group">
                        <button class="btn btn-sm btn-info text-white" onclick="otvoriBoravakModal(${hotel.id}, '${hotel.naziv}', ${hotel.broj_gostiju}, ${hotel.kapacitet})" title="Ažuriraj boravak">
                            <i class="bi bi-people"></i>
                        </button>
                        <button class="btn btn-sm btn-warning" onclick="otvoriUrediModal(${hotel.id})" title="Uredi">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="obrisiHotel(${hotel.id}, '${hotel.naziv}')" title="Obriši">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    });
    
    $('#hotelTableBody').html(html);
}

// Funkcija za dodavanje hotela
function dodajHotel() {
    const form = $('#dodajHotelForm')[0];
    
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    const formData = new FormData(form);
    formData.append('action', 'dodaj_hotel');
    
    $.ajax({
        url: 'api.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert(response.message);
                $('#dodajHotelModal').modal('hide');
                form.reset();
                ucitajHotele();
            } else {
                alert('Greška: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX greška:', error);
            alert('Došlo je do greške pri dodavanju hotela');
        }
    });
}

// Funkcija za otvaranje modala za uređivanje
function otvoriUrediModal(id) {
    $.ajax({
        url: 'api.php',
        type: 'GET',
        data: { action: 'dohvati_hotel', id: id },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const hotel = response.data;
                $('#edit_id').val(hotel.id);
                $('#edit_naziv').val(hotel.naziv);
                $('#edit_adresa').val(hotel.adresa);
                $('#edit_grad').val(hotel.grad);
                $('#edit_zupanija').val(hotel.zupanija);
                $('#edit_kapacitet').val(hotel.kapacitet);
                $('#edit_broj_soba').val(hotel.broj_soba);
                $('#edit_broj_gostiju').val(hotel.broj_gostiju);
                $('#edit_latitude').val(hotel.latitude);
                $('#edit_longitude').val(hotel.longitude);
                
                $('#urediHotelModal').modal('show');
            } else {
                alert('Greška: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX greška:', error);
            alert('Došlo je do greške pri učitavanju podataka hotela');
        }
    });
}

// Funkcija za uređivanje hotela
function urediHotel() {
    const form = $('#urediHotelForm')[0];
    
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    const formData = new FormData(form);
    formData.append('action', 'uredi_hotel');
    
    $.ajax({
        url: 'api.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert(response.message);
                $('#urediHotelModal').modal('hide');
                ucitajHotele();
            } else {
                alert('Greška: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX greška:', error);
            alert('Došlo je do greške pri ažuriranju hotela');
        }
    });
}

// Funkcija za brisanje hotela
function obrisiHotel(id, naziv) {
    if (confirm(`Jeste li sigurni da želite obrisati hotel "${naziv}"?`)) {
        $.ajax({
            url: 'api.php',
            type: 'POST',
            data: { action: 'obrisi_hotel', id: id },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    ucitajHotele();
                } else {
                    alert('Greška: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX greška:', error);
                alert('Došlo je do greške pri brisanju hotela');
            }
        });
    }
}

// Funkcija za otvaranje modala za ažuriranje boravka
function otvoriBoravakModal(id, naziv, trenutniBroj, kapacitet) {
    $('#boravak_id').val(id);
    $('#boravak_hotel').val(naziv);
    $('#boravak_broj_gostiju').val(trenutniBroj);
    $('#boravak_kapacitet').text(kapacitet);
    $('#boravakModal').modal('show');
}

// Funkcija za ažuriranje boravka
function azurirajBoravak() {
    const form = $('#boravakForm')[0];
    
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    const formData = new FormData(form);
    formData.append('action', 'azuriraj_boravak');
    
    $.ajax({
        url: 'api.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert(response.message);
                $('#boravakModal').modal('hide');
                ucitajHotele();
            } else {
                alert('Greška: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX greška:', error);
            alert('Došlo je do greške pri ažuriranju boravka');
        }
    });
}

// Funkcija za preklapanje između tablice i mape
function toggleMapView() {
    const tableView = document.getElementById('tableView');
    const mapView = document.getElementById('mapView');
    
    if (tableView.style.display === 'none') {
        tableView.style.display = 'block';
        mapView.style.display = 'none';
    } else {
        tableView.style.display = 'none';
        mapView.style.display = 'block';
        if (!map) {
            initMap();
        }
    }
}

// Inicijalizacija Google Mape
function initMap() {
    // Centar Hrvatske
    const hrvatska = { lat: 45.1, lng: 15.2 };
    
    map = new google.maps.Map(document.getElementById('map'), {
        zoom: 7,
        center: hrvatska,
        mapTypeId: 'roadmap'
    });
    
    // Dodaj markere za sve hotele
    hoteli.forEach(function(hotel) {
        if (hotel.latitude && hotel.longitude) {
            const position = {
                lat: parseFloat(hotel.latitude),
                lng: parseFloat(hotel.longitude)
            };
            
            const marker = new google.maps.Marker({
                position: position,
                map: map,
                title: hotel.naziv,
                icon: {
                    url: 'http://maps.google.com/mapfiles/ms/icons/red-dot.png'
                }
            });
            
            const infoWindow = new google.maps.InfoWindow({
                content: `
                    <div style="padding: 10px;">
                        <h6><strong>${hotel.naziv}</strong></h6>
                        <p style="margin: 5px 0;">
                            <i class="bi bi-geo-alt"></i> ${hotel.adresa}, ${hotel.grad}<br>
                            <i class="bi bi-pin-map"></i> ${hotel.zupanija}<br>
                            <i class="bi bi-people"></i> Gostiju: ${hotel.broj_gostiju}/${hotel.kapacitet}<br>
                            <i class="bi bi-door-open"></i> Slobodno: ${hotel.slobodno}
                        </p>
                    </div>
                `
            });
            
            marker.addListener('click', function() {
                infoWindow.open(map, marker);
            });
            
            markers.push(marker);
        }
    });
}
