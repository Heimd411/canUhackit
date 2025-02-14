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
        
        // Display search results
        if ($result && $result->num_rows > 0) {
            $search_results = "<div class='results'><h3>Search Results:</h3><ul>";
            while ($row = $result->fetch_assoc()) {
                $search_results .= "<li>" . htmlspecialchars($row['product']) . ": " . 
                                 htmlspecialchars($row['description']) . "</li>";
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

    // Check if the submitted secret matches one in the database
    $query = "SELECT * FROM secrets WHERE secret = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $secret);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        echo '<div class="centered">';
        echo '<h2>Congratulations!</h2>';
        echo '<p>You\'ve found the correct secret!</p>';
        echo '<form method="post" action="index.php?challenge=sqli">';
        echo '<input type="hidden" name="token" value="' . $_SESSION['token'] . '">';
        echo '<input type="hidden" name="complete" value="blacklist_strip">';
        echo '<button class="real-button" type="submit">Complete Challenge</button>';
        echo '</form>';
        echo '</div>';
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
        <input type="text" id="search" name="search" placeholder="Search products">
        <button class="real-button" type="submit">Search</button>
    </form>
    <?php if (isset($search_results)) { echo $search_results; } ?>
    <form method="post">
        <input type="text" id="secret" name="secret" placeholder="Secret">
        <button class="real-button" type="submit">Submit</button>
    </form>
    <?php if (isset($error)) { echo "<p class='error'>$error</p>"; } ?>
    <?php if (isset($success)) { echo "<p class='success'>$success</p>"; } ?>
</div>

<?php
include '../templates/footer.php';
$conn->close();
?>