<?php
$servername = "db"; // localhost | db
$username = "UbErHaxor";  // www | UbErHaxor
$password = "SuPeL33tgoAt"; // h4ck3rs4l1F3 |SuPeL33tgoAt
$dbname = "hackin"; // hackbase | hackin

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>