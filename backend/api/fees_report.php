<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
require_once '../config/Database.php';

$db = (new Database())->getConnection();
$year = $_GET['year'] ?? date('Y');
$stmt = $db->prepare("
    SELECT term, SUM(amount) as total_expected, SUM(paid_amount) as total_paid,
           (SUM(amount) - SUM(paid_amount)) as outstanding
    FROM fees
    WHERE year = ?
    GROUP BY term
    ORDER BY term
");
$stmt->execute([$year]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Also get total overall
$stmtAll = $db->prepare("SELECT SUM(amount) as total_expected, SUM(paid_amount) as total_paid FROM fees WHERE year = ?");
$stmtAll->execute([$year]);
$totals = $stmtAll->fetch(PDO::FETCH_ASSOC);

echo json_encode([
    "success" => true,
    "year" => $year,
    "by_term" => $data,
    "totals" => $totals
]);
?>
