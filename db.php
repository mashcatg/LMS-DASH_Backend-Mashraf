<?php
// Connect to the database
$host = '127.0.0.1';
$dbname = 'lms';
$username = 'root'; 
$password = 'mashPass789!@#';
// $port = '3306';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
// include 'check_auth.php';
?>
