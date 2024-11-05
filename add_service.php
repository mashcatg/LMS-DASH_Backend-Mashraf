<?php 

header("Access-Control-Allow-Origin: http://lms.ennovat.com:3000");
header("Access-Control-Allow-Methods: POST, OPTIONS"); // Allow OPTIONS for preflight requests
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");
session_start(); 
include 'db.php';

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0); // Exit for preflight requests
}

$data = json_decode(file_get_contents("php://input"), true);
if (!isset($_SESSION['u_id'])) {
    echo json_encode(['logged_in' => false, 'redirect' => '/signin']);
    exit();
}

$u_id = $_SESSION['u_id']; // Get user ID from session

try {
    // Sanitize input data
    $subdomain = $data['subdomain'];
    $company = $data['company'];
    $ad_phone = $data['ad_phone'];
    $ad_pass = $data['ad_pass'];


    // Input validation
    if (empty($subdomain) || empty($company) || empty($ad_phone) || empty($ad_pass)) {
        throw new Exception('All fields are required.');
    }

    // Limit input lengths to avoid security risks
    if (strlen($subdomain) > 100 || strlen($company) > 100 || strlen($ad_phone) > 20 || strlen($ad_pass) > 100) {
        throw new Exception('Input exceeds allowed length.');
    }

    // Check if subdomain already exists
    $check_sql = "SELECT service_id FROM services WHERE sub_domain = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->execute([$subdomain]);

    if ($check_stmt->rowCount() > 0) {
        throw new Exception('The subdomain is already in use.');
    }

    // Generate random service ID
    $service_id = rand(10000, 99999);

    // Hash the password securely
    $hashed_pass = password_hash($ad_pass, PASSWORD_DEFAULT);

    // Set status and creation time
    $status = "Active";
    $created_at = date('Y-m-d H:i:s');

    // Insert data into the `services` table
    $sql = "INSERT INTO services (service_id, sub_domain, company_name, ad_phone, ad_pass, u_id, status, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$service_id, $subdomain, $company, $ad_phone, $hashed_pass, $u_id, $status, $created_at]);

    // Insert into `admins` table
    $admin_sql = "INSERT INTO admins (admin_number, admin_password, service_id) VALUES (?, ?, ?)";
    $admin_stmt = $conn->prepare($admin_sql);
    $admin_stmt->execute([$ad_phone, $hashed_pass, $service_id]);

    // Send a success response
    echo json_encode(['success' => true, 'message' => 'Service and admin created successfully']);
} catch (Exception $e) {
    // Log the exception to debug
    file_put_contents("log.txt", "Error: ".$e->getMessage()."\n", FILE_APPEND);  // Log error
    // Send an error response with the exception message
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}