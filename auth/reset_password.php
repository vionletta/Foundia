<?php
session_start();
include "../config/database.php";

$token = trim($_GET['token'] ?? '');
$error = '';
$success = '';

$resetRow = null;
if ($token !== '') {
    $tokenHash = hash('sha256', $token);
    $stmt = mysqli_prepare(
        $conn,
        "SELECT id, user_id, expires_at, used_at FROM password_resets WHERE token_hash = ? LIMIT 1"
    );
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 's', $tokenHash);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        if ($res !== false) {
            $resetRow = mysqli_fetch_assoc($res);
        } else {
            $resetRow = null;
            mysqli_stmt_bind_result($stmt, $id, $user_id, $expires_at, $used_at);
            if (mysqli_stmt_fetch($stmt)) {
                $resetRow = [
                    'id' => $id,
                    'user_id' => $user_id,
                    'expires_at' => $expires_at,
                    'used_at' => $used_at,
                ];
            }
        }
        mysqli_stmt_close($stmt);
    }
}

$isValid = false;
if ($resetRow) {
    $isUsed = !empty($resetRow['used_at']);
    $isExpired = strtotime($resetRow['expires_at']) < time();
    if (!$isUsed && !$isExpired) {
        $isValid = true;
    }
}

if (isset($_POST['submit'])) {
    $password = $_POST['password'] ?? '';

    if (!$isValid) {
        $error = "Token tidak valid atau sudah kedaluwarsa";
    } elseif (strlen($password) < 8) {
        $error = "Kata sandi minimal 8 karakter";
    } elseif (!preg_match('/\d/', $password)) {
        $error = "Kata sandi harus mengandung minimal 1 angka";
    } else {
        $userID = (int)$resetRow['user_id'];
        $resetId = (int)$resetRow['id'];
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmtU = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE userID = ?");
        if (!$stmtU) {
            $error = "Gagal memperbarui password";
        } else {
            mysqli_stmt_bind_param($stmtU, 'si', $hash, $userID);
            $ok = mysqli_stmt_execute($stmtU);
            mysqli_stmt_close($stmtU);

            if (!$ok) {
                $error = "Gagal memperbarui password";
            } else {
                mysqli_query($conn, "UPDATE password_resets SET used_at = NOW() WHERE id = $resetId");
                $success = "Password berhasil direset. Silakan login.";
                $isValid = false;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Atur Kata Sandi - Foundia</title>
    <link rel="icon" href="assets/css/icon.png" type="image/png" sizes="192x192">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root{--primary:#8b5cf6;--primary2:#a78bfa;--text:#1f2937;--muted:#6b7280;--card:#ffffff;--field:#f3ecff;--fieldBorder:#e7ddff;}
        *{box-sizing:border-box;}
        body{margin:0;font-family:'Poppins','Segoe UI',Tahoma,Geneva,Verdana,sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#b388ff 0%,#ffd6a5 100%);padding:28px;}
        .auth-card{width:100%;max-width:430px;background:var(--card);border-radius:16px;box-shadow:0 18px 55px rgba(0,0,0,.16);padding:28px;position:relative;}
        .auth-title{margin:6px 0 8px 0;font-size:20px;color:var(--text);font-weight:800;letter-spacing:-.2px;}
        .auth-desc{margin:0 0 18px 0;font-size:12.5px;color:var(--muted);line-height:1.55;}
        .input-wrap{position:relative;}
        .auth-input{width:100%;border:1px solid var(--fieldBorder);outline:none;background:var(--field);border-radius:12px;padding:12px 44px 12px 14px;font-size:14px;color:var(--text);transition:box-shadow .15s ease,border-color .15s ease;}
        .auth-input::placeholder{color:#9ca3af;}
        .auth-input:focus{border-color:rgba(139,92,246,.7);box-shadow:0 0 0 4px rgba(139,92,246,.18);background:#fff;}
        .toggle{position:absolute;right:10px;top:50%;transform:translateY(-50%);border:none;background:rgba(139,92,246,.10);color:#4b5563;cursor:pointer;width:34px;height:34px;border-radius:10px;display:flex;align-items:center;justify-content:center;transition:all .15s ease;}
        .toggle:hover{background:rgba(139,92,246,.16);transform:translateY(-50%) translateY(-1px);}
        .auth-btn{width:100%;height:44px;margin-top:12px;border:none;border-radius:12px;padding:12px 14px;background:linear-gradient(135deg,var(--primary),var(--primary2));color:#fff;font-weight:700;cursor:pointer;box-shadow:0 10px 22px rgba(139,92,246,.28);transition:transform .15s ease,box-shadow .15s ease,filter .15s ease;}
        .auth-btn:hover{transform:translateY(-1px);filter:saturate(1.05);box-shadow:0 14px 28px rgba(139,92,246,.34);}
        .auth-btn:active{transform:translateY(0);box-shadow:0 10px 22px rgba(139,92,246,.28);}
        .alert{margin:10px 0 0 0;padding:10px 12px;border-radius:12px;font-size:13px;}
        .alert-error{background:#fee2e2;color:#991b1b;}
        .alert-success{background:#dcfce7;color:#166534;}
        .links{margin-top:14px;text-align:center;font-size:12px;color:var(--muted);}
        .links a{color:#6d28d9;text-decoration:none;font-weight:700;}
        .links a:hover{text-decoration:underline;}
        .disabled-note{margin-top:10px;font-size:12px;color:var(--muted);}
        @media (max-width:480px){body{padding:18px;}.auth-card{padding:22px;border-radius:14px;}}
    </style>
</head>
<body>

<div class="auth-card">
    <h1 class="auth-title">Atur Kata Sandi</h1>
    <p class="auth-desc">Kata sandi memerlukan minimal 8 karakter dan berisi angka</p>

    <?php if ($token === ''): ?>
        <div class="alert alert-error">Token tidak ditemukan</div>
        <div class="links"><a href="forgot_password.php">Kembali</a></div>
    <?php else: ?>
        <?php if (!$isValid && $success === ''): ?>
            <div class="alert alert-error">Token tidak valid atau sudah kedaluwarsa</div>
            <div class="links"><a href="forgot_password.php">Kembali</a></div>
        <?php endif; ?>

        <?php if ($error !== ''): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success !== ''): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success); ?></div>
            <div class="links"><a href="login.php">Login</a></div>
        <?php endif; ?>

        <?php if ($isValid && $success === ''): ?>
            <form method="POST" style="margin-top:14px;">
                <div class="input-wrap">
                    <input id="pwd" class="auth-input" type="password" name="password" placeholder="Password" required>
                    <button class="toggle" type="button" onclick="togglePwd()"><i id="eye" class="fas fa-eye"></i></button>
                </div>
                <button class="auth-btn" type="submit" name="submit">Lanjut</button>
            </form>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script>
function togglePwd(){
    const input = document.getElementById('pwd');
    const eye = document.getElementById('eye');
    if (!input || !eye) return;
    if (input.type === 'password') {
        input.type = 'text';
        eye.classList.remove('fa-eye');
        eye.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        eye.classList.remove('fa-eye-slash');
        eye.classList.add('fa-eye');
    }
}
</script>

</body>
</html>
