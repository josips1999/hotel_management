# Template System Documentation

## Organizacija Koda - Predlošci (Templates)

Projekt koristi **template system** za odvajanje PHP logike od HTML prezentacije.

## Struktura Template Sistema

### 📁 `templates/`

```
templates/
├── header.php    # Zaglavlje (navigation, HTML head)
└── footer.php    # Podnožje (footer, closing tags)
```

## 1. Header Template (`templates/header.php`)

### Sadržaj:
- `<!DOCTYPE html>` deklaracija
- `<head>` sekcija (meta tags, CSS, title)
- Bootstrap 5 CSS
- Bootstrap Icons
- Navigation bar (dinamički meni)
- Opening `<div class="container">`

### Potrebne Varijable:

```php
$pageTitle = 'Page Title';        // Naslov stranice
$currentPage = 'page_identifier'; // ID stranice za active nav
$isLoggedIn = true/false;         // Login status
$username = 'Username';           // Korisničko ime
$customCSS = '...';               // (Optional) Custom CSS
```

### Primjer Korištenja:

```php
<?php
// PHP CODE - Business Logic
$pageTitle = 'Hotel Management System';
$currentPage = 'index';
$isLoggedIn = $sessionManager->isLoggedIn();
$username = $sessionManager->getUsername();
?>
<?php include 'templates/header.php'; ?>

<!-- HTML Content -->
```

### Navigation Active State:

Template automatski highlighta aktivnu stranicu prema `$currentPage`:

| $currentPage | Aktivna Stranica |
|-------------|------------------|
| `index` | Hoteli |
| `ajax_search` | AJAX Search |
| `ajax_filter` | AJAX Filter |
| `search` | Pretraga |
| `statistics` | Statistika |
| `update_boravak` | Ažuriranje Boravka |

## 2. Footer Template (`templates/footer.php`)

### Sadržaj:
- Closing `</div>` (container)
- Footer sekcija (informacije, copyright)
- Bootstrap JS Bundle
- Custom JavaScript (ako postoji)
- Closing `</body>` i `</html>`

### Potrebne Varijable:

```php
$customJS = '...'; // (Optional) Custom JavaScript
```

### Primjer Korištenja:

```php
<!-- HTML Content -->

<?php include 'templates/footer.php'; ?>
<?php $connection->close(); ?>
```

## 3. Organizacija PHP Koda

### ✅ Pravilo: PHP prije HTML-a

Sav PHP kod (business logic) **MORA biti na početku** dokumenta, prije HTML output-a:

```php
<?php
/**
 * Page Description
 */

// ============================================================================
// PHP CODE - Business Logic (prije HTML-a)
// ============================================================================

require_once('lib/db_connection.php');
require_once('lib/SessionManager.php');
// ... ostali includes

// Session management
$sessionManager = new SessionManager($connection);
$isLoggedIn = $sessionManager->isLoggedIn();

// Database operations
$controller = new HotelController($connection);
$result = $controller->index();

// Page-specific variables
$pageTitle = 'Title';
$currentPage = 'identifier';

// ============================================================================
// HTML TEMPLATE
// ============================================================================
?>
<?php include 'templates/header.php'; ?>

<!-- Page content -->
<h1><?php echo $pageTitle; ?></h1>

<?php include 'templates/footer.php'; ?>
```

### ❌ Što Izbjegavati:

```php
<!-- Loše: PHP kod pomiješan s HTML-om -->
<!DOCTYPE html>
<?php require_once('...'); ?>
<html>
<?php $x = 10; ?>
<body>
<?php echo $x; ?>
</body>
```

## 4. Refaktorirane Stranice

### ✅ Refaktorirano (koristi templates):

1. **index.php** - Lista hotela
   - PHP logika: 1-50 linija
   - HTML template: 51-kraj
   - Koristi: `header.php`, `footer.php`

2. **ajax_search.php** - AJAX Live Search
   - PHP logika: 1-120 linija (uključujući custom CSS)
   - HTML template: 121-kraj
   - Koristi: `header.php`, `footer.php`
   - Custom CSS u `$customCSS` varijabli

3. **ajax_filter.php** - AJAX Filter
   - PHP logika: 1-90 linija (uključujući custom CSS)
   - HTML template: 91-kraj
   - Koristi: `header.php`, `footer.php`

### ⚠️ Djelomično Refaktorirano:

4. **search.php** - Full-Text Search
   - PHP logika odvojena na početku
   - HTML s inline `<style>` (zbog kompleksnosti)
   - **Napomena:** Zadržan inline CSS zbog specifičnog dizajna

## 5. Custom CSS i JavaScript

### Custom CSS:

Za stranice sa specifičnim stilovima, definiraj `$customCSS` prije header.php:

```php
<?php
$customCSS = "
    body {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    .custom-card {
        border-radius: 15px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.2);
    }
";
?>
<?php include 'templates/header.php'; ?>
```

### Custom JavaScript:

Za stranice sa specifičnim JS kodom, definiraj `$customJS`:

```php
<?php
$customJS = "
    function myFunction() {
        console.log('Hello!');
    }
    document.addEventListener('DOMContentLoaded', myFunction);
";
?>
<!-- ... content ... -->
<?php include 'templates/footer.php'; ?>
```

## 6. Prednosti Template Sistema

### ✅ Konzistentnost
- Jedinstvena navigacija na svim stranicama
- Isti header/footer dizajn
- Automatski Bootstrap i ikone

### ✅ Maintainability
- Promjena navigacije na **jednom mjestu** (`header.php`)
- Promjena footera na **jednom mjestu** (`footer.php`)
- Lakše dodavanje novih stranica

### ✅ DRY Princip (Don't Repeat Yourself)
- Nema ponavljanja HTML koda
- Nema ponavljanja `<head>` sekcije
- Nema ponavljanja navigacije

### ✅ Čistoća Koda
- PHP logika odvojena od prezentacije
- HTML kod čitljiviji
- Lakše debugging

## 7. Dodavanje Nove Stranice

### Template za novu stranicu:

```php
<?php
/**
 * New Page Description
 */

// ============================================================================
// PHP CODE - Business Logic (prije HTML-a)
// ============================================================================

require_once('lib/db_connection.php');
require_once('lib/SessionManager.php');

// Session
$sessionManager = new SessionManager($connection);
$sessionManager->checkRememberMe();
$isLoggedIn = $sessionManager->isLoggedIn();
$username = $sessionManager->getUsername();

// Your business logic here
// ...

// Page-specific variables for template
$pageTitle = 'Your Page Title';
$currentPage = 'your_page_id';

// Optional: Custom CSS
$customCSS = "
    /* Your custom styles */
";

// ============================================================================
// HTML TEMPLATE
// ============================================================================
?>
<?php include 'templates/header.php'; ?>

<!-- Your page content -->
<h1>Your Content</h1>

<!-- Optional: Page-specific JavaScript -->
<script>
    // Your JavaScript
</script>

<?php include 'templates/footer.php'; ?>
<?php $connection->close(); ?>
```

## 8. Migracija Postojećih Stranica

Za migraciju postojećih stranica na template system:

1. **Izdvoji PHP logiku** na početak dokumenta
2. **Zamijeni header** s `<?php include 'templates/header.php'; ?>`
3. **Zamijeni footer** s `<?php include 'templates/footer.php'; ?>`
4. **Postavi varijable** (`$pageTitle`, `$currentPage`)
5. **Testiraj** funkcionalnost

## 9. Best Practices

### ✅ Do:
- PHP kod **uvijek na početku** dokumenta
- Koristi templates gdje god je moguće
- Definiraj sve potrebne varijable prije header.php
- Zatvori database connection nakon footer.php

### ❌ Don't:
- Nemoj miješati PHP i HTML kod
- Nemoj koristiti inline stilove (osim ako je nužno)
- Nemoj ponavljati navigation kod
- Nemoj zaboraviti `$connection->close()`

## 10. Struktura Projekta

```
hotel_managment/
├── templates/
│   ├── header.php          # Template - Zaglavlje
│   └── footer.php          # Template - Podnožje
├── index.php               # ✅ Refaktorirano
├── ajax_search.php         # ✅ Refaktorirano
├── ajax_filter.php         # ✅ Refaktorirano
├── search.php              # ⚠️ Djelomično (PHP odvojen)
├── login.php
├── register.php
├── logout.php
└── ...
```

## Zaključak

Template system omogućava:
- **Čisti kod** - PHP odvojen od HTML-a
- **Reusable components** - header i footer kao predlošci
- **Lakše održavanje** - jedna izmjena na više stranica
- **Konzistentnost** - isti dizajn na cijelom projektu

**Pravilo #1:** PHP logika PRIJE HTML-a!
**Pravilo #2:** Koristi templates za sve nove stranice!
**Pravilo #3:** DRY - Don't Repeat Yourself!
