<?php
session_start();

if( !isset($_SESSION['zalogowany'])){
    header('Location: index.php');
    exit();
}

require_once "connect.php";
mysqli_report(MYSQLI_REPORT_STRICT);

// Wyszukiwanie zamowienia:    
try{
    $polaczenie = new mysqli($host, $db_user, $db_password, $db_name);
    if($polaczenie->connect_errno!=0){
        throw new Exception(mysqli_connect_errno());
    }
    else{
        if(isset($_POST['search-order'])){
            $validation_correct = true;
            
            //A. Sprawdzenie poprawnosci szukanego numeru zamowienia:
            $search_order = $_POST['search-order'];
            
            if( !(is_numeric($search_order)) ){
                $validation_correct = false;
                $_SESSION['err_search_order'] = "Akceptowalne są wyłącznie cyfry!";
            }
            if( strlen($search_order)<8 || (strlen($search_order)>10)){
                $validation_correct = false;
                $_SESSION['err_search_order'] = "Numer zamówienia powinien liczyć od 8 do 10 znaków!";
            }
            
            //B. Sprawdzenie poprawnosci szukanego numer telefonu:
            $search_phone = $_POST['search-phone'];
                
            if( !(is_numeric($search_phone)) ){
                $validation_correct = false;
                $_SESSION['err_search_phone'] = "Akceptowalne są wyłącznie cyfry!";
            }
            if((strlen($search_phone)<9) || (strlen($search_phone)>9)){
                $validation_correct = false;
                $_SESSION['err_search_phone'] = "Numer telefonu musi mieć 9 cyfr!";
            }

            if($validation_correct == true){
                //C. Sprawdzenie czy numer zamownienia istnieje w bazie:
                $result = $polaczenie->query("SELECT order_id FROM orders WHERE order_id='$search_order'");
    
                if(!$result) throw new Exception($polaczenie->error);
                $ile_takich_wynikow = $result->num_rows;
                if($ile_takich_wynikow == 0){
                    $validation_correct = false;
                    $_SESSION['err_search_order']="Nie znaleziono zamównienia o takim numerze.";
                }
    
                //D. Czy taki nr telefonu istnieje w bazie:
                $result = $polaczenie->query("SELECT order_phone FROM orders WHERE order_phone='$search_phone'");

                if(!$result) throw new Exception($polaczenie->error);
                $ile_takich_wynikow = $result->num_rows;
                
                if($ile_takich_wynikow == 0){
                    $validation_correct = false;
                    $_SESSION['err_search_phone']="Nie znaleziono takiego numeru telefonu w bazie.";
                }
            }
            
            // Jezeli wszystkie powyzsze testy zostaly zaliczone, realizuje sie docelowe zapytanie:
            if($validation_correct == true){
                $sql_A = "SELECT * FROM orders WHERE order_id='$search_order' AND order_phone='$search_phone'";

                $sql_B = "SELECT * FROM ordered_items WHERE order_id = '$search_order'";           
                $result_B = mysqli_query($polaczenie, $sql_B);
                $ile_B = mysqli_num_rows($result_B);
                $summary = 0;

                for ($y = 1; $y <= $ile_B; $y++){
                  $row_B = mysqli_fetch_assoc($result_B);
                  $product_pieces = $row_B['ordered_pieces'];
                  $product_price = $row_B['ordered_current_price'];

                  $sum = $product_price*$product_pieces;
                  $summary += $sum;
                }
            }
            // $polaczenie->close();
        }
    }
}catch(Exception $e){
    echo '<span style="color:tomato;">Błąd serwera! Prosimy o rejestrację w innym terminie.</span>';
    // echo '<br>Informacja deweloperska: '. $e;
}

// Zadeklarowanie i przypisanie zmiennym wartosci tymczasowych:
$name = $_SESSION['name'];
$surname = $_SESSION['surname'];
$phone = $_SESSION['phone'];
$post_code = $_SESSION['post_code'];
$city = $_SESSION['city'];
$street = $_SESSION['street'];
$flat_num = $_SESSION['flat_num'];

$polaczenie2 = mysqli_connect($host, $db_user, $db_password, $db_name);
mysqli_query($polaczenie2, "SET CHARSET utf8");
mysqli_query($polaczenie2, "SET NAMES 'utf8' COLLATE 'utf8_polish_ci'");
mysqli_select_db($polaczenie2, $db_name);

if( $polaczenie2->connect_errno!=0 ){
    echo "Error: ".$polaczenie2->connect_errno;
}
else{
    $validation_correct_in = true;

    if($polaczenie2 == true){
        $now_id = $_SESSION['id'];
    }

    //1. Sprawdzenie poprawnosci name:
    if(isset($_POST['name'])){
        $name = $_POST['name'];
        $_SESSION['name'] = $name;
    
        // Sprawdzenie dlugosci imienia:
        if( strlen($name)<3 || (strlen($name)>20)){
            $validation_correct_in = false;
            $_SESSION['err_name'] = "Imie musi się składać od 3 do 20 znaków!";
        }
    }

    //2. Sprawdzenie poprawności nazwiska:
    if(isset($_POST['surname'])){
        $surname = $_POST['surname'];
        $_SESSION['surname'] = $surname;
        
        // Sprawdzenie dlugosci nazwiska:
        if( strlen($surname)<3 || (strlen($surname)>20)){
            $validation_correct_in = false;
            $_SESSION['err_surname'] = "Nazwisko musi się składać od 3 do 20 znaków!";
        }
    }

    //3. Sprawdzenie poprawnosci numeru telefonu:
    if(isset($_POST['phone'])){
        $phone = $_POST['phone'];
        $_SESSION['phone'] = $phone;
        
        if( !(is_numeric($phone)) ){
            $validation_correct_in = false;
            $_SESSION['err_phone'] = "Akceptowalne są wyłącznie cyfry!";
        }
        if((strlen($phone)<9) || (strlen($phone)>9)){
            $validation_correct_in = false;
            $_SESSION['err_phone'] = "Numer telefonu musi mieć 9 cyfr!";
        }
    }

    //4. Sprawdzenie poprawnosci kodu pocztowego:
    if(isset($_POST['post_code'])){
        $post_code = $_POST['post_code'];
        $_SESSION['post_code'] = $post_code;

        if((strlen($post_code)<6) || (strlen($post_code)>6)){
            $validation_correct_in = false;
            $_SESSION['err_post_code'] = "Wprowadź 5 cyfrowy kod pocztowy.";
        }
    }

    //5. Sprawdzenie poprawnosci zawartosci pola miasto:
    if(isset($_POST['city'])){
        $city = $_POST['city'];
        $_SESSION['city'] = $city;
        
        if( strlen($city)<3 || (strlen($city)>15)){
            $validation_correct_in = false;
            $_SESSION['err_city'] = "Wymagane są co najmniej 3 znaki!";
        }
        if( is_numeric($city)==true ){
            $validation_correct_in = false;
            $_SESSION['err_city'] = "Akceptowalne są wyłącznie litery!";
        }
    }

    //6. Sprawdzenie poprawnosci wprowadzenia nazwy ulicy:
    if(isset($_POST['street'])){
        $street = $_POST['street'];
        $_SESSION['street'] = $street;

        if( strlen($street)<3 || (strlen($street)>15)){
            $validation_correct_in = false;
            $_SESSION['err_street'] = "Wymagane są co najmniej 3 znaki!";
        }
        if( is_numeric($street)==true ){
            $validation_correct_in = false;
            $_SESSION['err_street'] = "Akceptowalne są wyłącznie litery!";
        }
    }
    //7. Sprawdzenie poprawnosci wprowadzonego numeru mieszkania:
    if(isset($_POST['flat_num'])){
        $flat_num = $_POST['flat_num'];
        $_SESSION['flat_num'] = $flat_num;

        if( strlen($street)<1){
            $validation_correct_in = false;
            $_SESSION['err_flat_num'] = "Proszę podać poprawną wartość!";
        }
    }

    // Zapamietanie wprowadzonych danych:
    $_SESSION['save_name'] = $name;
    $_SESSION['save_surname'] = $surname;
    $_SESSION['save_phone'] = $phone;
    $_SESSION['save_post_code'] = $post_code;
    $_SESSION['save_city'] = $city;
    $_SESSION['save_street'] = $street;
    $_SESSION['save_flat_num'] = $flat_num;

    if($validation_correct_in == true){
        if($polaczenie2->query("UPDATE users SET user_name='$name', user_surname='$surname', user_phone='$phone', user_post_code='$post_code', user_city='$city', user_street='$street', user_flat='$flat_num' WHERE user_id='$now_id'")){ }
    }else{
        echo "Błąd: Nie udało się wprowadzić zmian w danych tego konta!";
    }
        $now_id = $_SESSION['id'];
        $sql_in = "SELECT order_id, order_date, order_status, order_phone, order_post_code, order_city, order_street, order_flat FROM orders WHERE user_id = '$now_id'";

    $polaczenie2->close();
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vears - pomyślnie zalogowany</title>
    <meta name="description"
        content="Użytkownikowi udało się zalogować.">
    <script src="https://kit.fontawesome.com/0f35c72659.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="./css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;400;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@425&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Niebieska gorna belka - nawigacji -->
    <div class="nav-top">
        <div class="nav-top__blocks">
            <!-- Dwa pierwsze przyciski niebieskiej belki -->
            <div class="nav-top__blocks-block">
                <a href="#" class="help-btn top-nav-btns">Pomoc</a>
            </div>
            <div class="nav-top__blocks-block">
                <a href="#" id="status-order-top" class="status-order-btn top-nav-btns">
                    <i class="fa-solid fa-truck"></i>
                    Status zamówienia</a>
            </div>
            <!-- Przycisk wyboru jezyka -->
            <ol class="nav-top__blocks-ol ol-normal-lang">
                <li><a href="#"><i class="fa-solid fa-earth-americas"></i>Polski</a>
                    <ul>
                        <li><a href="#">English</a></li>
                        <li><a href="#">Deutsch</a></li>
                    </ul>
            </ol>
        </div>
    </div>
    <!-- Pole wysuwane, gdy kliknie sie w POMOC -->
    <div class="pull-out-block-help">
        <div class="pull-out-block-help__box1">
            <p><a href="./regulations.php">Regulamin sklepu</a></p>
            <p><a href="./privacy_policy.php">Polityka prywatności</a></p>
            <p><a href="./shipping_cost.php">Koszt i sposoby dostawy</a></p>
        </div>
        <div class="pull-out-block-help__box2">
            <p>Czy masz pytanie?</p>
            <i class="fa-solid fa-phone-volume"></i>
            <p class="p-0">Nie możesz znaleźć informacji, związanych z zakupami w naszym e-sklepie? Zadzwoń do nas a
                chętnie
                pomożemy. </p>
            <p class="p-0 p-1">Jesteśmy pod telefonem:</p><span>777 888 999</span>
            <p class="p-2">od pon do pt w godz. 8:00 - 19:00</p>
        </div>
        <div class="pull-out-block-help__box3">
            <p><a href="./returns_and_complaints.php">Zwroty i reklamacje</a></p>
            <p><a href="./methods_of_payment.php">Formy płatności</a></p>
            <p><a href="./contact.php">Pozostałe formy kontaktu</a></p>
            <button class="pull-out-block-btn pull-close-btn">
                <p>Zwiń<i class="fa-solid fa-chevron-up"></i></p>
            </button>
        </div>
    </div>
    <!-- Wyszukiwanie zamowienia -->
<?php
// Jezeli wyslano nr zamowienia i nr telefonu:
if(isset($_POST['search-order'])){
    // Gdy walidacja nie ma bledow:
    if($validation_correct == true){
    ?>
        <div class="pull-out-block-order order-scroll open-accordion">
            <h1>Twoje zamówienie</h1>
          <div class="gray-box">
            <?php
                $result = mysqli_query($polaczenie, $sql_A);
                $ile = mysqli_num_rows($result);
            
                for ($i = 1; $i <= $ile; $i++){
                    $row = mysqli_fetch_assoc($result);
                    $order_id = $row['order_id'];
                    $order_date = $row['order_date'];
                    $order_phone = $row['order_phone'];
                    $order_post_code = $row['order_post_code'];
                    $order_city = $row['order_city'];
                    $order_street = $row['order_street'];
                    $order_flat = $row['order_flat'];

                    echo "<div class='order-table'>";
                    echo "<table><tr>
                                <th>Numer zamówienia:</th>
                                <th>Zamówienie z dnia:</th>
                                <th>Telefon kontaktowy:</th>
                                <th>Zapłacono łącznie:</th>
                            </tr><tr>
                                <td>$order_id</td>
                                <td>$order_date</td>
                                <td>$order_phone</td>
                                <td>$summary zł</td>
                            </tr></table></div>";
                    $sql_B = "SELECT * FROM ordered_items WHERE order_id = '$search_order'";
                    $result_B = mysqli_query($polaczenie, $sql_B);
                    $ile_B = mysqli_num_rows($result_B);
                    $summary = 0;

                    for ($y = 1; $y <= $ile_B; $y++){
                      $row_B = mysqli_fetch_assoc($result_B);
                      $product_id = $row_B['product_id'];
                      $product_size = $row_B['ordered_item_size'];
                      $product_pieces = $row_B['ordered_pieces'];
                      $product_price = $row_B['ordered_current_price'];
                      
                      $sum = $product_price*$product_pieces;
                      $summary += $sum;

                      $sql_C = "SELECT * FROM products WHERE idproduct = '$product_id'";
                      $result_C = mysqli_query($polaczenie, $sql_C);
                      $ile_C = mysqli_num_rows($result_C);  

                      for ($z = 1; $z <= $ile_C; $z++){
                      $row_C = mysqli_fetch_assoc($result_C);
                      $product_name = $row_C['nameProduct'];

                    echo "<div class='gray-section'>";
                      echo "<div class='order-items-table'>";
                      echo "<table><tr>
                                    <td class='td-product-name'>$y. $product_name</td>
                                    <td class='td-product-size'>Rozmiar: $product_size</td>
                                    <td class='td-product-price'>Cena: $product_price zł</td>
                                    <td class='td-product-pieces'>Sztuk: $product_pieces</td>
                                    <td class='td-product-sum'>Razem: $sum zł</td>
                               </tr>";
                      }
                      echo "</table></div></div>";
                    }
                    echo "<div class='order-address-table'>";
                      echo "<table><tr>
                                    <td class='td-adress'>Adres dostawy:</td>
                                    <td>$order_post_code</td>
                                    <td>$order_city</td>
                                    <td>ul. $order_street</td>
                                    <td>$order_flat</td>
                               </tr></table></div>";
            ?>
            <form method="post">
                <input type="submit" class="checkOutOrder-btn" value="Znajdź inne zamówienie">
            </form>    
          </div>
          <div class="search-order-btn">
             <button class="pull-out-block-btn pull-close-btn">
                <p>Zwiń<i class="fa-solid fa-chevron-up"></i></p>
             </button>  
          </div>
        </div><?php
                }      
    }else if($validation_correct == false){
        ?>
        <div class="pull-out-block-order open-accordion">
            <h1>Sprawdź status zamówienia</h1>
            <form method="post">
                <input type="text" name="search-order" maxlength="10" class="input-order-number" placeholder="Numer zamówienia:">
                <?php
                    if(isset($_SESSION['err_search_order'])){
                        echo '<div class="order-error">' . $_SESSION['err_search_order'] . '</div>';
                        unset($_SESSION['err_search_order']);
                    } ?>
                <input type="text" name="search-phone" maxlength="9" class="input-mailOrTelephone" placeholder="Numer telefonu:">
                <?php
                    if(isset($_SESSION['err_search_phone'])){
                        echo '<div class="order-error">' . $_SESSION['err_search_phone'] . '</div>';
                        unset($_SESSION['err_search_phone']);
                    } ?>
                <input type="submit" class="checkOutOrder-btn" value="Sprawdź">
            </form>    
            <button class="pull-out-block-btn pull-close-btn">
                <p>Zwiń<i class="fa-solid fa-chevron-up"></i></p>
            </button>
    </div><?php
    }
}else if(!isset($_POST['search-order'])){
?>
    <div class="pull-out-block-order">
        <h1>Sprawdź status zamówienia</h1>
        <form method="post">
            <input type="text" name="search-order" maxlength="10" class="input-order-number" placeholder="Numer zamówienia:">
            <?php
                if(isset($_SESSION['err_search_order'])){
                    echo '<div class="order-error">' . $_SESSION['err_search_order'] . '</div>';
                    unset($_SESSION['err_search_order']);
                } ?>
            <input type="text" name="search-phone" maxlength="9" class="input-mailOrTelephone" placeholder="Numer telefonu:">
            <?php
                if(isset($_SESSION['err_search_phone'])){
                    echo '<div class="order-error">' . $_SESSION['err_search_phone'] . '</div>';
                    unset($_SESSION['err_search_phone']);
                } ?>
            <input type="submit" class="checkOutOrder-btn" value="Sprawdź">
        </form>    
        <button class="pull-out-block-btn pull-close-btn">
            <p>Zwiń<i class="fa-solid fa-chevron-up"></i></p>
        </button>
    </div>
<?php } ?> 
    <!-- Srodkowa belka nawigacji z logo -->
    <nav>
        <div class="nav-middle">
            <div class="nav-middle__left-block">
                <a class="logo" href="./index.php"><i class="fa-solid fa-shirt"></i><span>VEARS</span></a>
            </div>
            <div class="nav-middle__central-block">
                <form action="products.php" method="post">    
                    <input type="text" name="phrase" class="search" placeholder="wyszukaj produkt...">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <?php if(isset($_SESSION['blad']))  echo $_SESSION['blad']; ?>
                </form>
            </div>
            <div class="nav-middle__right-block">
                <div class="nav-middle__right-block-box1 right-btns">
                    <a href="./logging.php">
                        <i class="mobile-icons fa-solid fa-user"></i>
                        <?php
                            if(!isset($_SESSION['zalogowany'])){
                                echo "<p>zaloguj</p>";
                            }else{
                                echo "<p>zalogowano</p>";
                            } ?>
                    </a>
                </div>
                <div class="nav-middle__right-block-box2 right-btns">
                    <?php
                        if(isset($_SESSION['zalogowany'])){
                            echo '<a href="./favourites.php"><i class="fa-solid fa-heart"></i><p>ulubione</p></a>';
                        }else{
                            echo '<a class="fav-icon" href="#"><i class="fa-solid fa-heart"></i><p>ulubione</p></a>';
                        } ?>
                    <div class="fav-info">
                        <p>Dostępne po zalogowaniu.</p>
                    </div>
                </div>
                <div class="nav-middle__right-block-box3 right-btns">
                    <a href="./cart.php">
                        <i class="fa-solid fa-cart-shopping"></i>
                        <p>koszyk</p>
                    </a>
                </div>
            </div>
        </div>
        <!-- Dolna belka nawigacji z kategoriami produktow -->
        <div class="nav-bottom">
            <ol>
                <li><a href="./products.php?category=nowości">Nowości</a>
                    <?php
                        $_GET['category'] = 'nowości'; ?>
                </li>
                <li><a href="./products.php?category=bestsellery">Bestsellery</a>
                    <?php
                        $_GET['category'] = 'bestsellery'; ?>
                </li>
                <li><a href="./products.php?category=męskie">Męskie</a>
                <ul>
                    <li>
                        <a href="./products.php?category=bluzy męskie">
                            <?php
                                $_GET['category'] = 'bluzy męskie'; ?>
                        Bluzy</a>
                    </li>  
                    <li><a href="./products.php?category=koszule męskie">
                        <?php
                            $_GET['category'] = 'koszule męskie'; ?>
                        Koszule</a></li> 
                    <li><a href="./products.php?category=spodnie męskie">
                        <?php
                            $_GET['category'] = 'spodnie męskie'; ?>
                        Spodnie</a></li>
                    <li><a href="./products.php?category=t-shirty męskie">
                        <?php
                            $_GET['category'] = 't-shirty męskie'; ?>
                        T-shirty</a></li>
                    <li><a href="./products.php?category=kurtki męskie">
                        <?php
                            $_GET['category'] = 'kurtki męskie'; ?>
                        Kurtki</a></li>
                        <li><a href="./products.php?category=bielizna męskie">
                        <?php
                            $_GET['category'] = 'bielizna męskie'; ?>
                        Bielizna</a></li>
                    </ul>
                </li>
                <li><a href="./products.php?category=damskie">Damskie</a>
                    <ul>
                        <li><a href="./products.php?category=sukienki">
                            <?php
                                $_GET['category'] = 'sukienki damskie'; ?>
                        Sukienki</a></li>
                        <li><a href="./products.php?category=spódniczki">
                            <?php
                                $_GET['category'] = 'spódniczki'; ?>
                        Spódniczki</a></li>
                        <li><a href="./products.php?category=spodnie damskie">
                            <?php
                                $_GET['category'] = 'spodnie damskie'; ?>
                        Spodnie</a></li>
                        <li><a href="./products.php?category=bluzki damskie">
                            <?php
                                $_GET['category'] = 'bluzki damskie'; ?>
                        Bluzki</a></li>
                        <li><a href="./products.php?category=koszulki damskie">
                            <?php
                                $_GET['category'] = 'koszulki damskie'; ?>
                        Koszulki</a></li>
                        <li><a href="./products.php?category=kurtki damskie">
                            <?php
                                $_GET['category'] = 'kurtki damskie'; ?>
                        Kurtki</a></li>
                        <li><a href="./products.php?category=bielizna damska">
                            <?php
                                $_GET['category'] = 'bielizna damska'; ?>
                        Bielizna</a></li>
                    </ul>
                </li>
                <li><a href="./products.php?category=obuwie">Obuwie kobieta</a>
                    <ul>
                        <li><a href="./products.php?category=botki damskie">Botki</a>
                            <?php
                                $_GET['category'] = 'botki damskie'; ?>
                        </li>
                        <li><a href="./products.php?category=kapcie damskie">Kapcie</a>
                            <?php
                                $_GET['category'] = 'kapcie damskie'; ?>
                        </li>
                        <li><a href="./products.php?category=kozaki damskie">Kozaki</a>
                            <?php
                                $_GET['category'] = 'kozaki damskie'; ?>
                        </li>
                        <li><a href="./products.php?category=mokasyny damskie">Mokasyny</a>
                            <?php
                                $_GET['category'] = 'mokasyny damskie'; ?>
                        </li>
                        <li><a href="./products.php?category=sportowe damskie">Sportowe</a>
                            <?php
                                $_GET['category'] = 'sportowe damskie'; ?>
                        </li>
                        <li><a href="./products.php?category=szpilki damskie">Szpilki</a>
                            <?php
                                $_GET['category'] = 'szpilki damskie'; ?>
                        </li>
                    </ul>
                </li>
                <li><a href="./products.php?category=biżuteria">Biżuteria</a>
                    <ul>
                        <li><a href="./products.php?category=bransoletki damskie">Branzoletki</a>
                            <?php
                                $_GET['category'] = 'bransoletki damskie'; ?>
                        </li>
                        <li><a href="./products.php?category=kolczyki damskie">Kolczyki</a>
                            <?php
                                $_GET['category'] = 'kolczyki damskie'; ?>
                        </li>
                        <li><a href="./products.php?category=naszyjniki damskie">Naszyjiki</a>
                            <?php
                                $_GET['category'] = 'naszyjniki damskie'; ?>
                        </li>
                        <li><a href="./products.php?category=pierścionki damskie">Pierścionki</a>
                            <?php
                                $_GET['category'] = 'pierścionki damskie'; ?>
                        </li>
                    </ul>
                </li>
              </ol>
        </div>
    </nav>
<main>
    <section class="section-user">
        <h1 class="section-user__title">TWOJE KONTO</h1>
        <div class="section-user__options">
            <a href="./zalogowany.php" class="active">Dane i adres</a>
            <a href="./user_orders.php" >Zamówienia</a>
            <a href="./favourites.php" >Ulubione</a>
            <a href="./cart.php" >Koszyk</a>
            <a href="./logout.php">wyloguj</a>
        </div>
        <div class="section-user__box">
          <form method="post">
            <div class="section-user__box-left">
                <p>Imię:</p> <input type="text" value="<?php 
                    if(isset($_SESSION['save_name'])){
                        echo $_SESSION['save_name'];
                        unset($_SESSION['save_name']);
                        } ?>" name="name"> <br>
                    <?php
                    if(isset($_SESSION['err_name'])){
                        echo '<div class="error">' . $_SESSION['err_name'] . '</div>';
                        unset($_SESSION['err_name']);
                    } ?><br>
                <p>Nazwisko:</p> <input type="text" value="<?php  
                    if(isset($_SESSION['save_surname'])){
                        echo $_SESSION['save_surname'];
                        unset($_SESSION['save_surname']);
                    } ?>" name="surname"> <br>
                    <?php
                    if(isset($_SESSION['err_surname'])){
                        echo '<div class="error">' . $_SESSION['err_surname'] . '</div>';
                        unset($_SESSION['err_surname']);
                    } ?><br>
                <p>Telefon:</p> <input type="text" maxlength="9" value="<?php  
                    if(isset($_SESSION['save_phone'])){
                        echo $_SESSION['save_phone'];
                        unset($_SESSION['save_phone']);
                    } ?>" name="phone"> <br>
                    <?php
                    if(isset($_SESSION['err_phone'])){
                        echo '<div class="error">' . $_SESSION['err_phone'] . '</div>';
                        unset($_SESSION['err_phone']);
                    } ?><br>              
            </div>
            <div class="section-user__box-right">
                <p>Kod pocztowy:</p> <input type="text" maxlength="6" value="<?php
                        if(isset($_SESSION['save_post_code'])){
                        echo $_SESSION['save_post_code'];
                        unset($_SESSION['save_post_code']);
                        } ?>" name="post_code"><br>
                    <?php
                    if(isset($_SESSION['err_post_code'])){
                        echo '<div class="eror">' . $_SESSION['err_post_code'] . '</div>';
                        unset($_SESSION['err_post_code']);
                    } ?><br>
                <p>Miasto:</p> <input type="text" value="<?php 
                        if(isset($_SESSION['save_city'])){
                        echo $_SESSION['save_city'];
                        unset($_SESSION['save_city']);
                        } ?>" name="city"><br>
                    <?php
                    if(isset($_SESSION['err_city'])){
                        echo '<div class="error">' . $_SESSION['err_city'] . '</div>';
                        unset($_SESSION['err_city']);
                    } ?><br>
                <p>Ulica:</p> <input type="text" value="<?php 
                        if(isset($_SESSION['save_street'])){
                        echo $_SESSION['save_street'];
                        unset($_SESSION['save_street']);
                        } ?>" name="street"><br>
                    <?php
                    if(isset($_SESSION['err_street'])){
                        echo '<div class="error">' . $_SESSION['err_street'] . '</div>';
                        unset($_SESSION['err_street']);
                    } ?><br>
                <p>Numer mieszkania:</p> <input type="text" value="<?php 
                        if(isset($_SESSION['save_flat_num'])){
                        echo $_SESSION['save_flat_num'];
                        unset($_SESSION['save_flat_num']);
                        } ?>" name="flat_num"><br>
                    <?php
                    if(isset($_SESSION['err_flat_num'])){
                        echo '<div class="error">' . $_SESSION['err_flat_num'] . '</div>';
                        unset($_SESSION['err_flat_num']);
                    } ?><br>
            </div>
            <br/>
            <input type="submit" class="btn-update" value="Zmień dane">
          </form>
        </div>
    </section>
    </main>
    <footer>
        <div class="footer-top">
            <div class="footer-top__box">
                <h2>Informacje</h2>
                <a href="./about_us.php">
                    <p>O nas</p>
                </a>
                <a href="./contact.php">
                    <p>Kontakt</p>
                </a>
                <a href="./regulations.php">
                    <p>Regulamin</p>
                </a>
                <a href="./privacy_policy.php">
                    <p>Polityka Prywatności</p>
                </a>
            </div>
            <div class="footer-top__box footer-top__box--middle">
                <h2>Dostawa i płatność</h2>
                <a href="./shipping_cost.php">
                    <p>Koszty i metody dostawy</p>
                </a>
                <a href="./methods_of_payment.php">
                    <p>Formy płatności</p>
                </a>
                <a class="status-order-footer-btn" href="#status-order-top">
                    <p>Status zamówienia</p>
                </a>
            </div>
            <div class="footer-top__box">
                <h2>Moje konto</h2>
                <a href="./logging.php">
                    <p>Logowanie/ Rejestracja</p>
                </a>
                <a href="./cart.php">
                    <p>Koszyk</p>
                </a>
                <?php
                // Wyswietlanie linku ULUBIONE tylko zalogowanym:
                    if(isset($_SESSION['zalogowany'])){
                        echo "<a href='./favourites.php'>
                                <p>Ulubione</p></a>";
                    } ?>
            </div>
        </div>
        <div class="footer-bottom">
            <p>© 2024 | Vears</p>
        </div>
    </footer>

    <script src="./js/script.js"></script>
    <script src="./js/slider.js"></script>
</body>
</html>