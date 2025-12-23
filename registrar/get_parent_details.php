<?php
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Registrar') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if (!isset($_GET['parent_id']) || empty($_GET['parent_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Parent ID required']);
    exit();
}

$parent_id = $_GET['parent_id'];

try {
    // Get parent information
    $parent_stmt = $pdo->prepare('SELECT id, name, email, created_at FROM users WHERE id = ? AND role = "Parent"');
    $parent_stmt->execute([$parent_id]);
    $parent = $parent_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$parent) {
        http_response_code(404);
        echo json_encode(['error' => 'Parent not found']);
        exit();
    }
    
    // Get linked children with class information
    $children_stmt = $pdo->prepare('SELECT u.name as student_name, c.grade_name, ps.relation FROM parent_student ps JOIN users u ON ps.student_id = u.id LEFT JOIN student_classes sc ON u.id = sc.student_id LEFT JOIN classes c ON sc.class_id = c.id WHERE ps.parent_id = ? ORDER BY u.name');
    $children_stmt->execute([$parent_id]);
    $children = $children_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'parent' => $parent,
        'children' => $children
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
?>