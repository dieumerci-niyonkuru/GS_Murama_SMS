<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
require_once '../config/Database.php';
$db = (new Database())->getConnection();
$stmt = $db->query("SELECT c.*, a.full_name as teacher_name FROM courses c LEFT JOIN administrators a ON c.teacher_sdms = a.sdms_code ORDER BY c.id DESC");
$uploads = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode(["success" => true, "data" => $uploads]);
?>
