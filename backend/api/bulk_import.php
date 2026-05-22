<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);
require_once '../config/Database.php';

if ($_FILES['csv']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(["success" => false, "message" => "Upload failed"]);
    exit();
}
$file = fopen($_FILES['csv']['tmp_name'], 'r');
$headers = fgetcsv($file);
$db = (new Database())->getConnection();
$stmt = $db->prepare("INSERT INTO students (student_id, first_name, last_name, level, class, parent_phone, parent_email, address) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$count = 0;
while (($row = fgetcsv($file)) !== false) {
    $student_id = 'GSM' . date('Y') . str_pad(rand(1,9999),4,'0',STR_PAD_LEFT);
    $stmt->execute([$student_id, $row[0], $row[1], $row[2], $row[3], $row[4] ?? '', $row[5] ?? '', $row[6] ?? '']);
    $count++;
}
fclose($file);
echo json_encode(["success" => true, "imported" => $count]);
?>
