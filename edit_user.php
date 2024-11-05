<?php
header("Access-Control-Allow-Origin: http://lms.ennovat.com:3000");
header("Access-Control-Allow-Methods: POST, GET");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
header("Access-Control-Allow-Credentials: true");

ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
include 'db.php'; 

if (!isset($_SESSION['u_id'])) {
    echo json_encode(['logged_in' => false, 'redirect' => '/signin']);
    exit();
}
$u_id = $_SESSION['u_id']; 

try {
    // Handle GET request to fetch user data
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $stmt = $conn->prepare("SELECT u_name, u_email, u_phone, u_address FROM users WHERE u_id = :u_id");
        $stmt->bindParam(':u_id', $u_id, PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            echo json_encode($user);
        } else {
            echo json_encode(['error' => 'User not found.']);
        }
    }

    // Handle POST request to update user data
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);
        $name = $data['name'];
        $phone = $data['phone'];
        $address = $data['address'];

        $stmt = $conn->prepare("UPDATE users SET u_name = :name, u_phone = :phone, u_address = :address WHERE u_id = :u_id");
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':u_id', $u_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => 'User updated successfully.']);
        } else {
            echo json_encode(['error' => 'Failed to update user.']);
        }
    }
} catch (Exception $e) {
    echo json_encode(['error' => 'An error occurred: ' . $e->getMessage()]);
}
?>
