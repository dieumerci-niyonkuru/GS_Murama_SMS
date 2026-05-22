<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);
require_once '../config/Database.php';
$db = (new Database())->getConnection();
$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'GET') {
    $role = $_GET['role'] ?? 'All';
    $class = $_GET['class'] ?? null;
    $sql = "SELECT * FROM announcements WHERE target_role = 'All' OR target_role = ?";
    $params = [$role];
    if ($class) { $sql .= " OR target_class = ?"; $params[] = $class; }
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(["success" => true, "data" => $announcements]);
} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $stmt = $db->prepare("INSERT INTO announcements (title, content, target_role, target_class, created_by) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$data['title'], $data['content'], $data['target_role'], $data['target_class'], $data['created_by']]);
    echo json_encode(["success" => true]);
}
?>
