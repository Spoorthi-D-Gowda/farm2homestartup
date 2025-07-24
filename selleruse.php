<?php
session_start();

// Check login
if (!isset($_SESSION['seller_name'])) {
    header("Location: forseller.php");
    exit();
}

$seller_name = $_SESSION['seller_name'];

// Handle AJAX update request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    header('Content-Type: application/json');

    $id = $_POST['id'] ?? null;
    $field = $_POST['field'] ?? null;
    $value = $_POST['value'] ?? null;

    if (!$id || !$field || !$value) {
        echo json_encode(['success' => false, 'message' => 'Missing parameters']);
        exit();
    }

    if (!in_array($field, ['delivery_status', 'payment_status'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid field']);
        exit();
    }

    $valid_values = [
        'delivery_status' => ['Pending', 'Delivered'],
        'payment_status' => ['Unpaid', 'Paid']
    ];

    if (!in_array($value, $valid_values[$field])) {
        echo json_encode(['success' => false, 'message' => 'Invalid value']);
        exit();
    }

    // Connect to DB
    $conn = new mysqli("localhost", "root", "", "productdb");
    if ($conn->connect_error) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit();
    }

    // Verify the order belongs to this seller
    $stmt = $conn->prepare("SELECT seller FROM orders WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($seller);
    $stmt->fetch();
    $stmt->close();

    if ($seller !== $seller_name) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized to update this order']);
        $conn->close();
        exit();
    }

    // Update order status
    $stmt = $conn->prepare("UPDATE orders SET $field = ? WHERE id = ?");
    $stmt->bind_param("si", $value, $id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update database']);
    }

    $stmt->close();
    $conn->close();
    exit();
}

// Normal page load - show orders

// Connect to DB
$conn = new mysqli("localhost", "root", "", "productdb");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Date filtering
$filter = $_GET['filter'] ?? null;
$today = date('Y-m-d');
$yesterday = date('Y-m-d', strtotime('-1 day'));

if ($filter === 'today') {
    $date_condition = "AND DATE(order_date) = '$yesterday'";
} elseif ($filter === 'tomorrow') {
    $date_condition = "AND DATE(order_date) = '$today'";
} else {
    $date_condition = "";
}

// Fetch orders for this seller with date filter
$query = "SELECT id, product_name, fullname, phone, address, quantity, total_amount, payment, delivery_status, payment_status FROM orders WHERE seller = ? $date_condition";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $seller_name);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Welcome Seller</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f8f9fa;
            padding: 20px;
        }
        h2 {
            text-align: center;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 10px 14px;
            text-align: center;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        a.back {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            color: #007bff;
        }
        a.back:hover {
            text-decoration: underline;
        }
        select {
            padding: 5px;
        }
        .filter-buttons {
            text-align: center;
            margin-bottom: 20px;
        }
        .filter-buttons button {
            padding: 10px 20px;
            margin: 0 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        .filter-buttons button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <h2>Orders Assigned to You (<?= htmlspecialchars($seller_name) ?>)</h2>

    <div class="filter-buttons">
        <button onclick="loadOrders('today')">Today's Orders</button>
        <button onclick="loadOrders('tomorrow')">Tomorrow's Orders</button>
    </div>

    <?php if ($result->num_rows > 0): ?>
        <table>
            <tr>
                <th>Order ID</th>
                <th>Product</th>
                <th>Name</th>
                <th>Phone</th>
                <th>Address</th>
                <th>Quantity</th>
                <th>Total Amount</th>
                <th>Payment Type</th>
                <th>Delivery Status</th>
                <th>Payment Status</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['product_name']) ?></td>
                    <td><?= htmlspecialchars($row['fullname']) ?></td>
                    <td><?= htmlspecialchars($row['phone']) ?></td>
                    <td><?= htmlspecialchars($row['address']) ?></td>
                    <td><?= $row['quantity'] ?></td>
                    <td><?= $row['total_amount'] ?></td>
                    <td><?= htmlspecialchars($row['payment']) ?></td>
                    <td>
                        <select class="delivery_status" data-id="<?= $row['id'] ?>">
                            <option value="Pending" <?= $row['delivery_status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="Delivered" <?= $row['delivery_status'] === 'Delivered' ? 'selected' : '' ?>>Delivered</option>
                        </select>
                    </td>
                    <td>
                        <select class="payment_status" data-id="<?= $row['id'] ?>">
                            <option value="Unpaid" <?= $row['payment_status'] === 'Unpaid' ? 'selected' : '' ?>>Unpaid</option>
                            <option value="Paid" <?= $row['payment_status'] === 'Paid' ? 'selected' : '' ?>>Paid</option>
                        </select>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p style="text-align:center;">No orders found for the selected filter.</p>
    <?php endif; ?>

    <a href="logout.php" class="back">&larr; logout here</a>

    <script>
        function loadOrders(type) {
            window.location.href = `selleruse.php?filter=${type}`;
        }

        document.querySelectorAll('select.delivery_status, select.payment_status').forEach(select => {
            select.addEventListener('change', function() {
                const orderId = this.getAttribute('data-id');
                const field = this.classList.contains('delivery_status') ? 'delivery_status' : 'payment_status';
                const value = this.value;

                fetch('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `action=update_status&id=${orderId}&field=${field}&value=${encodeURIComponent(value)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        alert('Update failed: ' + data.message);
                    }
                })
                .catch(() => {
                    alert('Error updating status.');
                });
            });
        });
    </script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
