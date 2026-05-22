<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);

$db_path = dirname(__DIR__) . '/config/Database.php';
if (!file_exists($db_path)) {
    echo json_encode(["success"=>false, "message"=>"Database config not found"]);
    exit();
}
require_once $db_path;

$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data['sdms_code']) || !isset($data['password']) || $data['password'] !== $data['confirm_password']) {
    echo json_encode(["success"=>false, "message"=>"Passwords do not match"]);
    exit();
}
if (strlen($data['password']) < 6) {
    echo json_encode(["success"=>false, "message"=>"Password must be at least 6 characters"]);
    exit();
}

$db = (new Database())->getConnection();
$stmt = $db->prepare("SELECT * FROM administrators WHERE sdms_code = :code AND is_first_login = 1");
$stmt->execute([':code' => $data['sdms_code']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode(["success"=>false, "message"=>"Invalid SDMS Code or password already set"]);
    exit();
}

$hash = password_hash($data['password'], PASSWORD_DEFAULT);
$upd = $db->prepare("UPDATE administrators SET password = ?, is_first_login = 0 WHERE sdms_code = ?");
if ($upd->execute([$hash, $data['sdms_code']])) {
    echo json_encode(["success"=>true, "message"=>"Password created. Please login."]);
} else {
    echo json_encode(["success"=>false, "message"=>"Failed to set password"]);
}
?>
