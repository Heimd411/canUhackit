<?php
session_start();

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

// Initialize valid credentials
$valid_user = 'manager';
$valid_pass = 'trustno1';

// Generate username and password lists
$usernames = array(
    'admin', 'administrator', 'root', 'system', 
    'security_admin', 'webmaster', 'moderator', 
    'superuser', 'master', 'supervisor',
    'manager', 'controller', 'admin123',
    'sysadmin', 'administrator2'
);

$passwords = array(
    'password123', 'admin123', 'letmein', '123456',
    'qwerty', 'password', 'TR0ub4dor&3', '12345678',
    'abc123', 'football', 'monkey', 'dragon',
    '111111', 'baseball', 'superman', 'trustno1',
    'hunter2', 'welcome', 'admin2020', 'password1',
    'master', 'hello123', 'freedom', 'whatever',
    'qazwsx', 'dragon123', 'pokemon', 'starwars',
    'letmein123', 'adminpass'
);

// Handle wordlist downloads
if (isset($_GET['download'])) {
    header('Content-Type: text/plain');
    if ($_GET['download'] === 'usernames') {
        header('Content-Disposition: attachment; filename="usernames.txt"');
        echo implode("\n", $usernames);
        exit;
    } elseif ($_GET['download'] === 'passwords') {
        header('Content-Disposition: attachment; filename="passwords.txt"');
        echo implode("\n", $passwords);
        exit;
    }
}

include '../templates/header.php';

// Clear any existing login state
unset($_SESSION['logged_in']);
unset($_SESSION['username']);

// Ensure the session token is set
if (!isset($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}

// Handle login attempts
if (isset($_POST['username']) && isset($_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (!in_array($username, $usernames)) {
        $error = "User not found";
    } elseif ($username === $valid_user) {
        if ($password === $valid_pass) {
            $_SESSION['logged_in'] = true;
            $_SESSION['username'] = $username;
        } else {
            $error = "Wrong password";
        }
    } else {
        $error = "Wrong password";
    }
}
?>

<div class="centered">
    <h1>Username Enumeration Challenge</h1>
    
    <div class="objective-box">
        <p>Try to find the valid username and password using the provided wordlists.</p>
        <p>Hint: Pay attention to different error messages!</p>
        <a href="?download=usernames">Usernames List</a>
        <p style=display:inline;> | </p>
        <a href="?download=passwords">Passwords List</a>
    </div>

    <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']): ?>
        <div class="congratulations">
            <h2>Congratulations!</h2>
            <p>You successfully enumerated the user and cracked the password!</p>
            <form method="post" action="index.php?challenge=auth">
                <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">
                <input type="hidden" name="complete" value="userenum_sub">
                <button class="real-button" type="submit">Complete Challenge</button>
            </form>
        </div>
    <?php else: ?>
        <form method="post" class="login-form">
            <input type="text" id="username" name="username" placeholder="username" required><br>
            <input type="password" id="password" name="password" placeholder="password" required><br>
            <button class="real-button" type="submit">Login</button>
        </form>
        <?php if (isset($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include '../templates/footer.php'; ?>