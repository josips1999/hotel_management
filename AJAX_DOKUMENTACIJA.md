# AJAX Username Provjera - Dokumentacija

## 📋 Pregled

Implementirana je **AJAX provjera dostupnosti korisničkog imena** koja omogućava real-time validaciju bez potrebe za reload stranice. Korisnik dobiva trenutni feedback o dostupnosti username-a direktno iz baze podataka.

---

## 🎯 Što je implementirano?

### 1. **Baza podataka**
- Nova tablica `users` kreirana u `instalacija.php`
- Struktura tablice:
  ```sql
  CREATE TABLE users (
      id INT(11) AUTO_INCREMENT PRIMARY KEY,
      username VARCHAR(50) NOT NULL UNIQUE,
      email VARCHAR(255) NOT NULL UNIQUE,
      password VARCHAR(255) NOT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  )
  ```

### 2. **API Endpoint - api/check_username.php**
- Prima username putem GET parametra
- Provjerava format (3-30 znakova, samo slova/brojevi/_)
- Koristi **prepared statements** za SQL injection zaštitu
- Vraća JSON odgovor:
  ```json
  {
    "available": true/false,
    "valid": true/false,
    "message": "Poruka za korisnika"
  }
  ```

### 3. **JavaScript AJAX funkcije - js/client_validation.js**

#### **Glavne funkcije:**

**a) checkUsernameAvailability(username, callback)**
```javascript
// XMLHttpRequest pristup (kompatibilan sa starijim browserima)
function checkUsernameAvailability(username, callback) {
    const xhr = new XMLHttpRequest();
    xhr.open('GET', 'api/check_username.php?username=' + encodeURIComponent(username), true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            callback(response);
        }
    };
    xhr.send();
}
```

**b) checkUsernameAvailabilityFetch(username)**
```javascript
// Moderna Fetch API verzija (async/await)
async function checkUsernameAvailabilityFetch(username) {
    const response = await fetch(`api/check_username.php?username=${encodeURIComponent(username)}`);
    return await response.json();
}
```

**c) validateUsernameWithAjax(inputElement, feedbackElement)**
```javascript
// Kompletan UI handler - validira format i poziva AJAX
function validateUsernameWithAjax(inputElement, feedbackElement) {
    // 1. Validira format
    // 2. Prikazuje "Provjeravam..."
    // 3. Poziva AJAX
    // 4. Prikazuje rezultat (zeleno/crveno)
}
```

### 4. **Registracijska forma - register.php**
- Bootstrap 5 dizajn
- Real-time username provjera
- Password strength indicator
- Email validacija
- Password match provjera
- Terms checkbox validacija

### 5. **Backend registracija - api/register_user.php**
- Validacija svih polja (server-side)
- Provjera duplikata (username i email)
- Password hashing (password_hash())
- Sigurno spremanje u bazu

### 6. **Demo stranica - ajax_demo.php**
- Dokumentacija implementacije
- Live test polje
- Primjeri koda
- Objašnjenja funkcionalnosti

---

## 🚀 Kako koristiti?

### **Korak 1: Kreiranje baze**
```bash
http://localhost/hotel_managment/instalacija.php
```
Ovo će kreirati `users` tablicu.

### **Korak 2: Testiranje AJAX provjere**

**Opcija A - Demo stranica:**
```bash
http://localhost/hotel_managment/ajax_demo.php
```

**Opcija B - Registracijska forma:**
```bash
http://localhost/hotel_managment/register.php
```

### **Korak 3: Integracija u vlastitu formu**

**HTML:**
```html
<input type="text" id="username" class="form-control">
<div id="usernameFeedback"></div>

<script src="js/client_validation.js"></script>
```

**JavaScript:**
```javascript
const usernameInput = document.getElementById('username');
const feedback = document.getElementById('usernameFeedback');

// Pozovi validaciju kada korisnik napusti polje
usernameInput.addEventListener('blur', function() {
    validateUsernameWithAjax(usernameInput, feedback);
});
```

---

## 🔒 Sigurnosne mjere

### **1. SQL Injection zaštita**
```php
// Prepared statements u svim upitima
$stmt = $connection->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
```

### **2. XSS zaštita**
```php
// Sanitizacija output-a
htmlspecialchars($username)
```

### **3. Password hashing**
```php
// BCrypt hashing
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
```

### **4. Input validacija**
```php
// Dual-layer validacija (client + server)
- Format provjera (regex)
- Duljina provjera
- Type provjera
```

---

## 📊 Tok podataka

```
1. Korisnik unosi username → blur event
2. JavaScript validira format (client-side)
3. Prikazuje "Provjeravam..."
4. AJAX šalje GET zahtjev → api/check_username.php
5. PHP provjerava bazu (prepared statement)
6. PHP vraća JSON {available, valid, message}
7. JavaScript procesira odgovor
8. Prikazuje zeleno (dostupno) ili crveno (zauzeto)
```

---

## 🎨 Visual Feedback

### **Bootstrap klase:**
- `.is-valid` - zeleni border (dostupno)
- `.is-invalid` - crveni border (zauzeto)
- `.valid-feedback` - zelena poruka
- `.invalid-feedback` - crvena poruka
- `.text-muted` - siva poruka (loading)

### **Primjer:**
```javascript
// Dostupno
inputElement.classList.add('is-valid');
feedbackElement.textContent = '✓ Username je dostupan';

// Zauzeto
inputElement.classList.add('is-invalid');
feedbackElement.textContent = '✗ Username je zauzet';
```

---

## 🧪 Testni scenariji

### **Test 1: Format validacija**
- Unesi: `ab` → ✗ "Mora imati minimalno 3 znaka"
- Unesi: `user@name` → ✗ "Samo slova, brojevi i _"

### **Test 2: Dostupnost**
- Unesi: `test_user` (prvi put) → ✓ "Dostupno"
- Registriraj se s `test_user`
- Unesi: `test_user` (drugi put) → ✗ "Zauzeto"

### **Test 3: Real-time provjera**
- Unesi username → klikni negdje drugo
- Vidi "Provjeravam..." → zatim rezultat

---

## 📝 Napomene

### **Performance optimizacija:**
```javascript
// Debounce za "input" event (sprječava previše zahtjeva)
let usernameTimeout;
usernameInput.addEventListener('input', function() {
    clearTimeout(usernameTimeout);
    usernameTimeout = setTimeout(function() {
        validateUsernameWithAjax(usernameInput, usernameFeedback);
    }, 500); // Čeka 500ms nakon zadnjeg tipkanja
});
```

### **Browser kompatibilnost:**
- **XMLHttpRequest** - Svi browseri (IE7+)
- **Fetch API** - Moderni browseri (Chrome 42+, Firefox 39+, Edge 14+)

### **Dvije verzije AJAX funkcija:**
1. `checkUsernameAvailability()` - XMLHttpRequest (široka podrška)
2. `checkUsernameAvailabilityFetch()` - Fetch API (moderan pristup)

---

## 🔧 Troubleshooting

### **Problem: AJAX ne radi**
**Rješenje:**
1. Provjeri jesu li svi fajlovi na pravom mjestu
2. Otvori Developer Tools (F12) → Console
3. Provjeri Network tab → vidi li se zahtjev prema `check_username.php`

### **Problem: "Nevažeći JSON"**
**Rješenje:**
1. Otvori `api/check_username.php` direktno u browseru
2. Provjeri vraća li validan JSON
3. Provjeri ima li PHP errora prije `echo json_encode()`

### **Problem: Uvijek vraća "dostupno"**
**Rješenje:**
1. Provjeri je li pokrenuo `instalacija.php`
2. Provjeri postoji li `users` tablica u bazi
3. Provjeri database connection u `lib/db_connection.php`

---

## 📦 Kreirane datoteke

```
hotel_managment/
├── api/
│   ├── check_username.php      # AJAX endpoint za provjeru
│   └── register_user.php        # Backend za registraciju
├── js/
│   └── client_validation.js     # JavaScript AJAX funkcije
├── register.php                 # Registracijska forma
├── ajax_demo.php                # Demo i dokumentacija
└── instalacija.php              # Ažurirano (users tablica)
```

---

## ✅ Zaključak

Implementirana je kompletna AJAX provjera korisničkog imena sa:
- ✓ Real-time validacijom
- ✓ Server-side provjerom baze
- ✓ Visual feedback-om
- ✓ Security best practices
- ✓ Dvije verzije (XMLHttpRequest i Fetch API)
- ✓ Kompletnom registracijskom formom
- ✓ Demo stranicom za testiranje

**Testiranje:** Otvori `ajax_demo.php` ili `register.php` i isprobaj!
