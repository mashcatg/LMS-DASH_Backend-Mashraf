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
    
    $stmt = $conn->prepare("SELECT id, service_id, company_name AS company, sub_domain, status, created_at FROM services WHERE u_id = $u_id ORDER BY created_at DESC");
    $stmt->execute();
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate total and active services
    $totalServices = count($services);
    $activeServices = count(array_filter($services, function($service) {
        return strtolower($service['status']) === 'active';
    }));

    // Prepare the JSON response
    $response = [
        'services' => $services,
        'totalServices' => $totalServices,
        'activeServices' => $activeServices
    ];

    echo json_encode($response);
} catch (Exception $e) {
    // Catch any exceptions, including fatal errors
    echo json_encode(['error' => 'An error occurred: ' . $e->getMessage()]);
}
?>
