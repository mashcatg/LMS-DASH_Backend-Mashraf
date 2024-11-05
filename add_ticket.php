<?php
header("Access-Control-Allow-Origin: http://lms.ennovat.com:3000");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true"); 
header("Content-Type: application/json");

session_start();
include 'db.php';

if (!isset($_SESSION['u_id'])) {
    echo json_encode(['logged_in' => false, 'redirect' => '/signin']);
    exit();
}

$u_id = $_SESSION['u_id']; 
$ticket_id = rand(10000, 99999);

try {
    // Decode the JSON input
    $data = json_decode(file_get_contents("php://input"), true);

    // Log the received data for debugging
    error_log(print_r($data, true));

    // Check for required fields
    if (!empty($data['issue']) && !empty($data['description']) && !empty($data['priority']) && !empty($data['service_id'])) {
        
        // Prepare the insert statement
        $stmt = $conn->prepare("INSERT INTO tickets (u_id, t_id, service_id, status, priority, problem_statement, description) 
                                VALUES (:u_id, :ticket_id, :service_id, 'open', :priority, :problem_statement, :description)");
        
        // Bind parameters
        $stmt->bindParam(':u_id', $u_id);
        $stmt->bindParam(':ticket_id', $ticket_id); // Use generated ticket_id
        $stmt->bindParam(':service_id', $data['service_id']);
        $stmt->bindParam(':priority', $data['priority']);
        $stmt->bindParam(':problem_statement', $data['issue']);
        $stmt->bindParam(':description', $data['description']);
        
        // Execute and check
        if ($stmt->execute()) {
            echo json_encode(['success' => 'Ticket created successfully.']);
        } else {
            echo json_encode(['error' => 'Failed to create ticket.']);
        }
    } else {
        echo json_encode(['error' => 'Incomplete form data.']);
    }
} catch (PDOException $e) {
    // Catch database-related errors
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    // Catch other types of errors
    echo json_encode(['error' => 'An error occurred: ' . $e->getMessage()]);
}
?>
