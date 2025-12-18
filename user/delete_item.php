<?php
session_start();
include "../config/database.php";

$id = $_GET['id'];
$userID = $_SESSION['user']['userID'];

mysqli_query($conn, "
    DELETE FROM items
    WHERE itemID='$id' AND userID='$userID' AND status='draft'
");

header("Location: laporan.php");
