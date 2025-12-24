<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

echo "Session Status: " . session_status() . "<br>";
echo "Session ID: " . session_id() . "<br>";
echo "User ID: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'Not set') . "<br>";
echo "Role: " . (isset($_SESSION['role']) ? $_SESSION['role'] : 'Not set') . "<br>";
?>