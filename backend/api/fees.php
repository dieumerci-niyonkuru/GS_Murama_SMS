<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
require_once '../config/Database.php';
$db = (new Database())->getConnection();
$method = $_SERVER['REQUEST_METHOD'];
if($method == 'GET') {
    $stmt = $db->query("SELECT f.*, s.first_name, s.last_name FROM fees f JOIN students s ON f.student_id = s.student_id ORDER BY f.payment_date DESC");
    echo json_encode(["success"=>true, "data"=>$stmt->fetchAll(PDO::FETCH_ASSOC)]);
} elseif($method == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $balance = $data['amount'] - $data['paid_amount'];
    $receipt = "RCP".time();
    $stmt = $db->prepare("INSERT INTO fees (student_id, amount, paid_amount, balance, term, year, payment_date, payment_method, receipt_number) VALUES (?,?,?,?,?,?,?,?,?)");
    $stmt->execute([$data['student_id'], $data['amount'], $data['paid_amount'], $balance, $data['term'], date('Y'), date('Y-m-d'), $data['method'], $receipt]);
    echo json_encode(["success"=>true, "receipt"=>$receipt]);
}
?>
