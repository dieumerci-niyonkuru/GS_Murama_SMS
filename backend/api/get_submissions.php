<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
require_once '../config/Database.php';
require_once '../config/Permissions.php';
$user = checkAuthAndPermissions('can_manage_courses');
$homework_id = $_GET['homework_id'] ?? 0;
if (!$homework_id) { echo json_encode(["success"=>false,"message"=>"Homework ID required"]); exit(); }
$db = (new Database())->getConnection();
$stmt = $db->prepare("SELECT hs.*, s.first_name, s.last_name FROM homework_submissions hs JOIN students s ON hs.student_id = s.student_id WHERE hs.homework_id = ? ORDER BY hs.submitted_at");
$stmt->execute([$homework_id]);
$submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode(["success"=>true, "data"=>$submissions]);
?>
