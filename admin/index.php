<?php
session_start();
include "../config/database.php";

/* PROTEKSI ADMIN */
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../index.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Beranda Admin</title>
    <link rel="icon" href="../assets/css/icon.png" type="image/png" sizes="192x192">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<!-- NAVIGASI ADMIN -->
<nav>
    <div class="nav-left">
        <img src="../assets/css/logo.png" alt="Foundia" class="nav-logo">
    </div>

    <div class="nav-right">
        <a href="index.php" class="nav-active">Beranda</a>
        <a href="search_barang.php">Cari Barang</a>
        <a href="daftar_barang.php">Daftar Barang</a>
        
        <div class="dropdown">
            <a href="#" class="user-menu">
                Admin: <?= $_SESSION['user']['nama_lengkap']; ?>
            </a>
            <div class="dropdown-content">
                <a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </div>
</nav>


        <!-- ADMIN DASHBOARD HERO -->
        <section class="hero">
            <div class="hero-content">
                <h1>Selamat datang, <?= $_SESSION['user']['nama_lengkap']; ?></h1>
                <p>
                    Kelola semua laporan barang yang masuk dan pantau aktivitas
                    platform Foundia
                </p>

                <div class="hero-actions">
                    <a href="daftar_barang.php" class="btn btn-primary">
                        <i class="fas fa-list"></i> Kelola Barang
                    </a>
                    <a href="search_barang.php" class="btn btn-secondary">
                        <i class="fas fa-search"></i> Cari Barang
                    </a>
                </div>
            </div>

            <div class="hero-image">
                <div class="floating-icons">
                    <i class="fas fa-shield-alt"></i>
                    <i class="fas fa-cogs"></i>
                    <i class="fas fa-chart-line"></i>
                    <i class="fas fa-users-cog"></i>
                    <i class="fas fa-database"></i>
                </div>
            </div>
        </section>


        <!-- ADMIN STATS DASHBOARD -->
        <section class="admin-dashboard">
            <div class="dashboard-header">
                <h2>Dashboard Statistik</h2>
                <p>Monitor real-time data dari platform Foundia</p>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card primary">
                    <div class="stat-icon">
                        <i class="fas fa-list-alt"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Total Laporan</h3>
                        <div class="stat-number"><?php 
                            $total = mysqli_query($conn, "SELECT COUNT(*) as count FROM items WHERE status='posted'");
                            echo mysqli_fetch_assoc($total)['count']; 
                        ?></div>
                        <p>Laporan aktif</p>
                    </div>
                </div>
                
                <div class="stat-card success">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Selesai</h3>
                        <div class="stat-number"><?php 
                            $selesai = mysqli_query($conn, "SELECT COUNT(*) as count FROM items WHERE status='selesai'");
                            echo mysqli_fetch_assoc($selesai)['count']; 
                        ?></div>
                        <p>Laporan selesai</p>
                    </div>
                </div>
                
                <div class="stat-card info">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Total User</h3>
                        <div class="stat-number"><?php 
                            $users = mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role='user'");
                            echo mysqli_fetch_assoc($users)['count']; 
                        ?></div>
                        <p>Pengguna terdaftar</p>
                    </div>
                </div>
            </div>
        </section>


<!-- Sticky Footer -->
<footer class="admin-footer">
    <div class="container">
        <div class="footer-content">
            <div class="footer-section">
                <p>&copy; 2024 Foundia - Platform Pencarian Barang Hilang</p>
            </div>
            <div class="footer-section">
                <p>Admin Dashboard</p>
            </div>
        </div>
    </div>
</footer>

<script>
// Sticky Navigation Scroll Effect
window.addEventListener('scroll', function() {
    const nav = document.querySelector('nav');
    if (window.scrollY > 50) {
        nav.classList.add('scrolled');
    } else {
        nav.classList.remove('scrolled');
    }
});

// Smooth scroll for internal links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});
</script>

</body>
</html>
