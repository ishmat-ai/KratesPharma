<?php 
session_start();
$user = $_SESSION['USER_ID'];

if ($user == "") {
    header("Location: login.php");
    exit();
}

require_once 'includes/db.php';

// Fetch cart details
$stmt = $conn->prepare("SELECT c.product_id, c.quantity, p.name, p.best_price, p.image, p.mrp, p.discount
                         FROM cart c
                         JOIN products p ON c.product_id = p.id
                         WHERE c.user_id = ?");
$stmt->bind_param("i", $user);
$stmt->execute();
$cart_result = $stmt->get_result();

$cart = [];
$subtotal = 0;

if ($cart_result && $cart_result->num_rows > 0) {
    while ($row = $cart_result->fetch_assoc()) {
        $cart[] = $row;
        $subtotal += $row['best_price'] * $row['quantity'];
    }
}

// Calculate tax and total
$tax = $subtotal * 0.05;
$shipping = ($subtotal > 0) ? 40 : 0;  // Shipping only if cart is not empty
$total = $subtotal + $tax + $shipping;

// Store totals in session
$_SESSION['subtotal'] = $subtotal;
$_SESSION['tax'] = $tax;
$_SESSION['shipping'] = $shipping;
$_SESSION['total'] = $total;


?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles/cart_style.css">
</head>
<body>
    <div class="container">
        <div class="cart-header">
            <h1>Your Cart</h1>
        </div>

        <div class="cart-items" id="cart-items">
            <?php if (count($cart) > 0): ?>
                <?php foreach ($cart as $item): ?>
                    <div class="cart-item">
                        <img src="<?php echo htmlspecialchars($item['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?>" class="item-image">
                        <div class="item-details">
                            <h3><?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?></h3>
                            <div class="price-info">
                                <span class="mrp">MRP: ₹<?php echo htmlspecialchars(number_format($item['mrp'], 2), ENT_QUOTES, 'UTF-8'); ?></span>
                                <span>Price: ₹<?php echo htmlspecialchars(number_format($item['best_price'], 2), ENT_QUOTES, 'UTF-8'); ?></span>
                                <span>Discount: <?php echo htmlspecialchars($item['discount'], ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                            <div class="quantity-controls">
                                <button class="quantity-btn" onclick="updateQuantity(<?php echo $item['product_id']; ?>, -1)">-</button>
                                <span><?php echo htmlspecialchars($item['quantity'], ENT_QUOTES, 'UTF-8'); ?></span>
                                <button class="quantity-btn" onclick="updateQuantity(<?php echo $item['product_id']; ?>, 1)">+</button>
                            </div>
                            <div class="remove-item" onclick="removeItem(<?php echo $item['product_id']; ?>)">Remove Item</div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div id="no-items-message">Your cart is empty.</div>
            <?php endif; ?>
        </div>

        <div class="cart-summary" <?php if (count($cart) === 0) echo 'style="display:none;"'; ?>>
            <div class="summary-row">
                <span>Subtotal:</span>
                <span id="subtotal">₹<?php echo htmlspecialchars(number_format($subtotal, 2), ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
            <div class="summary-row">
                <span>Tax (5%):</span>
                <span id="tax">₹<?php echo htmlspecialchars(number_format($tax, 2), ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
            <div class="summary-row">
                <span>Shipping:</span>
                <span id="shipping">₹<?php echo htmlspecialchars(number_format($shipping, 2), ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
            <div class="summary-row" style="font-weight: 700; font-size: 1.1rem;">
                <span>Total:</span>
                <span id="total">₹<?php echo htmlspecialchars(number_format($total, 2), ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
            <button class="checkout-btn" onclick="window.location.href='checkout.php'">Proceed to Checkout</button>
        </div>
    </div>

    <script>
        function updateQuantity(product_id, change) {
            $.ajax({
                url: 'update_quantity.php',
                method: 'POST',
                data: { product_id: product_id, change: change },
                success: function(response) {
                    if (response === 'success') {
                        location.reload();
                    } else {
                        alert('Error updating quantity: ' + response);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', error);
                    alert('An error occurred while updating quantity.');
                }
            });
        }

        function removeItem(product_id) {
            $.ajax({
                url: 'remove_item.php',
                method: 'POST',
                data: { product_id: product_id },
                success: function(response) {
                    if (response === 'success') {
                        location.reload();
                    } else {
                        alert('Error removing item: ' + response);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', error);
                    alert('An error occurred while removing item.');
                }
            });
        }
    </script>
</body>
</html>

