# Audit Logging System - Dokumentacija

## Implementacija Audit Logginga

Sustav za praćenje svih promjena podataka s Unix timestampom i reusable funkcijom.

## 1. Database Schema

### Tablica: `audit_log`

```sql
CREATE TABLE audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    table_name VARCHAR(100) NOT NULL,      -- Naziv tablice
    record_id INT NOT NULL,                -- ID zapisa
    action VARCHAR(20) NOT NULL,           -- INSERT, UPDATE, DELETE
    old_data TEXT NULL,                    -- Stari podaci (JSON)
    new_data TEXT NULL,                    -- Novi podaci (JSON)
    changed_by INT NULL,                   -- User ID
    changed_at INT NOT NULL,               -- Unix timestamp
    ip_address VARCHAR(45) NULL,           -- IP adresa
    user_agent TEXT NULL,                  -- Browser info
    INDEX idx_table_record (table_name, record_id),
    INDEX idx_changed_at (changed_at),
    FOREIGN KEY (changed_by) REFERENCES users(id)
);
```

**Key Features:**
- ✅ Unix timestamp (`changed_at`) za sve promjene
- ✅ JSON format za stare/nove podatke
- ✅ User tracking (tko je napravio promjenu)
- ✅ IP adresa i User Agent
- ✅ Indexi za brže upite

## 2. AuditLogger Class

**File:** `lib/AuditLogger.php`

### Reusable Funkcija:

```php
$logger = new AuditLogger($connection, $userId);

// INSERT
$logger->logInsert('hotels', $newId, $newData);

// UPDATE
$logger->logUpdate('hotels', $id, $oldData, $newData);

// DELETE
$logger->logDelete('hotels', $id, $oldData);
```

### Metode:

#### Core Logging:
- `log($table, $recordId, $action, $oldData, $newData)` - Osnovna funkcija
- `logInsert($table, $recordId, $newData)` - Za INSERT
- `logUpdate($table, $recordId, $oldData, $newData)` - Za UPDATE
- `logDelete($table, $recordId, $oldData)` - Za DELETE

#### Query Methods:
- `getHistory($table, $recordId, $limit)` - Povijest za određeni zapis
- `getRecentChanges($limit, $table)` - Nedavne promjene
- `getUserActivity($userId, $limit)` - Aktivnost korisnika
- `getStatistics($days)` - Statistika promjena

#### Utility Methods:
- `formatTimestamp($timestamp, $format)` - Format Unix timestamp
- `timeAgo($timestamp)` - "prije 5 minuta"
- `getClientIP()` - Dohvaća IP adresu (handles proxies)

### Constructor:

```php
public function __construct($db, $userId = null)
```

**Parametri:**
- `$db` (mysqli) - Database connection
- `$userId` (int|null) - ID korisnika (null za system akcije)

**Automatski dohvaća:**
- IP adresu korisnika
- User agent (browser info)

## 3. Implementacija u HotelController

### Constructor:

```php
public function __construct($db) {
    $this->db = $db;
    $this->hotelModel = new Hotel($db);
    $this->validator = new Validator();
    
    // Initialize audit logger
    $sessionManager = new SessionManager($db);
    $userId = $sessionManager->getUserId();
    $this->auditLogger = new AuditLogger($db, $userId);
}
```

### CREATE (INSERT):

```php
public function store($data) {
    // ... validation ...
    
    $newId = $this->hotelModel->create($hotelData);
    
    // AUDIT LOG
    $this->auditLogger->logInsert('hotels', $newId, $hotelData);
    
    return ['success' => true, 'id' => $newId];
}
```

### UPDATE:

```php
public function update($id, $data) {
    // Get old data before update
    $oldData = $this->hotelModel->findById($id);
    
    // ... validation & update ...
    
    $success = $this->hotelModel->update($id, $updateData);
    
    // AUDIT LOG
    if ($success && $oldData) {
        $this->auditLogger->logUpdate('hotels', $id, $oldData, $updateData);
    }
    
    return ['success' => true];
}
```

### DELETE:

```php
public function destroy($id) {
    // Get data before deletion
    $hotel = $this->hotelModel->findById($id);
    
    $success = $this->hotelModel->delete($id);
    
    // AUDIT LOG
    if ($success) {
        $this->auditLogger->logDelete('hotels', $id, $hotel);
    }
    
    return ['success' => true];
}
```

## 4. Audit Log Viewer (audit_log.php)

### Features:
- ✅ Prikaz svih promjena
- ✅ Filtriranje po tablici
- ✅ Filtriranje po akciji (INSERT/UPDATE/DELETE)
- ✅ Statistika (zadnjih 30 dana)
- ✅ Detalji promjena (modal)
- ✅ Unix timestamp prikaz
- ✅ "Time ago" format
- ✅ User i IP tracking

### Filtri:

```php
// Filter po tablici
GET /audit_log.php?table=hotels

// Filter po akciji
GET /audit_log.php?action=DELETE

// Filter po korisniku
GET /audit_log.php?user=1

// Limit rezultata
GET /audit_log.php?limit=250
```

### Prikaz:

| Kolona | Opis |
|--------|------|
| ID | Audit log ID |
| Vrijeme | Human-readable + "time ago" |
| Unix Timestamp | Raw timestamp |
| Tablica | Naziv tablice |
| Record ID | ID zapisa |
| Akcija | INSERT/UPDATE/DELETE badge |
| Korisnik | Username |
| IP Adresa | Klijent IP |
| Detalji | Button za modal |

## 5. Unix Timestamp

### Pohrana:

```php
$timestamp = time(); // Current Unix timestamp
```

**Format:** Integer (broj sekundi od 1. siječnja 1970.)

**Primjer:** `1738080000` = 28. januar 2026., 12:00:00

### Konverzija:

```php
// Unix timestamp -> Human readable
AuditLogger::formatTimestamp(1738080000, 'Y-m-d H:i:s');
// Output: "2026-01-28 12:00:00"

// Unix timestamp -> Time ago
AuditLogger::timeAgo(1738080000);
// Output: "prije 5 minuta"
```

### SQL Upiti:

```sql
-- Promjene u zadnja 24 sata
SELECT * FROM audit_log 
WHERE changed_at >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 24 HOUR));

-- Konverzija u SQL
SELECT FROM_UNIXTIME(changed_at) as formatted_time
FROM audit_log;
```

## 6. JSON Format Podataka

### Primjer OLD_DATA (UPDATE):

```json
{
  "id": "5",
  "naziv": "Hotel Adriatic",
  "adresa": "Obala 123",
  "grad": "Split",
  "kapacitet": "150",
  "broj_soba": "50"
}
```

### Primjer NEW_DATA (UPDATE):

```json
{
  "naziv": "Hotel Adriatic Premium",
  "adresa": "Obala 123",
  "grad": "Split",
  "kapacitet": "200",
  "broj_soba": "60"
}
```

**Razlika prikazana u modal prozoru.**

## 7. Primjeri Upotrebe

### Osnovni Log:

```php
require_once('lib/AuditLogger.php');

$logger = new AuditLogger($connection, $userId);

// Logiranje INSERT-a
$newData = ['naziv' => 'Hotel Test', 'grad' => 'Zagreb'];
$logger->logInsert('hotels', 15, $newData);
```

### Dohvaćanje Povijesti:

```php
// Povijest za hotel ID 5
$history = $logger->getHistory('hotels', 5, 20);

foreach ($history as $entry) {
    echo $entry['action'] . ' by ' . $entry['username'];
    echo ' at ' . AuditLogger::formatTimestamp($entry['changed_at']);
}
```

### Statistika:

```php
// Statistika za zadnjih 30 dana
$stats = $logger->getStatistics(30);

echo "Total changes: " . $stats['total_changes'];
echo "Inserts: " . $stats['inserts'];
echo "Updates: " . $stats['updates'];
echo "Deletes: " . $stats['deletes'];
```

### Aktivnost Korisnika:

```php
// Sve akcije korisnika ID 1
$activity = $logger->getUserActivity(1, 50);

foreach ($activity as $log) {
    echo $log['table_name'] . ' - ' . $log['action'];
}
```

## 8. Prednosti Sistema

### ✅ Reusability
- Jedna klasa za sve tablice
- Jednostavno pozivanje: `logInsert()`, `logUpdate()`, `logDelete()`
- Može se koristiti svugdje u aplikaciji

### ✅ Transparency
- Sve promjene zapisane
- Tko je napravio promjenu
- Kada je nastala (Unix timestamp)
- Što je promijenjeno (old vs new data)

### ✅ Compliance
- Audit trail za regulatorne zahtjeve
- Neobrisive promjene (append-only log)
- Potpuno praćenje povijesti

### ✅ Performance
- Indexi na važne kolone
- JSON format (kompaktno)
- Unix timestamp (brži od datetime)

## 9. Security

### User Tracking:
```php
// Automatski dohvaća trenutnog korisnika
$sessionManager = new SessionManager($db);
$userId = $sessionManager->getUserId();
$logger = new AuditLogger($db, $userId);
```

### IP Tracking:
```php
// Automatski dohvaća IP (handles proxies)
private function getClientIP() {
    // Checks: X-Forwarded-For, X-Real-IP, REMOTE_ADDR
}
```

### Data Integrity:
- Foreign key constraint na `users` tablicu
- ON DELETE SET NULL (zadržava log i ako je user obrisan)
- JSON validation pri dohvaćanju

## 10. Testiranje

### Test INSERT:

1. Dodaj novi hotel preko forme
2. Otvori `audit_log.php`
3. Trebao bi vidjeti novi INSERT zapis

### Test UPDATE:

1. Uredi postojeći hotel
2. Provjeri audit log
3. Klikni "Detalji" - vidi old vs new data

### Test DELETE:

1. Obriši hotel
2. Provjeri audit log
3. Vidi old_data prije brisanja

### SQL Provjera:

```sql
-- Najnovijih 10 promjena
SELECT * FROM audit_log ORDER BY changed_at DESC LIMIT 10;

-- Promjene za određeni hotel
SELECT * FROM audit_log 
WHERE table_name = 'hotels' AND record_id = 5;

-- Sve DELETE akcije
SELECT * FROM audit_log WHERE action = 'DELETE';
```

## 11. Proširenja

### Dodavanje Audit Logginga u Druge Tablice:

```php
// U UsersController.php
require_once(__DIR__ . '/../../lib/AuditLogger.php');

class UsersController {
    private $auditLogger;
    
    public function __construct($db) {
        $this->auditLogger = new AuditLogger($db, $userId);
    }
    
    public function register($data) {
        $newUserId = $this->userModel->create($data);
        
        // AUDIT LOG
        $this->auditLogger->logInsert('users', $newUserId, $data);
    }
}
```

### Custom Akcije:

Za posebne slučajeve (npr. login attempts), možeš proširiti:

```php
// Logiranje login pokušaja
$logger->log('login_attempts', $userId, 'LOGIN_SUCCESS', null, [
    'ip' => $_SERVER['REMOTE_ADDR'],
    'timestamp' => time()
]);
```

## Zaključak

✅ **Requirement Ispunjen:**
- Zasebna tablica (`audit_log`)
- Unix timestamp (`changed_at`)
- Reusable funkcija (`AuditLogger` klasa)
- Poziva se na svim potrebnim mjestima (CRUD operacije)

**Sve promjene podataka bilježe se automatski!**
