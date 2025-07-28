<?php
session_start();
header("Content-Type: application/json");
ob_clean();
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "productdb";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check DB connection
if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Database connection failed."]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $role = $_POST['role'] ?? '';
    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $plain_password = $_POST['password'] ?? '';

    // Basic validations
    if (!preg_match('/^\d{10}$/', $phone)) {
        echo json_encode(["status" => "error", "message" => "Phone number must be exactly 10 digits."]);
        $conn->close();
        exit();
    }

    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@#$%^&+=!]).{8,}$/', $plain_password)) {
        echo json_encode(["status" => "error", "message" => "Password must be at least 8 characters and include uppercase, lowercase, number, and special character."]);
        $conn->close();
        exit();
    }

    $defaultAdminPassword = "SST=farm2home";

    // If Admin, password must match the default
    if ($role === "Admin" && $plain_password !== $defaultAdminPassword) {
        echo json_encode(["status" => "error", "message" => "Enter correct password."]);
        $conn->close();
        exit();
    }

    $hashed_password = password_hash($plain_password, PASSWORD_BCRYPT);

    // Check for duplicate phone
    $checkUser = $conn->prepare("SELECT id FROM users WHERE phone = ?");
    $checkUser->bind_param("s", $phone);
    $checkUser->execute();
    $result = $checkUser->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "Phone number already registered!"]);
        $checkUser->close();
        $conn->close();
        exit();
    }
    $checkUser->close();

    // Insert new user
    $insert = $conn->prepare("INSERT INTO users (role, name, phone, password) VALUES (?, ?, ?, ?)");
    $insert->bind_param("ssss", $role, $name, $phone, $hashed_password);

    if ($insert->execute()) {
        echo json_encode(["status" => "success", "message" => "Registration successful!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Registration failed. Please try again."]);
    }

    $insert->close();
    $conn->close();
}
?>