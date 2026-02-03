<?php
/**
 * SearchEngine - Full-Text Search Engine
 * 
 * Implementacija Full-Text pretrage nad više tablica i stupaca.
 * Koristi MySQL MATCH() AGAINST() za efikasnu pretragu.
 * 
 * Pretražuje:
 * - hotels: name, description
 * - users: username, email
 */

class SearchEngine {
    private $conn;
    
    /**
     * Minimalna duljina search pojma (MySQL Full-Text ograničenje)
     */
    const MIN_SEARCH_LENGTH = 3;
    
    /**
     * Search mode opcije:
     * - NATURAL: Prirodna pretraga (default)
     * - BOOLEAN: Boolean pretraga (+word -word "phrase")
     * - QUERY_EXPANSION: Proširena pretraga
     */
    const MODE_NATURAL = 'IN NATURAL LANGUAGE MODE';
    const MODE_BOOLEAN = 'IN BOOLEAN MODE';
    const MODE_QUERY_EXPANSION = 'WITH QUERY EXPANSION';
    
    /**
     * Polja koja se pretražuju:
     * - hotels: naziv, adresa, grad (3 stupca)
     * - users: username, email (2 stupca)
     */
    
    public function __construct($db_connection) {
        $this->conn = $db_connection;
    }
    
    /**
     * Glavna metoda za pretragu
     * 
     * @param string $searchTerm - Pojam za pretragu
     * @param string $mode - Search mode (default: NATURAL)
     * @param int $page - Current page (default: 1)
     * @param int $itemsPerPage - Items per page (default: from config)
     * @return array - Rezultati pretrage iz obje tablice
     */
    public function search($searchTerm, $mode = self::MODE_NATURAL, $page = 1, $itemsPerPage = null) {
        // Sanitizacija input-a
        $searchTerm = trim($searchTerm);
        
        // Validacija duljine
        if (strlen($searchTerm) < self::MIN_SEARCH_LENGTH) {
            return [
                'success' => false,
                'error' => 'Pretraga mora sadržavati minimalno ' . self::MIN_SEARCH_LENGTH . ' znaka.',
                'hotels' => [],
                'users' => [],
                'total_results' => 0
            ];
        }
        
        // Use config value if not specified
        if ($itemsPerPage === null) {
            $itemsPerPage = defined('ITEMS_PER_PAGE') ? ITEMS_PER_PAGE : 10;
        }
        
        // Escape special characters za SQL
        $escapedTerm = $this->conn->real_escape_string($searchTerm);
        
        // Get counts first for pagination
        $hotelsCount = $this->getHotelsCount($escapedTerm, $mode);
        $usersCount = $this->getUsersCount($escapedTerm, $mode);
        
        // Calculate offset
        $offset = ($page - 1) * $itemsPerPage;
        
        // Pretraži obje tablice sa paginacijom
        $hotelResults = $this->searchHotels($escapedTerm, $mode, $itemsPerPage, $offset);
        $userResults = $this->searchUsers($escapedTerm, $mode, $itemsPerPage, $offset);
        
        return [
            'success' => true,
            'search_term' => $searchTerm,
            'hotels' => $hotelResults,
            'users' => $userResults,
            'total_results' => $hotelsCount + $usersCount,
            'pagination' => [
                'current_page' => $page,
                'items_per_page' => $itemsPerPage,
                'hotels_count' => $hotelsCount,
                'users_count' => $usersCount,
                'total_items' => $hotelsCount + $usersCount
            ]
        ];
    }
    
    /**
     * Pretraga u tablici hotels
     * Full-Text Search preko stupaca: naziv, adresa, grad (3 stupca)
     * 
     * @param string $searchTerm - Escaped search term
     * @param string $mode - Search mode
     * @param int $limit - Number of results (default: 20)
     * @param int $offset - Offset for pagination (default: 0)
     * @return array - Rezultati s relevance score-om
     */
    private function searchHotels($searchTerm, $mode, $limit = 20, $offset = 0) {
        $sql = "SELECT 
                    id,
                    naziv,
                    adresa,
                    grad,
                    zupanija,
                    kapacitet,
                    broj_soba,
                    broj_gostiju,
                    slobodno_soba,
                    MATCH(naziv, adresa, grad) AGAINST(? {$mode}) AS relevance
                FROM hotels
                WHERE MATCH(naziv, adresa, grad) AGAINST(? {$mode})
                ORDER BY relevance DESC
                LIMIT ? OFFSET ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssii", $searchTerm, $searchTerm, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $hotels = [];
        while ($row = $result->fetch_assoc()) {
            // Highlight search term u rezultatima
            $row['naziv_highlighted'] = $this->highlightText($row['naziv'], $searchTerm);
            $row['adresa_highlighted'] = $this->highlightText($row['adresa'], $searchTerm);
            $row['grad_highlighted'] = $this->highlightText($row['grad'], $searchTerm);
            $row['relevance'] = round($row['relevance'], 2);
            $row['type'] = 'hotel';
            
            $hotels[] = $row;
        }
        
        $stmt->close();
        return $hotels;
    }
    
    /**
     * Get total count of hotels matching search
     * 
     * @param string $searchTerm - Escaped search term
     * @param string $mode - Search mode
     * @return int - Total count
     */
    private function getHotelsCount($searchTerm, $mode) {
        $sql = "SELECT COUNT(*) as total
                FROM hotels
                WHERE MATCH(naziv, adresa, grad) AGAINST(? {$mode})";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return (int)$row['total'];
    }
    
    /**
     * Pretraga u tablici users
     * Full-Text Search preko stupaca: username, email
     * 
     * @param string $searchTerm - Escaped search term
     * @param string $mode - Search mode
     * @param int $limit - Number of results (default: 20)
     * @param int $offset - Offset for pagination (default: 0)
     * @return array - Rezultati s relevance score-om
     */
    private function searchUsers($searchTerm, $mode, $limit = 20, $offset = 0) {
        $sql = "SELECT 
                    id,
                    username,
                    email,
                    created_at,
                    MATCH(username, email) AGAINST(? {$mode}) AS relevance
                FROM users
                WHERE MATCH(username, email) AGAINST(? {$mode})
                ORDER BY relevance DESC
                LIMIT ? OFFSET ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssii", $searchTerm, $searchTerm, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $users = [];
        while ($row = $result->fetch_assoc()) {
            // Highlight search term u rezultatima
            $row['username_highlighted'] = $this->highlightText($row['username'], $searchTerm);
            $row['email_highlighted'] = $this->highlightText($row['email'], $searchTerm);
            $row['relevance'] = round($row['relevance'], 2);
            $row['type'] = 'user';
            
            $users[] = $row;
        }
        
        $stmt->close();
        return $users;
    }
    
    /**
     * Get total count of users matching search
     * 
     * @param string $searchTerm - Escaped search term
     * @param string $mode - Search mode
     * @return int - Total count
     */
    private function getUsersCount($searchTerm, $mode) {
        $sql = "SELECT COUNT(*) as total
                FROM users
                WHERE MATCH(username, email) AGAINST(? {$mode})";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return (int)$row['total'];
    }
    
    /**
     * Highlight search term u tekstu
     * 
     * @param string $text - Originalni tekst
     * @param string $searchTerm - Pojam za highlight
     * @return string - HTML sa <mark> tagom
     */
    private function highlightText($text, $searchTerm) {
        if (empty($text) || empty($searchTerm)) {
            return htmlspecialchars($text);
        }
        
        // Razdvoji search term na riječi
        $words = preg_split('/\s+/', $searchTerm);
        $escapedText = htmlspecialchars($text);
        
        foreach ($words as $word) {
            if (strlen($word) >= 2) {
                $escapedText = preg_replace(
                    '/(' . preg_quote($word, '/') . ')/i',
                    '<mark>$1</mark>',
                    $escapedText
                );
            }
        }
        
        return $escapedText;
    }
    
    /**
     * Dobij statistics o pretrazi
     * 
     * @return array - Broj indexed redova po tablicama
     */
    public function getSearchStats() {
        $stats = [];
        
        // Broj hotela
        $result = $this->conn->query("SELECT COUNT(*) as count FROM hotels");
        $stats['hotels_count'] = $result->fetch_assoc()['count'];
        
        // Broj korisnika
        $result = $this->conn->query("SELECT COUNT(*) as count FROM users");
        $stats['users_count'] = $result->fetch_assoc()['count'];
        
        // Provjeri da li postoje Full-Text indeksi
        $result = $this->conn->query("SHOW INDEX FROM hotels WHERE Key_name = 'ft_hotels_search'");
        $stats['hotels_ft_indexed'] = $result->num_rows > 0;
        
        $result = $this->conn->query("SHOW INDEX FROM users WHERE Key_name = 'ft_users_search'");
        $stats['users_ft_indexed'] = $result->num_rows > 0;
        
        return $stats;
    }
    
    /**
     * Preporučene pretraživačke upite (suggestions)
     * 
     * @return array - Lista popularnih termina
     */
    public function getSearchSuggestions() {
        return [
            'Hoteli' => ['Zagreb', 'Split', 'Rijeka', 'Osijek', 'hotel'],
            'Korisnici' => ['admin', 'user', 'test', '@gmail', '@hotmail']
        ];
    }
}
