<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Giris Yapmalisiniz']);
    header("Refresh:2; url=login.php");
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);

$user_id = $_GET['user_id'] ?? null;
$et_id = $_GET['etkinlik_id'] ?? null;

/*if (!isset($et_id) || !isset($user_id)) {
    echo json_encode(['error' => 'Bilgiler Eksik']);
    exit();
}*/


require_once "db.php";

if ($et_id && $user_id) {
    // Zaten sepette var mı kontrol et
    $kontrol = $conn->prepare("SELECT * FROM sepet WHERE user_id = ? AND et_id = ?");
    $kontrol->execute([$user_id, $et_id]);

    if ($kontrol->rowCount() == 0) {
        // Yoksa sepete ekle
        $ekle = $conn->prepare("INSERT INTO sepet (user_id, et_id) VALUES (?, ?)");
        $ekle->execute([$user_id, $et_id]);
    }
}


try {
    // Sepetteki etkinlikleri al
    $stmt = $conn->prepare("
        SELECT etkinlikler.id,etkinlikler.baslik, etkinlikler.et_tarih, etkinlikler.yer, etkinlikler.ucret
        FROM sepet
        JOIN etkinlikler ON sepet.et_id = etkinlikler.id
        WHERE sepet.user_id = ? AND sepet.satin_alma = 0
    ");
    $stmt->execute([$user_id]);
    $etkinlikler = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Veritabanı hatası: " . $e->getMessage();
    exit();
}
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <title>Sepetim</title>,
    <link rel="stylesheet" href="css.file/sepet.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            padding: 20px;
        }

        .sepet-kutu {
            background: white;
            padding: 20px;
            max-width: 700px;
            margin: auto;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
        }

        .etkinlik {
            border-bottom: 1px solid #ddd;
            padding: 10px 0;
        }

        .etkinlik:last-child {
            border-bottom: none;
        }

        h2 {
            margin-bottom: 20px;
        }

        .geri-btn {
            display: inline-block;
            margin-top: 20px;
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
        }

        .geri-btn:hover {
            background-color: #45a049;
        }

        .btn {
            display: inline-block;
            margin-top: 20px;
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
            border: 2px solid #4CAF50;
            position: fixed;
            margin-left: 470px;
        }

        .btn:hover {
            background-color: #45a049;
        }
    </style>
</head>

<body>
    <div class="sepet-kutu">
        <h2>Sepetinizdeki Etkinlikler</h2>

        <?php $index = 0;
        if (count($etkinlikler) > 0): ?>
            <?php foreach ($etkinlikler as $etkinlik): ?>
                <div class="etkinlik">
                    <strong>Başlık:</strong> <?= htmlspecialchars($etkinlik['baslik']) ?><br>
                    <strong>Tarih:</strong> <?= htmlspecialchars($etkinlik['et_tarih']) ?><br>
                    <strong>Konum:</strong> <?= htmlspecialchars($etkinlik['yer']) ?><br>
                    <strong>Fiyat:</strong>
                    <span id="fiyat<?= $index ?>" class="fiyat" data-orj="<?= $etkinlik['ucret'] ?>">
                        <?= number_format($etkinlik['ucret'], 2) ?> ₺
                    </span><br>

                    <label for="">Bilet Türü:</label>
                    <select onchange="guncelleFiyat(<?= $index ?>)" id="bilet_turu<?= $index ?>">
                        <option value="Standart">Standart</option>
                        <option value="VIP">VIP</option>
                        <option value="Ogrenci">Öğrenci</option>
                    </select>
                </div>
            <?php $index++;
            endforeach; ?>
        <?php else: ?>
            <p>Sepetinizde hiç etkinlik yok.</p>
        <?php endif; ?>
        <hr>
        <div class="odeme">
            <div class="checkbox">
                <input type="checkbox" id="c1" value="kredi">
                <label for="c1">Kredi Kartı</label>
            </div>
            <br>
            <div class="checkbox">
                <input type="checkbox" id="c2" value="kapida">
                <label for="c2">Kapıda Ödeme</label>
            </div>
            <br>
            <div class="checkbox">
                <input type="checkbox" id="c3" value="qr">
                <label for="c3">QR ile Ödeme</label>
            </div>
            <br>
            <div class="checkbox">
                <input type="checkbox" id="c4" value="mobil">
                <label for="c4">Mobil Uygulama ile Ödeme</label>
            </div>
        </div>

        <a href="anaEkran.php" class="geri-btn">← Geri Dön</a>

        <button type="submit" class="btn" id="satinAlBtn">Satın Al</button>
    </div>
    <script>
        document.getElementById("satinAlBtn").addEventListener("click", function() {
            const odemeYontemi = document.querySelector('input[type="checkbox"]:checked');
            if (!odemeYontemi) {
                alert("Lütfen bir ödeme yöntemi seçin.");
                return;
            }

            const secimler = [];
            const etkinlikler = document.querySelectorAll(".etkinlik");

            etkinlikler.forEach((etkinlik, index) => {
                const fiyatSpan = document.getElementById("fiyat" + index);
                const biletTuru = document.getElementById("bilet_turu" + index).value;
                const fiyat = parseFloat(fiyatSpan.innerText.replace("₺", "").trim());
                const et_id = <?= json_encode(array_column($etkinlikler, 'id')) ?>[index]; // PHP'den alıyoruz

                secimler.push({
                    etkinlik_id: et_id,
                    bilet_turu: biletTuru,
                    fiyat: fiyat
                });
            });

            fetch("satin_al.php", {
                    method: "POST",
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        odeme: odemeYontemi.value,
                        secimler: secimler
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert("Satın alma başarılı!");
                        location.reload();
                    } else {
                        alert(data.error || "Bir hata oluştu.");
                    }
                });
        });
    </script>


    <script class="fiyat guncelleme">
        function guncelleFiyat(index) {
            const select = document.getElementById("bilet_turu" + index);
            const secim = select.value;
            const fiyatSpan = document.getElementById("fiyat" + index);
            const orjinalFiyat = parseFloat(fiyatSpan.getAttribute("data-orj"));
            let yeniFiyat = orjinalFiyat;

            if (secim === 'VIP') {
                yeniFiyat = orjinalFiyat * 2;
            } else if (secim === 'Ogrenci') {
                yeniFiyat = orjinalFiyat * 0.7;
            }


            document.getElementById('fiyat' + index).innerText = yeniFiyat.toFixed(2) + "₺";
        }
    </script>
</body>

</html>