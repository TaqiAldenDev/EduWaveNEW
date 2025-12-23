<?php
require_once __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header('Location: ../login.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: users_list.php');
    exit();
}

$user_id = $_GET['id'];

try {
    // Handle cascade deletes for different user roles
    // Delete related records based on user role
    $stmt = $pdo->prepare('DELETE FROM grades WHERE student_id = ?');
    $stmt->execute([$user_id]);

    $stmt = $pdo->prepare('DELETE FROM attendance WHERE student_id = ?');
    $stmt->execute([$user_id]);

    $stmt = $pdo->prepare('DELETE FROM teacher_assignments WHERE teacher_id = ?');
    $stmt->execute([$user_id]);

    $stmt = $pdo->prepare('DELETE FROM parent_student WHERE parent_id = ? OR student_id = ?');
    $stmt->execute([$user_id, $user_id]);

    $stmt = $pdo->prepare('DELETE FROM student_classes WHERE student_id = ?');
    $stmt->execute([$user_id]);

    $stmt = $pdo->prepare('DELETE FROM submissions WHERE student_id = ?');
    $stmt->execute([$user_id]);

    $stmt = $pdo->prepare('DELETE FROM assignments WHERE teacher_id = ?');
    $stmt->execute([$user_id]);

    $stmt = $pdo->prepare('DELETE FROM certificates WHERE student_id = ? OR issued_by = ?');
    $stmt->execute([$user_id, $user_id]);



    $stmt = $pdo->prepare('DELETE FROM notifications WHERE user_id = ?');
    $stmt->execute([$user_id]);

    $stmt = $pdo->prepare('DELETE FROM calendar_events WHERE user_id = ?');
    $stmt->execute([$user_id]);

    $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
    $stmt->execute([$user_id]);
} catch (PDOException $e) {
    // handle error
}

header('Location: users_list.php');
exit();
?>
