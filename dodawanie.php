<?php
session_start();
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
$error = false;

// Clear message on page load (not POST)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    unset($_SESSION['message']);
} else {
    $message = isset($_SESSION['message']) ? $_SESSION['message'] : "";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Prevent form resubmission on refresh
    if (!isset($_SESSION['form_submitted'])) {
        $_SESSION['form_submitted'] = true;
    $id_uzytkownika = trim($_POST['id_uzytkownika']);
    $id_kategorii = trim($_POST['id_kategorii']);
    $tytul = trim($_POST['tytul']);
    $opis = trim($_POST['opis']);
    $data = trim($_POST['data']);
    $godzina_rozpoczecia = trim($_POST['godzina_rozpoczecia']);
    $godzina_zakonczenia = trim($_POST['godzina_zakonczenia']);

    // Convert date from DD-MM-YYYY to YYYY-MM-DD
    $parts = explode('-', $data);
    if (count($parts) !== 3 || !is_numeric($parts[0]) || !is_numeric($parts[1]) || !is_numeric($parts[2])) {
        $message = "Nieprawidłowy format daty! Użyj DD-MM-RRRR.";
        $_SESSION['message'] = $message;
        $error = true;
    } else {
        $data_sql = sprintf('%04d-%02d-%02d', $parts[2], $parts[1], $parts[0]);
    }

    if (!$error) {
        // Validate time format
        if (!preg_match('/^\d{2}:\d{2}$/', $godzina_rozpoczecia) || !preg_match('/^\d{2}:\d{2}$/', $godzina_zakonczenia)) {
            $message = "Nieprawidłowy format godziny! Użyj GG:MM.";
            $_SESSION['message'] = $message;
            $error = true;
        } elseif ($godzina_rozpoczecia >= $godzina_zakonczenia) {
            $message = "Godzina rozpoczęcia musi być wcześniejsza niż godzina zakończenia!";
            $_SESSION['message'] = $message;
            $error = true;
        }
    }

    if (!$error) {
        // Check for time conflicts
        $query = "SELECT COUNT(*) as count FROM zadania WHERE id_uzytkownika = :id_uzytkownika AND data = :data AND godzina_rozpoczecia < :new_end AND godzina_zakonczenia > :new_start";
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            ':id_uzytkownika' => $id_uzytkownika,
            ':data' => $data_sql,
            ':new_end' => $godzina_zakonczenia,
            ':new_start' => $godzina_rozpoczecia
        ]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result['count'] > 0) {
            $message = "Nie można dodać zadania w zajętych godzinach pracownika!";
            $_SESSION['message'] = $message;
            $error = true;
        }
    }

    if (!$error) {
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

        // Get user email
        $user_query = "SELECT email, imie, nazwisko FROM uzytkownicy WHERE id_uzytkownika = :id_uzytkownika";
        $user_stmt = $pdo->prepare($user_query);
        $user_stmt->execute([':id_uzytkownika' => $id_uzytkownika]);
        $user = $user_stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $to = $user['email'];
            $subject = "Nowe zadanie zostało dodane";
            $body = "Witaj " . $user['imie'] . " " . $user['nazwisko'] . ",\n\nZostało Ci przypisane nowe zadanie:\n\nTytuł: " . $tytul . "\nOpis: " . $opis . "\nData: " . $data . "\nGodziny: " . $godzina_rozpoczecia . " - " . $godzina_zakonczenia . "\n\nPozdrawiam,\nSystem Harmonogramu";

            // Send email using Resend API
            $api_key = 're_99gBGvN3_JC39VHP6gjdx4kKP1wKKpP9n';
            $url = 'https://api.resend.com/emails';

            $data = [
                'from' => 'onboarding@resend.dev',  // Use Resend's default domain for testing
                'to' => [$to],
                'subject' => $subject,
                'text' => $body
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $api_key,
                'Content-Type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            // Debug: write to a file
            file_put_contents('email_debug.log', "HTTP Code: $http_code\nResponse: $response\n", FILE_APPEND);

            if ($http_code === 200) {
                // Email sent successfully
            } else {
                error_log("Failed to send email via Resend. HTTP Code: $http_code, Response: " . $response);
                // For debugging, add to message
                $message .= " (Błąd wysyłania e-maila - sprawdź email_debug.log)";
                $_SESSION['message'] = $message;
            }
        }

        $message = "Zadanie dodane pomyślnie!";
        $_SESSION['message'] = $message;
    }
    } else {
        // Form was already submitted, redirect to prevent resubmission
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
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
            <?php
            // Clear the session flag after displaying the page
            if (isset($_SESSION['form_submitted'])) {
                unset($_SESSION['form_submitted']);
            }
            ?>
            <div id="formu">
                <?php if($message): ?>
                    <p style="text-align:center; color:red; margin-bottom: 10px; font-weight: bold;"><?= $message ?></p>
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
