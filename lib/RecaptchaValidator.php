<?php

/**
 * Google reCAPTCHA v2 Validator
 * 
 * Validates reCAPTCHA responses using Google's API
 */

class RecaptchaValidator {
    private $secretKey;
    private $verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';
    
    public function __construct() {
        // Get secret key from config or environment
        // Replace with your actual reCAPTCHA secret key
        $this->secretKey = defined('RECAPTCHA_SECRET_KEY') 
            ? RECAPTCHA_SECRET_KEY 
            : '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe'; // Test key
    }
    
    /**
     * Verify reCAPTCHA response
     * 
     * @param string $response The g-recaptcha-response token
     * @param string $remoteIp Optional: user's IP address
     * @return bool True if verification successful, false otherwise
     */
    public function verify($response, $remoteIp = null) {
        // Check if response is empty
        if (empty($response)) {
            return false;
        }
        
        // Prepare POST data
        $postData = [
            'secret' => $this->secretKey,
            'response' => $response
        ];
        
        if ($remoteIp !== null) {
            $postData['remoteip'] = $remoteIp;
        }
        
        // Make request to Google
        $options = [
            'http' => [
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($postData),
                'timeout' => 10
            ]
        ];
        
        $context = stream_context_create($options);
        $result = @file_get_contents($this->verifyUrl, false, $context);
        
        if ($result === false) {
            error_log('reCAPTCHA verification failed: Unable to connect to Google API');
            return false;
        }
        
        // Parse response
        $resultJson = json_decode($result, true);
        
        if (!isset($resultJson['success'])) {
            error_log('reCAPTCHA verification failed: Invalid response format');
            return false;
        }
        
        // Log errors if any
        if (!$resultJson['success'] && isset($resultJson['error-codes'])) {
            error_log('reCAPTCHA errors: ' . implode(', ', $resultJson['error-codes']));
        }
        
        return $resultJson['success'] === true;
    }
}