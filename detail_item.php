<?php
session_start();
include "config/database.php";

$id = $_GET['id'];
$userLogin = $_SESSION['user']['userID'] ?? 0;

$q = mysqli_query($conn,"
    SELECT items.*, users.nama_lengkap, users.no_telp
    FROM items
    JOIN users ON items.userID = users.userID
    WHERE itemID='$id'
");
$data = mysqli_fetch_assoc($q);
?>

<!-- Link CSS untuk login notification -->
<link rel="stylesheet" href="assets/css/login-notification.css">

<div class="modal-item-detail">

    <!-- HEADER -->
    <div class="modal-header">
        <div class="item-title-section">
            <h2 class="item-title"><?= $data['itemName']; ?></h2>
            <span class="status-badge <?= $data['jenis_laporan']=='hilang'?'status-lost':'status-found'; ?>">
                <?= ucfirst($data['jenis_laporan']); ?>
            </span>
        </div>
        <span class="close-modal" onclick="closeModal()">&times;</span>
    </div>

    <!-- BODY -->
    <div class="modal-body">

        <!-- IMAGE -->
        <div class="item-image-section">
            <div class="image-container">
                <?php if(!empty($data['photo'])): ?>
                    <img src="assets/images/<?= $data['photo']; ?>" 
                         class="item-image"
                         onerror="this.style.display='none'; this.parentElement.style.display='none';">
                <?php else: ?>
                    <div class="no-image-placeholder">
                        <i class="fas fa-image"></i>
                        <span>Tidak ada foto</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- INFO -->
        <div class="item-info-section">
            <div class="info-grid">

                <div class="info-item">
                    <div class="info-label"><i class="fas fa-map-marker-alt"></i> Lokasi</div>
                    <div class="info-value"><?= $data['location']; ?></div>
                </div>

                <div class="info-item">
                    <div class="info-label">
                        <i class="fas fa-calendar-alt"></i>
                        Tanggal <?= $data['jenis_laporan']=='hilang'?'Kehilangan':'Penemuan'; ?>
                    </div>
                    <div class="info-value"><?= date('d F Y', strtotime($data['dateLost'])); ?></div>
                </div>

                <div class="info-item">
                    <div class="info-label"><i class="fas fa-user"></i> Pelapor</div>
                    <div class="info-value"><?= $data['nama_lengkap']; ?></div>
                </div>

                <div class="info-item">
                    <div class="info-label"><i class="fas fa-tag"></i> Kategori</div>
                    <div class="info-value"><?= ucfirst($data['category']); ?></div>
                </div>

            </div>

            <div class="description-section">
                <div class="info-label"><i class="fas fa-info-circle"></i> Deskripsi</div>
                <div class="description-text"><?= $data['description']; ?></div>
            </div>
        </div>
    </div>

    <!-- FOOTER -->
    <div class="modal-footer">
        <div class="action-buttons">

            <?php if($data['userID'] == $userLogin): ?>
                <!-- OWNER BARANG -->
                <form action="user/confirm_item.php" method="POST" style="display: contents;">
                    <input type="hidden" name="itemID" value="<?= $data['itemID']; ?>">
                    <button type="submit" class="btn btn-primary btn-confirm">
                        <i class="fas fa-check"></i> Konfirmasi Barang
                    </button>
                </form>

            <?php else: ?>

                <?php if($userLogin): ?>
                    <!-- USER LOGIN -->
                    <a href="https://wa.me/62<?= ltrim($data['no_telp'],'0'); ?>" 
                       target="_blank"
                       class="btn btn-success btn-message">
                        <i class="fab fa-whatsapp"></i> Kirim Pesan
                    </a>
                <?php else: ?>
                    <!-- USER BELUM LOGIN -->
                    <div class="login-prompt">
                        <i class="fas fa-lock"></i>
                        <span>Login untuk kirim pesan kepada pelapor</span>
                    </div>
                <?php endif; ?>

            <?php endif; ?>

        </div>
    </div>
</div>

<!-- JavaScript untuk login notification -->
<script src="assets/js/login-notification.js"></script>



