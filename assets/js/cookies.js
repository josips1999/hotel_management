/**
 * Cookie Manager JavaScript
 * Handles cookie banner, terms acceptance, and cookie consent
 */

// Cookie utilities
const CookieJS = {
    // Set cookie
    set: function(name, value, days = 365) {
        const date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        const expires = "expires=" + date.toUTCString();
        document.cookie = name + "=" + value + ";" + expires + ";path=/;SameSite=Lax";
    },
    
    // Get cookie
    get: function(name) {
        const nameEQ = name + "=";
        const ca = document.cookie.split(';');
        for(let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) === ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
        }
        return null;
    },
    
    // Delete cookie
    delete: function(name) {
        document.cookie = name + "=;expires=Thu, 01 Jan 1970 00:00:00 UTC;path=/;";
    },
    
    // Check if cookie exists
    has: function(name) {
        return this.get(name) !== null;
    }
};

// Terms & Cookie Manager
const TermsManager = {
    
    // Initialize
    init: function() {
        // Check if terms are already accepted
        if (!this.hasAcceptedTerms()) {
            this.showCookieBanner();
        }
        
        // Set up event listeners
        this.setupEventListeners();
    },
    
    // Check if user has accepted terms
    hasAcceptedTerms: function() {
        return CookieJS.has('hotel_terms_accepted');
    },
    
    // Show cookie banner
    showCookieBanner: function() {
        const banner = document.getElementById('cookieBanner');
        if (banner) {
            banner.style.display = 'block';
        }
    },
    
    // Hide cookie banner
    hideCookieBanner: function() {
        const banner = document.getElementById('cookieBanner');
        if (banner) {
            banner.style.display = 'none';
        }
    },
    
    // Show terms modal
    showTermsModal: function() {
        const modal = document.getElementById('termsModal');
        if (modal) {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden'; // Prevent background scroll
        }
    },
    
    // Hide terms modal
    hideTermsModal: function() {
        const modal = document.getElementById('termsModal');
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = ''; // Restore scroll
        }
    },
    
    // Accept terms
    acceptTerms: function() {
        const termsData = {
            version: '1.0',
            accepted_at: Math.floor(Date.now() / 1000),
            ip_address: 'client-side' // IP is set server-side
        };
        
        // Set cookie (365 days)
        CookieJS.set('hotel_terms_accepted', JSON.stringify(termsData), 365);
        
        // Also set cookie consent (essential cookies only by default)
        this.setCookieConsent(true, false, false);
        
        // Hide banner and modal
        this.hideCookieBanner();
        this.hideTermsModal();
        
        // Show success message
        this.showNotification('Uvjeti korištenja prihvaćeni! ✓', 'success');
        
        // Reload if needed (to show content that requires acceptance)
        // window.location.reload();
    },
    
    // Decline terms
    declineTerms: function() {
        this.hideTermsModal();
        this.showNotification('Morate prihvatiti uvjete korištenja za nastavak.', 'warning');
    },
    
    // Set cookie consent
    setCookieConsent: function(essential = true, analytical = false, marketing = false) {
        const consentData = {
            essential: essential,
            analytical: analytical,
            marketing: marketing,
            timestamp: Math.floor(Date.now() / 1000)
        };
        
        CookieJS.set('hotel_cookie_consent', JSON.stringify(consentData), 365);
    },
    
    // Show cookie settings modal
    showCookieSettings: function() {
        // First hide cookie banner
        this.hideCookieBanner();
        
        // Then show terms modal (which includes cookie settings)
        this.showTermsModal();
        
        // Scroll to cookie settings section if it exists
        setTimeout(() => {
            const settingsSection = document.getElementById('cookieSettingsSection');
            if (settingsSection) {
                settingsSection.scrollIntoView({ behavior: 'smooth' });
            }
        }, 300);
    },
    
    // Save cookie preferences from settings modal
    saveCookiePreferences: function() {
        const analyticalCheckbox = document.getElementById('analyticalCookies');
        const marketingCheckbox = document.getElementById('marketingCookies');
        
        const analytical = analyticalCheckbox ? analyticalCheckbox.checked : false;
        const marketing = marketingCheckbox ? marketingCheckbox.checked : false;
        
        this.setCookieConsent(true, analytical, marketing);
        
        // Accept terms if not already accepted
        if (!this.hasAcceptedTerms()) {
            this.acceptTerms();
        } else {
            this.hideTermsModal();
            this.showNotification('Postavke kolačića spremljene! ✓', 'success');
        }
    },
    
    // Show notification
    showNotification: function(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10001;
            padding: 1rem 1.5rem;
            background: ${type === 'success' ? '#10b981' : type === 'warning' ? '#f59e0b' : '#3b82f6'};
            color: white;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            font-weight: 600;
            animation: slideInRight 0.4s ease-out;
        `;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        // Remove after 3 seconds
        setTimeout(() => {
            notification.style.animation = 'slideOutRight 0.4s ease-out';
            setTimeout(() => notification.remove(), 400);
        }, 3000);
    },
    
    // Setup event listeners
    setupEventListeners: function() {
        // Accept button in banner
        const acceptBannerBtn = document.getElementById('acceptCookiesBtn');
        if (acceptBannerBtn) {
            acceptBannerBtn.addEventListener('click', () => this.acceptTerms());
        }
        
        // Settings button in banner
        const settingsBannerBtn = document.getElementById('cookieSettingsBtn');
        if (settingsBannerBtn) {
            settingsBannerBtn.addEventListener('click', () => this.showCookieSettings());
        }
        
        // Close modal button
        const closeModalBtn = document.getElementById('closeTermsModal');
        if (closeModalBtn) {
            closeModalBtn.addEventListener('click', () => this.hideTermsModal());
        }
        
        // Accept button in modal
        const acceptModalBtn = document.getElementById('acceptTermsBtn');
        if (acceptModalBtn) {
            acceptModalBtn.addEventListener('click', () => this.saveCookiePreferences());
        }
        
        // Decline button in modal
        const declineModalBtn = document.getElementById('declineTermsBtn');
        if (declineModalBtn) {
            declineModalBtn.addEventListener('click', () => this.declineTerms());
        }
        
        // Checkbox to enable accept button
        const termsCheckbox = document.getElementById('agreeTermsCheckbox');
        const acceptBtn = document.getElementById('acceptTermsBtn');
        if (termsCheckbox && acceptBtn) {
            termsCheckbox.addEventListener('change', function() {
                acceptBtn.disabled = !this.checked;
            });
        }
        
        // Close modal when clicking outside
        const modal = document.getElementById('termsModal');
        if (modal) {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    this.hideTermsModal();
                }
            });
        }
    }
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    TermsManager.init();
});

// Add animations to styles
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);
