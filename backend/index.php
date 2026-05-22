<?php
echo json_encode([
    "system" => "G.S. Murama School Management System",
    "status" => "running",
    "endpoints" => [
        "/api/health.php" => "Health check",
        "/api/students.php" => "Student management",
        "/api/fees.php" => "Fee management",
        "/api/login.php" => "User authentication"
    ]
]);
?>
