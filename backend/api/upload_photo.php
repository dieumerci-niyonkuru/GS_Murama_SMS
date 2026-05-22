<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);

require_once '../config/Database.php';

// Check token
$headers = getallheaders();
$token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;
if (!$token) { http_response_code(401); echo json_encode(["success"=>false,"message"=>"Unauthorized"]); exit(); }

$db = (new Database())->getConnection();
$stmt = $db->prepare("SELECT a.id FROM admin_sessions s JOIN administrators a ON s.admin_id = a.id WHERE s.token = ? AND s.expires_at > NOW()");
$stmt->execute([$token]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) { http_response_code(401); echo json_encode(["success"=>false,"message"=>"Invalid token"]); exit(); }

if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = dirname(__DIR__) . '/uploads/';
    if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);
    $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
    $filename = 'profile_' . $user['id'] . '_' . time() . '.' . $ext;
    $destination = $uploadDir . $filename;
    if (move_uploaded_file($_FILES['photo']['tmp_name'], $destination)) {
        $update = $db->prepare("UPDATE administrators SET profile_pic = ? WHERE id = ?");
        $update->execute([$filename, $user['id']]);
        echo json_encode(["success"=>true, "photo_url"=>"/uploads/" . $filename]);
    } else {
        echo json_encode(["success"=>false,"message"=>"Failed to move uploaded file"]);
    }
} else {
    echo json_encode(["success"=>false,"message"=>"No file uploaded or upload error"]);
}
?>
