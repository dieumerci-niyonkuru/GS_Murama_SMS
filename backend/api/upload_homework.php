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
$due_date = $_POST['due_date'];
$uploadDir = dirname(__DIR__) . '/uploads/homework/';
if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);
$filePath = '';
if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
    $filename = 'hw_' . time() . '_' . rand(1000,9999) . '.' . $ext;
    $destination = $uploadDir . $filename;
    if (move_uploaded_file($_FILES['file']['tmp_name'], $destination)) {
        $filePath = '/uploads/homework/' . $filename;
    }
}
$insert = $db->prepare("INSERT INTO homework (course_id, title, description, due_date, file_path, created_by) VALUES (?, ?, ?, ?, ?, ?)");
$insert->execute([$course_id, $title, $description, $due_date, $filePath, $user['sdms_code']]);
echo json_encode(["success"=>true, "message"=>"Homework assigned"]);
?>
