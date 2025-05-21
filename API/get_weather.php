<?php

header('Content-Type: application/json');

try {
    $pdo = new PDO("mysql:host=localhost;dbname=etkinlik_db;charset=utf8", "root", "746348");
} catch (PDOException $e) {
    echo json_encode(['error' => 'Veritabanı bağlantı hatası:' . $e->getMessage()]);
    exit;
}

$et_id = isset($_GET['et_id']) ? intval($_GET['et_id']) : 0;

if ($et_id <= 0) {
    echo json_encode(['error' => 'Geçersiz etkinlik ID']);
    exit;
}

$stmt = $pdo->prepare("SELECT yer, et_tarih FROM etkinlikler WHERE id= ?");
$stmt->execute([$et_id]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$result) {
    echo json_encode(["error" => "Etkinlik bulunamadı"]);
    exit;
}

$sehir = $result['yer'];
$etkinlikTarihi = new DateTime($result['et_tarih']);
$bugun = new DateTime();
$bugunStr = $bugun->format('Y-m-d');
$etkinlikStr = $etkinlikTarihi->format('Y-m-d');
$apiKey = "b30e4af1a9a649d600e8cd7b82a58534";

// Eğer etkinlik tarihi bugünse -> current weather API
if ($etkinlikStr == $bugunStr) {
    $url = "https://api.openweathermap.org/data/2.5/weather?q=" . urlencode($sehir) . "&appid=$apiKey&units=metric&lang=tr";

    $response = file_get_contents($url);
    if (!$response) {
        echo json_encode(['error' => 'Hava durumu alınamadı (anlık)']);
        exit;
    }

    $data = json_decode($response, true);

    $main = $data['weather'][0]['main'];
    $hava = $data['weather'][0]['description'];
    $sicaklik = $data['main']['temp'];
    $planlanabilir = ($main == 'Rain' || $main == 'Thunderstorm') ? 0 : 1;

    echo json_encode([
        'sehir' => $sehir,
        'hava' => $hava,
        'sicaklik' => $sicaklik,
        'planlanabilir' => $planlanabilir
    ]);
    exit;
}

// Etkinlik bugün değilse -> forecast API
$url = "https://api.openweathermap.org/data/2.5/forecast?q=" . urlencode($sehir) . "&appid=$apiKey&units=metric&lang=tr";
$response = file_get_contents($url);

if (!$response) {
    echo json_encode(['error' => 'Hava durumu alınamadı (tahmin)']);
    exit;
}

$data = json_decode($response, true);
$bulundu = false;

foreach ($data['list'] as $item) {
    if (str_starts_with($item['dt_txt'], $etkinlikStr)) {
        $main = $item['weather'][0]['main'];
        $hava = $item['weather'][0]['description'];
        $sicaklik = $item['main']['temp'];
        $planlanabilir = ($main == 'Rain' || $main == 'Thunderstorm') ? 0 : 1;
        $bulundu = true;

        echo json_encode([
            'sehir' => $sehir,
            'hava' => $hava,
            'sicaklik' => $sicaklik,
            'planlanabilir' => $planlanabilir
        ]);
        break;
    }
}

if (!$bulundu) {
    echo json_encode(['error' => 'Etkinlik tarihi için hava tahmini bulunamadı']);
}
