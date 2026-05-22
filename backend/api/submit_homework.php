<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST, OPTIONS");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);
require_once '../config/Database.php';
require_once '../config/Permissions.php';
$user = checkAuthAndPermissions(); // student or parent
$db = (new Database())->getConnection();
$data = json_decode(file_get_contents("php://input"), true);
$homework_id = $data['homework_id'];
$student_id = $data['student_id'];
$submission_text = $data['submission_text'] ?? '';
$uploadDir = dirname(__DIR__) . '/uploads/submissions/';
if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);
$filePath = '';
if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
    $filename = 'sub_' . time() . '_' . $student_id . '.' . $ext;
    $destination = $uploadDir . $filename;
    if (move_uploaded_file($_FILES['file']['tmp_name'], $destination)) {
        $filePath = '/uploads/submissions/' . $filename;
    }
}
$insert = $db->prepare("INSERT INTO homework_submissions (homework_id, student_id, submission_text, file_path) VALUES (?, ?, ?, ?)");
$insert->execute([$homework_id, $student_id, $submission_text, $filePath]);
echo json_encode(["success"=>true, "message"=>"Homework submitted"]);
?>
