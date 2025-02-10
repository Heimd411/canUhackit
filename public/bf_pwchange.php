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

// Initialize login variables
$demo_credentials = [
    'groot' => 'iamgr00t',
    'rocket' => 'nothinglikeme'
];

// Handle login form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_username']) && isset($_POST['login_password'])) {
    $username = $_POST['login_username'];
    $password = $_POST['login_password'];

    if (isset($demo_credentials[$username]) && $demo_credentials[$username] === $password) {
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $username;
        header('Location: bf_pwchange.php');
        exit();
    } else {
        $login_error = "Invalid credentials";
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['current_password']) && 
    isset($_POST['new_password_1']) && isset($_POST['new_password_2'])) {
    
    $username = $_SESSION['username'];
    $current_password = $_POST['current_password'];
    $new_password_1 = $_POST['new_password_1'];
    $new_password_2 = $_POST['new_password_2'];

    // Check if current password is correct
    if ($demo_credentials[$username] === $current_password) {
        if ($new_password_1 !== $new_password_2) {
            $error = "New passwords do not match";
        } else {
            $error = "Password changed successfully";
        }
    } else {
        $error = "Current password is incorrect";
    }

    // Check if brute force was successful
    if ($username === 'dane' && $current_password === 'letmein') {
        $_SESSION['challenge_complete'] = true;
    }
}

// Display content based on login status
if (!isset($_SESSION['logged_in'])) {
?>
    <div class="centered">
        <h1>Password Brute-force via Password Change</h1>
        <div class="objective-box">
            <h3>Objective</h3>
            <p>The password change functionality contains a vulnerability that allows password brute-forcing. Your goal is to:</p>
            <ul>
                <li>Log in as user 'viking' with password 'ship123'</li>
                <li>Analyze the password change functionality</li>
                <li>Use the error messages to brute force dane's password</li>
            </ul>
            <div class="wordlist-download">
                <p style="display:inline;">Download the list of candidate passwords:</p>
                <a href="?download=passwords" class="download-link">Passwords List</a>
            </div>
        </div>

        <div class="login-form">
            <form method="post">
                <div class="form-group">
                    <label for="login_username">Username:</label>
                    <input type="text" id="login_username" name="login_username" required>
                </div>
                <div class="form-group">
                    <label for="login_password">Password:</label>
                    <input type="password" id="login_password" name="login_password" required>
                </div>
                <button class="real-button" type="submit">Login</button>
            </form>
            <?php if (isset($login_error)): ?>
                <p class="error"><?php echo htmlspecialchars($login_error); ?></p>
            <?php endif; ?>
        </div>
    </div>
<?php
} else {
?>
    <div class="centered">
        <h2>Change Password</h2>
        <?php if (isset($_SESSION['challenge_complete'])): ?>
            <div class="success-message">
                <h2>🎉 Challenge Complete! 🎉</h2>
                <p>You've successfully brute-forced dane's password!</p>
                <form method="post" action="index.php?challenge=auth">
                    <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">
                    <input type="hidden" name="complete" value="bf_pwchange">
                    <button class="real-button" type="submit">Complete Challenge</button>
                </form>
            </div>
        <?php endif; ?>

        <div class="change-password-form">
            <form method="post">
                <div class="form-group">
                    <label for="current_password">Current Password:</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>
                <div class="form-group">
                    <label for="new_password_1">New Password:</label>
                    <input type="password" id="new_password_1" name="new_password_1" required>
                </div>
                <div class="form-group">
                    <label for="new_password_2">Confirm New Password:</label>
                    <input type="password" id="new_password_2" name="new_password_2" required>
                </div>
                <button class="real-button" type="submit">Change Password</button>
            </form>
            <?php if (isset($error)): ?>
                <p class="error"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
        </div>

        <form method="post" action="logout.php" class="logout-form">
            <button class="real-button" type="submit">Logout</button>
        </form>
    </div>
<?php
}

include '../templates/footer.php';
?>