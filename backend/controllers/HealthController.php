<?php
class HealthController {
    public function check() {
        http_response_code(200);
        echo json_encode([
            "status" => "OK",
            "message" => "G.S. Murama API is running",
            "timestamp" => date('Y-m-d H:i:s'),
            "version" => "1.0.0",
            "school" => "G.S. Murama, Kisaro, Rwanda"
        ]);
    }
}
?>
