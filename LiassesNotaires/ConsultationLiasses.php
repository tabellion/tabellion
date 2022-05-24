<?php
require_once __DIR__ . '/../Commun/config.php';
require_once __DIR__ . '/../Commun/constantes.php';
require_once __DIR__ . '/../Commun/Identification.php';
require_once __DIR__ . '/../Commun/VerificationDroits.php';
verifie_privilege(DROIT_CONSULT_NOT);
require_once __DIR__ . '/../Commun/ConnexionBD.php';
require_once __DIR__ . '/../Commun/PaginationTableau.php';
require_once __DIR__ . '/../Commun/commun.php';

$connexionBD = ConnexionBD::singleton($gst_serveur_bd, $gst_utilisateur_bd, $gst_mdp_utilisateur_bd, $gst_nom_bd);

$gst_mode = empty($_POST['mode']) ? 'LISTE' : $_POST['mode'];

if (isset($_GET['mod'])) {
	$gst_mode = 'MENU_GERER';
	$_SESSION['cote_liasse_gal'] = $_GET['mod'];
	list($_SESSION['periodes_gal'], $_SESSION['notaires_gal'])
		= $connexionBD->sql_select_listeUtf8("select libelle_annees, libelle_notaires from liasse where cote_liasse='" . $_SESSION['cote_liasse_gal'] . "'");
}

$gi_num_page_cour = empty($_GET['num_page']) ? 1 : $_GET['num_page'];

$a_releveur = $connexionBD->liste_valeur_par_clef("SELECT idf,concat(nom, ' ', prenom) as nom FROM releveur order by nom");
$a_releveur[0] = 'Inconnu';
$a_couverture_photo = $connexionBD->liste_valeur_par_clef("SELECT idf, nom FROM couverture_photo order by idf");
$a_couverture_photo[0] = '';
$a_codif_photo = $connexionBD->liste_valeur_par_clef("SELECT idf, nom FROM codif_photo order by idf");
$a_codif_photo[0] = '';
$a_priorite_program = $connexionBD->liste_valeur_par_clef("SELECT idf, nom FROM programmation_releve order by idf");
$a_priorite_program[0] = '';
$pa_publication = $connexionBD->liste_valeur_par_clef("SELECT idf, concat(nom, ', publi&eacute; le ', " .
	"                   case when date_publication = str_to_date('0000/00/00', '%Y/%m/%d') then '' " .
	"                        else date_format(date_publication, '%d/%m/%Y') " .
	"                        end, ', ', " .
	"                   substr(info_complementaires,1,80)) as nom " .
	"FROM publication_papier order by nom");

print('<!DOCTYPE html>');
print("<head>");
print("<title>Consultation des liasses notariales</title>");
print('<meta name="viewport" content="width=device-width, initial-scale=1.0">');
print('<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" >');
print('<meta http-equiv="content-language" content="fr">');
print("<link href='../css/styles.css' type='text/css' rel='stylesheet'>");
print("<link href='../css/bootstrap.min.css' rel='stylesheet'>");
print("<link href='../css/jquery-ui.css' type='text/css' rel='stylesheet'>");
print("<link href='../css/jquery-ui.structure.min.css' type='text/css' rel='stylesheet'>");
print("<link href='../css/jquery-ui.theme.min.css' type='text/css' rel='stylesheet'>");
print("<link href='../css/select2.min.css' type='text/css' rel='stylesheet'>");
print("<link href='../css/select2-bootstrap.min.css' type='text/css' rel='stylesheet'>");
print("<script src='../js/jquery-min.js' type='text/javascript'></script>");
print("<script src='../js/jquery.validate.min.js' type='text/javascript'></script>");
print("<script src='../js/additional-methods.min.js' type='text/javascript'></script>");
print("<script src='../js/jquery-ui.min.js' type='text/javascript'></script>");
print("<script src='../js/select2.min.js' type='text/javascript'></script>");
print("<script src='../js/bootstrap.min.js' type='text/javascript'></script>");
print("<script src='./VerifieChampsGestionActionsLiasse.js' type='text/javascript'></script>");
print('</head>');
print('<body><div class="container">');

require_once __DIR__ . '/../Commun/menu.php';

$pa_publication[0] = '';
require_once __DIR__ . '/ConsultationLiassesFc.php';
switch ($gst_mode) {
	case 'LISTE':
		if (isset($_SESSION['cote_liasse_gal'])) {
			unset($_SESSION['cote_liasse_gal']);
		}
		menu_liste($connexionBD);
		break;
	case 'MENU_GERER':
		menu_gerer($connexionBD);
		break;
}
print('</div></body></html>');
