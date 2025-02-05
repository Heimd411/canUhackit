<?php
session_start();
include '../templates/header.php';

// Ensure the session token is set
if (!isset($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}

// Simulate a 2FA login form
if (isset($_POST['username']) && isset($_POST['password']) && isset($_POST['2fa_code'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $fa_code = $_POST['2fa_code'];

    // Simulate checking credentials and 2FA code
    if ($username === 'admin' && $password === 'password' && $fa_code === '123456') {
        echo '<form method="post" action="index.php?challenge=auth">';
        echo '<input type="hidden" name="token" value="' . $_SESSION['token'] . '">';
        echo '<input type="hidden" name="complete" value="twofabypass">';
        echo '<button class="real-button" type="submit">Complete Challenge</button>';
        echo '</form>';
    } else {
        $error = "Invalid credentials or 2FA code";
    }
}
?>

<div class="centered">
    <h1>2FA Simple Bypass</h1>
    <div class="objective-box">
        <p>Some%ne is playing tr%cks on me! bm90IGFuc3dlcg==</p>
    </div>
    <form method="post">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username"><br>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password"><br>
        <label for="2fa_code">2FA Code:</label>
        <input type="text" id="2fa_code" name="2fa_code"><br>
        <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">
        <button class="real-button" type="submit">Login</button>
    </form>
    <?php if (isset($error)) { echo "<p class='error'>$error</p>"; } ?>
</div>

<?php
include '../templates/footer.php';
?>