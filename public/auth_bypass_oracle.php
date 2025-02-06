<?php
session_start();
include '../templates/header.php';

// Encryption key and functions
define('ENCRYPTION_KEY', 'SuperSecretKey123');

function encrypt($data) {
    $iv = openssl_random_pseudo_bytes(16);
    $encrypted = openssl_encrypt($data, 'AES-256-CBC', ENCRYPTION_KEY, 0, $iv);
    return base64_encode($iv . $encrypted);
}

function decrypt($data) {
    $data = base64_decode($data);
    $iv = substr($data, 0, 16);
    $encrypted = substr($data, 16);
    return openssl_decrypt($encrypted, 'AES-256-CBC', ENCRYPTION_KEY, 0, $iv);
}

// Initialize users
if (!isset($_SESSION['users'])) {
    $_SESSION['users'] = array(
        'admin' => array(
            'password' => encrypt('adminpass123'),
            'role' => 'admin'
        )
    );
}

// Handle registration
if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $encrypted_password = encrypt($password);
    $_SESSION['users'][$username] = array(
        'password' => $encrypted_password,
        'role' => 'user'
    );
    
    // Vulnerability: Show encrypted credentials
    $message = "Registration successful! Encrypted credentials: " . $encrypted_password;
}

// Handle login
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $encrypted_password = $_POST['encrypted_password'];
    
    if (isset($_SESSION['users'][$username]) && 
        $_SESSION['users'][$username]['password'] === $encrypted_password) {
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $_SESSION['users'][$username]['role'];
        $message = "Login successful!";
    } else {
        $message = "Invalid credentials!";
    }
}

?>
<h1 class="centered">Authentication Oracle Challenge</h1>
<div class="objective-box">
        <p>This challenge demonstrates an encryption oracle vulnerability. Try to login as admin!</p>
    </div>
<div class="container">
    <div class="register">
        <h2>Register</h2>
        <form method="post">
            <input type="text" name="username" placeholder="Username" required><br>
            <input type="password" name="password" placeholder="Password" required><br>
            <button class="real-button" type="submit" name="register">Register</button>
        </form>
        <?php if (isset($message) && isset($_POST['register'])): ?>
            <p class="message"><?php echo $message; ?></p>
        <?php endif; ?>
    </div>
    <div class="login">
        <h2>Login</h2>
        <form method="post">
            <input type="text" name="username" placeholder="Username" required><br>
            <input type="text" name="encrypted_password" placeholder="Encrypted Password" required><br>
            <button class="real-button" type="submit" name="login">Login</button>
        </form>
        <?php if (isset($message) && isset($_POST['login'])): ?>
            <p class="message"><?php echo $message; ?></p>
        <?php endif; ?>
    </div>
</div>

<?php if (isset($_SESSION['logged_in']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <div class="congratulations">
            <h2>Congratulations!</h2>
            <form method="post" action="index.php?challenge=encryption">
                <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">
                <input type="hidden" name="complete" value="auth_bypass_oracle">
                <button class="real-button" type="submit">Complete Challenge</button>
            </form>
        </div>
    <?php endif; ?>
</div>
<?php include '../templates/footer.php'; ?>