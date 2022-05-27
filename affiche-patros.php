<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------

require_once __DIR__ . '/app/bootstrap.php';
require_once __DIR__ . '/Commun/commun.php';
require_once __DIR__ . '/RequeteRecherche.php';
require_once __DIR__ . '/Commun/PaginationTableau.php';

// ======== Default
$gst_tri = $_GET['tri_pat'] ?? 'patronyme';
// ================

if (isset($_POST)) {
	$gi_idf_source = $_POST['idf_source'] ?? 0;
	$gi_num_page = $_POST['num_page_pat'] ?? 1;
	$gi_idf_commune = $_POST['idf_commune_patro'] ?? 0;
	$gi_rayon = $_POST['rayon_patro'] ?? 0;
	$gst_mode = $_POST['mode'] ?? 'DEMANDE';
	$gst_patronyme = $_POST['patronyme'] ?? '';
	$st_variantes = $_POST['variantes_pat'] ?? '';
}

/**
 *  Affiche le menu de demande
 */
function affiche_menu($gi_idf_commune, $gi_rayon, $gi_idf_source, $pst_msg)
{
	global $connexionBD;

	$a_communes_acte = $connexionBD->liste_valeur_par_clef("SELECT idf, nom FROM commune_acte ORDER BY nom");
	$a_sources = $connexionBD->liste_valeur_par_clef("SELECT idf, nom FROM source ORDER BY nom");
	$a_sources[0] = 'Toutes';
	$a_toutes_communes = array('' => 'Toutes') + $a_communes_acte;
	?>
		<form id="patros" method="post">
			<input type="hidden" name="mode" value="LISTE">
			<input type="hidden" name="idf_source" value=0>
			<?php if (!empty($pst_msg)) { ?>
				<div class="alert alert-danger"><?= $pst_msg; ?></div>
			<?php } ?>
			<div class="form-group row col-md-12">
				<label for="patronyme" class="col-form-label col-md-2">Patronyme</label>
				<div class="col-md-3 lib_erreur">
					<input type="text" name="patronyme" id="patronyme" size="15" maxlength="30" class="form-control">
				</div>
				<div class="form-check col-md-4">
					<input type="checkbox" name="variantes_pat" id="variantes_pat" value="oui" checked class="form-check-input">
					<label for="variantes_pat" class="form-check-label">Recherche par variantes connues</label>
				</div>
				<button type="submit" class="btn btn-primary col-md-3"><span class="glyphicon glyphicon-search"></span> Rechercher le patronyme</button>
			</div>
			<div class="form-group row col-md-12">
				<label for="idf_source" class="col-form-label col-md-2">Source</label>
				<div class="col-md-2">
					<select name=idf_source id=idf_source class="form-control">
						<?= chaine_select_options($gi_idf_source, $a_sources); ?>
					</select>
				</div>
				<label for="idf_commune_patro" class="col-form-label col-md-2">Commune/Paroisse</label>
				<div class="col-md-2">
					<select name="idf_commune_patro" id="idf_commune_patro" class="js-select-avec-recherche form-control">
						<?= chaine_select_options($gi_idf_commune, $a_toutes_communes); ?>
					</select>
				</div>
				<div class="form-group col-md-4">
					<div class="input-group">
						<span class="input-group-addon">Rayon de recherche:</span>
						<label for="rayon_patro" class="sr-only">Rayon</label>
						<div class="lib_erreur">
							<input type="text" name="rayon_patro" id="rayon_patro" size="2" maxlength="2" value="<?= $gi_rayon; ?>" class="form-control">
						</div>
						<span class="input-group-addon">Km</span>
					</div>
				</div>
		</form>
	<?php
}

?>
<!DOCTYPE html>

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
	<script type="text/javascript">
		$(document).ready(function() {

			$.fn.select2.defaults.set("theme", "bootstrap");

			$(".js-select-avec-recherche").select2();

			$('#patronyme').autocomplete({
				source: function(request, response) {
					$.getJSON("./ajax/patronyme_commune.php", {
							term: request.term
						},
						response);
				},
				minLength: 3
			});

			$("#patros").validate({
				rules: {
					patronyme: {
						required: true,
						minlength: 2
					},
					rayon_patro: {
						integer: true
					}
				},
				messages: {
					patronyme: {
						required: "Le patronyme est obligatoire",
						minlength: "Saisir au moins deux caract&egrave;res"
					},
					rayon_patro: {
						integer: "Le rayon doit &ecirc;tre un entier"
					}
				},
				errorElement: "em",
				errorPlacement: function(error, element) {
					// Add the `help-block` class to the error element
					error.addClass("help-block");

					// Add `has-feedback` class to the parent div.form-group
					// in order to add icons to inputs
					element.parents(".lib_erreur").addClass("has-feedback");

					if (element.prop("type") === "checkbox") {
						error.insertAfter(element.parent("label"));
					} else {
						error.insertAfter(element);
					}

					// Add the span element, if doesn't exists, and apply the icon classes to it.
					if (!element.next("span")[0]) {
						$("<span class='glyphicon glyphicon-remove form-control-feedback'></span>").insertAfter(element);
					}
				},
				success: function(label, element) {
					// Add the span element, if doesn't exists, and apply the icon classes to it.
					if (!$(element).next("span")[0]) {
						$("<span class='glyphicon glyphicon-ok form-control-feedback'></span>").insertAfter($(element));
					}
				},
				highlight: function(element, errorClass, validClass) {
					$(element).parents(".lib_erreur").addClass("has-error").removeClass("has-success");
					$(element).next("span").addClass("glyphicon-remove").removeClass("glyphicon-ok");
				},
				unhighlight: function(element, errorClass, validClass) {
					$(element).parents(".lib_erreur").addClass("has-success").removeClass("has-error");
					$(element).next("span").addClass("glyphicon-ok").removeClass("glyphicon-remove");
				}
			});
		});
	</script>

	<title>Base <?= SIGLE_ASSO; ?> : Recherche d'un patronyme</title>
</head>

<body>
	<div class="container">

		<?php require_once __DIR__ . '/Commun/menu.php';

		switch ($gst_mode) {
			case 'DEMANDE':
				affiche_menu($gi_idf_commune, $gi_rayon, $gi_idf_source, '');
				break;
			case 'LISTE':
				$gst_patronyme  = preg_replace('/\*+/', '%', $gst_patronyme);
				if (($gst_patronyme == '*') || (empty($gst_patronyme)) || (strlen($gst_patronyme) < 2))
					affiche_menu($gi_idf_commune, $gi_rayon, $gi_idf_source, "Le patronyme doit comporter au moins deux caractères");
				else {
					print("<div class=alignCenter><input type=hidden name=mode value=LISTE>");
					$requeteRecherche = new RequeteRecherche($connexionBD);
					switch ($gst_tri) {
						case 'patronyme':
							$st_tri_sql = ' ORDER BY p.libelle,ca.nom,ta.nom';
							break;
						case 'commune':
							$st_tri_sql = ' ORDER BY ca.nom,p.libelle,ta.nom';
							break;
						case 'type_acte':
							$st_tri_sql = ' ORDER BY ta.nom,p.libelle,ca.nom';
							break;
						case 'nb_actes':
							$st_tri_sql = ' ORDER BY sp.nb_personnes desc,ca.nom,p.libelle';
							break;
					}
					if (!empty($gi_idf_source))
						$st_requete = "SELECT p.libelle, sp.idf_commune, ca.nom, sp.idf_type_acte, ta.nom, sp.annee_min, sp.annee_max, sp.nb_personnes 
						FROM stats_patronyme sp 
						JOIN patronyme p ON (sp.idf_patronyme=p.idf) 
						JOIN commune_acte ca ON (sp.idf_commune=ca.idf) 
						JOIN type_acte ta ON (sp.idf_type_acte=ta.idf) 
						WHERE idf_source=$gi_idf_source 
						AND sp.idf_type_acte IN (" . IDF_MARIAGE . "," . IDF_CM . "," . IDF_NAISSANCE . "," . IDF_DECES . ") 
						AND p.libelle " . $requeteRecherche->clause_droite_patronyme($gst_patronyme, $st_variantes, 1);
					else
						$st_requete = "SELECT p.libelle, sp.idf_commune, ca.nom, sp.idf_type_acte, ta.nom, sp.annee_min, sp.annee_max, sp.nb_personnes 
						FROM stats_patronyme sp 
						JOIN patronyme p ON (sp.idf_patronyme=p.idf) 
						JOIN commune_acte ca ON (sp.idf_commune=ca.idf) 
						JOIN type_acte ta ON (sp.idf_type_acte=ta.idf) 
						WHERE sp.idf_type_acte IN (" . IDF_MARIAGE . "," . IDF_CM . "," . IDF_NAISSANCE . "," . IDF_DECES . ") 
						AND p.libelle " . $requeteRecherche->clause_droite_patronyme($gst_patronyme, $st_variantes, 1);
					if (!empty($gi_idf_commune))
						$st_requete .=  " AND sp.idf_commune " . $requeteRecherche->clause_droite_commune($gi_idf_commune, $gi_rayon, 'oui');
					$st_requete .= $st_tri_sql;
					$a_liste_stats = $connexionBD->sql_select_multiple($st_requete);
					$i_nb_stats = count($a_liste_stats);
					if ($i_nb_stats != 0) {
						print("<form name=\"Patros\"  method=\"post\">");
						$pagination = new PaginationTableau(basename(__FILE__), 'num_page_pat', $i_nb_stats, NB_LIGNES_PAR_PAGE, DELTA_NAVIGATION, array("<a href=\"" . basename(__FILE__) . "?tri_pat=patronyme\">Patronyme</a>", "<a href=\"" . basename(__FILE__) . "?tri_pat=commune\">Commune</a>", "<a href=\"" . basename(__FILE__) . "?tri_pat=type_acte\">Type d'acte</a>", 'Ann&eacute;e minimale', 'Ann&eacute;e maximale', "<a href=\"" . basename(__FILE__) . "?tri_pat=nb_actes\">Nombre de personnes</a>"));
						$a_tableau_affichage = array();
						foreach ($a_liste_stats as $a_stat_patro) {
							list($st_patronyme, $i_idf_commune, $st_commune, $i_idf_type_acte, $st_type_acte, $i_annee_min, $i_annee_max, $i_nb_pers) = $a_stat_patro;
							if ($gi_idf_source != 0)
								$st_lien_patronyme = "<a href=\"" . PAGE_RECHERCHE . "?recherche=nouvelle&amp;idf_src=$gi_idf_source&amp;idf_ca=$i_idf_commune&amp;idf_ta=$i_idf_type_acte&amp;var=N&amp;nom=$st_patronyme\">$st_patronyme</a>";
							else
								$st_lien_patronyme = "<a href=\"" . PAGE_RECHERCHE . "?recherche=nouvelle&amp;idf_ca=$i_idf_commune&amp;idf_ta=$i_idf_type_acte&amp;a_min=$i_annee_min&amp;a_max=$i_annee_max&amp;var=N&amp;nom=$st_patronyme\">$st_patronyme</a>";
							$a_tableau_affichage[] = array($st_lien_patronyme, $st_commune, $st_type_acte, $i_annee_min, $i_annee_max, $i_nb_pers);
						}
						$pagination->init_page_cour($gi_num_page);
						$pagination->affiche_entete_liste_select('Patros');
						$pagination->affiche_tableau_simple($a_tableau_affichage);
						$pagination->affiche_entete_liste_select('Patros');
						print("</form>");
					} else
						print("<div class=\"text-center alert alert-danger\">Pas de donn&eacute;es</div>\n");

					print("<form  method=\"post\">");
					print("<input type=hidden name=mode value=\"DEMANDE\">");
					print('<div class="form-group row"><button type="submit" class="btn btn-primary col-md-4 col-md-offset-4"><span class="glyphicon glyphicon-erase"></span> Rechercher un autre patronyme</button></div>');
					print("</form>");
				}
				break;
			default:
				affiche_menu($gi_idf_commune, $gi_rayon, '');
		}
		?>
	</div>
</body>

</html>