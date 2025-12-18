<?php
session_start();
include "../config/database.php";

/* PROTEKSI ADMIN */
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../index.php");
    exit;
}

// Handle delete action
if (isset($_POST['action']) && $_POST['action'] == 'delete' && isset($_POST['itemID'])) {
    $itemID = $_POST['itemID'];
    $delete_query = mysqli_query($conn, "DELETE FROM items WHERE itemID = $itemID");
    if ($delete_query) {
        $success_message = "Barang berhasil dihapus!";
    } else {
        $error_message = "Gagal menghapus barang!";
    }
}

// Get all posted items for admin management
$query = mysqli_query($conn, "
    SELECT items.*, users.nama_lengkap, users.no_telp
    FROM items
    JOIN users ON items.userID = users.userID
    WHERE items.status='posted'
    ORDER BY items.created_at DESC
");

$items = [];
if ($query) {
    while ($row = mysqli_fetch_assoc($query)) {
        $items[] = $row;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Daftar Barang - Admin</title>
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
        <a href="index.php">Beranda</a>
        <a href="search_barang.php">Cari Barang</a>
        <a href="daftar_barang.php" class="nav-active">Daftar Barang</a>
        
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
        <?php foreach ($items as $item): ?>
            <div class="card" onclick="showDetail(<?= $item['itemID']; ?>)">
                <div class="card-image">
                    <?php if(isset($item['photo']) && $item['photo']): ?>
                        <img src="../assets/images/<?= $item['photo']; ?>" onerror="this.style.display='none';">
                    <?php endif; ?>
                    <span class="badge <?= $item['jenis_laporan']=='hilang'?'red':'green'; ?>">
                        <?= ucfirst($item['jenis_laporan']); ?>
                    </span>
                </div>
                <div class="card-content">
                    <h3><?= htmlspecialchars($item['itemName']); ?></h3>
                    <p><strong>Lokasi:</strong> <?= htmlspecialchars($item['location']); ?></p>
                    <p><strong>Pelapor:</strong> <?= htmlspecialchars($item['nama_lengkap']); ?></p>
                    <small><?= date('d M Y', strtotime($item['created_at'])); ?></small>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modal Detail Barang -->
<div id="detailModal" class="modal">
    <div id="modalBody">
        <!-- Content will be loaded here -->
    </div>
</div>

<script>
let currentItemID = null;

function showDetail(itemID) {
    currentItemID = itemID;
    
    // Load item details via AJAX
    fetch(`get_item_detail.php?id=${itemID}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('modalBody').innerHTML = data.html;
                
                // Show modal
                document.getElementById('detailModal').style.display = 'block';
            } else {
                alert('Gagal memuat detail barang');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat memuat detail barang');
        });
}

function closeModal() {
    document.getElementById('detailModal').style.display = 'none';
    currentItemID = null;
}

function toggleVisibility(itemID, currentStatus) {
    console.log('toggleVisibility called with itemID:', itemID, 'currentStatus:', currentStatus);
    
    const newStatus = currentStatus == 1 ? 0 : 1;
    const actionText = newStatus == 1 ? 'menyembunyikan' : 'menampilkan';
    
    if (confirm(`Apakah Anda yakin ingin ${actionText} barang ini?`)) {
        console.log('Sending request to toggle_visibility.php...');
        
        fetch('toggle_visibility.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `itemID=${itemID}`
        })
        .then(response => {
            console.log('Response received:', response);
            return response.json();
        })
        .then(data => {
            console.log('Data received:', data);
            if (data.success) {
                alert(data.message);
                // Refresh the modal to show updated button
                if (currentItemID) {
                    showDetail(currentItemID);
                }
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan. Silakan coba lagi. Error: ' + error.message);
        });
    }
}

function deleteItem() {
    if (currentItemID && confirm('Apakah Anda yakin ingin menghapus barang ini? Tindakan ini tidak dapat dibatalkan!')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '';
        
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'delete';
        
        const itemIDInput = document.createElement('input');
        itemIDInput.type = 'hidden';
        itemIDInput.name = 'itemID';
        itemIDInput.value = currentItemID;
        
        form.appendChild(actionInput);
        form.appendChild(itemIDInput);
        document.body.appendChild(form);
        form.submit();
    }
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('detailModal');
    if (event.target == modal) {
        closeModal();
    }
}
</script>

</body>
</html>
