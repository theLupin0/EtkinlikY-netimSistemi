<?php
header("Content-Type: application/json");

$host = "localhost";
$username = "root";
$password = "746348";
$dbname = "etkinlik_db";

$ilgi_raw = isset($_GET['ilgi']) ? $_GET['ilgi'] : '';
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(["error" => "Veritabanı bağlantı hatası"]);
    exit;
}

$etkinlik = [];
$tur = isset($_GET['tur']) ? $_GET['tur'] : '';
$ilgi = $conn->real_escape_string($ilgi_raw);

if (!empty($ilgi)) {
    $sql = "SELECT * FROM etkinlikler 
            WHERE et_yayin = 1 AND (
                ilgi_alani_1 = ? OR 
                ilgi_alani_2 = ? OR 
                ilgi_alani_3 = ?
            ) 
            ORDER BY et_tarih";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("sss", $ilgi, $ilgi, $ilgi);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $etkinlik[] = $row;
        }
        $stmt->close();
    }
} elseif ($tur !== '') {
    $sql = "SELECT * FROM etkinlikler WHERE tur = ? AND et_yayin = 1 ORDER BY et_tarih";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("s", $tur);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $etkinlik[] = $row;
        }
        $stmt->close();
    }
} else {
    $sql = "SELECT * FROM etkinlikler WHERE et_yayin = 1 ORDER BY et_tarih";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $etkinlik[] = $row;
    }
}

echo json_encode($etkinlik, JSON_UNESCAPED_UNICODE);
$conn->close();
