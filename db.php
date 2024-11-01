<?php
// Connect to the database
$host = 'localhost';
$dbname = 'lms';
$username = 'root'; 
$password = '';
$port = '3306';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;port=$port", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
// include 'check_auth.php';
?>