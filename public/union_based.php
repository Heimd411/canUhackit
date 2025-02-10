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

// Simulate a vulnerable search form
if (isset($_POST['search'])) {
    $search = $_POST['search'];

    // Vulnerable SQL query without single quote filter
    $query = "SELECT * FROM products WHERE product LIKE '%$search%'";
    
    // Debug output
    echo "<!-- Debug: " . htmlspecialchars($query) . " -->";
    
    try {
        $result = $conn->query($query);
        
        // Check if the query returns any rows
        if ($result && $result->num_rows > 0) {
            $search_results = "<div class='centered'><h2>Search Results:</h2><ul>";
            while ($row = $result->fetch_assoc()) {
                $search_results .= "<li>" . htmlspecialchars($row['product']) . ": " . htmlspecialchars($row['description']) . "</li>";
            }
            $search_results .= "</ul></div>";
        } else {
            $error = "No results found for '$search'";
        }
    } catch (mysqli_sql_exception $e) {
        $error = "Search failed: " . $e->getMessage(); // Detailed error for debugging
        error_log("SQL Error: " . $e->getMessage()); // Log actual error
    }
}

// Check if the admin password is supplied correctly
if (isset($_POST['admin_password'])) {
    $admin_password = $_POST['admin_password'];

    // Check the admin password
    $query = "SELECT * FROM users WHERE username = 'admin' AND password = '$admin_password'";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        echo '<div class="centered">';
        echo '<h2>Congratulations!</h2>';
        echo '<p>You\'ve found the admin password!</p>';
        echo '<form method="post" action="index.php?challenge=sqli">';
        echo '<input type="hidden" name="token" value="' . $_SESSION['token'] . '">';
        echo '<input type="hidden" name="complete" value="union_based">';
        echo '<button class="real-button" type="submit">Complete Challenge</button>';
        echo '</form>';
        echo '</div>';
        exit();
    } else {
        $error = "Incorrect admin password.";
    }
}
?>

<div class="centered">
    <h1>Union based with filter single quote</h1>
    <div class="objective-box">
        <p>It has to be here somewhere? Password!</p>
    </div>
    <form method="post">
        <label for="search">Search:</label>
        <input type="text" id="search" name="search">
        <button class="real-button" type="submit">Search</button>
    </form>
    <?php if (isset($search_results)) { echo $search_results; } ?>
    <form method="post">
        <label for="admin_password">Admin Password:</label>
        <input type="password" id="admin_password" name="admin_password">
        <button class="real-button" type="submit">Submit</button>
    </form>
    <?php if (isset($error)) { echo "<p class='error'>$error</p>"; } ?>
    <?php if (isset($success)) { echo "<p class='success'>$success</p>"; } ?>
</div>

<?php
include '../templates/footer.php';
$conn->close();
?>