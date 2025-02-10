<?php
session_start();
include '../templates/header.php';
include '../includes/db.php';

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
    <h1>Unfiltered SQL-injection and login bypass</h1>
    <div class="objective-box">
        <p>Password? What password? Maybe i don't need a password?</p>
    </div>
    <form method="post">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username">
        <label for="password">Password:</label>
        <input type="password" id="password" name="password">
        <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">
        <button class="real-button" type="submit">Login</button>
    </form>
    <?php if (isset($error)) { echo "<p class='error'>$error</p>"; } ?>
</div>

<?php
// Simulate a vulnerable login form
if (isset($_POST['username']) && isset($_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Vulnerable SQL query
    $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
    
    // Debug output
    echo "<!-- Debug: " . htmlspecialchars($query) . " -->";
    
    try {
        $result = $conn->query($query);
        
        // Check if the query returns any rows
        if ($result && $result->num_rows > 0) {
            echo '<div class="centered">';
            echo '<h2>Congratulations!</h2>';
            echo '<p>You\'ve successfully bypassed the login!</p>';
            echo '<form method="post" action="index.php?challenge=sqli">';
            echo '<input type="hidden" name="token" value="' . $_SESSION['token'] . '">';
            echo '<input type="hidden" name="complete" value="unfiltered_sqli">';
            echo '<button class="real-button" type="submit">Complete Challenge</button>';
            echo '</form>';
            echo '</div>';
            exit();
        } else {
            $error = "Invalid credentials";
        }
    } catch (mysqli_sql_exception $e) {
        $error = "Login failed"; // Generic error for users
        error_log("SQL Error: " . $e->getMessage()); // Log actual error
    }
}
include '../templates/footer.php';
$conn->close();
?>