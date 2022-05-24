<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------
require_once __DIR__ . '/../Commun/Identification.php';
require_once __DIR__ . '/../Commun/commun.php';
require_once __DIR__ . '/../Commun/constantes.php';
require_once __DIR__ . '/../Commun/VerificationDroits.php';
verifie_privilege(DROIT_STATS);
require_once __DIR__ . '/../Commun/ConnexionBD.php';
require_once __DIR__ . '/../Commun/PaginationTableau.php';

$connexionBD = ConnexionBD::singleton($gst_serveur_bd, $gst_utilisateur_bd, $gst_mdp_utilisateur_bd, $gst_nom_bd);

$i_session_idf_adherent =  isset($_SESSION['idf_adherent']) ? $_SESSION['idf_adherent'] : null;
$gi_idf_adherent =   isset($_GET['idf_adherent']) ? (int) $_GET['idf_adherent'] : $i_session_idf_adherent;
$_SESSION['idf_adherent'] = $gi_idf_adherent;

$st_session_mode =  isset($_SESSION['mode']) ? $_SESSION['mode'] : 'FORMULAIRE';
$gst_mode =  isset($_REQUEST['mode']) ? $_REQUEST['mode'] : $st_session_mode;

if (!empty($gst_mode) && !in_array($gst_mode, array('FORMULAIRE', 'AFFICHE_JOURNAL')))
    $gst_mode = 'FORMULAIRE';

print('<!DOCTYPE html>');
print("<head>");
print('<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" >');
print('<meta http-equiv="content-language" content="fr"> ');
print('<meta name="viewport" content="width=device-width, initial-scale=1.0">');
print('<title>Base ' . SIGLE_ASSO . ': Stats consulations adh&eacute;rent</title>');
print('<link href="../assets/css/styles.css" type="text/css" rel="stylesheet">');
print('<link href="../assets/css/bootstrap.min.css" rel="stylesheet">');
print('<script src="../assets/js/jquery-min.js" type="text/javascript"></script>');
print('<script src="../assets/js/bootstrap.min.js" type="text/javascript"></script>');
?>
<script type='text/javascript'>
    function maj(Formulaire) {

        if (document.forms[Formulaire].num_page_patcom)
            document.forms[Formulaire].num_page_patcom.value = 1;
        document.forms[Formulaire].submit();
    }
</script>
<?php
print('<link rel="shortcut icon" href="images/favicon.ico">');
print("</head>");

/**
 * Affiche le menu formulaire
 */
function affiche_formulaire()
{;
    print('<div class="text-center">');
    print("<form method=post>");
    print('<div class="row form-group">');
    print('<label for="idf_journal" class="form-form-label col-md-2">S&eacute;lection du fichier pour la recherche</label>');
    print('<div class="col-md-6">');
    print('<select name="idf_journal" id="idf_journal" class="form-control">');
    print('<option value=1>Requête recherche sur une personne </OPTION>');
    print('<option value=2>Requête recherche sur un couple </OPTION>');
    print('<option value=3>Requête recherche sur les dépouillements </OPTION>');
    print('<option value=4>Requête recherche sur les liasses</OPTION>');
    print('<option value=5>Requête recherche sur les r&eacute;pertoires</OPTION>');
    print('<option value=6>Requête recherche sur les TD de mariage</OPTION>');
    print('<option value=7>Requête recherche sur les TD de naissance</OPTION>');
    print('<option value=8>Requête recherche sur les TD de d&eacute;c&eacute;s</OPTION>');
    print("</select>");
    print('</div>');
    print('</div>');

    print('<div class="row form-group">');
    print('<label for="libre" class="form-form-label col-md-2">Recherche libre dans un des champs: </label>');
    print('<div class="col-md-6">');
    print('<input type="text" name="libre" id="text" class="form-control">');
    print('</div>');
    print("</div>");
    print('<input type="hidden" value="AFFICHE_JOURNAL" name="mode"/>');
    print('<button type="submit" value="valider" name="valider" class="btn btn-primary col-md-4 col-md-offset-4"><span class="glyphicon glyphicon-search"></span> Valider</button>');
    print("</form>");
    print("</div>");
}


print("<body>");
print('<div class="container">');

require_once __DIR__ . '/../Commun/menu.php';

$ga_fichiers_logs = array(
    1 => array('requetes_personne.log', 5, array('Date', 'Ident', 'IP', 'Nom', 'Prenom', 'Commune', 'Rayon', 'Année Min', 'Année Max', 'Commentaires')),
    2 => array('requetes_couple.log', 7, array('Date', 'Ident', 'IP', 'Nom Epx', 'Prenom Epx', 'Nom Epse', 'Prenom Epse', 'Commune', 'Rayon', 'Année Min', 'Année Max')),
    3 => array('requetes_depouillements.log', null, array('Date', 'Ident', 'IP', 'Commune', 'Type d\'acte')),
    4 => array('requetes_liasse.log', 8, array('Date', 'Ident', 'IP', 'Nom notaire', 'Prenom Notaire', 'Serie', 'Cote Debut', 'Cote Fin', 'Commune', 'Rayon', 'Année Min', 'Année Max')),
    5 => array('requetes_rep_not.log', 3, array('Date', 'Ident', 'IP', 'Commune', 'Rayon', 'Idf Rep', 'Type acte', 'Année Min', 'Année Max', 'Nom 1', 'Prenom 1', 'Nom 2', 'Prenom 2', 'Paroisse')),
    6 => array('requetes_td_mariages.log', 3, array('Date', 'Ident', 'IP', 'Commune')),
    7 => array('requetes_td_naissances.log', 3, array('Date', 'Ident', 'IP', 'Commune')),
    8 => array('requetes_td_deces.log', 3, array('Date', 'Ident', 'IP', 'Commune'))
);

list($gst_adherent, $gst_ident) = $connexionBD->sql_select_liste("select concat(prenom,' ',nom,' (',idf,')'),ident from adherent where idf=$gi_idf_adherent");
$a_communes_acte = $connexionBD->liste_valeur_par_clef("select idf,nom from commune_acte");

print('<div class="panel panel-primary">');
print("<div class=\"panel-heading\">Affichage des recherches de l'adh&eacute;rent " . cp1252_vers_utf8($gst_adherent) . "</div>");
print('<div class="panel-body">');
if (isset($gi_idf_adherent)) {
    switch ($gst_mode) {
        case 'FORMULAIRE':
            affiche_formulaire();
            break;
        case 'AFFICHE_JOURNAL':
            $i_session_idf_journal =  isset($_SESSION['idf_journal']) ? $_SESSION['idf_journal'] : null;
            $gi_idf_journal =   isset($_POST['idf_journal']) ? (int) $_POST['idf_journal'] : $i_session_idf_journal;
            $i_session_num_page = isset($_SESSION['num_page_recherches_adht']) ? $_SESSION['num_page_recherches_adht'] : 1;
            $gi_num_page = empty($_POST['num_page_recherches_adht']) ?  $i_session_num_page : (int) $_POST['num_page_recherches_adht'];
            $st_session_libre = isset($_SESSION['libre']) ? $_SESSION['libre'] : '';
            $gst_libre =   isset($_POST['libre']) ? $_POST['libre'] : $st_session_libre;
            $_SESSION['idf_journal'] = $gi_idf_journal;
            $_SESSION['num_page_recherches_adht'] = $gi_num_page;
            $_SESSION['libre'] = $gst_libre;
            if (array_key_exists($gi_idf_journal, $ga_fichiers_logs)) {
                $st_fichier_journal = sprintf("%s/%s", $gst_rep_logs, $ga_fichiers_logs[$gi_idf_journal][0]);
                $i_col_paroisse = $ga_fichiers_logs[$gi_idf_journal][1];
                $a_entete = $ga_fichiers_logs[$gi_idf_journal][2];
                $a_resultats = array();
                $fp = @fopen($st_fichier_journal, 'r') or die("Ouverture en lecture de \"$st_fichier_journal\" impossible !");
                while (!feof($fp)) {
                    $st_ligne = fgets($fp, 4096);
                    if (empty($st_ligne))
                        continue;
                    $a_ligne = explode(SEP_CSV, $st_ligne);
                    if ($a_ligne[1] != $gst_ident)
                        continue;

                    if (!is_null($i_col_paroisse)) {
                        if (array_key_exists($a_ligne[$i_col_paroisse], $a_communes_acte))
                            $a_ligne[$i_col_paroisse] = cp1252_vers_utf8($a_communes_acte[$a_ligne[$i_col_paroisse]]);
                        else
                            $a_ligne[$i_col_paroisse] = '';
                    }
                    if (empty($gst_libre)) {
                        $a_resultats[] = $a_ligne;
                    } else {
                        if (preg_grep("/$gst_libre/i", $a_ligne)) {
                            $a_resultats[] = $a_ligne;
                        }
                    }
                }
                fclose($fp);
                $nb = count($a_resultats);
                if ($nb > 0) {
                    if (!empty($gst_libre))
                        print("<div class='alert alet-warning'>Recherche filtr&eacute;e sur le champ '$gst_libre'</div>");
                    print("<form name=\"RecherchesAdherents\"  method=\"post\">");

                    $pagination = new PaginationTableau(basename(__FILE__), 'num_page_recherches_adht', $nb, 100, DELTA_NAVIGATION, $ga_fichiers_logs[$gi_idf_journal][2]);
                    $pagination->init_page_cour($gi_num_page);
                    $pagination->affiche_entete_liste_select('RecherchesAdherents');
                    $pagination->affiche_tableau_simple($a_resultats, false);
                    $pagination->affiche_entete_liste_select('RecherchesAdherents');
                    print("<input type=hidden name=mode value='AFFICHE_JOURNAL'>");
                    print("</form>");
                } else {
                    if (empty($st_libre))
                        print("<div class=\"alert alert-danger\">L'adh&eacute;rent n'a pas fait de recherche !</div>");
                    else
                        print('<div class="alert alert-danger">Pas de r&eacute;sultat</div>');
                }
            } else {
                print('<div class="alert alert-danger">Erreur: Ce fichier journal n\'est pas configur&eacute;</div>');
            }
            print("<form method=post>");
            print("<input type=hidden name=mode value=FORMULAIRE>");
            print('<button type=submit class="btn btn-primary col-md-4 col-md-offset-4"><span class="glyphicon glyphicon-home"></span> Retour</button>');
            print("</form>");
            break;
    }
} else {
    print('<div class="alert alert-danger">idf_adherent n\'est pas d&eacute;fini</div>');
}
print('</div>');
print('</div>');
print('</div>');
print("</body>");
print("</html>");
