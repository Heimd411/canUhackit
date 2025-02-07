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

// Initialize points if not already set
if (!isset($_SESSION['points'])) {
    $_SESSION['points'] = 0;
}

// Function to handle challenge completion
function completeChallenge($challenge) {
    if (!isset($_SESSION['completed_challenges'])) {
        $_SESSION['completed_challenges'] = [];
    }

    if (!in_array($challenge, $_SESSION['completed_challenges'])) {
        $_SESSION['completed_challenges'][] = $challenge;
        $_SESSION['points'] += 1;
    }
}

// Simulate a vulnerable search form
if (isset($_POST['search'])) {
    $search = $_POST['search'];

    // Blacklist certain keywords
    $blacklist = ['union', 'select'];
    foreach ($blacklist as $word) {
        $search = str_ireplace($word, '', $search);
    }

    // Vulnerable SQL query
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

// Check if the secret is retrieved
if (isset($_POST['secret'])) {
    $secret = $_POST['secret'];

    // Check the secret
    $query = "SELECT * FROM secrets WHERE secret = '$secret'";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        completeChallenge('blacklist_strip');
        header('Location: index.php?challenge=sqli&complete=blacklist_strip');
        exit();
    } else {
        $error = "Incorrect secret";
    }
}
?>

<div class="centered">
    <h1>Blacklist that strip input (blacklist union and select)</h1>
    <div class="objective-box">
        <p>There are secrets hiding in the database, can you uncover the secret?</p>
    </div>
    <form method="post">
        <label for="search">Search:</label>
        <input type="text" id="search" name="search">
        <button class="real-button" type="submit">Search</button>
    </form>
    <?php if (isset($search_results)) { echo $search_results; } ?>
    <form method="post">
        <label for="secret">Secret:</label>
        <input type="text" id="secret" name="secret">
        <button class="real-button" type="submit">Submit</button>
    </form>
    <?php if (isset($error)) { echo "<p class='error'>$error</p>"; } ?>
    <?php if (isset($success)) { echo "<p class='success'>$success</p>"; } ?>
</div>

<?php
include '../templates/footer.php';
$conn->close();
?>