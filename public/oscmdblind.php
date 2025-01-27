<?php
session_start();
include '../templates/header.php';

// Ensure the session token is set
if (!isset($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}

// List of allowed non-harmful commands
$allowed_commands = [
    'dir', 'ls', 'echo', 'type', 'cat', 'findstr', 'grep', 'cd', 'pwd', 'date', 'time', 
    'whoami', 'hostname', 'ver', 'uname', 'uptime', 'cal', 'df', 'du', 'free'
];
?>

<div class="centered">
    <h1>Blind OS Command Injection with Output Redirection</h1>
    <div class="objective-box">
        <p>Are we able to inject commands? Where does the output go? And who am i?</p>
    </div>
    <form method="post">
        <label for="command">Search:</label>
        <input type="text" id="command" name="command">
        <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">
        <button class="real-button" type="submit">Submit</button>
    </form>
</div>

<?php
// Simulate a blind OS command injection vulnerability with output redirection
if (isset($_POST['command'])) {
    $command = $_POST['command'];

    // Allow only specific non-harmful commands
    if (in_array($command, $allowed_commands)) {
        // Vulnerable command execution with output redirection
        shell_exec($command . ' > output.txt');
        echo "<center><p>no results</p></center>";
    } else {
        echo "<center><p class='error'>no results</p></center>";
    }
}

// Read the contents of the output file if requested
if (isset($_POST['command']) && ($_POST['command'] === 'cat output.txt' || $_POST['command'] === 'type output.txt')) {
    $output = file_get_contents('output.txt');
    echo "<center><pre>" . htmlspecialchars($output) . "</pre></center>";

    // Check if the output contains the current user
    $current_user = trim(shell_exec('whoami'));
    if (trim($output) === $current_user) {
        // Include the token in the form submission for challenge completion
        echo '<form method="post" action="index.php?challenge=cmdinject">';
        echo '<input type="hidden" name="token" value="' . $_SESSION['token'] . '">';
        echo '<input type="hidden" name="complete" value="oscmdblind">';
        echo '<center><button class="real-button" type="submit">Complete Challange</button></center>';
        echo '</form>';
    } else {
        echo "<p class='error'></p>";
    }
}
include '../templates/footer.php';
?>