# Hotel Management System

> Kompletan web sustav za upravljanje hotelima s naprednim značajkama i sigurnosnim zaštitama


## 📋 Pregled Projekta

Hotel Management System je potpuno funkcionalna web aplikacija razvijena kao završni projektni rad iz kolegija **Programiranje za Web**. Sustav implementira sve 36 obveznih zahtjeva, uključujući MVC arhitekturu, CRUD operacije, sigurnosne zaštite (CSRF, XSS, SQL Injection), SEO optimizaciju, korisničko upravljanje i audit log sistem.

### 🎯 Glavni Ciljevi

- ✅ **CRUD operacije** - Potpuno upravljanje hotelskim podacima
- ✅ **MVC arhitektura** - Jasna separacija odgovornosti
- ✅ **Sigurnosne zaštite** - CSRF, XSS, SQL Injection prevencija
- ✅ **Korisničko upravljanje** - Autentikacija, autorizacija, uloge (Admin/User/Guest)
- ✅ **SEO optimizacija** - SEO-friendly URL struktura
- ✅ **Responsive dizajn** - Prilagodba svim uređajima
- ✅ **Audit log** - Praćenje svih promjena u sustavu
- ✅ **AJAX komunikacija** - Dinamičko ažuriranje bez page reload

## 🚀 Značajke

### Osnovne Funkcionalnosti
- **Hotel Management**: CRUD operacije (Create, Read, Update, Delete)
- **User Authentication**: Login, registracija, Remember Me funkcionalnost
- **Role-Based Access Control**: 3 uloge (Admin, User, Guest) s različitim pravima
- **Search & Filter**: Napredna pretraga hotela po gradu i županiji
- **Statistics Dashboard**: Grafički prikazi statistika s Chart.js
- **Contact Form**: Kontakt forma s email notifikacijama

### Administratorske Funkcionalnosti
- **User Management**: Upravljanje korisnicima, uloge, blokiranje naloga
- **System Settings**: Dinamička konfiguracija sustava
- **Audit Log**: Pregled svih promjena u bazi podataka
- **Database Backup/Restore**: Sigurnosne kopije baze podataka
- **Security Dashboard**: Pregled sigurnosnih događaja

### Sigurnosne Zaštite
- **CSRF Protection**: Token-based zaštita svih formi (18 endpointa zaštićeno)
- **XSS Prevention**: htmlspecialchars() za sve outpute
- **SQL Injection**: Prepared statements za sve upite
- **Password Security**: bcrypt hashing
- **Account Locking**: Automatsko zaključavanje nakon neuspjelih prijava
- **Session Security**: Session regeneration, timeout kontrola
- **HTTPS Enforcement**: Automatski redirect na HTTPS

### Dodatne Značajke
- **RSS Feed**: Automatski generirani feed za najnovije hotele
- **SEO URLs**: /hotel/123/naziv-hotela format
- **Responsive Design**: Mobile-first pristup s CSS Grid & Flexbox
- **AJAX Operations**: Dinamičko dodavanje/uređivanje bez reload
- **Cookie Management**: Cookie consent banner s GDPR compliance
- **Guest Limits**: Ograničen pregled za neregistrirane korisnike

## 🛠️ Tehnologije

### Backend
- PHP 8.x
- MySQL 8.0 (MySQLi)
- Apache 2.4 (XAMPP)
- PHPMailer

### Frontend
- HTML5
- CSS3 (Grid, Flexbox, Custom Properties)
- JavaScript (ES6+, Fetch API)
- Bootstrap 5.3
- Bootstrap Icons 1.11
- Chart.js 4.x

### Arhitektura
- **MVC Pattern** - Model-View-Controller separacija
- **RESTful API** - JSON komunikacija za AJAX
- **Prepared Statements** - SQL injection prevencija
- **Templating System** - Reusable header/footer komponente

## 📁 Struktura Projekta

```
hotel_managment/
├── api/                    # Backend API endpoints (Controller)
│   ├── add_hotel.php
│   ├── update_hotel.php
│   ├── delete_hotel.php
│   ├── login.php
│   ├── register_user.php
│   ├── user_action.php
│   └── ...
├── assets/                 # Static resources
│   ├── css/
│   │   ├── grid-flexbox.css
│   │   ├── responsive.css
│   │   └── ...
│   ├── js/
│   └── images/
├── lib/                    # Business logic (Model)
│   ├── db_connection.php
│   ├── SessionManager.php
│   ├── Hotel.php
│   ├── User.php
│   ├── AuditLog.php
│   ├── CSRFToken.php
│   ├── SEOHelper.php
│   └── Router.php
├── templates/              # Reusable UI (View)
│   ├── header.php
│   ├── footer.php
│   └── hotel_card.php
├── backups/                # Database backups
├── index.php               # Main hotel listing
├── view.php                # Hotel details (SEO URL)
├── dashboard.php           # User dashboard
├── login.php
├── register.php
├── contact.php
├── admin_panel.php
├── user_management.php
├── system_settings.php
├── audit_log.php
├── statistics.php
├── database_backup.php
├── autor.html              # Author information
├── dokumentacija.html      # Technical documentation
├── security_report.html    # Security audit report
├── .htaccess              # Apache rewrite rules
└── README.md              # This file
```

## 🔧 Instalacija

### Preduvjeti
- XAMPP 8.x (PHP 8.x + MySQL 8.0 + Apache 2.4)
- Web browser (Chrome, Firefox, Edge, Safari)

### Koraci Instalacije

1. **Klonirajte projekt u htdocs direktorij:**
   ```bash
   cd C:\xampp\htdocs
   git clone [repository-url] hotel_managment
   ```

2. **Kreirajte bazu podataka:**
   ```bash
   mysql -u root -p
   CREATE DATABASE hotel_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

3. **Importajte SQL shemu:**
   ```bash
   mysql -u root hotel_management < database_schema.sql
   ```

4. **Konfigurirajte database connection:**
   Uredite `lib/db_connection.php`:
   ```php
   $host = 'localhost';
   $dbname = 'hotel_management';
   $username = 'root';
   $password = ''; // Postavite vašu lozinku
   ```

5. **Pokrenite Apache i MySQL u XAMPP-u**

6. **Pristupite aplikaciji:**
   ```
   http://localhost/hotel_managment
   ```

7. **Default admin pristup:**
   - Username: `admin`
   - Password: `admin123`
   
   **⚠️ VAŽNO:** Promijenite admin lozinku nakon prve prijave!

## 📖 Dokumentacija

Potpuna tehnička dokumentacija dostupna je na:
- **[Tehnička Dokumentacija](http://localhost/hotel_managment/dokumentacija.html)** - Opis projekta, arhitekture, tehnologija
- **[O Autoru](http://localhost/hotel_managment/autor.html)** - Informacije o autoru
- **[Sigurnosni Izvještaj](http://localhost/hotel_managment/security_report.html)** - Detaljni pregled sigurnosnih implementacija

### API Dokumentacija

#### Hotels API

**GET /api/get_hotels.php**
- Vraća sve hotele
- Response: JSON array hotel objekata

**POST /api/add_hotel.php**
- Dodaje novi hotel
- Body: JSON s hotel podacima + CSRF token
- Response: `{success: true, message: "..."}`

**POST /api/update_hotel.php**
- Ažurira postojeći hotel
- Body: JSON s hotel podacima + CSRF token
- Response: `{success: true, message: "..."}`

**POST /api/delete_hotel.php**
- Briše hotel
- Body: `{id: 123, csrf_token: "..."}`
- Response: `{success: true, message: "..."}`

#### Authentication API

**POST /api/login.php**
- Prijava korisnika
- Body: `{username: "...", password: "...", remember: true/false, csrf_token: "..."}`
- Response: `{success: true, redirect: "dashboard.php"}`

**POST /api/register_user.php**
- Registracija novog korisnika
- Body: JSON s user podacima + CSRF token
- Response: `{success: true, message: "..."}`

## 🔒 Sigurnost

### Implementirane Zaštite

#### 1. CSRF Protection
- 32-byte random tokeni
- Session storage s 1-sat expiryjem
- Timing-safe validacija
- **9 formi zaštićeno** (login, register, contact, hotel CRUD, system settings, user actions, backup)
- **9 API endpointa** validira tokene

**Primjer implementacije:**
```php
// U formi:
<?php echo CSRFToken::getField(); ?>

// U API-ju:
CSRFToken::verifyPost(); // Baca exception ako nije validan
```

#### 2. XSS Prevention
- `htmlspecialchars($var, ENT_QUOTES, 'UTF-8')` za sve outpute
- `SEOHelper::escape()` wrapper funkcija
- **5+ stranica zaštićeno**: index.php, view.php, dashboard.php, user_management.php, audit_log.php

**Primjer:**
```php
echo htmlspecialchars($hotel['naziv'], ENT_QUOTES, 'UTF-8');
// ili
echo SEOHelper::escape($hotel['naziv']);
```

#### 3. SQL Injection Prevention
- **100% prepared statements** - Nema direktnog SQL-a
- Parameter binding s tipiziranjem
- MySQLi s bind_param()

**Primjer:**
```php
$stmt = $conn->prepare("SELECT * FROM hotels WHERE id = ?");
$stmt->bind_param("i", $hotelId);
$stmt->execute();
```

#### 4. Authentication Security
- Password hashing s `password_hash()` (bcrypt)
- Account locking nakon 3 neuspjela pokušaja
- Session regeneration pri login/logout
- Remember Me tokeni (64-char, 30 dana)

#### 5. Dodatne Zaštite
- HTTPS enforcement (https_checker.php)
- Session timeout kontrola
- IP tracking u audit logu
- File upload validacija (ako se implementira)

## 🎨 UI/UX Značajke

### Responsive Design
- **Mobile-first** pristup
- CSS Grid & Flexbox layout
- Bootstrap 5 breakpoints
- Custom `@media` queries
- Responsive tables s data-label patternima

### Accessibility
- Semantic HTML5
- ARIA labels
- Keyboard navigation
- Color contrast WCAG AA compliant

### Design Sistem
- **Colors**: Purple gradient (#667eea → #764ba2)
- **Icons**: Bootstrap Icons 1.11
- **Typography**: System font stack
- **Components**: Reusable card, button, form styles

## 📊 Statistike Projekta

- **Linija koda**: ~8,000+
- **PHP datoteka**: 25+
- **CSS datoteka**: 5
- **JavaScript datoteka**: 3
- **Database tablice**: 7
- **API endpoints**: 15+
- **Zahtjeva implementirano**: 36/36 (100%)

## 🧪 Testiranje

### Manualno Testiranje

1. **CSRF Test:**
   - Otvorite DevTools → Network
   - Submit bilo koju formu
   - Provjerite da POST sadrži `csrf_token`

2. **XSS Test:**
   - Pokušajte dodati hotel s nazivom: `<script>alert('XSS')</script>`
   - Script se ne smije izvršiti, već prikazati kao tekst

3. **SQL Injection Test:**
   - Login s username: `' OR '1'='1`
   - Login NE smije uspjeti

4. **Responsive Test:**
   - Resize browser na različite veličine
   - Testiranje na mobilnim uređajima
   - Provjera breakpoints-a

### Alati za Testiranje
- Chrome DevTools
- Firefox Developer Tools
- Postman (API testing)
- OWASP ZAP (security scanning)

## 🐛 Poznati Problemi i Ograničenja

1. **Email**: Kontakt forma zahtijeva SMTP konfiguraciju za production
2. **File Upload**: Trenutno nema upload funkcionalnosti za hotel slike
3. **Pagination**: Search ne podržava pagination (prikazuje sve rezultate)
4. **i18n**: Aplikacija je trenutno samo na hrvatskom jeziku
5. **Browser Support**: Optimizirano za moderne browsere (Chrome 90+, Firefox 88+, Edge 90+, Safari 14+)

## 🚧 Budući Razvoj

### Planirane Značajke
- [ ] Hotel photo gallery upload
- [ ] Hotel rating & review system
- [ ] Booking/reservation sistem
- [ ] Email notifications za admin
- [ ] 2FA (Two-Factor Authentication)
- [ ] Export to PDF/Excel
- [ ] Multi-language support (i18n)
- [ ] Mobile app (Progressive Web App)

### Performance Optimizacije
- [ ] Redis cache za sessions
- [ ] Query caching
- [ ] Image optimization & lazy loading
- [ ] CDN integration
- [ ] Minification & compression

## 📞 Kontakt

**Ime i Prezime Autora**
- Email: student@example.com
- Projekt: Hotel Management System
- Kolegij: Programiranje za Web
- Godina: 2025/2026

## 📄 Licenca

Ovaj projekt je razvijen kao završni projektni rad u edukacijske svrhe.

---

<p align="center">
  <strong>Izrađeno s ❤️ za kolegij Programiranje za Web</strong><br>
  <sub>2026 © Hotel Management System</sub>
</p>
