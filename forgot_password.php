<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

session_start();
include 'db.php';

$email = $_POST['email'] ?? '';

if (empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Email is missing.']);
    exit();
}

try {
    $stmt = $conn->prepare("SELECT * FROM users WHERE u_email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Send OTP
        $otp = rand(100000, 999999);
        $stmt = $conn->prepare("UPDATE users SET otp = :otp WHERE u_email = :email");
        $stmt->execute([':otp' => $otp, ':email' => $email]);

        // Send OTP via email (Replace this with actual email code)
        // mail($email, 'Password Reset OTP', "Your OTP code is $otp");

        echo json_encode(['success' => true, 'message' => 'OTP sent. Please check your email.', 'redirect' => "/otp?email=$email"]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Email not found.']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
