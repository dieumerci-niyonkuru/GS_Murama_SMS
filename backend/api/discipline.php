<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);
require_once '../config/Database.php';
$db = (new Database())->getConnection();
$method = $_SERVER['REQUEST_METHOD'];
if ($method === "GET") { if(isset($_GET["student_id"])) { $stmt = $db->prepare("SELECT * FROM discipline_records WHERE student_id = ? ORDER BY date DESC"); $stmt->execute([$_GET["student_id"]]); } else { $stmt = $db->query("SELECT d.*, s.first_name, s.last_name FROM discipline_records d JOIN students s ON d.student_id = s.student_id ORDER BY d.date DESC"); } $records = $stmt->fetchAll(PDO::FETCH_ASSOC); echo json_encode(["success" => true, "data" => $records]); }
    $stmt = $db->query("SELECT d.*, s.first_name, s.last_name FROM discipline_records d JOIN students s ON d.student_id = s.student_id ORDER BY d.date DESC");
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(["success" => true, "data" => $records]);
} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $stmt = $db->prepare("INSERT INTO discipline_records (student_id, date, incident_type, description, action_taken, recorded_by) VALUES (?, CURDATE(), ?, ?, ?, ?)");
    $stmt->execute([$data['student_id'], $data['incident_type'], $data['description'], $data['action_taken'], $data['recorded_by']]);
    echo json_encode(["success" => true]);
}
?>
