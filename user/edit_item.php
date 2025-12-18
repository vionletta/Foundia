<?php
session_start();
include "../config/database.php";

if (!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit;
}

$id = $_GET['id'];
$item = mysqli_fetch_assoc(mysqli_query($conn,"
    SELECT * FROM items WHERE itemID='$id' AND userID='".$_SESSION['user']['userID']."'
"));

if (!$item) {
    header("Location: laporan.php");
    exit;
}

if (isset($_POST['update'])) {
    $jenis   = $_POST['jenis'];
    $category = $_POST['category'];
    $nama    = $_POST['itemName'];
    $desc    = $_POST['description'];
    $lokasi  = $_POST['location'];
    $telp    = $_POST['phone'];
    $tanggal = $_POST['dateLost'];

    // Upload foto baru jika ada
    $foto = $item['photo']; // Keep existing photo
    if (!empty($_FILES['photo']['name'])) {
        // Delete old photo if exists
        if ($item['photo'] && file_exists("../assets/images/" . $item['photo'])) {
            unlink("../assets/images/" . $item['photo']);
        }
        
        $foto = time() . "_" . $_FILES['photo']['name'];
        move_uploaded_file(
            $_FILES['photo']['tmp_name'],
            "../assets/images/" . $foto
        );
    }

    mysqli_query($conn, "UPDATE items SET
        jenis_laporan='$jenis',
        category='$category',
        itemName='$nama',
        phone='$telp',
        location='$lokasi',
        dateLost='$tanggal',
        description='$desc',
        photo='$foto'
        WHERE itemID='$id'");

    header("Location: laporan.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Laporan Barang</title>
    <link rel="icon" href="assets/css/icon.png" type="image/png" sizes="192x192">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body{background:#f6f7fb;}
        .edit-page-wrap{max-width:860px;margin:0 auto;padding:28px 16px 60px;}
        .edit-card{background:#fff;border-radius:14px;box-shadow:0 10px 30px rgba(0,0,0,0.08);padding:28px;}
        .edit-header{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:18px;}
        .back-btn{display:inline-flex;align-items:center;gap:10px;text-decoration:none;font-weight:700;color:#6c5ce7;background:rgba(108,92,231,0.10);padding:10px 12px;border-radius:12px;}
        .back-btn:hover{background:rgba(108,92,231,0.16);}
        .edit-title{margin:0;font-size:1.6em;font-weight:700;color:#333;}
        .edit-subtitle{margin:6px 0 0 0;color:#666;}

        .edit-form{background:transparent;padding:0;border-radius:0;box-shadow:none;max-width:none;margin:0;}
        .edit-form .form-group{margin-bottom:18px;}
        .edit-form label{display:block;margin-bottom:8px;font-weight:700;color:#444;}
        .edit-form input[type="text"],
        .edit-form input[type="date"],
        .edit-form select,
        .edit-form textarea{width:100%;padding:12px 14px;border:1px solid #e5e7eb;border-radius:12px;box-sizing:border-box;font-size:14px;background:#fff;transition:box-shadow .15s ease,border-color .15s ease;}
        .edit-form textarea{min-height:110px;resize:vertical;}
        .edit-form input[type="text"]:focus,
        .edit-form input[type="date"]:focus,
        .edit-form select:focus,
        .edit-form textarea:focus{outline:none;border-color:rgba(108,92,231,0.55);box-shadow:0 0 0 4px rgba(108,92,231,0.14);}

        .radio-inline{display:flex;gap:14px;flex-wrap:wrap;}
        .radio-option{display:inline-flex;align-items:center;gap:8px;padding:10px 12px;border:1px solid #e5e7eb;border-radius:12px;background:#fff;cursor:pointer;user-select:none;}
        .radio-option input{margin:0;}

        .edit-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:20px;flex-wrap:wrap;}
        .edit-actions .btn{padding:12px 18px;border-radius:12px;font-size:0.9rem;min-width:120px;flex:1 1 auto;text-align:center;}
        .btn-save{background:#6c5ce7;color:#fff;border:none;cursor:pointer;font-weight:800;box-shadow:0 10px 22px rgba(108,92,231,0.20);}
        .btn-save:hover{background:#5b4bd6;box-shadow:0 14px 28px rgba(108,92,231,0.26);}
        .btn-save:active{transform:translateY(0);}
        .btn-secondary{background:#fff;border:1px solid #e5e7eb;color:#333;cursor:pointer;font-weight:800;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;}
        .btn-secondary:hover{background:#f9fafb;}

        @media (max-width:600px){
            .edit-card{padding:20px;}
            .edit-header{flex-direction:column;}
            .edit-actions{justify-content:stretch;flex-direction:column;}
            .edit-actions .btn{width:100%;justify-content:center;min-width:auto;}
        }
    </style>
</head>
<body>

<div class="edit-page-wrap">
    <div class="edit-card">
        <div class="edit-header">
            <div>
                <h2 class="edit-title">Edit Laporan Barang</h2>
                <p class="edit-subtitle">Perbarui informasi barang yang kamu laporkan.</p>
            </div>
            <a class="back-btn" href="laporan.php">Kembali</a>
        </div>

        <form method="POST" enctype="multipart/form-data" class="edit-form">

            <div class="form-group">
                <label>Jenis Laporan</label>
                <div class="radio-inline">
                    <label class="radio-option">
                        <input type="radio" name="jenis" value="hilang" <?= $item['jenis_laporan'] == 'hilang' ? 'checked' : ''; ?> required>
                        <span>Barang Hilang</span>
                    </label>
                    <label class="radio-option">
                        <input type="radio" name="jenis" value="temuan" <?= $item['jenis_laporan'] == 'temuan' ? 'checked' : ''; ?>>
                        <span>Barang Temuan</span>
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label>Kategori Barang</label>
                <select name="category" required>
                    <option value="">Pilih Kategori</option>
                    <option value="elektronik" <?= (isset($item['category']) && $item['category'] == 'elektronik') ? 'selected' : ''; ?>>Elektronik</option>
                    <option value="dompet" <?= (isset($item['category']) && $item['category'] == 'dompet') ? 'selected' : ''; ?>>Dompet</option>
                    <option value="kunci" <?= (isset($item['category']) && $item['category'] == 'kunci') ? 'selected' : ''; ?>>Kunci</option>
                    <option value="hp" <?= (isset($item['category']) && $item['category'] == 'hp') ? 'selected' : ''; ?>>Handphone</option>
                    <option value="tas" <?= (isset($item['category']) && $item['category'] == 'tas') ? 'selected' : ''; ?>>Tas</option>
                    <option value="lainnya" <?= (isset($item['category']) && $item['category'] == 'lainnya') ? 'selected' : ''; ?>>Lainnya</option>
                </select>
            </div>

            <div class="form-group">
                <label>Nama Barang</label>
                <input type="text" name="itemName" placeholder="Nama Barang" value="<?= htmlspecialchars($item['itemName']); ?>" required>
            </div>

            <div class="form-group">
                <label>Nomor Telepon (WhatsApp)</label>
                <input type="text" name="phone" placeholder="Nomor Telepon (WhatsApp)" value="<?= htmlspecialchars($_SESSION['user']['no_telp']); ?>" readonly>
                <small class="form-hint">Nomor telepon sesuai dengan data registrasi Anda.</small>
            </div>

            <div class="form-group">
                <label>Lokasi Kehilangan / Temuan</label>
                <input type="text" name="location" placeholder="Lokasi Kehilangan / Temuan" value="<?= htmlspecialchars($item['location']); ?>" required>
            </div>

            <div class="form-group">
                <label>Tanggal</label>
                <input type="date" name="dateLost" value="<?= $item['dateLost']; ?>" required>
            </div>

            <div class="form-group">
                <label>Deskripsi</label>
                <textarea name="description" placeholder="Deskripsi barang"><?= htmlspecialchars($item['description']); ?></textarea>
            </div>

            <?php if ($item['photo']): ?>
                <div class="form-group">
                    <label>Foto Saat Ini</label>
                    <div class="current-photo">
                        <img src="../assets/images/<?= $item['photo']; ?>" alt="Current photo">
                    </div>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label>Ubah Foto (kosongkan jika tidak ingin mengubah)</label>
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

            <div class="edit-actions">
                <a class="btn btn-secondary" href="laporan.php">Batal</a>
                <button type="submit" name="update" class="btn btn-save">
                    <i class="fas fa-save"></i> Update Laporan
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
