<?php
session_start();
require_once 'config/database.php';
require_once 'config/email_config.php';

$message = '';
$error = '';

if ($_POST) {
    $email = $_POST['email'];
    
    $database = new Database();
    $db = $database->getConnection();
    
    // Cari admin dengan username (kita pakai username sebagai email)
    $query = "SELECT * FROM admins WHERE username = :username";
    $stmt = $db->prepare($query);
    $stmt->execute([':username' => $email]);
    
    if ($stmt->rowCount() > 0) {
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Generate dan simpan token
        $token = generateToken($email);
        saveToken($token, $email);
        
        // Kirim email
        if (sendResetPasswordEmail($email, $token)) {
            $message = "Link reset password telah dikirim! Check file <code>email_logs/reset_password_emails.txt</code> untuk link reset.";
        } else {
            $error = "Gagal mengirim email. Silakan coba lagi.";
        }
    } else {
        $error = "Username tidak ditemukan dalam sistem.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - SMA Negeri 1 Maju Jaya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .forgot-password-container {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .forgot-card {
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            border: none;
            border-radius: 15px;
        }
        .forgot-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0;
            padding: 2rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="forgot-password-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card forgot-card">
                        <div class="forgot-header">
                            <h3><i class="fas fa-school me-2"></i>SMA Negeri 1 Maju Jaya</h3>
                            <p class="mb-0">Reset Password Administrator</p>
                        </div>
                        <div class="card-body p-4">
                            <?php if ($message): ?>
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <?php echo $message; ?>
                                </div>
                                <div class="text-center mt-3">
                                    <a href="admin_login.php" class="btn btn-primary">
                                        <i class="fas fa-arrow-left me-2"></i>Kembali ke Login
                                    </a>
                                </div>
                            <?php else: ?>
                                <?php if ($error): ?>
                                    <div class="alert alert-danger">
                                        <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <p class="text-muted mb-4">Masukkan username admin Anda. Link reset password akan dibuat.</p>
                                
                                <form method="POST">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Username Admin</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                                            <input type="text" class="form-control" id="email" name="email" required 
                                                   placeholder="Contoh: admin atau superadmin">
                                        </div>
                                        <small class="text-muted">Username yang terdaftar: admin, superadmin</small>
                                    </div>
                                    <div class="d-grid mb-3">
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="fas fa-paper-plane me-2"></i>Buat Link Reset
                                        </button>
                                    </div>
                                </form>
                                
                                <div class="text-center">
                                    <a href="admin_login.php" class="text-decoration-none">
                                        <i class="fas fa-arrow-left me-2"></i>Kembali ke Login
                                    </a>
                                </div>
                            <?php endif; ?>
                            
                            <div class="mt-4 p-3 bg-light rounded">
                                <h6><i class="fas fa-info-circle me-2"></i>Informasi:</h6>
                                <p class="mb-1">• Link reset akan disimpan di: <code>email_logs/reset_password_emails.txt</code></p>
                                <p class="mb-1">• Token disimpan di: <code>email_logs/password_tokens.json</code></p>
                                <p class="mb-0">• Link berlaku 1 jam</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>