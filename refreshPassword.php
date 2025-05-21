<?php
session_start();
$info = "";
$conn = new mysqli('localhost', 'root', "746348", "etkinlik_db");
if ($conn->connect_error) {
    die("Connection failed" . $conn->connect_error);
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pass1 = $_POST['pass1'];
    $pass2 = $_POST['pass2'];
    $email = $_POST['email'];

    $sqlpass = "SELECT password FROM users WHERE email='$email'";
    $result = $conn->query($sqlpass);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $oldPass = $user["password"];

        if ($pass1 == $pass2) {
            if (password_verify($pass1, $oldPass)) {
                $info = "Yeni şifre eski şifrenizle aynı olamaz. Lütfen farklı bir şifre girin.";
            } else {
                $hash_password = password_hash($pass1, PASSWORD_DEFAULT);
                $sql = "UPDATE users SET password='$hash_password' WHERE email='$email'";
                $sql1 = "UPDATE users SET first_login=2 WHERE email='$email'";
                if ($conn->query($sql) == TRUE && $conn->query($sql1) == TRUE) {
                    $info = "Şifre Doğru, Giriş Ekranına Yönlendiriliyorsunuz...";
                    header("Refresh:3; url=login.php");
                    exit();
                } else {
                    echo "Hata: " . $conn->error;
                }
            }
        } else {
            $info = "Şifreleriniz Uyuşmuyor veya Aynı Şifreyi Kullanamazsınız, Lütfen Tekrar Deneyin";
        }
    } else {
        $info = "Bu e-posta adresiyle kayıtlı kullanıcı bulunamadı.";
    }
}
$conn->close();


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yenile</title>
    <link rel="stylesheet" href="css.file/styleRefreshPassword.css">
</head>

<body>
    <h1>Şifre Değiştirilmeli ⇆</h1>
    <form method="POST" action="">
        <label class="mail" for="email">E-posta:</label>
        <input type="email" id="email" name="email" required>
        <br>
        <br>
        <label class="p1" for="password">Yeni Şifre:</label>
        <input type="password" id="pas1" name="pass1" required>
        <br>
        <br>
        <label class="p2" for="password">Şifreyi Doğrula:</label>
        <input type="password" id="pas2" name="pass2" required>
        <br>
        <button type="submit">Doğrula</button>
    </form>
    <br>
    <div>
        <?php if (!empty($info)) : ?>

            <center><label id="info"><?php echo $info ?></label></center>
        <?php endif; ?>
    </div>

</body>

</html>