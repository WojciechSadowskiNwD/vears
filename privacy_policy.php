<?php
session_start();
require_once "connect.php";

// Wyszukiwanie zamOwienia;
try{
    $polaczenie = new mysqli($host, $db_user, $db_password, $db_name);
    if($polaczenie->connect_errno!=0){
        throw new Exception(mysqli_connect_errno());
    }
    else{
        if(isset($_POST['search-order'])){
            $validation_correct = true;
            
            //A. Sprawdzenie poprawności szukanego numeru zamowienia:
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

                $sql = "SELECT * FROM orders WHERE order_id='$search_order' AND order_phone='$search_phone'";

                $sql_B = "SELECT * FROM ordered_items WHERE order_id = '$search_order'";
                $result_B = mysqli_query($polaczenie, $sql_B);
                $ile_B = mysqli_num_rows($result_B);
                $summary = 0;

                for ($y = 1; $y <= $ile_B; $y++) {
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
    <title>Vears - polityka prywatności</title>
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
                            </tr>
                    </table></div>";

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
            <h1 class="section-text__title">POLITYKA PRYWATNOŚCI</h1>
            <p>
                W Vears.pl bardzo poważnie podchodzimy do kwestii zapewnienia klientom pełnego poszanowania ich
                prywatności oraz ochrony danych osobowych i wymogów RODO.
            </p>
            <p>
                Sformułowaliśmy Politykę prywatności, po to by wyjaśnić klientowi, na czym polega przetwarzanie przez
                nas danych osobowych każdej takiej osoby. Aby też wskazać podstawy prawne przetwarzania oraz przedstawić
                prawa osób, których dane dotyczą.
            </p>
            <h2>1. Administrator Danych</h2>
            <p>
                Administratorem Twoich danych osobowych, czyli podmiotem, który ustala cele i sposoby przetwarzania
                danych osobowych jest CraftWeb Adam Kowalski, z siedzibą w miejscowości Testowo 88-111 na ul. Sezamowej
                5, NIP: 123-456-78-90, Regon: 987654321.
            </p>
            <p>
                Administrator wyznaczył Inspektora Ochrony Danych, z którym kontakt możliwy jest pod adresem e mail:
                rodo@vears.pl. Pytania dotyczące przetwarzania danych osobowych przez Administratora możesz kierować
                również w formie pisemnej na adres korespondencyjny VEARS.
            </p>
            <h2>
                2. Czym są dane osobowe i na czym polega ich przetwarzanie?
            </h2>
            <p>
                Według definicji wskazanej w Rozporządzeniu Parlamentu Europejskiego i Radu UE 2016/679 z dnia 27
                kwietnia 2016r. w sprawie ochrony osób fizycznych w związku z przetwarzaniem danych osobowych i w
                sprawie swobodnego przepływu takich danych oraz uchylenia dyrektywy 95/4/WE (RODO), dane osobowe to
                wszelkie informacje pozwalające w sposób pośredni lub bezpośredni zidentyfikować osobę fizyczną. W
                zakres tego typu danych wchodzą m. in. dane umożliwiające identyfikację użytkownika (np. imię, nazwisko,
                adres zamieszkania, adres e - mail, numerem telefonu, zdjęcie), a także dane określające preferencje
                użytkownika (dotyczące np. rodzajów wybieranych produktów) oraz dane demograficzne i geolokalizacyjne
                (np. płeć, wiek, lokalizacja). Danymi osobowymi są również dane finansowe i transakcyjne (np. dane
                dotyczące płatności lub kart płatniczych, informacje dotyczące zamówień, zakupów, reklamacji i zwrotów)
                oraz informacje handlowe (np. informacje o potencjalnym zainteresowaniu ofertą). Przetwarzanie przez nas
                Twoich danych osobowych odbywa się w zależności od procesu przetwarzania. Są to m.in. następujące
                procesy:
            </p>
            <p>
                <strong>Rejestracja użytkownika w serwisie</strong> – w tym celu będziemy przetwarzać Twoje dane osobowe w ramach
                utworzenia konta, identyfikacji oraz nadania dostępu do poszczególnych funkcji i produktów. Podstawą
                prawną do przetwarzania tych danych jest art.6. pkt. 1. lit f. RODO. Dane niezbędne do realizacji tego
                procesu przetwarzać będziemy tak długo, jak będziesz posiadać status zarejestrowanego użytkownika (tzn.
                do momentu, aż zdecydujesz się wyrejestrować z naszego sklepu internetowego);
            </p>
            <p>
                <strong>Wykonanie umowy</strong> - w tym celu dane klienta przetwarzamy m.in. w ramach:
            </p>
            <ul>
                <li>
                    informowania o aktualizacjach zamówienia lub przekazywania informacji związanych z zamówionymi przez
                    klienta produktami,
                </li>
                <li>
                    zarządzania płatnościami za zakupione produkty,
                </li>
                <li>
                    zarządzania ewentualnymi wymianami lub zwrotami po dokonaniu zakupu,
                </li>
                <li>
                    zarządzania zapytaniami o dostępność produktów,
                </li>
                <li>
                    rezerwacjami produktów,
                </li>
                <li>
                    fakturowania i wystawiania paragonów za dokonane zakupy.
                </li>
            </ul>
            <p>
                Podstawą prawną do przetwarzania tych danych jest art.6. pkt. 1. lit b. RODO.
                Dane niezbędne do realizacji tego procesu przetwarzać będziemy przez czas niezbędny do zarządzania
                procesem zakupu, w tym do czasu zarządzania ewentualnymi zwrotami i reklamacjami;
            </p>
            <p>
                <strong>Podjęcia działań na wniosek klienta lub jego żądanie</strong> - w tym celu dane osoby przetwarzamy wyłącznie w
                zakresie niezbędnym do realizacji jego wniosków lub żądań. Klient ma możliwość kontaktować się z nami za
                pośrednictwem telefonu, wiadomości e-mail, korespondencyjne na adres siedziby firmy, a także wybierając
                komunikatory internetowe.
            </p>
            <p>
                Podstawą prawną do przetwarzania danych niezbędnych do podjęcia działań na klienta wniosek lub żądanie
                jest art.6. pkt. 1. lit f. RODO, art. 6 pkt.1 lit. b RODO, art. 6 pkt.1 lit.c RODO. Dane niezbędne do
                realizacji tego procesu przetwarzać będziemy przez czas niezbędny do obsługi działań na wniosek klienta
                lub żądanie;
            </p>
            <p><strong>Marketing</strong> – w tym celu dane przetwarzane są aby:</p>
            <ul>
                <li>zindywidualizować ofertę do potrzeb oraz zaproponować produkty w oparciu o preferencje klienta,</li>
                <li>zarządzać subskrypcjami i dostosować je do indywidualnych potrzeb i wyrażonych przez klienta zgód,
                </li>
                <li>wyświetlać klientowi reklamy internetowe naszych produktów PO przeglądaniu naszej witryny (reklamy
                    te mogą być wyświetlane zarówno na innych witrynach internetowych, jak również na mediach
                    społecznościowych, których odbiorca jest użytkownikiem), </li>
                <li>przeprowadzić działania promocyjne np. w ramach organizacji konkursów lub przypomnienia o produktach
                    zachowanych na profilu użytkownika, </li>
            </ul>
            <p>
                Podstawą prawną do przetwarzania tych danych art.6. pkt. 1. lit a. RODO. Dane niezbędne do realizacji
                celów marketingowych przetwarzać będziemy do momentu wycofania przez klienta zgody;
            </p>
            <p>
                <strong> Obrona przed ewentualnymi roszczeniami</strong> – w ramach tego procesu przetwarzać będziemy dane na podstawie
                art. 6 ust. 1 lit. f RODO, do momentu przedawnienia ewentualnych roszczeń.
            </p>
            <h2>3. Jakie prawa przysługują klientowi, którego dane dotyczą?</h2>
            <p>
                RODO daje osobom, których dane dotyczą szereg uprawnień, których realizację zapewniamy. Możesz wystąpić
                do nas o realizację swoich praw drogą mailową, wysyłając do nas wiadomość na adres: rodo@vears.pl,
                wskazując przyczynę swojego żądania oraz prawo, z którego chce klient skorzystać. Każdemu z klietów
                przysługują prawa do:
            </p>
            <ul>
                <li>prawo dostępu do treści swoich danych</li>
                <li>prawo sprostowania danych </li>
                <li>prawo do usunięcia danych</li>
                <li>prawo do ograniczenia przetwarzania</li>
                <li>prawo do cofnięcia zgody na przetwarzanie danych</li>
                <li>prawo do otrzymania kopii danych</li>
                <li>prawo do przeniesienia danych </li>
                <li>prawo wniesienia sprzeciwu wobec przetwarzania danych osobowych</li>
            </ul>
            <h2>4. Udostępnianie danych osobowych </h2>
            <p>Administrator udostępnia dane użytkowników serwisu www.vears.pl współpracującym usługodawcom, z którymi
                są podpisane umowy powierzenia. Wśród usługodawców, którym powierzane są dane występują m.in.
                hostingodawca poczty elektronicznej i hostingodawcy serwerów, firmy świadczące usługi pocztowe,
                kurierskie, transportowe i doręczeniowe, dostawcy usług technologicznych i analitycznych, dostawcy usług
                związanych z obsługą klienta, agencje marketingowe i inni dostawcy i partnerzy w świadczeniu usług
                związanych z marketingiem i reklamą. </p>
            <p>W celu jak największej skuteczności świadczonych usług, niektórzy z wymienionych usługodawców mają swoją
                siedzibę w krajach poza terenem Europejskiego Obszaru Gospodarczego. W związku z wystąpieniem możliwości
                przekazywania Twoich danych poza EOG wdrożyliśmy dodatkowe zabezpieczenia w postaci m.in. standardowych
                klauzul umownych i odpowiednich środków uzupełniających. Więcej informacji na temat stosowanych przez
                nas zabezpieczeń możesz uzyskać kontaktując się z nami. </p>
            <h2>5. Pliki cookies </h2>
            <p>
                Nasza strona internetowa korzysta z plików cookies. Są to niewielkie pliki tekstowe wysyłane przez
                serwer www i przechowywane przez oprogramowanie komputera przeglądarki. Kiedy przeglądarka ponownie
                połączy się ze stroną, witryna rozpoznaje rodzaj urządzenia, z którego łączy się użytkownik. Parametry
                pozwalają na odczytanie informacji w nich zawartych jedynie serwerowi, który je utworzył. Cookies
                ułatwiają więc korzystanie z wcześniej odwiedzonych witryn. Gromadzone informacje dotyczą adresu IP,
                typu wykorzystywanej przeglądarki, języka, rodzaju systemu operacyjnego, informacji o czasie i dacie,
                lokalizacji oraz informacji przesyłanych do witryny za pośrednictwem formularza kontaktowego. Zebrane
                dane służą nam do monitorowania i sprawdzenia, w jaki sposób użytkownicy korzystają z naszych witryn,
                aby usprawniać funkcjonowanie serwisu i zapewnić bardziej efektywną i bezproblemową nawigację.
                Monitorowania informacji o użytkownikach dokonujemy korzystając z narzędzia Google Analytics, które
                rejestruje zachowanie użytkownika na stronie. Stosujemy pliki cookies, aby zagwarantować najwyższy
                standard wygody naszego serwisu, a zebrane dane są wykorzystywane w celu optymalizacji działań. W każdej
                chwili możesz wyłączyć lub przywrócić opcję gromadzenia cookies poprzez zmianę ustawień w przeglądarce
                internetowej.</p>
            <h2>6. Zmiany Polityki Prywatności</h2>
            <p>
                Polityka Prywatności może ulegać zmianom wedle naszego uznania. W przypadku wprowadzenia przez nas zmian, informujemy o tym wszystkich użytkowników poprzez dostępne kanały komunikacji. 
            </p>
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