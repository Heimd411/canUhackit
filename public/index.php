<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Define root path
define('ROOT_PATH', dirname(__DIR__));

// Include configuration and database files
require_once ROOT_PATH . '/includes/config.php';
require_once ROOT_PATH . '/includes/db.php';

// Start session
session_start();

// Include templates
include '../templates/header.php';

// Determine which template to include based on the query parameter
$template = isset($_GET['challenge']) ? $_GET['challenge'] : 'select_challenge';
$template_path = '../templates/' . $template . '.php';

// Check if the template file exists
if (file_exists($template_path)) {
    include $template_path;
} else {
    echo '<div class="centered"><h1>404 - Page Not Found</h1></div>';
}

include '../templates/footer.php';
?>