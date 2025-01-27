<?php
session_start();
include '../templates/header.php';

// Ensure the session token is set
if (!isset($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}

// Simulate a login form with response timing
if (isset($_POST['username']) && isset($_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Simulate checking credentials with response timing
    if ($username === 'admin') {
        sleep(2); // Simulate delay for valid username
        if ($password === 'password') {
            echo '<form method="post" action="index.php?complete=userenum_time">';
            echo '<input type="hidden" name="token" value="' . $_SESSION['token'] . '">';
            echo '<input type="hidden" name="complete" value="userenum_time">';
            echo '<button class="real-button" type="submit">Complete Challenge</button>';
            echo '</form>';
        } else {
            $error = "Invalid password";
        }
    } else {
        $error = "Invalid username or password";
    }
}
?>

<div class="centered">
    <h1>Username Enumeration via Response Timing</h1>
    <div class="objective-box">
        <p>Some%ne is playing tr%cks on me! bm90IGFuc3dlcg==</p>
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
include '../templates/footer.php';
?>