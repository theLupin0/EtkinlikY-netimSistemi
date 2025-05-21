<?php
$host = "localhost";           // Genellikle localhost
$dbname = "etkinlik_db";    // Veritabanı adınız
$username = "root";   // MySQL kullanıcı adınız
$password = "746348";           // MySQL şifreniz

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    // Hata gösterimini aç
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Veritabanı bağlantısı başarısız: " . $e->getMessage());
}
