<?php
session_start(); //Oturum başlatır

$error = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Veritabanı bağlantısı
    $conn = new mysqli('localhost', 'root', '746348', 'etkinlik_db');

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {

            if ($user['is_verified'] == 0) {
                $error = "Hesabınız henüz admin tarafından onaylanmamış.";
            } else {
                // Giriş başarılı
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                if ($user['first_login'] == 1) {
                    header("Location: refreshPassword.php");
                } else {
                    $sql1 = "UPDATE users SET is_active = 1 WHERE email='$email'";
                    $conn->query($sql1);
                    header("Location: anaEkran.php");
                    exit;
                }
            }
        } else {
            $error = "Giriş Bilgileri Hatalı";
        }
    } else {
        $error = "Kullanıcı bulunamadı";
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap</title>
    <link rel="stylesheet" href="css.file/styleLogin.css">
</head>

<body>
    <h2>Giriş Yap</h2>
    <form method="POST" action="">
        <label class="mail" for="email">E-posta:</label>
        <input type="email" id="email" name="email" required><br><br>

        <label class="pas" for="password">Şifre:</label>
        <input type="password" id="password" name="password" required><br><br>

        <button type="submit">Giriş Yap</button><br><br><br>
        <p>Hesabınız yok mu? <a href="register.php">Kayıt Olun</a></p>
        <br>
        <?php if (!empty($error)) : ?>
            <center><label id="errorMessage"><?php echo $error ?></label></center>
        <?php endif; ?>
    </form>
</body>

</html>