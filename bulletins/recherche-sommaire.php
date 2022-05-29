<?php
/*
Programme de recherche des �l�ments du sommaire des bulletins AGC
PL 06/13
*/

/*
CREATE TABLE IF NOT EXISTS `sommaire`
(
  `idf` smallint(5) unsigned NOT NULL auto_increment,
  `numero` smallint(3),        num�ro du bulletin
  `moisannee` varchar(30),     mois et ann�e du bulletin
  `rubrique` text,             rubrique du sommaire
  `auteur` varchar(50),        auteur de la rubrique correspondante
  `type` varchar(5),           art pour article, asc pour ascendance, fam pour famille, cou pour cousins, des pour descendance
  `flag` enum ('O', 'N'),      pour utilisation ult�rieure
   PRIMARY KEY (`idf`)
);
*/

require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../Origin/PaginationTableau.php';

$gi_num_page_cour = $_GET['num_page'] ?? 1;


/**
 * Affiche la liste des rubriques, noms articles, familles, ascendances, descendances et cousinage 
 */
function Affiche_noms($type, $sconnexionBD)
{
    global $gi_num_page_cour, $gst_mode;

    switch ($type) {
        case 'RUB':
            $numero = $_POST['rubrique'] ?? '';
            $titre = "Sommaire du numéro " . $numero;
            break;
        case 'ART':
            $auteur = $_POST['article'] ?? '';
            $titre = "Articles de " . $auteur;
            break;
        case 'FAM':
            $auteur = $_POST['famille'] ?? '';
            $titre = "Famille étudiée de " . $auteur;
            break;
        case 'ASC':
            $auteur = $_POST['ascendance'] ?? '';
            $titre = "Ascendance de " . $auteur;
            break;
        case 'DES':
            $auteur = $_POST['descendance'] ?? '';
            $titre = "Descendance de " . $auteur;
            break;
        case 'COU':
            $auteur = $_POST['cousinage'] ?? '';
            $titre = "Cousinage de " . $auteur;
            break;
    }
    if ($type == "RUB")
        $st_requete = "SELECT numero, moisannee, rubrique FROM `sommaire` WHERE numero = $numero";
    else
        $st_requete = "SELECT numero, moisannee, rubrique FROM `sommaire` WHERE auteur LIKE '%$auteur%' AND type = '$type'";

    print("<form method=\"post\">");
    $a_liste_sommaire = $sconnexionBD->sql_select_multiple($st_requete);
    print('<div class="panel panel-primary">');
    print("<div class=\"panel-heading\">$titre</div>");
    print('<div class="panel-body">');
    $i_nb_sommaires = count($a_liste_sommaire);
    if ($i_nb_sommaires != 0) {
        $pagination = new PaginationTableau(basename(__FILE__), 'num_page', $i_nb_sommaires, NB_LIGNES_PAR_PAGE, DELTA_NAVIGATION, array('Bulletin', 'Paru en', 'Sommaire'));
        $pagination->init_param_bd($sconnexionBD, $st_requete);
        $pagination->init_page_cour($gi_num_page_cour);
        $pagination->affiche_entete_liens_navigation();
        $pagination->affiche_tableau_simple_requete_sql();
        $pagination->affiche_entete_liens_navigation();
    }
    print("<button type=\"submit\" class=\"btn btn-primary col-md-4 col-md-offset-4\"><span class=\"glyphicon glyphicon-home\"></span>  Retour &agrave; la recherche</button>");
    print('<input type=hidden name=mode value="DEPART">');
    print('</form>');
    print('</div></div>');
}

/* --- Remplit un select des rubriques --- */
function Select_rubrique($connexionBD)
{
    $chaine_options = "";
    $st_requete = "SELECT DISTINCT numero FROM sommaire ORDER BY numero";
    $a_numeros = $connexionBD->sql_select($st_requete);
    foreach ($a_numeros as $i_numero) {
        $chaine_options .= "<option >$i_numero</option>\n";
    }
    return $chaine_options;
}

/* --- Remplit un select des noms --- */
function Select_nom($type, $connexionBD)
{
    $chaine_options = "";
    if ($type == "ART")
        $a_auteurs = $connexionBD->sql_select("SELECT DISTINCT auteur FROM sommaire WHERE type = '$type' ORDER BY upper(trim(auteur))");
    else        // FAM, ASC, DES, COU
        $a_auteurs = $connexionBD->sql_select("SELECT DISTINCT det_auteur FROM detail_nom WHERE det_type = '$type' ORDER BY det_auteur");
    foreach ($a_auteurs as $st_auteur) {
        $chaine_options .= "<option >$st_auteur</option>\n";
    }
    return $chaine_options;
}

/** 
 * Formulaire de recherche
 * - Les rubriques d'un numéro
 * - Chaque article d'un auteur
 * - Familles étudiées
 * - Ascendance d'un adhérent
 * - Descendance d'un adhérent
 * - Cousinage des adhérents
 */
function Saisie_recherche($connexionBD)
{
    print('<div class="panel panel-primary">');
    print('<div class="panel-heading">Recherche sur le sommaire des bulletins</div>');
    print('<div class="panel-body">');
    print("<div id='sommaire'>");
    print("<form method=\"post\">");
    print('<div class="form-group row">');
    print('<div class="col-md-4">');
    print('<button class="btn btn-primary" type=submit id="rub_recherche" name="valide_rub"><span class="glyphicon glyphicon-search"></span> Recherche</button>');
    print('</div>');
    print('<label for="rub" class="col-form-label col-md-4">Les rubriques d\'un numéro</label>');
    print('<div class="col-md-4">');
    print('<select id="rub" name="rubrique" class="form-control">' . Select_rubrique($connexionBD) . '</select>');
    print("<input type=hidden name=mode value=\"RUBRIQUE\">");
    print('</div>');
    print('</div>');
    print("</form>");
    print("<form method=\"post\">");
    print('<div class="form-group row">');
    print('<div class="col-md-4">');
    print('<button class="btn btn-primary"  type=submit name="valide_art"><span class="glyphicon glyphicon-search"></span> Recherche</button>');
    print('</div>');
    print('<label for="art" class="col-form-label col-md-4">Chaque article d\'un auteur</label>');
    print('<div class="col-md-4">');
    print('<select id="art" name=article class="form-control">' . Select_nom('ART', $connexionBD) . '</select>');
    print("<input type=hidden name=mode value=\"ARTICLE\">");
    print('</div>');
    print('</div>');
    print("</form>");
    print("<form method=\"post\">");
    print('<div class="form-group row">');
    print('<div class="col-md-4">');
    print('<button class="btn btn-primary" type=submit  name="valide_fam"><span class="glyphicon glyphicon-search"></span> Recherche</button>');
    print('</div>');
    print('<label for="fam" class="col-form-label col-md-4">Familles étudiée</label>');
    print('<div class="col-md-4">');
    print('<select id="fam" name=famille class="form-control">' . Select_nom('FAM', $connexionBD) . '</select>');
    print("<input type=hidden name=mode value=\"FAMILLE\">");
    print('</div>');
    print('</div>');
    print("</form>");
    print("<form method=\"post\">");
    print('<div class="form-group row">');
    print('<div class="col-md-4">');
    print('<button class="btn btn-primary" type=submit name="valide_asc"><span class="glyphicon glyphicon-search"></span>  Recherche</button>');
    print('</div>');
    print('<label for="asc" class="col-form-label col-md-4">Ascendance d\'un adhérent</label>');
    print('<div class="col-md-4">');
    print('<select id="asc" name=ascendance class="form-control">' . Select_nom('ASC', $connexionBD) . '</select>');
    print("<input type=hidden name=mode value=\"ASCEND\">");
    print('</div>');
    print('</div>');
    print("</form>");
    print("<form method=\"post\">");
    print('<div class="form-group row">');
    print('<div class="col-md-4">');
    print('<button class="btn btn-primary" type=submit name="valide_des"><span class="glyphicon glyphicon-search"></span>  Recherche</button>');
    print('</div>');
    print('<label for="des" class="col-form-label col-md-4">Descendance d\'un adhérent</label>');
    print('<div class="col-md-4">');
    print('<select id="des" name=descendance class="form-control">' . Select_nom('DES', $connexionBD) . '</select>');
    print("<input type=hidden name=mode value=\"DESCEND\">");
    print('</div>');
    print('</div>');
    print("</form>");
    print("<form  method=\"post\">");
    print('<div class="form-group row">');
    print('<div class="col-md-4">');
    print('<button class="btn btn-primary" type=submit name="valide_cou"><span class="glyphicon glyphicon-search"></span> Recherche</button>');
    print('</div>');
    print('<label for="cou" class="col-form-label col-md-4">Cousinage des adhérents </label>');
    print('<div class="col-md-4">');
    print('<select id="cou" name=cousinage class="form-control">' . Select_nom('COU', $connexionBD) . '</select>');
    print("<input type=hidden name=mode value=\"COUSIN\">");
    print('</div>');
    print('</div>');

    print('</form>');
    print("</div>");
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <link rel="shortcut icon" href="assets/img/favicon.ico">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="content-language" content="fr">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='../assets/css/styles.css' type='text/css' rel='stylesheet'>
    <link href='../assets/css/bootstrap.min.css' rel='stylesheet'>
    <script src='../assets/js/jquery-min.js' type='text/javascript'></script>
    <script src='../assets/js/bootstrap.min.js' type='text/javascript'></script>
    <title>Recherche du sommaire des bulletins</title>
</head>

<body>
    <div class="container">

        <?php require_once __DIR__ . '/../commun/menu.php';

        $gst_mode = $_POST['mode'] ?? 'DEPART';

        switch ($gst_mode) {
            case 'DEPART':
                Saisie_recherche($connexionBD);
                break;
            case 'RUBRIQUE':
                Affiche_noms('RUB', $connexionBD);
                break;
            case 'ARTICLE':
                Affiche_noms('ART', $connexionBD);
                break;
            case 'FAMILLE':
                Affiche_noms('FAM', $connexionBD);
                break;
            case 'ASCEND':
                Affiche_noms('ASC', $connexionBD);
                break;
            case 'DESCEND':
                Affiche_noms('DES', $connexionBD);
                break;
            case 'COUSIN':
                Affiche_noms('COU', $connexionBD);
                break;
        } ?>

    </div>
</body>

</html>