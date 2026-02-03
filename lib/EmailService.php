<?php
/**
 * Email Service Class
 * Handles sending verification emails and other notifications
 * 
 * Note: Za produkciju koristite PHPMailer ili slične biblioteke
 * Ova verzija koristi PHP mail() funkciju za demo svrhe
 */

class EmailService {
    
    private $fromEmail;
    private $fromName;
    
    /**
     * Constructor
     * @param string $fromEmail - Sender email address
     * @param string $fromName - Sender name
     */
    public function __construct($fromEmail = 'noreply@hotelmanagement.com', $fromName = 'Hotel Management System') {
        $this->fromEmail = $fromEmail;
        $this->fromName = $fromName;
    }
    
    /**
     * Send verification code email
     * @param string $toEmail - Recipient email
     * @param string $username - User's username
     * @param string $verificationCode - 6-digit code
     * @return bool - True if email sent successfully
     */
    public function sendVerificationEmail($toEmail, $username, $verificationCode) {
        $subject = 'Potvrda registracije - Hotel Management System';
        
        // HTML email body
        $htmlBody = $this->getVerificationEmailTemplate($username, $verificationCode);
        
        // Plain text alternative
        $textBody = "Poštovani/a {$username},\n\n";
        $textBody .= "Hvala što ste se registrirali u Hotel Management System!\n\n";
        $textBody .= "Vaš verifikacijski kod je: {$verificationCode}\n\n";
        $textBody .= "Ovaj kod vrijedi 15 minuta.\n\n";
        $textBody .= "Molimo unesite kod na stranici za verifikaciju kako biste aktivirali svoj račun.\n\n";
        $textBody .= "Ako niste zatražili registraciju, molimo ignorišite ovaj email.\n\n";
        $textBody .= "S poštovanjem,\nHotel Management System";
        
        // Send email
        return $this->sendEmail($toEmail, $subject, $htmlBody, $textBody);
    }
    
    /**
     * Send email using PHP mail() function
     * @param string $to - Recipient email
     * @param string $subject - Email subject
     * @param string $htmlBody - HTML content
     * @param string $textBody - Plain text alternative
     * @return bool - Success status
     */
    private function sendEmail($to, $subject, $htmlBody, $textBody) {
        // Configure SMTP for Papercut (localhost testing)
        ini_set('SMTP', 'localhost');
        ini_set('smtp_port', '2525');
        ini_set('sendmail_from', $this->fromEmail);
        
        // Email headers
        $headers = [];
        $headers[] = "MIME-Version: 1.0";
        $headers[] = "Content-Type: text/html; charset=UTF-8";
        $headers[] = "From: {$this->fromName} <{$this->fromEmail}>";
        $headers[] = "Reply-To: {$this->fromEmail}";
        $headers[] = "X-Mailer: PHP/" . phpversion();
        
        // Join headers
        $headersString = implode("\r\n", $headers);
        
        // Send email
        $sent = mail($to, $subject, $htmlBody, $headersString);
        
        // Log email attempt (for debugging)
        $this->logEmail($to, $subject, $sent);
        
        return $sent;
    }
    
    /**
     * Get HTML template for verification email
     * @param string $username - User's username
     * @param string $verificationCode - 6-digit code
     * @return string - HTML email content
     */
    private function getVerificationEmailTemplate($username, $verificationCode) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    background-color: #f4f4f4;
                    margin: 0;
                    padding: 0;
                }
                .container {
                    max-width: 600px;
                    margin: 20px auto;
                    background: white;
                    border-radius: 10px;
                    overflow: hidden;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                }
                .header {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    padding: 30px;
                    text-align: center;
                }
                .header h1 {
                    margin: 0;
                    font-size: 24px;
                }
                .content {
                    padding: 40px 30px;
                }
                .verification-code {
                    background: #f8f9fa;
                    border: 2px dashed #667eea;
                    border-radius: 8px;
                    padding: 20px;
                    text-align: center;
                    margin: 30px 0;
                }
                .verification-code h2 {
                    color: #667eea;
                    font-size: 36px;
                    margin: 0;
                    letter-spacing: 8px;
                    font-weight: bold;
                }
                .footer {
                    background: #f8f9fa;
                    padding: 20px;
                    text-align: center;
                    color: #666;
                    font-size: 12px;
                }
                .button {
                    display: inline-block;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    padding: 12px 30px;
                    text-decoration: none;
                    border-radius: 5px;
                    margin: 20px 0;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🏨 Hotel Management System</h1>
                </div>
                <div class='content'>
                    <h2>Poštovani/a {$username},</h2>
                    <p>Hvala što ste se registrirali u Hotel Management System!</p>
                    <p>Za aktivaciju vašeg računa, molimo unesite sljedeći verifikacijski kod:</p>
                    
                    <div class='verification-code'>
                        <p style='margin: 0; color: #666; font-size: 14px;'>Vaš verifikacijski kod:</p>
                        <h2>{$verificationCode}</h2>
                        <p style='margin: 0; color: #999; font-size: 12px;'>Kod vrijedi 15 minuta</p>
                    </div>
                    
                    <p><strong>Napomena:</strong> Ovaj kod je povjerljiv. Ne dijelite ga ni s kim.</p>
                    <p>Ako niste zatražili registraciju, molimo ignorišite ovaj email.</p>
                    
                    <p style='margin-top: 30px;'>S poštovanjem,<br><strong>Hotel Management Tim</strong></p>
                </div>
                <div class='footer'>
                    <p>Ovo je automatska poruka. Molimo ne odgovarajte na ovaj email.</p>
                    <p>&copy; 2026 Hotel Management System. Sva prava pridržana.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    /**
     * Log email attempt for debugging
     * @param string $to - Recipient
     * @param string $subject - Email subject
     * @param bool $success - Whether email was sent successfully
     */
    private function logEmail($to, $subject, $success) {
        $logFile = __DIR__ . '/../logs/email_log.txt';
        $logDir = dirname($logFile);
        
        // Create logs directory if it doesn't exist
        if (!file_exists($logDir)) {
            mkdir($logDir, 0777, true);
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $status = $success ? 'SUCCESS' : 'FAILED';
        $logEntry = "[{$timestamp}] {$status} - To: {$to} - Subject: {$subject}\n";
        
        file_put_contents($logFile, $logEntry, FILE_APPEND);
    }
    
    /**
     * Alternative: Send email using Gmail SMTP (requires PHPMailer)
     * Uncomment and configure for production use
     */
    /*
    public function sendEmailViaSMTP($to, $subject, $htmlBody) {
        require_once 'PHPMailer/PHPMailer.php';
        require_once 'PHPMailer/SMTP.php';
        require_once 'PHPMailer/Exception.php';
        
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        
        try {
            // SMTP configuration
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'your-email@gmail.com';
            $mail->Password = 'your-app-password';
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            
            // Email content
            $mail->setFrom($this->fromEmail, $this->fromName);
            $mail->addAddress($to);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $htmlBody;
            
            return $mail->send();
        } catch (Exception $e) {
            return false;
        }
    }
    */
}