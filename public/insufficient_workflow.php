<?php
session_start();

// Process all redirects and session handling before including header.php
// Handle purchase confirmation redirect
if (isset($_GET['confirm'])) {
    if (!empty($_SESSION['cart'])) {
        $_SESSION['purchased_items'] = array_merge(
            isset($_SESSION['purchased_items']) ? $_SESSION['purchased_items'] : array(),
            $_SESSION['cart']
        );
        
        // Check if Graphics Card was obtained through exploit
        foreach ($_SESSION['cart'] as $item) {
            if ($item['name'] === 'Graphics Card') {
                $_SESSION['challenge_complete'] = true;
                header('Location: insufficient_workflow.php');
                exit;
            }
        }
    }
    header('Location: insufficient_workflow.php');
    exit;
}

// Handle purchase redirect
if (isset($_POST['purchase'])) {
    $total = 0;
    foreach ($_SESSION['cart'] as $item) {
        $total += $item['price'];
    }
    
    if ($_SESSION['balance'] >= $total) {
        $_SESSION['balance'] -= $total;
        $_SESSION['purchased_items'] = array_merge(
            isset($_SESSION['purchased_items']) ? $_SESSION['purchased_items'] : array(),
            $_SESSION['cart']
        );
        $_SESSION['cart'] = array();
        header('Location: insufficient_workflow.php?confirm=1');
        exit;
    }
}

// Now include header and rest of the page
include '../templates/header.php';

// Ensure the session token is set
if (!isset($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}

// Only clear session when first starting the challenge
if (!isset($_SESSION['insufficient_workflow_started'])) {
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
    }

    // Call only once when starting challenge
    clearChallengeSession();
    $_SESSION['insufficient_workflow_started'] = true;
}

// Initialize session variables
if (!isset($_SESSION['balance'])) {
    $_SESSION['balance'] = 100;
}
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

// Products
$products = array(
    'graphics_card' => array('name' => 'Graphics Card', 'price' => 1400),
    'usb_drive' => array('name' => 'USB Drive', 'price' => 20)
);

// Initialize message
$message = "";

// Handle add to cart
if (isset($_POST['add_to_cart']) && isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];
    if (isset($products[$product_id])) {
        $_SESSION['cart'][] = $products[$product_id];
    }
}

// Handle clear cart
if (isset($_POST['clear_cart'])) {
    $_SESSION['cart'] = array();
    $message = "Cart cleared.";
}

// Display interface
?>
<div class="centered">
    <h1>Insufficient Workflow Validation</h1>
    <div class="objective-box">
        <p>This lab makes flawed assumptions about the sequence of events in the purchasing workflow. To solve the lab, exploit this flaw to buy a "Graphics Card".</p>
    </div>
    
    <p>Balance: $<?php echo $_SESSION['balance']; ?></p>
    
    <div class="grid-container">
        <?php foreach ($products as $id => $product): ?>
            <div class="grid-item">
                <h2><?php echo $product['name']; ?></h2>
                <p>Price: $<?php echo $product['price']; ?></p>
                <form method="post">
                    <input type="hidden" name="product_id" value="<?php echo $id; ?>">
                    <button class="real-button" type="submit" name="add_to_cart">Add to Cart</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (!empty($_SESSION['cart'])): ?>
        <div class="cart">
            <h2>Cart</h2>
            <?php if (!empty($message)): ?>
                <p><?php echo $message; ?></p>
            <?php endif; ?>
            <?php 
            $total = 0;
            foreach ($_SESSION['cart'] as $item): 
                $total += $item['price'];
            ?>
                <p><?php echo $item['name']; ?> - $<?php echo $item['price']; ?></p>
            <?php endforeach; ?>
            <p>Total: $<?php echo $total; ?></p>
            <form method="post">
                <button class="real-button" type="submit" name="purchase">Purchase</button>
                <button class="real-button" type="submit" name="clear_cart">Clear Cart</button>
            </form>
        </div>
    <?php endif; ?>
</div>
<?php include '../templates/footer.php'; ?>