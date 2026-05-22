<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);

require_once '../config/Database.php';

$headers = getallheaders();
$token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;
if (!$token) { http_response_code(401); echo json_encode(["success"=>false,"message"=>"Unauthorized"]); exit(); }

$db = (new Database())->getConnection();
$stmt = $db->prepare("SELECT a.id FROM admin_sessions s JOIN administrators a ON s.admin_id = a.id WHERE s.token = ? AND s.expires_at > NOW()");
$stmt->execute([$token]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) { http_response_code(401); echo json_encode(["success"=>false,"message"=>"Invalid token"]); exit(); }

$data = json_decode(file_get_contents("php://input"), true);
$fields = [];
$params = [];
if (isset($data['full_name'])) { $fields[] = "full_name = ?"; $params[] = $data['full_name']; }
if (isset($data['phone'])) { $fields[] = "phone = ?"; $params[] = $data['phone']; }
if (isset($data['email'])) { $fields[] = "email = ?"; $params[] = $data['email']; }
if (empty($fields)) { echo json_encode(["success"=>false,"message"=>"No fields to update"]); exit(); }
$params[] = $user['id'];
$sql = "UPDATE administrators SET " . implode(", ", $fields) . " WHERE id = ?";
$update = $db->prepare($sql);
if ($update->execute($params)) {
    echo json_encode(["success"=>true,"message"=>"Profile updated"]);
} else {
    echo json_encode(["success"=>false,"message"=>"Failed to update"]);
}
?>
