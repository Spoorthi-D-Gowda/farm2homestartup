<?php
session_start();
header("Content-Type: application/json");
ob_clean();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "productdb";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "DB connection failed."]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $role = $_POST['role'] ?? '';
    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $plain_password = $_POST['password'] ?? '';
    $entered_otp = $_POST['otp'] ?? '';

    if ($_SESSION['otp'] != $entered_otp || $_SESSION['otp_phone'] != $phone) {
        echo json_encode(["status" => "error", "message" => "Incorrect OTP."]);
        exit();
    }

    if (!preg_match('/^\d{10}$/', $phone)) {
        echo json_encode(["status" => "error", "message" => "Phone number must be 10 digits."]);
        exit();
    }

    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@#$%^&+=!]).{8,}$/', $plain_password)) {
        echo json_encode(["status" => "error", "message" => "Weak password."]);
        exit();
    }

    if ($role === "Admin" && $plain_password !== "SST=farm2home") {
        echo json_encode(["status" => "error", "message" => "Incorrect admin password."]);
        exit();
    }

    $hashed_password = password_hash($plain_password, PASSWORD_BCRYPT);

    // Duplicate phone check
    $check = $conn->prepare("SELECT id FROM users WHERE phone = ?");
    $check->bind_param("s", $phone);
    $check->execute();
    $res = $check->get_result();
    if ($res->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "Phone already registered!"]);
        exit();
    }

    // Insert user
    $stmt = $conn->prepare("INSERT INTO users (role, name, phone, password, is_verified) VALUES (?, ?, ?, ?, 1)");
    $stmt->bind_param("ssss", $role, $name, $phone, $hashed_password);
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Registration complete!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Insert failed."]);
    }

    $stmt->close();
    $conn->close();
}
?>
