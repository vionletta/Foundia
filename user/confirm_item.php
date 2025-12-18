<?php
session_start();
include "../config/database.php";

if (!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit;
}

$itemID = $_POST['itemID'] ?? null;
$userID = $_SESSION['user']['userID'];

if ($itemID !== null) {
    $itemID = (int)$itemID;
    $userID = (int)$userID;
    mysqli_query(
        $conn,
        "UPDATE items SET status='selesai' WHERE itemID=$itemID AND userID=$userID"
    );
}

header("Location: laporan.php");
exit;
