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
$stmt = $db->prepare("SELECT a.id FROM admin_sessions s JOIN administrators a ON s.admin_id = a.id WHERE s.token = ? AND s.expires_at > NOW()");
$stmt->execute([$token]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) { http_response_code(401); echo json_encode(["success"=>false,"message"=>"Invalid token"]); exit(); }

$course_id = $_POST['course_id'];
$title = $_POST['title'];
$description = $_POST['description'];

$uploadDir = dirname(__DIR__) . '/uploads/materials/';
if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);

$filePath = '';
if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
    $filename = 'material_' . time() . '_' . rand(1000,9999) . '.' . $ext;
    $destination = $uploadDir . $filename;
    if (move_uploaded_file($_FILES['file']['tmp_name'], $destination)) {
        $filePath = '/uploads/materials/' . $filename;
        $fileType = in_array($ext, ['pdf']) ? 'pdf' : (in_array($ext, ['doc','docx']) ? 'doc' : (in_array($ext, ['ppt','pptx']) ? 'ppt' : 'other'));
    } else {
        echo json_encode(["success"=>false,"message"=>"Failed to upload file"]);
        exit();
    }
}

$insert = $db->prepare("INSERT INTO course_materials (course_id, title, description, file_path, file_type, uploaded_by) VALUES (?, ?, ?, ?, ?, ?)");
$insert->execute([$course_id, $title, $description, $filePath, $fileType, $user['id']]);

echo json_encode(["success"=>true, "message"=>"Material uploaded successfully"]);
?>
