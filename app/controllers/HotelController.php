<?php
/**
 * Hotel Controller
 * Handles business logic and coordinates between Model and View
 */
require_once(__DIR__ . '/../models/Hotel.php');
require_once(__DIR__ . '/../../lib/Validator.php');
require_once(__DIR__ . '/../../lib/AuditLogger.php');
require_once(__DIR__ . '/../../lib/SessionManager.php');

class HotelController {
    private $hotelModel;
    private $validator;
    private $auditLogger;
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
        $this->hotelModel = new Hotel($db);
        $this->validator = new Validator();
        
        // Initialize audit logger with current user ID
        $sessionManager = new SessionManager($db);
        $userId = $sessionManager->getUserId();
        $this->auditLogger = new AuditLogger($db, $userId);
    }
    
    /**
     * Get single hotel by ID
     * @param int $id - Hotel ID
     * @return array - JSON response
     */
    public function show($id) {
        // Validate ID parameter
        if (!isset($id) || intval($id) <= 0) {
            return [
                'error' => true,
                'message' => 'Nevažeći ID parametar'
            ];
        }
        
        // Get hotel from model
        $hotel = $this->hotelModel->findById(intval($id));
        
        // Check if hotel exists
        if ($hotel) {
            return [
                'success' => true,
                'data' => $hotel
            ];
        } else {
            return [
                'error' => true,
                'message' => 'Hotel sa ID-jem ' . $id . ' nije pronađen'
            ];
        }
    }
    
    /**
     * Get all hotels with pagination
     * @param int $page - Current page number (default: 1)
     * @param int $itemsPerPage - Items per page (default: from config)
     * @return array - JSON response with hotels and pagination metadata
     */
    public function index($page = 1, $itemsPerPage = null) {
        // Use config value if not specified
        if ($itemsPerPage === null) {
            $itemsPerPage = defined('ITEMS_PER_PAGE') ? ITEMS_PER_PAGE : 10;
        }
        
        // Get total count for pagination
        $totalCount = $this->hotelModel->getCount();
        
        // Calculate offset
        $offset = ($page - 1) * $itemsPerPage;
        
        // Get hotels with limit and offset
        $hotels = $this->hotelModel->getAllPaginated($itemsPerPage, $offset);
        
        return [
            'success' => true,
            'data' => $hotels,
            'pagination' => [
                'current_page' => $page,
                'items_per_page' => $itemsPerPage,
                'total_items' => $totalCount,
                'total_pages' => ceil($totalCount / $itemsPerPage)
            ]
        ];
    }
    
    /**
     * Create new hotel
     * @param array $data - POST data from form
     * @return array - JSON response
     */
    public function store($data) {
        // SERVER-SIDE VALIDATION
        $this->validator->validateMinLength($data['naziv'] ?? '', 3, 'Naziv');
        $this->validator->validateMinLength($data['adresa'] ?? '', 5, 'Adresa');
        $this->validator->validateMinLength($data['grad'] ?? '', 2, 'Grad');
        $this->validator->validateRequired($data['zupanija'] ?? '', 'Županija');
        $this->validator->validateNumberRange($data['kapacitet'] ?? 0, 10, 10000, 'Kapacitet');
        $this->validator->validateNumberRange($data['broj_soba'] ?? 0, 5, 5000, 'Broj soba');
        
        // Check if validation passed
        if (!$this->validator->isValid()) {
            return [
                'error' => true,
                'messages' => $this->validator->getErrors()
            ];
        }
        
        // Sanitize and prepare data
        $hotelData = [
            'naziv' => htmlspecialchars(trim($data['naziv']), ENT_QUOTES, 'UTF-8'),
            'adresa' => htmlspecialchars(trim($data['adresa']), ENT_QUOTES, 'UTF-8'),
            'grad' => htmlspecialchars(trim($data['grad']), ENT_QUOTES, 'UTF-8'),
            'zupanija' => htmlspecialchars($data['zupanija'], ENT_QUOTES, 'UTF-8'),
            'kapacitet' => intval($data['kapacitet']),
            'broj_soba' => intval($data['broj_soba']),
            'broj_gostiju' => 0,
            'slobodno_soba' => intval($data['broj_soba'])
        ];
        
        // Create hotel via model
        try {
            $newId = $this->hotelModel->create($hotelData);
            
            // LOG AUDIT: Insert action
            // $this->auditLogger->logInsert('hotels', $newId, $hotelData);
            
            return [
                'success' => true,
                'message' => 'Hotel uspješno dodan!',
                'id' => $newId,
                'data' => $hotelData
            ];
        } catch (Exception $e) {
            return [
                'error' => true,
                'message' => 'Greška pri dodavanju hotela: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Update existing hotel
     * @param int $id - Hotel ID
     * @param array $data - POST data
     * @return array - JSON response
     */
    public function update($id, $data) {
        // Validate ID
        if (intval($id) <= 0) {
            return [
                'error' => true,
                'message' => 'Nevažeći ID hotela'
            ];
        }
        
        // Validate input data
        $this->validator->validateMinLength($data['naziv'] ?? '', 3, 'Naziv');
        $this->validator->validateMinLength($data['adresa'] ?? '', 5, 'Adresa');
        $this->validator->validateNumberRange($data['kapacitet'] ?? 0, 10, 10000, 'Kapacitet');
        
        if (!$this->validator->isValid()) {
            return [
                'error' => true,
                'messages' => $this->validator->getErrors()
            ];
        }
        
        // Get old data before update (for audit log)
        $oldData = $this->hotelModel->findById(intval($id));
        
        // Prepare update data
        $updateData = [
            'naziv' => htmlspecialchars(trim($data['naziv']), ENT_QUOTES, 'UTF-8'),
            'adresa' => htmlspecialchars(trim($data['adresa']), ENT_QUOTES, 'UTF-8'),
            'grad' => htmlspecialchars(trim($data['grad']), ENT_QUOTES, 'UTF-8'),
            'zupanija' => htmlspecialchars($data['zupanija'], ENT_QUOTES, 'UTF-8'),
            'kapacitet' => intval($data['kapacitet']),
            'broj_soba' => intval($data['broj_soba'])
        ];
        
        // Update via model
        $success = $this->hotelModel->update(intval($id), $updateData);
        
        // LOG AUDIT: Update action
        // if ($success && $oldData) {
        //     $this->auditLogger->logUpdate('hotels', intval($id), $oldData, $updateData);
        // }
        
        if ($success) {
            return [
                'success' => true,
                'message' => 'Hotel uspješno ažuriran!'
            ];
        } else {
            return [
                'error' => true,
                'message' => 'Greška pri ažuriranju hotela'
            ];
        }
    }
    
    /**
     * Delete hotel
     * @param int $id - Hotel ID
     * @return array - JSON response
     */
    public function destroy($id) {
        // Validate ID
        if (intval($id) <= 0) {
            return [
                'error' => true,
                'message' => 'Nevažeći ID hotela'
            ];
        }
        
        // Check if hotel exists first (also get data for audit log)
        $hotel = $this->hotelModel->findById(intval($id));
        if (!$hotel) {
            return [
                'error' => true,
                'message' => 'Hotel nije pronađen'
            ];
        }
        
        // Delete via model
        $success = $this->hotelModel->delete(intval($id));
        
        // LOG AUDIT: Delete action
        // if ($success) {
        //     $this->auditLogger->logDelete('hotels', intval($id), $hotel);
        // }
        
        if ($success) {
            return [
                'success' => true,
                'message' => 'Hotel uspješno obrisan!'
            ];
        } else {
            return [
                'error' => true,
                'message' => 'Greška pri brisanju hotela'
            ];
        }
    }
    
    /**
     * Search hotels by city
     * @param string $grad - City name
     * @return array - JSON response
     */
    public function searchByCity($grad) {
        if (empty($grad)) {
            return [
                'error' => true,
                'message' => 'Grad parametar je obavezan'
            ];
        }
        
        $hotels = $this->hotelModel->searchByCity($grad);
        
        return [
            'success' => true,
            'data' => $hotels,
            'count' => count($hotels)
        ];
    }
    
    /**
     * Update guest count for hotel
     * @param int $id - Hotel ID
     * @param int $brojGostiju - Number of guests
     * @return array - JSON response
     */
    public function updateGuestCount($id, $brojGostiju) {
        $success = $this->hotelModel->updateGuestCount(intval($id), intval($brojGostiju));
        
        if ($success) {
            return [
                'success' => true,
                'message' => 'Broj gostiju uspješno ažuriran!'
            ];
        } else {
            return [
                'error' => true,
                'message' => 'Greška pri ažuriranju broja gostiju'
            ];
        }
    }
}
?>
