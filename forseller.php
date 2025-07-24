<?php
// Predefined password
$predefined_password = "seller1234";

// Database connection
$conn = new mysqli("localhost", "root", "", "productdb");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$login_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($password !== $predefined_password) {
        $login_message = "❌ Login failed: Incorrect password.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM sellers WHERE name = ? AND phone = ?");
        $stmt->bind_param("ss", $name, $phone);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            session_start();
            $_SESSION['seller_name'] = $name;
            header("Location: selleruse.php");
            exit();
        } else {
            $login_message = "❌ Login failed: Seller not found.";
        }

        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Seller Login</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f0f4f8;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .login-box {
            background-color: #fff;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        h2 {
            text-align: center;
            color: #333;
        }
        label {
            display: block;
            margin: 12px 0 6px;
            color: #555;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
        }
        input[type="submit"] {
            margin-top: 20px;
            width: 100%;
            padding: 12px;
            background-color: #28a745;
            border: none;
            color: white;
            font-size: 16px;
            border-radius: 8px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #218838;
        }
        .message {
            margin-top: 15px;
            text-align: center;
            font-weight: bold;
            color: #d9534f;
        }
        .message.success {
            color: #28a745;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Seller Login</h2>

        <?php if (!empty($login_message)) : ?>
            <div class="message <?= strpos($login_message, 'successful') !== false ? 'success' : '' ?>">
                <?= $login_message ?>
            </div>
        <?php endif; ?>

        <form action="forseller.php" method="POST">
            <label for="name">Name</label>
            <input type="text" name="name" required>

            <label for="phone">Phone Number</label>
            <input type="text" name="phone" required>

            <label for="password">Password</label>
            <input type="password" name="password" required>

            <input type="submit" value="Login">
        </form>
    </div>
</body>
</html>
