<?php
header("Access-Control-Allow-Origin: http://lms.ennovat.com:3000");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
session_start();
include 'db.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL); // Add this line to enable error reporting

$email = $_GET['email'] ?? '';
$otp = $_POST['otp'] ?? '';
if (!isset($_SESSION['u_id'])) {
    echo json_encode(['logged_in' => true, 'redirect' => '/']);
    exit();
}
if (empty($email) || empty($otp)) {
    echo json_encode(['success' => false, 'message' => "Email or OTP is missing."]);
    exit();
}

try {
    $stmt = $conn->prepare("SELECT * FROM users WHERE u_email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $user['otp'] == $otp) {
        // Mark user as verified and remove OTP
        $stmt = $conn->prepare("UPDATE users SET verified = 1, otp = NULL WHERE u_email = ?");
        $stmt->execute([$email]);

        // Start session and set cookie
        $_SESSION['u_id'] = $user['u_id'];
        $_SESSION['u_name'] = $user['u_name'];

        // Generate token and set 3-month cookie
        $token = bin2hex(random_bytes(32));
        setcookie("auth_token", $token, time() + (3 * 30 * 24 * 60 * 60), "/", "", false, true);

        // Store the token in the database
        $stmt = $conn->prepare("UPDATE users SET token = :token WHERE u_email = :email");
        $stmt->execute([':token' => $token, ':email' => $email]);

        echo json_encode(['success' => true, 'message' => 'Verification successful', 'redirect' => '/']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid OTP.']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
