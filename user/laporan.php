<?php
session_start();
include "../config/database.php";

if (!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit;
}

$userID = $_SESSION['user']['userID'];
$tab = $_GET['tab'] ?? 'draft';
if (!in_array($tab, ['draft', 'posted', 'selesai'], true)) {
    $tab = 'draft';
}

/* =========================
   KONFIRMASI BARANG
========================= */
if (isset($_POST['confirm']) && isset($_POST['itemID'])) {
    $itemID = $_POST['itemID'];

    mysqli_query($conn, "
        UPDATE items 
        SET status = 'selesai'
        WHERE itemID = '$itemID'
        AND userID = '$userID'
    ");

    header("Location: laporan.php");
    exit;
}

/* =========================
   QUERY DRAFT
========================= */
$draft = mysqli_query($conn, "
    SELECT * FROM items
    WHERE userID = '$userID'
    AND status = 'draft'
    ORDER BY created_at DESC
");

/* =========================
   QUERY POSTINGAN SAYA
========================= */
$postingan = mysqli_query($conn, "
    SELECT * FROM items
    WHERE userID = '$userID'
    AND status = 'posted'
    ORDER BY created_at DESC
");

/* =========================
   QUERY SELESAI
========================= */
$selesai = mysqli_query($conn, "
    SELECT * FROM items
    WHERE userID = '$userID'
    AND status = 'selesai'
    ORDER BY created_at DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Laporan Saya</title>
    <link rel="icon" href="../assets/css/icon.png" type="image/png" sizes="192x192">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<!-- NAVIGASI -->
<nav>
    <div class="nav-left">
        <img src="../assets/css/logo.png" alt="Foundia" class="nav-logo">
    </div>

    <div class="nav-right">
        <a href="../index.php">Beranda</a>
        <a href="../search.php">Cari Barang</a>
        <a href="../daftar_barang.php">Daftar Barang</a>
        <a href="laporan.php" class="nav-active">Laporan</a>

        <div class="dropdown">
            <a href="#" class="user-menu">
                <i class="fas fa-user"></i> <?= $_SESSION['user']['nama_lengkap']; ?>
            </a>
            <div class="dropdown-content">
                <a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </div>
</nav>

<div class="content">

<div class="report-header">
    <div class="report-header-left">
        <h1 class="report-title">Laporan Saya</h1>
        <p class="report-subtitle">Lihat dan kelola laporan barang hilang/temuan Anda</p>
    </div>
    <div class="report-header-right">
        <a href="add_item.php" class="btn btn-primary btn-create-report">
            <i class="fas fa-plus"></i> Buat Laporan Baru
        </a>
    </div>
</div>

<div class="report-tabs">
    <a href="laporan.php?tab=draft" class="report-tab <?= $tab === 'draft' ? 'active' : ''; ?>">Draft</a>
    <a href="laporan.php?tab=posted" class="report-tab <?= $tab === 'posted' ? 'active' : ''; ?>">Postingan Saya</a>
    <a href="laporan.php?tab=selesai" class="report-tab <?= $tab === 'selesai' ? 'active' : ''; ?>">Selesai</a>
</div>

<div class="report-tab-panel">
    <?php if ($tab === 'draft'): ?>
        <?php if (mysqli_num_rows($draft) > 0): ?>
            <div class="grid">
                <?php while ($d = mysqli_fetch_assoc($draft)) { ?>
                    <div class="card">
                        <div class="card-content">
                            <h3><?= htmlspecialchars($d['itemName']); ?></h3>
                            <p><strong>Lokasi:</strong> <?= htmlspecialchars($d['location']); ?></p>
                            <small><?= date('d M Y', strtotime($d['created_at'])); ?></small>

                            <div class="report-card-actions">
                                <a href="edit_item.php?id=<?= $d['itemID']; ?>" class="btn btn-edit">Edit</a>
                                <a href="delete_item.php?id=<?= $d['itemID']; ?>" onclick="return confirm('Hapus draft?')" class="btn btn-delete">Hapus</a>
                                <form method="POST" action="post_item.php" class="report-inline-form">
                                    <input type="hidden" name="itemID" value="<?= $d['itemID']; ?>">
                                    <button class="btn btn-confirm">Posting</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        <?php else: ?>
            <div class="report-empty"></div>
        <?php endif; ?>
    <?php elseif ($tab === 'posted'): ?>
        <?php if (mysqli_num_rows($postingan) > 0): ?>
            <div class="grid">
                <?php while ($p = mysqli_fetch_assoc($postingan)) { ?>
                    <div class="card">
                        <div class="card-image">
                            <?php if ($p['photo']) { ?>
                                <img src="../assets/images/<?= $p['photo']; ?>">
                            <?php } ?>
                            <span class="badge <?= $p['jenis_laporan']=='hilang'?'red':'green'; ?>">
                                <?= ucfirst($p['jenis_laporan']); ?>
                            </span>
                        </div>

                        <div class="card-content">
                            <h3><?= htmlspecialchars($p['itemName']); ?></h3>
                            <p><strong>Lokasi:</strong> <?= htmlspecialchars($p['location']); ?></p>
                            <small><?= date('d M Y', strtotime($p['created_at'])); ?></small>

                            <form method="POST" class="report-confirm-form">
                                <input type="hidden" name="itemID" value="<?= $p['itemID']; ?>">
                                <button type="submit" name="confirm" class="btn btn-confirm">Konfirmasi Barang</button>
                            </form>
                        </div>
                    </div>
                <?php } ?>
            </div>
        <?php else: ?>
            <div class="report-empty"></div>
        <?php endif; ?>
    <?php else: ?>
        <?php if (mysqli_num_rows($selesai) > 0): ?>
            <div class="grid">
                <?php while ($s = mysqli_fetch_assoc($selesai)) { ?>
                    <div class="card">
                        <div class="card-image">
                            <?php if ($s['photo']) { ?>
                                <img src="../assets/images/<?= $s['photo']; ?>">
                            <?php } ?>
                            <span class="badge blue">Selesai</span>
                        </div>

                        <div class="card-content">
                            <h3><?= htmlspecialchars($s['itemName']); ?></h3>
                            <p><strong>Lokasi:</strong> <?= htmlspecialchars($s['location']); ?></p>
                            <small><?= date('d M Y', strtotime($s['created_at'])); ?></small>
                        </div>
                    </div>
                <?php } ?>
            </div>
        <?php else: ?>
            <div class="report-empty"></div>
        <?php endif; ?>
    <?php endif; ?>
</div>

</div>
</body>
</html>
