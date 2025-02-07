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

// Ensure the session token is set
if (!isset($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}

// Initialize user balance if not already set
if (!isset($_SESSION['balance'])) {
    $_SESSION['balance'] = 100;
}

// Initialize message
$message = "";

// Handle purchase
if (isset($_POST['item']) && isset($_POST['price']) && isset($_POST['token']) && $_POST['token'] === $_SESSION['token']) {
    $item = $_POST['item'];
    $price = (int)$_POST['price'];

    if ($_SESSION['balance'] >= $price) {
        $_SESSION['balance'] -= $price;
        $_SESSION['purchased_items'][] = $item;
        if ($item === 'Graphics Card') {
            $message = "You have successfully purchased a $item for $$price.";
            echo '<form method="post" action="index.php?challenge=business_logic">';
            echo '<input type="hidden" name="token" value="' . $_SESSION['token'] . '">';
            echo '<input type="hidden" name="complete" value="excessive_trust">';
            echo '<center><button class="real-button" type="submit">Complete Challenge</button></center>';
            echo '</form>';
        } else {
            $message = "You have successfully purchased a $item for $$price.";
        }
    } else {
        $message = "You do not have enough balance to purchase a $item.";
    }
}
?>

<div class="centered">
    <h1>Excessive Trust in Client-Side Controls</h1>
    <div class="objective-box">
        <p>Exploit a logic flaw in the purchasing workflow to buy a "Graphics Card" for an unintended price.</p>
    </div>
    <p>Balance: $<?php echo $_SESSION['balance']; ?></p>
    <p class="message"><?php echo $message; ?></p>
    <div class="item">
        <h2>Graphics Card</h2>
        <p>Price: $1400</p>
        <form method="post">
            <input type="hidden" name="item" value="Graphics Card">
            <input type="hidden" name="price" value="1400">
            <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">
            <button class="real-button" type="submit">Buy</button>
        </form>
    </div>
</div>

<?php
include '../templates/footer.php';
?>