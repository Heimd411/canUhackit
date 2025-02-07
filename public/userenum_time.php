<?php
session_start();
include '../templates/header.php';

// Clear challenge-specific session variables
function clearChallengeSession() {
    // Keep attempts tracking
    $attempts = isset($_SESSION['attempts']) ? $_SESSION['attempts'] : array();
    $blocked_until = isset($_SESSION['blocked_until']) ? $_SESSION['blocked_until'] : array();
    
    // Clear other session variables
    unset($_SESSION['logged_in']);
    unset($_SESSION['username']);
    
    // Restore attempts tracking
    $_SESSION['attempts'] = $attempts;
    $_SESSION['blocked_until'] = $blocked_until;
}

// Call at start of challenge
clearChallengeSession();

// Initialize valid credentials
$valid_user = 'administrator';
$valid_pass = 'tigerpaw123';

// Rate limiting settings
define('MAX_ATTEMPTS', 3);
define('BLOCK_DURATION', 60);

// Get client IP, checking X-Forwarded-For header and handling IPv6
function getClientIP() {
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    
    if ($ip === '::1') {
        $ip = '127.0.0.1';
    }
    
    return $ip;
}

$ip = getClientIP();

// Initialize tracking arrays if not exist
if (!isset($_SESSION['attempts'])) {
    $_SESSION['attempts'] = array();
}
if (!isset($_SESSION['blocked_until'])) {
    $_SESSION['blocked_until'] = array();
}

// Initialize for this IP if not exists
if (!isset($_SESSION['attempts'][$ip])) {
    $_SESSION['attempts'][$ip] = 0;
    $_SESSION['blocked_until'][$ip] = 0;
}

// Handle login attempts
if (isset($_POST['username']) && isset($_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Check if blocked first
    if ($_SESSION['blocked_until'][$ip] > time()) {
        $error = "Too many attempts. Please try again later. Blocked for " . 
                 ($_SESSION['blocked_until'][$ip] - time()) . " more seconds.";
    } else {
        // Only increment if not blocked
        $_SESSION['attempts'][$ip]++;
        
        // Check if attempts is divisible by 3
        if ($_SESSION['attempts'][$ip] % 3 === 0) {
            $_SESSION['blocked_until'][$ip] = time() + BLOCK_DURATION;
            $error = "Too many attempts. Please try again later. Blocked for " . BLOCK_DURATION . " seconds.";
        } else {
            // Process login
            if ($username === $valid_user) {
                usleep(strlen($password) * 100000);
                if ($password === $valid_pass) {
                    $_SESSION['logged_in'] = true;
                    $_SESSION['username'] = $username;
                } else {
                    $error = "Invalid password";
                }
            } else {
                usleep(100000);
                $error = "Invalid username or password";
            }
        }
    }
    
    // Ensure session is written
    session_write_close();
}
?>

<div class="centered">
    <h1>Username Enumeration via Response Timing</h1>
    
    <div class="objective-box">
        <p>Try to find the valid username and password.</p>
        <p>Hint: Response timing might reveal more than you think...</p>
    </div>

    <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']): ?>
        <div class="congratulations">
            <h2>Congratulations!</h2>
            <p>You successfully bypassed the login!</p>
            <form method="post" action="index.php?challenge=auth">
                <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">
                <input type="hidden" name="complete" value="userenum_time">
                <button class="real-button" type="submit">Complete Challenge</button>
            </form>
        </div>
    <?php else: ?>
        <form method="post">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required><br>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required><br>
            <button class="real-button" type="submit">Login</button>
        </form>
        <?php if (isset($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include '../templates/footer.php'; ?>