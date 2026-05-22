<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);
require_once '../config/Database.php';
$db = (new Database())->getConnection();
$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'GET') {
    $stmt = $db->query("SELECT * FROM fee_waivers ORDER BY request_date DESC");
    $waivers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(["success" => true, "data" => $waivers]);
} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    if (isset($data['approve'])) {
        $stmt = $db->prepare("UPDATE fee_waivers SET status = 'Approved', approved_by = ? WHERE id = ?");
        $stmt->execute([$data['approved_by'], $data['id']]);
    } elseif (isset($data['reject'])) {
        $stmt = $db->prepare("UPDATE fee_waivers SET status = 'Rejected', approved_by = ? WHERE id = ?");
        $stmt->execute([$data['approved_by'], $data['id']]);
    } else {
        $stmt = $db->prepare("INSERT INTO fee_waivers (student_id, reason, requested_by, status) VALUES (?, ?, ?, 'Pending')");
        $stmt->execute([$data['student_id'], $data['reason'], $data['requested_by']]);
    }
    echo json_encode(["success" => true]);
}
?>
