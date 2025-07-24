<?php
session_start();


if (isset($_SESSION['name'])) {
    $name = $_SESSION['name'];
} else {
    $name = "Admin"; // default fallback
}
?>

<?php
// Database connection
$servername = "localhost";
$username = "root"; // default XAMPP username
$password = ""; // default XAMPP password
$dbname = "productdb"; // make sure to create this database

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch products from database
$sql = "SELECT * FROM products";
$result = $conn->query($sql);

// Organize products by category
$productsByCategory = [
    "Milk Products" => [],
    "Vegetables" => [],
    "Fruits" => [],
    "Flowers" => []
];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        if (array_key_exists($row['category'], $productsByCategory)) {
            $productsByCategory[$row['category']][] = $row;
        }
    }
}
$search = "";
if (isset($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
    $sql = "SELECT * FROM products WHERE name LIKE '%$search%'";
} else {
    $sql = "SELECT * FROM products";
}

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta charset="UTF-8">
    <title>Our Products</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        form {
    margin-top: 20px;
}

input[type="text"] {
    width: 300px;
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 4px;
}

button[type="submit"] {
    padding: 8px 16px;
    background-color: #f0c14b;
    border: 1px solid #a88734;
    border-radius: 4px;
    cursor: pointer;
    font-weight: bold;
}

button[type="submit"]:hover {
    background-color: #e6b800;
}

        body {
            background-color:white;
            font-family: Arial, sans-serif;
        }



        h2 {
            
            padding-bottom: 5px;
            color: #333;
            margin-top: 40px;
        }

        .card {
            border: none;
            transition: transform 0.3s, box-shadow 0.3s;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .card-title {
            font-size: 1.1rem;
            color: #0f1111;
            font-weight: bold;
        }

        .card-text {
            color: #555;
        }

        .price {
            color: #B12704;
            font-weight: bold;
            font-size: 1.1rem;
        }

        .quantity {
            font-size: 0.9rem;
        }

        .btn-primary {
            background-color: #ff9900;
            border: none;
        }

        .btn-primary:hover {
            background-color: #e68a00;
        }

        .out-of-stock {
            color: #B12704;
            font-weight: bold;
        }

        footer {
            margin-top: 50px;
            padding: 20px;
            background-color: #232f3e;
            color: white;
            text-align: center;
        }
        header {
            background-color: #2d3e50;
            color: white;
            padding: 30px 25px;
            display: flex;
            justify-content: space-between;
          
            flex-wrap: wrap;
            gap: 15px;
        }
        header h2 {
            margin: 0;
            color: white;
        }
        .topnav {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .topnav a {
            color: white;
            text-decoration: none;
            font-weight: bold;
        }
        .search-bar {
            flex: 1;
            display: flex;
            justify-content: center;
        }
        .search-bar input {
            width: 80%;
            padding: 8px;
            border-radius: 20px 0 0 20px;
            border: none;
            outline: none;
            font-size: 16px;
        }
        .search-bar button {
            padding: 8px 16px;
            background-color: #ffa500;
            border: none;
            border-radius: 0 20px 20px 0;
            cursor: pointer;
            font-weight: bold;
        }
        .search-bar button:hover {
            background-color: #ff8c00;
        }
        .row {
    background-color:rgba(213, 238, 129, 0.1);
    border-radius: 8px;
    padding: 20px;
    margin: 20px auto;
    max-width: 1200px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    display: flex;
    flex-wrap: wrap;
    justify-content: left;
}
.container my-5{
    background-color:white;
    
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
    <h2>Welcome, <?php echo htmlspecialchars($name); ?></h2>

    <div class="search-bar">
    <form action="search.php" method="get" style="display: flex;">
    <input type="text" name="search" placeholder="Search products...">
    <button type="submit">Search</button>
</form>

    </div>

    <div class="topnav">
        <a href="index2.php">Home</a>
        <a href="your-order.php">Your Order</a>
        <a href="queries.html">Queries</a>
        <a href="offers.php">Offers</a>
        <a href="view-cart.php">View Cart 🛒</a>

    </div>
</header>


<!-- Main container -->
<div class="container my-5">
    <h1 class="mb-4 text-center">Our Products 🛒</h1>

    <?php foreach ($productsByCategory as $category => $products): ?>
        <h2><?= htmlspecialchars($category) ?></h2>
        <div class="row">
            <?php if (count($products) > 0): ?>
                <?php foreach ($products as $product): ?>
                    <div class="col-md-3 mb-4"style="padding:10px; font-style:sans-serif;">
                        <div class="card h-100" >
                            <img src="<?= htmlspecialchars($product['image_url']) ?>" class="card-img-top" alt="<?= htmlspecialchars($product['name']) ?>" style="height:200px; object-fit:cover;">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
                                <p class="card-text"><?= htmlspecialchars($product['description']) ?></p>
                                <p class="price">₹<?= number_format($product['price'], 2) ?></p>
                                <p class="quantity <?= $product['quantity'] > 0 ? 'text-success' : 'out-of-stock' ?>">
                                    <?= $product['quantity'] > 0 ? 'In Stock: ' . $product['quantity'] : 'Out of Stock' ?>
                                </p>
                                <a href="<?= $product['quantity'] > 0 ? 'order.php?product_name=' . urlencode($product['name']) : '#' ?>" 
   class="btn btn-primary mt-auto <?= $product['quantity'] <= 0 ? 'disabled' : '' ?>" 
   <?= $product['quantity'] <= 0 ? 'onclick="return false;"' : '' ?>>
   Buy Now
</a>
<form method="post" action="cart.php" class="mt-2">
    <input type="hidden" name="product_id" value="<?= htmlspecialchars($product['id']) ?>">
    <button type="submit" class="btn btn-success w-100" <?= $product['quantity'] <= 0 ? 'disabled' : '' ?>>
        Add to Cart
    </button>
</form>


                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-muted">No products available in this category.</p>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>

<footer>
    &copy; <?= date('Y') ?> Milk & Vegetable Delivery. All rights reserved.
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

<?php
$conn->close();
?>
