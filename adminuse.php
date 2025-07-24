<?php
session_start();
if (!isset($_SESSION['phone']) || !isset($_SESSION['role']) || $_SESSION['role'] !== "Admin") {
    header("Location: adminlogin.php?message=Admins+only.");
    exit();
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
if (isset($_GET['msg']) && $_GET['msg'] === 'success') {
    $message = "Seller added successfully!";
}

// Handle form submissions
$message = "";

// Add Product
if (isset($_POST['add_product'])) {
    $name = $_POST['name'];
    $category = $_POST['category'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
    $image_url = $_POST['image_url'];

    $sql = "INSERT INTO products (name, category, description, price, quantity, image_url) 
            VALUES ('$name', '$category', '$description', '$price', '$quantity', '$image_url')";

    $message = $conn->query($sql) === TRUE ? "Product added successfully!" : "Error: " . $conn->error;
}

if (isset($_POST['edit_product'])) {
    $name = $_POST['name'];

    // Fetch current product data
    $fetch_sql = "SELECT * FROM products WHERE name='$name'";
    $result = $conn->query($fetch_sql);

    if ($result->num_rows > 0) {
        $existing = $result->fetch_assoc();

        // Use POST value if provided, otherwise use existing value
        $category   = !empty($_POST['category']) ? $_POST['category'] : $existing['category'];
        $description = !empty($_POST['description']) ? $_POST['description'] : $existing['description'];
        $price      = !empty($_POST['price']) ? $_POST['price'] : $existing['price'];
        $quantity   = !empty($_POST['quantity']) ? $_POST['quantity'] : $existing['quantity'];
        $image_url  = !empty($_POST['image_url']) ? $_POST['image_url'] : $existing['image_url'];
        $seller     = !empty($_POST['seller']) ? $_POST['seller'] : $existing['seller'];

        $sql = "UPDATE products SET 
                    category='$category', 
                    description='$description', 
                    price='$price', 
                    quantity='$quantity', 
                    image_url='$image_url',
                    seller='$seller'
                WHERE name='$name'";

        $message = $conn->query($sql) === TRUE ? "Product updated successfully!" : "Error: " . $conn->error;
    } else {
        $message = "Product not found.";
    }
}


// Add Seller
if (isset($_POST['add_seller'])) {
    $name = $_POST['seller_name'];
    $phone = $_POST['seller_phone'];
    $address = $_POST['seller_address'];
    $products = $_POST['seller_products'];
    $description = $_POST['seller_description'];

    // Check if seller already exists by phone
    $check_sql = "SELECT * FROM sellers WHERE phone='$phone'";
    $check_result = $conn->query($check_sql);

    if ($check_result->num_rows > 0) {
        $message = "Seller with this phone number already exists!";
    } else {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $target_file = $target_dir . basename($_FILES["seller_photo"]["name"]);

        $check = getimagesize($_FILES["seller_photo"]["tmp_name"]);
        if ($check !== false && move_uploaded_file($_FILES["seller_photo"]["tmp_name"], $target_file)) {
            $sql = "INSERT INTO sellers (name, phone, address, products, description, photo) 
                    VALUES ('$name', '$phone', '$address', '$products', '$description', '$target_file')";
            $message = $conn->query($sql) === TRUE ? "Seller added successfully!" : "Error: " . $conn->error;
        } else {
            $message = "Failed to upload image.";
        }
    }
}
if ($message === "Seller added successfully!") {
    header("Location: adminuse.php?msg=success");
    exit();
}

// Edit Seller - Fetch and Update
$edit_seller_data = null;

if (isset($_POST['fetch_seller'])) {
    $phone = $_POST['edit_phone'];
    $sql = "SELECT * FROM sellers WHERE phone='$phone'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $edit_seller_data = $result->fetch_assoc();
    } else {
        $message = "No seller found with this phone number.";
    }
}

if (isset($_POST['update_seller'])) {
    $phone = $_POST['seller_phone'];
    $name = $_POST['seller_name'];
    $address = $_POST['seller_address'];
    $products = $_POST['seller_products'];
    $description = $_POST['seller_description'];

    $update_sql = "UPDATE sellers SET 
        name='$name',
        address='$address',
        products='$products',
        description='$description'
        WHERE phone='$phone'";

    $message = $conn->query($update_sql) === TRUE ? "Seller updated successfully!" : "Error updating seller: " . $conn->error;
}
// Add Offer
if (isset($_POST['add_offer'])) {
    $title = $conn->real_escape_string($_POST['offer_title']);
    $description = $conn->real_escape_string($_POST['offer_description']);

    $sql = "INSERT INTO offers (title, description) VALUES ('$title', '$description')";
    $message = $conn->query($sql) ? "Offer added successfully!" : "Error adding offer: " . $conn->error;
}
// Delete Offer
if (isset($_POST['delete_offer'])) {
    $title = $conn->real_escape_string($_POST['offer_title']);
    $sql = "DELETE FROM offers WHERE title = '$title'";
    $message = $conn->query($sql) ? "Offer deleted successfully!" : "Error deleting offer: " . $conn->error;
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta charset="UTF-8">
    <title>Admin Panel - Manage Products & Sellers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f2f2f2; font-family: Arial, sans-serif; }
        .container { margin-top: 50px; }
        h1 { color: #232f3e; margin-bottom: 20px; }
        .btn-custom { background-color: #ff9900; color: white; border: none; }
        .btn-custom:hover { background-color: #e68a00; }
        .form-section { display: none; margin-top: 30px; }
        header { background: #2C3E50; color: white; padding: 15px; text-align: center; }
        nav ul { list-style: none; padding: 0; }
        nav ul li { display: inline; margin: 0 15px; }
        nav ul li a { color: white; text-decoration: none; }
    </style>
</head>
<body>
<header>

    <nav>
        <ul>
            <li><a href="index2.php">Home</a></li>
            <li><a href="product.php">Products</a></li>
            <li><a href="farming-area.html">Farming Area</a></li>
            <li><a href="sellers.php">Sellers</a></li>
            <li><a href="offers.html">Offers</a></li>
            <li><a href="queries.html">Queries/Suggestions</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>
</header>

<div class="container">

    <h1>Admin Panel - Manage Products & Sellers 🛠️</h1>

    <?php if (!empty($message)): ?>
        <div class="alert alert-info"><?= $message ?></div>
    <?php endif; ?>

    <!-- Buttons -->
    <button class="btn btn-custom me-2" onclick="showForm('add')">➕ Add Product</button>
    <button class="btn btn-custom me-2" onclick="showForm('edit')">✏️ Edit Product</button>
    <button class="btn btn-custom me-2" onclick="showForm('seller')">👤 Add Seller</button>
    <button class="btn btn-custom me-2" onclick="showForm('edit_seller')">✏️ Edit Seller</button>
    <button class="btn btn-custom me-2" onclick="showForm('offer')">🎁 Add Offer</button>
    <button class="btn btn-custom me-2" onclick="showForm('delete_offer')">🗑️ Delete Offer</button>



    <button class="btn btn-custom" onclick="window.location.href='admin-update-orders.php'">📦 Update Orders</button>

    <!-- Add Product Form -->
    <div id="addForm" class="form-section">
        <h3>Add New Product</h3>
        <form method="POST">
            <input type="hidden" name="add_product" value="1">
            <div class="mb-3"><label>Product Name</label><input type="text" name="name" class="form-control" required></div>
            <div class="mb-3"><label>Category</label><input type="text" name="category" class="form-control" required></div>
            <div class="mb-3"><label>Description</label><textarea name="description" class="form-control" required></textarea></div>
            <div class="mb-3"><label>Price</label><input type="number" step="0.01" name="price" class="form-control" required></div>
            <div class="mb-3"><label>Quantity</label><input type="number" name="quantity" class="form-control" required></div>
            <div class="mb-3"><label>Image URL</label><input type="text" name="image_url" class="form-control" required></div>
            <div class="mb-3"><label>Seller</label><input type="text" name="seller" class="form-control" required></div>
            <button type="submit" class="btn btn-success">Add Product</button>
        </form>
    </div>

    <!-- Edit Product Form -->
    <div id="editForm" class="form-section">
        <h3>Edit Existing Product</h3>
        <form method="POST">
            <input type="hidden" name="edit_product" value="1">
            <div class="mb-3"><label>Product Name (to Edit)</label><input type="text" name="name" class="form-control" required></div>
            <div class="mb-3"><label>Category</label><input type="text" name="category" class="form-control" required></div>
            <div class="mb-3"><label>Description</label><textarea name="description" class="form-control" ></textarea></div>
            <div class="mb-3"><label>Price</label><input type="number" step="0.01" name="price" class="form-control" ></div>
            <div class="mb-3"><label>Quantity</label><input type="number" name="quantity" class="form-control"></div>
            <div class="mb-3"><label>Image URL</label><input type="text" name="image_url" class="form-control"></div>
            <div class="mb-3"><label>Seller</label><input type="text" name="seller" class="form-control"></div>
            <button type="submit" class="btn btn-warning">Update Product</button>
        </form>
    </div>

    <!-- Add Seller Form -->
    <div id="sellerForm" class="form-section">
        <h3>Add New Seller</h3>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="add_seller" value="1">
            <div class="mb-3"><label>Seller Name</label><input type="text" name="seller_name" class="form-control" required></div>
            <div class="mb-3"><label>Phone Number</label><input type="text" name="seller_phone" class="form-control" required></div>
            <div class="mb-3"><label>Address</label><input type="text" name="seller_address" class="form-control" required></div>
            <div class="mb-3"><label>Selling Products</label><input type="text" name="seller_products" class="form-control" required></div>
            <div class="mb-3"><label>Description</label><textarea name="seller_description" class="form-control" required></textarea></div>
            <div class="mb-3"><label>Upload Photo</label><input type="file" name="seller_photo" class="form-control" accept="image/*" required></div>
            <button type="submit" class="btn btn-success">Add Seller</button>
        </form>
    </div>
    <!-- Edit Seller Form -->
<div id="editSellerForm" class="form-section">
    <h3>Edit Existing Seller</h3>

    <!-- Step 1: Enter Phone Number -->
    <?php if (!$edit_seller_data): ?>
        <form method="POST">
            <input type="hidden" name="fetch_seller" value="1">
            <div class="mb-3">
                <label>Enter Seller Phone Number</label>
                <input type="text" name="edit_phone" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-info">Fetch Seller</button>
        </form>
    <?php else: ?>
    
    <!-- Step 2: Display Editable Form -->
        <form method="POST">
            <input type="hidden" name="update_seller" value="1">
            <div class="mb-3"><label>Name</label><input type="text" name="seller_name" class="form-control" value="<?= $edit_seller_data['name'] ?>" required></div>
            <div class="mb-3"><label>Phone (unchanged)</label><input type="text" name="seller_phone" class="form-control" value="<?= $edit_seller_data['phone'] ?>" readonly></div>
            <div class="mb-3"><label>Address</label><input type="text" name="seller_address" class="form-control" value="<?= $edit_seller_data['address'] ?>" required></div>
            <div class="mb-3"><label>Products</label><input type="text" name="seller_products" class="form-control" value="<?= $edit_seller_data['products'] ?>" required></div>
            <div class="mb-3"><label>Description</label><textarea name="seller_description" class="form-control" required><?= $edit_seller_data['description'] ?></textarea></div>
            <button type="submit" class="btn btn-warning">Update Seller</button>
        </form>
    <?php endif; ?>
</div>
<div id="offerForm" class="form-section">
    <h3>Add New Offer</h3>
    <form method="POST">
        <input type="hidden" name="add_offer" value="1">
        <div class="mb-3"><label>Offer Title</label><input type="text" name="offer_title" class="form-control" required></div>
        <div class="mb-3"><label>Description</label><textarea name="offer_description" class="form-control" required></textarea></div>
        <button type="submit" class="btn btn-success">Add Offer</button>
    </form>
</div>
<!-- Delete Offer Form -->
<div id="deleteOfferForm" class="form-section">
    <h3>Delete Offer</h3>
    <form method="POST">
        <input type="hidden" name="delete_offer" value="1">
        <div class="mb-3">
            <label>Offer Title (to Delete)</label>
            <input type="text" name="offer_title" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-danger">Delete Offer</button>
    </form>
</div>

    
</div>

<script>
function showForm(formType) {
    document.getElementById('addForm').style.display = formType === 'add' ? 'block' : 'none';
    document.getElementById('editForm').style.display = formType === 'edit' ? 'block' : 'none';
    document.getElementById('sellerForm').style.display = formType === 'seller' ? 'block' : 'none';
    document.getElementById('editSellerForm').style.display = formType === 'edit_seller' ? 'block' : 'none';
    document.getElementById('offerForm').style.display = formType === 'offer' ? 'block' : 'none';
    document.getElementById('deleteOfferForm').style.display = formType === 'delete_offer' ? 'block' : 'none';
}


</script>

</body>
</html>

<?php $conn->close(); ?>
