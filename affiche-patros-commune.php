<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------
require_once __DIR__ . '/app/bootstrap.php';
require_once __DIR__ . '/Origin/PaginationTableau.php';


$i_get_idf_source = $_GET['idf_source'] ?? 1;
$i_get_idf_commune = $_GET['idf_commune'] ?? 1;
$i_get_idf_type_acte = $_GET['idf_type_acte'] ?? -1;

if (isset($_POST)) {
	$gi_idf_source = $_POST['idf_source'] ?? $i_get_idf_source;
	$gi_idf_commune = $_POST['idf_commune'] ?? $i_get_idf_commune;
	$gi_idf_type_acte = $_POST['idf_type_acte'] ?? $i_get_idf_type_acte;
	$gi_num_page = $_POST['num_page_patcom'] ?? 1;
	$gst_patronyme = $_POST['patro_patcom'] ?? '';
}

$gst_patronyme = str_replace('*', '%', $gst_patronyme);
if (preg_match('/\%/', $gst_patronyme))
	$gst_clause_patronyme = "LIKE :patro";
else
	$gst_clause_patronyme = "=:patro";

$a_sources = $connexionBD->liste_valeur_par_clef("SELECT idf, nom FROM source ORDER BY nom");
if (empty($gi_idf_source))
	$a_communes = $connexionBD->liste_valeur_par_clef("SELECT idf, nom FROM commune_acte ORDER BY nom");
else
	$a_communes = $connexionBD->liste_valeur_par_clef("SELECT DISTINCT ca.idf, ca.nom FROM commune_acte ca JOIN stats_commune sc ON (sc.idf_commune=ca.idf) WHERE sc.idf_source=$gi_idf_source ORDER BY ca.nom");

if (!array_key_exists($gi_idf_commune, $a_communes)) {
	if (count($a_communes) > 0) {
		$a_idf_commune = array_keys($a_communes);
		$gi_idf_commune = $a_idf_commune[0];
	} else
		$gi_idf_commune = 0;
}

$a_types_acte_dispo = [];

if (!empty($gi_idf_commune))
	$a_types_acte_dispo = $connexionBD->sql_select("SELECT DISTINCT idf_type_acte FROM stats_commune WHERE idf_commune=$gi_idf_commune AND idf_source=$gi_idf_source");

if (count($a_types_acte_dispo) != 0)
	$a_types_acte = $connexionBD->liste_valeur_par_clef("SELECT idf, nom FROM type_acte WHERE idf IN (" . implode(',', $a_types_acte_dispo) . ") ORDER BY nom");
else
	$a_types_acte = array();
$a_types_acte[-1] = "Tous"; // TODO: what the f** is that? an array cannot be a string!!!!

if (!in_array($gi_idf_type_acte, $a_types_acte_dispo))
	$gi_idf_type_acte = -1;

?>
<!DOCTYPE html>
<html lang="fr">

<head>
	<link rel="shortcut icon" href="assets/img/favicon.ico">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta http-equiv="content-language" content="fr">
	<link href="assets/css/styles.css" type="text/css" rel="stylesheet">
	<link href="assets/css/bootstrap.min.css" type="text/css" rel="stylesheet">
	<link href="assets/css/jquery-ui.css" type="text/css" rel="stylesheet">
	<link href="assets/css/jquery-ui.structure.min.css" type="text/css" rel="stylesheet">
	<link href="assets/css/jquery-ui.theme.min.css" type="text/css" rel="stylesheet">
	<link href="assets/css/select2.min.css" type="text/css" rel="stylesheet">
	<link href="assets/css/select2-bootstrap.min.css" type="text/css" rel="stylesheet">
	<script src="assets/js/jquery-min.js" type="text/javascript"></script>
	<script src="assets/js/jquery-ui.min.js" type="text/javascript"></script>
	<script src="assets/js/select2.min.js" type="text/javascript"></script>
	<script src="assets/js/jquery.validate.min.js" type="text/javascript"></script>
	<script src="assets/js/additional-methods.min.js" type="text/javascript"></script>
	<script src="assets/js/bootstrap.min.js" type="text/javascript"></script>
	<script type='text/javascript'>
		$(document).ready(function() {
			$('#patro_patcom').autocomplete({
				source: function(request, response) {
					$.getJSON("./ajax/patronyme_commune.php", {
							term: request.term,
							idf_commune: $('#idf_commune').val(),
							idf_source: $('#idf_source').val(),
							idf_type_acte: $('#idf_type_acte').val()
						},
						response);
				},
				minLength: 3
			});

			$.fn.select2.defaults.set("theme", "bootstrap");

			$(".js-select-avec-recherche").select2();

			$("#idf_source").change(function() {
				this.form.submit();
			});

			$("#idf_commune").change(function() {
				this.form.submit();
			});

			$("#idf_type_acte").change(function() {
				this.form.submit();
			});

		});
	</script>

	<title>Base <?= SIGLE_ASSO; ?> : Patronymes par communes</title>
</head>

<body>
	<div class="container">

		<?php require_once __DIR__ . '/commun/menu.php'; ?>

		<form name="PatrosCommune" method="post">
			<div class="form-row col-md-12">
				<label for="idf_source" class="col-form-label col-md-2 col-md-offset-3">Source:</label>
				<div class="col-md-4 ">
					<select name="idf_source" id="idf_source" class="js-select-avec-recherche form-control">
						<?= chaine_select_options($gi_idf_source, $a_sources); ?>
					</select>
				</div>
			</div>

			<div class="form-row col-md-12">
				<label for="idf_commune" class="col-form-label col-md-2 col-md-offset-3">Commune:</label>
				<div class="col-md-4">
					<select name=idf_commune id=idf_commune class="js-select-avec-recherche form-control">
						<?= chaine_select_options($gi_idf_commune, $a_communes); ?>
					</select>
				</div>
			</div>

			<div class="form-row col-md-12">
				<label for="idf_type_acte" class="col-form-label col-md-2 col-md-offset-3">Type d'acte:</label>
				<div class="col-md-4">
					<select name="idf_type_acte" id="idf_type_acte" class="js-select-avec-recherche form-control">
						<?= chaine_select_options($gi_idf_type_acte, $a_types_acte); ?>
					</select>
				</div>
			</div>

			<div class="form-row col-md-12">
				<label for="patro_patcom" class="col-form-label col-md-2 col-md-offset-3">Patronyme:</label>
				<div class="col-md-4">
					<input type="text" id="patro_patcom" name="patro_patcom" size="15" maxlength="30" value="<?= $gst_patronyme; ?>" class="form-control" aria-describedby="aideCommune">
					<small id="aideCommune" class="form-text text-muted">laisser * si aucun patronyme choisi</small>
				</div><button type="submit" class="btn btn-primary"><span class="glyphicon glyphicon-search"></span> Chercher</button>
			</div>

			<?php if (empty($gi_idf_commune)) {
				print("<div class=\"form-row col-md-12\"><div class=\"text-center alert alert-danger\">Pas de données</div></div>");
			} else if ($gi_idf_type_acte == -1) {
				// Calcul de la liste des initiales
				if ($gst_patronyme == '')
					$st_requete = "SELECT DISTINCT (left( p.libelle, 1 )) AS init 
					FROM `stats_patronyme` sp join `patronyme` p ON (sp.idf_patronyme=p.idf) 
					WHERE sp.idf_source=$gi_idf_source 
					AND sp.idf_commune=$gi_idf_commune 
					ORDER BY init";
				else
					$st_requete = "SELECT DISTINCT (left( p.libelle, 1 )) AS init 
					FROM `stats_patronyme` sp join `patronyme` p ON (sp.idf_patronyme=p.idf) 
					WHERE sp.idf_source=$gi_idf_source 
					AND sp.idf_commune=$gi_idf_commune 
					AND p.libelle $gst_clause_patronyme 
					ORDER BY init";
				$connexionBD->initialise_params(array(":patro" => utf8_vers_cp1252($gst_patronyme)));
				//print("Req=$st_requete< br>");
				$a_initiales_patronymes = $connexionBD->sql_select($st_requete);
				if (count($a_initiales_patronymes) > 0) {
					print('<div class="text-center"><ul class="pagination">');

					$st_patro = $a_initiales_patronymes[0] ?? '';
					$gc_initiale = $_GET['initiale_patcom'] ?? $st_patro;

					if (!in_array(utf8_vers_cp1252($gc_initiale), $a_initiales_patronymes)) {
						$gc_initiale = array_key_exists(0, $a_initiales_patronymes) ? $a_initiales_patronymes[0] : 'A';
						$gi_num_page = 1;
					}
					// Affichage de la liste des initiales des patronymes  
					foreach ($a_initiales_patronymes as $c_initiale) {
						if ($c_initiale == utf8_vers_cp1252($gc_initiale))
							print("<li class=\"page-item active\"><span class=\"page-link\">" . cp1252_vers_utf8($c_initiale) . "<span class=\"sr-only\">(current)</span></span></li>");
						else
							print("<li class=\"page-item\"><a href=\"" . basename(__FILE__) . "?initiale_patcom=" . cp1252_vers_utf8($c_initiale) . "\">" . cp1252_vers_utf8($c_initiale) . "</a></li>");
					}
					print("</ul></div>");
				} else
					$gc_initiale = "\%";
				if ($gst_patronyme == '')
					$st_requete = "SELECT p.libelle, sp.idf_type_acte, ta.nom, sp.annee_min, sp.annee_max, sp.nb_personnes 
					FROM stats_patronyme sp 
					JOIN `patronyme` p ON (sp.idf_patronyme=p.idf) 
					JOIN type_acte ta ON (sp.idf_type_acte=ta.idf) 
					WHERE sp.idf_source=$gi_idf_source 
					AND sp.idf_commune=$gi_idf_commune 
					AND p.libelle LIKE '" . utf8_vers_cp1252($gc_initiale) . "%' 
					ORDER BY p.libelle,ta.nom";
				else {
					$st_requete = "SELECT p.libelle, sp.idf_type_acte, ta.nom, sp.annee_min, sp.annee_max, sp.nb_personnes 
					FROM stats_patronyme sp 
					JOIN `patronyme` p ON (sp.idf_patronyme=p.idf) 
					JOIN type_acte ta ON (sp.idf_type_acte=ta.idf) 
					WHERE sp.idf_source=$gi_idf_source 
					AND sp.idf_commune=$gi_idf_commune 
					AND p.libelle LIKE '" . utf8_vers_cp1252($gc_initiale) . "%' 
					AND p.libelle $gst_clause_patronyme 
					ORDER BY p.libelle,ta.nom";
					$connexionBD->initialise_params(array(":patro" => utf8_vers_cp1252($gst_patronyme)));
				}
				$a_liste_stats = $connexionBD->sql_select_multiple($st_requete);
				// Affichage des patronymes correspondants
				$i_nb_stats = count($a_liste_stats);
				if ($i_nb_stats != 0) {
					$pagination = new PaginationTableau(basename(__FILE__), 'num_page_patcom', $i_nb_stats, NB_LIGNES_PAR_PAGE, DELTA_NAVIGATION, array('Patronyme', 'Type d\'acte', 'Ann&eacute;e minimale', 'Ann&eacute;e maximale', 'Nombre d\'occurrences'));
					$a_tableau_affichage = array();
					foreach ($a_liste_stats as $a_stat_patro) {
						list($st_patronyme, $i_idf_type_acte, $st_type_acte, $i_annee_min, $i_annee_max, $i_nb_pers) = $a_stat_patro;
						$a_tableau_affichage[] = array("<a href=\"/recherche.php?recherche=nouvelle&amp;idf_src=$gi_idf_source&amp;idf_ca=$gi_idf_commune&amp;idf_ta=$i_idf_type_acte&amp;a_min=$i_annee_min&amp;a_max=$i_annee_max&amp;var=N&amp;nom=$st_patronyme\">$st_patronyme</a>", $st_type_acte, $i_annee_min, $i_annee_max, $i_nb_pers);
					}
					$pagination->init_page_cour($gi_num_page);
					$pagination->affiche_entete_liste_select('PatrosCommune');
					$pagination->affiche_tableau_simple($a_tableau_affichage);
					$pagination->affiche_entete_liste_select('PatrosCommune');
				} else
					print("<div class=\"form-row col-md-12\"><div class=\"text-center alert alert-danger\">Pas de donn&eacute;es</div></div>\n");
			} else {
				// Calcul de la liste des initiales
				if ($gst_patronyme == '')
					$st_requete = "SELECT DISTINCT (left( p.libelle, 1 )) AS init 
					FROM `stats_patronyme` sp 
					JOIN `patronyme` p ON (sp.idf_patronyme=p.idf) 
					WHERE sp.idf_source=$gi_idf_source 
					AND sp.idf_commune=$gi_idf_commune 
					AND sp.idf_type_acte=$gi_idf_type_acte 
					ORDER BY init";
				else {
					$st_requete = "SELECT DISTINCT (left( p.libelle, 1 )) AS init 
					FROM `stats_patronyme` sp 
					JOIN `patronyme` p ON (sp.idf_patronyme=p.idf)
					WHERE sp.idf_source=$gi_idf_source 
					AND sp.idf_commune=$gi_idf_commune 
					AND sp.idf_type_acte=$gi_idf_type_acte 
					AND p.libelle $gst_clause_patronyme 
					ORDER BY init";
					$connexionBD->initialise_params(array(":patro" => utf8_vers_cp1252($gst_patronyme)));
				}
				$a_initiales_patronymes = $connexionBD->sql_select($st_requete);
				if (count($a_initiales_patronymes) > 0) {
					print('<div class="text-center"><ul class="pagination">');

					$gc_initiale = $_GET['initiale_patcom'] ?? $a_initiales_patronymes[0];

					if (!in_array(utf8_vers_cp1252($gc_initiale), $a_initiales_patronymes)) {
						$gc_initiale = $a_initiales_patronymes[0];
						$gi_num_page = 1;
					}
					// Affichage de la liste des initiales des patronymes  
					foreach ($a_initiales_patronymes as $c_initiale) {
						if ($c_initiale == utf8_vers_cp1252($gc_initiale))
							print("<li class=\"page-item active\"><span class=\"page-link\">" . cp1252_vers_utf8($c_initiale) . "<span class=\"sr-only\">(current)</span></span></li>");
						else
							print("<li class=\"page-item\"><a href=\"" . basename(__FILE__) . "?initiale_patcom=" . cp1252_vers_utf8($c_initiale) . "\">" . cp1252_vers_utf8($c_initiale) . "</a></li>");
					}
					print("</ul></div>");
				} else
					$gc_initiale = "\%";
				if ($gst_patronyme == '')
					$st_requete = "SELECT p.libelle, sp.annee_min, sp.annee_max, sp.nb_personnes 
					FROM stats_patronyme sp 
					JOIN `patronyme` p ON (sp.idf_patronyme=p.idf) 
					WHERE sp.idf_source=$gi_idf_source 
					AND sp.idf_commune=$gi_idf_commune 
					AND sp.idf_type_acte=$gi_idf_type_acte 
					AND p.libelle LIKE '" . utf8_vers_cp1252($gc_initiale) . "%' 
					ORDER BY p.libelle";
				else {
					$st_requete = "SELECT p.libelle, sp.annee_min, sp.annee_max, sp.nb_personnes 
					FROM stats_patronyme sp 
					JOIN `patronyme` p ON (sp.idf_patronyme=p.idf) 
					WHERE sp.idf_source=$gi_idf_source 
					AND sp.idf_commune=$gi_idf_commune 
					AND sp.idf_type_acte=$gi_idf_type_acte 
					AND p.libelle LIKE '" . utf8_vers_cp1252($gc_initiale) . "%' 
					AND p.libelle $gst_clause_patronyme 
					ORDER BY p.libelle";
					$connexionBD->initialise_params(array(":patro" => utf8_vers_cp1252($gst_patronyme)));
				}
				$a_liste_stats = $connexionBD->sql_select_multiple($st_requete);
				$i_nb_stats = count($a_liste_stats);
				if ($i_nb_stats != 0) {
					$pagination = new PaginationTableau(basename(__FILE__), 'num_page_patcom', $i_nb_stats, NB_LIGNES_PAR_PAGE, DELTA_NAVIGATION, array('Patronyme', 'Ann&eacute;e minimale', 'Ann&eacute;e maximale', 'Nombre d\'occurrences'));
					$a_tableau_affichage = array();
					foreach ($a_liste_stats as $a_stat_patro) {
						list($st_patronyme, $i_annee_min, $i_annee_max, $i_nb_pers) = $a_stat_patro;
						$a_tableau_affichage[] = array("<a href=\"/recherche.php?recherche=nouvelle&idf_src=$gi_idf_source&idf_ca=$gi_idf_commune&idf_ta=	$gi_idf_type_acte&a_min=$i_annee_min&a_max=$i_annee_max&var=N&nom=$st_patronyme\">$st_patronyme</a>", $i_annee_min, $i_annee_max, $i_nb_pers);
					}
					$pagination->init_page_cour($gi_num_page);
					$pagination->affiche_entete_liste_select('PatrosCommune');
					$pagination->affiche_tableau_simple($a_tableau_affichage);
					$pagination->affiche_entete_liste_select('PatrosCommune');
				} else
					print("<div class=\"form-row col-md-12\"><div class=\"text-center alert alert-danger\">Pas de données</div></div>\n");
			} ?>
		</form>
	</div>
</body>

</html>