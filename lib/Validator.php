<?php
/**
 * Validator Class - Server-side validation for hotel data
 * Validates input data to ensure data integrity and security
 */
class Validator {
    
    private $errors = [];
    
    /**
     * Validate minimum character length
     * @param string $value - Value to validate
     * @param int $minLength - Minimum required length
     * @param string $fieldName - Field name for error message
     * @return bool - True if valid
     */
    public function validateMinLength($value, $minLength, $fieldName) {
        if (strlen(trim($value)) < $minLength) {
            $this->errors[] = "{$fieldName} mora imati minimalno {$minLength} znaka";
            return false;
        }
        return true;
    }
    
    /**
     * Validate email structure using PHP filter
     * @param string $email - Email to validate
     * @return bool - True if valid email format
     */
    public function validateEmail($email) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = "Nevažeća email adresa";
            return false;
        }
        return true;
    }
    
    /**
     * Validate phone number using regex pattern
     * @param string $phone - Phone number to validate
     * @return bool - True if valid phone format
     */
    public function validatePhone($phone) {
        // Pattern: 9-20 characters, allows digits, +, -, spaces, parentheses
        if (!preg_match('/^[0-9+\-\s()]{9,20}$/', $phone)) {
            $this->errors[] = "Nevažeći broj telefona (minimalno 9 znakova)";
            return false;
        }
        return true;
    }
    
    /**
     * Validate number is within allowed range
     * @param int $value - Number to validate
     * @param int $min - Minimum value
     * @param int $max - Maximum value
     * @param string $fieldName - Field name for error message
     * @return bool - True if within range
     */
    public function validateNumberRange($value, $min, $max, $fieldName) {
        $num = intval($value);
        if ($num < $min || $num > $max) {
            $this->errors[] = "{$fieldName} mora biti između {$min} i {$max}";
            return false;
        }
        return true;
    }
    
    /**
     * Validate required field is not empty
     * @param string $value - Value to check
     * @param string $fieldName - Field name for error message
     * @return bool - True if not empty
     */
    public function validateRequired($value, $fieldName) {
        if (empty(trim($value))) {
            $this->errors[] = "{$fieldName} je obavezno polje";
            return false;
        }
        return true;
    }
    
    /**
     * Validate dropdown/select has valid value
     * @param string $value - Selected value
     * @param array $allowedValues - Array of allowed values
     * @param string $fieldName - Field name for error message
     * @return bool - True if valid selection
     */
    public function validateInArray($value, $allowedValues, $fieldName) {
        if (!in_array($value, $allowedValues)) {
            $this->errors[] = "{$fieldName} sadrži nevažeću vrijednost";
            return false;
        }
        return true;
    }
    
    /**
     * Get all validation errors
     * @return array - Array of error messages
     */
    public function getErrors() {
        return $this->errors;
    }
    
    /**
     * Check if validation passed (no errors)
     * @return bool - True if no errors
     */
    public function isValid() {
        return empty($this->errors);
    }
    
    /**
     * Clear all errors (reset validator)
     */
    public function clearErrors() {
        $this->errors = [];
    }
}