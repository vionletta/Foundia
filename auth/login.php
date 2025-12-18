<?php
session_start();
include "../config/database.php";

$error = '';

if (isset($_POST['login'])) {
    $email    = $_POST['email'];
    $password = $_POST['password'];

    $query = mysqli_query($conn, "
        SELECT * FROM users WHERE email='$email'
    ");

    if (mysqli_num_rows($query) == 1) {
        $user = mysqli_fetch_assoc($query);

        if (password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user;

            // 🔑 Redirect berdasarkan role
            if ($user['role'] === 'admin') {
                header("Location: ../admin/index.php");
            } else {
                header("Location: ../index.php");
            }
            exit;
        } else {
            $error = "Email atau password salah";
        }
    } else {
        $error = "Email atau password salah";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login Foundia</title>
    <link rel="icon" href="../assets/css/icon.png" type="image/png" sizes="192x192">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root{--primary:#8b5cf6;--primary2:#a78bfa;--text:#111827;--muted:#6b7280;--card:#ffffff;--field:#f3f4f6;--fieldBorder:#e5e7eb;}
        *{box-sizing:border-box;}
        body{margin:0;font-family:'Poppins','Segoe UI',Tahoma,Geneva,Verdana,sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#b388ff 0%,#ffd6a5 100%);padding:32px;}
        .auth-card{width:100%;max-width:560px;background:var(--card);border-radius:16px;box-shadow:0 22px 70px rgba(0,0,0,.18);padding:42px 44px;}
        .auth-title{text-align:center;margin:0 0 26px 0;font-size:26px;color:var(--text);font-weight:800;letter-spacing:-.3px;}
        .form-group{margin-top:16px;}
        .input-wrap{position:relative;}
        .auth-input{width:100%;border:1px solid var(--fieldBorder);outline:none;background:var(--field);border-radius:12px;padding:14px 16px;font-size:15px;color:var(--text);height:52px;transition:box-shadow .15s ease,border-color .15s ease,background .15s ease;}
        .auth-input::placeholder{color:#9ca3af;}
        .auth-input:focus{border-color:rgba(139,92,246,.7);box-shadow:0 0 0 4px rgba(139,92,246,.18);background:#fff;}
        .toggle{position:absolute;right:12px;top:50%;transform:translateY(-50%);border:none;background:transparent;color:#6b7280;cursor:pointer;width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;}
        .toggle:hover{background:rgba(139,92,246,.10);color:#4b5563;}
        .forgot{margin-top:10px;font-size:12px;text-align:right;}
        .forgot a{color:#6d28d9;text-decoration:none;font-weight:800;}
        .forgot a:hover{text-decoration:underline;}
        .auth-btn{display:block;width:100%;height:52px;margin:22px 0 0 0;border:none;border-radius:12px;padding:14px 16px;background:linear-gradient(135deg,var(--primary),var(--primary2));color:#fff;font-weight:800;cursor:pointer;box-shadow:0 14px 30px rgba(139,92,246,.28);transition:transform .15s ease,box-shadow .15s ease,filter .15s ease;}
        .auth-btn:hover{transform:translateY(-1px);filter:saturate(1.05);box-shadow:0 18px 40px rgba(139,92,246,.34);}
        .auth-btn:active{transform:translateY(0);box-shadow:0 14px 30px rgba(139,92,246,.28);}
        .alert{margin:10px 0 0 0;padding:10px 12px;border-radius:12px;font-size:13px;}
        .alert-error{background:#fee2e2;color:#991b1b;}
        .links{margin-top:18px;text-align:center;font-size:13px;color:var(--muted);}
        .links a{color:#6d28d9;text-decoration:none;font-weight:900;}
        .links a:hover{text-decoration:underline;}
        @media (max-width:600px){.auth-card{padding:28px 22px;}}
        @media (max-width:480px){body{padding:18px;}}
    </style>
</head>
<body>

<div class="auth-card">
    <h1 class="auth-title">Login</h1>

    <?php if ($error != '') { ?>
        <div class="alert alert-error"><?= htmlspecialchars($error); ?></div>
    <?php } ?>

    <form method="POST">
        <div class="form-group">
            <input class="auth-input" type="email" name="email" placeholder="Email" required>
        </div>
        <div class="form-group">
            <div class="input-wrap">
                <input id="pwd" class="auth-input" type="password" name="password" placeholder="Password" required>
                <button class="toggle" type="button" onclick="togglePwd()"><i id="eye" class="fas fa-eye"></i></button>
            </div>
        </div>
        <div class="forgot">
            <a href="forgot_password.php">Lupa Password?</a>
        </div>
        <button class="auth-btn" type="submit" name="login">Login</button>
    </form>

    <div class="links">
        <a href="register.php">Belum punya akun? Register</a>
    </div>
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
