<?php
session_start();
include "../config/database.php";

/* PROTEKSI ADMIN */
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../index.php");
    exit;
}

$search = isset($_GET['search']) ? $_GET['search'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';
$items = [];

if ($search || $category) {
    $where_conditions = ["items.status='posted'"];
    if ($search) {
        $where_conditions[] = "(items.itemName LIKE '%$search%' OR items.description LIKE '%$search%' OR items.location LIKE '%$search%')";
    }
    if ($category) {
        $where_conditions[] = "items.category = '$category'";
    }
    
    $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";
    
    $query = mysqli_query($conn, "
        SELECT items.*, users.nama_lengkap
        FROM items
        JOIN users ON items.userID = users.userID
        $where_clause
        ORDER BY items.created_at DESC
    ");
    
    if ($query) {
        while ($row = mysqli_fetch_assoc($query)) {
            $items[] = $row;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Cari Barang - Admin</title>
    <link rel="icon" href="../assets/css/icon.png" type="image/png" sizes="192x192">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="admin-page">

<!-- NAVIGASI ADMIN -->
<nav>
    <div class="nav-left">
        <img src="../assets/css/logo.png" alt="Foundia" class="nav-logo">
    </div>

    <div class="nav-right">
        <a href="index.php">Beranda</a>
        <a href="search_barang.php" class="nav-active">Cari Barang</a>
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

<!-- KONTEN CARI BARANG -->
<div class="content">
    <div class="search-hero">
        <div class="search-hero-content">
            <h1>Cari Barang Hilang atau Ditemukan</h1>
            <p>Temukan barang yang dilaporkan oleh pengguna platform</p>
        </div>
        
        <div class="search-hero-visual">
            <div class="floating-search-icons">
                <i class="fas fa-search"></i>
                <i class="fas fa-map-marker-alt"></i>
                <i class="fas fa-filter"></i>
            </div>
        </div>
    </div>
    
    <div class="container">
        <div class="search-section">
            <form method="GET" action="search_barang.php" class="search-form">
                <div class="search-input-group">
                    <div class="search-input-wrapper">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" name="search" class="search-input" placeholder="Masukkan nama barang atau deskripsi..." value="<?= htmlspecialchars($search); ?>">
                    </div>
                    <select name="category" class="search-select">
                        <option value="">Semua Kategori</option>
                        <option value="elektronik" <?= $category == 'elektronik' ? 'selected' : ''; ?>>Elektronik</option>
                        <option value="dompet" <?= $category == 'dompet' ? 'selected' : ''; ?>>Dompet</option>
                        <option value="kunci" <?= $category == 'kunci' ? 'selected' : ''; ?>>Kunci</option>
                        <option value="hp" <?= $category == 'hp' ? 'selected' : ''; ?>>Handphone</option>
                        <option value="tas" <?= $category == 'tas' ? 'selected' : ''; ?>>Tas</option>
                        <option value="lainnya" <?= $category == 'lainnya' ? 'selected' : ''; ?>>Lainnya</option>
                    </select>
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i> Cari Barang
                    </button>
                </div>
            </form>
        </div>
    
        <?php if ($search || $category): ?>
            <div class="search-results-header">
                <div class="search-results-info">
                    <span class="search-results-count"><?= count($items); ?> Hasil</span>
                    <span class="search-results-text">ditemukan untuk "<?= htmlspecialchars($search); ?>"</span>
                </div>
            </div>
            
            <?php if (empty($items)): ?>
                <div class="no-results">
                    <i class="fas fa-search"></i>
                    <p>Tidak ada barang yang ditemukan</p>
                </div>
            <?php else: ?>
                <div class="items-container">
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
                                    <p><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($item['location']); ?></p>
                                    <p><i class="fas fa-user"></i> <?= htmlspecialchars($item['nama_lengkap']); ?></p>
                                    <small><i class="fas fa-clock"></i> <?= date('d M Y', strtotime($item['created_at'])); ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
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
    const newStatus = currentStatus == 1 ? 0 : 1;
    const actionText = newStatus == 1 ? 'menyembunyikan' : 'menampilkan';
    
    if (confirm(`Apakah Anda yakin ingin ${actionText} barang ini?`)) {
        fetch('toggle_visibility.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `itemID=${itemID}`
        })
        .then(response => response.json())
        .then(data => {
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
            alert('Terjadi kesalahan. Silakan coba lagi.');
        });
    }
}

function deleteItem() {
    if (currentItemID && confirm('Apakah Anda yakin ingin menghapus barang ini? Tindakan ini tidak dapat dibatalkan!')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'daftar_barang.php';
        
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
