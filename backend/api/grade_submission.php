<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST, OPTIONS");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);
require_once '../config/Database.php';
require_once '../config/Permissions.php';
$user = checkAuthAndPermissions('can_manage_courses');
$data = json_decode(file_get_contents("php://input"), true);
$submission_id = $data['submission_id'];
$grade = $data['grade'];
$feedback = $data['feedback'];
$db = (new Database())->getConnection();
$stmt = $db->prepare("UPDATE homework_submissions SET grade = ?, feedback = ? WHERE id = ?");
$stmt->execute([$grade, $feedback, $submission_id]);
echo json_encode(["success"=>true, "message"=>"Grade saved"]);
?>
