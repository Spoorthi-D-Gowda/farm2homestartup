<?php
// Handle form submission
$message = "";

$conn = new mysqli("localhost", "root", "", "productdb");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Save product on form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id       = $_POST['id'] ?? '';
    $name     = $_POST['name'];
    $price    = $_POST['price'];
    $quantity = $_POST['quantity'];
    $image    = $_FILES['image']['name'] ?? '';
    $tmp_name = $_FILES['image']['tmp_name'] ?? '';
    $seller    = $_POST['seller'];

    if ($image) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        $image_path = $target_dir . time() . '_' . basename($image);
        move_uploaded_file($tmp_name, $image_path);
    }

    if ($id) {
        // UPDATE
        $sql = "UPDATE products SET name=?, price=?, quantity=?";
        $params = [$name, $price, $quantity];
        $types = "sdi";

        if ($image) {
            $sql .= ", image=?";
            $params[] = $image_path;
            $types .= "s";
        }

        $sql .= " WHERE id=?";
        $params[] = $id;
        $types .= "i";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $message = "Product updated successfully!";
    } else {
        // INSERT
        $image_path = $image_path ?? "default.png";
        $stmt = $conn->prepare("INSERT INTO products (name, price, quantity, image) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sdis", $name, $price, $quantity, $image_path);
        $stmt->execute();
        $message = "Product added successfully!";
    }
}
$name = trim($_POST['name']);
$price = floatval($_POST['price']);
$quantity = intval($_POST['quantity']);

if (empty($name) || $price <= 0 || $quantity <= 0) {
    exit("Invalid input. Make sure all fields are filled correctly.");
}

?>

