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

// Check if the decoded result is submitted
if (isset($_POST['decoded_result'])) {
    $decoded_result = $_POST['decoded_result'];

    // Check the decoded result
    $query = "SELECT * FROM urlencode WHERE code = '" . base64_encode($decoded_result) . "'";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        echo '<div class="centered">';
        echo '<h2>Congratulations!</h2>';
        echo '<p>You\'ve successfully decoded the message!</p>';
        echo '<form method="post" action="index.php?challenge=sqli">';
        echo '<input type="hidden" name="token" value="' . $_SESSION['token'] . '">';
        echo '<input type="hidden" name="complete" value="url_encoding">';
        echo '<button class="real-button" type="submit">Complete Challenge</button>';
        echo '</form>';
        echo '</div>';
        exit();
    } else {
        $error = "Incorrect decoded result.";
    }
}
?>

<div class="centered">
    <h1>Url Encoding and double URL encoding</h1>
    <div class="objective-box">
        <p>Some%ne is playing tr%cks on me! ZmluZCB0aGUgY29kZSEgbm90IHRoZSBzZWNyZXQh</p>
    </div>
    <form method="get">
        <label for="search">Search:</label>
        <input type="text" id="search" name="search">
        <button class="real-button" type="submit">Search</button>
    </form>
    <?php if (isset($search_results)) { echo $search_results; } ?>
    <form method="post">
        <label for="decoded_result">Decoded Result:</label>
        <input type="text" id="decoded_result" name="decoded_result">
        <button class="real-button" type="submit">Submit</button>
    </form>
    <?php if (isset($error)) { echo "<p class='error'>$error</p>"; } ?>
    <?php if (isset($success)) { echo "<p class='success'>$success</p>"; } ?>
</div>

<?php
// Simulate a vulnerable URL parameter
if (isset($_GET['search'])) {
    // Remove certain characters
    $search = str_replace(["'", " "], "", $_GET['search']);
    $search = urldecode($search);

    

    // Vulnerable SQL query
    $query = "SELECT * FROM products WHERE product LIKE '%$search%'";
    
    try {
        $result = $conn->query($query);
        
        // Check if the query returns any rows
        if ($result && $result->num_rows > 0) {
            echo "<div class='centered'><h2>Search Results:</h2><ul>";
            while ($row = $result->fetch_assoc()) {
                echo "<li>" . htmlspecialchars($row['product']) . ": " . htmlspecialchars($row['description']) . "</li>";
            }
            echo "</ul></div>";
        } else {
            $error = "No results found for '$search'";
        }
    } catch (mysqli_sql_exception $e) {
        $error = "Search failed: " . $e->getMessage(); // Detailed error for debugging
        error_log("SQL Error: " . $e->getMessage()); // Log actual error
    }
}

include '../templates/footer.php';
$conn->close();
?>