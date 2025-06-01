<?php
$host = "localhost";
$user = "root";
$password = ""; 
$db = "task_management";

$conn = new mysqli($host, $user, $password, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

date_default_timezone_set('Asia/Manila');
?>
