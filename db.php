<?php
$conn = new mysqli("localhost", "root", "", "productdb");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
