<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);
require_once '../config/Database.php';
require_once '../config/Permissions.php';

// Check authentication but don't enforce a specific permission for viewing
$user = checkAuthAndPermissions();

$db = (new Database())->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $student_id = $_GET['student_id'] ?? null;
    
    // If user is Parent, restrict to their children's student IDs
    if ($user['role'] === 'Parent') {
        $email = $user['email'];
        $childrenStmt = $db->prepare("SELECT student_id FROM students WHERE parent_email = ?");
        $childrenStmt->execute([$email]);
        $children = $childrenStmt->fetchAll(PDO::FETCH_COLUMN);
        if (empty($children)) {
            echo json_encode(["success" => true, "data" => []]);
            exit();
        }
        if ($student_id && !in_array($student_id, $children)) {
            http_response_code(403);
            echo json_encode(["success" => false, "message" => "Access denied"]);
            exit();
        }
        $placeholders = implode(',', array_fill(0, count($children), '?'));
        $stmt = $db->prepare("SELECT f.*, s.first_name, s.last_name FROM fees f JOIN students s ON f.student_id = s.student_id WHERE f.student_id IN ($placeholders) ORDER BY f.payment_date DESC");
        $stmt->execute($children);
        $fees = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(["success" => true, "data" => $fees]);
    } 
    // For other roles (Head Teacher, Bursar, etc.) – show all or filter by student_id
    else {
        if ($student_id) {
            $stmt = $db->prepare("SELECT f.*, s.first_name, s.last_name FROM fees f JOIN students s ON f.student_id = s.student_id WHERE f.student_id = ? ORDER BY f.payment_date DESC");
            $stmt->execute([$student_id]);
        } else {
            $stmt = $db->query("SELECT f.*, s.first_name, s.last_name FROM fees f JOIN students s ON f.student_id = s.student_id ORDER BY f.payment_date DESC");
        }
        $fees = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(["success" => true, "data" => $fees]);
    }
} 
elseif ($method === 'POST') {
    // Only users with can_manage_fees permission can add payments
    if (!$user['can_manage_fees']) {
        http_response_code(403);
        echo json_encode(["success" => false, "message" => "Permission denied"]);
        exit();
    }
    $data = json_decode(file_get_contents("php://input"), true);
    $balance = $data['amount'] - $data['paid_amount'];
    $receipt = "RCP" . time() . rand(100,999);
    $stmt = $db->prepare("INSERT INTO fees (student_id, amount, paid_amount, balance, term, year, payment_date, payment_method, receipt_number, recorded_by) VALUES (?,?,?,?,?,?,?,?,?,?)");
    $stmt->execute([$data['student_id'], $data['amount'], $data['paid_amount'], $balance, $data['term'], date('Y'), date('Y-m-d'), $data['method'], $receipt, $user['sdms_code']]);
    
    // Audit log
    $logStmt = $db->prepare("INSERT INTO system_logs (admin_sdms_code, action, details, ip_address) VALUES (?, ?, ?, ?)");
    $logStmt->execute([$user['sdms_code'], 'FEE_PAYMENT', "Receipt $receipt for student ".$data['student_id'], $_SERVER['REMOTE_ADDR']]);
    
    echo json_encode(["success" => true, "receipt" => $receipt, "balance" => $balance]);
}
?>
