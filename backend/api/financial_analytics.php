<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
require_once '../config/Database.php';
require_once '../config/Permissions.php';
$user = checkAuthAndPermissions('can_view_reports');
$db = (new Database())->getConnection();
$year = $_GET['year'] ?? date('Y');
$monthly = [];
for ($m = 1; $m <= 12; $m++) {
    $stmt = $db->prepare("SELECT SUM(paid_amount) as total FROM fees WHERE year = ? AND MONTH(payment_date) = ?");
    $stmt->execute([$year, $m]);
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    $monthly[] = ['month' => $m, 'total' => $total];
}
$stmtTerm = $db->prepare("SELECT term, SUM(paid_amount) as total FROM fees WHERE year = ? GROUP BY term");
$stmtTerm->execute([$year]);
$termly = $stmtTerm->fetchAll(PDO::FETCH_ASSOC);
$totalExpected = $db->query("SELECT SUM(amount) FROM fees")->fetchColumn();
$totalCollected = $db->query("SELECT SUM(paid_amount) FROM fees")->fetchColumn();
$balance = $totalExpected - $totalCollected;
echo json_encode([
    "success" => true,
    "year" => $year,
    "monthly" => $monthly,
    "termly" => $termly,
    "total_expected" => $totalExpected,
    "total_collected" => $totalCollected,
    "balance" => $balance
]);
?>
