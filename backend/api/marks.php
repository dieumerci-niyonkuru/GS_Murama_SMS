<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);
require_once '../config/Database.php';
$db = (new Database())->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (isset($_GET['student_id'])) {
        $stmt = $db->prepare("SELECT * FROM exam_results WHERE student_id = ?");
        $stmt->execute([$_GET['student_id']]);
        $marks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(["success" => true, "data" => $marks]);
    } else {
        $stmt = $db->query("SELECT er.*, s.first_name, s.last_name, s.class FROM exam_results er JOIN students s ON er.student_id = s.student_id ORDER BY er.exam_date DESC");
        $marks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(["success" => true, "data" => $marks]);
    }
} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $stmt = $db->prepare("INSERT INTO exam_results (student_id, subject, score, exam_date) VALUES (?, ?, ?, ?)");
    $stmt->execute([$data['student_id'], $data['subject'], $data['score'], $data['exam_date']]);
    echo json_encode(["success" => true]);
} elseif ($method === 'PUT') {
    $data = json_decode(file_get_contents("php://input"), true);
    $stmt = $db->prepare("UPDATE exam_results SET subject=?, score=?, exam_date=? WHERE id=?");
    $stmt->execute([$data['subject'], $data['score'], $data['exam_date'], $data['id']]);
    echo json_encode(["success" => true]);
}
?>
