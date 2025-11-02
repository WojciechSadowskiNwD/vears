<?php
session_start();
require_once "connect.php";

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
            
            // Jezeli wszystkie powyzsze testy zostaly zaloczone, realizujemy docelowe zapytanie:
            if($validation_correct == true){
                $sql = "SELECT * FROM orders WHERE order_id='$search_order' AND order_phone='$search_phone'";

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
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vears - regulamin sklepu</title>
    <meta name="description"
        content="Pasek nawigacji, przyklejony do górnej krawędzi okna przeglądarki w trakcie scrollowania myszką.">
    <script src="https://kit.fontawesome.com/0f35c72659.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="./CSS/style.css">
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
                $result = mysqli_query($polaczenie, $sql);
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
        <section class="section-text">
            <h1 class="section-text__title">REGULAMIN SKLEPU</h1>
            <h2>§ 1 PREAMBUŁA</h2>
            <p>Regulamin określa prawa i obowiązki użytkowników sklepu internetowego Vears.pl oraz zasady jego funkcjonowania.</p>
            <h2>§ 2 POSTANOWIENIA OGÓLNE</h2>
            <p>Właścicielem i administratorem sklepu internetowego www.vears.pl jest firma CraftWeb Spółka z ograniczoną
            odpowiedzialnością, ul. Sezamowa 5, 88-111 Testowo, wpisana do Krajowego Rejestru Sądowego pod numerem
            KRS:1234567890, NIP: 123-456-78-90, Regon: 987654321 (dane przykładowe na potrzeby realizowanego projektu
            dyplomowego zaliczeniowego).</p>
            <p>Grafiki umieszczone w treściach witryny pochodzą z wielu znalezionych w internecie serwisów i są jedynietreścią poglądową na potrzeby prezentacji projektu, nigdy nie będą wykorzystane komercyjnie ani w żadnyminnym celu. Dzieje się tak dla tego iż student nie jest w stanie poniesć tak dużych inwestycji tylko abyzaprezentować efekt swoich umiejętności w odniesieniu pracy zaliczeniowej.</p>
            <p>Treść regulaminu powstała w odzworowaniu do znalezionych w internecie (przykładowy taki regulamin: https://varlesca.pl/strony/regulamin-sklepu). Jej jedynym zdaniem jest prezentacja wizualna układu, rozmiesczenia elementów, umiejętność wypozycjonowania tekstu, dobrania odpowiedniej
            czcionki, zastosowania pasujących styli css tak aby całość prezentowała się zachęcająco dla potencjalnego odbiorcy.</p>
            <h2>§ 3 WARUNKI ZAWIERANIA UMOWY SPRZEDAŻY</h2>
            <p>1. Klient może złożyć zamówienie po zarejestrowaniu poprzez swoje konto, jak również nie posiadając konta. W obu przypadkach do złożenia zamówienia niezbędne jest wypełnienie formularza zamówienia. </p>
            <p>2. Podane na stronie ceny są cenami w polskich złotych i zawierają obowiązujący podatek VAT.</p>
            <p>3. W podsumowaniu zamówienia widnieje informacja o łącznej cenie przedmiotu zamówienia oraz o kosztach dostawy. O wszelkich kosztach Klient jest informowany w trakcie wypełniania formularza zamówienia. W procesie zamówienia nie ma prawa pojawić się żaden ukryty koszt, o czym zapewniamy.</p>
            <p>4. Przed złożeniem zamówienia Klient powinien zapoznać się z treścią regulaminu i go zaakceptować. Akceptacja regulaminu jest dobrowolna, lecz jednocześnie konieczna do poprawnego złożenia zamówienia na zakup towarów.
            </p>
            <p>5. Zaakceptowanie wypełnionego formularza zamówienia poprzez kliknięcie hasła „Zamawiam i płacę” jest jednoznaczne z zawarciem przez Kupującego umowy sprzedaży.</p>
            <p>6. Po dokonaniu zamówienia Sprzedawca niezwłocznie poinformuje Klienta o przyjęciu zamówienia do realizacji. Potwierdzenie nastąpi poprzez przesłanie Klientowi wiadomości e-mail na adres poczty elektronicznej który
            został wpisany przez Klienta do formularza podczas składania zamówienia.</p>
            <p>7. Zamówienie zostaje zapisane w systemie sklepu internetowego jako dowód zawarcia umowy.</p>
            <h2>§ 4 WARUNKI I TERMIN PŁATNOŚCI</h2>
            <p>1. Klient ma do dyspozycji następujące formy płatności: <br>
            Płatność za pomocą systemu szybkich płatności poprzez zwykły przelew lub BLIK. Płatność za pobraniem ( kurier Inpost). <br>
            2. Po złożeniu zamówienia i nieopłaceniu go w czasie 30 min poprzez udostępnione przez nas formy płatności umowa sprzedaży zostaje rozwiązana z winy Klienta, a zamówienie automatycznie anulowane. <br>
            3. Darmowa dostawa obowiązuje dla wszystkich zamówień krajowych powyżej 299 zł.</p>
            <h2>§ 5 WARUNKI I TERMIN DOSTAWY ZAMÓWIENIA</h2>
            <p>1. W przypadku wyboru płatności przelewem lub kartą płatniczą zamówienie zostanie przekazane do realizacji w momencie zaksięgowania wpłaty na rachunek bankowy Sprzedawcy.</p>
            <p>2. Termin realizacji zamówień to 1-4 dni roboczych.</p>
            <p>3. Dostawa produktu dostępna jest wyłącznie na terytorium Rzeczypospolitej Polskiej według cennika firm kurierskich.</p>
            <p>4. Dostawa Produktu do Klienta jest odpłatna, chyba że Umowa Sprzedaży stanowi inaczej. Koszty dostawy produktu są jawne i zawarte są w zakładce „Koszty dostawy” oraz w trakcie składania Zamówienia.</p>
            <p>5. Nie ma możliwości odbioru osobistego zakupów dokonywanych w sklepie internetowym.</p>
            <p>6. Czas transportu produktu do Klienta na terenie kraju wynosi 1-2 dni robocze (zgodnie z umową z firmą dostarczającą produkt).</p>
            <p>7. Klient ma obowiązek sprawdzenia przesyłki w obecności kuriera. W przypadku zauważenia uszkodzenia przesyłki niezbędne jest spisanie protokołu szkody w transporcie oraz niezwłoczne powiadomienie o tym
            fakcie sprzedawcy poprzez wysłanie wiadomości e-mail. Nie spisanie protokołu szkody z kurierem może uniemożliwić wszczęcie procedury reklamacyjnej, a tym samym zwrotu towaru.</p>
            <p>8. Podany termin wysyłki Pre Orderu jest datą orientacyjną i może ulec zmianie.</p>
            <h2> § 6 REKLAMACJE</h2>
            <p>1. Pełen zakres odpowiedzialności i obowiązków Sprzedawcy względem Klienta jest określony powszechnie obowiązującymi przepisami prawa, w szczególności w Kodeksie Cywilnym. Dla umów sprzedaży, podstawa i zakres
            odpowiedzialności Sprzedawcy wobec Klienta z tytułu niezgodności produktu z umową sprzedaży są określone powszechnie obowiązującymi przepisami prawa sprzedaży konsumenckiej.</p>
            <p>2. Sprzedawca odpowiada na zasadzie rękojmi. Podstawa prawna: art. 558 § 1 oraz 560 Kodeksu cywilnego.</p>
            <p>3. Sprzedawca ustosunkuje się do reklamacji w możliwie najkrótszym terminie, nie później niż w 14 dni kalendarzowych od dnia jej złożenia. Podstawą do wszczęcia procedury reklamacji jest prawidłowo wypełniony
            formularz reklamacyjny dostępny na stronie www.vears.pl oraz dowód zakupu. W przypadku nie dołączenia niezbędnych dokumentów reklamacja nie zostanie rozpatrzona.</p>
            <p>4.W sytuacji wystąpienia wady konsument może złożyć do sprzedawcy reklamację z tytułu rękojmi i zażądać jednego z czterech działań:
            -wymiany towaru na nowy;
            -naprawy towaru;
            -obniżenia ceny;
            -odstąpienia od umowy – o ile wada jest istotna.
            Wybór żądania zależy od konsumenta. Jeżeli przedsiębiorca nie zgadza się z tym wyborem, może pod pewnymi warunkami zaproponować inne rozwiązanie, ale musi się to odbywać w ramach przesłanek dozwolonych prawem. Pod uwagę mogą być brane następujące okoliczności:
            -łatwość i szybkość wymiany lub naprawy towaru;
            -charakter wady – istotna czy nieistotna;
            -to, czy towar był wcześniej reklamowany.</p>
            <p>5. Klient-Konsument zostaję objęty przepisami zawartymi w Kodeksie cywilnym w zakresie rękojmi za wady.</p>
            <h2>§ 7 ZWROTY, ODSTĄPIENIE OD UMOWY</h2>
            <p>1. Oświadczenie o odstąpieniu od umowy Klient może przesłać pocztą elektroniczną na adres kontakt@vears.pl bądź pismem na adres ul. Sezamowa 5 88-100 Testowo.</p>
            <p>2. Klient będący konsumentem oraz klient-konsument prowadzący działalność gospodarczą, który zawarł umowę na odległość, może w terminie 14 dni kalendarzowych odstąpić od niej bez podawania przyczyny i bez
            ponoszenia dodatkowych kosztów, z wyjątkiem kosztów transportu do Sprzedawcy.</p>
            <p>3. Podstawą do wszczęcia procedury zwrotu jest prawidłowo wypisany formularz odstąpienia od umowy, według ogólnego wzoru dostępnego powszechnie w internecie, oraz dowód zakupu. W przypadku nie dołączenia
             niezbędnych dokumentów zwrot nie będzie rozpatrywany.</p>
            <p>4. Sprzedawca ma obowiązek dostarczyć Klientowi produkt wolny od wad.
            </p><p>5. W przypadku zauważonych braków, wad w przesyłce lub niezgodności z zamówieniem, prosimy o niezwłoczny kontakt mailowy z naszym Biurem Obsługi Klienta na adres kontakt@vears.pl.</p>
            <p>6. Sprzedawca po weryfikacji zgłoszenia zleca odbiór wadliwego produktu na swój koszt. Zlecenie odbioru jest wykonywane tylko i wyłącznie na wadliwy produkt. W przypadku dołączenia innych produktów do zwrotu Klient zostanie obciążony kwotą 14,99 zł, na poczet kosztów transportu, które w
             przypadku zwrotu pokrywa Kupujący.</p>
            <p>7. Sprzedawca zamieszcza poniżej dodatkowe informacje dotyczące prawa odstąpienia od umowy w przypadku Produktów – strojów kąpielowych z kolekcji ABC TEST:</p>
            <p>7.1. Klient przed skorzystaniem z prawa odstąpienia od umowy ma możliwość przymierzenia stroju kąpielowego. Klient nie ma prawa noszenia stroju kąpielowego, prania, czy używania go w inny sposób. Przymierzenie
            stroju kąpielowego nie wymaga zrywania metek, etykiet czy zdejmowania plomb higienicznych.</p>
            <p>7.2. Sprzedawca przyjmie zwrot stroju kąpielowego zapakowanego tak, jak został otrzymany, wraz ze wszystkimi metkami i etykietami, wkładkami oraz plombami higienicznymi.</p>
            <p>7.3. Ustawa o Prawach Konsumenta przewiduje, że prawo odstąpienia od umowy zawartej poza lokalem przedsiębiorstwa lub na odległość nie przysługuje konsumentowi m.in. w odniesieniu do umów, w której
            przedmiotem świadczenia jest rzecz dostarczana w zapieczętowanym opakowaniu, której po otwarciu opakowania nie można zwrócić ze względu na ochronę zdrowia lub ze względów higienicznych, jeżeli opakowanie zostało
            otwarte po dostarczeniu. W odniesieniu do strojów kąpielowych posiadających plombę higieniczną, z zastrzeżeniem indywidualnej oceny okoliczności faktycznych, wyjątek określony powyżej mógłby mieć
            zastosowanie, gdy strój kąpielowy zostanie zwrócony bez plomby higienicznej.</p><p> 8. Nie przyjmujemy zwrotów wysyłanych za pobraniem, wysłanych do paczkomatu, bądź punktu odbioru.</p><p>
            9. Klient Konsument prowadzący jednoosobową działalność gospodarczą, będzie miał możliwość skorzystania z prawa odstąpienia od umowy jako Konsument, wyłącznie w przypadku, gdy nie będzie to dotyczyć bezpośrednio
            branży, w której specjalizuje się przedsiębiorca, a sam zakup nie ma charakteru zawodowego.</p><p>10. W przypadku odstąpienia od umowy Konsument i Klient-Konsument odpowiada materialnie za zmniejszenie wartości towarów wynikające z obchodzenia się z nimi w sposób inny niż jest to konieczne do oceny ich właściwości, cech i funkcjonalności. Sytuacja ta dotyczy w szczególności zwrotu bielizny.</p><p>
            11. Sprzedawca ma obowiązek niezwłocznie, nie później niż w terminie 14 dni kalendarzowych od dnia otrzymania zwrotu produktu oraz oświadczenia Klienta o odstąpieniu od umowy, zwrócić Klientowi wszystkie dokonane przez niego płatności, w tym koszty dostawy produktu (z wyjątkiem dodatkowych kosztów wynikających z wybranego przez Klienta sposobu dostawy innego niż najtańszy zwykły sposób dostawy). Sprzedawca dokonuje zwrotu płatności za towar, w ten sam sposób jaki zostało opłacone zamówienie, zgodnie z poniższym opisem:
            a. Przy płatności poprzez Platformę Przelewy24 na konto, z którego dokonano płatności za zamówienie.
            b. Przy płatności za pobraniem na nr konta podany w formularzu.</p>
            <h2>§ 8 DANE OSOBOWE</h2>
            <p>1. Sprzedawca przetwarza dane osobowe Klienta wyłącznie w celu realizacji zamówienia.</p><p>2. Sprzedawca może przetwarzać dane osobowe Klienta również w innym celu niż realizacja zamówienia po uprzednim uzyskaniu jego zgody.</p><p>3. Dane osobowe użytkowników serwisu są chronione, zgodnie z Ustawą z dnia 29.08.1997 r. o ochronie danych
            osobowych, w najlepszy możliwy sposób, uniemożliwiający dostęp do nich przez osoby trzecie. Użytkownik ma prawo wglądu do swoich danych osobowych, ich modyfikowania oraz usunięcia.</p><p> 4. Sprzedawca wykorzystuje pliki cookies (ciasteczka), czyli niewielkie informacje tekstowe, przechowywane na urządzeniu końcowym Klienta (np. komputerze, tablecie, smartfonie). Cookies mogą być odczytywane przez system teleinformatyczny Sprzedawcy.</p><p>5. Sprzedawca przechowuje pliki cookies na urządzeniu końcowym Klienta, a następnie uzyskuje dostęp do
            informacji w nich zawartych w celach statystycznych, w celach marketingowych (remartketing) oraz w celu zapewnienia prawidłowego działania sklepu internetowego.</p><p>6. Sprzedawca niniejszym informuje Klienta, że istnieje możliwość takiej konfiguracji przeglądarki internetowej, która uniemożliwia przechowywanie plików cookies na urządzeniu końcowym Klienta. W takiej sytuacji, korzystanie ze sklepu internetowego przez Klienta może być utrudnione.</p><p>
            7. Sprzedawca niniejszym wskazuje, że pliki cookies mogą być przez Klienta usunięte po ich zapisaniu przez Sprzedawcę, poprzez odpowiednie funkcje przeglądarki internetowej, programy służące w tym celu lub skorzystanie z odpowiednich narzędzi dostępnych w ramach systemu operacyjnego, z którego korzysta Klient.</p>
            <h2>§ 9 POSTANOWIENIA KOŃCOWE</h2>
            <p>1. W sprawach nieuregulowanych w niniejszym regulaminie mają zastosowanie powszechnie obowiązujące przepisy prawa polskiego oraz inne właściwe przepisy powszechnie obowiązującego prawa.</p><p>2. Aktualny Regulamin sklepu Internetowego jest publikowany na stronie www.vears.pl oraz na każde żądanie Klienta może zostać przesłany drogą elektroniczną na wskazany w formularzy rejestracyjnym adres e-mail.</p><p>
            3. Sprzedawca zastrzega sobie możliwość zmiany Regulaminu. Do umów zawartych przed zmianą stosuje się wersję Regulaminu obowiązującą w dacie zawarcia umowy sprzedaży. W razie dodatkowych pytań, skontaktuj się z nami:
            kontakt@vears.pl , tel: +48 777 888 999.</p>
            <p>(podane powyżej w treści dane mają charakter wyłącznie testowy, są w zupełności przypadkowe. Nie stanowią realnych danych do kontaktu, a ten sklep internetowy nie służy celom komercyjnym. Jego przeznaczenie jest
            wyłącznie po to aby móc zaprezentować umiejętności programowania webowego studenta w odniesieniu do tematyki pracy dyplomowej).</p>
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
</body>
</html>