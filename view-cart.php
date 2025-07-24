<?php
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "productdb");

// Handle item removal
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['remove_id'])) {
    $removeId = intval($_POST['remove_id']);
    if (isset($_SESSION['cart'][$removeId])) {
        unset($_SESSION['cart'][$removeId]);
    }
}

$cartItems = [];
$total = 0;

if (!empty($_SESSION['cart'])) {
    // Filter keys to only integers
    $ids = array_filter(array_keys($_SESSION['cart']), 'is_numeric');
    if (!empty($ids)) {
        // Sanitize and prepare list for SQL IN clause
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
            $row['subtotal'] = $row['price'] * $qty;
            $total += $row['subtotal'];
            $cartItems[] = $row;
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shopping Cart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f3f3f3;
            font-family: Arial, sans-serif;
        }
        .cart-container {
            max-width: 900px;
            margin: 40px auto;
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .cart-title {
            font-size: 1.8rem;
            margin-bottom: 20px;
            font-weight: bold;
            border-bottom: 2px solid #ddd;
            padding-bottom: 10px;
        }
        .cart-item {
            display: flex;
            gap: 20px;
            padding: 15px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        .cart-item img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 5px;
        }
        .cart-details {
            flex-grow: 1;
        }
        .cart-details h5 {
            margin-bottom: 10px;
        }
        .price {
            color: #B12704;
            font-size: 1.1rem;
            font-weight: bold;
        }
        .subtotal {
            font-size: 1rem;
            font-weight: bold;
        }
        .total {
            font-size: 1.3rem;
            color: #B12704;
            font-weight: bold;
        }
        .btn-checkout {
            background-color: #ffd814;
            border-color: #fcd200;
            color: #111;
            font-weight: bold;
        }
        .btn-checkout:hover {
            background-color: #f7ca00;
        }
        .btn-remove {
            color: #d00;
            background: none;
            border: none;
            font-weight: bold;
            cursor: pointer;
        }
    </style>
</head>
<body>

<div class="cart-container">
    <div class="cart-title">Your Shopping Cart</div>

    <?php if (empty($cartItems)): ?>
        <p>Your cart is empty.</p>
        <a href="product.php" class="btn btn-primary mt-3">Browse Products</a>
    <?php else: ?>
        <?php foreach ($cartItems as $item): ?>
            <div class="cart-item">
                <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                <div class="cart-details">
                    <h5><?= htmlspecialchars($item['name']) ?></h5>
                    <p><?= htmlspecialchars($item['description']) ?></p>
                    <p class="price">₹<?= number_format($item['price'], 2) ?></p>
                    <p class="subtotal">Quantity: <?= $item['cart_quantity'] ?> |
                        Subtotal: ₹<?= number_format($item['subtotal'], 2) ?></p>
                </div>
                <form method="post">
                    <input type="hidden" name="remove_id" value="<?= $item['id'] ?>">
                    <button type="submit" class="btn-remove">Remove</button>
                </form>
            </div>
        <?php endforeach; ?>

        <div class="text-end mt-4">
            <p class="total">Total: ₹<?= number_format($total, 2) ?></p>
            <a href="checkout.php" class="btn btn-checkout">Proceed to Checkout</a>
        </div>
    <?php endif; ?>
     <a class="back-button" href="product.php">← Back to Products</a>
</div>

</body>
</html>
