<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "productdb";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$search = '';
if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
}

// Prepare query
if (!empty($search)) {
    $sql = "SELECT * FROM products WHERE name LIKE ?";
    $stmt = $conn->prepare($sql);
    $param = '%' . $search . '%';
    $stmt->bind_param('s', $param);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    header("Location: product.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Search Results</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            margin: 0;
            padding: 20px;
        }
        header {
            background-color: #232f3e;
            padding: 20px;
            color: white;
            text-align: center;
        }
        .products {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
        }
        .product-card {
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 5px;
            width: 220px;
            margin: 15px;
            padding: 15px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .product-card:hover {
            transform: scale(1.05);
        }
        .product-card h3 {
            margin-top: 0;
        }
        .product-card p {
            margin: 5px 0;
        }
        .back-button {
            display: block;
            margin: 20px auto;
            padding: 10px 20px;
            background-color:white;
            border-radius: 4px;
           
            text-align: center;
            text-decoration: none;
            color: black;
            font-weight: bold;
        }
        .back-button:hover {
            background-color: #e6b800;
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
    <h1>Search Results for "<?php echo htmlspecialchars($search); ?>"</h1>
</header>

<div class="products">
<?php
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
    echo '<div class="product-card">';
    echo '<h3>' . htmlspecialchars($row['name']) . '</h3>';
    echo '<img src="' . htmlspecialchars($row['image_url']) . '" alt="' . htmlspecialchars($row['name']) . '" style="height:200px; object-fit:cover; width:100%; margin-bottom:10px;">';

    echo '<p>Category: ' . htmlspecialchars($row['category']) . '</p>';
    echo '<p>Price: ₹' . htmlspecialchars($row['price']) . '</p>';
    echo '<p>Quantity: ' . htmlspecialchars($row['quantity']) . '</p>';
    
    // Add to Cart Button
    // Add to Cart Button with Quantity Input
echo '<form action="cart.php" method="post" style="margin-top: 10px;">';
echo '<input type="hidden" name="product_id" value="' . htmlspecialchars($row['id']) . '">';

echo '<button type="submit" style="padding:8px 12px; background-color:#f0c14b; border:1px solid #a88734; border-radius:3px; cursor:pointer;">Add to Cart</button>';
echo '</form>';


    // Buy Now Button
   echo '<form action="order.php" method="get" style="margin-top: 5px;">';
echo '<input type="hidden" name="product_name" value="' . htmlspecialchars($row['name']) . '">';
echo '<button type="submit" style="padding:8px 12px; background-color:#ffa41c; border:1px solid #a88734; border-radius:3px; cursor:pointer;">Buy Now</button>';
echo '</form>';

    echo '</div>';
}

} else {
    echo '<p style="text-align: center; width: 100%;">No products found.</p>';
}

if (isset($stmt)) {
    $stmt->close();
}
$conn->close();
?>
</div>

<a href="product.php" class="back-button">View All</a>

</body>
</html>
