<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);
require_once '../config/Database.php';
$db = (new Database())->getConnection();
$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'GET') {
    $stmt = $db->query("SELECT * FROM courses ORDER BY id DESC");
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(["success" => true, "data" => $courses]);
} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $code = $data['course_code'] ?? 'CRS' . time();
    $stmt = $db->prepare("INSERT INTO courses (course_code, course_name, level, class, teacher_sdms, description) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$code, $data['course_name'], $data['level'], $data['class'], $data['teacher_sdms'], $data['description']]);
    echo json_encode(["success" => true]);
} elseif ($method === 'PUT') {
    $data = json_decode(file_get_contents("php://input"), true);
    $stmt = $db->prepare("UPDATE courses SET course_name=?, level=?, class=?, teacher_sdms=?, description=? WHERE id=?");
    $stmt->execute([$data['course_name'], $data['level'], $data['class'], $data['teacher_sdms'], $data['description'], $data['id']]);
    echo json_encode(["success" => true]);
} elseif ($method === 'DELETE') {
    $id = $_GET['id'];
    $stmt = $db->prepare("DELETE FROM courses WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(["success" => true]);
}
?>
