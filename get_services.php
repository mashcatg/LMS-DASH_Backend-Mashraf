<?php
header("Access-Control-Allow-Origin: http://lms.ennovat.com:3000");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");
session_start();

if (!isset($_SESSION['u_id'])) {
    echo json_encode(['error' => 'User not authenticated']);
    exit();
}

include 'db.php';

$u_id = $_SESSION['u_id'];

try {
    $stmt = $conn->prepare("SELECT id, company_name FROM services WHERE u_id = :u_id");
    $stmt->bindParam(':u_id', $u_id, PDO::PARAM_INT);
    $stmt->execute();
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($services);
} catch (Exception $e) {
    error_log('Database error: ' . $e->getMessage());
    echo json_encode(['error' => 'An internal error occurred.']);
}
?>
