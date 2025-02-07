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

// Initialize users if not exists
if (!isset($_SESSION['2fa_users'])) {
    $_SESSION['2fa_users'] = array(
        'Rex' => array(
            'password' => 'phantom',
            'role' => 'user'
        ),
        'admin' => array(
            'password' => 'secretpass',
            'role' => 'admin'
        )
    );
}

// Ensure the session token is set
if (!isset($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}

// Generate 2FA code
function generate2FACode() {
    return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
}

// ASCII phone art
function displayPhone($code) {
    return "
<pre class='ascii-phone'>
┌─────────────┐
│  ▀▀▀▀▀▀▀▀▀  │
│  NEW MSG!   │
│ 2FA Code:   │
│  {$code}     │
│             │
│             │
└─────────────┘
<p>Rex Phone</p>
</pre>";
}

// Handle login
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    if (isset($_SESSION['2fa_users'][$username]) && 
        $_SESSION['2fa_users'][$username]['password'] === $password) {
        $_SESSION['pending_2fa'] = true;
        $_SESSION['pending_username'] = $username;
        $_SESSION['2fa_code'] = generate2FACode();
        $message = "Please check your phone for the authentication code.";
    } else {
        $message = "Invalid credentials!";
    }
}

// Handle logout
if (isset($_POST['logout'])) {
    // Clear all session variables
    session_unset();
    // Destroy the session
    session_destroy();
    // Start new session for new messages
    session_start();
    $message = "Logged out successfully!";
}

if (isset($_POST['verify_2fa'])) {
    $code = $_POST['code'];
    $username = $_POST['username']; // Vulnerable parameter
    
    if ($code === $_SESSION['2fa_code']) {
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $username; // Vulnerability: No validation of username
        $_SESSION['role'] = $_SESSION['2fa_users'][$username]['role'];
        // Clear 2FA pending state
        unset($_SESSION['pending_2fa']);
        unset($_SESSION['2fa_code']);
        $message = "Welcome $username!";
    } else {
        $message = "Invalid 2FA code!";
    }
}
?>

<div class="centered">
    <h1>2FA Authentication Challenge</h1>
    
    <div class="objective-box">
        <p>Try to login with:</p>
        <p>Username: Rex</p>
        <p>Password: phantom</p>
        <p>Then try to bypass the 2FA to login as admin!</p>
    </div>
    
    <?php if (isset($message)): ?>
        <p class="message"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <?php if (isset($_SESSION['logged_in'])): ?>
        <div class="user-panel">
            <p>Logged in as: <?php echo htmlspecialchars($_SESSION['username']); ?></p>
            <form method="post">
                <button class="real-button" type="submit" name="logout">Logout</button>
            </form>
        </div>
        
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <div class="congratulations">
                <h2>Congratulations!</h2>
                <p>You successfully bypassed 2FA!</p>
                <form method="post" action="index.php?challenge=auth">
                    <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">
                    <input type="hidden" name="complete" value="2fa_bypass">
                    <button class="real-button" type="submit">Complete Challenge</button>
                </form>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <?php if (!isset($_SESSION['pending_2fa'])): ?>
            <div class="login-form">
                <h2>Login</h2>
                <form method="post">
                    <input type="text" name="username" placeholder="Username" required><br>
                    <input type="password" name="password" placeholder="Password" required><br>
                    <button class="real-button" type="submit" name="login">Login</button>
                </form>
            </div>
        <?php else: ?>
            <div class="phone-container">
                <?php echo displayPhone($_SESSION['2fa_code']); ?>
            </div>
            
            <div class="verify-form">
                <h2>Enter 2FA Code</h2>
                <form method="post">
                    <input type="hidden" name="username" value="<?php echo htmlspecialchars($_SESSION['pending_username']); ?>">
                    <input type="text" name="code" placeholder="Enter 6-digit code" required><br>
                    <button class="real-button" type="submit" name="verify_2fa">Verify</button>
                </form>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include '../templates/footer.php'; ?>