<?php

require_once __DIR__ . '/app/bootstrap.php';
require_once __DIR__ . '/Commun/commun.php';
require_once __DIR__ . '/RequeteRecherche.php';
require_once __DIR__ . '/Commun/PaginationTableau.php';
require_once __DIR__ . '/Commun/Benchmark.php';
require_once __DIR__ . '/Commun/VerificationDroits.php';

$a_clauses = array();
$st_icone_info = './images/infos.png';

/* ------------------------------------------------------
   r�cup�ration des crit�res de recherche 
*/
$i_session_idf_commune				= empty($_SESSION['idf_commune_recherche_rls']) ? '0' : $_SESSION['idf_commune_recherche_rls'];
$i_session_rayon					= empty($_SESSION['rayon_rls']) ? '' : $_SESSION['rayon_rls'];
$st_session_paroisses_rattachees	= empty($_SESSION['paroisses_rattachees_rls']) ? '' : $_SESSION['paroisses_rattachees_rls'];
$i_session_annee_min				= empty($_SESSION['annee_min_rls']) ? '' : $_SESSION['annee_min_rls'];
$i_session_annee_max				= empty($_SESSION['annee_max_rls']) ? '' : $_SESSION['annee_max_rls'];
$st_session_nom_notaire				= empty($_SESSION['nom_notaire_rls']) ? '' : $_SESSION['nom_notaire_rls'];
$st_session_prenom_notaire			= empty($_SESSION['prenom_notaire_rls']) ? '' : $_SESSION['prenom_notaire_rls'];
$st_session_variantes				= empty($_SESSION['variantes_rls']) ? '' : $_SESSION['variantes_rls'];
$st_session_idf_serie_liasse		= empty($_SESSION['idf_serie_liasse_rls']) ? '' : $_SESSION['idf_serie_liasse_rls'];
$st_session_cote_debut				= empty($_SESSION['cote_debut_rls']) ? '' : $_SESSION['cote_debut_rls'];
$st_session_cote_fin				= empty($_SESSION['cote_fin_rls']) ? '' : $_SESSION['cote_fin_rls'];
$i_session_idf_forme_liasse			= empty($_SESSION['idf_forme_liasse_rls']) ? '0' : $_SESSION['idf_forme_liasse_rls'];
$st_session_repertoire				= empty($_SESSION['repertoire_rls']) ? 'non' : $_SESSION['repertoire_rls'];
$st_session_sans_notaire			= empty($_SESSION['sans_notaire_rls']) ? 'non' : $_SESSION['sans_notaire_rls'];
$st_session_sans_periode			= empty($_SESSION['sans_periode_rls']) ? 'non' : $_SESSION['sans_periode_rls'];
$st_session_liasse_releve			= empty($_SESSION['liasse_releve_rls']) ? 'non' : $_SESSION['liasse_releve_rls'];

$gi_idf_commune						= empty($_POST['idf_commune_recherche']) ? $i_session_idf_commune : (int) $_POST['idf_commune_recherche'];
$gi_rayon							= empty($_POST['rayon']) ? $i_session_rayon : (int) trim($_POST['rayon']);
$gst_paroisses_rattachees			= empty($_POST['paroisses_rattachees']) ? $st_session_paroisses_rattachees : trim($_POST['paroisses_rattachees']);
$gi_annee_min						= empty($_POST['annee_min']) ? $i_session_annee_min : (int) trim($_POST['annee_min']);
$gi_annee_max						= empty($_POST['annee_max']) ? $i_session_annee_max : (int) trim($_POST['annee_max']);
$gst_nom_notaire					= empty($_POST['nom_notaire']) ? $st_session_nom_notaire : trim($_POST['nom_notaire']);
$gst_prenom_notaire					= empty($_POST['prenom_notaire']) ? $st_session_prenom_notaire : trim($_POST['prenom_notaire']);
$gst_variantes						= empty($_POST['variantes']) ? $st_session_variantes : trim($_POST['variantes']);
$gst_idf_serie_liasse				= empty($_POST['idf_serie_liasse']) ? $st_session_idf_serie_liasse : $_POST['idf_serie_liasse'];
$gst_cote_debut						= empty($_POST['cote_debut']) ? $st_session_cote_debut : trim($_POST['cote_debut']);
$gst_cote_fin						= empty($_POST['cote_fin']) ? $st_session_cote_fin : trim($_POST['cote_fin']);
$gi_idf_forme_liasse				= empty($_POST['idf_forme_liasse']) ? $i_session_idf_forme_liasse : (int) trim($_POST['idf_forme_liasse']);
$gst_repertoire						= empty($_POST['repertoire']) ? $st_session_repertoire : trim($_POST['repertoire']);
$gst_sans_notaire					= empty($_POST['sans_notaire']) ? $st_session_sans_notaire : trim($_POST['sans_notaire']);
$gst_sans_periode					= empty($_POST['sans_periode']) ? $st_session_sans_periode : trim($_POST['sans_periode']);
$gst_liasse_releve					= empty($_POST['liasse_releve']) ? $st_session_liasse_releve : trim($_POST['liasse_releve']);

$gi_get_num_page = empty($_GET['num_page']) ? 1 : (int) $_GET['num_page'];
$gi_num_page = empty($_POST['num_page']) ? $gi_get_num_page : (int) $_POST['num_page'];

$st_communes_voisines   = '';

$_SESSION['idf_commune_recherche_rls']		= $gi_idf_commune;
$_SESSION['rayon_rls']						= $gi_rayon;
$_SESSION['paroisses_rattachees_rls']		= $gst_paroisses_rattachees;
$_SESSION['annee_min_rls']					= $gi_annee_min;
$_SESSION['annee_max_rls']					= $gi_annee_max;

$_SESSION['nom_notaire_rls']				= $gst_nom_notaire;
$_SESSION['prenom_notaire_rls']				= $gst_prenom_notaire;
$_SESSION['variantes_rls']					= $gst_variantes;
$_SESSION['idf_serie_liasse_rls']			= $gst_idf_serie_liasse;
$_SESSION['cote_debut_rls']					= $gst_cote_debut;
$_SESSION['cote_fin_rls']					= $gst_cote_fin;
$_SESSION['repertoire_rls']					= $gst_repertoire;
$_SESSION['sans_notaire_rls']				= $gst_sans_notaire;
$_SESSION['sans_periode_rls']				= $gst_sans_periode;
$_SESSION['liasse_releve_rls']				= $gst_liasse_releve;

$b_pers_def = false;

if ($gst_nom_notaire != '')
	$b_pers_def = true;

/* ------------------------------------------------------
   constitution de la log 
*/
$gst_adresse_ip = $_SERVER['REMOTE_ADDR'];
$pf = @fopen("$gst_rep_logs/requetes_liasse.log", 'a');
list($i_sec, $i_min, $i_heure, $i_jmois, $i_mois, $i_annee, $i_j_sem, $i_j_an, $b_hiver) = localtime();
$i_mois++;
$i_annee += 1900;
$st_date_log = sprintf("%02d/%02d/%04d %02d:%02d:%02d", $i_jmois, $i_mois, $i_annee, $i_heure, $i_min, $i_sec);
$st_chaine_log = join(';', array(
	$st_date_log, $_SESSION['ident'], $gst_adresse_ip, $gst_nom_notaire, $gst_prenom_notaire,
	$gst_idf_serie_liasse, $gst_cote_debut, $gst_cote_fin, $gi_idf_commune, $gi_rayon,
	$gi_annee_min, $gi_annee_max
));
@fwrite($pf, "$st_chaine_log\n");
@fclose($pf);

function nomNotaire($pConnexionBD, $pst_patronyme, $pst_variantes)
{
	$st_clause = '';
	//$st_patronyme=utf8_vers_cp1252($pst_patronyme);
	$st_patronyme = $pst_patronyme;
	if (($pst_variantes == '') || preg_match('/\%/', $st_patronyme)) {
		if (preg_match('/\%/', $st_patronyme))
			$st_clause = " like '" . $st_patronyme . "'";
		else
			$st_clause = "='" . $st_patronyme . "'";
	} else {
		if ($pst_variantes == 'oui') {
			$st_requete = "select vp1.patronyme from variantes_patro vp1, variantes_patro vp2 " .
				"where vp2.patronyme = '" . $st_patronyme . "' and vp1.idf_groupe=vp2.idf_groupe";
		} else {
			// variantes phonetiques
			$st_requete = "select p2.libelle from `patronyme` p1 join `patronyme` p2 on (truncate(p1.phonex,7)=truncate(p2.phonex,7)) " .
				"where p1.libelle='" . $st_patronyme . "' ";
		}
		$st_clause = "in ($st_requete) ";
	}
	return $st_clause;
}

function prenomNotaire($pConnexionBD, $pst_prenom, $pst_variantes)
{
	$st_clause = '';
	if (!empty($pst_prenom)) {
		//$st_prenom=utf8_vers_cp1252($pst_prenom);
		$st_prenom = $pst_prenom;
		if (($pst_variantes == '') || preg_match('/\%/', $st_prenom)) {
			if (preg_match('/\%/', $st_prenom))
				$st_clause = "like '" . $st_prenom . "' ";
			else
				$st_clause = "= '" . $st_prenom . "' ";
		} else {
			$st_prenom = ucfirst(strtolower(trim($st_prenom)));
			$st_requete = "select vp1.libelle from variantes_prenom vp1, variantes_prenom vp2 " .
				"where vp2.libelle = '" . $st_prenom . "'  and vp1.idf_groupe=vp2.idf_groupe";
			$st_clause = "in ($st_requete) ";
		}
	}
	return $st_clause;
}

print('<!DOCTYPE html>');
print("<Head>\n");
print('<meta http-equiv="content-language" content="fr">');
print('<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" >');
print('<link rel="shortcut icon" href="assets/img/favicon.ico">');
print("<link href='css/styles.css' type='text/css' rel='stylesheet'>");
print("<link href='css/bootstrap.min.css' rel='stylesheet'>");
print("<script src='assets/js/jquery-min.js' type='text/javascript'></script>");
print("<script src='assets/js/jquery-ui.min.js' type='text/javascript'></script>");
print("<script src='assets/js/select2.min.js' type='text/javascript'></script>");
print("<script src='assets/js/bootstrap.min.js' type='text/javascript'></script>");
?>
<script type='text/javascript'>
	$(document).ready(function() {

		$('a.popup').click(function() {
			var url = $(this).attr("href");
			var windowName = "InfosActe";
			var windowSize = 'width=600,height=600,resizable=yes,scrollbars=yes';
			window.open(url, windowName, windowSize);
			return false;
		});

		$("#retour_recherche").click(function() {
			window.location.href = 'RecherchesLiasses.php';
		});

		$("#nouvelle_recherche").click(function() {
			window.location.href = 'RecherchesLiasses.php?recherche=nouvelle';
		});

		$('a.lien_infos_liasse').click(function() {
			window.open(this.href, 'InfosLiasse', 'width=600, height=500');
			return false;
		});

	});
</script>
<?php

print("<title>Base " . SIGLE_ASSO . ": Reponses a une recherche de liasse</title>");
print('</Head>');
print("<body>");
print('<div class="container">');

require_once __DIR__ . '/Commun/menu.php';

print("<form  method=\"post\">");


$requeteRecherche = new RequeteRecherche($connexionBD);

$a_communes_acte = $connexionBD->liste_valeur_par_clef("SELECT idf,nom FROM commune_acte");

$gst_requete_nb_liasses = "SELECT !isnull(r.cote_liasse), min(l.cote_liasse), min(n.nom_notaire), min(n.prenom_notaire), min(n.commentaire), " .
	"       min(d.date_debut_periode) as date_tri, min(l.libelle_annees), " .
	"       min(f.nom) as forme, n.idf_commune_etude, l.info_complementaires, l.in_liasse_consultable, " .
	"       case when r.idf is null then 'non' else 'oui' end " .
	"FROM liasse l join forme_liasse f on l.idf_forme_liasse = f.idf " .
	"     left outer join liasse_dates d on d.cote_liasse = l.cote_liasse " .
	"     left outer join liasse_notaire n on n.cote_liasse = l.cote_liasse " .
	"     left outer join liasse_releve r on r.cote_liasse = l.cote_liasse ";
print('   <br>');
if ($gi_idf_commune == -9)
	$a_clauses[] = "( n.idf_commune_etude is null or n.idf_commune_etude=0 )";
elseif ($gi_idf_commune != 0)
	$a_clauses[] = "n.idf_commune_etude " . $requeteRecherche->clause_droite_commune($gi_idf_commune, $gi_rayon, $gst_paroisses_rattachees);

if ($gi_annee_min != '')
	$gi_date_min = "str_to_date(concat('$gi_annee_min' , '-01-01'),'%Y-%m-%d')";
if ($gi_annee_max != '')
	$gi_date_max = "str_to_date(concat('$gi_annee_max' , '-12-31'),'%Y-%m-%d')";
if ($gi_annee_min != '' && $gi_annee_max != '')
	$a_clauses[] = "(( d.date_debut_periode<=$gi_date_min and d.date_fin_periode>=$gi_date_min ) or ( d.date_debut_periode<=$gi_date_max and d.date_fin_periode>=$gi_date_max) or (d.date_debut_periode>=$gi_date_min and d.date_fin_periode<=$gi_date_max ))";
elseif ($gi_annee_min != '')
	$a_clauses[] = "d.date_fin_periode>=$gi_date_min";
elseif ($gi_annee_max != '')
	$a_clauses[] = "d.date_debut_periode<=$gi_date_max";

if ($gst_repertoire == 'oui')
	$a_clauses[] = "l.idf_forme_liasse=9";

if ($gst_sans_notaire == 'oui')
	$a_clauses[] = "n.cote_liasse is null";

if ($gst_sans_periode == 'oui')
	$a_clauses[] = "d.cote_liasse is null";

if ($gst_liasse_releve == 'oui')
	$a_clauses[] = "case when r.idf is null then 'non' else 'oui' end='oui'";


$gst_nom_notaire  = str_replace('*', '%', $gst_nom_notaire);
if ($gst_nom_notaire != '' && $gst_nom_notaire != '*') {
	$gst_nom_notaire = strtoupper($gst_nom_notaire);
	$a_clauses[] = "n.nom_notaire " . nomNotaire($connexionBD, $gst_nom_notaire, $gst_variantes);
	if ($gst_prenom_notaire != '') {
		$gst_prenom_notaire  = str_replace('*', '%', $gst_prenom_notaire);
		$st_prenom_groupe = str_replace('%', '', $gst_prenom_notaire);
		$a_clauses[] = "(n.prenom_notaire " . $requeteRecherche->clause_droite_prenom($gst_prenom_notaire, $gst_variantes, 1) .
			" or n.prenom_notaire like '%" . $st_prenom_groupe . "%')";
	}
}

if ($gst_cote_debut != '' && $gst_cote_fin != '') {
	$gst_cote_debut = $gst_idf_serie_liasse . '-' . str_pad($gst_cote_debut, 5, '0', STR_PAD_LEFT);
	$gst_cote_fin = $gst_idf_serie_liasse . '-' . str_pad($gst_cote_fin, 5, '0', STR_PAD_LEFT);
	$a_clauses[] = "l.cote_liasse>='" . $gst_cote_debut . "' and l.cote_liasse<='" . $gst_cote_fin . "'";
} elseif ($gst_cote_debut != '') {
	$gst_cote_debut = $gst_idf_serie_liasse . '-' . str_pad($gst_cote_debut, 5, '0', STR_PAD_LEFT);
	$a_clauses[] = "l.cote_liasse>='" . $gst_cote_debut . "'";
} elseif ($gst_cote_fin != '') {
	$gst_cote_fin = $gst_idf_serie_liasse . '-' . str_pad($gst_cote_fin, 5, '0', STR_PAD_LEFT);
	$a_clauses[] = "l.cote_liasse<='" . $gst_cote_fin . "'";
}

if ($gst_repertoire == 'oui')
	$st_tri = " order by 2";
elseif ($gst_nom_notaire != '' && $gst_nom_notaire != '*')
	$st_tri = " order by 3,4,2";
else
	$st_tri = "order by 5,2";

$st_clauses = implode(" and ", $a_clauses);
$st_where = " where l.cote_liasse like '" . $gst_idf_serie_liasse . "%' ";
if ($st_clauses != '')
	$st_where .= ' and ' . $st_clauses;

$st_groupe = "group by l.cote_liasse, l.libelle_annees, f.nom, n.nom_notaire, n.prenom_notaire, n.idf_commune_etude," .
	"         l.info_complementaires, case when r.idf is null then 'non' else 'oui' end";

$gst_requete_liasses = "$gst_requete_nb_liasses $st_where $st_groupe $st_tri";


/* ------------------------------------------------------
   affichage des crit�res de recherche 
*/
print("<div class=\"row col-md-12\">");
print('<div id=col_paroisses class="col-md-4 col-md-offset-4 alert  alert-info">');
$st_criteres = "Recherche des liasses:\nS&eacute;rie " . $gst_idf_serie_liasse . "\n";

if ($gst_nom_notaire != '')
	$st_criteres .= "Notaire s&eacute;lectionn&eacute;: $gst_prenom_notaire $gst_nom_notaire";
elseif ($gst_sans_notaire == 'oui')
	$st_criteres .= "Liasses sans notaire";
else
	$st_criteres .= "Pas de notaire s&eacute;lectionn&eacute;";
$st_criteres .= "\n";

if ($gst_cote_debut != '' && $gst_cote_fin != '')
	$st_criteres .= "Cotes:  de $gst_cote_debut &agrave; $gst_cote_fin";
elseif ($gst_cote_debut != '')
	$st_criteres .= "Cotes:  &agrave; partir de $gst_cote_debut";
elseif ($gi_annee_max != '')
	$st_criteres .= "Cotes:  jusqu'&agrave; $gst_cote_fin";
else
	$st_criteres .= "Pas de cote s&eacute;lectionn&eacute;e";
$st_criteres .= "\n";

if ($gi_annee_min != '' && $gi_annee_max != '')
	$st_criteres .= "P&eacute;riode:  de $gi_annee_min &agrave; $gi_annee_max";
else if ($gi_annee_min != '')
	$st_criteres .= "P&eacute;riode:  &agrave; partir de $gi_annee_min";
else if ($gi_annee_max != '')
	$st_criteres .= "P&eacute;riode:  jusqu'en $gi_annee_max";
elseif ($gst_sans_periode == 'oui')
	$st_criteres .= "Liasses sans date";
else
	$st_criteres .= 'Pas de p&eacute;riode selectionn&eacute;e';
$st_criteres .= "\n";

if ($gi_idf_commune > 0) {
	$a_params_precedents = $connexionBD->params();
	$st_nom_commune = $connexionBD->sql_select1("select nom from commune_acte where idf=$gi_idf_commune");
	$st_criteres .= "Commune s&eacute;lectionn&eacute;e: " . cp1252_vers_utf8($st_nom_commune);
	$connexionBD->initialise_params($a_params_precedents);
} elseif ($gi_idf_commune == -9)
	$st_criteres .=  'Commune inconnue';
else
	$st_criteres .=  'Pas de commune selectionn&eacute;e';
$st_criteres .= "\n";

if ($gst_repertoire == 'oui')
	$st_criteres .= "uniquement les r&eacute;pertoires";
$st_criteres .= "\n";

if ($gst_liasse_releve == 'oui')
	$st_criteres .= "uniquement les liasses relev&eacute;es";
//$st_criteres .= "\n";
$st_criteres .= "** fond jaune = liasses relev&eacute;es\n";

print(nl2br($st_criteres));

if (count(array_values($requeteRecherche->communes_voisines())) > 1) {
	$st_communes_voisines = join("\n", array_values($requeteRecherche->communes_voisines()));
	print("Paroisses voisines ou rattach&eacute;es<br>");
	if ($gi_rayon != '') {
		print("(avec recherches dans un rayon de $gi_rayon km)\n");
		$st_criteres .= " (avec recherches dans un rayon de $gi_rayon km)\n";
	}
	print("<textarea rows=6 cols=40>" . cp1252_vers_utf8($st_communes_voisines) . "</textarea>");
}
print("</div>");
print("</div>");
$st_clauses = implode(" and ", $a_clauses);
$etape_prec = getmicrotime();
$a_params_precedents = $connexionBD->params();
$a_liasses = $connexionBD->sql_select_multiple($gst_requete_liasses);
$connexionBD->initialise_params($a_params_precedents);


print benchmark("Recherche ");
$i_nb_liasses = count($a_liasses);
print("<div class=\"row text-center col-md-12\"><span class=\"badge\">$i_nb_liasses</span> occurrences trouv&eacute;es.</div>");
print('<div id="curseur" class="infobulle"></div>');
/*if ($i_nb_liasses>$gi_nb_max_reponses) {
	print("<div class=\"row text-center col-md-12\">Seules les $gi_nb_max_reponses premi&egrave;res sont affich&eacute;es</div>");
	$a_liasses = array_slice($a_liasses,0,$gi_nb_max_reponses);
}*/
if ($i_nb_liasses > 0) {
	function premier_elem($a_tab)
	{
		return $a_tab[0];
	}
	$a_idf_acte = array_map("premier_elem", $a_liasses);
	$gst_requete_intv = "select idf_acte,idf,sexe,patronyme, prenom from personne where idf_acte in (" . join(',', $a_idf_acte) . ") and idf_type_presence=" . IDF_PRESENCE_INTV . " order by idf_acte,idf";
	$etape_prec = getmicrotime();

	$a_tableau = array();
	foreach ($a_liasses as $a_liasse) {
		list(
			$i_mev, $st_cote_liasse, $st_nom_notaire, $st_prenom_notaire, $st_commentaire, $st_date_tri, $st_libelle_annees,
			$i_idf_forme_liasse, $i_idf_commune_etude, $st_info_compl, $i_liasse_consultable, $st_liasse_releve
		) = $a_liasse;
		if ($st_info_compl != '' || $i_liasse_consultable == 0) {
			$st_detail = "<a href=\"InfosLiasse.php?cote_liasse=$st_cote_liasse\" class=\"lien_infos_liasse\">" .
				"<img src=\"./$st_icone_info\" alt=\"info\" ></a>";
		} else {
			$st_detail = '';
		}
		if ($i_idf_commune_etude == '' || $i_idf_commune_etude == 0)
			$a_tableau[] = array($i_mev, $st_cote_liasse, $st_nom_notaire, $st_prenom_notaire, $st_commentaire, '', $st_libelle_annees, $i_idf_forme_liasse, $st_detail);
		else
			$a_tableau[] = array($i_mev, $st_cote_liasse, $st_nom_notaire, $st_prenom_notaire, $st_commentaire, array_key_exists($i_idf_commune_etude, $a_communes_acte) ? $a_communes_acte[$i_idf_commune_etude] : '', $st_libelle_annees, $i_idf_forme_liasse, $st_detail);
	}
	$pagination = new PaginationTableau(
		basename(__FILE__),
		'num_page',
		count($a_tableau),
		NB_LIGNES_PAR_PAGE,
		DELTA_NAVIGATION,
		array('Cote', 'Nom notaire', 'Pr&eacute;nom notaire', 'Commentaire', 'Commune &eacute;tude', 'Dates', 'Forme de liasse', '')
	);
	$pagination->init_page_cour($gi_num_page);
	$pagination->affiche_entete_liens_navlimite();
	$pagination->affiche_tableau_simple_mev($a_tableau);
	$pagination->affiche_entete_liens_navlimite();
} else {
	print('<div class="alert alert-danger">');
	print("Aucun r&eacute;sultat<br>");
	print("V&eacute;rifiez que vous n'avez pas mis trop de contraintes (commune,type d'acte,...)");
	print("Rappel de vos crit&egrave;res:");
	print(nl2br($st_criteres));
	print("</div>");
}

print('<div class="btn-group col-md-6 col-md-offset-3" role="group">');
print('<button type="button" id="retour_recherche" class="btn btn-primary"><span class="glyphicon glyphicon-search"></span> Revenir &agrave; la page de recherche</button>');
print('<button type="button" id="nouvelle_recherche" class="btn btn-warning"><span class="glyphicon glyphicon-erase"></span>  Commencer une nouvelle recherche</button>');
print('</div>');
print("</form>");
print("</div></body></html>");
