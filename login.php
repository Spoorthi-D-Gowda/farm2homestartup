<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = new mysqli("localhost", "root", "", "productdb");

    if ($conn->connect_error) {
        echo json_encode(["status" => "error", "message" => "Database connection failed."]);
        exit;
    }

    // Forgot Password Logic
    if (isset($_POST["forgot"])) {
        $name = $_POST["name"];
        $phone = $_POST["phone"];

        $stmt = $conn->prepare("SELECT * FROM users WHERE name = ? AND phone = ?");
        $stmt->bind_param("ss", $name, $phone);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            // Found user, redirect to reset password page
            echo json_encode([
                "reset" => true,
                "message" => "User found! Redirecting to reset password.",
                "redirect" => "reset-password.php?phone=" . urlencode($phone)
            ]);
        } else {
            echo json_encode([
                "reset" => false,
                "message" => "User not found. Please check your details."
            ]);
        }

        $stmt->close();
        $conn->close();
        exit;
    }

    // Login Logic
    $phone = $_POST["phone"];
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT * FROM users WHERE phone = ?");
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user["password"])) {
            session_start();
            $_SESSION['phone'] = $user["phone"];
            $_SESSION['name'] = $user["name"];
            $_SESSION['role'] = $user["role"];

            echo json_encode([
                "status" => "success",
                "message" => "Login successful!",
                "name" => $user["name"],
                "role" => $user["role"]
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Incorrect password."
            ]);
        }
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "User not found."
        ]);
    }

    $stmt->close();
    $conn->close();
    exit;
}
?>

<!-- HTML + JS below -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - Milk & Veg</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f2f7f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .card {
            background: white;
            padding: 2rem 2.5rem;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            width: 350px;
        }
        .card h2 {
            margin-bottom: 1rem;
            text-align: center;
        }
        .card form input {
            width: 100%;
            margin: 10px 0;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }
        .card button {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            background-color: #1e90ff;
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }
        .card button:hover {
            background-color: #156fd2;
        }
        .toggle-link {
            display: block;
            text-align: center;
            margin-top: 12px;
            font-size: 0.9rem;
            color: blue;
            cursor: pointer;
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
        function loginUser(event) {
            event.preventDefault();
            const phone = document.getElementById("phone").value;
            const password = document.getElementById("password").value;

            const formData = new FormData();
            formData.append("phone", phone);
            formData.append("password", password);

            fetch("login.php", {
                method: "POST",
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                alert(data.message);
                if (data.status === "success") {
                    localStorage.setItem("isLoggedIn", true);
                    localStorage.setItem("customerName", data.name);
                    localStorage.setItem("role", data.role); // optional
                    window.location.href = "product.php";
                }
            });
        }

        function forgotPassword(event) {
            event.preventDefault();
            const name = document.getElementById("fp-name").value;
            const phone = document.getElementById("fp-phone").value;

            const formData = new FormData();
            formData.append("name", name);
            formData.append("phone", phone);
            formData.append("forgot", "1");

            fetch("login.php", {
                method: "POST",
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                alert(data.message);
                if (data.reset) {
                    window.location.href = data.redirect;
                }
            });
        }

        function toggleForm(showForgot) {
            document.getElementById("login-form").style.display = showForgot ? "none" : "block";
            document.getElementById("forgot-form").style.display = showForgot ? "block" : "none";
        }
    </script>
</head>
<body>
<?php if (isset($_GET['message'])): ?>
    <div style="background-color: #ffe6e6; color: #a33; padding: 10px; text-align: center; border-radius: 8px; margin-bottom: 10px;">
        <?php echo htmlspecialchars($_GET['message']); ?>
    </div>
<?php endif; ?>

<div class="card">
    <!-- Login Form -->
    <div id="login-form">
        <h2>Login</h2>
        <form onsubmit="loginUser(event)">
            <input type="tel" id="phone" name="phone" placeholder="Phone Number" pattern="[0-9]{10}" required>
            <input type="password" id="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <span class="toggle-link" onclick="toggleForm(true)">Forgot password?</span>

         <a class="toggle-link" href="index2.php"> Back to Home</a>
    </div>

    <!-- Forgot Password Form -->
    <div id="forgot-form" style="display: none;">
        <h2>Forgot Password</h2>
        <form onsubmit="forgotPassword(event)">
            <input type="text" id="fp-name" name="name" placeholder="Enter your name" required>
            <input type="tel" id="fp-phone" name="phone" placeholder="Enter phone number" pattern="[0-9]{10}" required>
            <button type="submit">Recover Password</button>
        </form>
        <span class="toggle-link" onclick="toggleForm(false)">Back to Login</span>
    </div>
</div>
</body>
</html>
