<?php
// CORS Headers
header("Access-Control-Allow-Origin: http://lms.ennovat.com:3000");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0); 
}

include 'db.php';
$input = file_get_contents('php://input');
$data = json_decode($input, true);
$name = $data['name'] ?? '';
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';
$confirmPassword = $data['confirmPassword'] ?? '';

// Validate input
if (empty($name) || empty($email) || empty($password) || empty($confirmPassword)) {
    http_response_code(400); // Bad Request
    echo json_encode(["status" => "error", "message" => "All fields are required."]);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400); // Bad Request
    echo json_encode(["status" => "error", "message" => "Invalid email format."]);
    exit();
}

if ($password !== $confirmPassword) {
    http_response_code(400); // Bad Request
    echo json_encode(["status" => "error", "message" => "Passwords do not match."]);
    exit();
}

// Check if email is already registered
try {
    $stmt = $conn->prepare("SELECT * FROM users WHERE u_email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        http_response_code(409); // Conflict
        echo json_encode(["status" => "error", "message" => "Email is already registered."]);
        exit();
    }
} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(["status" => "error", "message" => "Database query failed: " . $e->getMessage()]);
    exit();
}

// Generate OTP and hashed password
$otp = rand(100000, 999999);
$u_id = rand(1000, 9999) * time();  // Create unique user ID
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

try {
    $stmt = $conn->prepare("INSERT INTO users (u_id, u_name, u_email, u_pass, created_at, otp, verified) 
                            VALUES (:u_id, :name, :email, :password, NOW(), :otp, 0)");
    $stmt->bindParam(':u_id', $u_id);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $hashedPassword);
    $stmt->bindParam(':otp', $otp);

    if ($stmt->execute()) {
        // Email sending logic (can be added later)
        http_response_code(201); // Created
        echo json_encode([
            "status" => "success",
            "message" => "User registered successfully. OTP sent to email.",
            "redirect" => "/otp?email=" . urlencode($email)
        ]);
    } else {
        http_response_code(500); // Internal Server Error
        echo json_encode(["status" => "error", "message" => "Registration failed."]);
    }
} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(["status" => "error", "message" => "Database insertion failed: " . $e->getMessage()]);
    exit();
}
?>
