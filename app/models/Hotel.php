<?php
/**
 * Hotel Model
 * Handles all database operations for hotels
 */

 mysqli_select_db($connection,'hotel_management');
class Hotel {
    private $db;
    
    public function __construct($connection) {
        $this->db = $connection;
    }
    
    /**
     * Find hotel by ID
     * @param int $id - Hotel ID
     * @return array|null - Hotel data or null if not found
     */
    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM hotels WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $hotel = $result->fetch_assoc();
        $stmt->close();
        return $hotel; // Returns associative array or null
    }
    
    /**
     * Get all hotels
     * @return array - Array of all hotels
     */
    public function getAll() {
        $sql = "SELECT * FROM hotels ORDER BY naziv ASC";
        $result = $this->db->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Get total count of hotels
     * @return int - Total number of hotels
     */
    public function getCount() {
        $sql = "SELECT COUNT(*) as total FROM hotels";
        $result = $this->db->query($sql);
        $row = $result->fetch_assoc();
        return (int)$row['total'];
    }
    
    /**
     * Get all hotels with pagination (LIMIT and OFFSET)
     * @param int $limit - Number of records to return
     * @param int $offset - Number of records to skip
     * @return array - Array of hotels
     */
    public function getAllPaginated($limit, $offset) {
        $stmt = $this->db->prepare("SELECT * FROM hotels ORDER BY naziv ASC LIMIT ? OFFSET ?");
        $stmt->bind_param("ii", $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        $hotels = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $hotels;
    }
    
    /**
     * Create new hotel
     * @param array $data - Hotel data
     * @return int - New hotel ID
     */
    public function create($data) {
        $stmt = $this->db->prepare(
            "INSERT INTO hotels (naziv, adresa, grad, zupanija, kapacitet, broj_soba, broj_gostiju, slobodno_soba) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        
        $stmt->bind_param(
            "ssssiiii",
            $data['naziv'],
            $data['adresa'],
            $data['grad'],
            $data['zupanija'],
            $data['kapacitet'],
            $data['broj_soba'],
            $data['broj_gostiju'],
            $data['slobodno_soba']
        );
        
        $stmt->execute();
        $insertId = $this->db->insert_id;
        $stmt->close();
        
        return $insertId;
    }
    
    /**
     * Update hotel
     * @param int $id - Hotel ID
     * @param array $data - Updated hotel data
     * @return bool - True if successful
     */
    public function update($id, $data) {
        $stmt = $this->db->prepare(
            "UPDATE hotels 
             SET naziv=?, adresa=?, grad=?, zupanija=?, kapacitet=?, broj_soba=? 
             WHERE id=?"
        );
        
        $stmt->bind_param(
            "sssssii",
            $data['naziv'],
            $data['adresa'],
            $data['grad'],
            $data['zupanija'],
            $data['kapacitet'],
            $data['broj_soba'],
            $id
        );
        
        $success = $stmt->execute();
        $stmt->close();
        
        return $success;
    }
    
    /**
     * Delete hotel by ID
     * @param int $id - Hotel ID
     * @return bool - True if successful
     */
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM hotels WHERE id = ?");
        $stmt->bind_param("i", $id);
        $success = $stmt->execute();
        $stmt->close();
        
        return $success;
    }
    
    /**
     * Search hotels by city
     * @param string $grad - City name
     * @return array - Array of matching hotels
     */
    public function searchByCity($grad) {
        $stmt = $this->db->prepare("SELECT * FROM hotels WHERE grad LIKE ?");
        $searchTerm = "%{$grad}%";
        $stmt->bind_param("s", $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();
        $hotels = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        return $hotels;
    }
    
    /**
     * Get hotels by županija
     * @param string $zupanija - County name
     * @return array - Array of hotels
     */
    public function getByZupanija($zupanija) {
        $stmt = $this->db->prepare("SELECT * FROM hotels WHERE zupanija = ?");
        $stmt->bind_param("s", $zupanija);
        $stmt->execute();
        $result = $stmt->get_result();
        $hotels = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        return $hotels;
    }
    
    /**
     * Update guest count
     * @param int $id - Hotel ID
     * @param int $brojGostiju - Number of guests
     * @return bool - True if successful
     */
    public function updateGuestCount($id, $brojGostiju) {
        // Calculate available rooms
        $hotel = $this->findById($id);
        if (!$hotel) return false;
        
        $slobodnoSoba = $hotel['broj_soba'] - ceil($brojGostiju / 2); // Assuming 2 guests per room
        
        $stmt = $this->db->prepare(
            "UPDATE hotels SET broj_gostiju=?, slobodno_soba=? WHERE id=?"
        );
        $stmt->bind_param("iii", $brojGostiju, $slobodnoSoba, $id);
        $success = $stmt->execute();
        $stmt->close();
        
        return $success;
    }
}
?>
