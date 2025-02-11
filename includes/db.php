<?php
$servername = "localhost"; // localhost | db
$username = "www";  // www | UbErHaxor
$password = "h4ck3rs4l1F3"; // h4ck3rs4l1F3 |SuPeL33tgoAt
$dbname = "hackbase"; // hackbase | hackin

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>