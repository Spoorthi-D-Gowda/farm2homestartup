<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

echo "<h1>Welcome, " . $_SESSION['name'] . "!</h1>";
echo "<p>Role: " . $_SESSION['role'] . "</p>";
echo '<a href="logout.php">Logout</a>';
?>
