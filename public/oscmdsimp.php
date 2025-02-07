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
?>

<div class="centered">
    <h1>OS Command Injection, Simple Case</h1>
    <div class="objective-box">
        <p>Hey you. Yes you! I'm looking for a file, can you help me find it?</p>
    </div>
    <form method="post">
        <label for="command">Search:</label>
        <input type="text" id="command" name="command">
        <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">
        <button class="real-button" type="submit">Submit</button>
    </form>
</div>

<?php
// Simulate an OS command injection vulnerability
if (isset($_POST['command'])) {
    $command = $_POST['command'];

    // Allow only 'dir' or 'ls' commands for read-only access
    if ($command === 'dir' || $command === 'ls') {
        // Vulnerable command execution
        $output = shell_exec($command);

        // Check if the output is empty
        if (!empty($output)) {
            echo "<center><pre>" . htmlspecialchars($output) . "</pre></center>";

            // Include the token in the form submission for challenge completion
            echo '<form method="post" action="index.php?challenge=cmdinject">';
            echo '<input type="hidden" name="token" value="' . $_SESSION['token'] . '">';
            echo '<input type="hidden" name="complete" value="oscmdsimp">';
            echo '<center><button class="real-button" type="submit">Complete Challenge</button></center>';
            echo '</form>';
        } else {
            echo "<center><p class='error'>no results</p></center>";
        }
    } else {
        echo "<center><p class='error'>no results</p></center>";
    }
}
include '../templates/footer.php';
?>