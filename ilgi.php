<?php
header('Content-Type: application/json');

$mysqli = new mysqli("localhost", "root", "746348", "etkinlik_db");

if ($mysqli->connect_error) {
    echo json_encode(["error" => "Veritabanına bağlanılamadı."]);
    exit;
}

$ilgi = isset($_GET['ilgi']) ? $mysqli->real_escape_string($_GET['ilgi']) : '';

if ($ilgi === '') {
    echo json_encode([]);
    exit;
}

$sql = "
    SELECT * FROM etkinlikler 
    WHERE ilgi_alanlari_1 = '$ilgi' 
       OR ilgi_alanlari_2 = '$ilgi' 
       OR ilgi_alanlari_3 = '$ilgi'
    ORDER BY et_tarih ASC
";

$result = $mysqli->query($sql);

$etkinlikler = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $etkinlikler[] = $row;
    }
}

echo json_encode($etkinlikler);
$mysqli->close();
