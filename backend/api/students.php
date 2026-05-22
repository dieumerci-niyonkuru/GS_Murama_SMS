<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);
require_once '../config/Permissions.php';
$user = checkAuthAndPermissions('can_manage_students');

require_once '../config/Database.php';
$db = (new Database())->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (isset($_GET['id'])) {
        $stmt = $db->prepare("SELECT * FROM students WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode(["success" => true, "data" => $student]);
    } else {
        $stmt = $db->query("SELECT * FROM students ORDER BY id DESC");
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(["success" => true, "data" => $students]);
    }
} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $student_id = "GSM".date('Y').rand(1000,9999);
    $stmt = $db->prepare("INSERT INTO students (student_id, first_name, last_name, level, class, parent_phone, parent_email, address) VALUES (?,?,?,?,?,?,?,?)");
    $stmt->execute([$student_id, $data['first_name'], $data['last_name'], $data['level'], $data['class'], $data['parent_phone'], $data['parent_email'], $data['address']]);
    echo json_encode(["success" => true, "student_id" => $student_id]);
} elseif ($method === 'PUT') {
    $data = json_decode(file_get_contents("php://input"), true);
    $stmt = $db->prepare("UPDATE students SET first_name=?, last_name=?, level=?, class=?, parent_phone=?, parent_email=?, address=? WHERE id=?");
    $stmt->execute([$data['first_name'], $data['last_name'], $data['level'], $data['class'], $data['parent_phone'], $data['parent_email'], $data['address'], $data['id']]);
    echo json_encode(["success" => true]);
} elseif ($method === 'DELETE') {
    $id = $_GET['id'];
    $stmt = $db->prepare("DELETE FROM students WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(["success" => true]);
}
?>
