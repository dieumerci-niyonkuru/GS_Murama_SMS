<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST, OPTIONS");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);
require_once '../config/Database.php';
require_once '../config/Permissions.php';
$user = checkAuthAndPermissions('can_manage_courses');
$db = (new Database())->getConnection();
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
