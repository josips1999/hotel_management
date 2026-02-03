# HTTPS Setup za XAMPP - Kompletna Uputstva

## 📋 Pregled

Ovaj dokument opisuje kako omogućiti **HTTPS (SSL/TLS) na XAMPP-u** za siguran razvoj i testiranje aplikacije.

---

## 🔐 Zašto HTTPS?

- ✅ **Šifriranje**: Svi podaci između browsera i servera su šifrirani
- ✅ **Secure Cookies**: Kolačići se mogu označiti kao "Secure" (šalju se samo preko HTTPS)
- ✅ **Browser Trust**: Moderne funkcije zahtijevaju HTTPS (Geolocation, Camera, etc.)
- ✅ **Production Ready**: Testiranje u uvjetima sličnim produkciji

---

## 🛠️ Setup za Windows (XAMPP)

### Metoda 1: Automatska Instalacija (Preporučeno)

#### 1. Generiraj SSL Certifikat

Otvori **Command Prompt kao Administrator**:

```cmd
cd C:\xampp\apache
makecert.bat
```

**Odgovori na pitanja:**
- Country Name: `HR`
- State: `Zagreb`
- Locality: `Zagreb`
- Organization Name: `Hotel Management`
- Organizational Unit: `Development`
- Common Name: `localhost`
- Email: `admin@localhost`

#### 2. Omogući SSL Modul

Otvori: `C:\xampp\apache\conf\httpd.conf`

**Pronađi i odkomentiraj** (makni `#` na početku):

```apache
LoadModule ssl_module modules/mod_ssl.so
Include conf/extra/httpd-ssl.conf
LoadModule socache_shmcb_module modules/mod_socache_shmcb.so
```

#### 3. Konfiguriraj SSL Virtual Host

Otvori: `C:\xampp\apache\conf\extra\httpd-ssl.conf`

**Pronađi i promijeni:**

```apache
<VirtualHost _default_:443>
    DocumentRoot "C:/xampp/htdocs/hotel_managment"
    ServerName localhost:443
    ServerAdmin admin@localhost
    
    SSLEngine on
    SSLCertificateFile "conf/ssl.crt/server.crt"
    SSLCertificateKeyFile "conf/ssl.key/server.key"
    
    <Directory "C:/xampp/htdocs/hotel_managment">
        Options Indexes FollowSymLinks Includes ExecCGI
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

#### 4. Restartuj Apache

1. Otvori **XAMPP Control Panel**
2. Klikni **Stop** na Apache
3. Pričekaj da se potpuno zaustavi
4. Klikni **Start** na Apache

#### 5. Testiraj

Otvori browser i pristup:
```
https://localhost/hotel_managment/
```

**Očekivano upozorenje:** Browser će prikazati "Your connection is not private" jer je certifikat self-signed.

**Klikni:** Advanced → Proceed to localhost (unsafe)

---

### Metoda 2: Manuelna Generacija Certifikata (OpenSSL)

#### 1. Generiraj Private Key

```cmd
cd C:\xampp\apache
mkdir conf\ssl.crt
mkdir conf\ssl.key

"C:\xampp\apache\bin\openssl.exe" genrsa -out conf\ssl.key\server.key 2048
```

#### 2. Kreiraj Certificate Signing Request (CSR)

```cmd
"C:\xampp\apache\bin\openssl.exe" req -new -key conf\ssl.key\server.key -out server.csr
```

#### 3. Generiraj Self-Signed Certifikat

```cmd
"C:\xampp\apache\bin\openssl.exe" x509 -req -days 365 -in server.csr -signkey conf\ssl.key\server.key -out conf\ssl.crt\server.crt
```

#### 4. Nastavi s korakom 2-5 iz Metode 1

---

## 🔧 Dodatne Konfiguracije

### Preusmjeravanje HTTP → HTTPS (Opcionalno)

Ako želiš da **svi HTTP zahtjevi automatski preusmjere na HTTPS**, već imaš `.htaccess` koji to radi:

```apache
# .htaccess u hotel_managment/
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

**Za localhost development**, možeš onemogućiti redirecte komentiranjem:

```apache
# RewriteCond %{HTTPS} off
# RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

### HSTS (HTTP Strict Transport Security)

Već konfigurirano u `.htaccess`:

```apache
Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
```

Ovo govori browseru da **uvijek koristi HTTPS** za ovu domenu.

---

## 🌐 Setup za Linux/Mac (XAMPP)

### 1. Generiraj SSL Certifikat

```bash
cd /opt/lampp/apache
sudo ./makecert.sh
```

### 2. Omogući SSL

```bash
sudo nano /opt/lampp/etc/httpd.conf
```

Odkomentiraj:
```apache
LoadModule ssl_module modules/mod_ssl.so
Include etc/extra/httpd-ssl.conf
```

### 3. Konfiguriraj Virtual Host

```bash
sudo nano /opt/lampp/etc/extra/httpd-ssl.conf
```

Promijeni `DocumentRoot` i `ServerName`:
```apache
DocumentRoot "/opt/lampp/htdocs/hotel_managment"
ServerName localhost:443
```

### 4. Restartuj

```bash
sudo /opt/lampp/lampp restart
```

---

## 🧪 Testiranje

### 1. Provjeri SSL Status

Otvori: [ssl_status.php](http://localhost/hotel_managment/ssl_status.php)

### 2. Test Login/Register

- **Login:** [https://localhost/hotel_managment/login.php](https://localhost/hotel_managment/login.php)
- **Register:** [https://localhost/hotel_managment/register.php](https://localhost/hotel_managment/register.php)

### 3. Provjeri Certifikat u Browseru

**Chrome/Edge:**
1. Klikni na **padlock ikonu** lijevo od URL-a
2. Klikni **Certificate**
3. Provjeri detalje (Issued to: localhost)

**Firefox:**
1. Klikni na **padlock ikonu**
2. Connection secure → More information
3. View Certificate

### 4. Provjeri Console za Greške

**F12 → Console**

Trebalo bi biti:
- ✅ Nema "Mixed Content" upozorenja
- ✅ Nema "Insecure cookies" upozorenja

---

## ⚠️ Troubleshooting

### Problem: "Apache ne može startati"

**Uzrok:** Port 443 je zauzet

**Rješenje:**
```cmd
netstat -ano | findstr :443
```
Vidi koji proces koristi port 443 i zatvori ga.

### Problem: "SSL_ERROR_RX_RECORD_TOO_LONG"

**Uzrok:** SSL modul nije pravilno učitan

**Rješenje:**
1. Provjeri da je `LoadModule ssl_module` odkomentiran
2. Provjeri da `httpd-ssl.conf` nije oštećen
3. Restartuj Apache

### Problem: "Your connection is not private" (NIJE greška!)

**Uzrok:** Self-signed certifikat

**Rješenje:**
- Ovo je **normalno za development**
- Browser ne vjeruje self-signed certifikatima
- Klikni "Advanced" → "Proceed to localhost"

**Za produkciju**, koristi **pravi SSL certifikat** od:
- [Let's Encrypt](https://letsencrypt.org/) (BESPLATNO!)
- Cloudflare SSL
- Comodo, DigiCert, etc.

### Problem: "Mixed Content Warning"

**Uzrok:** Učitavaš HTTP resurse na HTTPS stranici

**Rješenje:**
Provjeri da svi linkovi koriste `https://` ili relativan path:
```html
<!-- ❌ BAD -->
<script src="http://example.com/script.js"></script>

<!-- ✅ GOOD -->
<script src="https://example.com/script.js"></script>
<script src="/hotel_managment/js/script.js"></script>
```

---

## 🚀 Production Deployment

Za **stvarnu produkciju** (ne localhost), koristi:

### 1. Let's Encrypt (BESPLATNO)

```bash
# Install Certbot
sudo apt install certbot python3-certbot-apache

# Get certificate
sudo certbot --apache -d yourdomain.com -d www.yourdomain.com

# Auto-renewal (cron)
sudo certbot renew --dry-run
```

### 2. Cloudflare SSL

1. Dodaj domenu na Cloudflare
2. Promijeni nameservere
3. SSL/TLS → Full (strict)
4. Automatic HTTPS Rewrites: ON

### 3. cPanel SSL

1. cPanel → SSL/TLS
2. Manage SSL Sites
3. Install SSL Certificate
4. Auto-SSL: Enabled

---

## 📊 Security Headers Checklist

Sve je već konfigurirano u `.htaccess`:

- ✅ `Strict-Transport-Security` (HSTS)
- ✅ `X-Frame-Options` (Clickjacking protection)
- ✅ `X-XSS-Protection` (XSS filter)
- ✅ `X-Content-Type-Options` (MIME sniffing)
- ✅ `Content-Security-Policy` (CSP)
- ✅ `Referrer-Policy`

**Provjeri headers:**
```
https://securityheaders.com/
```

---

## 📝 Zaključak

Nakon ove konfiguracije:

1. **Localhost:** `https://localhost/hotel_managment/` ✅
2. **Secure Cookies:** Automatski se koriste na HTTPS ✅
3. **Auto-redirect:** HTTP → HTTPS (može se disable-ati) ✅
4. **Security Headers:** Svi postavljeni ✅

**Za produkciju:** Zamijeni self-signed certifikat s Let's Encrypt ili sličnim!
