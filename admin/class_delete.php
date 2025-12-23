<?php
require_once __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header('Location: ../login.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: classes.php');
    exit();
}

$class_id = $_GET['id'];

try {
    // Also delete associated student classes
    $stmt = $pdo->prepare('DELETE FROM student_classes WHERE class_id = ?');
    $stmt->execute([$class_id]);

    $stmt = $pdo->prepare('DELETE FROM classes WHERE id = ?');
    $stmt->execute([$class_id]);
} catch (PDOException $e) {
    // handle error
}

header('Location: classes.php');
exit();
?>
