<?php
session_start();
ini_set("display_errors", 0);
require_once 'connect.php';

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

    $this_product = $_GET['idproduct'];

    $sql = "SELECT * FROM products WHERE idproduct='$this_product'";
    $result_product = mysqli_query($polaczenie, $sql);

    $row = mysqli_fetch_assoc($result_product);
    $companyCode = $row['companyCode']; 
    $nameProduct = $row['nameProduct'];
    $newPrice = $row['newPrice'];
    $oldPrice = $row['oldPrice'];
    $descriptionProduct = $row['descriptionProduct'];
    $path = $row['path'];
    $pictureA = $row['pictureA'];
    $pictureB = $row['pictureB'];
    $pictureC = $row['pictureC'];
    $category = $row['category'];

    // koszyk:
    $koszyk=$_COOKIE["koszyk"];
    $id = $_GET["idItem"];
    $ile = $_GET["ile"];
    $size = $_POST['size'];
    if ($ile<0) unset($ile);

    function dodaj($koszyk, $id, $ile, $size){

    $zakupy = explode("|",$koszyk);
    for ($i=0;$i<count($zakupy)-1;$i++){
        $p = explode("#",$zakupy[$i]);
        if ($p[0]==$id) {
        if (isset($ile)) $p[1]=$ile;
        else $p[1]++;
        $jest=true;
        }
        if ($p[1]>0) $nowy .= "$p[0]#$p[1]";
    }
    if (!$jest) $nowy .= "$id#1|";
    return $nowy;
    }

    if ($id<>""){
    $koszyk = dodaj($koszyk, $id, $ile, $size);
    setcookie("koszyk", $koszyk, 0, "/");
    header("Location: index.php");
    exit;
    }

    if(isset($_POST['size'])){
        setcookie("size", $_POST['size']);
        setcookie("price", $newPrice);
    }

    if(isset($_SESSION['zalogowany'])){
        $now_id = $_SESSION['id'];
        
        if($_POST['add_fav']){
            $on = 1;
            $_SESSION['on'] = $on;
            
            if($polaczenie->query("INSERT INTO favourites VALUES (NULL, '$now_id','$this_product')"));
        }
        if($_POST['remove_fav']){
            $on = 0;
            $_SESSION['on'] = $on;
            
            if($polaczenie->query("DELETE FROM favourites WHERE favourite_product_id ='$this_product' AND user_id='$now_id'"));
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
    <title>Vears - opis produktu</title>
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
        <div class="container">  
            <div class="product-detail-left">
                <div class="product-detail-left__path">
                    <p><?php echo $path ?></p>
                </div>
                <div class="product-detail-left__pictures-box">
                    <div class="small-squares">
                        <figure>
                            <div class="small-square">
                                <?php 
                                echo "<img class='small-square__img first-img' src='$pictureA' alt='Pierwsza miniatura zdjęcia produktu'>"; ?>
                            </div>
                        </figure>
                        <figure>
                            <div class="small-square">
                                <?php 
                                echo "<img class='small-square__img second-img' src='$pictureB' alt='Druga miniatura zdjęcia produktu'>"; ?>
                            </div>
                        </figure>
                        <figure>
                            <div class="small-square">
                                <?php 
                                echo "<img class='small-square__img third-img' src='$pictureC' alt='Trzecia miniatura zdjęcia produktu'>"; ?>
                            </div>
                        </figure>
                    </div>
                    <div class="wrapper-bImg">
                        <figure class="bImg">
                            <div class="big-square first-img-big">
                                <?php 
                                echo "<a href='$pictureA' target='_blank'><img class='big-square__img' src='$pictureA' alt='Pierwsze duże zdjęcie produktu.'></a>"; ?>
                            </div>
                        </figure>
                        <figure class="bImg">
                            <div class="big-square second-img-big display-none">
                                <?php 
                                echo "<a href='$pictureB' target='_blank'><img class='big-square__img' src='$pictureB' alt='Drugie duże zdjęcie produktu.'></a>"; ?>
                            </div>
                        </figure>
                        <figure class="bImg">
                            <div class="big-square third-img-big display-none">
                                <?php 
                                echo "<a href='$pictureC' target='_blank'><img class='big-square__img' src='$pictureC' alt='Trzecie duże zdjęcie produktu.'></a>"; ?>
                            </div>
                        </figure>
                        <div class="small-squares-bottom">
                        <figure>
                            <div class="small-square">
                                <?php 
                                echo "<img class='small-square__img first-img' src='$pictureA' alt='Pierwsza miniatura zdjęcia produktu'>"; ?>
                            </div>
                        </figure>
                        <figure>
                            <div class="small-square">
                                <?php 
                                echo "<img class='small-square__img second-img' src='$pictureB' alt='Druga miniatura zdjęcia produktu'>"; ?>
                            </div>
                        </figure>
                        <figure>
                            <div class="small-square">
                                <?php 
                                echo "<img class='small-square__img third-img' src='$pictureC' alt='Trzecia miniatura zdjęcia produktu'>"; ?>
                            </div>
                        </figure>
                    </div>
                    </div>
                </div>
            </div>
            <div class="product-detail-right">
                <div class="product-detail-right__inside-box">
                    <div class="company-name">
                        <?php
                            echo $companyCode;
                        ?>
                    </div>
                    <div class="product-name">
                        <h1><?php echo $nameProduct ?></h1>
                    </div>
                    <div class="ratings-stars">
                        <p>
                            <i class="fa-solid fa-star fa-star-color"></i>
                            <i class="fa-solid fa-star fa-star-color"></i>
                            <i class="fa-solid fa-star fa-star-color"></i>
                            <i class="fa-solid fa-star fa-star-color"></i>
                            <i class="fa-solid fa-star"></i>
                            56 ocen produktu
                        </p>
                    </div>
                    <div class="colours-section">
                        <h2>Dostępne kolory</h2>
                        <div class="colours-section-box">
                            <figure>
                                <div class="colours-section-box__block">
                                    <img src="./img/categories/colours/color_black.jpg" alt="">
                                </div>
                            </figure>
                            <figure>
                                <div class="colours-section-box__block">
                                    <img src="./img/categories/colours/color_blue.jpg" alt="">
                                </div>
                            </figure>
                            <figure>
                                <div class="colours-section-box__block">
                                    <img src="./img/categories/colours/color_crimson.jpg" alt="">
                                </div>
                            </figure>
                            <figure>
                                <div class="colours-section-box__block">
                                    <img src="./img/categories/colours/color_lasure.jpg" alt="">
                                </div>
                            </figure>
                        </div>
                        <h2>Stan magazynowy</h2>
                        <p class="text-green">
                            <i class="fa-solid fa-check text-green"></i>
                            Dostępny:<span> dostawa 2 - 3 dni robocze</span>
                        </p>
                    </div>
                    <div class="price-box">
                        <p class="price"><?php echo $newPrice . " PLN" ?> <span class="old-price"><?php echo $oldPrice . " PLN" ?></span> </p>
                    </div>
                    <div class="selection-size-product">
                        <form method="post">
                            <?php    
                                if( $category == 'biżuteria bransoletki damskie' || $category == 'biżuteria bransoletki damskie bestsellery' || $category == 'biżuteria bransoletki damskie nowości' || $category == 'biżuteria  kolczyki damskie' || $category == 'biżuteria kolczyki damskie bestsellery' || $category == 'biżuteria kolczyki damskie nowości' || $category == 'biżuteria naszyjniki damskie' || $category == 'biżuteria naszyjniki damskie bestsellery' || $category == 'biżuteria naszyjniki damskie nowości' || $category == 'biżuteria pierścionki damskie' || $category == 'biżuteria pierścionki damskie bestsellery' || $category == 'biżuteria pierścionki damskie nowości'){
                                    
                                    if($size == '6'){
                                        echo "<input id=\"active-size\" type=\"submit\" name=\"size\" value=\"6\"> ";
                                    }else if(!($size == '6')){
                                        echo "<input class=\"size_s size_product\" type=\"submit\" name=\"size\" value=\"6\"> ";
                                    }
                                    if($size == '7'){
                                        echo "<input id=\"active-size\" type=\"submit\" name=\"size\" value=\"7\"> ";
                                    }else if(!($size == '7')){
                                        echo "<input type=\"submit\" name=\"size\" value=\"7\"> ";
                                    }
                                    if($size == '8'){
                                        echo "<input id=\"active-size\" type=\"submit\" name=\"size\" value=\"8\"> ";
                                    }else if(!($size == '8')){
                                        echo "<input type=\"submit\" name=\"size\" value=\"8\"> ";
                                    }
                                    if($size == '9'){
                                        echo "<input id=\"active-size\" type=\"submit\" name=\"size\" value=\"9\"> ";
                                    }else if(!($size == '9')){
                                        echo "<input type=\"submit\" name=\"size\" value=\"9\"> ";
                                    }
                                    if($size == '10'){
                                        echo "<input id=\"active-size\" type=\"submit\" name=\"size\" value=\"10\"> ";
                                    }else if(!($size == '10')){
                                        echo "<input type=\"submit\" name=\"size\" value=\"10\"> ";
                                    }

                                }else if( $category == 'obuwie botki damskie' || $category == 'obuwie botki damskie bestsellery' || $category == 'obuwie botki damskie nowości' || $category == 'obuwie kozaki damskie' || $category == 'obuwie kozaki damskie bestsellery' || $category == 'obuwie kozaki damskie nowości' || $category == 'obuwie sportowe damskie' || $category == 'obuwie sportowe damskie bestsellery' || $category == 'obuwie sportowe damskie nowości' || $category == 'obuwie szpilki damskie' || $category == 'obuwie szpilki damskie bestsellery' || $category == 'obuwie szpilki damskie nowości' || $category == 'obuwie kapcie damskie' || $category == 'obuwie kapcie damskie bestsellery' || $category == 'obuwie kapcie damskie nowości' || $category == 'obuwie mokasyny damskie' || $category == 'obuwie mokasyny damskie bestsellery' || $category == 'obuwie mokasyny damskie nowości' ){
                                    
                                    if($size == '36'){
                                        echo "<input id=\"active-size\" type=\"submit\" name=\"size\" value=\"36\"> ";
                                    }else if(!($size == '36')){
                                        echo "<input class=\"size_s size_product\" type=\"submit\" name=\"size\" value=\"36\"> ";
                                    }
                                    if($size == '37'){
                                        echo "<input id=\"active-size\" type=\"submit\" name=\"size\" value=\"37\"> ";
                                    }else if(!($size == '37')){
                                        echo "<input type=\"submit\" name=\"size\" value=\"37\"> ";
                                    }
                                    if($size == '38'){
                                        echo "<input id=\"active-size\" type=\"submit\" name=\"size\" value=\"38\"> ";
                                    }else if(!($size == '38')){
                                        echo "<input type=\"submit\" name=\"size\" value=\"38\"> ";
                                    }
                                    if($size == '39'){
                                        echo "<input id=\"active-size\" type=\"submit\" name=\"size\" value=\"39\"> ";
                                    }else if(!($size == '39')){
                                        echo "<input type=\"submit\" name=\"size\" value=\"39\"> ";
                                    }
                                    if($size == '40'){
                                        echo "<input id=\"active-size\" type=\"submit\" name=\"size\" value=\"40\"> ";
                                    }else if(!($size == '40')){
                                        echo "<input type=\"submit\" name=\"size\" value=\"40\"> ";
                                    }
                                }else if( $category == 'spodnie męskie' || $category == 'spodnie męskie nowości' || $category == 'spodnie męskie bestsellery' || $category == 'spodnie damskie' || $category == 'spodnie damskie bestsellery' || $category == 'spodnie damskie nowości'){
                                    if($size == 'XS'){
                                        echo "<input id=\"active-size\" type=\"submit\" name=\"size\" value=\"XS\"> ";
                                    }else if(!($size == 'XS')){
                                        echo "<input type=\"submit\" name=\"size\" value=\"XS\"> ";
                                    }
                                    if($size == 'S'){
                                        echo "<input id=\"active-size\" type=\"submit\" name=\"size\" value=\"S\"> ";
                                    }else if(!($size == 'S')){
                                        echo "<input class=\"size_s size_product\" type=\"submit\" name=\"size\" value=\"S\"> ";
                                    }
                                    if($size == 'M'){
                                        echo "<input id=\"active-size\" type=\"submit\" name=\"size\" value=\"M\"> ";
                                    }else if(!($size == 'M')){
                                        echo "<input type=\"submit\" name=\"size\" value=\"M\"> ";
                                    }
                                    if($size == 'L'){
                                        echo "<input id=\"active-size\" type=\"submit\" name=\"size\" value=\"L\"> ";
                                    }else if(!($size == 'L')){
                                        echo "<input type=\"submit\" name=\"size\" value=\"L\"> ";
                                    }
                                    if($size == 'XL'){
                                        echo "<input id=\"active-size\" type=\"submit\" name=\"size\" value=\"XL\"> ";
                                    }else if(!($size == 'XL')){
                                        echo "<input type=\"submit\" name=\"size\" value=\"XL\"> ";
                                    }
                                }else {
                                    if($size == 'S'){
                                        echo "<input id=\"active-size\" type=\"submit\" name=\"size\" value=\"S\"> ";
                                    }else if(!($size == 'S')){
                                        echo "<input class=\"size_s size_product\" type=\"submit\" name=\"size\" value=\"S\"> ";
                                    }
                                    if($size == 'M'){
                                        echo "<input id=\"active-size\" type=\"submit\" name=\"size\" value=\"M\"> ";
                                    }else if(!($size == 'M')){
                                        echo "<input type=\"submit\" name=\"size\" value=\"M\"> ";
                                    }
                                    if($size == 'L'){
                                        echo "<input id=\"active-size\" type=\"submit\" name=\"size\" value=\"L\"> ";
                                    }else if(!($size == 'L')){
                                        echo "<input type=\"submit\" name=\"size\" value=\"L\"> ";
                                    }
                                    if($size == 'XL'){
                                        echo "<input id=\"active-size\" type=\"submit\" name=\"size\" value=\"XL\"> ";
                                    }else if(!($size == 'XL')){
                                        echo "<input type=\"submit\" name=\"size\" value=\"XL\"> ";
                                    }
                                    if($size == 'XXL'){
                                        echo "<input id=\"active-size\" type=\"submit\" name=\"size\" value=\"XXL\"> ";
                                    }else if(!($size == 'XXL')){
                                        echo "<input type=\"submit\" name=\"size\" value=\"XXL\"> ";
                                    }
                                } ?>
                                </form>
                                <form method="post">
                                  <?php
                                    if(!isset($_SESSION['zalogowany'])){
                                        echo '<div class="box-gray"><i class="fa-solid fa-heart dd"></i>';
                                    }else if(isset($_SESSION['zalogowany'])){
                                        if($on == 0){
                                            echo '<div class="box-gray">
                                            <i class="fa-solid fa-heart dd"><input type="hidden" name="add_fav" value="1"/><input type="submit" class="btn-add-to-favourites" value="XX"></i>';
                                        }else if($on == 1){
                                            echo '<div class="box-gray box-gray--active"><i class="fa-solid fa-heart dd"><input type="hidden" name="remove_fav" value="1"/><input type="submit" class="btn-add-to-favourites" value="XX"></i>';
                                        }
                                    } ?>
                                    </div>
                                </form>
                    </div>
                    <div class="box-btn-cart">
                        <?php
                            if(isset($_POST['size'])){
                                echo "<a class=\"btn-add-to-cart\" href=\"./cart.php?idItem=$this_product\"><p>Dodaj do koszyka</p></a>";
                            }else{
                                echo '<a class="btn-add-to-cart" href="#"><p>Dodaj do koszyka</p></a>';
                                echo '<p class="cart-error">Wybierz rozmiar.<p>';
                            } ?>
                    </div>
                </div>
            </div>
        </div>
        <section class="benefits-bottom-bar">
            <div class="benefits-bottom-bar__box">
                <i class="fa-solid fa-truck-fast benefits-icons"></i>
                Darmowa dostawa od 299,99 PLN
            </div>
            <div class="benefits-bottom-bar__box">
                <i class="fa-solid fa-medal benefits-icons"></i>
                100% Gwarancja satysfakcji
            </div>
            <div class="benefits-bottom-bar__box">
                <i class="fa-solid fa-euro-sign benefits-icons"></i>
                Zyskasz dodatkowe rabaty
            </div>
        </section>
    <!-- SEKCJA ZOBACZ TAKZE na 992px -->
        <section class="view-sample view-sample--margin">
            <h2>Zobacz także</h2>
            <div class="section-products lower-margin">
            <div class="section-products__products">
                <?php 
                    for ($i = 1; $i <= 3; $i++){
                        if($this_product < 234){
                            $this_product = $this_product+1;
                        }else if($this_product >= 234){
                            $this_product-=10;
                        }
                    
                    $sql2 = "SELECT * FROM products WHERE idproduct=$this_product";
                    $result_product = mysqli_query($polaczenie, $sql2);
                    $ile = mysqli_num_rows($result_product);
            
                    $row = mysqli_fetch_assoc($result_product);
                    $idproduct2 = $row['idproduct'];
                    $companyCode2 = $row['companyCode'];
                    $nameProduct2 = $row['nameProduct'];
                    $newPrice2 = $row['newPrice'];
                    $oldPrice2 = $row['oldPrice'];
                    $descriptionProduct2 = $row['descriptionProduct'];
                    $path2 = $row['path'];
                    $pictureA2 = $row['pictureA'];
                    $pictureB2 = $row['pictureB'];
                    $pictureC2 = $row['pictureC'];
                
                    echo<<<END
                    <a href="./product_details.php?idproduct=$idproduct2">
                      <figure>
                        <div class="product-box">
                            <img class="product-box__img" src="$pictureA2" alt="fotografia produktu">
                            <div class="transparent-band">
                                <p class="transparent-band__product-name">$nameProduct2</p>
                                <p class="transparent-band__product-price">$newPrice2 PLN</p>
                            </div>
                        </div>
                      </figure>
                    </a>
                    END;
                } ?>
            </div>
            </div>
        </section>
    <!-- SEKCJA ZOBACZ TAKZE od 1200px -->
        <section class="view-sample view-sample--margin-big">
            <h2>Zobacz także</h2>
            <div class="section-products lower-margin">
                <div class="section-products__products">
                    <?php 
                        for ($i = 1; $i <= 4; $i++){
                            if($this_product < 234){
                                $this_product = $this_product+1;
                            }else if($this_product >= 234){
                                $this_product-=10;
                            }
                        
                        $sql2 = "SELECT * FROM products WHERE idproduct=$this_product";
                        $result_product = mysqli_query($polaczenie, $sql2);
                        $ile = mysqli_num_rows($result_product);
                
                        $row = mysqli_fetch_assoc($result_product);
                        $idproduct2 = $row['idproduct'];
                        $companyCode2 = $row['companyCode'];
                        $nameProduct2 = $row['nameProduct'];
                        $newPrice2 = $row['newPrice'];
                        $oldPrice2 = $row['oldPrice'];
                        $descriptionProduct2 = $row['descriptionProduct'];
                        $path2 = $row['path'];
                        $pictureA2 = $row['pictureA'];
                        $pictureB2 = $row['pictureB'];
                        $pictureC2 = $row['pictureC'];
                    
                        echo<<<END
                            <a href="./product_details.php?idproduct=$idproduct2">
                            <figure>
                            <div class="product-box">
                            <img class="product-box__img" src="$pictureA2" alt="fotografia produktu">
                            <div class="transparent-band">
                            <p class="transparent-band__product-name">$nameProduct2</p>
                            <p class="transparent-band__product-price">$newPrice2 PLN</p>
                            </div>
                            </div>
                            </figure>
                            </a>
                        END;
                    } ?>
                </div>
            </div>
        </section>
        <div class="content-bottom">
            <div class="left-content">
                <h2 class="section-title">Opis produktu</h2>
                <p>
                    <?php
                        echo $descriptionProduct;
                    ?>
                </p>   
            </div>
            <div class="right-details">
                <h2>Tabela rozmiarów</h2>
                <div class="specification-product-box">
                    <?php
                        if( $category == 'spodnie męskie' || $category == 'spodnie męskie nowości' || $category == 'spodnie męskie bestsellery' || $category == 'spodnie damskie' || $category == 'spodnie damskie bestsellery' || $category == 'spodnie damskie nowości'){
                            echo<<<END
                            <table class="table-of-pants">
                                <thead><tr>
                                    <th class="firsth-column">Rozmiar</th>
                                    <th>XS</th>
                                    <th>S</th>
                                    <th>M</th>
                                    <th>L</th>
                                    <th>XL</th>
                                </tr></thead>
                                <tbody><tr>
                                    <td class="firsth-column">Obw. w talii</td>
                                    <td>60 cm</td>
                                    <td>64 cm</td>
                                    <td>68 cm</td>
                                    <td>72 cm</td>
                                    <td>74 cm</td>
                                </tr><tr>
                                    <td class="firsth-column">Obw. w biodrach</td>
                                    <td>82 cm</td>
                                    <td>84 cm</td>
                                    <td>86 cm</td>
                                    <td>94 cm</td>
                                    <td>96 cm</td>
                                </tr><tr>
                                    <td class="firsth-column">Długość wewn. nogawki</td>
                                    <td>69 cm</td>
                                    <td>69 cm</td>
                                    <td>70 cm</td>
                                    <td>70 cm</td>
                                    <td>70 cm</td>
                                </tr></tbody>
                            </table>
                        END;
                        }
                        else if( $category == 'bluzy męskie' || $category == 'bluzy męskie nowości' || $category == 'kurtki męskie' || $category == 'kurtki męskie nowości' || $category == 't-shirty męskie' || $category == 't-shirty męskie nowości' || $category == 't-shirty męskie bestsellery' || $category == 'koszule męskie' || $category == 'koszule męskie bestsellery' || $category == 'bluzki damskie' || $category == 'bluzki damskie nowości' || $category == 'bluzki damskie bestsellery' || $category == 'kurtki damskie' || $category == 'kurtki damskie bestsellery' || $category == 'kurtki damskie nowości' || $category == 'bluzki damskie nowości' || $category == 'bluzki damskie bestsellery' || $category == 'koszulki damskie' || $category == 'koszulki damskie nowości' || $category == 'koszulki damskie bestsellery'){
                            echo<<<END
                            <table class="table-of-torsos">
                                <thead><tr>
                                    <th class="firsth-column">Rozmiar</th>
                                    <th>S</th>
                                    <th>M</th>
                                    <th>L</th>
                                    <th>XL</th>
                                    <th>XXL</th>
                                </tr></thead>
                                <tbody><tr>
                                    <td class="firsth-column">Obw. w kl. piersiowej</td>
                                    <td>102 cm</td>
                                    <td>112 cm</td>
                                    <td>122 cm</td>
                                    <td>130 cm</td>
                                    <td>138 cm</td>
                                </tr><tr>
                                    <td class="firsth-column">Długość</td>
                                    <td>67 cm</td>
                                    <td>70 cm</td>
                                    <td>73 cm</td>
                                    <td>76 cm</td>
                                    <td>79 cm</td>
                                </tr><tr>
                                    <td class="firsth-column">Długość rękawa</td>
                                    <td>59 cm</td>
                                    <td>60,5 cm</td>
                                    <td>62 cm</td>
                                    <td>63,5 cm</td>
                                    <td>65 cm</td>
                                </tr></tbody>
                            </table>
                            END;
                        }else if( $category == 'sukienki damskie' || $category == 'sukienki damskie nowości' || $category == 'sukienki damskie nowości bestsellery' || $category == 'sukienki damskie bestsellery' || $category == 'bielizna damska' || $category == 'bielizna damska bestsellery' || $category == 'bielizna damska nowości'){
                            echo<<<END
                            <table class="table-of-torsos">
                                <thead><tr>
                                    <th class="firsth-column">Rozmiar</th>
                                    <th>S</th>
                                    <th>M</th>
                                    <th>L</th>
                                    <th>XL</th>
                                    <th>XXL</th>
                                </tr></thead>
                                <tbody><tr>
                                    <td class="firsth-column">szerokość pod biustem</td>
                                    <td>88 cm</td>
                                    <td>92 cm</td>
                                    <td>98 cm</td>
                                    <td>104 cm</td>
                                    <td>110 cm</td>
                                </tr><tr>
                                    <td class="firsth-column">Szerokość talii</td>
                                    <td>72 cm</td>
                                    <td>76 cm</td>
                                    <td>82 cm</td>
                                    <td>88 cm</td>
                                    <td>94 cm</td>
                                </tr><tr>
                                    <td class="firsth-column">Długość</td>
                                    <td>114 cm</td>
                                    <td>116 cm</td>
                                    <td>118 cm</td>
                                    <td>120 cm</td>
                                    <td>122 cm</td>
                                </tr></tbody>
                            </table>
                            END;
                        }
                        else if( $category == 'spódniczki damskie' || $category == 'spódniczki damskie nowości' || $category == 'spódniczki damskie bestsellery' || $category == 'bielizna męskie'){
                            echo<<<END
                            <table class="table-of-torsos">
                                <thead><tr>
                                    <th class="firsth-column">Rozmiar</th>
                                    <th>S</th>
                                    <th>M</th>
                                    <th>L</th>
                                    <th>XL</th>
                                    <th>XXL</th>
                                </tr></thead>
                                <tbody><tr>
                                    <td class="firsth-column">Długość całkowita</td>
                                    <td>56 cm</td>
                                    <td>57 cm</td>
                                    <td>58 cm</td>
                                    <td>60 cm</td>
                                    <td>64 cm</td>
                                </tr><tr>
                                    <td class="firsth-column">Obwód talii</td>
                                    <td>64 cm</td>
                                    <td>70 cm</td>
                                    <td>74 cm</td>
                                    <td>78 cm</td>
                                    <td>84 cm</td>
                                </tr><tr>
                                    <td class="firsth-column">Obwód w biodrach</td>
                                    <td>80 cm</td>
                                    <td>84 cm</td>
                                    <td>88 cm</td>
                                    <td>92 cm</td>
                                    <td>96 cm</td>
                                </tr></tbody>
                            </table>
                            END;
                        }
                        else if( $category == 'obuwie botki damskie' || $category == 'obuwie botki damskie bestsellery' || $category == 'obuwie botki damskie nowości' || $category == 'obuwie kozaki damskie' || $category == 'obuwie kozaki damskie bestsellery' || $category == 'obuwie kozaki damskie nowości' || $category == 'obuwie sportowe damskie' || $category == 'obuwie sportowe damskie bestsellery' || $category == 'obuwie sportowe damskie nowości' || $category == 'obuwie szpilki damskie' || $category == 'obuwie szpilki damskie bestsellery' || $category == 'obuwie szpilki damskie nowości' || $category == 'obuwie kapcie damskie' || $category == 'obuwie kapcie damskie bestsellery' || $category == 'obuwie kapcie damskie nowości' || $category == 'obuwie mokasyny damskie' || $category == 'obuwie mokasyny damskie bestsellery' || $category == 'obuwie mokasyny damskie nowości' ){
                            echo<<<END
                            <table class="table-of-torsos of-boots">
                                <thead><tr>
                                    <th class="firsth-column">Rozmiar</th>
                                    <th>36</th>
                                    <th>37</th>
                                    <th>38</th>
                                    <th>39</th>
                                    <th>40</th>
                                </tr></thead>
                                <tbody><tr>
                                    <td class="firsth-column">Długość stopy</td>
                                    <td>23 cm</td>
                                    <td>23.5 cm</td>
                                    <td>24 cm</td>
                                    <td>25 cm</td>
                                    <td>25.5 cm</td>
                                </tr><tr>
                                    <td class="firsth-column">Długość wkładki</td>
                                    <td>23.5 cm</td>
                                    <td>24 cm</td>
                                    <td>24.5 cm</td>
                                    <td>25.5 cm</td>
                                    <td>26 cm</td>
                                </tr><tr>
                                    <td class="firsth-column">Szer. wkładki</td>
                                    <td>7.5 cm</td>
                                    <td>7.5 cm</td>
                                    <td>8 cm</td>
                                    <td>8 cm</td>
                                    <td>8.5 cm</td>
                                </tr><tr>
                                    <td class="firsth-column">Obwód cholewki</td>
                                    <td>28 cm</td>
                                    <td>28.5 cm</td>
                                    <td>29 cm</td>
                                    <td>29.5 cm</td>
                                    <td>30 cm</td>
                                </tr></tbody>
                            </table>
                            END;
                        }else{
                            echo<<<END
                            <table class="table-of-torsos">
                                <thead><tr>
                                    <th class="firsth-column">Rozmiar</th>
                                    <th>6</th>
                                    <th>7</th>
                                    <th>8</th>
                                    <th>9</th>
                                    <th>10</th>
                                </tr></thead>
                                <tbody><tr>
                                    <td class="firsth-column">Materiał</td>
                                    <td>Stal chirirg.</td>
                                    <td>Stal chirirg.</td>
                                    <td>Stal chirirg.</td>
                                    <td>Stal chirirg.</td>
                                    <td>Stal chirirg.</td>
                                </tr><tr>
                                    <td class="firsth-column">Powłoka</td>
                                    <td>Złoto</td>
                                    <td>Złoto</td>
                                    <td>Złoto</td>
                                    <td>Złoto</td>
                                    <td>Złoto</td>
                                </tr><tr>
                                    <td class="firsth-column">Gwarancja</td>
                                    <td>2 lata</td>
                                    <td>2 lata</td>
                                    <td>2 lata</td>
                                    <td>2 lata</td>
                                    <td>2 lata</td>
                                </tr></tbody>
                            </table>
                            END;
                        } ?>
                </div>
            </div>
            <div style="clear:both"></div>
        </div>
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