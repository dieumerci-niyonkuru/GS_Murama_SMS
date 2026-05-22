<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
require_once '../config/Database.php';

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
