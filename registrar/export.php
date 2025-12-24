<?php
require_once __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Registrar') {
    header('Location: ../login.php');
    exit();
}

$type = $_REQUEST['type'] ?? '';
$format = $_REQUEST['format'] ?? 'csv';

switch($type) {
    case 'students':
        exportStudents($format);
        break;
    case 'parents':
        exportParents($format);
        break;
    default:
        die('Invalid export type: ' . htmlspecialchars($type));
}

function exportStudents($format) {
    global $pdo;
    
    try {
        $query = 'SELECT u.id, u.name, u.email, u.created_at, c.grade_name, s.section_name 
                  FROM users u 
                  LEFT JOIN student_classes sc ON u.id = sc.student_id 
                  LEFT JOIN classes c ON sc.class_id = c.id 
                  LEFT JOIN sections s ON sc.section_id = s.id 
                  WHERE u.role = "Student" 
                  ORDER BY u.name';
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $students = $stmt->fetchAll();
        
        $filename = 'students_management_' . date('Y-m-d');
        
        $data = [];
        $data[] = ['ID', 'Name', 'Email', 'Class', 'Section', 'Joined Date'];
        
        foreach ($students as $student) {
            $data[] = [
                $student['id'],
                $student['name'],
                $student['email'],
                $student['grade_name'] ?? 'N/A',
                $student['section_name'] ?? 'N/A',
                date('Y-m-d H:i:s', strtotime($student['created_at']))
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

function exportParents($format) {
    global $pdo;
    
    try {
        $query = 'SELECT u.id, u.name, u.email, u.created_at, COUNT(ps.student_id) as children_count 
                  FROM users u 
                  LEFT JOIN parent_student ps ON u.id = ps.parent_id 
                  WHERE u.role = "Parent" 
                  GROUP BY u.id 
                  ORDER BY u.name';
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $parents = $stmt->fetchAll();
        
        $filename = 'parents_management_' . date('Y-m-d');
        
        $data = [];
        $data[] = ['ID', 'Name', 'Email', 'Children Count', 'Registration Date'];
        
        foreach ($parents as $parent) {
            $data[] = [
                $parent['id'],
                $parent['name'],
                $parent['email'],
                $parent['children_count'],
                date('Y-m-d H:i:s', strtotime($parent['created_at']))
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