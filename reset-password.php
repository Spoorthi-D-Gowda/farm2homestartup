<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $phone = trim($_POST["phone"]);
    $new_password_raw = $_POST["new_password"];

    if (empty($phone)) {
        echo json_encode(["status" => "error", "message" => "Phone number is required."]);
        exit;
    }

    if (strlen($new_password_raw) < 6) {
        echo json_encode(["status" => "error", "message" => "Password must be at least 6 characters long."]);
        exit;
    }

    $new_password = password_hash($new_password_raw, PASSWORD_DEFAULT);

    $conn = new mysqli("localhost", "root", "", "productdb");
    if ($conn->connect_error) {
        die("Database connection failed.");
    }

    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE phone = ?");
    $stmt->bind_param("ss", $new_password, $phone);
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Password updated successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to update password."]);
    }

    $stmt->close();
    $conn->close();
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Reset Password</title>
    <style>
        body {
            background-color: #f0f4f8;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        form {
            background-color: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            width: 300px;
            text-align: center;
        }

        input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 14px;
        }

        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 12px 20px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            width: 100%;
        }

        button:hover {
            background-color: #45a049;
        }

        h2 {
            margin-bottom: 20px;
            color: #333;
        }
        /* Small devices (phones) */
@media (max-width: 600px) {
  body {
    font-size: 14px;
  }
  .nav, .sidebar {
    display: none;
  }
}

/* Medium devices (tablets) */
@media (min-width: 601px) and (max-width: 1024px) {
  .container {
    flex-direction: column;
  }
}

/* Large devices (laptops and desktops) */
@media (min-width: 1025px) {
  /* Optional desktop-specific styling */
}


    </style>
    <script>
        function resetPassword(event) {
            event.preventDefault();
            const phone = new URLSearchParams(window.location.search).get("phone");
            const newPassword = document.getElementById("new-password").value;

            if (!phone) {
                alert("Phone number is missing in the URL.");
                return;
            }

            const formData = new FormData();
            formData.append("phone", phone);
            formData.append("new_password", newPassword);

            fetch("reset-password.php", {
                method: "POST",
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                alert(data.message);
                if (data.status === "success") {
                    window.location.href = "login.php";
                }
            });
        }
    </script>
</head>
<body>
    <form onsubmit="resetPassword(event)">
        <h2>Reset Your Password</h2>
        <input type="password" id="new-password" placeholder="Enter new password" required>
        <button type="submit">Reset Password</button>
    </form>
</body>
</html>
