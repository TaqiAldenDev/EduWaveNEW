<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Simple session check without database
if (!isset($_SESSION['user_id'])) {
    echo "Session not found. User not logged in.<br>";
    echo "Session status: " . session_status() . "<br>";
    echo "Session data: " . print_r($_SESSION, true) . "<br>";
    die();
} else {
    echo "Session found! User ID: " . $_SESSION['user_id'] . "<br>";
    
    if (isset($_GET['test'])) {
        echo "Test parameter received: " . $_GET['test'];
    }
}
?>