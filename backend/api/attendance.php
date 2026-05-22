<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);
require_once '../config/Database.php';
$db = (new Database())->getConnection();
$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'GET') {
    $date = $_GET['date'] ?? date('Y-m-d');
    $stmt = $db->prepare("SELECT a.*, s.first_name, s.last_name, s.class FROM attendance a JOIN students s ON a.student_id = s.student_id WHERE a.date = ? ORDER BY s.class, s.first_name");
    $stmt->execute([$date]);
    $attendance = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(["success" => true, "date" => $date, "data" => $attendance]);
} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $date = $data['date'];
    $records = $data['records']; // array of [student_id, status]
    $stmt = $db->prepare("INSERT INTO attendance (student_id, date, status, marked_by) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE status = ?");
    foreach ($records as $rec) {
        $stmt->execute([$rec['student_id'], $date, $rec['status'], $data['marked_by'], $rec['status']]);
    }
    echo json_encode(["success" => true, "message" => "Attendance saved"]);
}
?>
