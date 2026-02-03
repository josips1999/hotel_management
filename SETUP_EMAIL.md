# Email Verification Setup Guide

## Quick Setup Options

### Option 1: Local Testing (No Real Emails) - RECOMMENDED FOR TESTING

**Use Fake SMTP Server:**

1. **Download Papercut SMTP:**
   - Visit: https://github.com/ChangemakerStudios/Papercut-SMTP/releases
   - Download and run `Papercut.exe`
   - It will listen on `localhost:25` automatically

2. **Configure PHP (C:\xampp\php\php.ini):**
   ```ini
   [mail function]
   SMTP = localhost
   smtp_port = 25
   sendmail_from = noreply@hotelmanagement.com
   ```

3. **Restart Apache** in XAMPP Control Panel

4. **Test:** Register a user, check Papercut to see the email with verification code

---

### Option 2: Gmail SMTP (Real Emails)

1. **Enable 2-Step Verification** in your Gmail account

2. **Generate App Password:**
   - Go to: https://myaccount.google.com/apppasswords
   - Create app password for "Mail"
   - Copy the 16-character password

3. **Install PHPMailer:**
   ```bash
   cd C:\xampp\htdocs\hotel_managment
   composer require phpmailer/phpmailer
   ```

4. **Update lib/EmailService.php** - Replace `sendEmail()` method:

   ```php
   private function sendEmail($to, $subject, $htmlBody, $textBody) {
       require_once __DIR__ . '/../vendor/autoload.php';
       
       $mail = new PHPMailer\PHPMailer\PHPMailer(true);
       
       try {
           // SMTP configuration
           $mail->isSMTP();
           $mail->Host = 'smtp.gmail.com';
           $mail->SMTPAuth = true;
           $mail->Username = 'your-email@gmail.com';  // <-- Change this
           $mail->Password = 'your-app-password';      // <-- Change this
           $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
           $mail->Port = 587;
           
           // Email content
           $mail->setFrom($this->fromEmail, $this->fromName);
           $mail->addAddress($to);
           $mail->isHTML(true);
           $mail->Subject = $subject;
           $mail->Body = $htmlBody;
           $mail->AltBody = $textBody;
           
           $mail->send();
           $this->logEmail($to, $subject, true);
           return true;
       } catch (Exception $e) {
           $this->logEmail($to, $subject, false);
           error_log("Email error: " . $mail->ErrorInfo);
           return false;
       }
   }
   ```

---

### Option 3: Skip Email (Testing Only)

**For quick testing without email setup:**

1. **Check verification codes in database:**
   ```sql
   SELECT username, email, verification_code, verification_expires 
   FROM users 
   WHERE is_verified = 0;
   ```

2. **Manually verify a user:**
   ```sql
   UPDATE users 
   SET is_verified = 1, verification_code = NULL, verification_expires = NULL 
   WHERE email = 'test@example.com';
   ```

3. **Check email log:**
   - Look at `logs/email_log.txt` to see if emails were attempted

---

## Testing the System

### 1. Register a New User

```bash
# Open in browser:
http://localhost/hotel_managment/register.php
```

Fill in the form:
- Username: testuser
- Email: test@example.com
- Password: password123
- Accept terms
- Complete reCAPTCHA

### 2. Check Email/Database

**With Papercut SMTP:**
- Open Papercut application
- See the email with 6-digit code

**With Gmail:**
- Check inbox for verification email

**Database check:**
```bash
C:\xampp\mysql\bin\mysql.exe -u root -e "USE hotel_management; SELECT username, email, verification_code, verification_expires, is_verified FROM users WHERE email='test@example.com';"
```

### 3. Verify Account

```bash
# Redirected automatically to:
http://localhost/hotel_managment/verify.php
```

Enter the 6-digit code from email (or database)

### 4. Confirm Verification

```bash
C:\xampp\mysql\bin\mysql.exe -u root -e "USE hotel_management; SELECT username, is_verified FROM users WHERE email='test@example.com';"
```

Should show `is_verified = 1`

---

## Troubleshooting

### Email not sending?

1. **Check PHP error log:** `C:\xampp\apache\logs\error.log`

2. **Check email log:** `logs/email_log.txt`

3. **Test PHP mail():**
   ```php
   <?php
   $to = "test@example.com";
   $subject = "Test Email";
   $message = "This is a test";
   $headers = "From: noreply@test.com";
   
   if (mail($to, $subject, $message, $headers)) {
       echo "Email sent!";
   } else {
       echo "Email failed!";
   }
   ?>
   ```

### Verification code expired?

- Codes expire after 15 minutes
- Click "Pošalji novi kod" (Resend code) button on verify.php
- Or register again

### Can't access verify.php?

- Make sure you register first (creates session)
- Session variable `pending_verification_email` is required
- Check `verify.php` line 122

---

## Configuration Files

- **Email Service:** `lib/EmailService.php`
- **Register API:** `api/register_user.php`
- **Verify API:** `api/verify_code.php`
- **Resend API:** `api/resend_code.php`
- **Verify Page:** `verify.php`

---

## Current Status

✅ Database tables ready (`verification_code`, `verification_expires`, `is_verified`)
✅ Email templates created (HTML + plain text)
✅ Verification page with 6-digit input
✅ Resend functionality with rate limiting
✅ Auto-login after verification
✅ Session management

**System is ready to use!** Just configure email sending method.
