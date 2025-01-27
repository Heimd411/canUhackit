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
    <h1>Path Traversal and CMD Injection Challenge</h1>
    <p>Welcome to the Path Traversal and CMD Injection challenge. Here you will learn about Path Traversal and OS Command Injection.</p>
    <p>Points Earned: <?php echo $_SESSION['points']; ?></p>
    <form method="post">
        <button class="real-button" type="submit" name="reset_points">Reset Points</button>
    </form>
</div>

<div class="grid-container">
    <div class="grid-item">
        <form method="post" action="fptrav.php">
            <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">
            <button type="submit" name="complete" value="fptrav">
                <h2>File Path Traversal<br>Simple Case</h2>
            </button>
        </form>
    </div>
    <div class="grid-item">
        <form method="post" action="fpvalid.php">
            <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">
            <button type="submit" name="complete" value="fpvalid">
                <h2>File Path Traversal<br>Validation of Start of Path</h2>
            </button>
        </form>
    </div>
    <div class="grid-item">
        <form method="post" action="oscmdsimp.php">
            <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">
            <button type="submit" name="complete" value="oscmdsimp">
                <h2>OS Command Injection<br>Simple Case</h2>
            </button>
        </form>
    </div>
    <div class="grid-item">
        <form method="post" action="oscmdblind.php">
            <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">
            <button type="submit" name="complete" value="oscmdblind">
                <h2>Blind OS Command Injection<br>with Output Redirection</h2>
            </button>
        </form>
    </div>
</div>