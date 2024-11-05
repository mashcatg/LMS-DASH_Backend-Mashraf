<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL); // Add this line to enable error reporting

header("Access-Control-Allow-Origin: http://lms.ennovat.com:3000");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

session_start();
include 'db.php';

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Email or password is missing.']);
    exit();
}

try {
    $stmt = $conn->prepare("SELECT * FROM users WHERE u_email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['u_pass'])) {
        if ($user['verified'] == 1) {
            // Login successful, set session and cookie
            $_SESSION['u_id'] = $user['u_id'];
            $_SESSION['u_name'] = $user['u_name'];

            $token = bin2hex(random_bytes(32));
            setcookie("auth_token", $token, time() + (3 * 30 * 24 * 60 * 60), "/", "", false, true); 

            $stmt = $conn->prepare("UPDATE users SET token = :token WHERE u_email = :email");
            $stmt->execute([':token' => $token, ':email' => $email]);

            echo json_encode(['success' => true, 'message' => 'Login successful', 'verified' => 1, 'redirect' => '/']);
        } else {
            // User is not verified, send OTP
            $otp = rand(100000, 999999);
            $stmt = $conn->prepare("UPDATE users SET otp = :otp WHERE u_email = :email");
            $stmt->execute([':otp' => $otp, ':email' => $email]);

            // Code to send OTP via email

            echo json_encode(['success' => true, 'message' => 'OTP sent. Please verify.', 'verified' => 0]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid email or password.']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
