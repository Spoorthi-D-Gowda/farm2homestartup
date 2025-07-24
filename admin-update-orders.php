<?php
$conn = new mysqli("localhost", "root", "", "productdb");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission to update statuses
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update'], $_POST['id'], $_POST['field'], $_POST['value'])) {
    $id = intval($_POST['id']);
    $field = $_POST['field'];
    $value = $_POST['value'];
    $amount = number_format(floatval($_POST['amount']), 2);

    $allowed_fields = ['delivery_status', 'payment_status'];
    if (!in_array($field, $allowed_fields)) {
        die("Invalid field name.");
    }

    if ($field === "payment_status") {
        $value .= " (₹$amount)";
    }

    $sql = "UPDATE orders SET $field = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("SQL prepare error: " . $conn->error);
    }

    $stmt->bind_param("si", $value, $id);
    $stmt->execute();
}



// Fetch yesterday's orders
$yesterday = date('Y-m-d', strtotime('-1 day'));
$sql = "SELECT * FROM orders WHERE DATE(order_date) = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $yesterday);
$stmt->execute();
$result = $stmt->get_result();

// Helper to get price
function getProductPrice($conn, $productName) {
    $stmt = $conn->prepare("SELECT price FROM products WHERE name = ?");
    $stmt->bind_param("s", $productName);
    $stmt->execute();
    $result = $stmt->get_result();
    return ($row = $result->fetch_assoc()) ? $row['price'] : 0;
}


?>

<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin - Update Orders</title>
    <style>
        body { font-family: Arial; background: #f7f7f7; padding: 20px; }
        table { width: 100%; border-collapse: collapse; background: white; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: center; }
        h2 { text-align: center; }
        form { display: inline; }
        button { padding: 6px 12px; margin: 2px; border: none; cursor: pointer; border-radius: 4px; }
        .yes { background: #4CAF50; color: white; }
        .no { background: #f44336; color: white; }
        .paid { background: #2196F3; color: white; }
        .due { background: orange; color: black; }
    </style>
</head>
<body>

<h2>Today's Deliveries (Orders from Yesterday)</h2>

<table>
    <tr>
    <th>Order ID</th>

        <th>Product</th>
        <th>Name</th>
        <th>Phone</th>
        <th>Quantity</th>
        <th>Price per unit</th>
        <th>Amount</th>
        <th>Delivery Status</th>
        <th>Payment Status</th>
        <th>Update Delivery</th>
        <th>Update Payment</th>
    </tr>

    <?php while ($row = $result->fetch_assoc()) {
        $price = getProductPrice($conn, $row['product_name']);
        $amount = $price * $row['quantity'];
    ?>
    <tr>
     <td><?php echo $row['id']; ?></td>

        
        <td><?php echo $row['product_name']; ?></td>
        <td><?php echo $row['fullname']; ?></td>
        <td><?php echo $row['phone']; ?></td>
        <td><?php echo $row['quantity']; ?></td>
        <?php
    $price = getProductPrice($conn, $row['product_name']);
?>
<td>₹<?php echo number_format($price, 2); ?></td>

        <td>₹<?php echo number_format($amount, 2); ?></td>
        <td><?php echo $row['delivery_status']; ?></td>
        <td><?php echo $row['payment_status']; ?></td>

        <!-- Delivery Buttons -->
        <td>
            <form method="post">
            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">

            <input type="hidden" name="field" value="delivery_status">

                <input type="hidden" name="amount" value="<?php echo $amount; ?>">
                <input type="hidden" name="update" value="1">
                <button class="yes" name="value" value="Delivered">Yes</button>
                <button class="no" name="value" value="To Be Delivered">No</button>
            </form>
        </td>

        <!-- Payment Buttons -->
        <td>
            <form method="post">
            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">

                <input type="hidden" name="field" value="payment_status">
                <input type="hidden" name="amount" value="<?php echo $amount; ?>">
                <input type="hidden" name="update" value="1">
                <button class="paid" name="value" value="Amount Paid">Paid</button>
                <button class="due" name="value" value="Amount to be Paid">To be Paid</button>
            </form>
        </td>
    </tr>
    <?php } ?>
</table>
<a class="back-button" href="adminuse.php">← Back</a>
</body>
</html>
