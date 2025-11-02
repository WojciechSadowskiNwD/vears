<?php
session_start();

if(isset($_POST['email']))
{
    $validation_correct = true;

    $user = $_POST['login'];
    // Sprawdzenie dlugosci loginu:
    if( strlen($user)<3 || (strlen($user)>20)){
        $validation_correct = false;
        $_SESSION['err_user'] = "Login musi się składać od 3 do 20 znaków!";
    }

    if(ctype_alnum($user) == false){
        $validation_correct = false;
        $_SESSION['err_user'] = "Login może składać się tylko z liter i cyfr.";
    }
    
    // Sprawdzenie poprawnosci adresu email:
    $email = $_POST['email'];
    $emailB = filter_var($email, FILTER_SANITIZE_EMAIL);
    
    // Walidacja poprawnosci skladni maila oraz porownanie wpisanego maila wersja po usunieciu polskich znakow:
    if((filter_var($email, FILTER_VALIDATE_EMAIL) == false) || ($emailB != $email)){
        $validation_correct = false;
        $_SESSION['err_email'] = "Podaj poprawny adres email!";
    }
    
    // Sprawdzenie poprawnosici numeru telefonu:
    $phone = $_POST['phone'];
    
    if( !(is_numeric($phone)) ){
        $validation_correct = false;
        $_SESSION['err_phone'] = "Akceptowalne są wyłącznie cyfry!";
    }

    if((strlen($phone)<1) || (strlen($phone)>9)){
        $validation_correct = false;
        $_SESSION['err_phone'] = "Numer telefonu musi mieć 9 cyfr!";
    }

    // Sprawdzenie poprawnosci wpisania imienia:
    $user_name = $_POST['user_name'];

    if( strlen($user_name)<3 || (strlen($user_name)>15)){
        $validation_correct = false;
        $_SESSION['err_user_name'] = "Wymagane są co najmniej 3 znaki!";
    }
    if( is_numeric($user_name)==true ){
        $validation_correct = false;
        $_SESSION['err_user_name'] = "Akceptowalne są wyłącznie litery!";
    }

     // Sprawdzenie pola nazwisko:
     $surname = $_POST['surname'];

     if( strlen($surname)<3 || (strlen($surname)>15)){
         $validation_correct = false;
         $_SESSION['err_surname'] = "Wymagane są co najmniej 3 znaki!";
     }
     if( is_numeric($surname)==true ){
         $validation_correct = false;
         $_SESSION['err_surname'] = "Akceptowalne są wyłącznie litery!";
     }

    // Sprawdzenie kodu pocztowego:
    $post_code = $_POST['post_code'];
    
    if( !(is_numeric($post_code)) ){
        $validation_correct = false;
        $_SESSION['err_post_code'] = "Akceptowalne są wyłącznie cyfry!";
    }

    if((strlen($post_code)<5) || (strlen($post_code)>5)){
        $validation_correct = false;
        $_SESSION['err_post_code'] = "Wprowadź 5 cyfrowy kod pocztowy.";
    }

    // Sprawdzenie poprawnosci pola miasto:
    $city = $_POST['city'];

    if( strlen($city)<3 || (strlen($city)>15)){
       $validation_correct = false;
       $_SESSION['err_city'] = "Wymagane są co najmniej 3 znaki!";
    }
    if( is_numeric($city) == true ){
        $validation_correct = false;
       $_SESSION['err_city'] = "Akceptowalne są wyłącznie litery!";
    }

    // Sprawdzenie poprawnosci pola ulica:
    $street = $_POST['street'];

    if( strlen($street)<3 || (strlen($street)>15)){
        $validation_correct=false;
       $_SESSION['err_street'] = "Wymagane są co najmniej 3 znaki!";
    }
    if( is_numeric($street) == true ){
       $validation_correct = false;
       $_SESSION['err_street'] = "Akceptowalne są wyłącznie litery!";
    }

    // Sprawdzenie poprawnosci hasla:
    $haslo1 = $_POST['haslo1'];
    $haslo2 = $_POST['haslo2'];

    if((strlen($haslo1)<8) || (strlen($haslo1)>20)){
        $validation_correct = false;
        $_SESSION['err_haslo'] = "Hasło musi posiadać od 8 do 20 znaków!";
    }

    if($haslo1 != $haslo2){
        $validation_correct = false;
        $_SESSION['err_haslo'] = "Podane hasła nie są identyczne!";
    }
    // Zahashowanie hasla po to by  w bazie d. nie bylo jawnie wyswietlane
    $haslo_hash = password_hash($haslo1, PASSWORD_DEFAULT);
    // echo "Sprawdzenie efektu operacji: "  . $haslo_hash;

    // Sprawdzenie czy zaakceptowano regulamin:
    if(!isset($_POST['regulamin'])){
        $validation_correct = false;
        $_SESSION['err_regulamin'] = "Potwierdź akceptację regulaminu!";
    }

    $flat_num = $_POST['flat_num'];
        
    require_once "connect.php";
    mysqli_report(MYSQLI_REPORT_STRICT);

    try{
        $polaczenie = new mysqli($host, $db_user, $db_password, $db_name);
        if($polaczenie->connect_errno!=0){
            throw new Exception(mysqli_connect_errno());
        }
        else{
            // Czy taki email istnieje w bazie:
            $rezultat = $polaczenie->query("SELECT id FROM uzytkownicy WHERE email='$email'");

            if(!$rezultat) throw new Exception($polaczenie->error);

            $ile_takich_maili = $rezultat->num_rows;

            if($ile_takich_maili > 0){
                $validation_correct = false;
                $_SESSION['err_email'] = "Istnieje już konto przypisane do tego adresu email.";
            }

            //Czy taki login istnieje w bazie:
            $rezultat = $polaczenie->query("SELECT id FROM uzytkownicy WHERE user='$user'");

            if(!$rezultat) throw new Exception($polaczenie->error);

            $ile_takich_loginow = $rezultat->num_rows;
            if($ile_takich_loginow > 0){
                $validation_correct = false;
                $_SESSION['login'] = "Istnieje już użytkownik o takiej nazwie, proszę podać inną.";
            }

            if($validation_correct == true){
                    // Aktualizacja danych uzytkownika w bazie danych:
                    if($polaczenie->query("UPDATE uzytkownicy SET 'pass'='$haslo_hash', 'name'='$user_name', 'surname'='$surname', 'phone'='$phone', 'post_code'='$post_code', 'city'='$city', 'street'='$street', 'flat_number'='$flat_num')")){
                        $_SESSION['udanarejestracja']=true;
                        header('Location: welcome.php');
                    }else{
                        throw new Exception($polaczenie->error);
                    }         
                }
                $polaczenie->close();
            }
        }catch(Exception $e){
            echo '<span style="color:tomato;">Błąd serwera! Prosimy o rejestrację w innym terminie.</span>';
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vears - Panel użytkownika</title>
    <link rel="stylesheet" href="./css/logowanie.css">
    <script src="https://kit.fontawesome.com/0f35c72659.js" crossorigin="anonymous"></script>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body>
    <div class="container-registration">
        <div class="container-registration__header">
            <p><a class="logo" href="./index.html"><i class="fa-solid fa-shirt"></i><span>VEARS</span></a></p>
        </div>
        <h1 class="container-registration__title">Aktualizuj Dane Personalne</h1>
        <p>Tutaj możesz edytować wcześniej wprowadzone dane w czasie rejestracji konta, oraz zmienić adres, do dostawy (Wszystkie pola są wymagane).</p>
        <br><br>
        <form method="post">
            <div class="wrapper-box">
                <div class="wrapper-box__left">
                    <input type="text" placeholder="Numer telefonu" value="<?php 
                        if(isset($_SESSION['save_phone'])){
                        echo $_SESSION['save_phone'];
                        unset($_SESSION['save_phone']);
                        } ?>" name="phone"><br>

                    <?php
                    if(isset($_SESSION['err_phone'])){
                        echo '<div class="error">' . $_SESSION['err_phone'] . '</div>';
                        unset($_SESSION['err_phone']);
                    } ?>

                    <input type="text" placeholder="Imię" value="<?php 
                        if(isset($_SESSION['save_user_name'])){
                        echo $_SESSION['save_user_name'];
                        unset($_SESSION['save_user_name']);
                        } ?>" name="user_name"><br>
                    
                    <?php
                    if(isset($_SESSION['err_user_name'])){
                        echo '<div class="error">' . $_SESSION['err_user_name'] . '</div>';
                        unset($_SESSION['err_user_name']);
                    } ?>

                    <input type="text" placeholder="Nazwisko" value="<?php 
                        if(isset($_SESSION['save_surname'])){
                        echo $_SESSION['save_surname'];
                        unset($_SESSION['save_surname']);
                        } ?>" name="surname"><br>
                    <?php
                    if(isset($_SESSION['err_surname'])){
                        echo '<div class="error">' . $_SESSION['err_surname'] . '</div>';
                        unset($_SESSION['err_surname']);
                    } ?>
                    
                    <input type="password" value="<?php 
                        if(isset($_SESSION['save_haslo1'])){
                        echo $_SESSION['save_haslo1'];
                        unset($_SESSION['save_haslo1']);
                        } ?>" placeholder="nowe hasło*" name="haslo1"><br>
                    <?php
                    if(isset($_SESSION['err_haslo'])){
                        echo '<div class="error">' . $_SESSION['err_haslo'] . '</div>';
                        unset($_SESSION['err_haslo']);
                    } ?>

                    <input type="password" placeholder="powtórz nowe hasło*" value="<?php 
                        if(isset($_SESSION['save_haslo2'])){
                        echo $_SESSION['save_haslo2'];
                        unset($_SESSION['save_haslo2']);
                        } ?>" name="haslo2"><br>
                    <?php
                    if(isset($_SESSION['err_haslo'])){
                        echo '<div class="error">' . $_SESSION['err_haslo'] . '</div>';
                        unset($_SESSION['err_haslo']);
                    } ?>

                </div>
                <div class="wrapper-box__right">

                    <input type="text" placeholder="Kod pocztowy" value="<?php 
                        if(isset($_SESSION['save_post_code'])){
                        echo $_SESSION['save_post_code'];
                        unset($_SESSION['save_post_code']);
                        }    
                    ?>" name="post_code"><br>
                    <?php
                    if(isset($_SESSION['err_post_code'])){
                        echo '<div class="error">' . $_SESSION['err_post_code'] . '</div>';
                        unset($_SESSION['err_post_code']);
                    } ?>

                    <input type="text" placeholder="Miasto" value="<?php 
                        if(isset($_SESSION['save_city'])){
                        echo $_SESSION['save_city'];
                        unset($_SESSION['save_city']);
                        } ?>" name="city"><br>
                    <?php
                    if(isset($_SESSION['err_city'])){
                        echo '<div class="error">' . $_SESSION['err_city'] . '</div>';
                        unset($_SESSION['err_city']);
                    } ?>

                    <input type="text" placeholder="Ulica" value="<?php 
                        if(isset($_SESSION['save_street'])){
                        echo $_SESSION['save_street'];
                        unset($_SESSION['save_street']);
                        } ?>" name="street"><br>
                    <?php
                    if(isset($_SESSION['err_street'])){
                        echo '<div class="error">' . $_SESSION['err_street'] . '</div>';
                        unset($_SESSION['err_street']);
                    } ?>

                    <input type="text" placeholder="Numer mieszkania" value="<?php 
                        if(isset($_SESSION['save_flat_num'])){
                        echo $_SESSION['save_flat_num'];
                        unset($_SESSION['save_flat_num']);
                        } ?>" name="flat_num"><br>
                    <?php
                    if(isset($_SESSION['err_flat_num'])){
                        echo '<div class="error">' . $_SESSION['err_flat_num'] . '</div>';
                        unset($_SESSION['err_flat_num']);
                    } ?>

                </div>
            </div>
            <br><br>
            <div class="wrapper-bottom">
                <input type="submit" class="registration-submit" value="zapisz zmiany">
            </div>    
        </form>
    </div>
</body>
</html>