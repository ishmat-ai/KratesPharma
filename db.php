<?php
ini_set('display_errors', 1);  
error_reporting(E_ALL);

$servername = "localhost";
$username = "root";  
$password = "";      
$dbname = "medapp";  

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);  
} 
?>
