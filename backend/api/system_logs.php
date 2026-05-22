<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
require_once '../config/Database.php';
$db = (new Database())->getConnection();
$stmt = $db->query("SELECT * FROM system_logs ORDER BY created_at DESC LIMIT 200");
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode(["success" => true, "data" => $logs]);
?>
