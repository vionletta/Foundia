<?php
session_start();
include "../config/database.php";

/* PROTEKSI ADMIN */
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Content-Type: application/json");
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (isset($_GET['id'])) {
    $itemID = $_GET['id'];
    
    $query = mysqli_query($conn, "
        SELECT items.*, users.nama_lengkap, users.no_telp
        FROM items
        JOIN users ON items.userID = users.userID
        WHERE items.itemID = $itemID
    ");
    
    if ($query && mysqli_num_rows($query) > 0) {
        $item = mysqli_fetch_assoc($query);
        
        // Generate HTML like detail_item.php but for admin
        $html = '<div class="modal-item-detail">
            <div class="modal-header">
                <div class="item-title-section">
                    <h2 class="item-title">' . htmlspecialchars($item['itemName']) . '</h2>
                    <span class="status-badge ' . ($item['jenis_laporan']=='hilang'?'status-lost':'status-found') . '">
                        ' . ucfirst($item['jenis_laporan']) . '
                    </span>
                </div>
                <span class="close-modal" onclick="closeModal()">&times;</span>
            </div>
            
            <div class="modal-body">
                <div class="item-image-section">
                    <div class="image-container">';
                    
        if(isset($item['photo']) && $item['photo']) {
            $html .= '<img src="../assets/images/' . $item['photo'] . '" class="item-image" onerror="this.style.display=\'none\'; this.parentElement.style.display=\'none\';" alt="' . htmlspecialchars($item['itemName']) . '">';
        } else {
            $html .= '<div class="no-image-placeholder">
                <i class="fas fa-image"></i>
                <span>Tidak ada foto</span>
            </div>';
        }
        
        $html .= '</div>
                </div>
                
                <div class="item-info-section">
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-map-marker-alt"></i>
                                Lokasi
                            </div>
                            <div class="info-value">' . htmlspecialchars($item['location']) . '</div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-calendar-alt"></i>
                                Tanggal ' . ($item['jenis_laporan']=='hilang'?'Kehilangan':'Penemuan') . '
                            </div>
                            <div class="info-value">' . date('d F Y', strtotime($item['dateLost'])) . '</div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-user"></i>
                                Pelapor
                            </div>
                            <div class="info-value">' . htmlspecialchars($item['nama_lengkap']) . '</div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-tag"></i>
                                Kategori
                            </div>
                            <div class="info-value">' . htmlspecialchars($item['category']) . '</div>
                        </div>
                    </div>
                    
                    <div class="description-section">
                        <div class="info-label">
                            <i class="fas fa-info-circle"></i>
                            Deskripsi
                        </div>
                        <div class="description-text">' . htmlspecialchars($item['description']) . '</div>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <div class="action-buttons">
                    <button class="btn ' . (($item['is_hidden'] ?? 0) == 1 ? 'btn-success' : 'btn-warning') . ' btn-toggle" onclick="toggleVisibility(' . $item['itemID'] . ', ' . ($item['is_hidden'] ?? 0) . ')">
                        <i class="fas ' . (($item['is_hidden'] ?? 0) == 1 ? 'fa-eye' : 'fa-eye-slash') . '"></i>
                        ' . (($item['is_hidden'] ?? 0) == 1 ? 'Tampilkan' : 'Sembunyikan') . '
                    </button>
                </div>
            </div>
        </div>';
        
        header("Content-Type: application/json");
        echo json_encode(['success' => true, 'html' => $html]);
    } else {
        header("Content-Type: application/json");
        echo json_encode(['success' => false, 'message' => 'Item not found']);
    }
} else {
    header("Content-Type: application/json");
    echo json_encode(['success' => false, 'message' => 'Item ID not provided']);
}
?>
