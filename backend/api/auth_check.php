<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$headers = getallheaders();
$token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;
if (!$token) { echo json_encode(["authenticated"=>false]); exit(); }

require_once '../config/Database.php';
$db = (new Database())->getConnection();
$stmt = $db->prepare("SELECT a.*, r.* FROM admin_sessions s JOIN administrators a ON s.admin_id = a.id JOIN role_permissions r ON a.role = r.role WHERE s.token = ? AND s.expires_at > NOW()");
$stmt->execute([$token]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    echo json_encode(["authenticated"=>true, "user"=>["id"=>$user['id'], "sdms_code"=>$user['sdms_code'], "full_name"=>$user['full_name'], "role"=>$user['role'], "permissions"=>$user]]);
} else {
    echo json_encode(["authenticated"=>false]);
}
?>
