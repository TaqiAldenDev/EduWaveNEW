<?php
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if (!isset($_GET['class_id']) || empty($_GET['class_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Class ID required']);
    exit();
}

$class_id = $_GET['class_id'];

try {
    $stmt = $pdo->prepare('SELECT id, name FROM subjects WHERE class_id = ? ORDER BY name');
    $stmt->execute([$class_id]);
    $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($subjects);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
?>