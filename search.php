<?php
session_start();
include "config/database.php";

$keyword = trim($_GET['q'] ?? '');
$category = trim($_GET['category'] ?? '');
?>

<!DOCTYPE html>
<html>
<head>
    <title>Cari Barang</title>
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
        <a href="search.php" class="nav-active">Cari Barang</a>
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

<div class="content">
    <div class="search-hero">
        <div class="search-hero-content">
            <h1>Cari Barang Hilang atau Ditemukan</h1>
            <p>Temukan kembali barang Anda yang hilang atau bantu orang lain menemukan barang mereka</p>
        </div>
        <div class="search-hero-visual">
            <div class="floating-search-icons">
                <i class="fas fa-search"></i>
                <i class="fas fa-map-marker-alt"></i>
                <i class="fas fa-bell"></i>
            </div>
        </div>
    </div>
    
    <?php if(isset($_GET['q']) || isset($_GET['category'])): ?>
    <div class="search-section">
        <div class="search-form">
            <form method="GET">
                <div class="search-input-group">
                    <div class="search-input-wrapper">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" name="q" placeholder="Masukkan nama barang atau deskripsi..." value="<?= htmlspecialchars($keyword); ?>" class="search-input">
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
                    <button type="submit" class="btn btn-primary search-btn">
                        <i class="fas fa-search"></i> Cari Barang
                    </button>
                </div>
            </form>
        </div>
    </div>

    <hr>

    <div class="items-container">
        <?php
        $where_conditions = ["items.status='posted'", "(items.is_hidden IS NULL OR items.is_hidden = 0)"];
        $types = '';
        $params = [];

        if ($keyword !== '') {
            $where_conditions[] = "(items.itemName LIKE ? OR items.description LIKE ? OR items.location LIKE ?)";
            $like = '%' . $keyword . '%';
            $types .= 'sss';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        if ($category !== '') {
            $where_conditions[] = "items.category = ?";
            $types .= 's';
            $params[] = $category;
        }

        $where_clause = "WHERE " . implode(" AND ", $where_conditions);

        $sql = "
            SELECT
                items.itemID,
                items.itemName,
                items.location,
                items.created_at,
                items.jenis_laporan,
                items.photo,
                users.nama_lengkap,
                users.no_telp
            FROM items
            JOIN users ON items.userID = users.userID
            $where_clause
            ORDER BY items.created_at DESC
        ";

        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            $q = false;
        } else {
            if ($types !== '') {
                $bind_params = [$stmt, $types];
                foreach ($params as $i => $value) {
                    $bind_params[] = &$params[$i];
                }
                call_user_func_array('mysqli_stmt_bind_param', $bind_params);
            }

            mysqli_stmt_execute($stmt);
            $q = mysqli_stmt_get_result($stmt);

            if ($q === false) {
                $rows = [];
                mysqli_stmt_bind_result(
                    $stmt,
                    $itemID,
                    $itemName,
                    $location,
                    $created_at,
                    $jenis_laporan,
                    $photo,
                    $nama_lengkap,
                    $no_telp
                );

                while (mysqli_stmt_fetch($stmt)) {
                    $rows[] = [
                        'itemID' => $itemID,
                        'itemName' => $itemName,
                        'location' => $location,
                        'created_at' => $created_at,
                        'jenis_laporan' => $jenis_laporan,
                        'photo' => $photo,
                        'nama_lengkap' => $nama_lengkap,
                        'no_telp' => $no_telp,
                    ];
                }
            }
        }

        $result_count = 0;
        if (isset($rows)) {
            $result_count = count($rows);
        } elseif ($q) {
            $result_count = mysqli_num_rows($q);
        }
        
        if($result_count > 0) {
        ?>
        <div class="search-results-header">
            <div class="search-results-info">
                <span class="search-results-count"><?= $result_count; ?> Hasil</span>
                <span class="search-results-text">
                    <?php 
                    if($keyword && $category) {
                        echo "untuk '" . htmlspecialchars($keyword) . "' di kategori '" . ucfirst($category) . "'";
                    } elseif($keyword) {
                        echo "untuk '" . htmlspecialchars($keyword) . "'";
                    } elseif($category) {
                        echo "di kategori '" . ucfirst($category) . "'";
                    } else {
                        echo "Semua barang";
                    }
                    ?>
                </span>
            </div>
            <div class="search-results-actions">
                <select class="sort-dropdown">
                    <option>Terbaru</option>
                    <option>Terlama</option>
                    <option>Nama A-Z</option>
                    <option>Nama Z-A</option>
                </select>
                <div class="view-toggle">
                    <button class="active"><i class="fas fa-th"></i></button>
                    <button><i class="fas fa-list"></i></button>
                </div>
            </div>
        </div>
        
        <div class="grid">
                <?php if (isset($rows)): ?>
                <?php foreach ($rows as $row): ?>
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
                <?php endforeach; ?>
                <?php else: ?>
                <?php while($row=mysqli_fetch_assoc($q)): ?>
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
                <?php endwhile; ?>
                <?php endif; ?>
        </div>
            <?php
            } else {
                echo '<div class="no-results"><p>Barang yang dicari tidak ditemukan.</p></div>';
            }

            if (isset($stmt) && $stmt) {
                mysqli_stmt_close($stmt);
            }
            ?>
        </div>
    <?php else: ?>
        <!-- Tampilkan search form saja saat tidak ada pencarian -->
        <div class="search-section">
            <div class="search-form">
                <form method="GET">
                    <div class="search-input-group">
                        <div class="search-input-wrapper">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" name="q" placeholder="Masukkan nama barang atau deskripsi..." value="<?= htmlspecialchars($keyword); ?>" class="search-input">
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
                        <button type="submit" class="btn btn-primary search-btn">
                            <i class="fas fa-search"></i> Cari Barang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
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
</script>

</body>
</html>