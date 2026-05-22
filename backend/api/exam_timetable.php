<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);
require_once '../config/Database.php';
$db = (new Database())->getConnection();
$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'GET') {
    $stmt = $db->query("SELECT * FROM exam_timetable ORDER BY exam_date");
    $timetable = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(["success" => true, "data" => $timetable]);
} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $stmt = $db->prepare("INSERT INTO exam_timetable (subject, class, exam_date, start_time, end_time, room) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$data['subject'], $data['class'], $data['exam_date'], $data['start_time'], $data['end_time'], $data['room']]);
    echo json_encode(["success" => true]);
} elseif ($method === 'DELETE') {
    $id = $_GET['id'];
    $stmt = $db->prepare("DELETE FROM exam_timetable WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(["success" => true]);
}
?>
