<?php
session_start();

if(!isset($_POST['login']) || (!isset($_POST['haslo']))){
    header('Location: logging.php');
    exit();
}

require_once "connect.php";
$polaczenie = @new mysqli($host, $db_user, $db_password, $db_name);
mysqli_query($polaczenie, "SET CHARSET utf8");
mysqli_query($polaczenie, "SET NAMES 'utf8' COLLATE 'utf8_polish_ci'");

if( $polaczenie->connect_errno!=0 ){
    echo "Error: ".$polaczenie->connect_errno;
}
else{
    // Pobranie z formularza logowania login i haslo ktore wpisal uzytkownik:
    $login = $_POST['login'];
    $pass = $_POST['haslo'];

    // Zabezpieczenie przed atakiem tzw.: wstrzykiwaniem SQL-owych zapytan:
    $login = htmlentities($login, ENT_QUOTES, "UTF-8");
    
    if ($rezultat = $polaczenie->query(sprintf( "SELECT * FROM users WHERE user_login='%s'", 
    mysqli_real_escape_string($polaczenie, $login)))){
        $ilu_userow = $rezultat->num_rows;
        
        //Jezeli znaleziono taki szukany wiersz:
        if($ilu_userow > 0){
            $wiersz = $rezultat->fetch_assoc();

            if(password_verify($pass, $wiersz['user_pass'])){

                // Ustawienie flagi w zmiennej globalnej, mowiacej ze jest zalogowany uzytkownik:
                $_SESSION['zalogowany'] = true;
                $_SESSION['id'] = $wiersz['user_id'];
                $_SESSION['user'] = $wiersz['user_login'];
                $_SESSION['name'] = $wiersz['user_name'];
                $_SESSION['surname'] = $wiersz['user_surname'];
                $_SESSION['phone'] = $wiersz['user_phone'];
                $_SESSION['email'] = $wiersz['user_email'];
                $_SESSION['post_code'] = $wiersz['user_post_code'];
                $_SESSION['city'] = $wiersz['user_city'];
                $_SESSION['street'] = $wiersz['user_street'];
                $_SESSION['flat_num'] = $wiersz['user_flat'];

                // Asekuracyjnie usunieta zostanie zmienna bledu:
                unset($_SESSION['blad']);

                // Zwolnienie zawartosci $rezultat z pamieci:
                $rezultat->free_result();

                //Wymuszenie przekierowania przegladarki do: zalogowany.php
                header('Location: zalogowany.php');
            }
            else {
                $_SESSION['blad'] = '<strong><span style="position:absolute; margin-top:30px;  margin-left: 50%;  transform:translateX(-50%); letter-spacing:.5px; word-spacing: 4px; color:tomato;">Nieprawidłowy login lub hasło!</span></strong>';
                header('Location: logging.php');
            }
        }else {
            $_SESSION['blad'] = '<strong><span style="position:absolute; margin-top:30px;  margin-left: 50%;  transform:translateX(-50%); letter-spacing:.5px; word-spacing: 4px; color:tomato;">Nieprawidłowy login lub hasło!</span></strong>';
            header('Location: logging.php');
        }
    }
    else{
        echo "Coś poszło nie tak z wpisaną kwerendą SQL-a...";
    } 
    $polaczenie->close();
}
?>