<?php
require_once __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header('Location: ../login.php');
    exit();
}

$type = $_REQUEST['type'] ?? '';
$format = $_REQUEST['format'] ?? 'csv';

switch($type) {
    case 'users':
        exportUsers($format);
        break;
    case 'classes':
        exportClasses($format);
        break;
    case 'subjects':
        exportSubjects($format);
        break;
    case 'library':
        exportLibrary($format);
        break;
    case 'events':
        exportEvents($format);
        break;
    default:
        die('Invalid export type: ' . htmlspecialchars($type));
}

function exportUsers($format) {
    global $pdo;
    
    try {
        $stmt = $pdo->query('SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC');
        $users = $stmt->fetchAll();
        
        $filename = 'users_list_' . date('Y-m-d');
        
        $data = [];
        $data[] = ['ID', 'Name', 'Email', 'Role', 'Created Date'];
        
        foreach ($users as $user) {
            $data[] = [
                $user['id'],
                $user['name'],
                $user['email'],
                $user['role'],
                date('Y-m-d H:i:s', strtotime($user['created_at']))
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

function exportClasses($format) {
    global $pdo;
    
    try {
        $stmt = $pdo->query('SELECT * FROM classes ORDER BY grade_name');
        $classes = $stmt->fetchAll();
        
        $filename = 'classes_list_' . date('Y-m-d');
        
        $data = [];
        $data[] = ['ID', 'Grade', 'Section', 'Room', 'Capacity', 'Teacher'];
        
        foreach ($classes as $class) {
            $data[] = [
                $class['id'],
                $class['grade_name'],
                $class['section'] ?? 'N/A',
                $class['room_number'] ?? 'N/A',
                $class['capacity'] ?? 'N/A',
                $class['teacher_name'] ?? 'N/A'
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

function exportSubjects($format) {
    global $pdo;
    
    try {
        $stmt = $pdo->query('SELECT * FROM subjects ORDER BY subject_name');
        $subjects = $stmt->fetchAll();
        
        $filename = 'subjects_list_' . date('Y-m-d');
        
        $data = [];
        $data[] = ['ID', 'Subject Code', 'Subject Name', 'Grade Level', 'Credits', 'Teacher'];
        
        foreach ($subjects as $subject) {
            $data[] = [
                $subject['id'],
                $subject['subject_code'] ?? 'N/A',
                $subject['subject_name'],
                $subject['grade_level'] ?? 'N/A',
                $subject['credits'] ?? 'N/A',
                $subject['teacher_name'] ?? 'N/A'
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

function exportLibrary($format) {
    global $pdo;
    
    try {
        $stmt = $pdo->query('SELECT id, title, category, file_path, uploaded_by, uploaded_at FROM library_books ORDER BY title');
        $books = $stmt->fetchAll();
        
        $filename = 'library_books_' . date('Y-m-d');
        
        $data = [];
        $data[] = ['ID', 'Title', 'Category', 'File Path', 'Uploaded By', 'Upload Date'];
        
        foreach ($books as $book) {
            $data[] = [
                $book['id'],
                $book['title'],
                $book['category'] ?? 'N/A',
                $book['file_path'] ?? 'N/A',
                $book['uploaded_by'] ?? 'N/A',
                date('Y-m-d H:i:s', strtotime($book['uploaded_at']))
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

function exportEvents($format) {
    global $pdo;
    
    try {
        $stmt = $pdo->query('SELECT id, title, start_date, end_date, user_id, class_id FROM calendar_events ORDER BY start_date');
        $events = $stmt->fetchAll();
        
        $filename = 'calendar_events_' . date('Y-m-d');
        
        $data = [];
        $data[] = ['ID', 'Event Title', 'Start Date', 'End Date', 'Created By', 'Class'];
        
        foreach ($events as $event) {
            $data[] = [
                $event['id'],
                $event['title'],
                $event['start_date'],
                $event['end_date'] ?? 'N/A',
                $event['user_id'] ?? 'N/A',
                $event['class_id'] ?? 'N/A'
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