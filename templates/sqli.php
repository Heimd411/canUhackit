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
    <h1>SQLi Challenge</h1>
    <p>Welcome to the SQLi challenge. Here you will learn about SQL-Injection.</p>
    <p>Points Earned: <?php echo $_SESSION['points']; ?></p>
    <form method="post">
        <button class="real-button" type="submit" name="reset_points">Reset Points</button>
    </form>
</div>

<div class="grid-container">
    <div class="grid-item">
        <form method="post" action="unfiltered_sqli.php">
            <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">
            <button type="submit" name="complete" value="unfiltered_sqli">
                <h2>Unfiltered SQL-injection and login bypass</h2>
            </button>
        </form>
    </div>
    <div class="grid-item">
        <form method="post" action="union_based.php">
            <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">
            <button type="submit" name="complete" value="union_based">
                <h2>Union based with filter single quote</h2>
            </button>
        </form>
    </div>
    <div class="grid-item">
        <form method="post" action="blacklist_strip.php">
            <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">
            <button type="submit" name="complete" value="blacklist_strip">
                <h2>Blacklist that strip input (blacklist union and select)</h2>
            </button>
        </form>
    </div>
    <div class="grid-item">
        <form method="post" action="url_encoding.php">
            <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">
            <button type="submit" name="complete" value="url_encoding">
                <h2>Url Encoding and double URL encoding</h2>
            </button>
        </form>
    </div>
</div>