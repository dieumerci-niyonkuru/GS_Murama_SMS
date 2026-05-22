<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);
require_once '../config/Database.php';
$db = (new Database())->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $course_id = $_GET['course_id'];
    $stmt = $db->prepare("SELECT * FROM course_materials WHERE course_id = ? ORDER BY upload_date DESC");
    $stmt->execute([$course_id]);
    $materials = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(["success" => true, "data" => $materials]);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // For simplicity, just record material info without actual file upload (can be extended)
    $data = json_decode(file_get_contents("php://input"), true);
    $stmt = $db->prepare("INSERT INTO course_materials (course_id, title, description, uploaded_by) VALUES (?, ?, ?, ?)");
    $stmt->execute([$data['course_id'], $data['title'], $data['description'], $data['uploaded_by']]);
    echo json_encode(["success" => true]);
}
?>
