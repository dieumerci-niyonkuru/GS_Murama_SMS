<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
require_once '../config/Database.php';
require_once '../config/Permissions.php';
$user = checkAuthAndPermissions('can_manage_fees');
$db = (new Database())->getConnection();
$stmt = $db->query("SELECT fa.*, a.full_name as changer_name FROM fee_audit fa LEFT JOIN administrators a ON fa.changed_by = a.sdms_code ORDER BY fa.changed_at DESC");
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode(["success" => true, "data" => $logs]);
?>
