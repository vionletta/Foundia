<?php
session_start();
include "../config/database.php";

/* PROTEKSI ADMIN */
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Content-Type: application/json");
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (isset($_POST['itemID'])) {
    $itemID = (int)$_POST['itemID']; // Sanitize input
    
    // Debug: Log the request
    error_log("Toggle visibility request for itemID: " . $itemID);
    
    // Get current is_hidden status
    $query = mysqli_query($conn, "SELECT is_hidden FROM items WHERE itemID = $itemID");
    
    if ($query && mysqli_num_rows($query) > 0) {
        $item = mysqli_fetch_assoc($query);
        $currentStatus = $item['is_hidden'] ?? 0; // Handle NULL as 0 (visible)
        $newStatus = $currentStatus == 1 ? 0 : 1; // Toggle status
        
        // Debug: Log the status change
        error_log("Current status: " . $currentStatus . ", New status: " . $newStatus);
        
        // Update the is_hidden status
        $updateQuery = mysqli_query($conn, "UPDATE items SET is_hidden = $newStatus WHERE itemID = $itemID");
        
        if ($updateQuery) {
            $message = $newStatus == 1 ? 'Item berhasil disembunyikan' : 'Item berhasil ditampilkan';
            header("Content-Type: application/json");
            echo json_encode([
                'success' => true, 
                'message' => $message,
                'is_hidden' => $newStatus
            ]);
        } else {
            // Debug: Log the SQL error
            error_log("SQL Error: " . mysqli_error($conn));
            header("Content-Type: application/json");
            echo json_encode(['success' => false, 'message' => 'Gagal mengupdate visibility item: ' . mysqli_error($conn)]);
        }
    } else {
        // Debug: Log the query error
        error_log("Query Error: " . mysqli_error($conn));
        header("Content-Type: application/json");
        echo json_encode(['success' => false, 'message' => 'Item not found']);
    }
} else {
    header("Content-Type: application/json");
    echo json_encode(['success' => false, 'message' => 'Item ID not provided']);
}
?>
