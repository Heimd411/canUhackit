<?php
session_start();

// Generate password list
$passwords = array(
    'password123', 'admin123', 'letmein', '123456',
    'qwerty', 'password', 'TR0ub4dor&3', '12345678',
    'abc123', 'football', 'monkey', 'dragon',
    '111111', 'baseball', 'superman', 'trustno1',
    'hunter2', 'welcome', 'admin2020', 'password1',
    'ship123' // Adding the valid password to the list
);

// Handle wordlist download
if (isset($_GET['download']) && $_GET['download'] === 'passwords') {
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="passwords.txt"');
    echo implode("\n", $passwords);
    exit;
}

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

// Helper function to generate stay-logged-in cookie
function generateStayLoggedInCookie($username, $password) {
    return base64_encode($username . ':' . md5($password));
}

// Login form processing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username']) && isset($_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Valid credentials for demo account
    if ($username === 'viking' && $password === 'ship123') {
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $username;
        
        // Set stay-logged-in cookie if checkbox was checked
        if (isset($_POST['stay-logged-in'])) {
            $cookie_value = generateStayLoggedInCookie($username, $password);
            setcookie('stay_logged_in', $cookie_value, time() + 3600, '/');
        }
        
        header('Location: bf_cookie.php');
        exit();
    } else {
        $error = "Invalid credentials";
    }
}

// Check for stay-logged-in cookie for target account
if (isset($_COOKIE['stay_logged_in'])) {
    $cookie = $_COOKIE['stay_logged_in'];
    
    try {
        $decoded = base64_decode($cookie);
        list($username, $hash) = explode(':', $decoded);
        
        // Target account credentials
        $target_username = 'dane';
        $target_hash = md5('letmein');
        
        if ($username === $target_username && $hash === $target_hash) {
            $_SESSION['logged_in'] = true;
            $_SESSION['username'] = $username;
            $_SESSION['challenge_complete'] = true;
        }
    } catch (Exception $e) {
        $error = "Invalid cookie format";
    }
}

// Display content based on login status
if (!isset($_SESSION['logged_in'])) {
    // Show login form and challenge description
    ?>
    <div class="centered">
        <h1>Brute-forcing a Stay-Logged-In Cookie</h1>
        <div class="objective-box">
            <h3>Objective</h3>
            <p>The stay-logged-in cookie is constructed using a predictable pattern. Your goal is to:</p>
            <ul>
                <li>Log in as the user 'viking' with password 'ship123'</li>
                <li>Analyze how the stay-logged-in cookie is constructed</li>
                <li>Brute force the cookie for user 'dane'</li>
            </ul>
            <div class="wordlist-download">
                <p style=display:inline; >Download the list of candidate passwords to try:</p>
                <a href="?download=passwords" class="download-link">Passwords List</a>
            </div>
        </div>
        
        <div class="login-form">
            <form method="post">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="stay-logged-in"> Stay logged in
                    </label>
                </div>
                <button class="real-button" type="submit">Login</button>
            </form>
        </div>
        
        <?php if (isset($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
    </div>
    <?php
} else {
    // Show logged in content
    ?>
    <div class="centered">
        <h2>Welcome <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
        <?php if (isset($_COOKIE['stay_logged_in'])): ?>
            <p>Your stay-logged-in cookie: <?php echo htmlspecialchars($_COOKIE['stay_logged_in']); ?></p>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['challenge_complete'])): ?>
            <h2>Congratulations! You've successfully brute-forced the cookie!</h2>
            <form method="post" action="index.php?challenge=auth">
                <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">
                <input type="hidden" name="complete" value="bf_cookie">
                <button class="real-button" type="submit">Complete Challenge</button>
            </form>
        <?php endif; ?>
        
        <form method="post" action="logout.php">
            <button type="submit">Logout</button>
        </form>
    </div>
    <?php
}

include '../templates/footer.php';
?>