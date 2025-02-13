<?php
session_start();

// Define encryption key and functions first
define('ENCRYPTION_KEY', 'SuperSecretKey123');

function encrypt($data) {
    $iv = str_repeat('A', 16);  // Fixed IV for predictable encryption
    return base64_encode(openssl_encrypt($data, 'AES-256-CBC', ENCRYPTION_KEY, OPENSSL_RAW_DATA, $iv));
}

function decrypt($data) {
    $iv = str_repeat('A', 16);
    return openssl_decrypt(base64_decode($data), 'AES-256-CBC', ENCRYPTION_KEY, OPENSSL_RAW_DATA, $iv);
}

// Near the top of the file, define valid roles
define('VALID_ROLES', ['consumer', 'creator']);

// Update the cookie checking code at the top
if (isset($_COOKIE['stay-logged-in']) && !isset($_SESSION['logged_in'])) {
    try {
        $parts = explode(':', $_COOKIE['stay-logged-in']);
        if (count($parts) === 3) {
            $username = decrypt($parts[0]);
            $password = decrypt($parts[1]);
            $role = decrypt($parts[2]);
            
            // Only check username exists and role
            if (isset($_SESSION['users'][$username])) {
                $_SESSION['logged_in'] = true;
                $_SESSION['username'] = $username;
                $_SESSION['role'] = $role;
            }
        }
    } catch(Exception $e) {
        // Invalid cookie - ignore
    }
}

include '../templates/header.php';

// Ensure the session token is set
if (!isset($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}

// Clear challenge-specific session variables
function clearChallengeSession() {
    unset($_SESSION['logged_in']);
    unset($_SESSION['username']);
    unset($_SESSION['role']);
    unset($_SESSION['stay_logged_in']);
}

// Only clear session when first starting the challenge
if (!isset($_SESSION['auth_bypass_oracle_started'])) {
    clearChallengeSession();
    $_SESSION['auth_bypass_oracle_started'] = true;
}

// Initialize admin account
if (!isset($_SESSION['users'])) {
    $_SESSION['users'] = array(
        'admin' => array(
            'password' => 'SuperSecret123!',
            'role' => 'admin'
        )
    );
}

// Update registration handler
if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $stay_logged_in = isset($_POST['stay_logged_in']);
    $selected_role = isset($_POST['role']) ? $_POST['role'] : 'consumer';  // Default to consumer
    
    // Validate selected role
    $role = in_array($selected_role, VALID_ROLES) ? $selected_role : 'consumer';
    
    if ($username === 'admin') {
        $message = "Cannot register admin user!";
    } else {
        $_SESSION['users'][$username] = array(
            'password' => $password,
            'role' => $role
        );
        
        // Log user in after registration
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $role;
        
        if ($stay_logged_in) {
            // Encrypt username, password and role separately
            $encrypted_username = encrypt($username);
            $encrypted_password = encrypt($password);
            $encrypted_role = encrypt($role);
            $encrypted_cookie = $encrypted_username . ':' . $encrypted_password . ':' . $encrypted_role;
            
            setcookie(
                'stay-logged-in',
                $encrypted_cookie,
                [
                    'expires' => time() + 3600,
                    'path' => '/',
                    'httponly' => true,
                    'samesite' => 'Lax'
                ]
            );
            $message = "Registration successful! Cookie set.";
        } else {
            $message = "Registration successful!";
        }
    }
}

// Normal login handler
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    if (isset($_SESSION['users'][$username]) && 
        $_SESSION['users'][$username]['password'] === $password) {
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $_SESSION['users'][$username]['role'];
        $message = "Login successful!";
    } else {
        $message = "Invalid credentials!";
    }
}

// Add logout handler
if (isset($_POST['logout'])) {
    clearChallengeSession();
    setcookie('stay-logged-in', '', time() - 3600, '/');
    header('Location: auth_bypass_oracle.php');
    exit;
}
?>

<h1 class="centered">Authentication Oracle Challenge</h1>
<div class="objective-box">
    <p>This challenge demonstrates an encryption oracle vulnerability through a "stay logged in" feature. Can you assume the role of admin?</p>
</div>

<div class="container">
    <div class="register">
        <h2>Register</h2>
        <form method="post">
            <input type="text" name="username" placeholder="Username" required><br>
            <input type="password" name="password" placeholder="Password" required><br>
            <select class="dripdown" name="role" required>
                <option value="consumer">Consumer</option>
                <option value="creator">Creator</option>
            </select><br>
            <label><input type="checkbox" name="stay_logged_in"> Stay logged in</label><br>
            <button class="real-button" type="submit" name="register">Register</button>
        </form>
        <?php if (isset($message) && isset($_POST['register'])): ?>
            <p class="message"><?php echo $message; ?></p>
        <?php endif; ?>
    </div>
    
    <div class="login">
        <?php if (!isset($_SESSION['logged_in'])): ?>
            <h2>Login</h2>
            <form method="post">
                <input type="text" name="username" placeholder="Username" required><br>
                <input type="password" name="password" placeholder="Password" required><br>
                <button class="real-button" type="submit" name="login">Login</button>
            </form>
            <?php if (isset($message) && (isset($_POST['login']))): ?>
                <p class="message"><?php echo $message; ?></p>
            <?php endif; ?>
        <?php else: ?>
            <div class="logout">
                <p>Logged in as: <?php echo htmlspecialchars($_SESSION['username']); ?> 
                   (<?php echo htmlspecialchars($_SESSION['role']); ?>)</p>
                <form method="post">
                    <button class="real-button" type="submit" name="logout">Logout</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
    
</div>

<?php if (isset($_SESSION['logged_in']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
    <div class="congratulations">
        <h2>Congratulations!</h2>
        <form method="post" action="index.php?challenge=business_logic">
            <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">
            <input type="hidden" name="complete" value="auth_bypass_oracle">
            <button class="real-button" type="submit">Complete Challenge</button>
        </form>
    </div>
<?php endif; ?>
<?php include '../templates/footer.php'; ?>