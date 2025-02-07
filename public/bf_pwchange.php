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

// Simulate a password change form
if (isset($_POST['username']) && isset($_POST['old_password']) && isset($_POST['new_password'])) {
    $username = $_POST['username'];
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];

    // Simulate checking credentials and changing password
    if ($username === 'admin' && $old_password === 'password') {
        // Simulate password change
        echo '<form method="post" action="index.php?complete=bf_pwchange">';
        echo '<input type="hidden" name="token" value="' . $_SESSION['token'] . '">';
        echo '<input type="hidden" name="complete" value="bf_pwchange">';
        echo '<button class="real-button" type="submit">Complete Challenge</button>';
        echo '</form>';
    } else {
        $error = "Invalid credentials";
    }
}
?>

<div class="centered">
    <h1>Password Brute-force via Password Change</h1>
    <div class="objective-box">
        <p>THIS CHALLENGE IS NOT IMPLEMENTED! COME BACK ANOTHER TIME</p>
    </div>
    <form method="post">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username"><br>
        <label for="old_password">Old Password:</label>
        <input type="password" id="old_password" name="old_password"><br>
        <label for="new_password">New Password:</label>
        <input type="password" id="new_password" name="new_password"><br>
        <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">
        <button class="real-button" type="submit">Change Password</button>
    </form>
    <?php if (isset($error)) { echo "<p class='error'>$error</p>"; } ?>
</div>

<?php
include '../templates/footer.php';
?>