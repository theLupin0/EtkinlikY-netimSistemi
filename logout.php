<?php 
session_start();

if(isset($_SESSION['email'])){
    //Veritabanı
    $conn = new mysqli('localhost','root','746348','etkinlik_db');
    if($conn->connect_error){
        die('Connection failed: '. $conn->connect_error);
    }
    $email = $_SESSION['email'];
    $sql = "UPDATE users SET is_active = 0 WHERE email='$email'";
    $conn->query($sql);
    $conn->close();
}

session_unset();
session_destroy();

header("Location: login.php");
exit();
?>