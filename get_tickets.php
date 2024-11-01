<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, GET");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
header("Access-Control-Allow-Credentials: true");
// Turn off error display for production
ini_set('display_errors', 0);
error_reporting(E_ALL);

session_start();
include 'db.php'; 

if (!isset($_SESSION['u_id'])) {
    echo json_encode(['logged_in' => false, 'redirect' => '/signin']);
    exit();
}
$u_id = $_SESSION['u_id'];

try {
    // Prepare and execute the main tickets query
    $stmt = $conn->prepare("SELECT * FROM tickets WHERE u_id = :u_id");
    $stmt->bindParam(':u_id', $u_id, PDO::PARAM_INT);
    $stmt->execute();
    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get total tickets, open tickets, and resolved tickets for widgets
    $totalStmt = $conn->prepare("SELECT COUNT(*) AS total_tickets FROM tickets WHERE u_id = :u_id");
    $totalStmt->bindParam(':u_id', $u_id, PDO::PARAM_INT);
    $totalStmt->execute();
    $totalTickets = $totalStmt->fetchColumn();

    $openStmt = $conn->prepare("SELECT COUNT(*) AS open_tickets FROM tickets WHERE u_id = :u_id AND status = 'Open'");
    $openStmt->bindParam(':u_id', $u_id, PDO::PARAM_INT);
    $openStmt->execute();
    $openTickets = $openStmt->fetchColumn();

    $resolvedStmt = $conn->prepare("SELECT COUNT(*) AS resolved_tickets FROM tickets WHERE u_id = :u_id AND status = 'Resolved'");
    $resolvedStmt->bindParam(':u_id', $u_id, PDO::PARAM_INT);
    $resolvedStmt->execute();
    $resolvedTickets = $resolvedStmt->fetchColumn();

    // Return the response as JSON
    echo json_encode([
        'tickets' => $tickets,
        'widgets' => [
            'totalTickets' => $totalTickets,
            'openTickets' => $openTickets,
            'resolvedTickets' => $resolvedTickets
        ]
    ]);

} catch (PDOException $e) {
    // Return error as JSON
    echo json_encode(['error' => $e->getMessage()]);
}
