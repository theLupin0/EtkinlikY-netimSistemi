<?php
session_start();
require_once "db.php";

$data = json_decode(file_get_contents("php://input"), true);
$user_id = $_SESSION['user_id'];

$odeme_yontemi = $data['odeme'];
$secimler = $data['secimler'];

foreach ($secimler as $secim) {
    $etkinlik_id = $secim['etkinlik_id'];
    $bilet_turu = $secim['bilet_turu'];
    $fiyat = $secim['fiyat'];

    // Kontenjan kontrolü
    $kontrol = $conn->prepare("SELECT kapasite FROM etkinlikler WHERE id = ?");
    $kontrol->execute([$etkinlik_id]);
    $k = $kontrol->fetch(PDO::FETCH_ASSOC);

    if ($k['kapasite'] <= 0) {
        echo json_encode(['error' => "Etkinlik ID $etkinlik_id kontenjan dolu."]);
        exit();
    }

    // Sepeti güncelle
    $update = $conn->prepare("UPDATE sepet SET satin_alma = 1, bilet_tur = ?, odeme_yontemi = ?, ucret = ? WHERE user_id = ? AND et_id = ?");
    $update->execute([$bilet_turu, $odeme_yontemi, $fiyat, $user_id, $etkinlik_id]);

    // Kontenjan azalt
    $kontenjanAzalt = $conn->prepare("UPDATE etkinlikler SET kapasite = kapasite - 1 WHERE id = ? AND kapasite > 0");
    $kontenjanAzalt->execute([$etkinlik_id]);
}

echo json_encode(['success' => true]);
