/**
 * Client-Side JavaScript Validation with AJAX
 * Hotel Management System
 * 
 * This file contains AJAX-enabled validation functions
 * for real-time username availability checking
 */

'use strict';

// ============================================================================
// BASIC VALIDATION FUNCTIONS (without AJAX)
// ============================================================================

/**
 * 1. VALIDATE MINIMUM CHARACTER LENGTH
 * @param {string} value - Value to validate
 * @param {number} minLength - Minimum required length
 * @returns {boolean} - True if valid
 */
function validateMinLength(value, minLength) {
    return value && value.trim().length >= minLength;
}

/**
 * 2. VALIDATE EMAIL STRUCTURE
 * @param {string} email - Email to validate
 * @returns {boolean} - True if valid email format
 */
function validateEmail(email) {
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailPattern.test(email);
}

/**
 * 3. VALIDATE PHONE NUMBER FORMAT
 * @param {string} phone - Phone number to validate
 * @returns {boolean} - True if valid phone format
 */
function validatePhone(phone) {
    const phonePattern = /^[0-9+\-\s()]{9,20}$/;
    return phonePattern.test(phone);
}

/**
 * 4. VALIDATE PASSWORD MATCH
 * @param {string} password - Original password
 * @param {string} confirmPassword - Confirmation password
 * @returns {boolean} - True if passwords match
 */
function validatePasswordMatch(password, confirmPassword) {
    return password === confirmPassword && password && password.length > 0;
}

/**
 * 5. VALIDATE NUMBER RANGE
 * @param {number|string} value - Number to validate
 * @param {number} min - Minimum value
 * @param {number} max - Maximum value
 * @returns {boolean} - True if within range
 */
function validateNumberRange(value, min, max) {
    const num = parseInt(value, 10);
    return !isNaN(num) && num >= min && num <= max;
}

/**
 * 6. VALIDATE DROPDOWN SELECTION
 * @param {string} value - Selected value
 * @returns {boolean} - True if not empty
 */
function validateDropdown(value) {
    return value !== '' && value !== null && value !== undefined;
}

/**
 * 7. VALIDATE REQUIRED FIELD
 * @param {string} value - Value to check
 * @returns {boolean} - True if not empty
 */
function validateRequired(value) {
    return value && value.trim().length > 0;
}

/**
 * 8. VALIDATE USERNAME FORMAT
 * Alphanumeric + underscore only, 3-30 characters
 * @param {string} username - Username to validate
 * @returns {boolean} - True if valid format
 */
function validateUsernameFormat(username) {
    const usernamePattern = /^[a-zA-Z0-9_]{3,30}$/;
    return usernamePattern.test(username);
}

// ============================================================================
// AJAX FUNCTIONS FOR USERNAME AVAILABILITY
// ============================================================================

/**
 * AJAX FUNCTION: Check Username Availability (XMLHttpRequest)
 * Provjera postoji li već korisničko ime u bazi podataka
 * 
 * @param {string} username - Username to check
 * @param {function} callback - Callback function(response)
 */
function checkUsernameAvailability(username, callback) {
    // Validate callback
    if (typeof callback !== 'function') {
        console.error('checkUsernameAvailability: callback must be a function');
        return;
    }

    // Create XMLHttpRequest object
    const xhr = new XMLHttpRequest();
    
    // Configure request: GET method to api/check_username.php
    xhr.open('GET', 'api/check_username.php?username=' + encodeURIComponent(username), true);
    
    // Set request headers
    xhr.setRequestHeader('Content-Type', 'application/json');
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    
    // Handle response
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) { // Request completed
            if (xhr.status === 200) { // Success
                try {
                    const response = JSON.parse(xhr.responseText);
                    callback(response);
                } catch (e) {
                    console.error('Error parsing JSON response:', e);
                    callback({
                        available: false,
                        valid: false,
                        message: 'Greška pri obradi odgovora'
                    });
                }
            } else {
                console.error('HTTP Error:', xhr.status, xhr.statusText);
                callback({
                    available: false,
                    valid: false,
                    message: 'Greška pri povezivanju s poslužiteljem (HTTP ' + xhr.status + ')'
                });
            }
        }
    };

    // Handle network errors
    xhr.onerror = function() {
        console.error('Network error occurred');
        callback({
            available: false,
            valid: false,
            message: 'Greška mreže - provjerite internetsku vezu'
        });
    };

    // Handle timeout
    xhr.ontimeout = function() {
        console.error('Request timeout');
        callback({
            available: false,
            valid: false,
            message: 'Zahtjev je istekao - pokušajte ponovo'
        });
    };

    // Set timeout (10 seconds)
    xhr.timeout = 10000;
    
    // Send request
    xhr.send();
}

/**
 * Real-time Username Validation with AJAX (XMLHttpRequest version)
 * Poziva se kada korisnik unosi username (on blur ili on input)
 * 
 * @param {HTMLInputElement} inputElement - Username input field
 * @param {HTMLElement} feedbackElement - Element for displaying feedback
 */
function validateUsernameWithAjax(inputElement, feedbackElement) {
    // Validate input parameters
    if (!inputElement || !feedbackElement) {
        console.error('validateUsernameWithAjax: missing required parameters');
        return;
    }

    const username = inputElement.value.trim();
    
    // Clear previous feedback
    feedbackElement.textContent = '';
    feedbackElement.className = '';
    inputElement.classList.remove('is-valid', 'is-invalid');
    
    // Check if field is empty
    if (username === '') {
        inputElement.classList.add('is-invalid');
        feedbackElement.className = 'invalid-feedback';
        feedbackElement.textContent = 'Korisničko ime je obavezno';
        feedbackElement.style.display = 'block';
        return;
    }
    
    // Check username format first (client-side validation)
    if (!validateUsernameFormat(username)) {
        inputElement.classList.add('is-invalid');
        feedbackElement.className = 'invalid-feedback';
        feedbackElement.textContent = 'Korisničko ime mora imati 3-30 znakova (slova, brojevi, _)';
        feedbackElement.style.display = 'block';
        return;
    }
    
    // Show loading indicator
    feedbackElement.className = 'text-muted';
    feedbackElement.innerHTML = '<i class="bi bi-hourglass-split"></i> Provjeravam dostupnost...';
    feedbackElement.style.display = 'block';
    
    // Call AJAX function to check availability
    checkUsernameAvailability(username, function(response) {
        if (response.valid && response.available) {
            // Username is available
            inputElement.classList.remove('is-invalid');
            inputElement.classList.add('is-valid');
            feedbackElement.className = 'valid-feedback';
            feedbackElement.innerHTML = '<i class="bi bi-check-circle-fill"></i> ' + response.message;
            feedbackElement.style.display = 'block';
        } else {
            // Username is not available or invalid format
            inputElement.classList.remove('is-valid');
            inputElement.classList.add('is-invalid');
            feedbackElement.className = 'invalid-feedback';
            feedbackElement.innerHTML = '<i class="bi bi-x-circle-fill"></i> ' + response.message;
            feedbackElement.style.display = 'block';
        }
    });
}

// ============================================================================
// MODERN FETCH API VERSION (Alternative to XMLHttpRequest)
// ============================================================================

/**
 * Alternative: Using Fetch API (Modern approach)
 * Može se koristiti umjesto XMLHttpRequest
 * 
 * @param {string} username - Username to check
 * @returns {Promise<Object>} - Promise resolving to response object
 */
async function checkUsernameAvailabilityFetch(username) {
    try {
        // Create abort controller for timeout
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 10000); // 10 second timeout

        const response = await fetch(
            `api/check_username.php?username=${encodeURIComponent(username)}`,
            {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                signal: controller.signal
            }
        );

        clearTimeout(timeoutId);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        return data;

    } catch (error) {
        console.error('Error checking username:', error);

        // Handle different error types
        if (error.name === 'AbortError') {
            return {
                available: false,
                valid: false,
                message: 'Zahtjev je istekao - pokušajte ponovo'
            };
        }

        return {
            available: false,
            valid: false,
            message: 'Greška pri provjeri korisničkog imena: ' + error.message
        };
    }
}

/**
 * Real-time Username Validation with Fetch API (Modern async/await version)
 * Modernija verzija koja koristi async/await
 * 
 * @param {HTMLInputElement} inputElement - Username input field
 * @param {HTMLElement} feedbackElement - Element for displaying feedback
 */
async function validateUsernameWithFetch(inputElement, feedbackElement) {
    // Validate input parameters
    if (!inputElement || !feedbackElement) {
        console.error('validateUsernameWithFetch: missing required parameters');
        return;
    }

    const username = inputElement.value.trim();
    
    // Clear previous feedback
    feedbackElement.textContent = '';
    feedbackElement.className = '';
    inputElement.classList.remove('is-valid', 'is-invalid');
    
    // Check if field is empty
    if (username === '') {
        inputElement.classList.add('is-invalid');
        feedbackElement.className = 'invalid-feedback';
        feedbackElement.textContent = 'Korisničko ime je obavezno';
        feedbackElement.style.display = 'block';
        return;
    }
    
    // Check username format first
    if (!validateUsernameFormat(username)) {
        inputElement.classList.add('is-invalid');
        feedbackElement.className = 'invalid-feedback';
        feedbackElement.textContent = 'Korisničko ime mora imati 3-30 znakova (slova, brojevi, _)';
        feedbackElement.style.display = 'block';
        return;
    }
    
    // Show loading indicator
    feedbackElement.className = 'text-muted';
    feedbackElement.innerHTML = '<i class="bi bi-hourglass-split"></i> Provjeravam dostupnost...';
    feedbackElement.style.display = 'block';
    
    // Call Fetch API
    const response = await checkUsernameAvailabilityFetch(username);
    
    if (response.valid && response.available) {
        // Username is available
        inputElement.classList.remove('is-invalid');
        inputElement.classList.add('is-valid');
        feedbackElement.className = 'valid-feedback';
        feedbackElement.innerHTML = '<i class="bi bi-check-circle-fill"></i> ' + response.message;
        feedbackElement.style.display = 'block';
    } else {
        // Username is not available or invalid
        inputElement.classList.remove('is-valid');
        inputElement.classList.add('is-invalid');
        feedbackElement.className = 'invalid-feedback';
        feedbackElement.innerHTML = '<i class="bi bi-x-circle-fill"></i> ' + response.message;
        feedbackElement.style.display = 'block';
    }
}

// ============================================================================
// UTILITY FUNCTIONS
// ============================================================================

/**
 * Debounce function - prevents excessive AJAX calls
 * Useful for real-time validation on input events
 * 
 * @param {function} func - Function to debounce
 * @param {number} wait - Wait time in milliseconds
 * @returns {function} - Debounced function
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Setup real-time validation with debouncing
 * Example usage for username input field
 * 
 * @param {HTMLInputElement} inputElement - Input field
 * @param {HTMLElement} feedbackElement - Feedback element
 * @param {number} delay - Debounce delay in ms (default: 500)
 */
function setupRealtimeValidation(inputElement, feedbackElement, delay = 500) {
    if (!inputElement || !feedbackElement) {
        console.error('setupRealtimeValidation: missing required parameters');
        return;
    }

    // Create debounced validation function
    const debouncedValidation = debounce(
        () => validateUsernameWithFetch(inputElement, feedbackElement),
        delay
    );

    // Attach event listeners
    inputElement.addEventListener('input', debouncedValidation);
    inputElement.addEventListener('blur', () => validateUsernameWithFetch(inputElement, feedbackElement));
}

// Export functions for use in other files (if using ES6 modules)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        // Basic validation
        validateMinLength,
        validateEmail,
        validatePhone,
        validatePasswordMatch,
        validateNumberRange,
        validateDropdown,
        validateRequired,
        validateUsernameFormat,
        // AJAX functions
        checkUsernameAvailability,
        validateUsernameWithAjax,
        checkUsernameAvailabilityFetch,
        validateUsernameWithFetch,
        // Utilities
        debounce,
        setupRealtimeValidation
    };
}
