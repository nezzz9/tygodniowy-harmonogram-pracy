<?php
$host = "localhost";
$dbname = "tygharmonogram";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Błąd połączenia z bazą danych: " . $e->getMessage());
}

// Pobranie listy kategorii
$categories = $pdo->query("SELECT id_kategorii, nazwa FROM kategorie ORDER BY nazwa ASC")->fetchAll(PDO::FETCH_ASSOC);

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_uzytkownika = trim($_POST['id_uzytkownika']);
    $id_kategorii = trim($_POST['id_kategorii']);
    $tytul = trim($_POST['tytul']);
    $opis = trim($_POST['opis']);
    $data = trim($_POST['data']);
    $godzina_rozpoczecia = trim($_POST['godzina_rozpoczecia']);
    $godzina_zakonczenia = trim($_POST['godzina_zakonczenia']);

    // Konwersja daty na format YYYY-MM-DD
    $data_array = explode("-", $data);
    if(count($data_array) === 3){
        $data_sql = $data_array[2] . "-" . $data_array[1] . "-" . $data_array[0];
    } else {
        $message = "Niepoprawny format daty.";
    }

    if (!$message) {
        $sql = "INSERT INTO zadania (id_uzytkownika, id_kategorii, tytul, opis, data, godzina_rozpoczecia, godzina_zakonczenia) 
                VALUES (:id_uzytkownika, :id_kategorii, :tytul, :opis, :data, :godzina_rozpoczecia, :godzina_zakonczenia)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id_uzytkownika' => $id_uzytkownika,
            ':id_kategorii' => $id_kategorii,
            ':tytul' => $tytul,
            ':opis' => $opis,
            ':data' => $data_sql,
            ':godzina_rozpoczecia' => $godzina_rozpoczecia,
            ':godzina_zakonczenia' => $godzina_zakonczenia
        ]);
        $message = "Zadanie dodane pomyślnie!";
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="sheet.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    <title>Dodawanie zadań</title>
</head>
<body>
    <header>
        <a href="index.php"><div class="polowa"><h1>Tygodniowy Harmonogram Pracy</h1></div></a>
        <a href="dodawanie.php"><div class="polowa" id="active"><h1>Dodawanie Zadań</h1></div></a>
    </header>

    <main>
        <div class="box">
            <div id="miniheader">
                <h2>Dodaj nowe zadanie</h2>
            </div>
            <div id="formu">
                <?php if($message): ?>
                    <p style="text-align:center; color:white; margin-bottom: 10px;"><?= $message ?></p>
                <?php endif; ?>
                <form action="" method="post">
                    <input type="text" class="button" name="id_uzytkownika" placeholder="ID pracownika" required>
                    <select name="id_kategorii" class="btnselect" required>
                        <option value="" disabled selected>Wybierz kategorię</option>
                        <?php foreach($categories as $cat): ?>
                            <option value="<?= $cat['id_kategorii'] ?>"><?= htmlspecialchars($cat['nazwa']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" class="button" name="tytul" placeholder="Nazwa zadania" required>
                    <input type="text" class="button" name="opis" placeholder="Opis zadania" required>
                    <input type="text" class="button" name="data" placeholder="Data (DD-MM-RRRR)" required>
                    <input type="text" class="button" name="godzina_rozpoczecia" placeholder="Rozpoczęcie (GG:MM)" required>
                    <input type="text" class="button" name="godzina_zakonczenia" placeholder="Zakończenie (GG:MM)" required>
                    <input type="submit" class="btnsubmit" value="Dodaj zadanie">
                </form>
            </div>
        </div>
    </main>
        <footer>

        <h2>Harmonogram firmy: x</h2>
    </footer>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="script.js"></script>
</body>
</html>
