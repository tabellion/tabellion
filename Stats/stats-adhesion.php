<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------

require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../Commun/VerificationDroits.php';
verifie_privilege(DROIT_STATS);
require_once __DIR__ . '/../Origin/PaginationTableau.php';
require_once __DIR__ . '/../Commun/commun.php';

$gst_mode = empty($_POST['mode']) ? 'DEPART' : $_POST['mode'];
$gi_num_page_cour = empty($_GET['num_page']) ? 1 : $_GET['num_page'];

function Mois_Annee()  // Function pour affichage du mois en français
{
    $mois = array('', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre');
    $mois_numero = date("n");
    $mois_complet = $mois[$mois_numero];
    $jour = date("d");
    $annee = date("Y");
    return $jour . " " . $mois_complet . " " . $annee;
}


/* --- Cumule et affiche les résultats --- */
function Affiche_Stats()
{
    global $connexionBD;
    $annee = (int) $_POST['annee'];
    $ga_nb_cartes = array();
    $ga_nb_cheques = array();
    $ga_tarifs = array(15, 33, 43);
    foreach ($ga_tarifs as $i_tarif) {
        $ga_nb_cartes[$i_tarif] = 0;
        $ga_nb_cheques[$i_tarif] = 0;
    }

    $st_requete = "select prix, jeton_paiement, concat(prenom,' ',nom,' (',idf,')') from adherent where annee_cotisation = $annee and prix != 0";
    $date_jour = Mois_Annee();
    print('<div class="panel panel-primary">');
    print("<div class=\"panel-heading\">Statistiques du nombre et montant des adh&eacute;sions pour l'ann&eacute;e $annee au $date_jour</div>");
    print('<div class="panel-body">');
    print("<form  method=\"post\">");
    $a_adhesions = $connexionBD->sql_select_multiple($st_requete);
    foreach ($a_adhesions as $a_adh) {
        list($i_prix, $st_jeton, $st_adherent) = $a_adh;
        if (in_array($i_prix, $ga_tarifs)) {
            $st_jeton = trim($st_jeton);
            if (empty($st_jeton))
                $ga_nb_cheques[$i_prix]++;
            else
                $ga_nb_cartes[$i_prix]++;
        } else
            print("<div class=\"alert alert-danger\">Tarif $i_prix inexistant pour l'adh&eacute;rent $st_adherent</div>");
    }

    print("<table class=\"table table-bordered table-striped\">");
    print("<tr><th>&nbsp;</th>");
    foreach ($ga_tarifs as $i_tarif) {
        print("<th>Adh&eacute;sion $i_tarif euros</th>");
    }
    print("<th>&nbsp;</th></tr>");
    print("<tr>");
    print("<td> Paiement par ch&egrave;que </td>");
    $gi_nb_tot_cheques = 0;
    $gi_tot_cheques = 0;
    foreach ($ga_nb_cheques as $i_tarif => $i_nb_cheques) {
        $i_montant = $i_nb_cheques * $i_tarif;
        print(sprintf("<td>%d adh&eacute;sions pour %d euros </td>", $i_nb_cheques, $i_montant));
        $gi_nb_tot_cheques += $i_nb_cheques;
        $gi_tot_cheques += $i_montant;
    }
    print(sprintf("<td>Soit %d adh&eacute;sions pour %d euros </td>", $gi_nb_tot_cheques, $gi_tot_cheques));
    print("</tr>");
    print("<tr>");
    print("<td> Paiement par Internet </td>");
    $gi_nb_tot_cartes = 0;
    $gi_tot_cartes = 0;
    foreach ($ga_nb_cartes as $i_tarif => $i_nb_cartes) {
        $i_montant = $i_nb_cartes * $i_tarif;
        print(sprintf("<td>%d adh&eacute;sions pour %d euros </td>", $i_nb_cartes, $i_montant));
        $gi_nb_tot_cartes += $i_nb_cartes;
        $gi_tot_cartes += $i_montant;
    }
    print(sprintf("<td>Soit %d adh&eacute;sions pour %d euros </td>", $gi_nb_tot_cartes, $gi_tot_cartes));

    print("<tr>");
    print("<td> Soit au total </td>");
    $gi_nb_tot_adhesions = 0;
    $gi_tot_adhesions = 0;
    foreach ($ga_tarifs as $i_tarif) {
        $i_montant = $ga_nb_cheques[$i_tarif] * $i_tarif + $ga_nb_cartes[$i_tarif] * $i_tarif;
        print(sprintf("<td>%d adh&eacute;sions pour %d euros </td>", $ga_nb_cheques[$i_tarif] + $ga_nb_cartes[$i_tarif], $i_montant));
        $gi_nb_tot_adhesions += $ga_nb_cheques[$i_tarif] + $ga_nb_cartes[$i_tarif];
        $gi_tot_adhesions += $i_montant;
    }
    print(sprintf("<td>Soit %d adh&eacute;sions pour %d euros </td>", $gi_nb_tot_adhesions, $gi_tot_adhesions));
    print("</tr>");
    print("</table>");
    print('<div class="form-group col-md-4 col-md-offset-4"><button type="submit" class="btn btn-primary"><span class="glyphicon glyphicon-home"></span> Retour</button></div>');
    print("<input type=hidden name=mode value=\"DEPART\">");
    print("</form></div></div>");
}

/* --- Saisie de l'année à afficher --- */

function Saisie_annee()
{
    global $connexionBD;
    $a_annees = $connexionBD->sql_select($st_requete = "SELECT DISTINCT (`annee_cotisation`) FROM `adherent` ORDER BY `annee_cotisation`");

    print('<div class="panel panel-primary">');
    print('<div class="panel-heading">Statistiques du nombre et montant des adh&eacute;sions</div>');
    print('<div class="panel-body">');
    print("<form   method=\"post\">");
    print('<div class="form-row col-md-12">');
    print("<label for=\"annee\" class=\"col-form-label col-md-2\">Ann&eacute;e</label>");
    print('<div class="col-md-8">');
    print('<div class="input-group"><select name=annee id=anneee class="form-control">');
    print chaine_select_options_simple('', $a_annees);
    print("</select>");
    print('<span class="input-group-btn"><button type="submit" class="btn btn-primary"><span class="glyphicon glyphicon-stats"></span> Valider</button>');
    print('</span></div></div></div>');
    print("<input type=hidden name=mode value=\"AFFICHE\">");
    print("</form>");
    print("</div></div>");
}

?>

<!DOCTYPE html>

<head>
    <title>Statistiques des adhésions</title>
    <meta charset="iso-8859-15"> <!-- ou charset="utf-8" -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="assets/img/favicon.ico">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="content-language" content="fr">
    <link href='../assets/css/styles.css' type='text/css' rel='stylesheet'>
    <link href='../assets/css/bootstrap.min.css' rel='stylesheet'>
    <script src='../assets/js/jquery-min.js' type='text/javascript'></script>
    <script src='../assets/js/bootstrap.min.js' type='text/javascript'></script>

</head>

<body>
    <div class="container">

        <?php

        require_once __DIR__ . '/../Commun/menu.php';

        switch ($gst_mode) {
            case 'DEPART':
                Saisie_annee();
                break;
            case 'AFFICHE':
                Affiche_Stats($connexionBD);
                break;
        }

        //=====================================================================================
        // requête SQL qui compte le nombre  d'adhérent par année

        $st_requete = "SELECT a.annee_cotisation,count(*),sum(case when jeton_paiement !='' then 1 else 0 end)  FROM `adherent` a where a.statut in ('B','I') group by a.annee_cotisation order by a.annee_cotisation desc";
        print('<div class="panel-group">');
        print('<div class="panel panel-info">');
        print('<div class="panel-heading">Nbrs Adh(B+I) ann&eacute;e de cotisation</div>');
        print('<div class="panel-body">');

        $pagination = new PaginationTableau(basename(__FILE__), 'stats_adhesions', 3, NB_LIGNES_PAR_PAGE, DELTA_NAVIGATION, array('Ann&eacute;e', 'Nbrs', 'Adh&eacute;sions<br> en ligne'));
        $pagination->init_param_bd($connexionBD, $st_requete);
        $pagination->init_page_cour($gi_num_page_cour);
        $pagination->affiche_tableau_simple_requete_sql();
        print('</div></div>');

        //=================================================================================== 
        // début du tableau Adhésion par mois
        // requête SQL Comptage des demandes par mois
        $st_requete = "SELECT YEAR(date_paiement)as annee, MONTH(date_paiement)as mois, COUNT(*)as nombre FROM adherent WHERE `statut`IN ('B','I') GROUP BY YEAR(date_paiement) desc,MONTH(date_paiement) desc ";

        print('<div class="panel panel-info">');
        print('<div class="panel-heading">Adh&eacute;sion par mois</div>');
        print('<div class="panel-body">');
        $pagination = new PaginationTableau(basename(__FILE__), 'stats_adhesions', 3, NB_LIGNES_PAR_PAGE, DELTA_NAVIGATION, array('Ann&eacute;e', 'Mois', 'Nbrs'));
        $pagination->init_param_bd($connexionBD, $st_requete);
        $pagination->init_page_cour($gi_num_page_cour);
        $pagination->affiche_tableau_simple_requete_sql();
        print('</div></div>');

        //====================================================================================== 
        // Nbrs Adh par statut et année de cotisation

        $st_requete = 'SELECT a.annee_cotisation,sa.nom,count(*) FROM `adherent` a  join `statut_adherent` sa on (sa.idf=a.statut) group by a.annee_cotisation,a.statut order by a.annee_cotisation desc,a.statut';

        print('<div class="panel panel-info">');
        print('<div class="panel-heading">Nbrs Adh par statut et ann&eacute;e de cotisation</div>');
        print('<div class="panel-body">');
        $pagination = new PaginationTableau(basename(__FILE__), 'stats_adhesions', 3, NB_LIGNES_PAR_PAGE, DELTA_NAVIGATION, array('Ann&eacute;e', 'Statut', 'Nbrs'));
        $pagination->init_param_bd($connexionBD, $st_requete);
        $pagination->init_page_cour($gi_num_page_cour);
        $pagination->affiche_tableau_simple_requete_sql();
        print('</div></div>');

        //Répartition des Adhérents  

        // Combien en France
        $st_requete = ("SELECT COUNT( * ) as Nbrs , left( cp, 2 ) as Departement FROM `adherent` WHERE `pays` LIKE 'france' AND `statut` IN ('B', 'I')GROUP BY left( cp, 2 )");
        print('<div class="pane panel-info">');
        print('<div class="panel-heading">Adh&eacute;rents par d&eacute;partement</div>');
        print('<div class="panel-body">');
        $pagination = new PaginationTableau(basename(__FILE__), 'stats_adhesions', 3, NB_LIGNES_PAR_PAGE, DELTA_NAVIGATION, array('Nbrs', 'D&eacute;p'));
        $pagination->init_param_bd($connexionBD, $st_requete);
        $pagination->init_page_cour($gi_num_page_cour);
        $pagination->affiche_tableau_simple_requete_sql();
        print('</div></div>');

        // Combien hors de France
        $st_requete = ("SELECT COUNT( * ) as Nbrs ,pays FROM `adherent` WHERE `pays` NOT LIKE 'france' AND `statut` IN ('B', 'I') GROUP BY pays order by pays");
        print('<div class="panel panel-info">');
        print('<div class="panel-heading">Adh&eacute;rents hors de France</div>');
        print('<div class="panel-body">');
        $pagination = new PaginationTableau(basename(__FILE__), 'stats_adhesions', 3, NB_LIGNES_PAR_PAGE, DELTA_NAVIGATION, array('Nbrs', 'Pays'));
        $pagination->init_param_bd($connexionBD, $st_requete);
        $pagination->init_page_cour($gi_num_page_cour);
        $pagination->affiche_tableau_simple_requete_sql();
        print('</div></div></div>');

        ?>
    </div>
</body>

</html>