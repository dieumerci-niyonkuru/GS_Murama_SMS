<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);
require_once '../config/Database.php';
$db = (new Database())->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (isset($_GET['action']) && $_GET['action'] === 'borrowed') {
        $stmt = $db->query("SELECT b.*, s.first_name, s.last_name FROM library_borrows lb JOIN library_books b ON lb.book_id = b.book_id JOIN students s ON lb.student_id = s.student_id WHERE lb.status = 'Borrowed'");
        $borrows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(["success" => true, "data" => $borrows]);
    } else {
        $stmt = $db->query("SELECT * FROM library_books ORDER BY title");
        $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(["success" => true, "data" => $books]);
    }
} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    if (isset($data['action']) && $data['action'] === 'borrow') {
        $book_id = $data['book_id'];
        $student_id = $data['student_id'];
        $due_date = date('Y-m-d', strtotime('+14 days'));
        $stmt = $db->prepare("INSERT INTO library_borrows (book_id, student_id, borrow_date, due_date) VALUES (?, ?, CURDATE(), ?)");
        $stmt->execute([$book_id, $student_id, $due_date]);
        $db->prepare("UPDATE library_books SET available = available - 1 WHERE book_id = ?")->execute([$book_id]);
        echo json_encode(["success" => true, "message" => "Book borrowed", "due_date" => $due_date]);
    } elseif (isset($data['action']) && $data['action'] === 'return') {
        $borrow_id = $data['borrow_id'];
        $stmt = $db->prepare("UPDATE library_borrows SET return_date = CURDATE(), status = 'Returned' WHERE id = ?");
        $stmt->execute([$borrow_id]);
        $book = $db->prepare("SELECT book_id FROM library_borrows WHERE id = ?")->execute([$borrow_id]);
        // Get book_id from borrow record
        $book_stmt = $db->prepare("SELECT book_id FROM library_borrows WHERE id = ?");
        $book_stmt->execute([$borrow_id]);
        $book_id = $book_stmt->fetchColumn();
        $db->prepare("UPDATE library_books SET available = available + 1 WHERE book_id = ?")->execute([$book_id]);
        echo json_encode(["success" => true, "message" => "Book returned"]);
    } else {
        // Add new book
        $book_id = 'BK' . time();
        $stmt = $db->prepare("INSERT INTO library_books (book_id, title, author, isbn, quantity, available, location) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$book_id, $data['title'], $data['author'], $data['isbn'], $data['quantity'], $data['quantity'], $data['location']]);
        echo json_encode(["success" => true, "book_id" => $book_id]);
    }
} elseif ($method === 'DELETE') {
    $book_id = $_GET['book_id'];
    $db->prepare("DELETE FROM library_books WHERE book_id = ?")->execute([$book_id]);
    echo json_encode(["success" => true]);
}
?>
