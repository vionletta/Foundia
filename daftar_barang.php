<?php
session_start();
include "config/database.php";

// Handle confirm action (for item owner)
if (isset($_POST['action']) && $_POST['action'] == 'confirm' && isset($_POST['itemID'])) {
    if (!isset($_SESSION['user'])) {
        $error_message = "Silakan login terlebih dahulu.";
    } else {
        $itemID = (int)$_POST['itemID'];
        $userID = (int)$_SESSION['user']['userID'];
        $confirm_query = mysqli_query($conn, "UPDATE items SET status = 'selesai' WHERE itemID = $itemID AND userID = $userID");
        if ($confirm_query) {
            $success_message = "Barang berhasil dikonfirmasi!";
        } else {
            $error_message = "Gagal mengkonfirmasi barang!";
        }
    }
}

$query = mysqli_query($conn,"
    SELECT items.*, users.nama_lengkap, users.no_telp
    FROM items
    JOIN users ON items.userID = users.userID
    WHERE items.status='posted' AND (items.is_hidden IS NULL OR items.is_hidden = 0)
    ORDER BY items.created_at DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Daftar Barang</title>
    <link rel="icon" href="assets/css/icon.png" type="image/png" sizes="192x192">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<!-- NAVIGASI -->
<nav>
    <div class="nav-left">
        <img src="assets/css/logo.png" alt="Foundia" class="nav-logo">
    </div>

    <div class="nav-right">
        <a href="index.php">Beranda</a>
        <a href="search.php">Cari Barang</a>
        <a href="daftar_barang.php"  class="nav-active">Daftar Barang</a>
        
        <?php if (!isset($_SESSION['user'])) { ?>
            <!-- JIKA BELUM LOGIN -->
            <a href="auth/login.php" class="btn-login">Login</a>
        <?php } else { ?>
            <!-- JIKA SUDAH LOGIN -->
            <a href="user/laporan.php">Laporan</a>

            <div class="dropdown">
                <a href="#" class="user-menu">
                    <i class="fas fa-user"></i> <?= $_SESSION['user']['nama_lengkap']; ?>
                </a>
                <div class="dropdown-content">
                    <a href="auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
        <?php } ?>
    </div>
</nav>

<div class="content">
    <div class="search-hero">
        <div class="search-hero-content">
            <h1>Daftar Barang</h1>
            <p>Lihat semua laporan barang hilang atau ditemukan yang sudah diposting</p>
        </div>
        <div class="search-hero-visual">
            <div class="floating-search-icons">
                <i class="fas fa-search"></i>
                <i class="fas fa-map-marker-alt"></i>
                <i class="fas fa-bell"></i>
            </div>
        </div>
    </div>
    
    <?php if (isset($success_message)): ?>
        <div class="alert alert-success"><?= $success_message; ?></div>
    <?php endif; ?>
    
    <?php if (isset($error_message)): ?>
        <div class="alert alert-error"><?= $error_message; ?></div>
    <?php endif; ?>

    <div class="grid">
    <?php while($row = mysqli_fetch_assoc($query)) { ?>
        <div class="card" onclick="openDetail(<?= $row['itemID']; ?>)">
            <div class="card-image">
                <?php if(isset($row['photo']) && $row['photo']): ?>
                    <img src="assets/images/<?= $row['photo']; ?>" onerror="this.style.display='none';">
                <?php endif; ?>
                <span class="badge <?= $row['jenis_laporan']=='hilang'?'red':'green'; ?>">
                    <?= ucfirst($row['jenis_laporan']); ?>
                </span>
            </div>
            <div class="card-content">
                <h3><?= $row['itemName']; ?></h3>
                <p><strong>Lokasi:</strong> <?= $row['location']; ?></p>
                <p><strong>Pelapor:</strong> <?= $row['nama_lengkap']; ?></p>
                <small><?= date('d M Y', strtotime($row['created_at'])); ?></small>
            </div>
        </div>
    <?php } ?>
    </div>
</div>

<!-- Modal -->
<div id="modal" class="modal"></div>

<script>
function openDetail(id){
    fetch("detail_item.php?id="+id)
    .then(res=>res.text())
    .then(data=>{
        document.getElementById("modal").innerHTML = data;
        document.getElementById("modal").style.display = "block";
    });
}

function closeModal() {
    document.getElementById("modal").style.display = "none";
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById("modal");
    if (event.target == modal) {
        closeModal();
    }
}

function showLoginAlert() {

    console.log("LOGIN ALERT TRIGGERED"); // DEBUG (cek console)

    // Cegah dobel
    if (document.querySelector('.login-alert')) return;

    const alertBox = document.createElement('div');
    alertBox.className = 'login-alert';
    alertBox.textContent = 'Anda belum login. Silakan login terlebih dahulu.';

    document.body.appendChild(alertBox);

    // Trigger animasi
    setTimeout(() => alertBox.classList.add('show'), 10);

    // Hilang otomatis
    setTimeout(() => {
        alertBox.classList.remove('show');
        setTimeout(() => alertBox.remove(), 300);
    }, 3000);
}
</script>

</body>
</html>
