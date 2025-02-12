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
    <h1>File Path Traversal, Validation of Start of Path</h1>
    <div class="objective-box">
        <p>Your objective is to find your current working directory is.(dont overthink)</p>
    </div>
    <form method="post">
        <label for="command">Search:</label>
        <input type="text" id="command" name="command">
        <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">
        <button class="real-button" type="submit">Submit</button>
    </form>
    <?php if (isset($error)) { echo "<p class='error'>$error</p>"; } ?>
</div>

<?php
// Simulate a file path traversal vulnerability with validation
if (isset($_POST['command'])) {
    $command = $_POST['command'];

    // Allow only the 'cd' command for Windows
    if ($command === 'cd' || $command === 'pwd') {
        // Execute the 'cd' command
        $output = shell_exec($command);
        $output = trim($output);
        // Use a regular expression to extract the relevant part of the path
        if (preg_match('/(\/var\/www\/html\/public|\/www\/public|\\\www\\\public)$/', $output, $matches)) {
            $output = $matches[0];
        }
        echo "<center><pre>" . htmlspecialchars($output) . "</pre></center>";

        // Check if the output matches the expected path
        if ($output === '/www/public' || $output === '\www\public' || $output === '/var/www/html/public') {
            // Include the token in the form submission for challenge completion
            echo '<form method="post" action="index.php?challenge=cmdinject">';
            echo '<input type="hidden" name="token" value="' . $_SESSION['token'] . '">';
            echo '<input type="hidden" name="complete" value="fpvalid">';
            echo '<center><button class="real-button" type="submit">Complete Challenge</button></center>';
            echo '</form>';
        } else {
            $error = "<center>Invalid path</center>";
        }
    } else {
        $error = "<center>no results</center>";
    }
    // Display the error message if set
    if (isset($error)) {
        echo "<p class='error'>$error</p>";
    }
}
?>

<?php
include '../templates/footer.php';
?>