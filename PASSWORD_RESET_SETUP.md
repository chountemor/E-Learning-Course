# eLearning College — Password Reset Setup

The login now includes a compact **Forgot Password?** flow:

1. User enters their email.
2. A random 4-digit verification code is generated.
3. The code is emailed to the account address.
4. User enters the 4 digits.
5. After verification, the user creates a new password.
6. The existing account is kept; only the password is changed.

## Email configuration

The project uses PHP `mail()` and does not require Composer.

Edit:

`config/mail.php`

Set:

```php
define('ELC_MAIL_FROM', 'no-reply@your-domain.com');
define('ELC_MAIL_REPLY_TO', 'support@your-domain.com');
```

### Shared hosting

Use an email address that exists on the same domain when possible, then confirm the hosting provider has PHP mail enabled.

### XAMPP / localhost

PHP `mail()` is normally not configured by default. Configure SMTP in PHP/your local mail relay first, or deploy to a hosting/server with mail configured.

## Recovery security

- OTP is 4 digits.
- OTP is stored as a password hash in the session, not as plain text.
- OTP expires after 10 minutes.
- Maximum 5 verification attempts.
- Resend is limited to once per 60 seconds.
- The email step does not reveal whether an email exists in the database.
- Passwords are stored with `password_hash()`.
