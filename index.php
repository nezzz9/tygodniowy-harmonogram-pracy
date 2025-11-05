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
        <a href="index.php"><div class="polowa" id="active"><h1>Tygodniowy Harmonogram Pracy</h1></div></a>
        <a href="dodawanie.php"><div class="polowa" ><h1>Dodawanie Zadan</h1></div></a>
        
    </header>

    <main>
        <section>
            <div id=inpcontainer>
                <div class="check" style="position:absolute; margin-left:80%">
                Czy zapisywac date:
                <input type="checkbox" id="autoRefresh" style="width:0.9em; height:0.9em;">
                <br>
                Czy wyswietlac opis:
                <input type="checkbox" id="showDescription" style="width:0.9em; height:0.9em;">
                <br>
                Zmien szerokosc pól w tabeli (px):
                <input type="text" id="TRwidth" value="130" style="width:4em; height:1.2em;">
                <button id="resetWidthBtn" style="    margin-left: 5px;
                    padding: 2px 5px;
                    font-size: 0.8em;
                    background-color: #f1f1ff;
                    border: none;
                    border-radius: 5px;
                    cursor: pointer;">
                    Reset
                </button>
            </div>
            </div>


<input type="text" id="data" placeholder="01-01-2000">
<div id="container">
    <button id="button1">Wyszukaj</button>
    <button id="button3">Wyszukaj i Pobierz PDF</button>
    <button id="button2">Zresetuj</button>
</div>



<table>
                    <tr>
                        <td>
                            <div style="margin-top: 10px;">
                            <input type="checkbox" id="colCheckbox0" >
                            <label for="colCheckbox0"></label>
                        </div>
                            ID Uzytkownika
                        <td>
                            <div style="margin-top: 10px;">
                            <input type="checkbox" id="colCheckbox1" >
                            <label for="colCheckbox1"></label>
                        </div>
                            Imie
                        </td>
                        <td>
                            <div style="margin-top: 10px;">
                            <input type="checkbox" id="colCheckbox2" >
                            <label for="colCheckbox2"></label>
                        </div>
                            Nazwisko
                        </td>
                        <td>
                            <div style="margin-top: 10px;">
                            <input type="checkbox" id="colCheckbox3" >
                            <label for="colCheckbox3"></label>
                        </div>
                            E-Mail
                        </td>
                        <td>
                            <div style="margin-top: 10px;">
                            <input type="checkbox" id="colCheckbox4" >
                            <label for="colCheckbox4"></label>
                        </div>
                            Zadanie
                        </td>
                        <td>
                            <div style="margin-top: 10px;">
                            <input type="checkbox" id="colCheckbox5" >
                            <label for="colCheckbox5"></label>
                        </div>
                            Czas rozpoczecia
                        </td>
                        <td>
                            <div style="margin-top: 10px;">
                            <input type="checkbox" id="colCheckbox6" >
                            <label for="colCheckbox6"></label>
                        </div>
                            Czas zakonczenia
                        </td>
                        <td>
                            <div style="margin-top: 10px;">
                            <input type="checkbox" id="colCheckbox7" >
                            <label for="colCheckbox7"></label>
                        </div>
                            Data
                        </td>
                        <td>
                            <div style="margin-top: 10px;">
                            <input type="checkbox" id="colCheckbox8" >
                            <label for="colCheckbox8"></label>
                        </div>
                        PDF
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
                        u.id_uzytkownika,
                        u.imie,
                        u.nazwisko,
                        u.email,
                        z.tytul AS kategoria,
                        z.godzina_rozpoczecia,
                        z.godzina_zakonczenia,
                        z.data,
                        z.opis
                    FROM zadania z
                    JOIN uzytkownicy u ON z.id_uzytkownika = u.id_uzytkownika
                    JOIN kategorie k ON z.id_kategorii = k.id_kategorii
                    ORDER BY z.data ASC, z.godzina_rozpoczecia ASC
                ";

                $result = $conn->query($sql);

               
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>" . $row['id_uzytkownika'] . "</td>
                                <td>" . $row['imie'] . "</td>
                                <td>" . $row['nazwisko'] . "</td>
                                <td>" . $row['email'] . "</td>
                                <td>" . $row['kategoria'] . "<br>Opis:<br>" . $row['opis'] ."</td>
                                <td>" . $row['godzina_rozpoczecia'] . "</td>
                                <td>" . $row['godzina_zakonczenia'] . "</td>
                                <td>" . date('d-m-Y', strtotime($row['data'])) . "</td>
                                <td><button class='pdf-btn'>PDF</button></td>


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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="script.js"></script>

</body>
</html>