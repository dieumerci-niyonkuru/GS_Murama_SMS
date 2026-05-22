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
$stmt = $db->prepare("SELECT a.id, a.password FROM admin_sessions s JOIN administrators a ON s.admin_id = a.id WHERE s.token = ? AND s.expires_at > NOW()");
$stmt->execute([$token]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) { http_response_code(401); echo json_encode(["success"=>false,"message"=>"Invalid token"]); exit(); }

$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data['current_password']) || !isset($data['new_password'])) {
    echo json_encode(["success"=>false,"message"=>"Current and new password required"]);
    exit();
}
if (strlen($data['new_password']) < 6) {
    echo json_encode(["success"=>false,"message"=>"New password must be at least 6 characters"]);
    exit();
}
if (!password_verify($data['current_password'], $user['password'])) {
    echo json_encode(["success"=>false,"message"=>"Current password is incorrect"]);
    exit();
}
$new_hash = password_hash($data['new_password'], PASSWORD_DEFAULT);
$update = $db->prepare("UPDATE administrators SET password = ? WHERE id = ?");
if ($update->execute([$new_hash, $user['id']])) {
    echo json_encode(["success"=>true,"message"=>"Password changed successfully"]);
} else {
    echo json_encode(["success"=>false,"message"=>"Failed to change password"]);
}
?>
