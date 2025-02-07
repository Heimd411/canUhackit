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

// Simulate a stay-logged-in cookie brute-force challenge
if (isset($_COOKIE['stay_logged_in'])) {
    $cookie = $_COOKIE['stay_logged_in'];

    // Simulate checking the cookie
    if ($cookie === 'correct_cookie_value') {
        echo '<form method="post" action="index.php?complete=bf_cookie">';
        echo '<input type="hidden" name="token" value="' . $_SESSION['token'] . '">';
        echo '<input type="hidden" name="complete" value="bf_cookie">';
        echo '<button class="real-button" type="submit">Complete Challenge</button>';
        echo '</form>';
    } else {
        $error = "Invalid stay-logged-in cookie";
    }
}
?>

<div class="centered">
    <h1>Brute-forcing a Stay-Logged-In Cookie</h1>
    <div class="objective-box">
        <p>THIS CHALLENGE IS NOT IMPLEMENTED! COME BACK ANOTHER TIME</p>
    </div>
    <?php if (isset($error)) { echo "<p class='error'>$error</p>"; } ?>
</div>

<?php
include '../templates/footer.php';
?>