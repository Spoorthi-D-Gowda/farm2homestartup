<?php
// Database connection
$servername = "localhost";
$username = "root"; // Use your database username
$password = ""; // Use your database password
$dbname = "productdb"; // Use your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the seller details from the form
    $seller_name = mysqli_real_escape_string($conn, $_POST['seller_name']);
    $seller_phone = mysqli_real_escape_string($conn, $_POST['seller_phone']);
    $seller_address = mysqli_real_escape_string($conn, $_POST['seller_address']);
    $seller_products = mysqli_real_escape_string($conn, $_POST['seller_products']);
    $seller_description = mysqli_real_escape_string($conn, $_POST['seller_description']);

    // Handle the file upload for photo
    $target_dir = "uploads/"; // Folder to save uploaded images
    $target_file = $target_dir . basename($_FILES["seller_photo"]["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    $uploadOk = 1;

    // Check if image file is a valid image or fake image
    if(isset($_POST["submit"])) {
        $check = getimagesize($_FILES["seller_photo"]["tmp_name"]);
        if($check !== false) {
            echo "File is an image - " . $check["mime"] . ".";
            $uploadOk = 1;
        } else {
            echo "File is not an image.";
            $uploadOk = 0;
        }
    }

    // Check if file already exists
    if (file_exists($target_file)) {
        echo "Sorry, file already exists.";
        $uploadOk = 0;
    }

    // Check file size (max 5MB)
    if ($_FILES["seller_photo"]["size"] > 5000000) {
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    // Allow certain file formats
    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
        echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }

    // If everything is ok, upload the file
    if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded.";
    } else {
        if (move_uploaded_file($_FILES["seller_photo"]["tmp_name"], $target_file)) {
            // Insert the seller data into the database
            $sql = "INSERT INTO sellers (name, phone, address, products, description, photo) 
                    VALUES ('$seller_name', '$seller_phone', '$seller_address', '$seller_products', '$seller_description', '$target_file')";

            if ($conn->query($sql) === TRUE) {
                echo "New seller added successfully!";
            } else {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }
}

$conn->close();
?>
