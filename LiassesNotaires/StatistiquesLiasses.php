<?php

require_once __DIR__ . '/../Commun/Identification.php';
require_once __DIR__ . '/../Commun/commun.php';
require_once __DIR__ . '/../Commun/constantes.php';
require_once __DIR__ . '/../Commun/config.php';
require_once __DIR__ . '/../Commun/ConnexionBD.php';
require_once __DIR__ . '/../RequeteRecherche.php';
require_once __DIR__ . '/../Commun/PaginationTableau.php';
require_once __DIR__ . '/../Commun/Benchmark.php';
require_once __DIR__ . '/../Commun/VerificationDroits.php';
verifie_privilege(DROIT_NOTAIRES);

$connexionBD = ConnexionBD::singleton($gst_serveur_bd, $gst_utilisateur_bd, $gst_mdp_utilisateur_bd, $gst_nom_bd);

$requeteRecherche = new RequeteRecherche($connexionBD);


/* ------------------------------------------------------
   constitution des requêtes
*/
//-----------------------------Ensemble 16-2E
$st_requete = "SELECT count(distinct case when liasse_dates.date_debut_periode < str_to_date('1793/01/01', '%Y/%m/%d') then liasse.cote_liasse else null end) as nb_ante_1793, " .
    "       count(distinct case when liasse_dates.date_debut_periode < str_to_date('1793/01/01', '%Y/%m/%d') then null else liasse.cote_liasse end) as nb_post_1793," .
    "       count(distinct liasse.cote_liasse) as nb_tot " .
    "FROM   liasse join liasse_dates			on liasse_dates.cote_liasse	= liasse.cote_liasse " .
    "WHERE  liasse.cote_liasse like '2E%'";
list($i_nb_2E_ante_1793, $i_nb_2E_post_1793, $i_nb_2E_total) = $connexionBD->sql_select_listeUtf8($st_requete);

//-----------------------------Relevés 16-2E
$st_requete = "SELECT count(distinct case when liasse_dates.date_debut_periode < str_to_date('1793/01/01', '%Y/%m/%d') then liasse.cote_liasse else null end) as nb_ante_1793, " .
    "       count(distinct case when liasse_dates.date_debut_periode < str_to_date('1793/01/01', '%Y/%m/%d') then null else liasse.cote_liasse end) as nb_post_1793," .
    "       count(distinct liasse.cote_liasse) as nb_tot " .
    "FROM   liasse join liasse_dates			on liasse_dates.cote_liasse		= liasse.cote_liasse " .
    "              join liasse_releve			on liasse_releve.cote_liasse	= liasse.cote_liasse " .
    "WHERE  liasse.cote_liasse like '2E%'";
list($i_nb_2E_releve_ante_1793, $i_nb_2E_releve_post_1793, $i_nb_2E_releve_total) = $connexionBD->sql_select_listeUtf8($st_requete);

//-----------------------------Publiés papier 16-2E
$st_requete = "SELECT count(distinct case when liasse_dates.date_debut_periode < str_to_date('1793/01/01', '%Y/%m/%d') then liasse.cote_liasse else null end) as nb_ante_1793, " .
    "       count(distinct case when liasse_dates.date_debut_periode < str_to_date('1793/01/01', '%Y/%m/%d') then null else liasse.cote_liasse end) as nb_post_1793," .
    "       count(distinct liasse.cote_liasse) as nb_tot " .
    "FROM   liasse join liasse_dates					on liasse_dates.cote_liasse					= liasse.cote_liasse " .
    "              join liasse_publication_papier		on liasse_publication_papier.cote_liasse	= liasse.cote_liasse " .
    "WHERE  liasse.cote_liasse like '2E%'";
list($i_nb_2E_publi_ante_1793, $i_nb_2E_publi_post_1793, $i_nb_2E_publi_total) = $connexionBD->sql_select_listeUtf8($st_requete);

//-----------------------------Photographiés 16-2E
$st_requete = "SELECT count(distinct case when liasse_dates.date_debut_periode < str_to_date('1793/01/01', '%Y/%m/%d') then liasse.cote_liasse else null end) as nb_ante_1793, " .
    "       count(distinct case when liasse_dates.date_debut_periode < str_to_date('1793/01/01', '%Y/%m/%d') then null else liasse.cote_liasse end) as nb_post_1793," .
    "       count(distinct liasse.cote_liasse) as nb_tot " .
    "FROM   liasse join liasse_dates			on liasse_dates.cote_liasse		= liasse.cote_liasse " .
    "              join liasse_photo			on liasse_photo.cote_liasse		= liasse.cote_liasse " .
    "WHERE  liasse.cote_liasse like '2E%'";
list($i_nb_2E_photo_ante_1793, $i_nb_2E_photo_post_1793, $i_nb_2E_photo_total) = $connexionBD->sql_select_listeUtf8($st_requete);

//-----------------------------Répertoires 16-2E
$st_requete = "SELECT count(distinct case when liasse_dates.date_debut_periode < str_to_date('1793/01/01', '%Y/%m/%d') then liasse.cote_liasse else null end) as nb_ante_1793, " .
    "       count(distinct case when liasse_dates.date_debut_periode < str_to_date('1793/01/01', '%Y/%m/%d') then null else liasse.cote_liasse end) as nb_post_1793," .
    "       count(distinct liasse.cote_liasse) as nb_tot " .
    "FROM   liasse join liasse_dates			on liasse_dates.cote_liasse	= liasse.cote_liasse " .
    "WHERE  liasse.cote_liasse like '2E%' and liasse.idf_forme_liasse = 9";
list($i_nb_2E_repert_ante_1793, $i_nb_2E_repert_post_1793, $i_nb_2E_repert_total) = $connexionBD->sql_select_listeUtf8($st_requete);

//-----------------------------Répertoires relevés 16-2E
$st_requete = "SELECT count(distinct case when liasse_dates.date_debut_periode < str_to_date('1793/01/01', '%Y/%m/%d') then liasse.cote_liasse else null end) as nb_ante_1793, " .
    "       count(distinct case when liasse_dates.date_debut_periode < str_to_date('1793/01/01', '%Y/%m/%d') then null else liasse.cote_liasse end) as nb_post_1793," .
    "       count(distinct liasse.cote_liasse) as nb_tot " .
    "FROM   liasse join liasse_dates			on liasse_dates.cote_liasse		= liasse.cote_liasse " .
    "              join liasse_releve			on liasse_releve.cote_liasse	= liasse.cote_liasse " .
    "WHERE  liasse.cote_liasse like '2E%' and liasse.idf_forme_liasse = 9";
list($i_nb_2E_repert_releve_ante_1793, $i_nb_2E_repert_releve_post_1793, $i_nb_2E_repert_releve_total) = $connexionBD->sql_select_listeUtf8($st_requete);

//-----------------------------Répertoires publiés papier 16-2E
$st_requete = "SELECT count(distinct case when liasse_dates.date_debut_periode < str_to_date('1793/01/01', '%Y/%m/%d') then liasse.cote_liasse else null end) as nb_ante_1793, " .
    "       count(distinct case when liasse_dates.date_debut_periode < str_to_date('1793/01/01', '%Y/%m/%d') then null else liasse.cote_liasse end) as nb_post_1793," .
    "       count(distinct liasse.cote_liasse) as nb_tot " .
    "FROM   liasse join liasse_dates					on liasse_dates.cote_liasse					= liasse.cote_liasse " .
    "              join liasse_publication_papier		on liasse_publication_papier.cote_liasse	= liasse.cote_liasse " .
    "WHERE  liasse.cote_liasse like '2E%' and liasse.idf_forme_liasse = 9";
list($i_nb_2E_repert_publi_ante_1793, $i_nb_2E_repert_publi_post_1793, $i_nb_2E_repert_publi_total) = $connexionBD->sql_select_listeUtf8($st_requete);

//-----------------------------Répertoires photographiés 16-2E
$st_requete = "SELECT count(distinct case when liasse_dates.date_debut_periode < str_to_date('1793/01/01', '%Y/%m/%d') then liasse.cote_liasse else null end) as nb_ante_1793, " .
    "       count(distinct case when liasse_dates.date_debut_periode < str_to_date('1793/01/01', '%Y/%m/%d') then null else liasse.cote_liasse end) as nb_post_1793," .
    "       count(distinct liasse.cote_liasse) as nb_tot " .
    "FROM   liasse join liasse_dates			on liasse_dates.cote_liasse		= liasse.cote_liasse " .
    "              join liasse_photo			on liasse_photo.cote_liasse		= liasse.cote_liasse " .
    "WHERE  liasse.cote_liasse like '2E%' and liasse.idf_forme_liasse = 9";
list($i_nb_2E_repert_photo_ante_1793, $i_nb_2E_repert_photo_post_1793, $i_nb_2E_repert_photo_total) = $connexionBD->sql_select_listeUtf8($st_requete);

//-----------------------------Non communicables 16-2E
$st_requete = "SELECT count(distinct case when liasse_dates.date_debut_periode < str_to_date('1793/01/01', '%Y/%m/%d') then liasse.cote_liasse else null end) as nb_ante_1793, " .
    "       count(distinct case when liasse_dates.date_debut_periode < str_to_date('1793/01/01', '%Y/%m/%d') then null else liasse.cote_liasse end) as nb_post_1793," .
    "       count(distinct liasse.cote_liasse) as nb_tot " .
    "FROM   liasse join liasse_dates			on liasse_dates.cote_liasse	= liasse.cote_liasse " .
    "WHERE  liasse.cote_liasse like '2E%' and liasse.in_liasse_consultable = 0";
list($i_nb_2E_non_comm_ante_1793, $i_nb_2E_non_comm_post_1793, $i_nb_2E_non_comm_total) = $connexionBD->sql_select_listeUtf8($st_requete);

//-----------------------------Non communicables relevés 16-2E
$st_requete = "SELECT count(distinct case when liasse_dates.date_debut_periode < str_to_date('1793/01/01', '%Y/%m/%d') then liasse.cote_liasse else null end) as nb_ante_1793, " .
    "       count(distinct case when liasse_dates.date_debut_periode < str_to_date('1793/01/01', '%Y/%m/%d') then null else liasse.cote_liasse end) as nb_post_1793," .
    "       count(distinct liasse.cote_liasse) as nb_tot " .
    "FROM   liasse join liasse_dates			on liasse_dates.cote_liasse		= liasse.cote_liasse " .
    "              join liasse_releve			on liasse_releve.cote_liasse	= liasse.cote_liasse " .
    "WHERE  liasse.cote_liasse like '2E%' and liasse.in_liasse_consultable = 0";
list($i_nb_2E_non_comm_releve_ante_1793, $i_nb_2E_non_comm_releve_post_1793, $i_nb_2E_non_comm_releve_total) = $connexionBD->sql_select_listeUtf8($st_requete);

//-----------------------------Non communicables publiés papier 16-2E
$st_requete = "SELECT count(distinct case when liasse_dates.date_debut_periode < str_to_date('1793/01/01', '%Y/%m/%d') then liasse.cote_liasse else null end) as nb_ante_1793, " .
    "       count(distinct case when liasse_dates.date_debut_periode < str_to_date('1793/01/01', '%Y/%m/%d') then null else liasse.cote_liasse end) as nb_post_1793," .
    "       count(distinct liasse.cote_liasse) as nb_tot " .
    "FROM   liasse join liasse_dates					on liasse_dates.cote_liasse					= liasse.cote_liasse " .
    "              join liasse_publication_papier		on liasse_publication_papier.cote_liasse	= liasse.cote_liasse " .
    "WHERE  liasse.cote_liasse like '2E%' and liasse.in_liasse_consultable = 0";
list($i_nb_2E_non_comm_publi_ante_1793, $i_nb_2E_non_comm_publi_post_1793, $i_nb_2E_non_comm_publi_total) = $connexionBD->sql_select_listeUtf8($st_requete);

//-----------------------------Non communicables photographiés 16-2E
$st_requete = "SELECT count(distinct case when liasse_dates.date_debut_periode < str_to_date('1793/01/01', '%Y/%m/%d') then liasse.cote_liasse else null end) as nb_ante_1793, " .
    "       count(distinct case when liasse_dates.date_debut_periode < str_to_date('1793/01/01', '%Y/%m/%d') then null else liasse.cote_liasse end) as nb_post_1793," .
    "       count(distinct liasse.cote_liasse) as nb_tot " .
    "FROM   liasse join liasse_dates			on liasse_dates.cote_liasse		= liasse.cote_liasse " .
    "              join liasse_photo			on liasse_photo.cote_liasse		= liasse.cote_liasse " .
    "WHERE  liasse.cote_liasse like '2E%' and liasse.in_liasse_consultable = 0";
list($i_nb_2E_non_comm_photo_ante_1793, $i_nb_2E_non_comm_photo_post_1793, $i_nb_2E_non_comm_photo_total) = $connexionBD->sql_select_listeUtf8($st_requete);

//-----------------------------Peu de pièces 16-2E
$st_requete = "SELECT count(distinct case when liasse_dates.date_debut_periode < str_to_date('1793/01/01', '%Y/%m/%d') then liasse.cote_liasse else null end) as nb_ante_1793, " .
    "       count(distinct case when liasse_dates.date_debut_periode < str_to_date('1793/01/01', '%Y/%m/%d') then null else liasse.cote_liasse end) as nb_post_1793," .
    "       count(distinct liasse.cote_liasse) as nb_tot " .
    "FROM   liasse join liasse_dates			on liasse_dates.cote_liasse	= liasse.cote_liasse " .
    "WHERE  liasse.cote_liasse like '2E%' and liasse.idf_forme_liasse = 2";
list($i_nb_2E_peu_ante_1793, $i_nb_2E_peu_post_1793, $i_nb_2E_peu_total) = $connexionBD->sql_select_listeUtf8($st_requete);

//-----------------------------Ensemble autres séries
$st_requete = "SELECT substr(liasse.cote_liasse, 1, instr(liasse.cote_liasse, '-')-1) as serie, " .
    "       count(distinct case when liasse_dates.date_debut_periode < str_to_date('1793/01/01', '%Y/%m/%d') then liasse.cote_liasse else null end) as nb_ante_1793, " .
    "       count(distinct case when liasse_dates.date_debut_periode < str_to_date('1793/01/01', '%Y/%m/%d') then null else liasse.cote_liasse end) as nb_post_1793," .
    "       count(distinct liasse.cote_liasse) as nb_tot " .
    "FROM   liasse join liasse_dates	on liasse_dates.cote_liasse	= liasse.cote_liasse " .
    "WHERE  liasse.cote_liasse not like '2E%' " .
    "GROUP BY substr(liasse.cote_liasse, 1, instr(liasse.cote_liasse, '-')-1)";
$a_liasses = $connexionBD->sql_select_multipleUtf8($st_requete);

/* ------------------------------------------------------
   constitution de la log 
*/
$gst_adresse_ip = $_SERVER['REMOTE_ADDR'];
$pf = @fopen("$gst_rep_logs/requetes_action_liasse.log", 'a');
list($i_sec, $i_min, $i_heure, $i_jmois, $i_mois, $i_annee, $i_j_sem, $i_j_an, $b_hiver) = localtime();
$i_mois++;
$i_annee += 1900;
$st_date_log = sprintf("%02d/%02d/%04d %02d:%02d:%02d", $i_jmois, $i_mois, $i_annee, $i_heure, $i_min, $i_sec);
$st_chaine_log = join(';', array($st_date_log, $_SESSION['ident'], 'statistiques', ''));
@fwrite($pf, "$st_chaine_log\n");
@fclose($pf);


print('<!DOCTYPE html>');
print("<head>\n");
print('<meta name="viewport" content="width=device-width, initial-scale=1.0">');
print('<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" >');
print('<meta http-equiv="content-language" content="fr">');
print("<link href='../assets/css/styles.css' type='text/css' rel='stylesheet'>");
print("<link href='../assets/css/bootstrap.min.css' rel='stylesheet'>");
print("<link href='../assets/css/jquery-ui.css' type='text/css' rel='stylesheet'>");
print("<link href='../assets/css/jquery-ui.structure.min.css' type='text/css' rel='stylesheet'>");
print("<link href='../assets/css/jquery-ui.theme.min.css' type='text/css' rel='stylesheet'>");
print("<link href='../assets/css/select2.min.css' type='text/css' rel='stylesheet'>");
print("<link href='../assets/css/select2-bootstrap.min.css' type='text/css' rel='stylesheet'>");
print("<script src='../assets/js/jquery-min.js' type='text/javascript'></script>");
print("<script src='../assets/js/jquery.validate.min.js' type='text/javascript'></script>");
print("<script src='../assets/js/additional-methods.min.js' type='text/javascript'></script>");
print("<script src='../assets/js/jquery-ui.min.js' type='text/javascript'></script>");
print("<script src='../assets/js/select2.min.js' type='text/javascript'></script>");
print("<script src='../assets/js/bootstrap.min.js' type='text/javascript'></script>");
print("<title>Base AGC: Statistiques sur les liasses notariales</title>");
print('</head>');




print("<body>");
print('<div class="container" align=center>');
require_once __DIR__ . '/../Commun/menu.php';

print('<div class="panel panel-primary">');
print('<div class="panel-heading">Statistiques sur les liasses</div>');
print('<div class="panel-body" align="center">');
print('<div class="alert alert-info">Série AD16 - 2E</div>');
print('<table class="table table-bordered">');
print('<thead><tr><th width="20%">&nbsp;</th>' .
    '<th width="30%">Nombre de liasses</th>' .
    '<th width="10%">&nbsp;&nbsp;&nbsp;Avant 1793</th>' .
    '<th width="10%">&nbsp;Depuis 1793</th>' .
    '<th width="10%">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Total</th>' .
    '<th width="20%">&nbsp;</th></tr></thead><tbody>');
print('<tr><td>&nbsp;</td>' .
    '<td align="center">Ensemble des liasses</td>' .
    '<td align="center">' . $i_nb_2E_ante_1793 . '</td>' .
    '<td align="center">' . $i_nb_2E_post_1793 . '</td>' .
    '<td align="center">' . $i_nb_2E_total . '</td>' .
    '<td>&nbsp;</td></tr>');
print('<tr><td>&nbsp;</td>' .
    '<td align="right"><i>Relevées</i></td>' .
    '<td align="center">' . $i_nb_2E_releve_ante_1793 . '</td>' .
    '<td align="center">' . $i_nb_2E_releve_post_1793 . '</td>' .
    '<td align="center">' . $i_nb_2E_releve_total . '</td>' .
    '<td>&nbsp;</td></tr>');
print('<tr><td></td>' .
    '<td align="right"><i>Publiées papier</i></td>' .
    '<td align="center">' . $i_nb_2E_publi_ante_1793 . '</td>' .
    '<td align="center">' . $i_nb_2E_publi_post_1793 . '</td>' .
    '<td align="center">' . $i_nb_2E_publi_total . '</td>' .
    '<td></td></tr>');
print('<tr><td></td>' .
    '<td align="right"><i>Photographiées</i></td>' .
    '<td align="center">' . $i_nb_2E_photo_ante_1793 . '</td>' .
    '<td align="center">' . $i_nb_2E_photo_post_1793 . '</td>' .
    '<td align="center">' . $i_nb_2E_photo_total . '</td>' .
    '<td></td></tr>');
print('<tr><td></td>' .
    '<td align="center">Liasses répertoires</td>' .
    '<td align="center">' . $i_nb_2E_repert_ante_1793 . '</td>' .
    '<td align="center">' . $i_nb_2E_repert_post_1793 . '</td>' .
    '<td align="center">' . $i_nb_2E_repert_total . '</td>' .
    '<td></td></tr>');
print('<tr><td></td>' .
    '<td align="right"><i>Relevées</i></td>' .
    '<td align="center">' . $i_nb_2E_repert_releve_ante_1793 . '</td>' .
    '<td align="center">' . $i_nb_2E_repert_releve_post_1793 . '</td>' .
    '<td align="center">' . $i_nb_2E_repert_releve_total . '</td>' .
    '<td></td></tr>');
print('<tr><td></td>' .
    '<td align="right"><i>Publiées papier</i></td>' .
    '<td align="center">' . $i_nb_2E_repert_publi_ante_1793 . '</td>' .
    '<td align="center">' . $i_nb_2E_repert_publi_post_1793 . '</td>' .
    '<td align="center">' . $i_nb_2E_repert_publi_total . '</td>' .
    '<td></td></tr>');
print('<tr><td></td>' .
    '<td align="right"><i>Photographiées</i></td>' .
    '<td align="center">' . $i_nb_2E_repert_photo_ante_1793 . '</td>' .
    '<td align="center">' . $i_nb_2E_repert_photo_post_1793 . '</td>' .
    '<td align="center">' . $i_nb_2E_repert_photo_total . '</td>' .
    '<td></td></tr>');
print('<tr><td></td>' .
    '<td align="center">Liasses non communicables</td>' .
    '<td align="center">' . $i_nb_2E_non_comm_ante_1793 . '</td>' .
    '<td align="center">' . $i_nb_2E_non_comm_post_1793 . '</td>' .
    '<td align="center">' . $i_nb_2E_non_comm_total . '</td>' .
    '<td></td></tr>');
print('<tr><td></td>' .
    '<td align="right"><i>Relevées</i></td>' .
    '<td align="center">' . $i_nb_2E_non_comm_releve_ante_1793 . '</td>' .
    '<td align="center">' . $i_nb_2E_non_comm_releve_post_1793 . '</td>' .
    '<td align="center">' . $i_nb_2E_non_comm_releve_total . '</td>' .
    '<td></td></tr>');
print('<tr><td></td>' .
    '<td align="right"><i>Publiées papier</i></td>' .
    '<td align="center">' . $i_nb_2E_non_comm_publi_ante_1793 . '</td>' .
    '<td align="center">' . $i_nb_2E_non_comm_publi_post_1793 . '</td>' .
    '<td align="center">' . $i_nb_2E_non_comm_publi_total . '</td>' .
    '<td></td></tr>');
print('<tr><td></td>' .
    '<td align="right"><i>Photographiées</i></td>' .
    '<td align="center">' . $i_nb_2E_non_comm_photo_ante_1793 . '</td>' .
    '<td align="center">' . $i_nb_2E_non_comm_photo_post_1793 . '</td>' .
    '<td align="center">' . $i_nb_2E_non_comm_photo_total . '</td>' .
    '<td></td></tr>');
print('<tr><td></td>' .
    '<td align="center">Liasses peu de pièces</td>' .
    '<td align="center">' . $i_nb_2E_peu_ante_1793 . '</td>' .
    '<td align="center">' . $i_nb_2E_peu_post_1793 . '</td>' .
    '<td align="center">' . $i_nb_2E_peu_total . '</td>' .
    '<td></td></tr>');
print('</tbody></table>');
print('<table class="table table-bordered">');
print('<thead><tr><th width="20%">&nbsp;</th>' .
    '<th width="30%">% sur total</th>' .
    '<th width="10%">&nbsp;&nbsp;&nbsp;Avant 1793</th>' .
    '<th width="10%">&nbsp;Depuis 1793</th>' .
    '<th width="10%">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Total</th>' .
    '<th width="20%">&nbsp;</th></tr></thead><tbody>');
print('<tr><td></td>' .
    '<td align="right"><i>Relevées</i></td>' .
    '<td align="center">' . round($i_nb_2E_releve_ante_1793 / $i_nb_2E_ante_1793 * 100, 2) . ' %</td>' .
    '<td align="center">' . round($i_nb_2E_releve_post_1793 / $i_nb_2E_post_1793 * 100, 2) . ' %</td>' .
    '<td align="center">' . round($i_nb_2E_releve_total / $i_nb_2E_total * 100, 2) . ' %</td>' .
    '<td></td></tr>');
print('<tr><td></td>' .
    '<td align="right"><i>Publiées papier</i></td>' .
    '<td align="center">' . round($i_nb_2E_publi_ante_1793 / $i_nb_2E_ante_1793 * 100, 2) . ' %</td>' .
    '<td align="center">' . round($i_nb_2E_publi_post_1793 / $i_nb_2E_post_1793 * 100, 2) . ' %</td>' .
    '<td align="center">' . round($i_nb_2E_publi_total / $i_nb_2E_total * 100, 2) . ' %</td>' .
    '<td></td></tr>');
print('<tr><td></td>' .
    '<td align="right"><i>Photographiées</i></td>' .
    '<td align="center">' . round($i_nb_2E_photo_ante_1793 / $i_nb_2E_ante_1793 * 100, 2) . ' %</td>' .
    '<td align="center">' . round($i_nb_2E_photo_post_1793 / $i_nb_2E_post_1793 * 100, 2) . ' %</td>' .
    '<td align="center">' . round($i_nb_2E_photo_total / $i_nb_2E_total * 100, 2) . ' %</td>' .
    '<td></td></tr>');
print('<tr><td></td>' .
    '<td align="center">Liasses répertoires</td>' .
    '<td align="center">' . round($i_nb_2E_repert_ante_1793 / $i_nb_2E_ante_1793 * 100, 2) . ' %</td>' .
    '<td align="center">' . round($i_nb_2E_repert_post_1793 / $i_nb_2E_post_1793 * 100, 2) . ' %</td>' .
    '<td align="center">' . round($i_nb_2E_repert_total / $i_nb_2E_total * 100, 2) . ' %</td>' .
    '<td></td></tr>');
print('<tr><td></td>' .
    '<td align="center">Liasses non communicables</td>' .
    '<td align="center">' . round($i_nb_2E_non_comm_ante_1793 / $i_nb_2E_ante_1793 * 100, 2) . ' %</td>' .
    '<td align="center">' . round($i_nb_2E_non_comm_post_1793 / $i_nb_2E_post_1793 * 100, 2) . ' %</td>' .
    '<td align="center">' . round($i_nb_2E_non_comm_total / $i_nb_2E_total * 100, 2) . ' %</td>' .
    '<td></td></tr>');
print('<tr><td></td>' .
    '<td align="center">Liasses peu de pièces</td>' .
    '<td align="center">' . round($i_nb_2E_peu_ante_1793 / $i_nb_2E_ante_1793 * 100, 2) . ' %</td>' .
    '<td align="center">' . round($i_nb_2E_peu_post_1793 / $i_nb_2E_post_1793 * 100, 2) . ' %</td>' .
    '<td align="center">' . round($i_nb_2E_peu_total / $i_nb_2E_total * 100, 2) . ' %</td>' .
    '<td></td></tr>');
print('</tbody></table ><br>');
print('<div class="alert alert-info">Autres séries</div>');
print('<table class="table table-bordered">');
print('<thead><tr><th width="40%">&nbsp;</th>' .
    '<th width="10%">Série</th>' .
    '<th width="10%">&nbsp;&nbsp;&nbsp;Avant 1793</th>' .
    '<th width="10%">&nbsp;Depuis 1793</th>' .
    '<th width="10%">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Total</th>' .
    '<th width="20%">&nbsp;</th></tr></thead><tbody>');

foreach ($a_liasses as $a_liasse) {
    list($st_serie, $i_nb_autre_ante_1793, $i_nb_autre_post_1793, $i_nb_autre_total) = $a_liasse;
    print('<tr><td>&nbsp;</td>' .
        '<td align="center">' . $st_serie . '</td>' .
        '<td align="center">' . $i_nb_autre_ante_1793 . '</td>' .
        '<td align="center">' . $i_nb_autre_post_1793 . '</td>' .
        '<td align="center">' . $i_nb_autre_total . '</td>' .
        '<td>&nbsp;</td></tr>');
}
print('</tbody></table ></div></div></div>');
print('<div align="center" style="font-size:11px;color:#4f6b72"><i>Liasses relevées : liasses dont les CM ont été retranscrits</i></div>');
print("</body></html>");
