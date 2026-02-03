<!-- Cookie Banner -->
<div id="cookieBanner" class="cookie-banner">
    <div class="cookie-banner-content">
        <div class="cookie-banner-text">
            <div class="cookie-banner-title">
                <i class="bi bi-info-circle"></i>
                Kolačići i Uvjeti Korištenja
            </div>
            <div class="cookie-banner-description">
                Ova web stranica koristi kolačiće kako bi osigurala najbolje korisničko iskustvo. 
                Nastavkom korištenja pristajete na naše 
                <a href="javascript:void(0)" onclick="TermsManager.showTermsModal()">uvjete korištenja</a> 
                i <a href="javascript:void(0)" onclick="TermsManager.showTermsModal()">politiku privatnosti</a>.
            </div>
        </div>
        <div class="cookie-banner-actions">
            <button id="acceptCookiesBtn" class="cookie-btn cookie-btn-accept">
                <i class="bi bi-check-circle"></i>
                Prihvaćam
            </button>
            <button id="cookieSettingsBtn" class="cookie-btn cookie-btn-settings">
                <i class="bi bi-gear"></i>
                Postavke
            </button>
        </div>
    </div>
</div>

<!-- Terms Modal -->
<div id="termsModal" class="terms-modal-overlay">
    <div class="terms-modal">
        <div class="terms-modal-header">
            <h2 class="terms-modal-title">
                <i class="bi bi-shield-check"></i>
                Uvjeti Korištenja i Politika Privatnosti
            </h2>
            <button id="closeTermsModal" class="terms-modal-close">
                <i class="bi bi-x"></i>
            </button>
        </div>
        
        <div class="terms-modal-body">
            <div class="terms-content">
                <!-- Introduction -->
                <div class="terms-section">
                    <h3><i class="bi bi-file-text"></i> 1. Uvod</h3>
                    <p>
                        Dobrodošli u Hotel Management System. Korištenjem ove web aplikacije pristajete na sljedeće 
                        uvjete korištenja. Molimo pažljivo pročitajte sve uvjete prije nastavka.
                    </p>
                    <div class="terms-highlight">
                        <strong><i class="bi bi-exclamation-triangle"></i> Važno:</strong> 
                        Ovi uvjeti stupaju na snagu odmah nakon prihvaćanja i čuvaju se pomoću kolačića na vašem uređaju.
                    </div>
                </div>

                <!-- Terms of Use -->
                <div class="terms-section">
                    <h3><i class="bi bi-list-check"></i> 2. Uvjeti Korištenja</h3>
                    <ul>
                        <li>Aplikacija je namijenjena isključivo za upravljanje hotelskim podacima</li>
                        <li>Svaki korisnik je odgovoran za sigurnost svojih pristupnih podataka</li>
                        <li>Zabranjeno je zlonamjerno korištenje ili pokušaj neovlaštenog pristupa</li>
                        <li>Svi podaci uneseni u sustav moraju biti točni i ažurni</li>
                        <li>Zadržavamo pravo izmjene uvjeta uz prethodnu obavijest korisnicima</li>
                    </ul>
                </div>

                <!-- Privacy Policy -->
                <div class="terms-section">
                    <h3><i class="bi bi-shield-lock"></i> 3. Politika Privatnosti</h3>
                    <p><strong>Podaci koje prikupljamo:</strong></p>
                    <ul>
                        <li><strong>Osobni podaci:</strong> Korisničko ime, email adresa, lozinka (hashirana)</li>
                        <li><strong>Tehnički podaci:</strong> IP adresa, tip preglednika, vrijeme pristupa</li>
                        <li><strong>Poslovni podaci:</strong> Podaci o hotelima, sobama, rezervacijama</li>
                    </ul>
                    
                    <p><strong>Kako koristimo podatke:</strong></p>
                    <ul>
                        <li>Za pružanje funkcionalnosti upravljanja hotelima</li>
                        <li>Za poboljšanje sigurnosti i sprječavanje zloupotrebe</li>
                        <li>Za praćenje promjena podataka (audit log)</li>
                        <li>Za analitiku i poboljšanje korisničkog iskustva</li>
                    </ul>
                </div>

                <!-- Cookies Policy -->
                <div class="terms-section" id="cookieSettingsSection">
                    <h3><i class="bi bi-cookie"></i> 4. Politika Kolačića</h3>
                    <p>Koristimo sljedeće vrste kolačića:</p>
                    
                    <div class="cookie-settings-group">
                        <!-- Essential Cookies -->
                        <div class="cookie-setting-item">
                            <div class="cookie-setting-info">
                                <div class="cookie-setting-title">
                                    <i class="bi bi-shield-fill-check"></i>
                                    Nužni Kolačići
                                </div>
                                <div class="cookie-setting-description">
                                    Potrebni za osnovno funkcioniranje stranice (prijava, sigurnost, pamćenje postavki).
                                    Ovi kolačići se ne mogu isključiti.
                                </div>
                                <div class="cookie-setting-required">
                                    <i class="bi bi-lock-fill"></i> Uvijek aktivni
                                </div>
                            </div>
                            <label class="cookie-toggle">
                                <input type="checkbox" checked disabled>
                                <span class="cookie-toggle-slider"></span>
                            </label>
                        </div>

                        <!-- Analytical Cookies -->
                        <div class="cookie-setting-item">
                            <div class="cookie-setting-info">
                                <div class="cookie-setting-title">
                                    <i class="bi bi-graph-up"></i>
                                    Analitički Kolačići
                                </div>
                                <div class="cookie-setting-description">
                                    Pomažu nam razumjeti kako korisnici koriste stranicu i poboljšati korisničko iskustvo.
                                </div>
                            </div>
                            <label class="cookie-toggle">
                                <input type="checkbox" id="analyticalCookies">
                                <span class="cookie-toggle-slider"></span>
                            </label>
                        </div>

                        <!-- Marketing Cookies -->
                        <div class="cookie-setting-item">
                            <div class="cookie-setting-info">
                                <div class="cookie-setting-title">
                                    <i class="bi bi-megaphone"></i>
                                    Marketing Kolačići
                                </div>
                                <div class="cookie-setting-description">
                                    Koriste se za prikazivanje personaliziranih oglasa i mjerenje učinkovitosti kampanja.
                                </div>
                            </div>
                            <label class="cookie-toggle">
                                <input type="checkbox" id="marketingCookies">
                                <span class="cookie-toggle-slider"></span>
                            </label>
                        </div>
                    </div>

                    <p style="margin-top: 1rem;"><strong>Trajanje kolačića:</strong></p>
                    <ul>
                        <li><code>hotel_terms_accepted</code> - 1 godina (pamćenje prihvaćenih uvjeta)</li>
                        <li><code>hotel_cookie_consent</code> - 1 godina (postavke kolačića)</li>
                        <li><code>remember_token</code> - 30 dana (funkcionalnost "Zapamti me")</li>
                        <li>Session kolačići - do zatvaranja preglednika</li>
                    </ul>
                </div>

                <!-- Data Security -->
                <div class="terms-section">
                    <h3><i class="bi bi-lock-fill"></i> 5. Sigurnost Podataka</h3>
                    <ul>
                        <li>Sve lozinke su zaštićene BCrypt hash algoritmom</li>
                        <li>HTTPS enkripcija za prijenos podataka (preporučeno)</li>
                        <li>Split-token pristup za "Zapamti me" funkcionalnost</li>
                        <li>Automatsko odjavljivanje nakon neaktivnosti</li>
                        <li>Audit log praćenja svih promjena u bazi podataka</li>
                    </ul>
                </div>

                <!-- User Rights -->
                <div class="terms-section">
                    <h3><i class="bi bi-person-check"></i> 6. Prava Korisnika (GDPR)</h3>
                    <p>U skladu s GDPR-om imate pravo:</p>
                    <ul>
                        <li>Pristup svojim osobnim podacima</li>
                        <li>Ispravak netočnih podataka</li>
                        <li>Brisanje podataka ("pravo na zaborav")</li>
                        <li>Prigovor na obradu podataka</li>
                        <li>Prenosivost podataka</li>
                    </ul>
                </div>

                <!-- Contact -->
                <div class="terms-section">
                    <h3><i class="bi bi-envelope"></i> 7. Kontakt</h3>
                    <p>
                        Za pitanja o uvjetima korištenja ili politici privatnosti, kontaktirajte nas na:
                        <br><strong>Email:</strong> privacy@hotelmanagement.hr
                        <br><strong>Telefon:</strong> +385 1 234 5678
                    </p>
                </div>

                <!-- Last Updated -->
                <div class="terms-section">
                    <p><small><strong>Posljednje ažurirano:</strong> <?php echo date('d.m.Y'); ?> | <strong>Verzija:</strong> 1.0</small></p>
                </div>
            </div>
        </div>
        
        <div class="terms-modal-footer">
            <div class="terms-checkbox-container">
                <input type="checkbox" id="agreeTermsCheckbox">
                <label for="agreeTermsCheckbox">
                    Pročitao/la sam i prihvaćam uvjete korištenja
                </label>
            </div>
            <div class="terms-modal-actions">
                <button id="declineTermsBtn" class="terms-btn terms-btn-decline">
                    <i class="bi bi-x-circle"></i>
                    Odbij
                </button>
                <button id="acceptTermsBtn" class="terms-btn terms-btn-accept" disabled>
                    <i class="bi bi-check-circle"></i>
                    Prihvaćam
                </button>
            </div>
        </div>
    </div>
</div>
