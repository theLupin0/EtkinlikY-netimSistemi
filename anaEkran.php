<?php
session_start();
$profil_durum = 0;
$duyurular = [];

$conn = new mysqli('localhost', 'root', '746348', 'etkinlik_db');
if ($conn->connect_error) {
    die('Bağlantı Hatası: ' . $conn->connect_error);
}

if (!$conn->connect_error) {
    $sqlDuyuru = $conn->query("SELECT baslik , yayin_tarihi,icerik FROM duyurular ORDER BY yayin_tarihi DESC");
    while ($d = $sqlDuyuru->fetch_assoc()) {
        $duyurular[] = $d;
    }
}

if (isset($_SESSION['user_id'])) {

    $user_id = $_SESSION['user_id'];
    $role = $_SESSION['role'];
    $username = $_SESSION['username'];
    $sql = "Select is_active From users Where id='$user_id'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if ($user['is_active'] == 1) {
            $profil_durum = 1;
        } else {
            $profil_durum = 0;
            session_unset();
            session_destroy();
        }
    }
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ana Sayfa</title>
    <link rel="stylesheet" href="css.file/styleAnaEkran.css">
</head>

<body>
    <div class="menu-bar">

        <div class="menu-bar-left">Etkinlik Sistemi</div>

        <ul>
            <li><a href="sepet.php?user_id=<?= isset($user_id) ? $_SESSION['user_id'] : null ?>" class="link-sepet">Sepet</a></li>
            <div>
                <?php if ($profil_durum == 1) : ?>
                    <label style="color: azure;"><?php echo $username; ?></label>
                    <?php if ($_SESSION['role'] == 'admin') : ?>
                        <div id="panel">
                            <a href="adminPaneli.php">Admin Paneli</a>
                        </div>
                    <?php endif; ?>
                    <p style="color: black;">&</p>
                    <a href="logout.php" class="link-reg">Çıkış Yapın</a>
                <?php else : ?>
                    <a href="login.php" class="link-log">Oturum Aç</a>
                    <p style="color: black;">&</p>
                    <a href="register.php" class="link-reg">Kayıt Ol</a>
                <?php endif; ?>
            </div>
        </ul>
    </div>
    <div class="ekran">

        <div class="duyuru-kutusu">
            <div>
                <h2>Duyurular</h2>
                <?php if (!empty($duyurular)) : ?>
                    <ul style="list-style: none; padding: 0;">
                        <?php foreach ($duyurular as $duyuru): ?>
                            <li style="margin-bottom: 10px;">
                                <strong><?= htmlspecialchars($duyuru['baslik']) ?></strong><br>
                                <p><?= htmlspecialchars($duyuru['yayin_tarihi']) ?></p><br><br>
                                <span><?= htmlspecialchars($duyuru['icerik']) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else : ?>
                    <p>Henüz duyuru yok.</p>
                <?php endif; ?>
            </div>

        </div>

        <div class="Ilgi-alani">
            <h2>İlgi Alanların Ne?</h2>
            <div>
                <button onclick="fetchEtkinlikk('muzik')">🎵 Müzik</button>
                <button onclick="fetchEtkinlikk('spor')">🏃‍♂️ Spor</button>
                <button onclick="fetchEtkinlikk('sanat')">🎨 Sanat</button>
                <button onclick="fetchEtkinlikk('teknoloji')">💻 Teknoloji</button>
                <button onclick="fetchEtkinlikk('doga')">🌲 Doğa</button>
                <button onclick="fetchEtkinlikk('rekabet')">🏆 Rekabet</button>
                <button onclick="fetchEtkinlikk('kitap')">📚 Kitap</button>
                <button onclick="fetchEtkinlikk('oyun')">🎮 Oyun</button>
                <button onclick="fetchEtkinlikk('yemek')">🍳 Yemek</button>
                <button onclick="fetchEtkinlikk('seyehat')">✈️ Seyahat</button>
                <button onclick="fetchEtkinlikk('film')">🎬 Film</button>
                <button onclick="fetchEtkinlikk('fotograf')">📸 Fotoğraf</button>
                <button onclick="fetchEtkinlikk('grafik')">🎨 Grafik</button>
                <button onclick="fetchEtkinlikk('tarih')">🏛️ Tarih</button>
                <button onclick="fetchEtkinlikk('uzay')">🪐 Uzay</button>
                <button onclick="fetchEtkinlikk('bilim')">🧪 Bilim</button>
                <button onclick="fetchEtkinlikk('otomobil')">🚗 Otomobil</button>
                <button onclick="fetchEtkinlikk('macera')">🧗‍♂️ Macera</button>
            </div>
        </div>
        <script>
            function fetchEtkinlikk(ilgi = "") {
                const liste = document.getElementById("liste");

                let url = "API/data.php";
                if (ilgi !== "") {
                    url += "?ilgi=" + encodeURIComponent(ilgi);
                }

                liste.innerHTML = "";

                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        if (data.length === 0) {
                            const bos = document.createElement("p");
                            bos.textContent = "Bu türde etkinlik bulunamadı.";
                            bos.style.color = "white";
                            bos.style.fontStyle = "italic";
                            bos.style.margin = "15px";
                            liste.appendChild(bos);
                            return;
                        }

                        data.forEach(etk => {
                            const container = document.createElement("div");
                            container.style.border = "1px solid #ccc";
                            container.style.padding = "10px";
                            container.style.marginBottom = "10px";
                            container.style.borderRadius = "5px";
                            container.style.wordWrap = "break-word";
                            container.style.width = "50%";

                            if (window.isAdmin) {
                                const id = document.createElement("h3");
                                id.textContent = etk.id;
                                container.appendChild(id);
                            }

                            const baslik = document.createElement("h3");
                            baslik.textContent = etk.baslik;
                            container.appendChild(baslik);

                            const tarih = document.createElement("p");
                            tarih.innerHTML = `<strong>Tarih:</strong> ${etk.et_tarih}`;
                            container.appendChild(tarih);

                            const aciklama = document.createElement("p");
                            aciklama.innerHTML = `<strong>Açıklama:</strong> ${etk.aciklama}`;
                            container.appendChild(aciklama);

                            const yer = document.createElement("p");
                            yer.innerHTML = `<strong>Yer:</strong> ${etk.yer}`;
                            container.appendChild(yer);

                            const kapasite = document.createElement("p");
                            kapasite.innerHTML = `<strong>Kapasite:</strong> ${etk.kapasite}`;
                            container.appendChild(kapasite);

                            const ucret = document.createElement("p");
                            ucret.innerHTML = `<strong>Ücret:</strong> ${etk.ucret}`;
                            container.appendChild(ucret);

                            const havaDurumuDiv = document.createElement("div");
                            havaDurumuDiv.className = "hava";
                            havaDurumuDiv.style.border = "1px solid #ccc";
                            havaDurumuDiv.textContent = "Hava durumu yükleniyor...";
                            havaDurumuDiv.style.marginTop = "10px";
                            havaDurumuDiv.style.fontStyle = "italic";

                            const biletAlBtn = document.createElement("button");
                            biletAlBtn.textContent = "🎟️ Bilet Al";
                            biletAlBtn.style.marginTop = "10px";
                            biletAlBtn.style.padding = "8px 16px";
                            biletAlBtn.style.border = "none";
                            biletAlBtn.style.backgroundColor = "#4CAF50";
                            biletAlBtn.style.color = "white";
                            biletAlBtn.style.borderRadius = "5px";
                            biletAlBtn.style.cursor = "pointer";

                            const altKutu = document.createElement("div");
                            altKutu.style.display = "flex";
                            altKutu.style.gap = "10px";
                            altKutu.style.alignItems = "center";
                            altKutu.style.marginTop = "10px";

                            altKutu.appendChild(container);
                            altKutu.appendChild(havaDurumuDiv);
                            altKutu.appendChild(biletAlBtn);


                            fetch(`API/get_weather.php?et_id=${etk.id}`)
                                .then(response => response.json())
                                .then(weather => {
                                    if (weather.error) {
                                        havaDurumuDiv.textContent = `Hava bilgisi alınamadı: ${weather.error}`;
                                    } else {
                                        havaDurumuDiv.innerHTML = `
                                            <strong>Hava:</strong> ${weather.hava}<br>
                                            <strong>Sıcaklık:</strong> ${weather.sicaklik}°C<br>
                                            <strong>Planlanabilirlik:</strong> ${weather.planlanabilir ? "Uygun" : "Uygun değil"}
                                            `;
                                        havaDurumuDiv.style.fontStyle = "normal";
                                    }
                                })
                                .catch(error => {
                                    havaDurumuDiv.textContent = "Hava durumu alınırken hata oluştu.";
                                });


                            liste.appendChild(altKutu);


                            // Butonun tıklanma olayı
                            biletAlBtn.onclick = function() {
                                // Eğer kullanıcı giriş yapmadıysa login sayfasına yönlendir
                                <?php if (!$profil_durum): ?>
                                    window.location.href = "login.php";
                                <?php else: ?>
                                    /*const currentUserID = <?= isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null ?>;
                                    fetch("sepet.php", {
                                            method: "POST",
                                            headers: {
                                                "Content-Type": "application/json"
                                            },
                                            body: JSON.stringify({
                                                kullanici_id: currentUserID,
                                                etkinlik_id: etk.id
                                            })
                                        })
                                        .then(response => response.json())
                                        .then(data => {
                                            alert(data.message || "Sepete Eklendi");
                                        })
                                        .catch(error => {
                                            alert("Bir hata oluştu.");
                                            console.error(error);
                                        });*/

                                    const userId = <?php echo $_SESSION['user_id']; ?>;
                                    const etkinlikID = etk.id;
                                    window.location.href = `sepet.php?user_id=${userId}&etkinlik_id=${etkinlikID}`;
                                <?php endif; ?>
                            };



                        });
                    })
                    .catch(error => {
                        console.error("Hata: ", error);
                        const hata = document.createElement("p");
                        hata.textContent = "Etkinlikler alınırken bir hata oluştu.";
                        hata.style.color = "red";
                        liste.appendChild(hata);
                    });
            }
        </script>

        <div class="etkinlik">
            <div>
                <h2>Etkinlikler
                    <select name="et_tur" id="et_tur" onchange="fetchEtkinlik()">
                        <option value="">Hepsi</option>
                        <option value="Müzik">Müzik</option>
                        <option value="Teknoloji">Teknoloji</option>
                        <option value="Sanat">Sanat</option>
                        <option value="Spor">Spor</option>
                    </select>
                </h2>
                <ul id="liste"></ul>


                <script>
                    let aktifIlgi = ""; // İlgi alanı seçilmişse bu değişkende tutulur

                    function fetchEtkinlik(tur = "") {
                        const liste = document.getElementById("liste");

                        // İlgi alanı butonuyla gelmişse tur'u ata
                        if (tur) {
                            aktifIlgi = tur;
                            document.getElementById("et_tur").value = ""; // select'i sıfırla
                        } else {
                            tur = document.getElementById("et_tur").value;
                            aktifIlgi = ""; // select ile filtreleme yapılırsa ilgi alanı sıfırlanır
                        }

                        const url = `/API/data.php${tur ? `?tur=${tur}` : ''}`;
                        liste.innerHTML = ""; // listeyi temizle

                        fetch(url)
                            .then(response => response.json())
                            .then(data => {
                                if (data.length === 0) {
                                    const bos = document.createElement("p");
                                    bos.textContent = "Bu türde etkinlik bulunamadı.";
                                    bos.style.color = "white";
                                    bos.style.fontStyle = "italic";
                                    bos.style.margin = "15px";
                                    liste.appendChild(bos);
                                    return;
                                }

                                data.forEach(etk => {
                                    const container = document.createElement("div");
                                    container.style.border = "1px solid #ccc";
                                    container.style.padding = "10px";
                                    container.style.marginBottom = "10px";
                                    container.style.borderRadius = "5px";
                                    container.style.wordWrap = "break-word";
                                    container.style.width = "50%";

                                    if (window.isAdmin) {
                                        const id = document.createElement("h3");
                                        id.textContent = etk.id;
                                        container.appendChild(id);
                                    }

                                    const baslik = document.createElement("h3");
                                    baslik.textContent = etk.baslik;
                                    container.appendChild(baslik);

                                    const tarih = document.createElement("p");
                                    tarih.innerHTML = `<strong>Tarih:</strong> ${etk.et_tarih}`;
                                    container.appendChild(tarih);

                                    const aciklama = document.createElement("p");
                                    aciklama.innerHTML = `<strong>Açıklama:</strong> ${etk.aciklama}`;
                                    container.appendChild(aciklama);

                                    const yer = document.createElement("p");
                                    yer.innerHTML = `<strong>Yer:</strong> ${etk.yer}`;
                                    container.appendChild(yer);

                                    const kapasite = document.createElement("p");
                                    kapasite.innerHTML = `<strong>Kapasite:</strong> ${etk.kapasite}`;
                                    container.appendChild(kapasite);

                                    const ucret = document.createElement("p");
                                    ucret.innerHTML = `<strong>Ücret:</strong> ${etk.ucret}`;
                                    container.appendChild(ucret);

                                    const havaDurumuDiv = document.createElement("div");
                                    havaDurumuDiv.className = "hava";
                                    havaDurumuDiv.style.border = "1px solid #ccc";
                                    havaDurumuDiv.textContent = "Hava durumu yükleniyor...";
                                    havaDurumuDiv.style.marginTop = "10px";
                                    havaDurumuDiv.style.fontStyle = "italic";

                                    const biletAlBtn = document.createElement("button");
                                    biletAlBtn.textContent = "🎟️ Bilet Al";
                                    biletAlBtn.style.marginTop = "10px";
                                    biletAlBtn.style.padding = "8px 16px";
                                    biletAlBtn.style.border = "none";
                                    biletAlBtn.style.backgroundColor = "#4CAF50";
                                    biletAlBtn.style.color = "white";
                                    biletAlBtn.style.borderRadius = "5px";
                                    biletAlBtn.style.cursor = "pointer";

                                    const altKutu = document.createElement("div");
                                    altKutu.style.display = "flex";
                                    altKutu.style.gap = "10px";
                                    altKutu.style.alignItems = "center";
                                    altKutu.style.marginTop = "10px";

                                    altKutu.appendChild(container);
                                    altKutu.appendChild(havaDurumuDiv);
                                    altKutu.appendChild(biletAlBtn);


                                    fetch(`API/get_weather.php?et_id=${etk.id}`)
                                        .then(response => response.json())
                                        .then(weather => {
                                            if (weather.error) {
                                                havaDurumuDiv.textContent = `Hava bilgisi alınamadı: ${weather.error}`;
                                            } else {
                                                havaDurumuDiv.innerHTML = `
                                            <strong>Hava:</strong> ${weather.hava}<br>
                                            <strong>Sıcaklık:</strong> ${weather.sicaklik}°C<br>
                                            <strong>Planlanabilirlik:</strong> ${weather.planlanabilir ? "Uygun" : "Uygun değil"}
                                            `;
                                                havaDurumuDiv.style.fontStyle = "normal";
                                            }
                                        })
                                        .catch(error => {
                                            havaDurumuDiv.textContent = "Hava durumu alınırken hata oluştu.";
                                        });


                                    liste.appendChild(altKutu);


                                    // Butonun tıklanma olayı
                                    biletAlBtn.onclick = function() {
                                        // Eğer kullanıcı giriş yapmadıysa login sayfasına yönlendir
                                        <?php if (!$profil_durum): ?>
                                            window.location.href = "login.php";
                                        <?php else: ?>
                                            /*const currentUserID = <?= isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null ?>;
                                            fetch("sepet.php", {
                                                    method: "POST",
                                                    headers: {
                                                        "Content-Type": "application/json"
                                                    },
                                                    body: JSON.stringify({
                                                        kullanici_id: currentUserID,
                                                        etkinlik_id: etk.id
                                                    })
                                                })
                                                .then(response => response.json())
                                                .then(data => {
                                                    alert(data.message || "Sepete Eklendi");
                                                })
                                                .catch(error => {
                                                    alert("Bir hata oluştu.");
                                                    console.error(error);
                                                });*/

                                            const userId = <?php echo $_SESSION['user_id']; ?>;
                                            const etkinlikID = etk.id;
                                            window.location.href = `sepet.php?user_id=${userId}&etkinlik_id=${etkinlikID}`;
                                        <?php endif; ?>
                                    };



                                });
                            })
                            .catch(error => {
                                console.error("Hata: ", error);
                                const hata = document.createElement("p");
                                hata.textContent = "Etkinlikler alınırken bir hata oluştu.";
                                hata.style.color = "red";
                                liste.appendChild(hata);
                            });
                    }

                    // Admin olup olmadığını kontrol etmek için
                    window.isAdmin = <?= isset($_SESSION['role']) && $_SESSION['role'] == 'admin' ? 'true' : 'false' ?>;

                    window.onload = () => fetchEtkinlik(""); // Sayfa yüklendiğinde tüm etkinlikleri getir
                </script>
            </div>

        </div>

    </div>
    </div>
</body>

</html>

<?php $conn->close(); ?>