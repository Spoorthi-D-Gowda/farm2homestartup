<?php
session_start();

if (!isset($_SESSION['phone'])) {
    // Redirect to login if not logged in
    header("Location: login.php?message=Please+login+first");
    exit;
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "productdb";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the user's phone number from the session
$user_phone = $_SESSION['phone'];

// Fetch user details from the users table based on phone number
$user_sql = "SELECT name, phone FROM users WHERE phone = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("s", $user_phone);
$user_stmt->execute();
$user_result = $user_stmt->get_result();

if ($user_result->num_rows === 0) {
    echo "User not found.";
    exit;
}

$user = $user_result->fetch_assoc();
$fullname = $user['name'];
$phone = $user['phone'];

// Get product name from URL
if (!isset($_GET['product_name'])) {
    echo "No product selected.";
    exit;
}

$product_name = trim($_GET['product_name']);

// Fetch product details
// Fetch product details including seller
$product_sql = "SELECT * FROM products WHERE name = ?";
$product_stmt = $conn->prepare($product_sql);
$product_stmt->bind_param("s", $product_name);
$product_stmt->execute();
$product_result = $product_stmt->get_result();

if ($product_result->num_rows === 0) {
    echo "Product not found.";
    exit;
}

$product = $product_result->fetch_assoc();
$seller = $product['seller']; // <-- Get the seller


// Handle order submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $address = trim($_POST['address']);
    $quantity = intval($_POST['quantity']);
    $payment = $_POST['payment'];

    // Validate quantity
    if ($quantity <= 0) {
        $error = "Please enter a valid quantity.";
    } elseif ($quantity > $product['quantity']) {
        $error = "Only " . $product['quantity'] . " units available.";
    } else {
        // Calculate total amount
        $total_amount = $product['price'] * $quantity;

        // Insert into orders table
        $order_sql = "INSERT INTO orders (product_name, fullname, phone, address, quantity, total_amount, payment, seller) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $order_stmt = $conn->prepare($order_sql);
        $order_stmt->bind_param("ssssiiss", $product_name, $fullname, $phone, $address, $quantity, $total_amount, $payment, $seller);
        $order_stmt->execute();

        // Update product quantity
        $new_quantity = $product['quantity'] - $quantity;
        $update_sql = "UPDATE products SET quantity = ? WHERE name = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("is", $new_quantity, $product_name);
        $update_stmt->execute();

        $success = "Order placed successfully!";
        $product['quantity'] = $new_quantity;
    }
}


$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Order Product</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 500px;
            background: white;
            margin: auto;
            padding: 30px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            border-radius: 10px;
        }
        h2 {
            text-align: center;
            color: #232f3e;
        }
        .product-details {
            margin-bottom: 20px;
        }
        .product-details p {
            margin: 5px 0;
        }
        form label {
            display: block;
            margin-top: 10px;
        }
        form input[type="text"],
        form input[type="number"],
        form textarea {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        form input[type="submit"] {
            background-color: #f0c14b;
            border: 1px solid #a88734;
            color: black;
            padding: 10px;
            width: 100%;
            margin-top: 20px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
        form input[type="submit"]:hover {
            background-color: #e6b800;
        }
        .message {
            text-align: center;
            margin-top: 15px;
            font-weight: bold;
        }
        .error {
            color: red;
        }
        .success {
            color: green;
        }
        .back-button {
            display: block;
            text-align: center;
            margin-top: 20px;
            text-decoration: none;
            color: #232f3e;
            font-weight: bold;
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
<script>
function fillAddress() {
    const status = document.getElementById("location-status");
    const editable = document.getElementById("address");

    if (!navigator.geolocation) {
        status.textContent = "Geolocation not supported.";
        return;
    }

    status.textContent = "Detecting location...";

    navigator.geolocation.getCurrentPosition(success, error);

    function success(pos) {
        const lat = pos.coords.latitude;
        const lon = pos.coords.longitude;

        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}&addressdetails=1`)
            .then(response => response.json())
            .then(data => {
                const addr = data.address;
                const road = addr.road || "";
                const neighbourhood = addr.neighbourhood || addr.suburb || "";
                const landmark = addr.building || addr.university || "";

                const result = `${landmark}, ${road}, ${neighbourhood}`.replace(/(, )+/g, ", ").trim().replace(/^,|,$/g, "");
                editable.value = result;
                status.textContent = "Please review and complete your address.";
            })
            .catch(() => {
                status.textContent = "Could not fetch address. Try again.";
            });
    }

    function error() {
        status.textContent = "Permission denied or location unavailable.";
    }
}
</script>



<body>

<div class="container">
    <h2>Order Product</h2>

    <div class="product-details">
        <p><strong>Product Name:</strong> <?php echo htmlspecialchars($product['name']); ?></p>
        <p><strong>Category:</strong> <?php echo htmlspecialchars($product['category']); ?></p>
        <p><strong>Price:</strong> ₹<?php echo htmlspecialchars($product['price']); ?></p>
        <p><strong>Available Quantity:</strong> <?php echo htmlspecialchars($product['quantity']); ?></p>
    </div>

    <?php
    if (isset($error)) {
        echo '<div class="message error">' . htmlspecialchars($error) . '</div>';
    } elseif (isset($success)) {
        echo '<div class="message success">' . htmlspecialchars($success) . '</div>';
    }
    ?>

    <form method="post">
        <label for="fullname">Full Name:</label>
        <input type="text" name="fullname" id="fullname" value="<?php echo htmlspecialchars($fullname); ?>" readonly>

        <label for="phone">Phone Number:</label>
        <input type="text" name="phone" id="phone" value="<?php echo htmlspecialchars($phone); ?>" readonly>

        <label for="address">Address:</label>
<textarea name="address" id="address" rows="3" placeholder="e.g. Door No 12, Street Name, Near Landmark" required></textarea>

<!-- Fixed Hassan, Karnataka, Pincode -->
<input type="text" name="city_state_pin" value="Hassan, Karnataka, 573201" readonly style="margin-top:5px; background-color:#f1f1f1; border:none; font-weight:bold;">
<button type="button" onclick="fillAddress()">Use Current Location</button>
<span id="location-status" style="font-size:13px; color:#666;"></span>
 <label for="quantity">Quantity:</label>
        <input type="number" name="quantity" id="quantity" min="1" max="<?php echo htmlspecialchars($product['quantity']); ?>" required>

        <label for="payment">Payment Method:</label>
        <select name="payment" required>
            <option value="" disabled selected>Select Payment Method</option>
            <option value="Cash on Delivery">Pay on Delivery</option>
        </select>

        <input type="submit" value="Place Order">
    </form>

    <a class="back-button" href="product.php">← Back to Products</a>
</div>

</body>
</html>
