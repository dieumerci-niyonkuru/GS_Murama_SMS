<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
require_once '../config/Database.php';
require_once '../config/Permissions.php';

$user = checkAuthAndPermissions();
$db = (new Database())->getConnection();

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
    $stmt = $db->prepare("SELECT s.student_id, s.first_name, s.last_name, s.class, 
                          COUNT(CASE WHEN a.status = 'Absent' THEN 1 END) as absences, 
                          COUNT(CASE WHEN a.status = 'Late' THEN 1 END) as lates 
                          FROM students s 
                          LEFT JOIN attendance a ON s.student_id = a.student_id 
                          WHERE s.student_id IN ($placeholders) 
                          GROUP BY s.id ORDER BY absences DESC");
    $stmt->execute($children);
    $summary = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(["success" => true, "data" => $summary]);
} else {
    // Admin or teacher – show all or filtered by student_id
    if ($student_id) {
        $stmt = $db->prepare("SELECT s.student_id, s.first_name, s.last_name, s.class, 
                              COUNT(CASE WHEN a.status = 'Absent' THEN 1 END) as absences, 
                              COUNT(CASE WHEN a.status = 'Late' THEN 1 END) as lates 
                              FROM students s 
                              LEFT JOIN attendance a ON s.student_id = a.student_id 
                              WHERE s.student_id = ?
                              GROUP BY s.id");
        $stmt->execute([$student_id]);
    } else {
        $stmt = $db->query("SELECT s.student_id, s.first_name, s.last_name, s.class, 
                            COUNT(CASE WHEN a.status = 'Absent' THEN 1 END) as absences, 
                            COUNT(CASE WHEN a.status = 'Late' THEN 1 END) as lates 
                            FROM students s 
                            LEFT JOIN attendance a ON s.student_id = a.student_id 
                            GROUP BY s.id ORDER BY absences DESC");
    }
    $summary = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(["success" => true, "data" => $summary]);
}
?>
