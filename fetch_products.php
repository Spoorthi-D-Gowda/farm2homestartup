<?php
session_start();
include 'db.php'; // Ensure this defines $conn as your DB connection

$category = $_GET['category'] ?? 'All';

if ($category === 'All') {
    $stmt = $conn->prepare("SELECT * FROM products");
} else {
    $stmt = $conn->prepare("SELECT * FROM products WHERE category = ?");
    $stmt->bind_param("s", $category);
}
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $image = htmlspecialchars($row['image']);
        $name = htmlspecialchars($row['name']);
        $price = htmlspecialchars($row['price']);
        $quantity = htmlspecialchars($row['quantity']);

        echo "
        <div class='product-card'>
            <img src='uploads/{$image}' alt='{$name}' width='100'>
            <h3>{$name}</h3>
            <p>₹{$price} | Qty: {$quantity}</p>
        </div>";
    }
} else {
    echo "<p>No products found in this category.</p>";
}
?>
