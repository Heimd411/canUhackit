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
    <h1>Business Logic Challenge</h1>
    <p>Welcome to the Business Logic challenge. Here you will learn about various business logic vulnerabilities.</p>
    <p>Points Earned: <?php echo $_SESSION['points']; ?></p>
    <form method="post">
        <button class="real-button" type="submit" name="reset_points">Reset Points</button>
    </form>
</div>

<div class="grid-container">
    <div class="grid-item">
        <form method="post" action="excessive_trust.php">
            <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">
            <button type="submit" name="complete" value="excessive_trust">
                <h2>Excessive Trust<br>in Client-Side Controls</h2>
            </button>
        </form>
    </div>
    <div class="grid-item">
        <form method="post" action="auth_bypass_state.php">
            <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">
            <button type="submit" name="complete" value="auth_bypass_state">
                <h2>Authentication Bypass<br>via Flawed State Machine</h2>
            </button>
        </form>
    </div>
    <div class="grid-item">
        <form method="post" action="insufficient_workflow.php">
            <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">
            <button type="submit" name="complete" value="insufficient_workflow">
                <h2>Insufficient<br>Workflow Validation</h2>
            </button>
        </form>
    </div>
    <div class="grid-item">
        <form method="post" action="infinite_money.php">
            <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">
            <button type="submit" name="complete" value="infinite_money">
                <h2>Infinite Money<br>Logic Flaw</h2>
            </button>
        </form>
    </div>
    <div class="grid-item">
        <form method="post" action="auth_bypass_oracle.php">
            <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">
            <button type="submit" name="complete" value="auth_bypass_oracle">
                <h2>Authentication Bypass<br>via Encryption Oracle</h2>
            </button>
        </form>
    </div>
</div>