<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

echo json_encode([
    "status" => "OK",
    "message" => "G.S. Murama API is running",
    "timestamp" => date('Y-m-d H:i:s'),
    "version" => "1.0.0",
    "school" => "G.S. Murama, Kisaro, Rwanda",
    "levels" => ["Nursery", "Primary (P1-P6)", "Secondary (S1-S6)"]
]);
?>
