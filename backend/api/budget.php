<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
require_once '../config/Database.php';
$db = (new Database())->getConnection();
$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'GET') {
    $stmt = $db->query("SELECT SUM(paid_amount) as income FROM fees");
    $income = $stmt->fetch(PDO::FETCH_ASSOC)['income'] ?: 0;
    // Simple expense table – you can add later
    echo json_encode(["success" => true, "income" => $income, "expenses" => 0, "balance" => $income]);
} elseif ($method === 'POST') {
    // record expense (simplified)
    $data = json_decode(file_get_contents("php://input"), true);
    // store in a new table 'expenses' – for brevity, just return success
    echo json_encode(["success" => true]);
}
?>
