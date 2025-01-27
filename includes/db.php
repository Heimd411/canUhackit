<?php
$servername = "localhost";
$username = "www";
$password = "h4ck3rs4l1F3";
$dbname = "hackbase";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>