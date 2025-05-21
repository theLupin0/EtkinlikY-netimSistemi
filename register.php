<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $userSurname = $_POST['userSurname'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Şifreyi güvenli şekilde hash'liyoruz.

    // Veritabanı bağlantısı
    $conn = new mysqli('localhost', 'root', '746348', 'etkinlik_db');

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "INSERT INTO users (username,userSurname,email, password, is_verified, role) VALUES ('$username', '$userSurname','$email', '$password',0,'user')";

    if ($conn->query($sql) === TRUE) {
        echo "Kayıt başarılı!";
        header("Location: login.php");
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kayıt Ol</title>
    <link rel="stylesheet" href="css.file/styleRegister.css">
</head>
<body>
    <h2 class="baslik">Kayıt Ol</h2>
    <form method="POST" action="">
        <label for="username">İsim:</label>
        <input type="text" id="username" name="username" required><br><br>

        <label for="userSurname">Soyisim:</label>
        <input type="text" id="userSurname" name="userSurname" required><br><br>

        <label class="mail" for="email">E-posta:</label>
        <input type="email" id="email" name="email" required><br><br>

        <label class="pas" for="password">Şifre:</label>
        <input type="password" id="password" name="password" required><br><br>

        <button type="submit">Kayıt Ol</button><br><br><br>
    </form>
    <div><center><p class="center-text">Hesabınız var mı? 
        <a href="login.php">Giriş Yapın</a>
    </p></center>
    </div>
    
</body>
</html>
