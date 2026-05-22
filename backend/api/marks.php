<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);
require_once '../config/Database.php';
require_once '../config/Permissions.php';

$user = checkAuthAndPermissions();

$db = (new Database())->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $student_id = $_GET['student_id'] ?? null;
    
    if ($user['role'] === 'Parent') {
        $email = $user['email'];
        $childrenStmt = $db->prepare("SELECT student_id FROM students WHERE parent_email = ?");
        $childrenStmt->execute([$email]);
        $children = $childrenStmt->fetchAll(PDO::FETCH_COLUMN);
        if (empty($children)) {
            echo json_encode(["success" => true, "data" => []]);
            exit();
        }
        if ($student_id && !in_array($student_id, $children)) {
            http_response_code(403);
            echo json_encode(["success" => false, "message" => "Access denied"]);
            exit();
        }
        $placeholders = implode(',', array_fill(0, count($children), '?'));
        $stmt = $db->prepare("SELECT er.*, s.first_name, s.last_name, s.class FROM exam_results er JOIN students s ON er.student_id = s.student_id WHERE er.student_id IN ($placeholders) ORDER BY er.exam_date DESC");
        $stmt->execute($children);
        $marks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(["success" => true, "data" => $marks]);
    } 
    else {
        if ($student_id) {
            $stmt = $db->prepare("SELECT er.*, s.first_name, s.last_name, s.class FROM exam_results er JOIN students s ON er.student_id = s.student_id WHERE er.student_id = ? ORDER BY er.exam_date DESC");
            $stmt->execute([$student_id]);
        } else {
            $stmt = $db->query("SELECT er.*, s.first_name, s.last_name, s.class FROM exam_results er JOIN students s ON er.student_id = s.student_id ORDER BY er.exam_date DESC");
        }
        $marks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(["success" => true, "data" => $marks]);
    }
} 
elseif ($method === 'POST' || $method === 'PUT') {
    // Only teachers, DOS, Head Teacher can add/edit marks
    if (!in_array($user['role'], ['Teacher', 'DOS', 'Head_Teacher'])) {
        http_response_code(403);
        echo json_encode(["success" => false, "message" => "Permission denied"]);
        exit();
    }
    // ... rest of POST/PUT logic unchanged
}
?>
