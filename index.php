<?php
session_start();
include "config/database.php";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Beranda</title>
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
        <a href="index.php" class="nav-active">Beranda</a>
        <a href="search.php">Cari Barang</a>
        <a href="daftar_barang.php">Daftar Barang</a>
        
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

<!-- HERO SECTION -->
<section class="hero">
    <div class="hero-content">
        <h1>Temukan Kembali Barang Hilang Anda</h1>
        <p>Platform terpercaya untuk melaporkan dan mencari barang hilang atau ditemukan</p>
        
        <?php if (!isset($_SESSION['user'])) { ?>
            <div class="hero-actions">
                <a href="auth/login.php" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Masuk untuk Melaporkan
                </a>
                <a href="search.php" class="btn btn-secondary">
                    <i class="fas fa-search"></i> Cari Barang
                </a>
            </div>
        <?php } else { ?>
            <div class="hero-actions">
                <a href="user/laporan.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Buat Laporan Baru
                </a>
                <a href="search.php" class="btn btn-secondary">
                    <i class="fas fa-search"></i> Cari Barang
                </a>
            </div>
        <?php } ?>
    </div>
    
    <div class="hero-image">
        <div class="floating-icons">
            <i class="fas fa-mobile-alt"></i>
            <i class="fas fa-wallet"></i>
            <i class="fas fa-key"></i>
            <i class="fas fa-backpack"></i>
            <i class="fas fa-id-card"></i>
        </div>
    </div>
</section>

<!-- FEATURES SECTION -->
<section class="features">
    <div class="container">
        <h2>Mengapa Memilih Foundia?</h2>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-bullhorn"></i>
                </div>
                <h3>Laporkan Mudah</h3>
                <p>Laporkan barang hilang atau temuan Anda dengan cepat dan mudah</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3>Komunitas Besar</h3>
                <p>Bergabung dengan ribuan pengguna yang membantu menemukan barang hilang</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3>Aman & Terpercaya</h3>
                <p>Privasi dan keamanan data Anda adalah prioritas kami</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-bell"></i>
                </div>
                <h3>Notifikasi Real-time</h3>
                <p>Dapatkan pemberitahuan saat barang yang Anda cari ditemukan</p>
            </div>
        </div>
    </div>
</section>

<!-- RECENT ITEMS -->
<section class="recent-items">
    <div class="container">
        <h2>Daftar Barang</h2>
        <div class="items-preview">
            <?php
            $recent_items = mysqli_query($conn, "
                SELECT items.*, users.nama_lengkap
                FROM items
                JOIN users ON items.userID = users.userID
                WHERE items.status='posted' AND (items.is_hidden IS NULL OR items.is_hidden = 0)
                ORDER BY items.created_at DESC
                LIMIT 3
            ");
            
            if (mysqli_num_rows($recent_items) > 0) {
                while ($item = mysqli_fetch_assoc($recent_items)) {
                    ?>
                    <div class="preview-card" onclick="openDetail(<?= $item['itemID']; ?>)">
                        <div class="preview-image">
                            <?php if(isset($item['photo']) && $item['photo']): ?>
                                <img src="assets/images/<?= $item['photo']; ?>" onerror="this.style.display='none';">
                            <?php endif; ?>
                            <span class="badge <?= $item['jenis_laporan']=='hilang'?'red':'green'; ?>">
                                <?= ucfirst($item['jenis_laporan']); ?>
                            </span>
                        </div>
                        <div class="preview-content">
                            <h4><?= htmlspecialchars($item['itemName']); ?></h4>
                            <p><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($item['location']); ?></p>
                            <small><i class="fas fa-clock"></i> <?= date('d M Y', strtotime($item['created_at'])); ?></small>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo '<p>Belum ada barang yang dilaporkan.</p>';
            }
            ?>
        </div>
        
        <div class="text-center">
            <a href="daftar_barang.php" class="btn btn-primary">
                <i class="fas fa-arrow-right"></i> Lihat Semua Barang
            </a>
        </div>
    </div>
</section>

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

function showLoginAlert() {
    console.log("LOGIN ALERT TRIGGERED");
    
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

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById("modal");
    if (event.target == modal) {
        closeModal();
    }
}
</script>

<!-- FOOTER -->
<footer>
    <div class="container">
        <div class="footer-content">
            <div class="footer-section">
                <h3>Foundia</h3>
                <p>Platform terpercaya untuk melaporkan dan mencari barang hilang atau ditemukan</p>
            </div>
            
            <div class="footer-section">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="index.php">Beranda</a></li>
                    <li><a href="search.php">Cari Barang</a></li>
                    <li><a href="daftar_barang.php">Daftar Barang</a></li>
                    <?php if (isset($_SESSION['user'])): ?>
                        <li><a href="user/laporan.php">Laporan</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            
            <div class="footer-section">
                <h4>Kontak</h4>
                <p><i class="fas fa-envelope"></i> support@foundia.com</p>
                <p><i class="fas fa-phone"></i> +62 812-3456-7890</p>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; 2024 Foundia. All rights reserved.</p>
        </div>
    </div>
</footer>

</body>
</html>
