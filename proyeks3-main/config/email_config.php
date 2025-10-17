<?php
// config/email_config.php

// Konfigurasi Email
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your_email@gmail.com');
define('SMTP_PASSWORD', 'your_app_password');
define('SMTP_FROM_EMAIL', 'your_email@gmail.com');
define('SMTP_FROM_NAME', 'SMA Negeri 1 Maju Jaya');

function sendResetPasswordEmail($to, $token) {
    $subject = "Reset Password - SMA Negeri 1 Maju Jaya";
    
    $reset_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset_password.php?token=" . $token;
    
    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #2c3e50; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background: #f9f9f9; }
            .button { background: #3498db; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block; }
            .footer { text-align: center; padding: 20px; color: #666; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>SMA Negeri 1 Maju Jaya</h2>
            </div>
            <div class='content'>
                <h3>Reset Password</h3>
                <p>Anda menerima email ini karena ada permintaan reset password untuk akun admin Anda.</p>
                <p>Klik tombol di bawah ini untuk mereset password:</p>
                <p>
                    <a href='$reset_link' class='button'>Reset Password</a>
                </p>
                <p>Atau copy link berikut di browser Anda:</p>
                <p><small>$reset_link</small></p>
                <p><strong>Link ini akan kadaluarsa dalam 1 jam.</strong></p>
                <p>Jika Anda tidak meminta reset password, abaikan email ini.</p>
            </div>
            <div class='footer'>
                <p>&copy; " . date('Y') . " SMA Negeri 1 Maju Jaya. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return saveEmailToFile($to, $subject, $message);
}

function saveEmailToFile($to, $subject, $message) {
    $email_content = "=================================\n";
    $email_content .= "TO: $to\n";
    $email_content .= "SUBJECT: $subject\n";
    $email_content .= "TIME: " . date('Y-m-d H:i:s') . "\n";
    $email_content .= "LINK: http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset_password.php?token=" . md5($to . time()) . "\n";
    $email_content .= "=================================\n";
    $email_content .= $message . "\n\n";
    
    $file_path = 'email_logs/reset_password_emails.txt';
    
    if (!file_exists('email_logs')) {
        mkdir('email_logs', 0777, true);
    }
    
    if (file_put_contents($file_path, $email_content, FILE_APPEND | LOCK_EX)) {
        return true;
    }
    
    return false;
}

function generateToken($email) {
    return md5($email . time() . uniqid());
}

function validateToken($token, $email) {
    $tokens_file = 'email_logs/password_tokens.json';
    
    if (!file_exists($tokens_file)) {
        return false;
    }
    
    $tokens = json_decode(file_get_contents($tokens_file), true);
    
    if (isset($tokens[$token]) && 
        $tokens[$token]['email'] === $email && 
        $tokens[$token]['expires'] > time()) {
        return true;
    }
    
    return false;
}

function saveToken($token, $email) {
    $tokens_file = 'email_logs/password_tokens.json';
    $tokens = [];
    
    if (file_exists($tokens_file)) {
        $tokens = json_decode(file_get_contents($tokens_file), true);
    }
    
    // Hapus token expired
    foreach ($tokens as $key => $token_data) {
        if ($token_data['expires'] < time()) {
            unset($tokens[$key]);
        }
    }
    
    // Simpan token baru (expire 1 jam)
    $tokens[$token] = [
        'email' => $email,
        'expires' => time() + 3600, // 1 jam
        'used' => false
    ];
    
    if (!file_exists('email_logs')) {
        mkdir('email_logs', 0777, true);
    }
    
    return file_put_contents($tokens_file, json_encode($tokens, JSON_PRETTY_PRINT));
}

function markTokenUsed($token) {
    $tokens_file = 'email_logs/password_tokens.json';
    
    if (!file_exists($tokens_file)) {
        return false;
    }
    
    $tokens = json_decode(file_get_contents($tokens_file), true);
    
    if (isset($tokens[$token])) {
        $tokens[$token]['used'] = true;
        return file_put_contents($tokens_file, json_encode($tokens, JSON_PRETTY_PRINT));
    }
    
    return false;
}
?>