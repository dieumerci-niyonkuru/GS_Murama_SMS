<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST, OPTIONS");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);
require_once '../config/Database.php';

$headers = getallheaders();
$token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;
if (!$token) { http_response_code(401); echo json_encode(["success"=>false,"message"=>"Unauthorized"]); exit(); }

$db = (new Database())->getConnection();
$stmt = $db->prepare("SELECT a.id, a.sdms_code FROM admin_sessions s JOIN administrators a ON s.admin_id = a.id WHERE s.token = ? AND s.expires_at > NOW()");
$stmt->execute([$token]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) { http_response_code(401); echo json_encode(["success"=>false,"message"=>"Invalid token"]); exit(); }

// For students, they might not be in administrators table? We'll use parent role or student table.
// Simplify: assume student uses parent email to identify. We'll just accept student_id from POST.
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
