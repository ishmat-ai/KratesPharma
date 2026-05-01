<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['USER_ID'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['checkout_id']) || !is_numeric($_GET['checkout_id'])) {
    // Invalid checkout ID
    echo "Invalid order confirmation.";
    exit();
}

if (!isset($_GET['full_name'])) {
    echo "Name parameter Missing!!";
    exit();
}

$checkout_id = (int)$_GET['checkout_id']; // Cast to integer for security
$full_name = htmlspecialchars(urldecode($_GET['full_name']), ENT_QUOTES, 'UTF-8');  // Sanitize NAME fetched from url, sanitize name here

// Fetch order details
$stmt = $conn->prepare("SELECT * FROM checkout WHERE checkout_id = ? AND user_id = ?");
$stmt->bind_param("ii", $checkout_id, $_SESSION['USER_ID']);
$stmt->execute();
$checkout_result = $stmt->get_result();

if ($checkout_result->num_rows == 0) {
    echo "Order not found.";
    exit();
}

$checkout_data = $checkout_result->fetch_assoc();

// Fetch the stored data for json form, and change them back into address data for output to the screen
$shipping_address_data = json_decode($checkout_data['shipping_address'], true);

// Make sure everything is in address information if not error.
if ($shipping_address_data) {
    $address_line1 = htmlspecialchars($shipping_address_data['address_line1'], ENT_QUOTES, 'UTF-8');
    $city = htmlspecialchars($shipping_address_data['city'], ENT_QUOTES, 'UTF-8');
    $state = htmlspecialchars($shipping_address_data['state'], ENT_QUOTES, 'UTF-8');
    $zip_code = htmlspecialchars($shipping_address_data['zip_code'], ENT_QUOTES, 'UTF-8');

     $formatted_shipping_address = $address_line1 . ", " . $city . ", " . $state . " " . $zip_code; //Show data
} else {
   $formatted_shipping_address = "Shipping address information not valid!!";  //If Shipping addres isnt correctly showing it on output

}


// Fetch order items
$stmt = $conn->prepare("SELECT oi.*, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.checkout_id = ?");
$stmt->bind_param("i", $checkout_id);
$stmt->execute();
$order_items_result = $stmt->get_result();

$order_items = [];
while ($row = $order_items_result->fetch_assoc()) {
    $order_items[] = $row;
}

?>


<html>
<head>
    <title>Order Confirmation</title>
    <link rel="stylesheet" href="styles/order.css">
</head>
<body>
    

    <div class="confirmation-container">
        
        <h1 style="color: #00a651; text-align: center;">Order Confirmed!</h1>
        
        <div class="order-details">
    <h2>Order Details</h2>
    <p>Order ID: <?php echo htmlspecialchars($checkout_data['checkout_id'], ENT_QUOTES, 'UTF-8'); ?></p>
    <p>Order Date: <?php echo htmlspecialchars($checkout_data['order_date'], ENT_QUOTES, 'UTF-8'); ?></p>
    <p>Customer Name: <?php echo $full_name; ?></p> <!--Show customer name from URL-->
    <p>Total Amount: ₹<?php echo htmlspecialchars(number_format($checkout_data['total_amount'], 2), ENT_QUOTES, 'UTF-8'); ?></p>

    <h3>Shipping Address</h3>
    <p><?php echo $formatted_shipping_address ?></p> <!--Replaces Shipping address here and it changes dynamically by whatever code is stored-->

    <h3>Order Items</h3>
    <ul>
        <?php foreach ($order_items as $item): ?>
            <li>
                <?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?> x <?php echo htmlspecialchars($item['quantity'], ENT_QUOTES, 'UTF-8'); ?>
                - ₹<?php echo htmlspecialchars(number_format($item['price'], 2), ENT_QUOTES, 'UTF-8'); ?>
            </li>
        <?php endforeach; ?>
    </ul>

    <p><b>Your order will be shipped soon.</b></p>

<div style="text-align: center;">
            <utton class="print-receipt" onclick="window.print()">
                <i class="fas fa-print"></i> Print Receipt
            </button>
        </div>
    </div>
</body>
</html>
