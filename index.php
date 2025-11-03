<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="sheet.css">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <title>Document</title>
</head>
    
<body>
    <header>
        <h1>Tygodniowy Harmonogram Pracy</h1>
    </header>

    <main>
        <section>

                <input type="text" id="data" placeholder="01-01-2000">
                <button id="button1">Wyszukaj</button>
                <button id="button2">Zresetuj</button>
                <table>
                    <tr>
                        <td>
                            Imie
                        </td>
                        <td>
                            Nazwisko
                        </td>
                        <td>
                            E-Mail
                        </td>
                        <td>
                            Zadanie
                        </td>
                        <td>
                            Czas rozpoczecia
                        </td>
                        <td>
                            Czas zakonczenia
                        </td>
                        <td>
                            Data
                        </td>
                    </tr>
                     <?php
               
                $servername = "localhost";
                $username = "root";        
                $password = "";            
                $dbname = "tygharmonogram";    

                $conn = new mysqli($servername, $username, $password, $dbname);

                
                $sql = "
                    SELECT 
                        u.imie,
                        u.nazwisko,
                        u.email,
                        k.nazwa AS kategoria,
                        z.godzina_rozpoczecia,
                        z.godzina_zakonczenia,
                        z.data
                    FROM zadania z
                    JOIN uzytkownicy u ON z.id_uzytkownika = u.id_uzytkownika
                    JOIN kategorie k ON z.id_kategorii = k.id_kategorii
                    ORDER BY z.data ASC, z.godzina_rozpoczecia ASC
                ";

                $result = $conn->query($sql);

               
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>" . $row['imie'] . "</td>
                                <td>" . $row['nazwisko'] . "</td>
                                <td>" . $row['email'] . "</td>
                                <td>" . $row['kategoria'] . "</td>
                                <td>" . $row['godzina_rozpoczecia'] . "</td>
                                <td>" . $row['godzina_zakonczenia'] . "</td>
                                <td>" . date('d-m-Y', strtotime($row['data'])) . "</td>


                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='7'>Brak danych w harmonogramie.</td></tr>";
                }

                $conn->close();
                ?>
                </table>

        </section>
    </main>


    <footer>

        <h2>Harmonogram firmy: x</h2>
    </footer>
    <script src="script.js"> </script>
</body>
</html>