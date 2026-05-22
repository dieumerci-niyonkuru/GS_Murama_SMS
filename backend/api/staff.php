<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);
require_once '../config/Database.php';
$db = (new Database())->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $db->query("SELECT id, sdms_code, full_name, role, phone, email, is_active, is_first_login, last_login FROM administrators ORDER BY role, full_name");
    $staff = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(["success" => true, "data" => $staff]);
} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $sdms = $data['sdms_code'] ?? 'STAFF' . time();
    $stmt = $db->prepare("INSERT INTO administrators (sdms_code, full_name, role, phone, email, is_first_login, is_active) VALUES (?, ?, ?, ?, ?, 1, 1)");
    $stmt->execute([$sdms, $data['full_name'], $data['role'], $data['phone'], $data['email']]);
    echo json_encode(["success" => true, "sdms_code" => $sdms]);
} elseif ($method === 'PUT') {
    $data = json_decode(file_get_contents("php://input"), true);
    $stmt = $db->prepare("UPDATE administrators SET full_name=?, role=?, phone=?, email=?, is_active=? WHERE id=?");
    $stmt->execute([$data['full_name'], $data['role'], $data['phone'], $data['email'], $data['is_active'], $data['id']]);
    echo json_encode(["success" => true]);
} elseif ($method === 'DELETE') {
    $id = $_GET['id'];
    $stmt = $db->prepare("DELETE FROM administrators WHERE id=? AND role NOT IN ('Head_Teacher')");
    $stmt->execute([$id]);
    echo json_encode(["success" => true]);
}
?>
