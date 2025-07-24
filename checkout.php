<?php
session_start();

if (!isset($_SESSION['phone'])) {
    header("Location: login.php?message=Please+login+first");
    exit;
}

$conn = new mysqli("localhost", "root", "", "productdb");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_phone = $_SESSION['phone'];

// Fetch user details
$user_stmt = $conn->prepare("SELECT name, address FROM users WHERE phone = ?");
$user_stmt->bind_param("s", $user_phone);
$user_stmt->execute();
$user_result = $user_stmt->get_result();

if ($user_result->num_rows === 0) {
    die("User not found.");
}
$user = $user_result->fetch_assoc();
$fullname = $user['name'];
$address = $user['address'] ?? '';

$cartItems = [];
$subtotal = 0;
$shipping = 0;
$tax = 0;
$grandTotal = 0;
$orderPlaced = false;

if (!empty($_SESSION['cart'])) {
    // Filter numeric keys only
    $ids = array_filter(array_keys($_SESSION['cart']), 'is_numeric');
    if (!empty($ids)) {
        // Convert all to integers for safety
        $ids = array_map('intval', $ids);
        $ids_list = implode(',', $ids);

        $sql = "SELECT * FROM products WHERE id IN ($ids_list)";
        $result = $conn->query($sql);

        if ($result === false) {
            die("Database query failed: " . $conn->error);
        }

        while ($row = $result->fetch_assoc()) {
            $id = $row['id'];
            $qty = $_SESSION['cart'][$id];
            $row['cart_quantity'] = $qty;
            $row['line_total'] = $qty * $row['price'];
            $subtotal += $row['line_total'];
            $cartItems[] = $row;
        }
    }
}

// Handle order form
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $address = trim($_POST['address']);
    $payment = $_POST['payment'];

    // Save updated address in DB
    $update_stmt = $conn->prepare("UPDATE users SET address = ? WHERE phone = ?");
    $update_stmt->bind_param("ss", $address, $user_phone);
    $update_stmt->execute();

    // Place order (optional: insert to orders table)
    $orderPlaced = true;
    $_SESSION['cart'] = [];
}
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $address = trim($_POST['address']);
    $payment = $_POST['payment'];

    // Update user's address
    $update_stmt = $conn->prepare("UPDATE users SET address = ? WHERE phone = ?");
    $update_stmt->bind_param("ss", $address, $user_phone);
    $update_stmt->execute();

    // Insert orders and update product stock
    foreach ($cartItems as $item) {
    $product_id = $item['id'];
    $product_name = $item['name'];
    $quantity = $item['cart_quantity'];
    $price = $item['price'];
    $total_amount = $price * $quantity;

    $seller_stmt = $conn->prepare("SELECT seller FROM products WHERE id = ?");
    $seller_stmt->bind_param("i", $product_id);
    $seller_stmt->execute();
    $seller_result = $seller_stmt->get_result();
    $seller_row = $seller_result->fetch_assoc();
    $seller = $seller_row['seller'] ?? 'Unknown';

    $order_date = date("Y-m-d H:i:s");
    $payment_status = "Amount to be Paid";
    $delivery_status = "Pending";

    $order_stmt = $conn->prepare("INSERT INTO orders (product_name, fullname, address, quantity, total_amount, order_date, phone, payment, delivery_status, payment_status, seller) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $order_stmt->bind_param("sssidsissss", $product_name, $fullname, $address, $quantity, $total_amount, $order_date, $user_phone, $payment, $delivery_status, $payment_status, $seller);
    $order_stmt->execute();

    $stock_update_stmt = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");
    $stock_update_stmt->bind_param("ii", $quantity, $product_id);
    $stock_update_stmt->execute();
}


    // Clear cart
    $_SESSION['cart'] = [];
    $orderPlaced = true;
}

?>

<!DOCTYPE html>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f3f3f3; font-family: Arial, sans-serif; }
        .checkout-container {
            max-width: 1000px;
            margin: 40px auto;
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .section-title {
            font-size: 1.5rem;
            font-weight: bold;
            border-bottom: 2px solid #ddd;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .price { color: #B12704; font-weight: bold; }
        .btn-place-order {
            background-color: #ffd814;
            border: 1px solid #fcd200;
            font-weight: bold;
            color: #111;
        }
        .btn-place-order:hover { background-color: #f7ca00; }
        .thank-you {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="checkout-container">
    <div class="section-title">Checkout</div>

```
<?php if ($orderPlaced): ?>
    <div class="thank-you">
        Thank you <strong><?= htmlspecialchars($fullname) ?></strong>, your order has been placed!<br>
        <a href="product.php">Continue shopping</a>
    </div>
<?php elseif (empty($cartItems)): ?>
    <p>Your cart is empty. <a href="product.php">Shop now</a></p>
<?php else: ?>
    <div class="row">
        <div class="col-md-7">
            <form method="post" class="order-form">
                <h5 class="mb-3">Shipping Information</h5>

                <div class="mb-3">
                    <label for="fullname" class="form-label">Full Name</label>
                    <input type="text" class="form-control" id="fullname" value="<?= htmlspecialchars($fullname) ?>" disabled>
                </div>

                <div class="mb-3">
                    <label for="address" class="form-label">Address</label>
                    <textarea class="form-control" id="address" name="address" rows="2" required><?= htmlspecialchars($address) ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="payment" class="form-label">Payment Method</label>
                    <select class="form-select" id="payment" name="payment" required>
                        <option value="">Select</option>
                        <option value="cod">Cash on Delivery</option>
                        <option value="card">Credit/Debit Card</option>
                        <option value="upi">UPI</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-place-order w-100 mt-3">Place Your Order</button>
            </form>
             <a class="back-button" href="view-cart.php">← Back to cart</a>
        </div>

        <div class="col-md-5 cart-summary">
            <h5 class="mb-3">Order Summary</h5>
            <ul class="list-group">
                <?php foreach ($cartItems as $item): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <?= htmlspecialchars($item['name']) ?> × <?= $item['cart_quantity'] ?>
                        <span class="price">₹<?= number_format($item['line_total'], 2) ?></span>
                    </li>
                <?php endforeach; ?>
                <li class="list-group-item d-flex justify-content-between">
                    Shipping
                    <span>₹<?= number_format($shipping, 2) ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    Tax (18%)
                    <span>₹<?= number_format($tax, 2) ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    Total
                    <span>₹<?= number_format($subtotal, 2) ?></span>
                </li>
            </ul>
        </div>
    </div>
<?php endif; ?>
```

</div>

</body>
</html> 
