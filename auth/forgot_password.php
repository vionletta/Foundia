
<?php
session_start();
include "../config/database.php";

$email = '';
$error = '';
$success = '';
$resetLink = '';

if (isset($_POST['submit']) || isset($_POST['resend'])) {
    $email = trim($_POST['email'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid";
    } else {
        $stmt = mysqli_prepare($conn, "SELECT userID, email FROM users WHERE email = ? LIMIT 1");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 's', $email);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            if ($res !== false) {
                $user = mysqli_fetch_assoc($res);
            } else {
                $user = null;
                mysqli_stmt_bind_result($stmt, $userID, $userEmail);
                if (mysqli_stmt_fetch($stmt)) {
                    $user = ['userID' => $userID, 'email' => $userEmail];
                }
            }
            mysqli_stmt_close($stmt);
        } else {
            $user = null;
        }

        if (!$user) {
            $error = "Email tidak terdaftar";
        } else {
            $userID = (int)$user['userID'];
            $token = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $token);

            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
            $resetLink = $scheme . '://' . $host . $basePath . '/reset_password.php?token=' . urlencode($token);

            mysqli_query($conn, "UPDATE password_resets SET used_at = NOW() WHERE user_id = $userID AND used_at IS NULL");

            $stmt2 = mysqli_prepare(
                $conn,
                "INSERT INTO password_resets (user_id, token_hash, expires_at, created_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 30 MINUTE), NOW())"
            );

            if (!$stmt2) {
                $error = "Gagal menyiapkan reset password";
            } else {
                mysqli_stmt_bind_param($stmt2, 'is', $userID, $tokenHash);
                $ok = mysqli_stmt_execute($stmt2);
                mysqli_stmt_close($stmt2);

                if (!$ok) {
                    $error = "Gagal membuat token reset. Pastikan tabel password_resets sudah dibuat.";
                } else {
                    $success = "Link reset password sudah dibuat. Silakan cek email Anda.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Lupa Password - Foundia</title>
    <link rel="icon" href="../assets/css/icon.png" type="image/png" sizes="192x192">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root{--primary:#8b5cf6;--primary2:#a78bfa;--text:#1f2937;--muted:#6b7280;--card:#ffffff;--field:#f3ecff;--fieldBorder:#e7ddff;}
        *{box-sizing:border-box;}
        body{margin:0;font-family:'Poppins','Segoe UI',Tahoma,Geneva,Verdana,sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#b388ff 0%,#ffd6a5 100%);padding:28px;}
        .auth-card{width:100%;max-width:430px;background:var(--card);border-radius:16px;box-shadow:0 18px 55px rgba(0,0,0,.16);padding:28px;position:relative;}
        .back-link{position:absolute;left:18px;top:18px;width:36px;height:36px;border-radius:999px;display:flex;align-items:center;justify-content:center;color:#374151;text-decoration:none;background:rgba(139,92,246,.10);transition:all .15s ease;}
        .back-link:hover{background:rgba(139,92,246,.16);transform:translateY(-1px);}
        .auth-title{margin:36px 0 8px 0;font-size:20px;color:var(--text);font-weight:800;letter-spacing:-.2px;}
        .auth-desc{margin:0 0 18px 0;font-size:12.5px;color:var(--muted);line-height:1.55;}
        .auth-input{width:100%;border:1px solid var(--fieldBorder);outline:none;background:var(--field);border-radius:12px;padding:12px 14px;font-size:14px;color:var(--text);transition:box-shadow .15s ease,border-color .15s ease;}
        .auth-input::placeholder{color:#9ca3af;}
        .auth-input:focus{border-color:rgba(139,92,246,.7);box-shadow:0 0 0 4px rgba(139,92,246,.18);background:#fff;}
        .auth-btn{width:100%;height:44px;margin-top:12px;border:none;border-radius:12px;padding:12px 14px;background:linear-gradient(135deg,var(--primary),var(--primary2));color:#fff;font-weight:700;cursor:pointer;box-shadow:0 10px 22px rgba(139,92,246,.28);transition:transform .15s ease,box-shadow .15s ease,filter .15s ease;}
        .auth-btn:hover{transform:translateY(-1px);filter:saturate(1.05);box-shadow:0 14px 28px rgba(139,92,246,.34);}
        .auth-btn:active{transform:translateY(0);box-shadow:0 10px 22px rgba(139,92,246,.28);}
        .auth-footer{margin-top:14px;text-align:center;font-size:12px;color:var(--muted);}
        .auth-footer button{background:none;border:none;color:#6d28d9;font-weight:700;cursor:pointer;padding:0;}
        .auth-footer button:hover{text-decoration:underline;}
        .alert{margin:10px 0 0 0;padding:10px 12px;border-radius:12px;font-size:13px;}
        .alert-error{background:#fee2e2;color:#991b1b;}
        .alert-success{background:#dcfce7;color:#166534;}
        .dev-link{margin-top:10px;font-size:12px;word-break:break-all;color:var(--muted);}
        .dev-link a{color:#6d28d9;text-decoration:none;font-weight:700;}
        .dev-link a:hover{text-decoration:underline;}
        @media (max-width:480px){body{padding:18px;}.auth-card{padding:22px;border-radius:14px;}}
    </style>
</head>
<body>

<div class="auth-card">
    <a class="back-link" href="login.php"><i class="fas fa-arrow-left"></i></a>

    <h1 class="auth-title">Lupa Password</h1>
    <p class="auth-desc">Masukkan email yang terdaftar dengan akun Anda, dan kami akan mengirimkan kode verifikasi melalui email untuk mengatur ulang kata sandi Anda.</p>

    <?php if ($error !== ''): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if ($success !== ''): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success); ?></div>
        <?php if ($resetLink !== ''): ?>
            <div class="dev-link">Dev link: <a href="<?= htmlspecialchars($resetLink); ?>"><?= htmlspecialchars($resetLink); ?></a></div>
        <?php endif; ?>
    <?php endif; ?>

    <form method="POST" style="margin-top:14px;">
        <input class="auth-input" type="email" name="email" placeholder="Email" value="<?= htmlspecialchars($email); ?>" required>
        <button class="auth-btn" type="submit" name="submit">Lanjut</button>

        <div class="auth-footer">
            Belum Menerima Email?
            <button type="submit" name="resend">Kirim Ulang</button>
        </div>
    </form>
</div>

</body>
</html>
