<?php
// DB connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "productdb";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sellers = [];
$sql = "SELECT name, address, products, description, photo FROM sellers";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $sellers[] = $row;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Sellers</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            background-color: #f9f9f9;
            color: #333;
        }

        header {
    background: #2C3E50;
    color: white;
    padding: 15px;
    text-align: center;
}

nav ul {
    list-style: none;
    padding: 0;
}

nav ul li {
    display: inline;
    margin: 0 15px;
}

nav ul li a {
    color: white;
    text-decoration: none;
}
       

        .seller-container {
            padding: 30px;
        }

        .seller-list {
            display: flex;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
        }

        .seller-card {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            transition: transform 0.2s ease;
            width: 30%;
            height: 500px;
            
        }

        .seller-card:hover {
            transform: translateY(-5px);
        }

        .seller-card img {
            width: 80%;
            height: 300px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 15px;
        }

        .seller-card h3 {
            margin: 0;
            color: #2c3e50;
        }

        .seller-card p {
            margin: 5px 0;
        }

        footer {
            background-color: #2c3e50;
            color: white;
            text-align: center;
            padding: 15px;
            margin-top: 30px;
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
<header>
    <nav>
        <h1>Sellers Information</h1>
        <ul>
            <li><a href="index2.php">Home</a></li>
            <li><a href="product.php">Products</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>
</header>

<main class="seller-container">
    <h2>Meet Our Sellers</h2>
    <div id="seller-list" class="seller-list">
        <?php foreach ($sellers as $seller): ?>
            <div class="seller-card">
                <img src="<?= htmlspecialchars($seller['photo']) ?>" alt="Seller Photo" />
                <h3><?= htmlspecialchars($seller['name']) ?></h3>
                <p><strong>Address:</strong> <?= htmlspecialchars($seller['address']) ?></p>
                <p><strong>Products:</strong> <?= htmlspecialchars($seller['products']) ?></p>
                <p><strong>Description:</strong> <?= htmlspecialchars($seller['description']) ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</main>

<footer>
    <p>&copy; 2025 Milk & Vegetable Delivery</p>
</footer>
</body>
</html>
