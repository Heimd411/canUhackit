<?php
// Ensure the session token is set
if (!isset($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}

// Initialize points if not already set
if (!isset($_SESSION['points'])) {
    $_SESSION['points'] = 0;
}

// Function to handle challenge completion
function completeChallenge($challenge) {
    if (!isset($_SESSION['completed_challenges'])) {
        $_SESSION['completed_challenges'] = [];
    }

    if (!in_array($challenge, $_SESSION['completed_challenges'])) {
        $_SESSION['completed_challenges'][] = $challenge;
        $_SESSION['points'] += 1;
    }
}

// Check if a challenge is completed
if (isset($_POST['complete']) && isset($_POST['token']) && $_POST['token'] === $_SESSION['token']) {
    completeChallenge($_POST['complete']);
}

// Reset points if requested
if (isset($_POST['reset_points'])) {
    $_SESSION['points'] = 0;
    $_SESSION['completed_challenges'] = [];
}

// Generate a new token for the session
$_SESSION['token'] = bin2hex(random_bytes(32));
?>

<div class="centered">
    <h1>Auth Challenge</h1>
    <p>Welcome to the Authentication challenge. Here you will learn about Authentication.</p>
    <p>Points Earned: <?php echo $_SESSION['points']; ?></p>
    <form method="post">
        <button class="real-button" type="submit" name="reset_points">Reset Points</button>
    </form>
</div>

<div class="grid-container">
    <div class="grid-item">
        <form method="post" action="twofabypass.php">
            <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">
            <button type="submit" name="complete" value="twofabypass">
                <h2>2FA<br>Simple Bypass</h2>
            </button>
        </form>
    </div>
    <div class="grid-item">
        <form method="post" action="userenum_sub.php">
            <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">
            <button type="submit" name="complete" value="userenum_sub">
                <h2>User Enumeration<br>Response</h2>
            </button>
        </form>
    </div>
    <div class="grid-item">
        <form method="post" action="userenum_time.php">
            <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">
            <button type="submit" name="complete" value="userenum_time">
                <h2>User Enumeration<br>Timing</h2>
            </button>
        </form>
    </div>
    <div class="grid-item">
        <form method="post" action="bf_cookie.php">
            <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">
            <button type="submit" name="complete" value="bf_cookie">
                <h2>Brute-force<br>Cookie</h2>
            </button>
        </form>
    </div>
    <div class="grid-item">
        <form method="post" action="bf_pwchange.php">
            <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">
            <button type="submit" name="complete" value="bf_pwchange">
                <h2>Brute-force<br>Password Change</h2>
            </button>
        </form>
</div>