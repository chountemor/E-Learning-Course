<?php
/**
 * eLearning College mail settings.
 *
 * This project uses PHP's native mail() function so no Composer package is
 * required. On localhost/XAMPP, configure PHP mail/SMTP first. On shared
 * hosting, use an email address on the same domain as the website.
 */

define('ELC_MAIL_FROM', getenv('ELC_MAIL_FROM') ?: 'no-reply@elearningcollege.com');
define('ELC_MAIL_FROM_NAME', 'eLearning College');

define('ELC_OTP_EXPIRE_MINUTES', 10);
define('ELC_OTP_MAX_ATTEMPTS', 5);

define('ELC_MAIL_REPLY_TO', getenv('ELC_MAIL_REPLY_TO') ?: ELC_MAIL_FROM);

function send_password_reset_code(string $toEmail, string $userName, string $code): bool
{
    $subject = 'Your eLearning College password reset code';
    $safeName = htmlspecialchars($userName ?: 'Student', ENT_QUOTES, 'UTF-8');
    $safeCode = htmlspecialchars($code, ENT_QUOTES, 'UTF-8');

    $html = '<!doctype html><html><body style="margin:0;background:#f5f7fb;font-family:Arial,sans-serif;color:#172033">'
        . '<div style="max-width:560px;margin:30px auto;background:#fff;border:1px solid #e8ecf2;border-radius:18px;overflow:hidden">'
        . '<div style="padding:22px 26px;background:#f5a623;color:#172033;font-weight:800;font-size:20px">eLearning College</div>'
        . '<div style="padding:28px 26px">'
        . '<h2 style="margin:0 0 10px">Reset your password</h2>'
        . '<p style="margin:0 0 18px;line-height:1.6">Hello ' . $safeName . ', use the verification code below to reset your password.</p>'
        . '<div style="display:inline-block;padding:14px 22px;border-radius:12px;background:#fff4dd;color:#c47b00;font-size:32px;font-weight:800;letter-spacing:8px">' . $safeCode . '</div>'
        . '<p style="margin:20px 0 0;color:#64748b;line-height:1.6">This code expires in ' . (int)ELC_OTP_EXPIRE_MINUTES . ' minutes. If you did not request a password reset, you can ignore this email.</p>'
        . '</div></div></body></html>';

    $headers = [
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',
        'From: ' . ELC_MAIL_FROM_NAME . ' <' . ELC_MAIL_FROM . '>',
        'Reply-To: ' . ELC_MAIL_REPLY_TO,
        'X-Mailer: PHP/' . PHP_VERSION,
    ];

    return @mail($toEmail, $subject, $html, implode("\r\n", $headers));
}
