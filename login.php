<?php
session_start();
require_once 'includes/db.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];  
    $result = $conn->query("SELECT id, username, password FROM users WHERE username='$username'"); 
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {              
            echo "User ID from database: " . $user['id'] . "<br>";
            $_SESSION['LOGINUSER'] = $username; // Or whatever you want to store
            $_SESSION['USER_ID'] = $user['id']; 
            echo "Login successful! User ID set in session: " . $_SESSION['USER_ID'] . "<br>";
            header("Location: home.php");
            exit();
        } else {
            echo "Invalid password."; 
        }

    } else {
        echo "Invalid username."; 
    }
}
?>

