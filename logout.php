<?php
require_once __DIR__ . '/includes/config.php';
session_destroy();
if (isset($_COOKIE['remember_me'])) {
    unset($_COOKIE['remember_me']);
    setcookie('remember_me', '', time() - 3600, '/');
}
header('Location: index.php');
exit();
?>