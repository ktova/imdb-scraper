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

function scraping_imdb($url)
{
    // init
    $return = [];

    // create HTML DOM
    $dom = \voku\helper\HtmlDomParser::file_get_html($url);

    // get title
    $title_node = $dom->find('title', 0)->innertext;
    $return['Title'] = str_replace('- IMDb','',$title_node);

    // get cover
    $film_cover = $dom->find('.poster')->innerHtml;
    $sub = substr($film_cover[0], strpos($film_cover[0],'src="')+strlen('src="'),strlen($film_cover[0]));
    $return['Cover'] = substr($sub,0,strpos($sub,'">'));

    // get year
    $film_year = $dom->find('#titleYear')->innertext;
    $sub_year = substr($film_year[0], strpos($film_year[0],'>')+strlen('>'),strlen($film_year[0]));
    $result_year = substr($sub_year,0,strpos($sub_year,'<'));
    if( empty($result_year) || $result_year == 0 ):
        $return['Year'] = 'Série';
    else:
        $return['Year'] = $result_year;
    endif;

    // get rating
    $return['Rating'] = $dom->find('.ratingValue strong', 0)->getAttribute('title');

    // get stars
    $stars_node = $dom->find('.credit_summary_item');
    $stars_count = 0;
    foreach( $stars_node->find('a') as $star ):
        if( $stars_count !== 4 ):
            $return['Stars'][$stars_count] = $star->innerHtml;
        endif;
        $stars_count++;
    endforeach;

    return $return;
}

// Get film data
if( isset($_GET['id']) ):
    try{
        $data = scraping_imdb('http://imdb.com/title/tt'. $_GET['id'] .'/');
    }
    catch (exception $e) {
        die('Film ID is invalid !');
    }
endif;

if(isset($_POST['submitted'])):

    // Force Download
    header('Content-Description: File Transfer');
    header('Content-Type: application/csv');
    header("Content-Disposition: attachment; filename=".$data['Title'].".csv");
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');

    $handle = fopen('php://output', 'w');
    ob_clean();

    foreach( $data as $value ):
        fputcsv($handle, $value);
    endforeach;   

    ob_flush();
    fclose($handle);
    die();

endif;

?>

<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="robots" content="noindex">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
    <title>IMDB Scraper</title>
</head>

<body class="bg-dark d-flex justify-content-center flex-column align-items-center">

<div id="app" class="container column justify-center bg-dark text-light mt-5 mb-10">
    <a href="index" class="text-white text-decoration-none"><h1 class="text-center">IMDB Scraper</h1></a>
    <form action="search.php" class="flex text-center" method="get">
        <label for="filmInput" class="form-label">Entrer le nom d'un film</label>
        <input type="text" class="form-control" id="filmInput" name="filmq" aria-describedby="filmHelp">
        <div id="filmHelp" class="form-text">Les recherches imprécises sont limitées à 200 résultats !</div>
        <button type="submit" class="btn btn-warning text-uppercase">Chercher</button>
    </form>
</div>

<?php
// If Search Query is less than 3 characters or empty
if( empty($_GET['id']) ):
?>

<div class="text-center">
    <strong class="text-warning">
        La recherche effectuée est invalide, merci de recommencer !
    </strong>
</div>

<?php
else:
?>

<hr>

<div class="card w-75 align-items-center">
  <img src="<?= $data['Cover'] ?>" class="card-img-top" style="max-width:200px" alt="Film_Image">
  <div class="card-body text-center">
    <h3 class="card-title"><?= $data['Title'] ?></h3>
    <strong class="card-text mb-5"><?= $data['Year'] ?></strong>
    <p class="card-text"><?= $data['Rating'] ?></p>
    <ul>
    <?php 
    foreach($data['Stars'] as $data_star):
        echo '<li>'.$data_star.'</li>';
    endforeach;
    ?>
    </ul>
    <form method="post" action="<?= $current_url ?>">
        <input type="submit" name="submitted" class="btn btn-primary" value="Download Infos"/>
    </form>
    <br>
  </div>
</div>

<?php    
endif;
?>

</body>

<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>

</html>