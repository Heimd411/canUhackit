<?php
session_start();
include '../templates/header.php';

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
        <p>Some%ne is playing tr%cks on me! bm90IGFuc3dlcg==</p>
    </div>
    <?php if (isset($error)) { echo "<p class='error'>$error</p>"; } ?>
</div>

<?php
include '../templates/footer.php';
?>