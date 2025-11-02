<?php
session_start();

if( (isset($_SESSION['zalogowany'])) && ($_SESSION['zalogowany'] == true)){
    header('Location: zalogowany.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vears - logowanie</title>
    <link rel="stylesheet" href="./css/logowanie.css">
    <script src="https://kit.fontawesome.com/0f35c72659.js" crossorigin="anonymous"></script>
</head>
<body>
    <div class="container cointainer-width">
        <form action="./zaloguj.php" method="post">
            <p><a class="logo" href="./index.php"><i class="fa-solid fa-shirt"></i><span>VEARS</span></a></p>
            <br>
            <input type="text" name="login" placeholder="login">
            <input type="password" name="haslo" placeholder="hasło">
            <a href="#"><p>przypomnij hasło</p></a>  
            <input type="submit" value="zaloguj się">
            <br><br>
            <a href="registration.php"><p>* LUB zarejestruj konto! *</p></a>
        </form>    
    </div>

<?php if(isset($_SESSION['blad']))  echo $_SESSION['blad']; ?>

</body>
</html>