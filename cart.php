<?php
session_start();

ini_set("display_errors", 0);
require_once "connect.php";
$polaczenie2 = mysqli_connect($host, $db_user, $db_password, $db_name);
mysqli_query($polaczenie2, "SET CHARSET utf8");
mysqli_query($polaczenie2, "SET NAMES 'utf8' COLLATE 'utf8_polish_ci'");
mysqli_select_db($polaczenie2, $db_name);

try{
    $polaczenie = new mysqli($host, $db_user, $db_password, $db_name);
    mysqli_query($polaczenie, "SET CHARSET utf8");
    mysqli_query($polaczenie, "SET NAMES 'utf8' COLLATE 'utf8_polish_ci'");
    
    if($polaczenie->connect_errno!=0){
        throw new Exception(mysqli_connect_errno());
    }
    else{

        // Wyszukiwanie zamowienia:    
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
                $result_A = $polaczenie->query("SELECT order_id FROM orders WHERE order_id='$search_order'");
    
                if(!$result_A) throw new Exception($polaczenie->error);
                $ile_takich_wynikow = $result_A->num_rows;
                if($ile_takich_wynikow == 0){
                    $validation_correct = false;
                    $_SESSION['err_search_order']="Nie znaleziono zamównienia o takim numerze.";
                }
    
                //D. Czy taki nr telefonu istnieje w bazie:
                $result_A = $polaczenie->query("SELECT order_phone FROM orders WHERE order_phone='$search_phone'");

                if(!$result_A) throw new Exception($polaczenie->error);
                $ile_takich_wynikow = $result_A->num_rows;
                
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

$id = $_GET["idItem"];
$newPrice = $_COOKIE['price'];
$size = $_COOKIE['size'];
$koszyk=$_COOKIE["koszyk"];

$ile = $_GET["ile"];
if ($ile>= 2) ($ile--);

function dodaj($koszyk, $id, $ile, $size, $newPrice){
  $zakupy = explode("|", $koszyk);
  for ($i=0; $i < count($zakupy)-1; $i++) {
    $p = explode("#", $zakupy[$i]);
    if ($p[0]==$id) {
      if (isset($ile)) $p[1]=$ile;
      else $p[1]++;
      $jest = true;
    }
    if ($p[1]>0) $nowy .= "$p[0]#$p[1]#$p[2]#$p[3]|";
  }
  if (!$jest) $nowy .= "$id#1#$size#$newPrice|";
  return $nowy;
}

if ($id<>""){
  $koszyk = dodaj($koszyk, $id, $ile, $size, $newPrice);
  setcookie("koszyk", $koszyk, 0, "/");
  header("Location: cart.php"); 
  exit;
}

if(isset($_SESSION['clearCart'])){

    if($_SESSION['clearCart'] == 1){
        $ile = 1;
        $koszyk = skasuj_koszyk($koszyk, $id, $ile, $size, $newPrice);
        setcookie("koszyk", $koszyk, 0, "/");
        unset($koszyk);
        unset($_COOKIE['koszyk']);
    }
    $_SESSION['clearCart'] = 0;
}

function skasuj_koszyk($koszyk, $id, $ile, $size, $newPrice){
    $zakupy = explode("|", $koszyk);
    for ($i=0; $i < count($zakupy)-1; $i++) {
      $p = explode("#", $zakupy[$i]);
      if ($p[0]==$id) {
        if (isset($ile)) $p[1]=$ile;
        else $p[1]++;
        $jest = true;
      }
      if ($p[1]>0) $nowy .= "";
    }
    if (!$jest) $nowy .= "";
    return $nowy;
  }
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vears - koszyk etap I</title>
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
            <p class="p-0">Nie możesz znaleźć informacji, związanych z zakupami w naszym e-sklepie? Zadzwoń do nas a chętnie pomożemy. </p>
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
                    <?php
                        if(isset($_SESSION['blad']))  echo $_SESSION['blad']; ?>
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
            <h1 class="section-text__title lower-margin2">Zawartość Koszyka</h1>
        </section>
        <section class="delivery-free">
                <i class="fa-solid fa-circle-info"></i>
                Od kwoty koszyka 299zł dostawa jest darmowa !
        </section>
        <section class="empty-cart">
            <?php
            if (!$koszyk) {
                echo '<p><strong>Twój koszyk jest pusty.</strong></p>';
            } ?>
        </section>
        <section class="section-cart">
            <div class="section-cart__left-box">
                <?php
                    $_SESSION['total'] = 0;
                    // Rozbicie koszyka na osobne ciągi, po jednym dla każego produktu:
                    $zakupy = explode("|", $koszyk); 
                    // teraz dopiero rozbijam z kazdego takiego ciągu na osobne pozycje te stanowia detale kazdego z produktow:
                    for ($i=0; $i<count($zakupy)-1; $i++){
                        $p = explode("#", $zakupy[$i]);

                        $sql_cart = "SELECT * FROM products WHERE idproduct='$p[0]'";
                        $rezultat = mysqli_query($polaczenie2, $sql_cart);

                        $row_cart = mysqli_fetch_assoc($rezultat);

                        $companyCode = $row_cart['companyCode']; 
                        $nameProduct = $row_cart['nameProduct'];
                        $newPrice = $row_cart['newPrice'];
                        $pictureA = $row_cart['pictureA'];

                        $sum_cart = $newPrice * $p[1];
                        $_SESSION['total'] = $_SESSION['total'] + $sum_cart;

                        echo<<<END
                        <div class="product-block">
                            <div class="product-block__photo-box">
                                <a href="./product_details.php?idproduct=$p[0]" target="_blank">
                                    <figure>
                                        <img class="photo-box-img" src="$pictureA" alt="fotografia produktu">
                                    </figure>
                                </a>
                            </div>
                            <div class="product-block__info-box">
                                <div class="company-name">$companyCode</div>
                                <div class="product-name">
                                    <h1>$nameProduct</h1>
                                </div>
                                <div class="product-size">Rozmiar: $p[2];
                                </div>
                                <div class="product-price">Cena: $newPrice ZŁ
                                </div>
                                <div class="product-quality-title">
                                    <p>szt. produktu:</p>
                                </div>
                                <div class="product-quantity">
                                    <div class="product-quantity--minus">
                                        <form action="cart.php" method="get">    
                                            <input type="hidden" name="idItem" value="$p[0]" />
                                            <input type="hidden" name="ile" value="$p[1]" style="width:50px;" />
END;
echo<<<END
                                            <input type="submit" class="subtract" value="-" style="80px;" />    
                                        </form>
                                    </div>
                                    <div class="product-quantity--num">
                                        <form action="cart.php" method="get">
                                            <input type="hidden" name="idItem" value="$p[0]" />
                                            <input type="text" class="input-how-many" value="$p[1]" name="ile" maxlength="1">
                                        </form>
                                    </div>
                                    <div class="product-quantity--plus">
                                        <form action="cart.php" method="get">    
                                            <input type="hidden" name="idItem" value="$p[0]" />
                                            <input type="hidden" name="add" value="$p[1]" style="width:50px;" />
                                            <input type="submit" value="+" style="80px;" />    
                                        </form>
                                    </div>
                                </div>
                                <div class="product-price-sum">
                                END;
                echo "SUMA: " . number_format($sum_cart,2) . " ZŁ";
                echo<<<END
                </div>
            </div>
            <div class="product-block__buttons">
                <form action="cart.php" method="get">
                    <input type="hidden" name="idItem" value="$p[0]"/>
                    <input type="hidden" name="ile" value="0"/>
                    <i class="fa-solid fa-trash-can">
                        <input class="btn-delete-product" value="XX" type="submit" />
                    </i>
                </form>
            </div>
            </div>
            END;
        }
        $total = $_SESSION['total'];
        $delivery = 15.99;
        ?>
            </div>
            </div>
            <?php 
                if(isset($koszyk)){
                    echo<<<END
                    <div class="section-cart__right-box">
                    <div class="summary-title">
                        <h1>Całkowita wartość koszyka</h1>
                    </div>
                    <div class="summary-table">
                        <div class="row">
                            <div class="row__left-cell">
                                <p>Wartość zamówienia:</p>
                            </div>
                            <div class="row__right-cell">
END;
                                echo $total . " ZŁ";
                                echo<<<END
                            </div>
                        </div>
                        <div class="row">
                            <div class="row__left-cell row__left-cell--up">
                                <p>Dostawa:</p>
                            </div>
                            <div class="row__right-cell row__right-cell--up">
END;
                                    if($total >= 299){
                                        $delivery = 0;
                                    }else
                                        $delivery = 15.99;    
                                echo $delivery . " ZŁ";
                                echo<<<END
                            </div>
                        </div>
                        <div class="row">
                            <div class="row__left-cell row__left-cell--up">
                            <strong>SUMA:</strong>
                            </div>
                            <div class="row__right-cell row__right-cell--up">
END;
                                    $final_summary = $total + $delivery;
                                    $_SESSION['final_summary'] = $final_summary;
                                    $_SESSION['total'] = $total;
                                    $_SESSION['delivery'] = $delivery;
                                    echo "<strong>" . $final_summary . " ZŁ</strong>
                            </div>
                        </div>";
                        echo<<<END
                        <div class="summary-table__box-btn">
                            <a href="cart_step2.php">    
                                <div class="btn-delivery-payment">
                                    <p>Dostawa i płatność</p>
                                </div>
                            </a>
                        </div>
                        </div>
                        </div>
END;
                        } ?>
        </section>
<!-- Podsumowanie koszyka na rozdzielczosci 992px - uklad bottom -->
        <section class="cart-bottom">
            <?php 
                if(isset($koszyk)){
                    echo<<<END
                    <div class="section-cart__right-box section-cart__right-box--small">
                    <div class="summary-title">
                        <h1>Całkowita wartość koszyka</h1>
                    </div>
                    <div class="summary-table">
                        <div class="row">
                            <div class="row__left-cell">
                                <p>Wartość zamówienia:</p>
                            </div>
                            <div class="row__right-cell">
END;
                                echo $total . " ZŁ";
                                echo<<<END
                            </div>
                        </div>
                        <div class="row">
                            <div class="row__left-cell row__left-cell--up">
                                <p>Dostawa:</p>
                            </div>
                            <div class="row__right-cell row__right-cell--up">
END;
                                    if($total >= 299){
                                        $delivery = 0;
                                    }else
                                        $delivery = 15.99;       
                                echo $delivery . " ZŁ";
                                echo<<<END
                            </div>
                        </div>
                        <div class="row">
                            <div class="row__left-cell row__left-cell--up">
                            <strong>SUMA:</strong>
                            </div>
                            <div class="row__right-cell row__right-cell--up">
END;
                                    $final_summary = $total + $delivery;
                                    $_SESSION['final_summary'] = $final_summary;
                                    $_SESSION['total'] = $total;
                                    $_SESSION['delivery'] = $delivery;
                                    echo "<strong>" . $final_summary . " ZŁ</strong>
                            </div>
                        </div>";
                        echo<<<END
                        <div class="summary-table__box-btn">
                            <a href="cart_step2.php">    
                                <div class="btn-delivery-payment">
                                    <p>Dostawa i płatność</p>
                                </div>
                            </a>
                        </div>
                        </div>
                        </div>
END;
                        } ?>
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