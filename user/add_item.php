<?php
session_start();
include "../config/database.php";

if (!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit;
}

if (isset($_POST['submit'])) {
    $jenis   = $_POST['jenis'];
    $category = $_POST['category'];
    $nama    = $_POST['itemName'];
    $desc    = $_POST['description'];
    $lokasi  = $_POST['location'];
    $telp    = $_POST['phone'];
    $tanggal = $_POST['dateLost'];
    $userID  = $_SESSION['user']['userID'];

    // Upload foto
    $foto = "";
    if (!empty($_FILES['photo']['name'])) {
        $foto = time() . "_" . $_FILES['photo']['name'];
        move_uploaded_file(
            $_FILES['photo']['tmp_name'],
            "../assets/images/" . $foto
        );
    }

    mysqli_query($conn, "INSERT INTO items
        (jenis_laporan, category, itemName, phone, location, dateLost, description, photo, userID, status)
        VALUES
        ('$jenis','$category','$nama','$telp','$lokasi','$tanggal','$desc','$foto','$userID', 'draft')");

    header("Location: laporan.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Buat Laporan</title>
    <link rel="icon" href="../favicon.ico" type="image/x-icon">
    <link rel="icon" href="../favicon-32x32.png" type="image/png" sizes="32x32">
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

<div class="content report-form-page">
    <div class="report-form-card">
        <div class="report-form-header">
            <h1 class="report-form-title">Buat Laporan</h1>
        </div>

        <form method="POST" enctype="multipart/form-data" class="report-form">
            <div class="form-group">
                <label>Jenis Laporan</label>
                <div class="radio-inline">
                    <label class="radio-item">
                        <input type="radio" name="jenis" value="hilang" required>
                        <span>Barang Hilang</span>
                    </label>
                    <label class="radio-item">
                        <input type="radio" name="jenis" value="temuan">
                        <span>Barang Temuan</span>
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label>Kategori Barang</label>
                <select name="category" required>
                    <option value="">Pilih Kategori</option>
                    <option value="elektronik">Elektronik</option>
                    <option value="dompet">Dompet</option>
                    <option value="kunci">Kunci</option>
                    <option value="hp">Handphone</option>
                    <option value="tas">Tas</option>
                    <option value="lainnya">Lainnya</option>
                </select>
            </div>

            <div class="form-group">
                <label>Nama Barang</label>
                <input type="text" name="itemName" placeholder="Contoh: Dompet Kulit Hitam" required>
            </div>

            <div class="form-group">
                <label>Nomor Telfon</label>
                <input type="text" name="phone" placeholder="+62xxxxxxxxxxx" required>
            </div>

            <div class="form-group">
                <label>Lokasi Kehilangan/Temuan</label>
                <input type="text" name="location" placeholder="Contoh: Lab Komputer Gedung A" required>
            </div>

            <div class="form-group">
                <label>Tanggal Kehilangan/Temuan</label>
                <input type="date" name="dateLost" required>
            </div>

            <div class="form-group">
                <label>Deskripsi Barang</label>
                <textarea name="description" placeholder="Deskripsikan ciri-ciri barang secara detail..."></textarea>
            </div>

            <div class="form-group">
                <label>Unggah Foto Barang</label>
                <div class="file-upload-wrapper">
                    <div class="file-upload-area" id="fileUploadArea">
                        <div class="file-upload-content">
                            <i class="fas fa-cloud-upload-alt file-upload-icon"></i>
                            <p class="file-upload-text">Klik atau seret file ke area ini untuk mengunggah</p>
                            <p class="file-upload-subtext">PNG, JPG, JPEG hingga 10MB</p>
                        </div>
                        <input type="file" name="photo" class="file-input" id="fileInput" accept="image/*">
                    </div>
                    <div class="file-preview" id="filePreview" style="display: none;">
                        <img src="" alt="Preview" class="file-preview-image">
                        <button type="button" class="file-remove-btn" id="fileRemoveBtn">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="report-form-actions">
                <button type="submit" name="submit" class="btn btn-primary report-submit-btn">
                    <i class="fas fa-paper-plane"></i> Kirim Laporan
                </button>
            </div>
        </form>
    </div>
</div>

</body>
</html>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileUploadArea = document.getElementById('fileUploadArea');
    const fileInput = document.getElementById('fileInput');
    const filePreview = document.getElementById('filePreview');
    const filePreviewImage = document.querySelector('.file-preview-image');
    const fileRemoveBtn = document.getElementById('fileRemoveBtn');

    // Click to upload
    fileUploadArea.addEventListener('click', function() {
        fileInput.click();
    });

    // Drag and drop
    fileUploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        fileUploadArea.classList.add('drag-over');
    });

    fileUploadArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        fileUploadArea.classList.remove('drag-over');
    });

    fileUploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        fileUploadArea.classList.remove('drag-over');
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            fileInput.files = files;
            handleFileSelect(files[0]);
        }
    });

    // File input change
    fileInput.addEventListener('change', function(e) {
        if (e.target.files.length > 0) {
            handleFileSelect(e.target.files[0]);
        }
    });

    // Remove file
    fileRemoveBtn.addEventListener('click', function() {
        fileInput.value = '';
        filePreview.style.display = 'none';
        fileUploadArea.style.display = 'block';
    });

    function handleFileSelect(file) {
        // Check file type
        if (!file.type.startsWith('image/')) {
            alert('Harap unggah file gambar (PNG, JPG, JPEG)');
            return;
        }

        // Check file size (10MB)
        if (file.size > 10 * 1024 * 1024) {
            alert('Ukuran file tidak boleh lebih dari 10MB');
            return;
        }

        // Show preview
        const reader = new FileReader();
        reader.onload = function(e) {
            filePreviewImage.src = e.target.result;
            filePreview.style.display = 'block';
            fileUploadArea.style.display = 'none';
        };
        reader.readAsDataURL(file);
    }
});
</script>
