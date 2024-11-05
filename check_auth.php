<?php
header("Access-Control-Allow-Origin: http://lms.ennovat.com:3000");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

session_start(); 
include 'db.php';
if (!isset($_SESSION['u_id'])) {
    echo json_encode(['logged_in' => false, 'redirect' => '/signin']);
    exit();
}else {
    echo json_encode(['logged_in' => true, 'redirect' => '/']);
    exit();
}
?>
