<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------

require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../commun/benchmark.php';
require_once __DIR__ . '/../libs/phonex.cls.php';
require_once __DIR__ . '/../libs/finediff.php';
require_once __DIR__ . '/chargement/chargement.php';
require_once __DIR__ . '/../Origin/CompteurActe.php';
require_once __DIR__ . '/../Origin/Acte.php';
require_once __DIR__ . '/../Origin/CompteurPersonne.php';
require_once __DIR__ . '/../Origin/Personne.php';
require_once __DIR__ . '/../Origin/CommunePersonne.php';
require_once __DIR__ . '/../Origin/Patronyme.php';
require_once __DIR__ . '/../Origin/Prenom.php';
require_once __DIR__ . '/../Origin/Profession.php';
require_once __DIR__ . '/../Origin//TypeActe.php';
require_once __DIR__ . '/../Origin/Union.php';
require_once __DIR__ . '/../Origin/StatsPatronyme.php';
require_once __DIR__ . '/../Origin/StatsCommune.php';

// ========== check auth
if (!$session->isAuthenticated()) {
    $session->setAttribute('url_retour', '/administration/gestion-communes.php');
    header('HTTP/1.0 401 Unauthorized');
    header('Location: /se-connecter.php');
    exit;
}

// ========== Check permissions
if (!in_array('CHGMT_EXPT', $user['privileges'])) {
    header('HTTP/1.0 401 Unauthorized');
    exit;
}

// ========== Default

// ========== Request
$gst_mode = isset($_REQUEST['MODE']) ? $_REQUEST['MODE'] : '';

if (isset($_REQUEST['idf_acte'])) {
	//+ vérification que l'acte a bien été demandé par l'adhérent connecté
	$gi_idf_acte = (int) $_REQUEST['idf_acte'];
	$go_acte = new Acte($connexionBD, null, null, null, null, null, null);
	$a_filtres_acte = array();
	$go_acte->setFiltresParametres($a_filtres_acte);
	$go_acte->charge($gi_idf_acte);
	if (empty($gst_mode)) {
		$gst_formulaire = $go_acte->affichage_image_permalien(800, 800);
		$gst_formulaire .= $go_acte->formulaire_haut_acte();
		$gst_formulaire .= $go_acte->formulaire_liste_personnes();
		$gst_formulaire .= $go_acte->formulaire_bas_acte();
	}
}

/**
* Construit la chaine permettant la validation des paramètres d'un formulaire
* @return string règles de validation
*/
function regles_validation()
{
	global $go_acte;
	$a_filtres = $go_acte->getFiltresParametres();
	$ga_liste_personnes = $go_acte->getListePersonnes();
	$a_messages = array();
	$st_chaine = '';
	foreach ($ga_liste_personnes as $o_pers) {
		foreach ($o_pers->getFiltresParametres() as $st_param => $a_filtres_personne) {
			$a_filtres[$st_param] = $a_filtres_personne;
		}
	}
	foreach ($a_filtres as $st_param => $a_liste_tests) {
		$st_test =	"\t$st_param: { ";
		$st_message = "\t$st_param: { ";
		$a_tests = array();
		$a_msgs = array();
		foreach ($a_liste_tests as $a_test) {
			list($st_type_test, $st_valeur_test, $st_message_erreur) = $a_test;
			$a_tests[] = "\t\t$st_type_test: $st_valeur_test";
			$a_msgs[] = "\t\t$st_type_test: \"$st_message_erreur\"";
		}
		$st_test .= implode(",\n", $a_tests);
		$st_test .= "\n\t}";
		$st_message .= implode(",\n", $a_msgs);
		$st_message .= "\n\t}";
		$a_regles[] = $st_test;
		$a_messages[] = $st_message;
	}
	$st_chaine =	"rules: {\n" . implode(",\n", $a_regles) . "},\n";
	$st_chaine .= "messages: {\n" . implode(",\n", $a_messages) . "}\n";
	return  $st_chaine;
}


?>
<!DOCTYPE html>
<html lang="fr">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link href='../assets/css/styles.css' type='text/css' rel='stylesheet'>
	<link href='../assets/css/bootstrap.min.css' rel='stylesheet'>;
	<link href='../assets/css/jquery-ui.css' type='text/css' rel='stylesheet'>
	<link href='../assets/css/jquery-ui.structure.min.css' type='text/css' rel='stylesheet'>
	<link href='../assets/css/jquery-ui.theme.min.css' type='text/css' rel='stylesheet'>
	<meta http-equiv="content-language" content="fr">
	<script src='../assets/js/jquery-min.js' type='text/javascript'></script>
	<script src='../assets/js/jquery.validate.min.js' type='text/javascript'></script>
	<script src='../assets/js/additional-methods.min.js' type='text/javascript'></script>
	<script src='../assets/js/jquery-ui.min.js' type='text/javascript'></script>
	<script src='../assets/js/CalRep.js' type='text/javascript'></script>
	<script src='../assets/js/iviewer/jquery-ui.min.js' type='text/javascript'></script>
	<script src='../assets/js/iviewer/jquery.mousewheel.min.js' type='text/javascript'></script>
	<script src='../assets/js/iviewer/jquery.iviewer.js' type='text/javascript'></script>
	<link href='../assets/js/iviewer/jquery.iviewer.css' type='text/css' rel='stylesheet'>
	<script src='../assets/js/bootstrap.min.js' type='text/javascript'></script>
	<script type='text/javascript'>
		$(document).ready(function() {
			<?php
			print file_get_contents('../js/dateITA.js');
			?>
			$("#edition_acte").validate({
				<?php
				print regles_validation();
				?>,
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
			$(function() {
				$("#bouton_supprimer").click(function() {
					if (confirm("Confirmer la suppression?")) {
						$('#suppression_acte').submit();
					}
				});
			});
			<?php
			//print parametres_completion_auto();
			print $go_acte->fonctions_jquery_completion();
			print file_get_contents('../js/EditionActe.js');
			?>
		});
	</script>
	<?php
	print("<title>Modification d'un acte</title>");
	print("</head>\n");
	print("<body>\n");
	print('<div class="container">');

	require_once __DIR__ . '/../commun/menu.php';

	print('<div class="panel panel-primary">');
	print("<div class=\"panel-heading\">Modification d'un acte</div>");
	print('<div class="panel-body">');

	if (empty($gst_mode)) {
		print("<form id=\"edition_acte\" method=\"POST\" >");
		print("<input type=\"hidden\" name=\"MODE\" value=\"EDITION\">");
		print("<input type=\"hidden\" name=\"idf_acte\" value=\"$gi_idf_acte\">");
		print("<table class=\"table table-bordered\">");
		print($gst_formulaire);
		print("</table>");
		print('<button type="submit" class="btn btn-primary col-md-4 col-md-offset-4"><span class="glyphicon glyphicon-edit"></span> Modifier l\'acte</button>');
		print("</form>");
		print("<form id=\"suppression_acte\" name=\"suppression_acte\" method=\"POST\" >");
		print("<input type=\"hidden\" name=\"idf_acte\" value=\"$gi_idf_acte\">\n");
		print("<input type=\"hidden\" name=\"MODE\" value=\"SUPPRESSION\">");
		print('<button type="button" class="btn btn-danger col-md-4 col-md-offset-4" id="bouton_supprimer"><span class="glyphicon glyphicon-trash"></span> Supprimer l\'acte</button>');

		print("</form>");
	} else {
		$gi_idf_acte = isset($_REQUEST['idf_acte']) ? (int) $_REQUEST['idf_acte'] :  null;
		if (empty($gi_idf_acte)) {
			print("<div class=\"alert alert-danger\">Pas d'identifiant d'acte d&eacute;fini</div>");
		} else {

			$stats_commune = new StatsCommune($connexionBD, $go_acte->getIdfCommune(), $go_acte->getIdfSource());
			$unions = Union::singleton($connexionBD);
			switch ($gst_mode) {
				case 'EDITION':
					$go_acte->initialise_depuis_formulaire($gi_idf_acte);
					$st_requete = "LOCK TABLES `personne` write, `patronyme` as pat read, `patronyme` write, `prenom` write  ,`acte` write, `profession` write, `commune_personne` write, `union` write,`stats_patronyme` as sp read,`stats_patronyme` write,`stats_commune` write,`acte` as a read,`personne` as p read, `type_acte` read, `type_acte` as ta read,`prenom_simple` write, `groupe_prenoms` write";
					$connexionBD->execute_requete($st_requete);
					$etape_prec = getmicrotime();
					$go_acte->maj_liste_personnes($go_acte->getIdfSource(), $go_acte->getIdfCommune(), $unions);
					print benchmark("Mise à jour des personnes et statistiques de patronyme");
					$go_acte->sauve();
					$stats_commune->maj_stats($go_acte->getIdfTypeActe());
					print benchmark("Mise &agrave jour des statistiques des communes");
					$connexionBD->execute_requete("UNLOCK TABLES");
					print("<div class=\"text-center\"><textarea rows=40 cols=80>\n");
					print($go_acte->versChaine());
					print("</textarea></div>\n");
					print("<div class=\"alert alert-success\"><br>Modification effectu&eacute;e</div><br>\n");
					print("<form id=\"export_nimv3\" method=\"POST\" action=\"ExportNimV3.php\">");
					print("<input type=\"hidden\" name=\"idf_acte\" value=\"$gi_idf_acte\">\n");
					print('<button type="submit" class="btn btn-primary col-md-4 col-md-offset-4"><span class="glyphicon glyphicon-download-alt"></span> Export Nimegue V3</button>');
					print("</form>");

					break;
				case 'SUPPRESSION':
					$go_acte = new Acte($connexionBD, null, null, null, null, null, null);
					$go_acte->charge($gi_idf_acte);
					$st_requete = "LOCK TABLES `personne` write,`personne` as p read,`acte` write,`acte` as a write,`union` write,`stats_patronyme` write,`stats_patronyme` as sp read,`stats_commune` write,`type_acte` as ta read,`patronyme` as pat read";
					$etape_prec = getmicrotime();
					$connexionBD->execute_requete($st_requete);
					$go_acte->supprime_personnes();
					print benchmark("Suppression des personnes et mise à jour des statistiques de patronyme");
					$connexionBD->execute_requete("DELETE FROM `acte`where idf=$gi_idf_acte");
					print benchmark("Suppression de l'acte");
					$stats_commune->maj_stats($go_acte->getIdfTypeActe());
					print benchmark("Mise &agrave jour des statistiques des communes");
					$connexionBD->execute_requete("UNLOCK TABLES");
					print("<div class=\"alert alert-success text-center\">Acte supprim&eacute;</div>");
					break;
			}
		}
	}
	print("</div></div>");
	print("<a href=\"/recherche.php\" class=\"btn btn-primary col-md-4 col-md-offset-4\"><span class=\"glyphicon glyphicon-search\"></span> Retour au menu recherche</a>");
	print("</div></body></html>\n");
