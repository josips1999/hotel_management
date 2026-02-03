# Security Implementation Checklist (Requirement 33)

## CSRF Protection Implementation

### Files that need CSRF Token validation:

#### API Endpoints (add CSRFToken::verifyPost() after POST check):
- [x] `/api/login.php` - DONE
- [ ] `/api/register_user.php`
- [ ] `/api/verify_code.php`
- [ ] `/api/resend_code.php`
- [ ] `/api/contact_submit.php`
- [ ] `/api/add_hotel.php`
- [ ] `/api/update_hotel.php`
- [ ] `/api/delete_hotel.php`
- [ ] `/api/update_boravak.php`
- [ ] `/api/save_setting.php`
- [ ] `/api/user_action.php`
- [ ] `/api/backup_database.php`
- [ ] `/api/restore_database.php`

#### Forms that need CSRF Token field (add CSRFToken::getField()):
- [x] `login.php` - DONE
- [ ] `register.php`
- [ ] `verify.php`
- [ ] `contact.php`
- [ ] `add_hotel.php`
- [ ] `edit.php`
- [ ] `update_boravak.php`
- [ ] `system_settings.php`
- [ ] `user_management.php`
- [ ] `database_backup.php`

## XSS Protection Implementation

### Locations that need htmlspecialchars():

#### Display User Input:
- All `echo $variable` statements displaying user input
- All `<?= $variable ?>` statements
- All form value attributes: `value="<?php echo htmlspecialchars($value); ?>"`

#### Critical Files to Review:
- `index.php` - Hotel listings (naziv, grad, zupanija)
- `view.php` - Hotel details (all fields)
- `search.php` - Search results
- `dashboard.php` - User data display
- `user_management.php` - Username, email display
- `statistics.php` - All data displays
- `audit_log.php` - Username, IP, details display

## SQL Injection Protection

### Review all database queries:
- ✅ All queries should use prepared statements
- ✅ Never concatenate user input directly into SQL
- Check: `$conn->query("SELECT ... WHERE field = '$userInput'")` ❌
- Correct: `$stmt->prepare("SELECT ... WHERE field = ?"); $stmt->bind_param("s", $userInput);` ✅

## SEO-Friendly URLs (Requirement 32)

### Implementation Steps:
- [x] Created `.htaccess` with URL rewrite rules - DONE
- [x] Created `SEOHelper.php` - DONE
- [x] Created `Router.php` - DONE
- [ ] Update all hotel links to use SEOHelper::hotelUrl()
- [ ] Update pagination to use SEO-friendly format
- [ ] Add canonical URLs to pages
- [ ] Add meta descriptions
- [ ] Add structured data (schema.org)

## Priority Order:

1. **HIGH PRIORITY - CSRF Protection**
   - Add CSRF validation to all API endpoints
   - Add CSRF tokens to all forms

2. **HIGH PRIORITY - XSS Protection**
   - Escape all user input displays
   - Review and fix main display pages

3. **MEDIUM PRIORITY - SEO URLs**
   - Update hotel links throughout the project
   - Test URL rewriting

4. **LOW PRIORITY - SQL Injection Review**
   - Most queries already use prepared statements
   - Spot-check remaining files
