<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Connect to DB
$conn = new mysqli("localhost", "root", "", "productdb");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch offers
$offers_result = $conn->query("SELECT title, description FROM offers ORDER BY created_at DESC");

if (!$offers_result) {
    die("Query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exclusive Offers</title>
    <style>
        /* Reset Default Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f9f9f9;
        }

        /* Header */
        header {
            background: #2C3E50;
            color: white;
            padding: 20px;
            text-align: center;
        }

        nav ul {
            list-style: none;
            text-align: center;
            margin-top: 10px;
        }

        nav ul li {
            display: inline;
            margin: 0 15px;
        }

        nav ul li a {
            color: white;
            text-decoration: none;
            font-weight: bold;
        }

        /* Banner */
        .offer-banner {
            text-align: center;
            margin: 20px 0;
        }

        .offer-banner img {
            width: 90%;
            max-height: 300px;
            border-radius: 10px;
            box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.2);
        }

        /* Offers Section */
        .offers-container {
            text-align: center;
            padding: 30px;
        }

        h2 {
            font-size: 26px;
            margin-bottom: 20px;
        }

        /* Offers Grid */
        .offers-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            padding: 20px;
        }

        /* Offer Card */
        .offer-card {
            background: white;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.3s;
        }

        .offer-card:hover {
            transform: scale(1.05);
        }

        .offer-card img {
            width: 100%;
            border-radius: 8px;
        }

        .offer-info {
            padding: 10px;
        }

        .offer-info h3 {
            font-size: 18px;
            margin: 10px 0;
        }

        .offer-info p {
            font-size: 14px;
            color: orangered;
            margin-bottom: 10px;
        }

        .price {
            font-size: 18px;
            color: #e67e22;
            font-weight: bold;
        }

        del {
            color: #999;
            margin-left: 10px;
        }

        /* Buy Now Button */
        button {
            background: #ff5722;
            color: white;
            border: none;
            padding: 10px 15px;
            cursor: pointer;
            border-radius: 5px;
            font-size: 16px;
            margin-top: 10px;
        }

        button:hover {
            background: #e64a19;
        }

        /* Footer */
        footer {
            background: #2C3E50;
            color: white;
            text-align: center;
            padding: 15px;
            margin-top: 300px;
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
            <h1>Super Deals & Offers</h1>
            <ul>
                <li><a href="index2.php">Home</a></li>
                <li><a href="product.php">Products</a></li>
                <li><a href="queries.html">Queries</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <!-- Offers Section -->
    <main class="offers-container">
        <h2>Today's Best Deals</h2>
        <div class="offers-grid">
            <?php if ($offers_result->num_rows > 0): ?>
                <?php while ($offer = $offers_result->fetch_assoc()): ?>
                    <div class="offer-card">
                        <div class="offer-info">
                            <h3><?= htmlspecialchars($offer['title']) ?></h3>
                            <p><?= htmlspecialchars($offer['description']) ?></p>

                                
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No offers available at the moment.</p>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <p>&copy; 2025 Milk & Vegetable Delivery | Best Deals & Discounts</p>
    </footer>
</body>
</html>

<?php $conn->close(); ?>
