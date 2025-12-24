<?php
require_once __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Teacher') {
    header('Location: ../login.php');
    exit();
}

$type = $_REQUEST['type'] ?? '';
$format = $_REQUEST['format'] ?? 'csv';

switch($type) {
    case 'classes':
        exportTeacherClasses($format);
        break;
    default:
        die('Invalid export type: ' . htmlspecialchars($type));
}

function exportTeacherClasses($format) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare('
            SELECT
                classes.id as class_id,
                classes.grade_name,
                subjects.id as subject_id,
                subjects.name AS subject_name,
                (SELECT COUNT(sc.student_id) FROM student_classes sc WHERE sc.class_id = classes.id AND sc.academic_year = YEAR(CURDATE())) AS student_count,
                (SELECT COUNT(*) FROM attendance a WHERE a.class_id = classes.id AND a.subject_id = subjects.id AND a.attend_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)) AS attendance_records,
                (SELECT COUNT(*) FROM grades g WHERE g.subject_id = subjects.id) AS grade_records
            FROM teacher_assignments
            JOIN classes ON teacher_assignments.class_id = classes.id
            JOIN subjects ON teacher_assignments.subject_id = subjects.id
            WHERE teacher_assignments.teacher_id = ?
            ORDER BY classes.grade_name, subjects.name
        ');
        $stmt->execute([$_SESSION['user_id']]);
        $classes = $stmt->fetchAll();
        
        $filename = 'teacher_classes_' . date('Y-m-d');
        
        $data = [];
        $data[] = ['Class ID', 'Class', 'Subject', 'Current Students', 'Attendance Records (30 days)', 'Grade Records'];
        
        foreach ($classes as $class) {
            $data[] = [
                $class['class_id'],
                $class['grade_name'],
                $class['subject_name'],
                $class['student_count'],
                $class['attendance_records'],
                $class['grade_records']
            ];
        }
        
        if ($format === 'txt') {
            exportToTXT($data, $filename . '.txt');
        } else {
            exportToCSV($data, $filename . '.csv');
        }
        exit;
        
    } catch (Exception $e) {
        die('Error generating export: ' . $e->getMessage());
    }
}

function exportToCSV($data, $filename) {
    header('Content-Type: text/csv;charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    foreach ($data as $row) {
        fputcsv($output, $row);
    }
    
    fclose($output);
}

function exportToTXT($data, $filename) {
    header('Content-Type: text/plain;charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    foreach ($data as $row) {
        echo implode("\t", $row) . "\n";
    }
}
?>