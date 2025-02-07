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
    <h1>File Path Traversal,Simple Case</h1>
    <div class="objective-box">
        <p>Your objective is simple, someone has uploaded a secret.txt file. It is most likly found in the uploads folder, find it!</p>
    </div>
    <form method="post">
        <label for="file">File:</label>
        <input type="text" id="file" name="file">
        <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">
        <button class="real-button" type="submit">Read File</button>
    </form>
</div>

<?php
// Simulate a file path traversal vulnerability
if (isset($_POST['file'])) {
    $file = $_POST['file'];

    // Check if the file is 'uploads/secret.txt'
    if ($file === '../uploads/secret.txt') {
        // Read and display the file contents
        $content = file_get_contents($file);
        echo "<center><h2><pre>" . htmlspecialchars($content) . "</pre></center>";

        // Include the token in the form submission for challenge completion
        echo '<form method="post" action="index.php?challenge=cmdinject">';
        echo '<input type="hidden" name="token" value="' . $_SESSION['token'] . '">';
        echo '<input type="hidden" name="complete" value="fptrav">';
        echo '<center><button class="real-button" type="submit">Complete Challenge</button></center>';
        echo '</form>';
    } else {
        // Display permission denied message
        echo "<center><p>Permission Denied</p></center>";
    }
}

include '../templates/footer.php';
?>