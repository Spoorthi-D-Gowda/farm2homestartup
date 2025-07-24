<?php
session_start();
if (!isset($_SESSION['phone'])) {
    echo "<script>alert('Please login first.'); window.location.href = 'login.php';</script>";
    exit();
}
?>
