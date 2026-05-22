<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$db_path = dirname(__DIR__) . '/config/Database.php';
if (!file_exists($db_path)) {
    http_response_code(500);
    echo json_encode(["error" => "Database.php not found at $db_path"]);
    exit();
}
require_once $db_path;

$db = (new Database())->getConnection();
$totalStudents = $db->query("SELECT COUNT(*) FROM students")->fetchColumn();
$totalFees = $db->query("SELECT SUM(paid_amount) FROM fees")->fetchColumn();
$totalTeachers = $db->query("SELECT COUNT(*) FROM administrators WHERE role = 'Teacher'")->fetchColumn();
$totalBooks = $db->query("SELECT COUNT(*) FROM library_books")->fetchColumn();
echo json_encode([
    "success" => true,
    "totalStudents" => $totalStudents,
    "totalFees" => $totalFees ?: 0,
    "totalTeachers" => $totalTeachers,
    "totalBooks" => $totalBooks ?: 0
]);
?>
