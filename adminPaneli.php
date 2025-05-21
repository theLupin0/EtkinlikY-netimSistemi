<?php
session_start();

// Admin kontrolü
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    die("Bu sayfaya yalnızca admin girebilir.");
}

// Veritabanı bağlantısı
$conn = new mysqli("localhost", "root", "746348", "etkinlik_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Onay işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_type']) && $_POST['form_type'] === 'onay') {
    $user_id = $_POST['id'];
    $sql = "UPDATE users SET is_verified = 1 WHERE id = '$user_id'";
    $conn->query($sql);
    header("Refresh:0; url=adminpaneli.php");
    exit;
}

// Duyuru ekleme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_type']) && $_POST['form_type'] === 'duyuru') {
    $baslik = $_POST['baslik'];
    $icerik = $_POST['icerik'];

    $sqlDuy = "INSERT INTO duyurular (baslik, icerik) 
               VALUES ('$baslik', '$icerik')";
    if ($conn->query($sqlDuy) === TRUE) {
        echo "Duyuru Eklendi";
    } else {
        echo "Hata: " . $sqlDuy . "<br>" . $conn->error;
    }
}

//Etkinlik silme işlemi 
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['form_type']) && $_POST['form_type'] === 'silf') {
    $id = $_POST['ID'];

    $sql = "DELETE FROM etkinlikler WHERE id='$id'";
    if ($conn->query($sql) === TRUE) {
        echo "Etkinlik Silindi";
    } else {
        echo "Hata: " . $sql . "<br>" . $conn->error;
    }
    header("Refresh:2; url=anaEkran.php");
    exit;
}

//Etkinlik düzenleme işleme
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['form_type']) && $_POST['form_type'] === 'duzenlef') {
    $id = $_POST['ID'];

    // Mevcut veriyi al
    $stmt = $conn->prepare("SELECT baslik, tur, aciklama, et_tarih, yer, kapasite,et_yayin FROM etkinlikler WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($db_baslik, $db_tur, $db_aciklama, $db_tarih, $db_yer, $db_kapasite, $db_yayin);
    $stmt->fetch();
    $stmt->close();

    // Yeni verileri al (eğer boşsa eski değerle değiştir)
    $baslik = !empty($_POST['et_baslik']) ? $_POST['et_baslik'] : $db_baslik;
    $tur = !empty($_POST['tur']) ? $_POST['tur'] : $db_tur;
    $aciklama = !empty($_POST['aciklama']) ? $_POST['aciklama'] : $db_aciklama;
    $et_tarih = !empty($_POST['et_tarih']) ? $_POST['et_tarih'] : $db_tarih;
    $yer = !empty($_POST['yer']) ? $_POST['yer'] : $db_yer;
    $kapasite = !empty($_POST['kapasite']) ? $_POST['kapasite'] : $db_kapasite;
    $yayin = !empty($_POST['yayin']) ? $_POST['yayin'] : $db_yayin;

    // Güncelle
    $sql = "UPDATE etkinlikler SET 
                baslik = ?, 
                tur = ?, 
                aciklama = ?, 
                et_tarih = ?, 
                yer = ?, 
                kapasite = ? ,
                et_yayin = ?
            WHERE id = ?";

    $result = $conn->prepare($sql);
    $result->bind_param("sssssiii", $baslik, $tur, $aciklama, $et_tarih, $yer, $kapasite, $yayin, $id);

    if ($result->execute()) {
        echo "Güncellendi";
        header("Location: anaEkran.php");
    } else {
        echo "Hata: " . $result->error;
    }
}


// Onay bekleyen kullanıcıları çekme
$sql = "SELECT * FROM users WHERE is_verified = 0";
$result = $conn->query($sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <title>Admin Paneli</title>
    <link rel="stylesheet" href="css.file/adminPaneli.css">
</head>

<body>
    <h1>Admin Paneli</h1>
    <div class="panel">

        <div class="Onay" style="width: 700px;">
            <h2>Onay Bekleyen Kullanıcılar</h2>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($user = $result->fetch_assoc()): ?>
                    <p><br><?= $user['username'] ?> - <?= $user['userSurname'] ?><br><?= $user['email'] ?></p>
                    <form method="POST" action="">
                        <input type="hidden" name="form_type" value="onay">
                        <input type="hidden" name="id" value="<?= $user['id'] ?>">
                        <br>
                        <button class="btn" type="submit">Onayla</button>
                    </form>
                <?php endwhile; ?>
            <?php else: ?>
                <br>
                <p>Onay bekleyen kullanıcı yok.</p>
            <?php endif; ?>
        </div>

        <div class="etkinlik">
            <h2>Etkinlikler</h2>
            <div style="display: flex; gap: 20%; align-items: center; padding-left: 25%; padding-right: 25%;">
                <label><input type="radio" name="secim" value="ekle" checked>Ekle</label>
                <label><input type="radio" name="secim" value="sil">Sil</label>
                <label><input type="radio" name="secim" value="duzenle">Düzenle</label>
            </div>
            <hr style=" margin-left: 12.5%; margin-right: 12.5%;">
            <br>
            <div id="eklef">
                <form action="et_ekle.php" method="POST">
                    <label for="et_baslik">Başlık</label><br>
                    <input type="text" name="et_baslik" required placeholder="Etkinlik Başlığı"><br>
                    <br>
                    <label for="tur">Tür</label><br>
                    <div class="tur-ilgi">
                        <label><input type="radio" name="tur" value="muzik">Müzik</label>
                        <label><input type="radio" name="tur" value="teknoloji">Teknoloji</label>
                        <label><input type="radio" name="tur" value="sanat">Sanat</label>
                        <label><input type="radio" name="tur" value="spor">Spor</label>
                    </div>
                    <hr style=" margin-left: 12.5%; margin-right: 12.5%;">
                    <label for="ilgi">Tür (3 adet)</label><br>
                    <div class="ilgi">
                        <label><input type="checkbox" name="ilgi[]" value="muzik">Müzik</label>
                        <label><input type="checkbox" name="ilgi[]" value="spor">Spor</label>
                        <label><input type="checkbox" name="ilgi[]" value="teknoloji">Teknoloji</label>
                        <label><input type="checkbox" name="ilgi[]" value="sanat">Sanat</label>
                        <label><input type="checkbox" name="ilgi[]" value="doga">Doğa</label>
                        <label><input type="checkbox" name="ilgi[]" value="rekabet">Rekabet</label>
                        <label><input type="checkbox" name="ilgi[]" value="kitap">Kitap</label>
                        <label><input type="checkbox" name="ilgi[]" value="oyun">Oyun</label>
                        <label><input type="checkbox" name="ilgi[]" value="yemek">Yemek</label>
                        <label><input type="checkbox" name="ilgi[]" value="seyehat">Seyehat</label>
                        <label><input type="checkbox" name="ilgi[]" value="film">Film</label>
                        <label><input type="checkbox" name="ilgi[]" value="fotograf">Fotoğraf</label>
                        <label><input type="checkbox" name="ilgi[]" value="grafik">Grafik</label>
                        <label><input type="checkbox" name="ilgi[]" value="tarih">Tarih</label>
                        <label><input type="checkbox" name="ilgi[]" value="uzay">Uzay</label>
                        <label><input type="checkbox" name="ilgi[]" value="bilim">Bilim</label>
                        <label><input type="checkbox" name="ilgi[]" value="macera">Macera</label>
                        <label><input type="checkbox" name="ilgi[]" value="otomobil">Otomobil</label>
                    </div>
                    <hr style=" margin-left: 12.5%; margin-right: 12.5%;">

                    <label for="aciklama">Açıklama</label><br>
                    <textarea type="text" name="aciklama" placeholder="Etkinlik Metni" style="width: 74%; font-size: 17px; border-radius: 8px;"></textarea><br>
                    <br>
                    <label for="date">Tarih</label><br>
                    <input type="date" name="et_tarih" required placeholder="Etkinlik Tarihi"><br>
                    <br>
                    <label for="yer">Yer</label><br>
                    <input type="text" name="yer" placeholder="Etkinlik Yeri"><br>
                    <br>
                    <label for="ucret">Ücret</label><br>
                    <input type="number" name="ucret" required placeholder="Etkinlik Ucreti"><br>
                    <br>
                    <label for="kapasite">Kontenjan</label><br>
                    <input type="number" name="kapasite" required placeholder="Etkinlik Kontenjanı"><br><br>
                    <button class="btn" type="submit">Ekle</button>
                </form>
            </div>

            <div id="silf" style="display: none;">
                <form action="" method="POST">
                    <input type="hidden" name="form_type" value="silf">
                    <label for="">Etkinlik ID</label><br>
                    <input type="number" name="ID" required placeholder="ID"><br><br>
                    <button class="btn" type="submit">Sil</button>
                </form>
                <!--Admin ana sayfada etkinlikleri ID ile görecek , sadece admin-->
            </div>

            <div id="duzenlef" style="display: none;">
                <form action="" method="POST">
                    <input type="hidden" name="form_type" value="duzenlef">

                    <label for="">Etkinlik ID</label><br>
                    <input type="number" name="ID" required placeholder="ID"><br><br>
                    <label for="et_baslik">Başlık</label><br>
                    <input type="text" name="et_baslik" placeholder="Etkinlik Başlığı"><br>
                    <br>
                    <label for="tur">Tür</label><br>
                    <input type="text" name="tur" placeholder="Etkinlik Türü"><br>
                    <br>
                    <label for="aciklama">Açıklama</label><br>
                    <textarea type="text" name="aciklama" placeholder="Etkinlik Metni" style="width: 74%; font-size: 17px; border-radius: 8px;"></textarea><br>
                    <br>
                    <label for="date">Tarih</label><br>
                    <input type="date" name="et_tarih" placeholder="Etkinlik Tarihi"><br>
                    <br>
                    <label for="yer">Yer</label><br>
                    <input type="text" name="yer" placeholder="Etkinlik Yeri"><br>
                    <br>
                    <label for="">Yayınlama</label><br>
                    <input type="number" name="yayin" min="1" max="2 " step="1" placeholder="1: Yayında 2: Yayında Değil"><br>
                    <br>
                    <label for="kapasite">Kontenjan</label><br>
                    <input type="number" name="kapasite" placeholder="Etkinlik Kontenjanı"><br><br>
                    <button class="btn" type="submit">Kaydet</button>
                </form>
            </div>

        </div>


        <!--Admin ana sayfada etkinlikleri ID ile görecek , sadece admin . Bu işlemler burdan sonra devam edicektir-->

        <script>
            const r = document.querySelectorAll('input[name="secim"]');
            const ekle = document.getElementById("eklef");
            const sil = document.getElementById("silf");
            const duzenle = document.getElementById("duzenlef");

            r.forEach(radio => {
                radio.addEventListener("change", () => {
                    eklef.style.display = (radio.value === "ekle") ? "block" : "none";
                    silf.style.display = (radio.value === "sil") ? "block" : "none";
                    duzenlef.style.display = (radio.value === "duzenle") ? "block" : "none";
                });
            })
        </script>

        <div class="duyuru">
            <h2>Duyuru Ekle</h2>
            <form method="POST" action="">
                <input type="hidden" name="form_type" value="duyuru">
                <label for="baslik">Başlık:</label><br>
                <input type="text" id="baslik" name="baslik" required style="width: 90%;"><br><br>

                <label for="icerik">İçerik:</label><br>
                <textarea id="icerik" name="icerik" rows="6" required
                    style="width: 90%; font-size: 17px; border-radius: 5px; resize: vertical;"></textarea><br><br>

                <button class="btn" type="submit">Duyuru Ekle</button>
            </form>
        </div>

    </div>
</body>

</html>