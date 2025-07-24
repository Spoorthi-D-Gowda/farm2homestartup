<?php
session_start();

if (!isset($_SESSION['phone'])) {
    // Redirect to login if not logged in
    header("Location: login.php?message=Please+login+first");
    exit;
}

$phone = $_SESSION['phone'];
$conn = new mysqli("localhost", "root", "", "productdb");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$successMessage = "";

// Update email and address if form submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $newEmail = $_POST["email"];
    $newAddress = $_POST["address"];

    $stmt = $conn->prepare("UPDATE users SET email = ?, address = ? WHERE phone = ?");
    $stmt->bind_param("sss", $newEmail, $newAddress, $phone);
    if ($stmt->execute()) {
        $successMessage = "Profile updated successfully!";
    }
    $stmt->close();
}

// Fetch updated user data
$stmt = $conn->prepare("SELECT * FROM users WHERE phone = ?");
$stmt->bind_param("s", $phone);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Customer Profile</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(to right, #f7f8fc, #e0eafc);
            margin: 0;
            padding: 20px;
        }
        .close-btn {
    position: absolute;
    top: 20px;
    right: 25px;
    font-size: 28px;
    text-decoration: none;
    color: #666;
    font-weight: bold;
    z-index: 999;
}
.close-btn:hover {
    color: #000;
}

        .profile-container {
            max-width: 500px;
            background: white;
            margin: auto;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .profile-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .profile-header img {
            border-radius: 50%;
            width: 110px;
            height: 110px;
            object-fit: cover;
            margin-bottom: 10px;
        }
        .profile-header h2 {
            margin: 0;
            font-size: 24px;
            color: #333;
        }
        .profile-header p {
            color: #777;
            font-size: 14px;
        }
        .success-msg {
            background-color: #d4edda;
            border: 1px solid #b6dfc4;
            color: #3c763d;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 15px;
            text-align: center;
        }
        .profile-info label {
            display: block;
            font-weight: bold;
            margin-top: 15px;
            color: #444;
        }
        .profile-info p,
        .profile-info input,
        .profile-info textarea {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            background: #f5f5f5;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-sizing: border-box;
        }
        .profile-info input:focus,
        .profile-info textarea:focus {
            background: #fff;
            border-color: #1e90ff;
            outline: none;
        }
        .update-btn, .logout-btn {
            display: block;
            width: 100%;
            margin-top: 25px;
            padding: 12px;
            background-color: #1e90ff;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s ease;
            text-align: center;
        }
        .update-btn:hover {
            background-color: #0c74d4;
        }
        .logout-btn {
            background-color: #dc3545;
            margin-top: 10px;
        }
        .logout-btn:hover {
            background-color: #bb2d3b;
        }
        @media (max-width: 600px) {
            .profile-container {
                padding: 20px;
            }
            .profile-header h2 {
                font-size: 20px;
            }
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
</head>
<body>

<div class="profile-container">
    <div class="profile-header">
        <!-- Close button -->
<a href="javascript:history.back()" class="close-btn" title="Close Profile">&times;</a>

        <img src="https://cdn-icons-png.flaticon.com/512/149/149071.png" alt="Profile Picture">
        <h2><?php echo htmlspecialchars($user['name']); ?></h2>
        <p><?php echo htmlspecialchars($user['role']); ?></p>
    </div>

    <?php if ($successMessage): ?>
        <div class="success-msg"><?php echo $successMessage; ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="profile-info">
            <label>Phone:</label>
            <p><?php echo htmlspecialchars($user['phone']); ?></p>

            <label>Email:</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">

            <label>Address:</label>
            <textarea name="address"><?php echo htmlspecialchars($user['address']); ?></textarea>

            <button type="submit" class="update-btn">Update Profile</button>
        </div>
    </form>

    <form action="logout.php" method="post">
        <button class="logout-btn">Logout</button>
    </form>
</div>

</body>
</html>
