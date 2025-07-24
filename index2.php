<!DOCTYPE html>

<html lang="en"><head><meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Milk & Vegetable Delivery - Home</title>

<link rel="stylesheet" href="style.css">

<!-- Update your script to use fetch() for register.php -->
<script>
    document.getElementById("registerForm").addEventListener("submit", function (e) {
      e.preventDefault();

      const formData = new FormData(this);

      fetch("register.php", {
        method: "POST",
        body: formData
      })
      .then(response => response.text())
      .then(data => {
        alert(data.trim()); // Show clean single popup message
        if (data.includes("successfully")) {
          document.getElementById("registerForm").reset();
        }
      })
      .catch(error => {
        alert("Error: " + error);
      });
    });
  </script>
</head>
<body>
    <header>
    
    <nav>

        <h1>Farm2Home</h1>

        <ul>

            <li><a href="product.php">Products</a></li>

            <li><a href="farming-area.html">Farming Area</a></li>

            <li><a href="sellers.php">Sellers</a></li>

            <li><a href="offers.php">Offers</a></li>
            <li><a href="your-order.php">Your Order</a></li>
            <li><a href="queries.html">Queries/Suggestions</a></li>
            <li><a href="logout.php">Logout</a></li>
            

            
            
            
            
        </ul>

    </nav>

</header>



<!-- Customer Profile (Top Left) -->

<div>
    <a href="profile.php" title="My Profile">
        <img src="https://cdn-icons-png.flaticon.com/512/149/149071.png" alt="Profile" style="width: 60px; height: 60px; border-radius: 50%; vertical-align: middle; top: 30px; position: absolute; left:15px;">
      </a>
</div>



<main class="container">

    <!-- About Us Section (75%) -->

    <!-- About Us Section -->
<section class="about" style="background: #fffbe6; padding: 40px 20px; border-radius: 10px; margin: 30px auto; max-width: 1000px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">

<h2 style="text-align: center; color: #2C3E50; font-size: 30px; margin-bottom: 20px;">About Us</h2>

<p style="font-size: 16px; color: #444; line-height: 1.8; text-align: center; max-width: 800px; margin: 0 auto 30px;">
    <strong>Farm2Home</strong> is more than just a delivery service – it's a commitment to healthy living and empowering local farmers. We bring you the freshest milk and vegetables, sourced every morning from trusted local farms and delivered straight to your doorstep.
    <br><br>
    Our mission is to create a sustainable farm-to-door ecosystem where quality, health, and community thrive together. Whether it’s your daily milk, leafy greens, or seasonal harvests – we deliver it fresh, fast, and with care.
    At Farm2Home, we believe in bridging the gap between local farmers and your family’s kitchen. Our mission is to support farmers by giving them fair prices and steady demand, while helping families enjoy healthy, chemical-free food at honest rates.

We’re passionate about sustainability, health, and community. That’s why every delivery supports eco-friendly farming practices and promotes a healthier lifestyle for our customers. Whether it's creamy farm-fresh milk or nutrient-rich greens, everything we offer is harvested with care and delivered with love.
Every order you place helps a farmer grow and a family eat better. It's a small step toward a better food system.

Farm2Home isn’t just a service — it’s a movement toward conscious consumption and community support. Join us in reshaping how food reaches homes: fresher, faster, and friendlier.

</p>

<h2 style="text-align: center; color: #2C3E50; font-size: 26px; margin-bottom: 10px;">Reach us at:</h2>

<p style="text-align: center; font-size: 16px;">
    📧 <a href="mailto:farm2home@gmail.com" style="color: #e67e22; text-decoration: none; font-weight: bold;">farm2home@gmail.com</a>
</p>

</section>




    <!-- Spacing -->

    <div class="spacer"></div>
<!-- Registration Section (25%) -->

    <?php include 'register.html'; ?>



<!-- Keep everything below the same --><!-- Update your script to use fetch() for register.php -->
 <script>
  
    document.addEventListener("DOMContentLoaded", function () {
  
        document.getElementById("registerForm").addEventListener("submit", function (event) {
  
            event.preventDefault();
  

  
            let role = document.getElementById("user-role").value;
  
            let name = document.getElementById("name").value;
  
            let phone = document.getElementById("phone").value;
  
            let password = document.getElementById("password").value;
  

  
            if (!role || !name || !phone || !password) {
  
                alert("All fields are required!");
  
                return;
  
            }
  

  
            let formData = new FormData();
  
            formData.append("role", role);
  
            formData.append("name", name);
  
            formData.append("phone", phone);
  
            formData.append("password", password);
  

  
            fetch("register.php", {
  
                method: "POST",
  
                body: formData
  
            })
  
            .then(response => response.json())
  
            .then(data => {
  
                alert(data.message); // show popup
  
                if (data.status === "success") {
  
                    document.getElementById("registerForm").reset(); // Clear form
  
                    localStorage.setItem("isRegistered", true);
  
                    localStorage.setItem("isLoggedIn", true);
  
                    localStorage.setItem("customerName", name);
  
                    document.getElementById("customer-name").innerText = name;
  
                }
  
            })
  
            .catch(error => {
  
                console.error("Error:", error);
  
                alert("Something went wrong. Please try again.");
  
            });
  
        });
  
    });
  
</script></main>



<footer>

    <p>&copy; 2025 Milk & Vegetable Delivery</p>

</footer>

</body>
</html> 