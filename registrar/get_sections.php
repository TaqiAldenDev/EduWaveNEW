<?php
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Registrar') {
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
    $stmt = $pdo->prepare('SELECT s.id, s.section_name, s.max_students, COALESCE(COUNT(sc.student_id), 0) as enrolled FROM sections s LEFT JOIN student_classes sc ON s.id = sc.section_id WHERE s.class_id = ? GROUP BY s.id ORDER BY s.section_name');
    $stmt->execute([$class_id]);
    $sections = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($sections);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
?>