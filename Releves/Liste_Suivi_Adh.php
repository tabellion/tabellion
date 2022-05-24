<?php

require_once __DIR__ . '/../Commun/config.php';
require_once __DIR__ . '/../Commun/constantes.php';
require_once __DIR__ . '/../Commun/Identification.php';
require_once __DIR__ . '/../Commun/VerificationDroits.php';
//verifie_privilege(DROIT_UTILITAIRES);
require_once __DIR__ . '/../Commun/ConnexionBD.php';
require_once __DIR__ . '/../Commun/PaginationTableau.php';
require_once __DIR__ . '/../Commun/commun.php';

print("<head>");
print('<meta http-equiv="Content-Type" content="text/html; charset=cp1252" />');
print('<meta http-equiv="content-language" content="fr" /> ');
print("<link href='../assets/css/styles.css' type='text/css' rel='stylesheet'/>");
print("<script src='../assets/js/jquery-min.js' type='text/javascript'></script>");
print("<script src='../assets/js/menu.js' type='text/javascript'></script>");
print('</head>');
print('<body>');

/**
 * Affiche la liste des suivis
 * @param object $sconnexionBD
 */
function menu_liste($sconnexionBD)
{
    global $gi_num_page_cour;

    $st_requete = "SELECT DISTINCT (left(ad.nom, 1)) AS init FROM `suivi_releve` s join `adherent` ad on (s.id_adherent = ad.idf ) ORDER BY init";
    $a_initiales_suivis = $sconnexionBD->sql_select($st_requete);
    print("<form   method=\"post\" onSubmit=\"return VerifieChamps(0)\">");
    print("<div align=center>");
    print("<div>");
    $i_session_initiale = isset($_SESSION['initiale']) ? $_SESSION['initiale'] : $a_initiales_suivis[0];
    $gc_initiale = empty($_GET['initiale']) ? $i_session_initiale : $_GET['initiale'];
    $_SESSION['initiale'] = $gc_initiale;
    foreach ($a_initiales_suivis as $c_initiale) {
        if ($c_initiale == $gc_initiale)
            print("<span style=\"font-style: bold;\">$c_initiale </span>");
        else
            print("<a href=" . basename(__FILE__) . "?initiale=$c_initiale>$c_initiale</a> ");
    }
    print("</div><br>");

    $st_requete = "select ad.nom, ca.nom, s.fourchette, co.libelle, s.annee_envoi, s.envoi_adherent, s.retour_adherent from `suivi_releve` s join `adherent` ad  on (s.id_adherent = ad.idf ) join `commune_acte` ca  on (s.id_commune = ca.idf) join `collection_acte` co on (s.id_collection = co.idf) where ad.nom like '$gc_initiale%' order by ad.nom, ca.nom, s.fourchette, co.libelle";
    $a_liste_suivis = $sconnexionBD->liste_valeur_par_clef($st_requete);
    if (count($a_liste_suivis) != 0) {
        $pagination = new PaginationTableau(basename(__FILE__), 'num_page', $sconnexionBD->nb_lignes(), NB_LIGNES_PAR_PAGE, DELTA_NAVIGATION, array('Releveur', 'Commune', 'Fourchette', 'Collection', 'Fourchette envoy�e', 'Date envoi', 'Retour envoi'));
        $pagination->init_param_bd($sconnexionBD, $st_requete);
        $pagination->init_page_cour($gi_num_page_cour);
        $pagination->affiche_entete_liens_navigation();
        print("<br>");
        $pagination->affiche_tableau_simple_requete_sql();
        print("<br>");
        $pagination->affiche_entete_liens_navigation();
    } else
        print("<div align=center>Pas de suivis</div>\n");
}

$connexionBD = ConnexionBD::singleton($gst_serveur_bd, $gst_utilisateur_bd, $gst_mdp_utilisateur_bd, $gst_nom_bd);
require_once __DIR__ . '/../Commun/menu.php';

$ga_communes    =    $connexionBD->liste_valeur_par_clef("select idf,nom from `commune_acte` order by nom");
$ga_collections =    $connexionBD->liste_valeur_par_clef("select idf,libelle from `collection_acte` order by libelle");
$ga_adherent     =   $connexionBD->liste_valeur_par_clef("select idf,concat(nom,'  ',prenom,' (',idf,')') from adherent order by nom,prenom");

print("<div CLASS=TITRE>Liste des suivis par releveur</div><br>");
menu_liste($connexionBD);
print('</body>');
