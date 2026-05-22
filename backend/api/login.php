<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);

$db_path = dirname(__DIR__) . '/config/Database.php';
if (!file_exists($db_path)) {
    echo json_encode(["success" => false, "message" => "Database config not found"]);
    exit();
}
require_once $db_path;

$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data['sdms_code']) || !isset($data['password'])) {
    echo json_encode(["success"=>false, "message"=>"SDMS Code and password required"]);
    exit();
}

$db = (new Database())->getConnection();

$stmt = $db->prepare("SELECT * FROM administrators WHERE sdms_code = :code AND is_active = 1");
$stmt->execute([':code' => $data['sdms_code']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode(["success"=>false, "message"=>"Invalid SDMS Code"]);
    exit();
}

// First login (no password set)
if ($user['is_first_login'] == 1 || empty($user['password'])) {
    echo json_encode([
        "success"=>false,
        "requires_setup"=>true,
        "sdms_code"=>$user['sdms_code'],
        "message"=>"First login – set your password"
    ]);
    exit();
}

// Verify password
if (!password_verify($data['password'], $user['password'])) {
    echo json_encode(["success"=>false, "message"=>"Invalid password"]);
    exit();
}

// Update last login
$upd = $db->prepare("UPDATE administrators SET last_login = NOW() WHERE id = ?");
$upd->execute([$user['id']]);

// Generate token
$token = base64_encode(json_encode([
    'id' => $user['id'],
    'sdms_code' => $user['sdms_code'],
    'role' => $user['role'],
    'exp' => time() + 86400 * 7
]));

// Delete any existing sessions for this user
$del = $db->prepare("DELETE FROM admin_sessions WHERE admin_id = ?");
$del->execute([$user['id']]);

// Store new session
$sess = $db->prepare("INSERT INTO admin_sessions (admin_id, token, ip_address, user_agent, expires_at) VALUES (?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 7 DAY))");
$sess->execute([$user['id'], $token, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']]);

// Get permissions – use column 'role' (if your table uses 'role')
$perm = $db->prepare("SELECT * FROM role_permissions WHERE role = ?");
$perm->execute([$user['role']]);
$permissions = $perm->fetch(PDO::FETCH_ASSOC);
if (!$permissions) $permissions = [];

echo json_encode([
    "success" => true,
    "token" => $token,
    "user" => [
        "id" => $user['id'],
        "sdms_code" => $user['sdms_code'],
        "full_name" => $user['full_name'],
        "role" => $user['role'],
        "phone" => $user['phone'],
        "email" => $user['email'],
        "permissions" => $permissions
    ]
]);
?>
