<?php
session_start();


if (isset($_SESSION['name'])) {
    $name = $_SESSION['name'];
} else {
    $name = "Admin"; // default fallback
}
?>
<?php
// your-order.php


if (!isset($_SESSION['phone'])) {
    // Redirect to login if not logged in
    header("Location: login.php?message=Please+login+first");
    exit;
}



// ✅ Add this line to get the phone number from the session:
$phone = $_SESSION['phone'];

// Get logged-in user's name
$loggedInUser = $_SESSION['phone'];

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "productdb";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch product prices
$product_prices = [];
$price_sql = "SELECT name, price FROM products";
$price_result = $conn->query($price_sql);
if ($price_result->num_rows > 0) {
    while ($row = $price_result->fetch_assoc()) {
        $product_prices[$row['name']] = $row['price'];
    }
}

// Filter orders (optional: filter by date for their own orders)
$filter_date = isset($_GET['order_date']) ? $_GET['order_date'] : '';

$whereClauses = [];
$whereClauses[] = "phone = '" . $conn->real_escape_string($loggedInUser) . "'";

if (!empty($filter_date)) {
    $whereClauses[] = "DATE(order_date) = '" . $conn->real_escape_string($filter_date) . "'";
}

$whereSQL = 'WHERE ' . implode(' AND ', $whereClauses);

$sql = "SELECT * FROM orders $whereSQL ORDER BY order_date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta charset="UTF-8">
    <title>Your Orders</title>
    <style>
        /* Your existing styles (kept intact) */
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
        .container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            max-width: 1000px;
            margin: 30px auto;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        form {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: center;
        }
        input[type="date"],
        button {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            background-color: #232f3e;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #1a2533;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
        }
        th {
            background-color: #232f3e;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .status-tomorrow {
            color: green;
            font-weight: bold;
        }
        .status-today {
            color: orange;
            font-weight: bold;
        }
        .total-amount {
            text-align: right;
            font-size: 18px;
            font-weight: bold;
            margin-top: 10px;
        }
        .back-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #f0c14b;
            border: 1px solid #a88734;
            border-radius: 4px;
            text-decoration: none;
            color: black;
            font-weight: bold;
            margin-top: 10px;
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
    <h1>Your Orders Summary</h1>
</header>

<div class="container">
    <h2>Orders List for <?php echo htmlspecialchars($name); ?></h2></h2>

    <!-- Filter form -->
    <form method="get">
        <input type="date" name="order_date" value="<?= htmlspecialchars($filter_date) ?>">
        <button type="submit">Filter Orders</button>
        <a href="your-order.php"><button type="button">Reset</button></a>
    </form>

    <?php
    if ($result->num_rows > 0) {
        echo '<table>';
        echo '<tr><th>Product Name</th><th>Full Name</th><th>Address</th><th>Quantity</th><th>Order Date</th><th>Delivery Status</th><th>Amount (₹)</th>><th>Phone Number</th><th>Payment Status</th></tr>';

        $total_amount = 0;

        while ($row = $result->fetch_assoc()) {
            $orderDate = new DateTime($row['order_date']);
            $today = new DateTime();
            $yesterday = (clone $today)->modify('-1 day');
            
           
            

            $product_name = $row['product_name'];
            $quantity = $row['quantity'];
            $price = isset($product_prices[$product_name]) ? $product_prices[$product_name] : 0;
            $amount = $price * $quantity;
            $total_amount += $amount;

            echo '<tr>';
            echo '<td>' . htmlspecialchars($product_name) . '</td>';
            echo '<td>' . htmlspecialchars($row['fullname']) . '</td>';
            echo '<td>' . htmlspecialchars($row['address']) . '</td>';
            echo '<td>' . htmlspecialchars($quantity) . '</td>';
            echo '<td>' . htmlspecialchars($row['order_date']) . '</td>';
            echo '<td>' . htmlspecialchars($row['delivery_status']) . '</td>';
            echo '<td>₹' . $amount . '</td>';
            echo '<td>' . htmlspecialchars($row['phone']) . '</td>';
            echo '<td>' . htmlspecialchars($row['payment_status']) . '</td>';
            echo '</tr>';

        }

        echo '</table>';
       
    } else {
        echo '<p>No orders found.</p>';
    }

    $conn->close();
    ?>

    <a href="product.php" class="back-button">Back to Products</a>
</div>

</body>
</html>
