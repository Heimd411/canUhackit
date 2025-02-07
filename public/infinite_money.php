<?php
session_start();
include '../templates/header.php';

// Clear challenge-specific session variables
function clearChallengeSession() {
    // Auth related
    unset($_SESSION['logged_in']);
    unset($_SESSION['username']);
    unset($_SESSION['role']);
    unset($_SESSION['pending_2fa']);
    unset($_SESSION['2fa_code']);
    unset($_SESSION['pending_username']);
    
    // Business logic related
    unset($_SESSION['balance']);
    unset($_SESSION['cart']);
    unset($_SESSION['purchased_items']);
    unset($_SESSION['gift_cards']);
    unset($_SESSION['used_gift_cards']);
    unset($_SESSION['discount']);
    
    // Keep global session variables
    // $_SESSION['token']
    // $_SESSION['points']
    // $_SESSION['completed_challenges']
}

// Call at start of challenge
clearChallengeSession();

// Clear unrelated session data
unset($_SESSION['insufficient_workflow_cart']);
unset($_SESSION['insufficient_workflow_purchased_items']);

// Initialize session variables
if (!isset($_SESSION['infinite_money_balance'])) {
    $_SESSION['infinite_money_balance'] = 100;
}
if (!isset($_SESSION['infinite_money_cart'])) {
    $_SESSION['infinite_money_cart'] = array();
}
if (!isset($_SESSION['infinite_money_gift_cards'])) {
    $_SESSION['infinite_money_gift_cards'] = array();
}
if (!isset($_SESSION['infinite_money_used_gift_cards'])) {
    $_SESSION['infinite_money_used_gift_cards'] = array();
}
if (!isset($_SESSION['infinite_money_purchased_items'])) {
    $_SESSION['infinite_money_purchased_items'] = array();
}

// Products
$products = array(
    'cpu' => array('name' => 'Intel CPU', 'price' => 600),
    'gift_card' => array('name' => '$10 Gift Card', 'price' => 10)
);

// Validate and clear invalid items from cart
$_SESSION['infinite_money_cart'] = array_filter($_SESSION['infinite_money_cart'], function($item) use ($products) {
    return isset($products[$item['id']]);
});

// Handle add to cart
if (isset($_POST['add_to_cart']) && isset($_POST['product_id']) && isset($_POST['quantity'])) {
    $product_id = $_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    if (isset($products[$product_id]) && $quantity > 0) {
        for ($i = 0; $i < $quantity; $i++) {
            $_SESSION['infinite_money_cart'][] = array('id' => $product_id, 'name' => $products[$product_id]['name'], 'price' => $products[$product_id]['price']);
        }
    }
}

// Handle apply coupon
if (isset($_POST['apply_coupon']) && $_POST['coupon'] === 'thirtyoff') {
    $_SESSION['infinite_money_discount'] = 0.3;
}

// Handle purchase
if (isset($_POST['purchase'])) {
    $total = 0;
    foreach ($_SESSION['infinite_money_cart'] as $item) {
        $total += $item['price'];
    }

    if (isset($_SESSION['infinite_money_discount'])) {
        $total *= (1 - $_SESSION['infinite_money_discount']);
    }
    
    if ($_SESSION['infinite_money_balance'] >= $total) {
        $_SESSION['infinite_money_balance'] -= $total;
        
        // Clear previous purchased items
        $_SESSION['infinite_money_purchased_items'] = array();
        
        // Generate gift card codes
        foreach ($_SESSION['infinite_money_cart'] as $item) {
            if ($item['id'] === 'gift_card') {
                $code = bin2hex(random_bytes(8));
                $_SESSION['infinite_money_gift_cards'][$code] = 10;
                $_SESSION['infinite_money_purchased_items'][] = array('id' => 'gift_card', 'name' => '$10 Gift Card', 'price' => 10, 'code' => $code);
            } else {
                $_SESSION['infinite_money_purchased_items'][] = $item;
            }
        }
        
        $_SESSION['infinite_money_cart'] = array();
        $message = "Purchase successful!";
    } else {
        $message = "Insufficient funds!";
    }
}

// Handle clear cart
if (isset($_POST['clear_cart'])) {
    $_SESSION['infinite_money_cart'] = array();
    $message = "Cart cleared!";
}

// Handle redeem gift card
if (isset($_POST['redeem_gift_card']) && isset($_POST['gift_card_code'])) {
    $gift_card_code = $_POST['gift_card_code'];
    if (isset($_SESSION['infinite_money_gift_cards'][$gift_card_code]) && !in_array($gift_card_code, $_SESSION['infinite_money_used_gift_cards'])) {
        $_SESSION['infinite_money_balance'] += $_SESSION['infinite_money_gift_cards'][$gift_card_code];
        $_SESSION['infinite_money_used_gift_cards'][] = $gift_card_code;
        unset($_SESSION['infinite_money_gift_cards'][$gift_card_code]);
        $message = "Gift card redeemed!";
    } else {
        $message = "Invalid or already used gift card!";
    }
}

// Check if CPU has been purchased
$cpu_purchased = false;
if (isset($_SESSION['infinite_money_purchased_items']) && is_array($_SESSION['infinite_money_purchased_items'])) {
    foreach ($_SESSION['infinite_money_purchased_items'] as $item) {
        if (is_array($item) && isset($item['id']) && $item['id'] === 'cpu') {
            $cpu_purchased = true;
            break;
        }
    }
}

// Calculate total sum of items in cart
$total_sum = 0;
foreach ($_SESSION['infinite_money_cart'] as $item) {
    $total_sum += $item['price'];
}

// Apply discount if available
if (isset($_SESSION['infinite_money_discount'])) {
    $discounted_total = $total_sum * (1 - $_SESSION['infinite_money_discount']);
} else {
    $discounted_total = $total_sum;
}

// Display interface
?>
<div class="container">
    <div class="products">
        <h2>Products</h2>
        <form method="post">
            <input type="hidden" name="product_id" value="cpu">
            Quantity: <input type="number" name="quantity" value="1"><br>
            <button class="real-button" type="submit" name="add_to_cart">Add Intel CPU to Cart</button>
        </form>
        <form method="post">
            <input type="hidden" name="product_id" value="gift_card">
            Quantity: <input type="number" name="quantity" value="1"><br>
            <button class="real-button" type="submit" name="add_to_cart">Add $10 Gift Card to Cart</button>
        </form>
    </div>

    <div class="cart">
        <h2>Cart</h2>
        <p>Balance: $<?php echo $_SESSION['infinite_money_balance']; ?></p>
        <?php if (!empty($_SESSION['infinite_money_cart'])): ?>
            <ul>
                <?php foreach ($_SESSION['infinite_money_cart'] as $item): ?>
                    <li><?php echo $item['name']; ?> - $<?php echo $item['price']; ?></li>
                <?php endforeach; ?>
            </ul>
            <p>Total: $<?php echo $total_sum; ?></p>
            <?php if (isset($_SESSION['infinite_money_discount'])): ?>
                <p>Discounted Total: $<?php echo $discounted_total; ?></p>
            <?php endif; ?>
            <form method="post">
                <button class="real-button" type="submit" name="purchase">Purchase</button>
                <button class="real-button" type="submit" name="clear_cart">Clear Cart</button>
            </form>
        <?php endif; ?>
    </div>

    <div class="actions">
        <h2>Coupon</h2>
        <form method="post">
            Coupon Code: <input type="text" name="coupon"><br>
            <button class="real-button" type="submit" name="apply_coupon">Apply Coupon (thirtyoff)</button>
        </form>

        <h2>Redeem Gift Card</h2>
        <form method="post">
            Gift Card Code: <input type="text" name="gift_card_code"><br>
            <button class="real-button" type="submit" name="redeem_gift_card">Redeem</button>
        </form>
    </div>

    <div class="receipt">
        <h2>Receipt</h2>
        <?php if (!empty($_SESSION['infinite_money_purchased_items'])): ?>
            <ul>
                <?php foreach ($_SESSION['infinite_money_purchased_items'] as $item): ?>
                    <?php if (is_array($item) && isset($item['name']) && isset($item['price'])): ?>
                        <li><?php echo $item['name']; ?> - $<?php echo $item['price']; ?>
                            <?php if (isset($item['code'])): ?>
                                (Code: <?php echo $item['code']; ?>)
                            <?php endif; ?>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <?php if ($cpu_purchased): ?>
        <div class="congratulations">
            <h2>Congratulations!</h2>
            <form method="post" action="index.php?challenge=business_logic">
                <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">
                <input type="hidden" name="complete" value="infinite_money">
                <button class="real-button" type="submit">Complete Challenge</button>
            </form>
        </div>
    <?php endif; ?>
</div>
<?php include '../templates/footer.php'; ?>