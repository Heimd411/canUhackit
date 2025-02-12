<?php
session_start();

// Process all redirects before including header.php
// Handle login
if (isset($_POST['username']) && isset($_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Simulate user authentication
    if ($username === 'ufo' && $password === 'alien') {
        $_SESSION['authenticated'] = true;
        $_SESSION['role_selected'] = false;
        header('Location: auth_bypass_state.php?select_role=1');
        exit;
    }
}

// Handle role selection
if (isset($_POST['select_role']) && isset($_SESSION['authenticated'])) {
    $_SESSION['role'] = $_POST['select_role'];
    $_SESSION['role_selected'] = true;
    header('Location: auth_bypass_state.php');
    exit;
}

// Handle logout
if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: auth_bypass_state.php');
    exit;
}

// Now include header and rest of the page
include '../templates/header.php';

// Ensure the session token is set
if (!isset($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}

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

// Only clear session when first starting the challenge
if (!isset($_SESSION['auth_bypass_state_started'])) {
    clearChallengeSession();
    $_SESSION['auth_bypass_state_started'] = true;
}

// Initialize message
$message = "";

// Set error message for failed login
if (isset($_POST['username'])) {
    $message = "Invalid username or password.";
}

// Check authentication state
if (isset($_SESSION['authenticated'])) {
    if (!isset($_SESSION['role_selected']) || $_SESSION['role_selected'] === false) {
        // Show role selection form
        if (isset($_GET['select_role'])) {
            echo '<div class="centered">';
            echo '<h1>Select Your Role</h1>';
            echo '<form method="post">';
            echo '<select name="select_role">';
            echo '<option value="user">User</option>';
            echo '</select>';
            echo '<button class="real-button" type="submit">Continue</button>';
            echo '</form>';
            echo '</div>';
            exit;
        } else {
            $_SESSION['role'] = 'admin'; // Vulnerability: Default to admin if role selection is skipped
        }
    }
    
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        echo '<div class="centered"><h1>Welcome, Admin!</h1>';
        echo '<p>You have access to the admin panel.</p>';
        echo '<form method="post" action="index.php?challenge=business_logic">';
        echo '<input type="hidden" name="token" value="' . $_SESSION['token'] . '">';
        echo '<input type="hidden" name="complete" value="auth_bypass_state">';
        echo '<center><button class="real-button" type="submit">Complete Challenge</button></center>';
        echo '</form>';
        echo '<form method="post">';
        echo '<button class="real-button" type="submit" name="logout">Logout</button>';
        echo '</form>';
        echo '</div>';
    } else {
        echo '<div class="centered"><h1>Welcome, User!</h1>';
        echo '<p>You do not have access to the admin panel.</p>';
        echo '<form method="post">';
        echo '<button class="real-button" type="submit" name="logout">Logout</button>';
        echo '</form>';
        echo '</div>';
    }
} else {
    // Display login form
    echo '<div class="centered">';
    echo '<h1>Authentication Bypass via Flawed State Machine</h1>';
    echo '<div class="objective-box">';
    echo '<p>The admin account is well protected, but maybe there is a way to access it anyway?</p>';
    echo '<p>User: ufo<br>Password: alien</p>';
    echo '</div>';
    echo '<form method="post">';
    echo '<label for="username">Username:</label><br>';
    echo '<input type="text" id="username" name="username" required><br>';
    echo '<label for="password">Password:</label><br>';
    echo '<input type="password" id="password" name="password" required><br>';
    echo '<button class="real-button" type="submit">Login</button>';
    echo '</form>';
    echo '<p class="message">' . $message . '</p>';
    echo '</div>';
}
?>