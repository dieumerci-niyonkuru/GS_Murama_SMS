<?php
require_once dirname(__DIR__) . '/config/Database.php';

class StudentController {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function getAll() {
        $query = "SELECT * FROM students ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        http_response_code(200);
        echo json_encode([
            "status" => "success",
            "count" => count($students),
            "data" => $students
        ]);
    }
    
    public function create() {
        $data = json_decode(file_get_contents("php://input"), true);
        
        $query = "INSERT INTO students (student_id, first_name, last_name, level, class, parent_phone, parent_email) 
                  VALUES (:student_id, :first_name, :last_name, :level, :class, :parent_phone, :parent_email)";
        
        $stmt = $this->conn->prepare($query);
        
        $student_id = $data['student_id'] ?? 'GSM' . date('Y') . rand(1000, 9999);
        
        $stmt->bindParam(':student_id', $student_id);
        $stmt->bindParam(':first_name', $data['first_name']);
        $stmt->bindParam(':last_name', $data['last_name']);
        $stmt->bindParam(':level', $data['level']);
        $stmt->bindParam(':class', $data['class']);
        $stmt->bindParam(':parent_phone', $data['parent_phone']);
        $stmt->bindParam(':parent_email', $data['parent_email']);
        
        if($stmt->execute()) {
            http_response_code(201);
            echo json_encode([
                "status" => "success",
                "message" => "Student created successfully",
                "student_id" => $student_id
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                "status" => "error",
                "message" => "Unable to create student"
            ]);
        }
    }
    
    public function getOne($id) {
        $query = "SELECT * FROM students WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $student = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($student) {
            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "data" => $student
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                "status" => "error",
                "message" => "Student not found"
            ]);
        }
    }
    
    public function update($id) {
        $data = json_decode(file_get_contents("php://input"), true);
        
        $query = "UPDATE students SET first_name = :first_name, last_name = :last_name, 
                  level = :level, class = :class, parent_phone = :parent_phone, 
                  parent_email = :parent_email WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':first_name', $data['first_name']);
        $stmt->bindParam(':last_name', $data['last_name']);
        $stmt->bindParam(':level', $data['level']);
        $stmt->bindParam(':class', $data['class']);
        $stmt->bindParam(':parent_phone', $data['parent_phone']);
        $stmt->bindParam(':parent_email', $data['parent_email']);
        $stmt->bindParam(':id', $id);
        
        if($stmt->execute()) {
            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "message" => "Student updated successfully"
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                "status" => "error",
                "message" => "Unable to update student"
            ]);
        }
    }
    
    public function delete($id) {
        $query = "DELETE FROM students WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        
        if($stmt->execute()) {
            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "message" => "Student deleted successfully"
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                "status" => "error",
                "message" => "Unable to delete student"
            ]);
        }
    }
}
?>
