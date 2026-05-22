<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);
require_once '../config/Database.php';
$db = (new Database())->getConnection();

$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'GET') {
    $settings = [];
    $stmt = $db->query("SELECT * FROM school_settings");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { $settings[$row['setting_key']] = $row['setting_value']; }
    echo json_encode(["success" => true, "data" => $settings]);
} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    foreach ($data as $key => $value) {
        $stmt = $db->prepare("INSERT INTO school_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->execute([$key, $value, $value]);
    }
    echo json_encode(["success" => true]);
}
?>
