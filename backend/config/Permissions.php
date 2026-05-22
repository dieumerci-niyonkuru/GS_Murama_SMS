<?php
function checkAuthAndPermissions($requiredPermission = null) {
    $headers = getallheaders();
    $token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;
    if (!$token) {
        http_response_code(401);
        echo json_encode(["success"=>false,"message"=>"Unauthorized"]);
        exit();
    }
    require_once __DIR__ . '/Database.php';
    $db = (new Database())->getConnection();
    $stmt = $db->prepare("SELECT a.*, r.* FROM admin_sessions s 
                          JOIN administrators a ON s.admin_id = a.id 
                          JOIN role_permissions r ON a.role = r.role 
                          WHERE s.token = ? AND s.expires_at > NOW()");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        http_response_code(401);
        echo json_encode(["success"=>false,"message"=>"Invalid token"]);
        exit();
    }
    if ($requiredPermission && !$user[$requiredPermission]) {
        http_response_code(403);
        echo json_encode(["success"=>false,"message"=>"Permission denied"]);
        exit();
    }
    return $user;
}
?>
