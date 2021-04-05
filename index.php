<?php

/*
 * Extraire les données de IMDB en PHP
 * Teva Keo
 * CREA DEV4 Avril 2021
 * Homepage
 */

 // Session init
 session_start();

 // Generate Session User List
 if( empty($_SESSION['myList'])):
    $_SESSION['myList'] = array();
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

</body>

<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>

</html>
