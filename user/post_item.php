<?php
session_start();
include "../config/database.php";

if (!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit;
}

$userID = $_SESSION['user']['userID'];
$itemID = $_POST['itemID'];

mysqli_query($conn, "
    UPDATE items 
    SET status = 'posted'
    WHERE itemID = '$itemID'
    AND userID = '$userID'
");

header("Location: laporan.php");
exit;
