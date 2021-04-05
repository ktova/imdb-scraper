<?php

/*
 * Extraire les données de IMDB en PHP
 * Teva Keo
 * CREA DEV4 Avril 2021
 * Homepage
 */

// Hide Serveur Errors 
error_reporting(0);
ini_set('display_errors', 0);

// Call HTML DOM Parser
use voku\helper\HtmlDomParser;
require_once './vendor/autoload.php';

// Session init
session_start();

$current_url = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

// Generate Session User List
if( empty($_SESSION['myList'])):
$_SESSION['myList'] = array();
endif; 

// Search Query
if( strlen($_GET['filmq']) >= 3 && !empty($_GET['filmq']) ):
    
    // Instantiate DOM component
    $filtered_film = str_replace(' ', '+', $_GET['filmq']);
    try{
        $dom = HtmlDomParser::file_get_html('https://www.imdb.com/find?q=' . $filtered_film . '&s=tt&ref_=fn_al_tt_mr');
    }
    catch (exception $e) {
        die('Query is invalid !');
    }

    // Parsing variables
    
    /* Title ID */
    $href_from = 'title/tt';
    $href_to = '/?ref';

    /* Image url */
    $img_from = '<img src="';
    $img_to = '"></a>';

    /* Title String */
    $title_from = '<td class="result_text"> <a href="';
    $title_to = '/td>';

    /* Title Filtered */
    $filtered_from = '">';
    $filtered_to = '<';

endif;

function getStringBetween($str,$from,$to)
{
    $sub = substr($str, strpos($str,$from)+strlen($from),strlen($str));
    return substr($sub,0,strpos($sub,$to));
}

// If film is picked as favorite
if( isset($_POST['favori']) ):

    $index = count($_SESSION['myList']);

    $_SESSION['myList'][$index]['name'] = $_POST['film_name'];
    $_SESSION['myList'][$index]['id'] = $_POST['film_id'];
    $_SESSION['myList'][$index]['img'] = $_POST['film_img'];
    $_SESSION['myList'][$index]['index'] = $index;

endif;

// If favorite is deleted
if( isset($_POST['delete_favori']) ):

    unset($_SESSION['myList'][$_POST['delete_index']]);

endif;

?>

<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="robots" content="noindex">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
    <title>IMDB Scraper</title>
</head>

<body class="bg-dark">

<div id="app" class="container column justify-center bg-dark text-light mt-5">
    <a href="index" class="text-white text-decoration-none"><h1 class="text-center">IMDB Scraper</h1></a>

    <?php 
    if( !empty($_SESSION['myList']) ):
    ?>

    <h3>Vos Favoris</h3>
    <hr>

    <table class="table table-striped">
    <tbody>
        
        <?php 
        foreach( $_SESSION['myList'] as $list_film ):
        ?>

        <tr>
            <td><img src="<?= $list_film['img'] ?>"></td>
            <td><a href="film.php?id=<?php echo $list_film['id']; ?>"><?= $list_film['name'] ?></a></td>
            <td>
                <form method="post" action="<?= $current_url ?>">
                    <input type="hidden" name="delete_index" value="<?= $list_film['index'] ?>">
                    <input type="submit" name="delete_favori" class="btn btn-danger" value="Supprimer"/>
                </form>
            </td>
        </tr>

        <?php 
        endforeach;
        ?>

    </tbody>
    </table>

    <?php 
    endif;
    ?>

    <form action="search.php" class="flex text-center" method="get">
        <label for="filmInput" class="form-label">Entrer le nom d'un film</label>
        <input type="text" class="form-control" id="filmInput" name="filmq" aria-describedby="filmHelp">
        <div id="filmHelp" class="form-text">Les recherches imprécises sont limitées à 200 résultats !</div>
        <button type="submit" class="btn btn-warning text-uppercase">Chercher</button>
    </form>
</div>

<hr>

<?php 

// If Search Query is less than 3 characters or empty
if( strlen($_GET['filmq']) < 3 || empty($_GET['filmq']) ):
?>

<div class="text-center">
    <strong class="text-warning">
        La recherche effectuée est invalide, merci de recommencer !
    </strong>
</div>

<?php

// Display Query result
else:
?>

<table class="table table-striped">
    <tbody>
  
<?php

    // Loop throught Result List
    foreach( $dom->find('.findResult') as $film ):
        $film_title = getStringBetween($film->innerHtml, $title_from, $title_to);

?>
    <tr>
        <td><img src="<?= getStringBetween($film->innerHtml, $img_from, $img_to) ?>"></td>
        <td><a href="film.php?id=<?= getStringBetween($film->innerHtml, $href_from, $href_to) ?>"><?= getStringBetween($film_title, $filtered_from, $filtered_to) ?></a></td>
        <td>
        <form method="post" action="<?= $current_url ?>">
            <input type="hidden" name="film_name" value="<?= getStringBetween($film_title, $filtered_from, $filtered_to) ?>">
            <input type="hidden" name="film_id" value="<?= getStringBetween($film->innerHtml, $href_from, $href_to) ?>">
            <input type="hidden" name="film_img" value="<?= getStringBetween($film->innerHtml, $img_from, $img_to) ?>">
            <input type="submit" name="favori" class="btn btn-warning" value="Favori"/>
        </form>
        </td>
    </tr>

<?php
    
    endforeach;

endif;

?>

    </tbody>
</table>

</body>

<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>

</html>