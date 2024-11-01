<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Content-Type: application/json");
session_start();

include 'db.php'; // Include database connection

$data = json_decode(file_get_contents("php://input"), true);

$ticket_id = $data['ticket_id'];
$message = $data['message'];
$sender_type = $data['sender_type'];

try {
    // Insert the new message into the database
    $stmt = $conn->prepare("INSERT INTO messages (ticket_id, message, sender_type, time) VALUES (:ticket_id, :message, :sender_type, NOW())");
    $stmt->bindParam(':ticket_id', $ticket_id, PDO::PARAM_INT);
    $stmt->bindParam(':message', $message, PDO::PARAM_STR);
    $stmt->bindParam(':sender_type', $sender_type, PDO::PARAM_STR);
    $stmt->execute();

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
