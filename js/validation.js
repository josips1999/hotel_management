/**
 * Form Validation Utilities
 * Hotel Management System - Client-Side Validation
 * 
 * This file contains reusable validation functions for forms
 * Matches server-side validation in lib/Validator.php
 */

'use strict';

/**
 * Validator Class - Object-oriented approach for form validation
 */
class FormValidator {
    constructor() {
        this.errors = [];
    }

    /**
     * 1. VALIDATE MINIMUM CHARACTER LENGTH
     * @param {string} value - Value to validate
     * @param {number} minLength - Minimum required length
     * @param {string} fieldName - Field name for error message
     * @returns {boolean} - True if valid
     */
    validateMinLength(value, minLength, fieldName) {
        if (!value || value.trim().length < minLength) {
            this.errors.push(`${fieldName} mora imati minimalno ${minLength} znaka`);
            return false;
        }
        return true;
    }

    /**
     * 2. VALIDATE EMAIL STRUCTURE
     * @param {string} email - Email to validate
     * @returns {boolean} - True if valid email format
     */
    validateEmail(email) {
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(email)) {
            this.errors.push('Nevažeća email adresa (npr. hotel@example.com)');
            return false;
        }
        return true;
    }

    /**
     * 3. VALIDATE PHONE NUMBER FORMAT
     * @param {string} phone - Phone number to validate
     * @returns {boolean} - True if valid phone format
     */
    validatePhone(phone) {
        const phonePattern = /^[0-9+\-\s()]{9,20}$/;
        if (!phonePattern.test(phone)) {
            this.errors.push('Nevažeći broj telefona (mora imati 9-20 znakova)');
            return false;
        }
        return true;
    }

    /**
     * 4. VALIDATE PASSWORD MATCH
     * @param {string} password - Original password
     * @param {string} confirmPassword - Confirmation password
     * @returns {boolean} - True if passwords match
     */
    validatePasswordMatch(password, confirmPassword) {
        if (password !== confirmPassword || !password || password.length === 0) {
            this.errors.push('Lozinke se ne podudaraju');
            return false;
        }
        return true;
    }

    /**
     * 5. VALIDATE NUMBER RANGE
     * @param {number|string} value - Number to validate
     * @param {number} min - Minimum value
     * @param {number} max - Maximum value
     * @param {string} fieldName - Field name for error message
     * @returns {boolean} - True if within range
     */
    validateNumberRange(value, min, max, fieldName) {
        const num = parseInt(value, 10);
        if (isNaN(num) || num < min || num > max) {
            this.errors.push(`${fieldName} mora biti između ${min} i ${max}`);
            return false;
        }
        return true;
    }

    /**
     * 6. VALIDATE REQUIRED FIELD
     * @param {string} value - Value to check
     * @param {string} fieldName - Field name for error message
     * @returns {boolean} - True if not empty
     */
    validateRequired(value, fieldName) {
        if (!value || value.trim().length === 0) {
            this.errors.push(`${fieldName} je obavezno polje`);
            return false;
        }
        return true;
    }

    /**
     * 7. VALIDATE ALLOWED VALUES (for dropdowns)
     * @param {string} value - Selected value
     * @param {Array} allowedValues - Array of allowed values
     * @param {string} fieldName - Field name for error message
     * @returns {boolean} - True if valid selection
     */
    validateInArray(value, allowedValues, fieldName) {
        if (!allowedValues.includes(value)) {
            this.errors.push(`${fieldName} sadrži nevažeću vrijednost`);
            return false;
        }
        return true;
    }

    /**
     * 8. VALIDATE USERNAME FORMAT
     * @param {string} username - Username to validate
     * @returns {boolean} - True if valid format
     */
    validateUsernameFormat(username) {
        const usernamePattern = /^[a-zA-Z0-9_ ]{3,30}$/;
        if (!usernamePattern.test(username)) {
            this.errors.push('Korisničko ime mora imati 3-30 znakova (slova, brojevi, razmak, _)');
            return false;
        }
        return true;
    }

    /**
     * 9. VALIDATE PASSWORD STRENGTH
     * @param {string} password - Password to validate
     * @returns {boolean} - True if strong enough
     */
    validatePasswordStrength(password) {
        if (password.length < 8) {
            this.errors.push('Lozinka mora imati minimalno 8 znakova');
            return false;
        }
        return true;
    }

    /**
     * Get all validation errors
     * @returns {Array} - Array of error messages
     */
    getErrors() {
        return this.errors;
    }

    /**
     * Check if validation passed (no errors)
     * @returns {boolean} - True if no errors
     */
    isValid() {
        return this.errors.length === 0;
    }

    /**
     * Clear all errors (reset validator)
     */
    clearErrors() {
        this.errors = [];
    }

    /**
     * Get first error message
     * @returns {string|null} - First error or null
     */
    getFirstError() {
        return this.errors.length > 0 ? this.errors[0] : null;
    }
}

/**
 * Standalone validation functions (functional approach)
 * These can be used directly without instantiating FormValidator class
 */

function validateMinLength(value, minLength) {
    return value && value.trim().length >= minLength;
}

function validateEmail(email) {
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailPattern.test(email);
}

function validatePhone(phone) {
    const phonePattern = /^[0-9+\-\s()]{9,20}$/;
    return phonePattern.test(phone);
}

function validatePasswordMatch(password, confirmPassword) {
    return password === confirmPassword && password && password.length > 0;
}

function validateNumberRange(value, min, max) {
    const num = parseInt(value, 10);
    return !isNaN(num) && num >= min && num <= max;
}

function validateDropdown(value) {
    return value !== '' && value !== null && value !== undefined;
}

function validateRequired(value) {
    return value && value.trim().length > 0;
}

function validateUsernameFormat(username) {
    const usernamePattern = /^[a-zA-Z0-9_]{3,30}$/;
    return usernamePattern.test(username);
}

/**
 * Display validation error on form field
 * @param {HTMLElement} inputElement - Input field
 * @param {string} errorMessage - Error message to display
 */
function showValidationError(inputElement, errorMessage) {
    if (!inputElement) return;

    inputElement.classList.remove('is-valid');
    inputElement.classList.add('is-invalid');

    // Find or create feedback element
    let feedbackElement = inputElement.parentElement.querySelector('.invalid-feedback');
    if (!feedbackElement) {
        feedbackElement = document.createElement('div');
        feedbackElement.className = 'invalid-feedback';
        inputElement.parentElement.appendChild(feedbackElement);
    }

    feedbackElement.textContent = errorMessage;
    feedbackElement.style.display = 'block';
}

/**
 * Display validation success on form field
 * @param {HTMLElement} inputElement - Input field
 * @param {string} successMessage - Success message to display (optional)
 */
function showValidationSuccess(inputElement, successMessage = '') {
    if (!inputElement) return;

    inputElement.classList.remove('is-invalid');
    inputElement.classList.add('is-valid');

    if (successMessage) {
        let feedbackElement = inputElement.parentElement.querySelector('.valid-feedback');
        if (!feedbackElement) {
            feedbackElement = document.createElement('div');
            feedbackElement.className = 'valid-feedback';
            inputElement.parentElement.appendChild(feedbackElement);
        }

        feedbackElement.textContent = successMessage;
        feedbackElement.style.display = 'block';
    }
}

/**
 * Clear validation state from form field
 * @param {HTMLElement} inputElement - Input field
 */
function clearValidationState(inputElement) {
    if (!inputElement) return;

    inputElement.classList.remove('is-valid', 'is-invalid');

    const feedbacks = inputElement.parentElement.querySelectorAll('.invalid-feedback, .valid-feedback');
    feedbacks.forEach(feedback => {
        feedback.style.display = 'none';
        feedback.textContent = '';
    });
}

/**
 * Validate entire form
 * @param {HTMLFormElement} formElement - Form to validate
 * @returns {boolean} - True if all fields are valid
 */
function validateForm(formElement) {
    if (!formElement) return false;

    let isValid = true;
    const inputs = formElement.querySelectorAll('input[required], select[required], textarea[required]');

    inputs.forEach(input => {
        if (!validateRequired(input.value)) {
            showValidationError(input, `${input.name || 'Ovo polje'} je obavezno`);
            isValid = false;
        } else {
            clearValidationState(input);
        }
    });

    return isValid;
}

// Export for use as ES6 modules (if needed)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        FormValidator,
        validateMinLength,
        validateEmail,
        validatePhone,
        validatePasswordMatch,
        validateNumberRange,
        validateDropdown,
        validateRequired,
        validateUsernameFormat,
        showValidationError,
        showValidationSuccess,
        clearValidationState,
        validateForm
    };
}