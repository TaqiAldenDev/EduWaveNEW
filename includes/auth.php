<?php
require_once __DIR__.'/config.php';

// Note: Had to add this remember_me check to fix the auto-login issue
if (!isset($_SESSION['role']) && isset($_COOKIE['remember_me'])) {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE remember_token = ?');
    $stmt->execute([$_COOKIE['remember_me']]);
    $user = $stmt->fetch();

    if ($user) {
        $_SESSION['role'] = $user['role'];
        $_SESSION['colour'] = $user['color_theme'];
        $_SESSION['user_id'] = $user['id'];
    }
}

if(!isset($_SESSION['role'])){header('Location: /eduwave/index.php'); exit();}
$role=$_SESSION['role'];
$colour=$_SESSION['colour'];
?>