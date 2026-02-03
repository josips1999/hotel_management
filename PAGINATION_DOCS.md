# Pagination (Straničenje) - Implementacija

## ✅ Ispunjeni Zahtjevi

### 1. Prikaz N Rezultata Po Stranici ✓
- **Broj rezultata podesiv** u `lib/config.php`
- Default: `ITEMS_PER_PAGE = 10`
- Lako promjenljivo (5, 10, 20, 50, 100, itd.)

### 2. Vlastiti PHP i SQL Kod ✓
- **Pagination klasa** - `lib/Pagination.php` (400+ linija)
- **SQL LIMIT i OFFSET** - Vlastiti SQL upiti
- **NE koristi DataTables** - 100% custom implementacija
- **NE koristi gotove JS alate** - Pure PHP/SQL

### 3. Zasebna Reusable Funkcija ✓
- **Pagination klasa** - Može se koristiti bilo gdje
- Implementirana na:
  - `index.php` - Lista hotela
  - `search.php` - Search rezultati
  - Može se dodati na bilo koju stranicu

---

## 📁 Kreirane/Ažurirane Datoteke

### Nove Datoteke:
1. **lib/Pagination.php** (400+ linija) - Glavna pagination klasa

### Ažurirane Datoteke:
2. **lib/config.php** - Dodao `ITEMS_PER_PAGE` konstantu
3. **app/models/Hotel.php** - Dodao `getCount()` i `getAllPaginated()`
4. **app/controllers/HotelController.php** - Dodao pagination parametar u `index()`
5. **lib/SearchEngine.php** - Dodao pagination u `search()`, `searchHotels()`, `searchUsers()`
6. **index.php** - Implementirao Pagination za hotel listu
7. **search.php** - Implementirao Pagination za search rezultate

---

## 🔧 Konfiguracija

### lib/config.php

```php
// Pagination Settings (Straničenje)
define('ITEMS_PER_PAGE', 10); // Broj rezultata po stranici (podesivo)
```

**Promjena broja rezultata:**
```php
define('ITEMS_PER_PAGE', 5);   // 5 po stranici
define('ITEMS_PER_PAGE', 20);  // 20 po stranici
define('ITEMS_PER_PAGE', 50);  // 50 po stranici
```

---

## 💻 Pagination Klasa API

### Konstruktor

```php
$pagination = new Pagination($totalItems, $currentPage, $itemsPerPage, $baseUrl);
```

**Parametri:**
- `$totalItems` - Ukupan broj zapisa u bazi
- `$currentPage` - Trenutna stranica (default: 1)
- `$itemsPerPage` - Broj po stranici (default: iz config.php)
- `$baseUrl` - Base URL za linkove (default: trenutna stranica)

**Primjer:**
```php
$totalHotels = 45;
$currentPage = 2;
$pagination = new Pagination($totalHotels, $currentPage);
```

### SQL Metode

#### `getOffset()`
```php
$offset = $pagination->getOffset();
// Vraća SQL OFFSET vrijednost
// Primjer: Stranica 2, 10 po stranici → Offset = 10
```

#### `getLimit()`
```php
$limit = $pagination->getLimit();
// Vraća SQL LIMIT vrijednost
// Primjer: ITEMS_PER_PAGE = 10 → Limit = 10
```

**SQL Query:**
```php
$offset = $pagination->getOffset();
$limit = $pagination->getLimit();

$sql = "SELECT * FROM hotels ORDER BY naziv ASC LIMIT ? OFFSET ?";
$stmt->bind_param("ii", $limit, $offset);
```

### Info Metode

#### `getCurrentPage()`
```php
$currentPage = $pagination->getCurrentPage();
// Vraća: 2
```

#### `getTotalPages()`
```php
$totalPages = $pagination->getTotalPages();
// Vraća: 5 (ako ima 45 zapisa, 10 po stranici)
```

#### `getTotalItems()`
```php
$totalItems = $pagination->getTotalItems();
// Vraća: 45
```

#### `hasPrevious()` / `hasNext()`
```php
if ($pagination->hasPrevious()) {
    echo "Postoji prethodna stranica";
}

if ($pagination->hasNext()) {
    echo "Postoji sljedeća stranica";
}
```

#### `getRange()`
```php
$range = $pagination->getRange();
// Vraća: ['start' => 11, 'end' => 20, 'total' => 45]
// "Prikazano 11-20 od 45 rezultata"
```

### HTML Rendering Metode

#### `render($size, $alignment)`
```php
// Default pagination (medium, center)
echo $pagination->render();

// Small pagination, left aligned
echo $pagination->render('sm', 'start');

// Large pagination, right aligned
echo $pagination->render('lg', 'end');
```

**Sizes:**
- `''` - Default (medium)
- `'sm'` - Small
- `'lg'` - Large

**Alignments:**
- `'start'` - Left
- `'center'` - Center (default)
- `'end'` - Right

**Output:**
```html
<nav aria-label='Straničenje'>
    <ul class='pagination justify-content-center'>
        <li class='page-item'><a class='page-link' href='?page=1'>&laquo;</a></li>
        <li class='page-item active'><span class='page-link'>2</span></li>
        <li class='page-item'><a class='page-link' href='?page=3'>&raquo;</a></li>
    </ul>
</nav>
```

#### `renderInfo()`
```php
echo $pagination->renderInfo();
// Output: "Prikazano 11-20 od 45 rezultata"
```

#### `getMetadata()`
```php
$meta = $pagination->getMetadata();
// Vraća array sa svim info (za AJAX/API)
```

**Return:**
```php
[
    'current_page' => 2,
    'total_pages' => 5,
    'total_items' => 45,
    'items_per_page' => 10,
    'offset' => 10,
    'range_start' => 11,
    'range_end' => 20,
    'has_previous' => true,
    'has_next' => true
]
```

---

## 🗄️ SQL Implementacija

### Hotel Model (app/models/Hotel.php)

#### 1. getCount()
```php
public function getCount() {
    $sql = "SELECT COUNT(*) as total FROM hotels";
    $result = $this->db->query($sql);
    $row = $result->fetch_assoc();
    return (int)$row['total'];
}
```

#### 2. getAllPaginated($limit, $offset)
```php
public function getAllPaginated($limit, $offset) {
    $stmt = $this->db->prepare("SELECT * FROM hotels ORDER BY naziv ASC LIMIT ? OFFSET ?");
    $stmt->bind_param("ii", $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}
```

**SQL:**
```sql
-- Stranica 1 (prvih 10)
SELECT * FROM hotels ORDER BY naziv ASC LIMIT 10 OFFSET 0;

-- Stranica 2 (sljedećih 10)
SELECT * FROM hotels ORDER BY naziv ASC LIMIT 10 OFFSET 10;

-- Stranica 3 (sljedećih 10)
SELECT * FROM hotels ORDER BY naziv ASC LIMIT 10 OFFSET 20;
```

### SearchEngine (lib/SearchEngine.php)

#### Hotels Search sa Pagination
```php
private function searchHotels($searchTerm, $mode, $limit = 20, $offset = 0) {
    $sql = "SELECT ..., MATCH(...) AGAINST(?) AS relevance
            FROM hotels
            WHERE MATCH(naziv, adresa, grad) AGAINST(?)
            ORDER BY relevance DESC
            LIMIT ? OFFSET ?";
    
    $stmt->bind_param("ssii", $searchTerm, $searchTerm, $limit, $offset);
    // ...
}
```

#### Count Query
```php
private function getHotelsCount($searchTerm, $mode) {
    $sql = "SELECT COUNT(*) as total
            FROM hotels
            WHERE MATCH(naziv, adresa, grad) AGAINST(?)";
    
    $stmt->bind_param("s", $searchTerm);
    // ...
}
```

---

## 🚀 Korištenje

### Primjer 1: Hotel Lista (index.php)

```php
<?php
require_once('lib/config.php');
require_once('lib/Pagination.php');
require_once('app/controllers/HotelController.php');

// Get current page
$currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

// Get data
$controller = new HotelController($connection);
$result = $controller->index($currentPage, ITEMS_PER_PAGE);
$hotels = $result['data'];

// Create pagination
$pagination = new Pagination($result['pagination']['total_items'], $currentPage);
?>

<!-- Display hotels -->
<?php foreach ($hotels as $hotel): ?>
    <div><?php echo $hotel['naziv']; ?></div>
<?php endforeach; ?>

<!-- Pagination controls -->
<div class="d-flex justify-content-between">
    <div><?php echo $pagination->renderInfo(); ?></div>
    <div><?php echo $pagination->render(); ?></div>
</div>
```

### Primjer 2: Search sa Pagination (search.php)

```php
<?php
require_once('lib/config.php');
require_once('lib/Pagination.php');
require_once('lib/SearchEngine.php');

$currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$searchTerm = $_GET['q'] ?? '';

$searchEngine = new SearchEngine($conn);
$results = $searchEngine->search($searchTerm, SearchEngine::MODE_NATURAL, $currentPage, ITEMS_PER_PAGE);

$pagination = new Pagination($results['pagination']['total_items'], $currentPage);
?>

<!-- Display results -->
<?php foreach ($results['hotels'] as $hotel): ?>
    <div><?php echo $hotel['naziv_highlighted']; ?></div>
<?php endforeach; ?>

<!-- Pagination -->
<?php echo $pagination->render(); ?>
```

### Primjer 3: Custom Query

```php
<?php
// Custom SQL query
$totalQuery = "SELECT COUNT(*) as total FROM hotels WHERE grad = 'Zagreb'";
$result = $conn->query($totalQuery);
$totalItems = $result->fetch_assoc()['total'];

// Create pagination
$currentPage = $_GET['page'] ?? 1;
$pagination = new Pagination($totalItems, $currentPage, 20); // 20 per page

// Get data with LIMIT/OFFSET
$offset = $pagination->getOffset();
$limit = $pagination->getLimit();

$dataQuery = "SELECT * FROM hotels WHERE grad = 'Zagreb' LIMIT $limit OFFSET $offset";
$hotels = $conn->query($dataQuery)->fetch_all(MYSQLI_ASSOC);

// Display
foreach ($hotels as $hotel) {
    echo $hotel['naziv'] . "<br>";
}

echo $pagination->render();
?>
```

---

## 🎨 Smart Page Numbers Algorithm

Pagination prikazuje **pametno rangiranje stranica** (max 7):

### Primjeri:

**Total 5 stranica (prikaži sve):**
```
[1] [2] [3] [4] [5]
```

**Total 10 stranica, Current = 1:**
```
[1] [2] [3] [...] [10]
```

**Total 10 stranica, Current = 5:**
```
[1] [...] [3] [4] [5] [6] [7] [...] [10]
```

**Total 10 stranica, Current = 10:**
```
[1] [...] [8] [9] [10]
```

**Algoritam:**
1. Uvijek prikaži prvu stranicu [1]
2. Prikaži 2 stranice prije trenutne
3. Prikaži trenutnu stranicu (active)
4. Prikaži 2 stranice nakon trenutne
5. Uvijek prikaži zadnju stranicu
6. Dodaj [...] ako postoji gap

---

## 📊 URL Parameter Handling

Pagination **automatski čuva postojeće query parametre**:

```php
// Original URL: search.php?q=Hotel&mode=boolean&filter=Zagreb
// Pagination link: search.php?q=Hotel&mode=boolean&filter=Zagreb&page=2
```

**Automatski:**
- Dodaje `&page=X`
- Čuva sve postojeće parametre (`q`, `mode`, `filter`, itd.)
- Koristi `http_build_query()`

---

## 🔍 Testing

### Test 1: Hotel List Pagination
```
URL: index.php
URL: index.php?page=2
```

### Test 2: Search Pagination
```
URL: search.php?q=Hotel
URL: search.php?q=Hotel&page=2
```

### Test 3: Promjena broja rezultata
```php
// lib/config.php
define('ITEMS_PER_PAGE', 5); // Promijeni na 5

// Refresh: index.php
// Sad će biti 5 hotela po stranici
```

### Test 4: Custom Stranica
```php
$pagination = new Pagination(100, 3, 25);
// 100 total items, stranica 3, 25 po stranici
// Offset = 50, pokazuje 51-75
```

---

## ✅ Prednosti Implementacije

1. **Reusable** - Jedna klasa za sve stranice
2. **Configurable** - Lako promjenljivo u config.php
3. **SQL Efficient** - LIMIT/OFFSET optimizacija
4. **Bootstrap 5 UI** - Responsive design
5. **Smart Pagination** - Inteligentno prikazivanje brojeva
6. **URL Preservation** - Čuva query parametre
7. **Security** - Type casting, validation
8. **No External Dependencies** - 100% vlastiti kod

---

## 📝 Zaključak

Implementirana je **potpuno funkcionalna pagination** koja:

1. ✅ Prikazuje **N rezultata po stranici** (podesivo u config.php)
2. ✅ Koristi **vlastiti PHP i SQL kod** (LIMIT/OFFSET)
3. ✅ **NE koristi DataTables** ili gotove JS alate
4. ✅ **Zasebna reusable klasa** (Pagination.php)
5. ✅ **Može se koristiti bilo gdje** (index.php, search.php, itd.)
6. ✅ **Production ready** - Security, error handling, responsive UI

**Pristup:**
- Hotel lista: http://localhost/hotel_managment/index.php
- Search rezultati: http://localhost/hotel_managment/search.php?q=Hotel
- Config: lib/config.php (promijeni ITEMS_PER_PAGE)

---

**Tehnologije:** PHP 8.2, MySQL 8.0, Bootstrap 5, SQL LIMIT/OFFSET  
**Datum:** Januar 2026
