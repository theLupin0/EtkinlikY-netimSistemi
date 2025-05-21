<?php

$host = "localhost";
$dbname = "etkinlik_db";
$username = "root";
$password = "746348";

//Veritabanı Bağlantısı
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password); //PDO : PHP DATA OBJECTS
} catch (PDOException $e) {
    die(json_encode([
        "kontrol" => false,
        "mesaj" => "Connection failed: " . $e->getMessage()
    ]));
}

header("Content-Type: application/json"); //JSon formatında gönder

$baslik = $_POST['et_baslik'] ?? null;
$tur = $_POST['tur'] ?? null;
$aciklama = $_POST['aciklama'] ?? null;
$et_tarih = $_POST['et_tarih'] ?? null;
$yer = $_POST['yer'] ?? null;
$ucret = $_POST['ucret'] ?? null;
$kapasite = $_POST['kapasite'] ?? null;
$ilgi = $_POST['ilgi'] ?? [];
$ilgi1 = $ilgi[0] ?? null;
$ilgi2 = $ilgi[1] ?? null;
$ilgi3 = $ilgi[2] ?? null;
//Bilet sepet ekranında yapılacaktır.


if (!$baslik || !$et_tarih) {
    echo json_encode([
        "kontrol" => false,
        "mesaj" => "Başlık ve tarih zorunludur."
    ]);
    exit;
}

//Veritabanına ekle
$sql = "INSERT INTO etkinlikler (baslik,tur,aciklama,et_tarih,yer,kapasite,ucret,ilgi_alani_1,ilgi_alani_2,ilgi_alani_3) VALUES (:et_baslik, :tur, :aciklama, :et_tarih, :yer,:kapasite,:ucret,:ilgi1,:ilgi2,:ilgi3)"; //Parametredir doğrudan veri yazılmaz
$result = $pdo->prepare($sql);
$sonuc = $result->execute([
    'et_baslik' => $baslik,
    'tur' => $tur,
    'aciklama' => $aciklama,
    'et_tarih' => $et_tarih,
    'yer' => $yer,
    'ucret' => $ucret,
    'kapasite' => $kapasite,
    'ilgi1' => $ilgi1,
    'ilgi2' => $ilgi2,
    'ilgi3' => $ilgi3
]); //Güvenlik için önce parametre şeklinde : SQL Injection

if ($sonuc) {
    echo json_encode([
        "kontrol" => true,
        "mesaj" => "Etkinlik Eklendi"
    ]);
    header("Refresh: 2; url=anaEkran.php");
    exit;
} else {
    echo json_encode([
        "kontrol" => false,
        "mesaj" => "Etkinlik Eklenemdi"
    ]);
}
