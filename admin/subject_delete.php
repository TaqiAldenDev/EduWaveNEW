<?php
require_once __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header('Location: ../login.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: subjects.php');
    exit();
}

$subject_id = $_GET['id'];

try {
    // Also delete associated assignments
    $stmt = $pdo->prepare('DELETE FROM teacher_assignments WHERE subject_id = ?');
    $stmt->execute([$subject_id]);

    // Also delete associated grades
    $stmt = $pdo->prepare('DELETE FROM grades WHERE subject_id = ?');
    $stmt->execute([$subject_id]);

    $stmt = $pdo->prepare('DELETE FROM subjects WHERE id = ?');
    $stmt->execute([$subject_id]);
} catch (PDOException $e) {
    // handle error
}

header('Location: subjects.php');
exit();
?>
