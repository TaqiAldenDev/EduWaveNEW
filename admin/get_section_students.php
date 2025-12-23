<?php
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if (!isset($_GET['section_id']) || empty($_GET['section_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Section ID required']);
    exit();
}

$section_id = $_GET['section_id'];

try {
    // Get section info
    $section_stmt = $pdo->prepare('SELECT s.id, s.section_name, s.max_students, COUNT(sc.student_id) as enrolled FROM sections s LEFT JOIN student_classes sc ON s.id = sc.section_id WHERE s.id = ? GROUP BY s.id');
    $section_stmt->execute([$section_id]);
    $section = $section_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$section) {
        http_response_code(404);
        echo json_encode(['error' => 'Section not found']);
        exit();
    }
    
    // Get students in this section
    $students_stmt = $pdo->prepare('SELECT u.id, u.name, u.email, sc.academic_year FROM users u JOIN student_classes sc ON u.id = sc.student_id WHERE sc.section_id = ? AND u.role = "Student" ORDER BY u.name');
    $students_stmt->execute([$section_id]);
    $students = $students_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'section' => $section,
        'students' => $students
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
?>