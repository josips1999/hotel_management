# Token-Based "Remember Me" Funkcionalnost

## 📋 Pregled

Implementiran je **sigurni token-based "Remember Me" sistem** koji koristi:
- Split-token pristup (selector + validator)
- Pohrana hashiranih tokena u bazu podataka
- Podesiv vremenski period (konfigurabilan u `config.php`)
- **VAŽNO:** Lozinka se NIKADA ne pohranjuje u kolačić!

---

## 🔒 Sigurnosni Princip: Split-Token Approach

### Kako radi?

1. **Token se sastoji od dva dijela:**
   - **Selector** (javni identifikator): 32 hex znaka (16 bytes)
   - **Validator** (tajni token): 64 hex znaka (32 bytes)

2. **Pohrana:**
   - **Kolačić:** `selector:validator` (oba plain text)
   - **Baza:** `selector` (plain) + `password_hash(validator)` (hashiran)

3. **Verifikacija:**
   ```php
   // 1. Pročitaj kolačić: selector:validator
   // 2. Nađi token u bazi koristeći selector
   // 3. Usporedi validator s hashiranim validatorom: password_verify()
   // 4. Ako se poklapa → prijavi korisnika
   ```

### Zašto je ovo sigurno?

✅ **Database Compromise:** Čak i ako napadač ukrade bazu, ne može kreirati valjan kolačić jer nema plain validator  
✅ **Timing Attacks:** Koristi `password_verify()` koji je otporan na timing napade  
✅ **Token Revocation:** Svaki token se može individualno opozvati iz baze  
✅ **Brza Pretraga:** Selector omogućava brzo pronalaženje tokena bez full table scan  
✅ **No Password Storage:** Lozinka se NIKADA ne pohranjuje nigdje osim u `users.password` (hashirana)

---

## 📁 Struktura Datoteka

```
hotel_managment/
├── lib/
│   ├── config.php                 ✅ NOVO - Globalne postavke (REMEMBER_ME_DURATION_DAYS)
│   └── SessionManager.php         🔄 AŽURIRANO - Token-based implementacija
├── database/
│   ├── create_remember_tokens.sql ✅ NOVO - Struktura tablice
│   └── install_remember_tokens.sql✅ NOVO - Instalacijski SQL
├── cron/
│   └── clean_expired_tokens.php   ✅ NOVO - Cron job za čišćenje
├── security_sessions.php          ✅ NOVO - Dashboard aktivnih sesija
├── api/login.php                  🔄 AŽURIRANO - Koristi SessionManager($connection)
├── index.php                      🔄 AŽURIRANO - Koristi checkRememberMe()
└── logout.php                     🔄 AŽURIRANO - Briše token iz baze
```

---

## 🗄️ Struktura Baze - `remember_tokens`

```sql
CREATE TABLE remember_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    selector VARCHAR(64) NOT NULL UNIQUE,       -- Javni identifikator
    hashed_validator VARCHAR(255) NOT NULL,     -- Hashiran validator
    expires_at DATETIME NOT NULL,               -- Datum isteka
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_used_at TIMESTAMP NULL,                -- Zadnje korištenje
    ip_address VARCHAR(45) NULL,                -- IP za tracking
    user_agent VARCHAR(255) NULL,               -- Browser info
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_selector (selector),
    INDEX idx_user_id (user_id),
    INDEX idx_expires_at (expires_at)
);
```

### Instalacija Tablice

```bash
# Metoda 1: Koristi phpMyAdmin
# Otvori: http://localhost/phpmyadmin
# Odaberi bazu: hotel_management
# Import: database/install_remember_tokens.sql

# Metoda 2: MySQL CLI
mysql -u root -p hotel_management < database/install_remember_tokens.sql
```

---

## ⚙️ Konfiguracija - `lib/config.php`

```php
// Remember Me Postavke
define('REMEMBER_ME_DURATION_DAYS', 30);      // Trajanje tokena (podesivo!)
define('REMEMBER_ME_COOKIE_NAME', 'hotel_remember');
define('SESSION_TIMEOUT_MINUTES', 30);         // Session timeout

// Token Sigurnost
define('TOKEN_SELECTOR_BYTES', 16);            // 16 bytes = 32 hex chars
define('TOKEN_VALIDATOR_BYTES', 32);           // 32 bytes = 64 hex chars
```

### Kako promijeniti trajanje?

```php
// Promijeni REMEMBER_ME_DURATION_DAYS u config.php:
define('REMEMBER_ME_DURATION_DAYS', 60); // 60 dana
define('REMEMBER_ME_DURATION_DAYS', 7);  // 7 dana
define('REMEMBER_ME_DURATION_DAYS', 90); // 3 mjeseca
```

---

## 💻 Korištenje API-ja

### 1. Login s "Remember Me"

**api/login.php:**
```php
$sessionManager = new SessionManager($connection);
$sessionManager->login($userId, $username, $email, $rememberMe = true);
// Automatski kreira token ako je $rememberMe = true
```

**AJAX Request:**
```javascript
fetch('api/login.php', {
    method: 'POST',
    body: new URLSearchParams({
        usernameOrEmail: 'john@example.com',
        password: 'password123',
        rememberMe: '1',  // Označi za kreiranje tokena
        'g-recaptcha-response': grecaptcha.getResponse()
    })
});
```

### 2. Auto-Login iz Tokena

**index.php:**
```php
$sessionManager = new SessionManager($connection);
$sessionManager->checkRememberMe(); // Automatski prijavljuje iz kolačića
```

**Flow:**
1. Korisnik posjeti stranicu bez aktivne sesije
2. `checkRememberMe()` provjerava postoji li kolačić
3. Parsira `selector:validator` iz kolačića
4. Traži token u bazi pomoću selectora
5. Verificira validator: `password_verify($validator, $hashed_validator)`
6. Ako je valjan → automatski prijavljuje korisnika

### 3. Logout (Briše Token)

**logout.php:**
```php
$sessionManager = new SessionManager($connection);
$sessionManager->logout(); // Briše session + token iz baze + kolačić
```

### 4. Security Dashboard

**security_sessions.php:**
```php
// Prikaži sve aktivne tokene za korisnika
$activeTokens = $sessionManager->getUserActiveTokens();

// Opozovi specifičan token
$sessionManager->revokeToken($tokenId);
```

---

## 🔄 Token Lifecycle

```
┌─────────────────────────────────────────────────────────────┐
│ 1. LOGIN (rememberMe = true)                                │
│    → SessionManager->login($userId, $username, $email, true)│
│    → createRememberToken($userId)                            │
│       ├─ Generiraj selector (16 bytes random)               │
│       ├─ Generiraj validator (32 bytes random)              │
│       ├─ Hash validator: password_hash($validator)          │
│       ├─ INSERT INTO remember_tokens (selector, hashed)     │
│       └─ setcookie('hotel_remember', 'selector:validator')  │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│ 2. BROWSER REOPEN (nova posjeta)                            │
│    → SessionManager->checkRememberMe()                       │
│       ├─ Pročitaj kolačić: explode(':', $_COOKIE)          │
│       ├─ SELECT FROM remember_tokens WHERE selector = ?     │
│       ├─ Verificiraj: password_verify($validator, hashed)   │
│       └─ Ako OK → login($userId, ..., false)                │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│ 3. LOGOUT / REVOKE                                           │
│    → SessionManager->logout()                                │
│       ├─ DELETE FROM remember_tokens WHERE selector = ?     │
│       ├─ setcookie('hotel_remember', '', time() - 3600)     │
│       └─ session_destroy()                                   │
└─────────────────────────────────────────────────────────────┘
```

---

## 🧪 Testiranje

### Test Scenario 1: Remember Me Funkcionalnost

1. **Registriraj se:** `register.php` → Verifikuj email
2. **Prijavi se:** `login.php` → Označi "Zapamti me" → Klikni "Prijavi se"
3. **Provjeri kolačić:** 
   - F12 → Application → Cookies
   - Trebao bi vidjeti: `hotel_remember = <selector>:<validator>`
4. **Zatvori browser** (potpuno)
5. **Otvori ponovo:** `http://localhost/hotel_managment/index.php`
6. **✅ Trebao bi biti automatski prijavljen!**

### Test Scenario 2: Security Dashboard

1. Prijavi se na 2 različita browsera (Chrome + Firefox)
2. Na oba označi "Zapamti me"
3. Otvori `security_sessions.php`
4. Trebao bi vidjeti 2 aktivna tokena
5. Klikni "Opozovi Pristup" na jedan token
6. Drugi browser više ne može auto-login

### Test Scenario 3: Token Expiry

```php
// U config.php postavi:
define('REMEMBER_ME_DURATION_DAYS', 1); // 1 dan za testiranje

// Pričekaj 24 sata ili:
// Manuelno u bazi:
UPDATE remember_tokens SET expires_at = NOW() - INTERVAL 1 DAY;

// Otvori index.php → Neće biti prijavljen (token istekao)
```

---

## 🛠️ Maintenance - Cron Job

**Setup Automatic Cleanup:**

### Linux/Mac (Crontab)
```bash
crontab -e

# Dodaj liniju (pokreće se svaki dan u 3:00 AM):
0 3 * * * /usr/bin/php /path/to/hotel_managment/cron/clean_expired_tokens.php
```

### Windows (Task Scheduler)
```
1. Otvori "Task Scheduler"
2. Create Basic Task → "Clean Remember Tokens"
3. Trigger: Daily, 3:00 AM
4. Action: Start a program
   - Program: C:\xampp\php\php.exe
   - Arguments: C:\xampp\htdocs\hotel_managment\cron\clean_expired_tokens.php
5. Finish
```

### Manual Cleanup
```bash
cd C:\xampp\htdocs\hotel_managment
php cron\clean_expired_tokens.php
```

**Output:**
```
[2026-01-28 03:00:00] Starting cleanup of expired remember tokens...
[2026-01-28 03:00:01] Cleanup complete. Deleted 15 expired token(s).
```

---

## 📊 SessionManager API Reference

### Constructor
```php
$sessionManager = new SessionManager($connection);
// $connection je MySQLi objekt iz db_connection.php
```

### Login & Logout
```php
// Login s remember me
$sessionManager->login($userId, $username, $email, $rememberMe = true);

// Logout (briše sve)
$sessionManager->logout();
```

### Session Check
```php
// Provjeri je li prijavljen
if ($sessionManager->isLoggedIn()) {
    echo "Prijavljen!";
}

// Auto-login iz kolačića
$sessionManager->checkRememberMe();
```

### User Info
```php
$userId = $sessionManager->getUserId();
$username = $sessionManager->getUsername();
$email = $sessionManager->getEmail();

// Detaljne info
$info = $sessionManager->getSessionInfo();
print_r($info);
```

### Token Management
```php
// Dohvati sve aktivne tokene za korisnika
$tokens = $sessionManager->getUserActiveTokens();

// Opozovi specifičan token
$sessionManager->revokeToken($tokenId);

// Očisti istekle tokene (cron job)
$deletedCount = $sessionManager->cleanExpiredTokens();
```

---

## 🔐 Sigurnosne Best Practices

### ✅ Implementirano

1. **Split-Token:** Selector + Hashed Validator
2. **Password Hashing:** BCrypt za validator
3. **Database Cascade:** ON DELETE CASCADE za user_id
4. **HttpOnly Cookies:** JavaScript ne može pristupiti
5. **Session Regeneration:** Sprječava session fixation
6. **IP/User-Agent Check:** Sprječava session hijacking
7. **Token Expiry:** Automatski istek nakon N dana
8. **Individual Revocation:** Korisnik može opozvati tokene

### 🚀 Production Checklist

- [ ] Promijeni reCAPTCHA test keys u produkcijske
- [ ] Postavi `'secure' => true` u cookie params (HTTPS)
- [ ] Konfiguriraj REMEMBER_ME_DURATION_DAYS prema potrebi
- [ ] Setup cron job za clean_expired_tokens.php
- [ ] Implementiraj rate limiting za login (opciono)
- [ ] Dodaj email notifikaciju za novi token (opciono)
- [ ] Logiranje neuspjelih login pokušaja (opciono)

---

## 📝 Razlike: Stara vs Nova Implementacija

| Feature | Stara Implementacija | Nova Implementacija |
|---------|---------------------|---------------------|
| **Pohrana tokena** | Enkriptirani kolačić (JSON) | Baza podataka + split-token |
| **Sigurnost** | Osnovna (base64) | Visoka (BCrypt hashing) |
| **Revocation** | Samo brisanje kolačića | Individualno iz baze |
| **Tracking** | Nema | IP + User-Agent + last_used_at |
| **Expiry Check** | Hardcoded u kolačiću | Baza + konfigurabilan period |
| **Multiple Devices** | Ne podržava | ✅ Podržava (svaki device = token) |
| **Security Dashboard** | Nema | ✅ security_sessions.php |
| **Database Compromise** | Lako kompromitirati | ✅ Otpornost na krađu baze |
| **Cron Cleanup** | Nema | ✅ Automatsko čišćenje |

---

## 🎯 Zaključak

Implementiran je **enterprise-grade "Remember Me" sistem** koji:

✅ **NIKADA ne pohranjuje lozinku** (ni hashiranu) u kolačić  
✅ Koristi **split-token pristup** za maksimalnu sigurnost  
✅ Omogućava **individualno opozivanje tokena** po uređaju  
✅ Ima **podesiv vremenski period** (config.php)  
✅ Sprječava **database theft attacks**  
✅ Uključuje **security dashboard** za korisnike  
✅ Automatsko čišćenje isteklih tokena (cron job)

**Postavke se mogu lako mijenjati** u `lib/config.php` bez diranja koda!
