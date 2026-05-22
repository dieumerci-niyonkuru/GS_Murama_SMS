<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
require_once '../config/Database.php';
require_once '../config/Permissions.php';
$user = checkAuthAndPermissions('can_manage_fees');
$db = (new Database())->getConnection();
// Get all defaulters with parent email
$stmt = $db->query("
    SELECT s.student_id, s.first_name, s.last_name, s.parent_email, 
           COALESCE(SUM(f.amount),0) as total_fees, 
           COALESCE(SUM(f.paid_amount),0) as total_paid,
           (COALESCE(SUM(f.amount),0) - COALESCE(SUM(f.paid_amount),0)) as balance
    FROM students s
    LEFT JOIN fees f ON s.student_id = f.student_id
    GROUP BY s.id
    HAVING balance > 0
");
$defaulters = $stmt->fetchAll(PDO::FETCH_ASSOC);
$sent = [];
foreach ($defaulters as $d) {
    if ($d['parent_email']) {
        // Simulate email (log to file)
        $message = "Dear Parent, Your child {$d['first_name']} {$d['last_name']} has an outstanding fee balance of {$d['balance']} RWF. Please clear by end of term.";
        file_put_contents(__DIR__ . '/../logs/email_log.txt', date('Y-m-d H:i:s') . " - To: {$d['parent_email']} - $message\n", FILE_APPEND);
        $sent[] = $d['student_id'];
    }
}
echo json_encode(["success" => true, "alerts_sent" => count($sent)]);
?>
