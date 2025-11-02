<?php
session_start();

if(isset($_POST['email']))
{
    $validation_correct = true;

    // Sprawdzenie dlugosci loginu
    $login = $_POST['login'];
    if( strlen($login)<3 || (strlen($login)>20)){
        $validation_correct = false;
        $_SESSION['err_login'] = "Login musi się składać od 3 do 20 znaków!";
    }
    // Weryfikacja czy fraza nie zawiera polskich znakow, jesli nie zwraca true:
    if(ctype_alnum($login) == false){
        $validation_correct = false;
        $_SESSION['err_login'] = "Dopuszczalne tylko Polskie znaki i cyfry.";
    }
    
    //Sprawdzenie poprawnosci adresu email:
    $email = $_POST['email'];
    $emailB = filter_var($email, FILTER_SANITIZE_EMAIL);
    
    // Walidacja poprawnosci skladni maila oraz porownanie wpisanego maila ze zwroconym po usunieciu z niego polskich znakow:
    if((filter_var($email, FILTER_VALIDATE_EMAIL)==false) || ($emailB != $email)){
        $validation_correct = false;
        $_SESSION['err_email'] = "Podaj poprawny adres email!";
    }
    
    // Sprawdzenie numeru telefonu czy ciag to wylacznie cyfry oraz czy wpisano wymagana ilosc cyfr:
    $phone = $_POST['phone'];
    if( !(is_numeric($phone)) ){
        $validation_correct = false;
        $_SESSION['err_phone'] = "Akceptowalne są wyłącznie cyfry!";
    }

    if((strlen($phone)<9) || (strlen($phone)>9)){
        $validation_correct = false;
        $_SESSION['err_phone'] = "Numer telefonu musi mieć 9 cyfr!";
    }

    // Sprawdzenie poprawnosci pola imię, pod katem dopuszczalnej dlugosci ciagu, oraz czy nie wpisano niedopuszczalnych cyfr:
    $user_name = $_POST['user_name'];
    if( strlen($user_name)<3 || (strlen($user_name)>15)){
        $validation_correct = false;
        $_SESSION['err_name'] = "Wymagane są co najmniej 3 znaki!";
    }
    if( is_numeric($user_name) == true ){
        $validation_correct = false;
        $_SESSION['err_name'] = "Akceptowalne są wyłącznie litery!";
    }

    // Sprawdzenie poprawnosci pola nazwisko. Czy wprowadzono wlasciwa dlugosc ciagu i czy sa podane wylacznie litery:
    $surname = $_POST['surname'];
    if( strlen($surname)<3 || (strlen($surname)>15)){
        $validation_correct = false;
        $_SESSION['err_surname'] = "Wymagane są co najmniej 3 znaki!";
    }
    if( is_numeric($surname) == true ){
        $validation_correct = false;
        $_SESSION['err_surname'] = "Akceptowalne są wyłącznie litery!";
    }

    // Sprawdzenie poprawnosci pola kod pocztowy, pod katem wymaganej dlugosci ciagu:
    $post_code = $_POST['post_code'];
    if((strlen($post_code)<6) || (strlen($post_code)>6)){
        $validation_correct = false;
        $_SESSION['err_post_code'] = "Wprowadź 5 cyfrowy kod pocztowy.";
    }

    // Sprawdzenie poprwanosci wypelnionego pola miasto. Weryfikacja dopuszczalnej dlugosci ciagu, oraz czy wpisano wylacznie litery:
    $city = $_POST['city'];
    if( strlen($city)<3 || (strlen($city)>15)){
        $validation_correct = false;
        $_SESSION['err_city'] = "Wymagane są co najmniej 3 znaki!";
    }
    if( is_numeric($city) == true ){
        $validation_correct = false;
        $_SESSION['err_city'] = "Akceptowalne są wyłącznie litery!";
    }

    // Sprawdzenie popawnosci wypelnienia pola ulica:
    $street = $_POST['street'];
    if( strlen($street)<3 || (strlen($street)>15)){
        $validation_correct = false;
        $_SESSION['err_street'] = "Wymagane są co najmniej 3 znaki!";
    }
    if( is_numeric($street) == true ){
        $validation_correct = false;
        $_SESSION['err_street'] = "Akceptowalne są wyłącznie litery!";
    }

    // Sprawdzenie poprawnosci wpisanego hasla:
    $haslo1 = $_POST['haslo1'];
    $haslo2 = $_POST['haslo2'];

    // Wymagana dlugosc ciagu:
    if((strlen($haslo1)<8) || (strlen($haslo1)>20)){
        $validation_correct = false;
        $_SESSION['err_haslo'] = "Hasło musi posiadać od 8 do 20 znaków!";
    }
    // Weryfikacja czy oba hasla sa identyczne:
    if($haslo1 != $haslo2){
        $validation_correct = false;
        $_SESSION['err_haslo'] = "Podane hasła nie są identyczne!";
    }
    // Zahashowanie hasla, po to by z poziomu bazy d. nie bylo ono jawne
    $haslo_hash = password_hash($haslo1, PASSWORD_DEFAULT);
    // echo $haslo_hash;

    // Weryfikacja czy zaakceptowano regulamin:
    if(!isset($_POST['regulamin'])){
        $validation_correct = false;
        $_SESSION['err_regulamin'] = "Potwierdź akceptację regulaminu!";
    }

    //Implikacja API recaptcha:
	$sekret = "6Lc5LiQpAAAAACoYrrnPQIz4L8GAc83qJTECnOhU";
	$sprawdz = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$sekret.'&response='.$_POST['g-recaptcha-response']);
		
	$odpowiedz = json_decode($sprawdz);
		
	if ($odpowiedz->success==false){
		$validation_correct = false;
		$_SESSION['err_bot']="Potwierdź, że nie jesteś robotem!";
	}

    $flat_num = $_POST['flat_num'];

    //W tym miejscu dochodzi do zapamietania wprowadzonych informacji z wypelnionych pol:
    $_SESSION['save_login'] = $login;
    $_SESSION['save_email'] = $email;
    $_SESSION['save_phone'] = $phone;
    $_SESSION['save_name'] = $user_name;
    $_SESSION['save_surname'] = $surname;
    $_SESSION['save_post_code'] = $post_code;
    $_SESSION['save_city'] = $city;
    $_SESSION['save_street'] = $street;
    $_SESSION['save_flat_num'] = $flat_num;
    $_SESSION['save_haslo1'] = $haslo1;
    $_SESSION['save_haslo2'] = $haslo2;    
    if(isset($_POST['regulamin'])) $_SESSION['save_regulamin'] = true;

    require_once "connect.php";
    mysqli_report(MYSQLI_REPORT_STRICT);

    try{
        $polaczenie = new mysqli($host, $db_user, $db_password, $db_name);
        if($polaczenie->connect_errno!=0){
            throw new Exception(mysqli_connect_errno());
        }
        else{
            // Weryfikacja czy taki adres email juz istnieje w bazie:
            $rezultat = $polaczenie->query("SELECT user_id FROM users WHERE user_email='$email'");

            if(!$rezultat) throw new Exception($polaczenie->error);

            $ile_takich_maili = $rezultat->num_rows;
            if($ile_takich_maili>0){
                $validation_correct = false;
                $_SESSION['err_email']="Istnieje już konto przypisane do tego adresu email.";
            }

            //Sprawdzenie czy wpisany login juz istnieje w bazie:
            $rezultat = $polaczenie->query("SELECT user_id FROM users WHERE user_login='$login'");

            if(!$rezultat) throw new Exception($polaczenie->error);

            $ile_takich_loginow = $rezultat->num_rows;
            if($ile_takich_loginow>0){
                $validation_correct = false;
                $_SESSION['login']="Istnieje już użytkownik o takiej nazwie, proszę podać inną.";
            }
                
            if($validation_correct == true){ 
                if($polaczenie->query("INSERT INTO users VALUES (NULL, '$login', '$haslo_hash', '$email', '$user_name', '$surname', '$phone', '$post_code', '$city', '$street', '$flat_num')")){
                    
                    $_SESSION['udanarejestracja']=true;
                    header('Location: welcome.php');
                }
                else{
                    throw new Exception($polaczenie->error);
                }        
            }
            $polaczenie->close();
        }
    }catch(Exception $e){
        echo '<span style="color:tomato;">Błąd serwera! Prosimy o rejestrację w innym terminie.</span>';
        // echo '<br>Informacja do wyświetlenia dla dewelopera: '. $e;
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vears - Rejestracja</title>
    <link rel="stylesheet" href="./css/logowanie.css">
    <script src="https://kit.fontawesome.com/0f35c72659.js" crossorigin="anonymous"></script>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body>
    <div class="container-registration">
        <div class="container-registration__header">
            <p><a class="logo" href="./index.php"><i class="fa-solid fa-shirt"></i><span>VEARS</span></a></p>
        </div>
        <h1 class="container-registration__title">Rejestracja nowego konta</h1>
        <form method="post">
            <div class="wrapper-box">
                <div class="wrapper-box__left">
                    <input type="text" placeholder="nadaj login" value="<?php 
                            if(isset($_SESSION['save_login'])){
                            echo $_SESSION['save_login'];
                            unset($_SESSION['save_login']);
                        } ?>" name="login"><br>
                        <?php
                        if(isset($_SESSION['err_login']))
                        {
                            echo '<div class="error">' . $_SESSION['err_login'] . '</div>';
                            unset($_SESSION['err_login']);
                        } ?>
                    <input type="text" value="<?php 
                        if(isset($_SESSION['save_email'])){
                            echo $_SESSION['save_email'];
                            unset($_SESSION['save_email']);
                        } ?>" placeholder="Wpisz swój e-mail" name="email"><br>
                    <?php
                    if(isset($_SESSION['err_email'])){
                        echo '<div class="error">' . $_SESSION['err_email'] . '</div>';
                        unset($_SESSION['err_email']);
                    } ?>
                    <input type="text" placeholder="Numer telefonu" maxlength="9" value="<?php 
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
                        if(isset($_SESSION['save_name'])){
                        echo $_SESSION['save_name'];
                        unset($_SESSION['save_name']);
                        } ?>" name="user_name"><br>
                    <?php
                    if(isset($_SESSION['err_name'])){
                        echo '<div class="error">' . $_SESSION['err_name'] . '</div>';
                        unset($_SESSION['err_name']);
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
                        } ?>" placeholder="hasło*" name="haslo1"><br>
                    <?php
                    if(isset($_SESSION['err_haslo'])){
                        echo '<div class="error">' . $_SESSION['err_haslo'] . '</div>';
                        unset($_SESSION['err_haslo']);
                    } ?>
                    <input type="password" placeholder="potwierdź hasło*" value="<?php 
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
                        } ?>" name="post_code"><br>
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
            <label class="checkbox-registration">
                        <input type="checkbox" name="regulamin" <?php 
                if(isset($_SESSION['save_regulamin'])){
                    echo "checked";
                    unset($_SESSION['save_regulamin']);
                }?>>
                Oświadczam że zapoznałem/łam się z <a href="./regulations.php" class="register-link" target="_blank">Regulaminem</a> oraz <a href="./privacy_policy.php" class="register-link" target="_blank">Polityką prywatności</a> i akceptuję ich warunki.
            </label>
                <?php
                    if(isset($_SESSION['err_regulamin'])){
                        echo '<div class="error error-registration">' . $_SESSION['err_regulamin'] . '</div>';
                        unset($_SESSION['err_regulamin']);
                    } ?>
            <div class="wrapper-bottom">
                <div class="g-recaptcha" data-sitekey="6Lc5LiQpAAAAAORgTo5cPry3jkvy83nXfNphXvXH"></div>
                    <?php
                        if(isset($_SESSION['err_bot'])){
                            echo '<div class="error">' . $_SESSION['err_bot'] . '</div>';
                            unset($_SESSION['err_bot']);
                        } ?>
                <input type="submit" class="registration-submit" value="zarejestruj konto">
            </div>   
            <a href="logging.php"><p>* Masz już konto -> Zaloguj*</p></a>
        </form>
    </div>
</body>
</html>