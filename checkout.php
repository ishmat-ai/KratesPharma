<?php
session_start();
$user = $_SESSION['USER_ID'];

if ($user == "") {
    header("Location: login.php");
    exit();
}

require_once 'includes/db.php';

// Retrieve user's name from the database
$stmt_user = $conn->prepare("SELECT name FROM users WHERE id = ?");
$stmt_user->bind_param("i", $user);
$stmt_user->execute();
$user_result = $stmt_user->get_result();

if ($user_result && $user_result->num_rows > 0) {
    $user_data = $user_result->fetch_assoc();
    $user_name = htmlspecialchars($user_data['name'], ENT_QUOTES, 'UTF-8'); //Sanitize user input and then used.
} else {
    $user_name = "User"; // Default value if name not found (very unlikely)
}
$stmt_user->close(); //Close the sql statement

// Retrieve cart items
$stmt = $conn->prepare("SELECT c.product_id, c.quantity, p.name, p.best_price, p.image, p.mrp, p.discount
                         FROM cart c
                         JOIN products p ON c.product_id = p.id
                         WHERE c.user_id = ?");
$stmt->bind_param("i", $user); // Use $user instead of $user_id
$stmt->execute();
$cart_result = $stmt->get_result();


$cart = [];
if ($cart_result && $cart_result->num_rows > 0) {
    while ($row = $cart_result->fetch_assoc()) {
        $cart[] = $row;
    }
} else {
    header("Location: cart2.php");
    exit();
}

// Calculate totals
// Calculate subtotal
$subtotal = 0;
foreach ($cart as $item) {
    $subtotal += $item['best_price'] * $item['quantity'];
}

// Calculate tax and total
$tax = $subtotal * 0.05;
$shipping = 40;
$total = $subtotal + $tax + $shipping;
?>


<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - PharmaCare</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles/cart_style.css">
    <link rel="stylesheet" href="styles/checkout.css">
</head>
<body>
    <header class="pharma-header">
        <h1>Secure Checkout</h1>
    </header>

    <div class="checkout-container">
        <!-- Checkout Form -->
        <div class="checkout-form">
            <h2>Shipping Information</h2>
            <form action="checkout_process.php" method="POST">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" value="<?php echo $user_name; ?>" required> <!--Pre-fill name with user's name-->
                </div>

                <div class="form-group">
                    <label>Address</label>
                    <input type="text" name="address_line1" required>
                </div>

                <div class="form-group">
                    <label>City</label>
                    <input type="text" name="city" required>
                </div>

                <div class="form-group">
                    <label>State</label>
                    <input type="text" name="state" required>
                </div>

                <div class="form-group">
                    <label>Pin Code</label>
                    <input type="text" name="zip_code" required>
                </div>

                <h2>Payment Method</h2>
                <div class="payment-method">
                    <div class="payment-option">
                        <input type="radio" name="payment_method" value="cod" id="cod" required>
                        <label for="cod">Cash on Delivery</label>
                    </div>
                 </div>

                <button type="submit" class="place-order-btn">Place Order</button>
            </form>
        </div>

        <!-- Order Summary -->
        <div class="cart-summary">
            <h2>Order Summary</h2>
            <div class="summary-row">
                <span>Subtotal:</span>
                <span>₹<?php echo htmlspecialchars(number_format($subtotal, 2), ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
            <div class="summary-row">
                <span>Tax (5%):</span>
                <span>₹<?php echo htmlspecialchars(number_format($tax, 2), ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
            <div class="summary-row">
                <span>Shipping:</span>
                <span>₹<?php echo htmlspecialchars(number_format($shipping, 2), ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
            <div class="summary-row" style="font-weight: 700;">
                <span>Total:</span>
                <span>₹<?php echo htmlspecialchars(number_format($total, 2), ENT_QUOTES, 'UTF-8'); ?></span>
            </div>


        </div>
    </div>
</body>
</html>


