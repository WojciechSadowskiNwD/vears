<?php
session_start();

$_SESSION['clearCart'] = 1;
unset($_COOKIE["koszyk"]);
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vears - dokonano zakupu</title>
    <link rel="stylesheet" href="./css/logowanie.css">
    <script src="https://kit.fontawesome.com/0f35c72659.js" crossorigin="anonymous"></script>
</head>
<body> 

    <div class="container cointainer-width">
        <p><a class="logo" href="./index.php"><i class="fa-solid fa-shirt"></i><span>VEARS</span></a></p>
        <br>
        <div class="welcome-info">
            <p>Dziękujemy za dokonanie zamówienia!</p>
            <p>Wysłaliśmy na Twoją skrzynkę mailową link niezbędny do opłacenia koszyka!</p>
        </div>
        <a href="./logging.php">Przejdź na profil konta</a>
        <br><br>
        <a href="./index.php"><p>* Strona główna *</p></a>
    </div>

</body>
</html>