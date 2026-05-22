<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
require_once '../config/Database.php';
$db = (new Database())->getConnection();
$stmt = $db->query("SELECT sdms_code, full_name, phone, email, role FROM administrators WHERE role IN ('Teacher','Class_Master')");
$teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode(["success" => true, "data" => $teachers]);
?>
