<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);
require_once '../config/Database.php';
$db = (new Database())->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $db->query("SELECT f.*, s.first_name, s.last_name FROM fees f JOIN students s ON f.student_id = s.student_id ORDER BY f.payment_date DESC");
    $fees = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(["success" => true, "data" => $fees]);
} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $balance = $data['amount'] - $data['paid_amount'];
    $receipt = "RCP" . time() . rand(100,999);
    $stmt = $db->prepare("INSERT INTO fees (student_id, amount, paid_amount, balance, term, year, payment_date, payment_method, receipt_number, recorded_by) VALUES (?,?,?,?,?,?,?,?,?,?)");
    $stmt->execute([$data['student_id'], $data['amount'], $data['paid_amount'], $balance, $data['term'], date('Y'), date('Y-m-d'), $data['method'], $receipt, $data['recorded_by'] ?? 'system']);
    
    // Audit log
    $logStmt = $db->prepare("INSERT INTO system_logs (admin_sdms_code, action, details, ip_address) VALUES (?, ?, ?, ?)");
    $logStmt->execute([$data['recorded_by'] ?? 'system', 'FEE_PAYMENT', "Receipt $receipt for student ".$data['student_id'], $_SERVER['REMOTE_ADDR']]);
    
    echo json_encode(["success" => true, "receipt" => $receipt, "balance" => $balance]);
}
?>
