<?php
/**
 * Pagination - Reusable Pagination Class
 * 
 * Univerzalna klasa za straničenje (pagination) koja se može koristiti
 * na svim mjestima u aplikaciji.
 * 
 * Koristi vlastiti PHP i SQL kod (LIMIT, OFFSET).
 * NE koristi DataTables ili gotove JavaScript alate.
 * 
 * Features:
 * - Configurable items per page (iz config.php)
 * - SQL LIMIT/OFFSET calculation
 * - Bootstrap 5 pagination HTML
 * - URL parameter handling
 * - Total pages calculation
 * 
 * Usage:
 * $pagination = new Pagination($totalItems, $currentPage, $itemsPerPage);
 * $offset = $pagination->getOffset();
 * $limit = $pagination->getLimit();
 * // SQL: SELECT * FROM table LIMIT $limit OFFSET $offset
 * echo $pagination->render();
 */

class Pagination {
    private $totalItems;
    private $itemsPerPage;
    private $currentPage;
    private $totalPages;
    private $offset;
    private $baseUrl;
    private $queryParams;
    
    /**
     * Constructor
     * 
     * @param int $totalItems - Ukupan broj zapisa u bazi
     * @param int $currentPage - Trenutna stranica (default: 1)
     * @param int $itemsPerPage - Broj zapisa po stranici (default: iz config.php)
     * @param string $baseUrl - Base URL za linkove (default: trenutna stranica)
     */
    public function __construct($totalItems, $currentPage = 1, $itemsPerPage = null, $baseUrl = null) {
        $this->totalItems = max(0, (int)$totalItems);
        
        // Ako itemsPerPage nije zadan, uzmi iz config-a
        if ($itemsPerPage === null) {
            $itemsPerPage = defined('ITEMS_PER_PAGE') ? ITEMS_PER_PAGE : 10;
        }
        $this->itemsPerPage = max(1, (int)$itemsPerPage);
        
        // Calculate total pages
        $this->totalPages = $this->totalItems > 0 ? ceil($this->totalItems / $this->itemsPerPage) : 1;
        
        // Validate and set current page
        $this->currentPage = max(1, min((int)$currentPage, $this->totalPages));
        
        // Calculate offset for SQL query
        $this->offset = ($this->currentPage - 1) * $this->itemsPerPage;
        
        // Set base URL (current script by default)
        $this->baseUrl = $baseUrl ?: $_SERVER['PHP_SELF'];
        
        // Preserve existing query parameters (except 'page')
        $this->queryParams = $_GET;
        unset($this->queryParams['page']);
    }
    
    /**
     * Get SQL OFFSET value
     * Use in SQL query: LIMIT $limit OFFSET $offset
     * 
     * @return int
     */
    public function getOffset() {
        return $this->offset;
    }
    
    /**
     * Get SQL LIMIT value
     * Use in SQL query: LIMIT $limit OFFSET $offset
     * 
     * @return int
     */
    public function getLimit() {
        return $this->itemsPerPage;
    }
    
    /**
     * Get current page number
     * 
     * @return int
     */
    public function getCurrentPage() {
        return $this->currentPage;
    }
    
    /**
     * Get total number of pages
     * 
     * @return int
     */
    public function getTotalPages() {
        return $this->totalPages;
    }
    
    /**
     * Get total number of items
     * 
     * @return int
     */
    public function getTotalItems() {
        return $this->totalItems;
    }
    
    /**
     * Get items per page
     * 
     * @return int
     */
    public function getItemsPerPage() {
        return $this->itemsPerPage;
    }
    
    /**
     * Check if there is a previous page
     * 
     * @return bool
     */
    public function hasPrevious() {
        return $this->currentPage > 1;
    }
    
    /**
     * Check if there is a next page
     * 
     * @return bool
     */
    public function hasNext() {
        return $this->currentPage < $this->totalPages;
    }
    
    /**
     * Get range of items being displayed (e.g., "11-20 of 45")
     * 
     * @return array ['start' => 11, 'end' => 20, 'total' => 45]
     */
    public function getRange() {
        if ($this->totalItems === 0) {
            return ['start' => 0, 'end' => 0, 'total' => 0];
        }
        
        $start = $this->offset + 1;
        $end = min($this->offset + $this->itemsPerPage, $this->totalItems);
        
        return [
            'start' => $start,
            'end' => $end,
            'total' => $this->totalItems
        ];
    }
    
    /**
     * Build URL for specific page
     * Preserves existing query parameters
     * 
     * @param int $pageNumber
     * @return string
     */
    private function buildUrl($pageNumber) {
        $params = $this->queryParams;
        $params['page'] = $pageNumber;
        return $this->baseUrl . '?' . http_build_query($params);
    }
    
    /**
     * Render Bootstrap 5 pagination HTML
     * 
     * @param string $size - Pagination size: 'sm', 'lg', or '' (default)
     * @param string $alignment - Alignment: 'start', 'center', 'end' (default: center)
     * @return string - HTML pagination code
     */
    public function render($size = '', $alignment = 'center') {
        // If only one page, don't render pagination
        if ($this->totalPages <= 1) {
            return '';
        }
        
        $sizeClass = $size ? "pagination-{$size}" : '';
        $alignmentClass = "justify-content-{$alignment}";
        
        $html = "<nav aria-label='Straničenje'>";
        $html .= "<ul class='pagination {$sizeClass} {$alignmentClass}'>";
        
        // Previous button
        if ($this->hasPrevious()) {
            $prevUrl = $this->buildUrl($this->currentPage - 1);
            $html .= "<li class='page-item'>";
            $html .= "<a class='page-link' href='{$prevUrl}' aria-label='Prethodna'>";
            $html .= "<span aria-hidden='true'>&laquo;</span>";
            $html .= "</a></li>";
        } else {
            $html .= "<li class='page-item disabled'>";
            $html .= "<span class='page-link'>&laquo;</span>";
            $html .= "</li>";
        }
        
        // Page numbers with smart display (show max 7 pages)
        $pagesToShow = $this->getPageNumbersToShow();
        
        foreach ($pagesToShow as $page) {
            if ($page === '...') {
                $html .= "<li class='page-item disabled'><span class='page-link'>...</span></li>";
            } else {
                $isActive = $page === $this->currentPage;
                $activeClass = $isActive ? 'active' : '';
                $pageUrl = $this->buildUrl($page);
                
                $html .= "<li class='page-item {$activeClass}'>";
                if ($isActive) {
                    $html .= "<span class='page-link'>{$page}</span>";
                } else {
                    $html .= "<a class='page-link' href='{$pageUrl}'>{$page}</a>";
                }
                $html .= "</li>";
            }
        }
        
        // Next button
        if ($this->hasNext()) {
            $nextUrl = $this->buildUrl($this->currentPage + 1);
            $html .= "<li class='page-item'>";
            $html .= "<a class='page-link' href='{$nextUrl}' aria-label='Sljedeća'>";
            $html .= "<span aria-hidden='true'>&raquo;</span>";
            $html .= "</a></li>";
        } else {
            $html .= "<li class='page-item disabled'>";
            $html .= "<span class='page-link'>&raquo;</span>";
            $html .= "</li>";
        }
        
        $html .= "</ul>";
        $html .= "</nav>";
        
        return $html;
    }
    
    /**
     * Get smart list of page numbers to display
     * Algorithm: Show first, last, current, and 2 pages around current
     * Example with current=5, total=10: [1, ..., 3, 4, 5, 6, 7, ..., 10]
     * 
     * @return array
     */
    private function getPageNumbersToShow() {
        $pages = [];
        $total = $this->totalPages;
        $current = $this->currentPage;
        
        // If 7 or less pages, show all
        if ($total <= 7) {
            return range(1, $total);
        }
        
        // Always include first page
        $pages[] = 1;
        
        // Calculate range around current page
        $rangeStart = max(2, $current - 2);
        $rangeEnd = min($total - 1, $current + 2);
        
        // Add ellipsis if gap between 1 and range start
        if ($rangeStart > 2) {
            $pages[] = '...';
        }
        
        // Add range around current page
        for ($i = $rangeStart; $i <= $rangeEnd; $i++) {
            $pages[] = $i;
        }
        
        // Add ellipsis if gap between range end and last page
        if ($rangeEnd < $total - 1) {
            $pages[] = '...';
        }
        
        // Always include last page
        if (!in_array($total, $pages)) {
            $pages[] = $total;
        }
        
        return $pages;
    }
    
    /**
     * Render pagination info text (e.g., "Prikazano 11-20 od 45 rezultata")
     * 
     * @return string
     */
    public function renderInfo() {
        $range = $this->getRange();
        
        if ($range['total'] === 0) {
            return "<p class='text-muted mb-0'>Nema rezultata</p>";
        }
        
        return "<p class='text-muted mb-0'>" .
               "Prikazano <strong>{$range['start']}-{$range['end']}</strong> " .
               "od <strong>{$range['total']}</strong> rezultata" .
               "</p>";
    }
    
    /**
     * Get pagination metadata as array (useful for AJAX/API)
     * 
     * @return array
     */
    public function getMetadata() {
        $range = $this->getRange();
        
        return [
            'current_page' => $this->currentPage,
            'total_pages' => $this->totalPages,
            'total_items' => $this->totalItems,
            'items_per_page' => $this->itemsPerPage,
            'offset' => $this->offset,
            'range_start' => $range['start'],
            'range_end' => $range['end'],
            'has_previous' => $this->hasPrevious(),
            'has_next' => $this->hasNext()
        ];
    }
}
