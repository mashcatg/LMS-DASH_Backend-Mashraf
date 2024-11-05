<?php
header("Access-Control-Allow-Origin: http://lms.ennovat.com:3000");
header("Access-Control-Allow-Methods: POST, GET");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
header("Access-Control-Allow-Credentials: true");
session_start();
include 'db.php';

if (!isset($_SESSION['u_id'])) {
    echo json_encode(['logged_in' => false, 'redirect' => '/signin']);
    exit();
}
$u_id = $_SESSION['u_id'];
$id = $_GET['id'];

try {
    // Fetch the ticket details
    $stmt = $conn->prepare("SELECT problem_statement, description FROM tickets WHERE t_id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($ticket) {
        $limit = 100;

        // Fetch the messages related to the ticket
        $messagesStmt = $conn->prepare("SELECT * FROM messages WHERE ticket_id = :id ORDER BY time ASC LIMIT :limit");
        $messagesStmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $messagesStmt->bindParam(':id', $id, PDO::PARAM_INT);
        $messagesStmt->execute();
        $messages = $messagesStmt->fetchAll(PDO::FETCH_ASSOC);
    } else { 
        throw new Exception("Ticket not found"); 
    }

    echo json_encode([
        'ticket' => $ticket,
        'messages' => $messages
    ]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
