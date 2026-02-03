# Full-Text Search Implementacija

## 📋 Pregled

Implementirana je **Full-Text pretraga** koristeći MySQL MATCH() AGAINST() funkcije nad **5 stupaca podataka** u **2 tablice**.

## 🗄️ Pretražuje Se

### Tablica 1: `hotels`
- **naziv** - Naziv hotela
- **adresa** - Adresa
- **grad** - Grad

### Tablica 2: `users`
- **username** - Korisničko ime
- **email** - Email adresa

**Ukupno: 5 stupaca u 2 različite tablice**

## 🔧 Tehnička Implementacija

### 1. Full-Text Indeksi (MySQL)

```sql
-- Hotel tablica - 3 stupca
ALTER TABLE hotels ADD FULLTEXT INDEX ft_hotels_search (naziv, adresa, grad);

-- Users tablica - 2 stupca
ALTER TABLE users ADD FULLTEXT INDEX ft_users_search (username, email);
```

### 2. SearchEngine Klasa (PHP)

**Lokacija:** `lib/SearchEngine.php`

**Glavne Metode:**

#### `search($searchTerm, $mode)`
Glavna metoda za pretragu. Vraća rezultate iz obje tablice.

```php
$searchEngine = new SearchEngine($conn);
$results = $searchEngine->search('Zagreb');
```

**Return struktura:**
```php
[
    'success' => true,
    'search_term' => 'Zagreb',
    'hotels' => [...],      // Pronađeni hoteli
    'users' => [...],       // Pronađeni korisnici
    'total_results' => 5
]
```

#### `searchHotels($searchTerm, $mode)`
Pretraga u hotels tablici koristeći Full-Text Search:

```php
SELECT 
    id, naziv, adresa, grad, kapacitet, broj_soba,
    MATCH(naziv, adresa, grad) AGAINST(? IN NATURAL LANGUAGE MODE) AS relevance
FROM hotels
WHERE MATCH(naziv, adresa, grad) AGAINST(? IN NATURAL LANGUAGE MODE)
ORDER BY relevance DESC
LIMIT 20
```

**Karakteristike:**
- Pretražuje 3 stupca: naziv, adresa, grad
- Koristi MATCH() AGAINST() - vlastiti SQL (ne DataTables)
- Rangira po relevantnosti (relevance score)
- Highlight search term u rezultatima

#### `searchUsers($searchTerm, $mode)`
Pretraga u users tablici:

```php
SELECT 
    id, username, email, first_name, last_name,
    MATCH(username, email) AGAINST(? IN NATURAL LANGUAGE MODE) AS relevance
FROM users
WHERE MATCH(username, email) AGAINST(? IN NATURAL LANGUAGE MODE)
ORDER BY relevance DESC
LIMIT 20
```

**Karakteristike:**
- Pretražuje 2 stupca: username, email
- Rangira po relevantnosti
- Highlight search term

### 3. Search Modes

#### Natural Language Mode (Default)
```php
SearchEngine::MODE_NATURAL
```
- Prirodna pretraga
- Automatsko rangiranje po relevantnosti
- Ignorira stop words

#### Boolean Mode
```php
SearchEngine::MODE_BOOLEAN
```
Napredna sintaksa:
- `+word` - Mora sadržavati
- `-word` - Ne smije sadržavati  
- `"exact phrase"` - Točna fraza
- `word1 word2` - Jedno ili oba

Primjer: `+Zagreb -Split` (mora Zagreb, ne smije Split)

#### Query Expansion Mode
```php
SearchEngine::MODE_QUERY_EXPANSION
```
- Automatski proširuje pretragu
- Traži sličnije pojmove

### 4. Dodatne Funkcionalnosti

#### Highlight Search Term
```php
private function highlightText($text, $searchTerm)
```
- Automatski označava pronađene riječi sa `<mark>` tagom
- Koristi regex za case-insensitive matching

#### Relevance Score
```php
MATCH(naziv, adresa, grad) AGAINST('Zagreb') AS relevance
```
- MySQL automatski računa relevantnost (0-10+)
- Veći score = relevantniji rezultat
- Sortiranje po `relevance DESC`

## 📄 Kreirane Datoteke

### 1. `lib/SearchEngine.php`
- SearchEngine klasa
- searchHotels() i searchUsers() metode
- Highlight i relevance scoring
- 200+ linija koda

### 2. `search.php`
- Frontend stranica za pretragu
- Bootstrap 5 UI
- Real-time rezultati
- Suggestion chips
- 300+ linija HTML/PHP/CSS

### 3. `api/search.php`
- API endpoint za AJAX pozive
- JSON response format
- Support za različite search modes

### 4. `database/create_fulltext_indexes.sql`
- SQL za kreiranje Full-Text indeksa
- Instalacija preko MySQL command line

## 🚀 Korištenje

### PHP Primjer:
```php
require_once('lib/db_connection.php');
require_once('lib/SearchEngine.php');

$searchEngine = new SearchEngine($conn);

// Natural language search
$results = $searchEngine->search('Zagreb');

// Boolean search
$results = $searchEngine->search('+hotel -Split', SearchEngine::MODE_BOOLEAN);

// Ispis rezultata
foreach ($results['hotels'] as $hotel) {
    echo $hotel['naziv_highlighted'] . ' - Relevance: ' . $hotel['relevance'];
}
```

### API Primjer:
```javascript
// GET request
fetch('api/search.php?q=Zagreb&mode=natural')
    .then(response => response.json())
    .then(data => {
        console.log('Pronađeno:', data.total_results);
        console.log('Hoteli:', data.hotels);
        console.log('Korisnici:', data.users);
    });
```

### Direktan pristup:
```
http://localhost/hotel_managment/search.php
```

## ✅ Ispunjeni Zahtjevi

### ✅ Minimalno 2 stupca podataka
- **hotels:** naziv + adresa + grad = **3 stupca**
- **users:** username + email = **2 stupca**
- **UKUPNO: 5 stupaca** ✅

### ✅ Minimalno 2 različite tablice
- **hotels** tablica ✅
- **users** tablica ✅

### ✅ Vlastiti PHP i SQL kod
- SearchEngine.php klasa (vlastiti kod)
- SQL MATCH() AGAINST() query (vlastiti SQL)
- **NE koristi DataTables** ✅
- **NE koristi gotove JS alate** ✅

### ✅ Full-Text Search
- MySQL Full-Text indeksi kreirani
- MATCH() AGAINST() funkcije
- Relevance scoring
- Natural Language Mode + Boolean Mode

## 🔍 Testiranje

### Test 1: Pretraži Zagreb
```
URL: search.php?q=Zagreb
```
Trebao bi pronaći:
- Hotele u Zagrebu
- Hotele sa "Zagreb" u adresi
- Korisnike sa "Zagreb" u emailu

### Test 2: Boolean Search
```
URL: search.php?q=+hotel+-Split&mode=boolean
```
Pronađi sve sa "hotel" ali NE "Split"

### Test 3: Provjera Indeksa
```sql
SHOW INDEX FROM hotels WHERE Key_name = 'ft_hotels_search';
SHOW INDEX FROM users WHERE Key_name = 'ft_users_search';
```
Trebao bi vidjeti 3 stupca (naziv, adresa, grad) za hotels i 2 (username, email) za users.

## 📊 Performance

### Full-Text prednosti:
- **Brže od LIKE %term%** - koristi indekse
- **Relevance ranking** - bolji rezultati
- **Scalable** - radi i sa milijunima redova
- **Stop words filtering** - automatski
- **Case insensitive** - default behaviour

### Limitacije:
- Minimalna duljina riječi: 3 znaka (MySQL default: `ft_min_word_len=4`, može se promijeniti)
- Koristi MyISAM ili InnoDB (MySQL 5.6+)

## 🎨 UI Karakteristike

- **Responsive design** - Bootstrap 5
- **Real-time search** - submit forma
- **Highlight rezultata** - <mark> tag
- **Relevance score** - vidljiv za svaki rezultat
- **Type badges** - razlikuje hotele/korisnike
- **Suggestion chips** - brzi pristup
- **Statistics dashboard** - broj indexed zapisa

## 📝 Napomene

1. **Full-Text indeksi instalirani**: Izvršen `create_fulltext_indexes.sql`
2. **Vlastiti kod**: Sve napisano from scratch, bez external libraries
3. **Production ready**: Error handling, SQL injection zaštita (prepared statements)
4. **Extensible**: Lako dodati nove tablice (npr. rezervacije, ocjene)

---

**Zaključak:** Implementirana je **potpuno funkcionalna Full-Text pretraga** sa **5 stupaca** u **2 tablice**, koristeći **100% vlastiti PHP i SQL kod**, bez DataTables ili drugih gotovih JavaScript alata. ✅
