<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------
require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../libs/phonex.cls.php';

// Redirect to identification
if (!$session->isAuthenticated()) {
    $session->setAttribute('url_retour', '/administration/gestion-communes.php');
    header('HTTP/1.0 401 Unauthorized');
    header('Location: /se-connecter.php');
    exit;
}
if (!in_array('UTILITAIRE', $user['privileges'])) {
    header('HTTP/1.0 401 Unauthorized');
    exit;
}


$gst_get_mode = empty($_GET['mode']) ? 'AFFICHER' : $_GET['mode'];
$gst_mode = empty($_POST['mode']) ? $gst_get_mode : $_POST['mode'];
$gi_idf_groupe = empty($_POST['idf_groupe']) ? '' : $_POST['idf_groupe'];
$gst_infos = '';
$gst_erreurs = '';

switch ($gst_mode) {
	case 'EXPORT':
		header("Content-type: text/csv");
		header("Expires: 0");
		header("Pragma: public");
		header("Content-disposition: attachment; filename=\"VariantesNimegue.txt\"");
		$fh = @fopen('php://output', 'w');
		//$ga_patronymes = $connexionBD->sql_select("select distinct p.libelle from `stats_patronyme` sp join patronyme p on (sp.idf_patronyme=p.idf) where p.libelle REGEXP '^[A-Z \?\(\)ÂÄÇÉÈÊËÎÏÔÖÙÚÛ]+$' and p.libelle not in (select patronyme from `variantes_patro`) order by p.libelle");
		$ga_patronymes = $connexionBD->sql_select("select distinct p.libelle from `stats_patronyme` sp join patronyme p on (sp.idf_patronyme=p.idf) where  p.libelle not in (select patronyme from `variantes_patro`) order by p.libelle");
		$gh_variantes = array();
		$oPhonex = new phonex;
		foreach ($ga_patronymes as $st_patronyme) {
			if (empty($st_patronyme))
				continue;
			$st_patronyme = cp1252_vers_utf8($st_patronyme);
			$oPhonex->build($st_patronyme);
			$sPhonex = $oPhonex->sString;
			if (array_key_exists($sPhonex, $gh_variantes)) {
				$a_variantes = $gh_variantes[$sPhonex];
				$a_variantes[] = $st_patronyme;
				$gh_variantes[$sPhonex] = $a_variantes;
			} else
				$gh_variantes[$sPhonex] = array($st_patronyme);
		}
		foreach ($gh_variantes as $st_soundex => $a_variantes) {
			if (count($a_variantes) > 1) {
				$st_majeure = array_shift($a_variantes);
				foreach ($a_variantes as $st_patro) {
					fputcsv($fh, array("NIMEGUEV3", "H", $st_majeure, $st_patro, ''), SEP_CSV);
				}
			}
		}
		fclose($fh);
		exit();
		break;
}

/**
 * Affiche le menu formulaire
 * @param object $pconnexionBD  Connexion à la base
 * @param integer $pi_idf_groupe Identifiant du groupe à ajouter 
 * @global string $gst_infos Chaine d'infos (succes)
 * @global string $gst_erreurs (danger)
 */
function affiche_menu($pconnexionBD, $pi_idf_groupe)
{
	global $gst_infos, $gst_erreurs;

	print('<form id="variantes_patro" method="post">');
	print('<input type="hidden" name="mode" id="mode" value="EXPORT">');
	print("<input type=\"hidden\" name=\"idf_groupe\" id=\"idf_groupe\" value=\"$pi_idf_groupe\">");
	if (!empty($gst_infos))
		print("<div id=\"infos\" class=\"alert alert-success\">$gst_infos</div>");
	if (!empty($gst_erreurs))
		print("<div id=\"erreurs\" class=\"alert alert-danger\">$gst_erreurs</div>");
	print("<div class='row col-md-12'>");
	print('<div class="col-md-4">');
	print('<div id="cmt_retour"></div>');

	print('<div class="form-group row">');
	print("<label for=\"variante_a_chercher\" class=\"col-form-label col-md-2\">Variante &agrave; chercher:</label>");
	print("<div class='col-md-10'>");
	print("<input type=text name=\"variante_a_chercher\" id=\"variante_a_chercher\" value=\"\" class=\"form-control\" size=30>");
	print("</div>");
	print('</div>');

	print('<div class="form-group row">');
	print("<label for=\"majeure_a_chercher\" class=\"col-form-label col-md-2\">ou bien majeure &agrave; chercher:</label>");
	print("<div class='col-md-10'>");
	print('<select name=majeure_a_chercher id=majeure_a_chercher class="form-control">');
	print('</select>');
	print("</div>");
	print('</div>');

	$st_majeure = empty($pi_idf_groupe) ?  '' : $pconnexionBD->sql_select1("select patronyme from variantes_patro where idf_groupe=$pi_idf_groupe and majeure=1");
	print('<div class="form-group row">');
	print("<label for=\"majeure\" class=\"col-form-label col-md-2\">Majeure trouv&eacute;e</label>");
	print("<div class='col-md-10'>");
	print("<input type=text name=\"majeure\" id=\"majeure\" class=\"form-control\" size=30 value=\"" . cp1252_vers_utf8($st_majeure) . "\">");
	print("</div>");
	print('</div>');

	print("<div class='row col-md-12'>");
	print("<textarea rows=20 cols=40 id=\"variantes\" name=\"variantes\" class=\"form-control col-md-8 col-md-offset-2\">");

	if (!empty($pi_idf_groupe)) {
		$a_variantes = $pconnexionBD->sql_select("select patronyme from variantes_patro where idf_groupe=$pi_idf_groupe and majeure=0 order by patronyme");
		foreach ($a_variantes as $st_variante) {
			print(cp1252_vers_utf8($st_variante) . "\n");
		}
	}
	print("</textarea>");
	print("</div>");
	print("</div>");
	print('<div class="col-md-3">');
	print('<div class="btn-group-vertical">');
	print("<button type=\"button\" id=\"creer\" class=\"btn btn-primary\"><span class=\"glyphicon glyphicon-plus\"></span> Cr&eacute;er</button>");
	print("<button type=\"button\" id=\"modifier\" class=\"btn btn-primary\"><span class=\"glyphicon glyphicon-edit\"></span> Modifier</button>");
	print("<button type=\"button\" id=\"completer\" class=\"btn btn-primary\"><span class=\"glyphicon glyphicon-transfer\"></span> Compl&eacute;ter</button>");
	print("<button type=\"button\" id=\"supprimer\" class=\"btn btn-danger\"><span class=\"glyphicon glyphicon-trash\"></span> Supprimer</button>");
	print("<button type=\"button\" id=\"fusionner\" class=\"btn btn-warning\"><span class=\"glyphicon glyphicon-arrow-left\"></span> Fusionner</button>");
	print("<button type=\"button\" id=\"vider\" class=\"btn btn-warning\"><span class=\"glyphicon glyphicon-erase\"></span> Vider</button>");
	print("<button type=\"button\" id=\"exporter\" class=\"btn btn-primary\"><span class=\"glyphicon glyphicon glyphicon-download-alt\"> Calculer les variantes restantes</button>");
	print("<button type=\"button\" id=\"supprimer_inutilises\" class=\"btn btn-warning\"><span class=\"glyphicon glyphicon glyphicon-trash\"></span> Supprimer les patronymes inutilis&eacute;s</button>");
	print("</div>");
	print("</div>");

	print("<div class='col-md-5'>");
	print('<div id="cmt_retour_a_fusionner"></div>');
	print('<div class="form-group row">');
	print('<label for="variante_a_fusionner" class="col-form-label col-md-2">Variante &agrave; chercher:</label>');
	print("<div class='col-md-10'>");
	print("<input type=text name=\"variante_a_fusionner\" id=\"variante_a_fusionner\" value=\"\" size=30 class=\"form-control\">");
	print('</div>');
	print('</div>');
	print("<input type=\"hidden\" name=\"idf_groupe_a_fusionner\" id=\"idf_groupe_a_fusionner\" value=\"\">");
	print('<div class="form-group row">');
	print("<label for=\"majeure_a_fusionner\" class=\"col-form-label col-md-2\">Majeure trouv&eacute;e</label>");
	print("<div class='col-md-10'>");
	print("<input type=text name=\"majeure_a_fusionner\" id=\"majeure_a_fusionner\" value=\"\" class=\"form-control\" size=30>");
	print("</div>");
	print('</div>');
	print("<div class='row col-md-12'>");
	print("<textarea rows=20 cols=40 id=\"variantes_a_fusionner\" name=\"variantes_a_fusionner\" class=\"form-control col-md-8 col-md-offset-2\"></textarea>");
	print("</div>");
	print("</div>");
	print('</form>');
	print("</div>");
}

/*
* Ajoute les variantes au groupe sélectionné
* @param object $pconnexionBD  Connexion à la base
* @param integer $pi_idf_groupe identifiant du groupe
* @param string $pst_majeure majeure du groupe
* @param array $pa_variantes liste des variantes à ajouter
* @global string $gst_infos Chaine d'infos (succes)
* @global string $gst_erreurs (danger)
*/
function ajoute_variantes($pconnexionBD, $pi_idf_groupe, $pst_majeure, $pa_variantes)
{
	global $gst_infos, $gst_erreurs;
	$st_majeure = mb_strtoupper(stripslashes(trim($pst_majeure)));
	$a_variantes = array_unique(array_map('trim', $pa_variantes));
	$st_infos = '';
	$st_erreurs = '';
	$pconnexionBD->initialise_params(array(':majeure' => utf8_vers_cp1252($pst_majeure)));
	$i_nb_majeures = $pconnexionBD->sql_select1("select count(*) from variantes_patro where patronyme = :majeure collate latin1_general_ci");

	if ($i_nb_majeures > 0) {
		$gst_erreurs = "La majeure $st_majeure est d&eacute;j&agrave; pr&eacute;sente dans les variantes";
	} else {
		$a_variantes_a_ajouter = array();
		$a_variantes_ajoutees  = array();
		$i_groupe_courant = $pconnexionBD->sql_select1("select max(idf_groupe)+1 from variantes_patro");
		if (empty($i_groupe_courant)) $i_groupe_courant = 0;
		$pconnexionBD->initialise_params(array(':majeure' => (utf8_vers_cp1252(mb_strtoupper($pst_majeure)))));
		$st_variantes_a_ajouter = "values(:majeure,$i_groupe_courant,1)";
		$i = 0;
		foreach ($a_variantes as $st_variante) {
			$st_variante = mb_strtoupper(stripslashes($st_variante));
			if ($st_variante == "") continue;
			$a_params_precedents = $pconnexionBD->params();
			$pconnexionBD->initialise_params(array(':variante' => utf8_vers_cp1252($st_variante)));
			list($i_nb_variantes, $st_majeure_variante) = $pconnexionBD->sql_select_liste("select count(*),vp2.patronyme from variantes_patro vp1 join variantes_patro vp2 on (vp1.idf_groupe=vp2.idf_groupe) where vp2.majeure=1 and vp1.patronyme = :variante collate latin1_german1_ci");
			$pconnexionBD->initialise_params($a_params_precedents);
			if ($i_nb_variantes > 0)
				$gst_erreurs .= "Variante $st_variante d&eacute;j&agrave; r&eacute;f&eacute;renc&eacute;e sous la majeure $st_majeure_variante. Elle ne sera pas ajout&eacute;e";
			else {
				$pconnexionBD->ajoute_params(array(":variante$i" => utf8_vers_cp1252($st_variante)));
				$st_variantes_a_ajouter .= ",(:variante$i,$i_groupe_courant,0)";
				$a_variantes_ajoutees[] = $st_variante;
				$i++;
			}
		}
		$st_requete = "insert into variantes_patro(patronyme,idf_groupe,majeure) $st_variantes_a_ajouter";
		$pconnexionBD->execute_requete($st_requete);

		$st_variantes_ajoutees = join(',', $a_variantes_ajoutees);
		$gst_infos .= "Variantes ajout&eacute;es: $st_variantes_ajoutees";
	}
}

/*
* Traite la modification d'une variante
* @param object $pconnexionBD Connexion à la base
* @param integer $pi_idf_groupe identifiant du groupe
* @param string $pst_majeure Majeure à créer
* @param string $pst_variantes Liste des variantes associées à la majeure (séparée par des retours chariots)
*/
function modifie_variantes($pconnexionBD, $pi_idf_groupe, $pst_majeure, $pst_variantes)
{
	global $gst_infos, $gst_erreurs;

	$st_requete = "delete from  variantes_patro where idf_groupe=$pi_idf_groupe";
	$pconnexionBD->execute_requete($st_requete);
	$a_variantes = array_unique(array_map('trim', explode("\n", $pst_variantes)));
	$a_variantes_a_ajouter = array();
	$a_variantes_ajoutees = array();
	$i = 0;
	$pconnexionBD->initialise_params(array(':majeure' => utf8_vers_cp1252($pst_majeure)));
	$i_nb_majeures = $pconnexionBD->sql_select1("select count(*) from variantes_patro where patronyme = :majeure collate latin1_general_ci");

	if ($i_nb_majeures > 0) {
		$gst_erreurs = "La majeure $pst_majeure est d&eacute;j&agrave; pr&eacute;sente dans les variantes";
	} else {
		$pconnexionBD->initialise_params(array(':majeure' => utf8_vers_cp1252($pst_majeure)));
		$a_variantes_a_ajouter[]  =  "(:majeure,$pi_idf_groupe,1)";
		foreach ($a_variantes as $st_variante) {
			$st_variante = mb_strtoupper($st_variante);
			if ($st_variante == "") continue;
			$a_params_precedents = $pconnexionBD->params();
			$pconnexionBD->initialise_params(array(':variante' => utf8_vers_cp1252($st_variante)));
			list($i_nb_variantes, $st_majeure_variante) = $pconnexionBD->sql_select_liste("select count(*),min(vp2.patronyme) from variantes_patro vp1 join variantes_patro vp2 on (vp1.idf_groupe=vp2.idf_groupe) where vp2.majeure=1 and vp1.patronyme = :variante collate latin1_general_ci");
			$pconnexionBD->initialise_params($a_params_precedents);
			if ($i_nb_variantes > 0)
				$gst_erreurs .= "Variante $st_variante d&eacute;j&agrave; r&eacute;f&eacute;renc&eacute;e sous la majeure " . cp1252_vers_utf8($st_majeure_variante) . ".Elle ne sera pas ajout&eacute;e<br>";
			else {
				$pconnexionBD->ajoute_params(array(":variante$i" => utf8_vers_cp1252($st_variante)));
				$a_variantes_a_ajouter[] = "(:variante$i,$pi_idf_groupe,0)";
				$a_variantes_ajoutees[] = $st_variante;
				$i++;
			}
		}
		if (count($a_variantes_a_ajouter) > 0) {
			$st_variantes_a_ajouter = join(',', $a_variantes_a_ajouter);
			$st_requete = "insert into variantes_patro(patronyme,idf_groupe,majeure) values $st_variantes_a_ajouter";
			$pconnexionBD->execute_requete($st_requete);
			$gst_infos .= "Variantes ajout&eacute;es: " . join(',', $a_variantes_ajoutees);
		} else
			$gst_erreurs = "Rien n'a &eacute;t&eacute; ajout&eacute;";
	}
}

/*
* Traite l'ajout d'une variante
* @param object $pconnexionBD Connexion à la base
* @param integer $pi_idf_groupe identifiant du groupe
* @param array $pa_variantes liste des variantes … ajouter
*/
function complete_variantes($pconnexionBD, $pi_idf_groupe, $pa_variantes)
{
	global $gst_infos, $gst_erreurs;
	$a_variantes_a_ajouter = array();
	$i = 0;
	foreach ($pa_variantes as $st_variante) {
		$st_variante = mb_strtoupper(stripslashes($st_variante));
		$pconnexionBD->ajoute_params(array(":variante$i" => utf8_vers_cp1252($st_variante)));
		$a_variantes_a_ajouter[] = "(:variante$i,$pi_idf_groupe,0)";
		$i++;
	}
	$st_variantes_a_ajouter = join(',', $a_variantes_a_ajouter);
	$st_requete = "insert into variantes_patro(patronyme,idf_groupe,majeure) values$st_variantes_a_ajouter";
	//print("Req=$st_requete<br>");
	$pconnexionBD->execute_requete($st_requete);
	$gst_infos = "Variantes ajout&eacute;es: " . join(',', $pa_variantes);
}

/*
* Affiche les variantes à compléter
* @param object $pconnexionBD Connexion à la base
* @param integer $pi_idf_groupe identifiant du groupe
*/
function affiche_menu_completer($pconnexionBD, $pi_idf_groupe)
{
	$a_groupes_variantes = $pconnexionBD->liste_valeur_par_clef("select patronyme,majeure from `variantes_patro` where idf_groupe = $pi_idf_groupe");
	$oPhonex = new phonex;
	$a_phonex_variantes = array();
	$a_variantes_connues = array();
	$a_code_phonex = array();
	$st_majeure = '';
	$st_variantes_courantes = '';

	foreach ($a_groupes_variantes as $st_patronyme => $st_majeure_courante) {
		$st_patronyme = cp1252_vers_utf8($st_patronyme);
		$oPhonex->build($st_patronyme);
		$sPhonex = $oPhonex->sString;
		$a_code_phonex[] = $sPhonex;
		if ($st_majeure_courante == 1)
			$st_majeure = $st_patronyme;
		else
			$st_variantes_courantes .= "$st_patronyme\n";
	}
	$st_liste_phonex = join(',', $a_code_phonex);
	$st_requete = "select libelle from patronyme where libelle not in (select patronyme from `variantes_patro`) and phonex in ($st_liste_phonex)";
	//print("Req=$st_requete<br>");
	$a_variantes_ajouter = $pconnexionBD->sql_select($st_requete);
	print("<form  id=\"menu_completer\"  method=post >");
	print("<input type=hidden name=mode value=COMPLETER>");
	print("<input type=hidden name=idf_groupe value=$pi_idf_groupe>");
	print('<div class="row form-group">');
	print('<label for="majeure" class="col-form-label col-md-2">Majeure</label>');
	print('<div class="col-md-10">');
	print("<input type=text name=\"majeure\" id=\"majeure\" value=\"" . $st_majeure . "\" class=\"form-control\" size=30 readonly>");
	print('</div>');
	print('</div>');
	print('<div class="row form-group">');
	print('<label for="variantes_courantes" class="col-form-label col-md-2">Variantes actuelles:</label>');
	print('<div class="col-md-10">');
	print("<textarea id=variantes_courantes disabled rows=8 cols=40 class=\"form-control\">$st_variantes_courantes</textarea>");
	print('</div>');
	print('</div>');
	if (count($a_variantes_ajouter) == 0) {
		print('<div class="alert alert-danger">Pas de variantes trouv&eacute;es qui ne sont pas d&eacute;j&agrave; connues</div>');
		print('<button type="button" id="annuler" class="btn btn-primary col-md-4 col-md-offset-4"><span class="glyphicon glyphicon-home"></span> Retourner au menu</button>');
	} else {
		print('<div class="row form-group">');
		print('<label for="nouvelles_variantes" class="col-form-label col-md-2">Nouvelles variantes propos&eacute;es: </label>');
		print('<div class="col-md-10">');
		print('<table class="table table-bordered table-striped">');
		foreach ($a_variantes_ajouter as $st_patronyme) {
			$st_patronyme = cp1252_vers_utf8($st_patronyme);
			print("<tr><td>$st_patronyme</td><td><div class=\"lib_erreur\"><div class=\"checkbox\"><label><input type=checkbox class=\"form-check-input\" name=\"variantes[]\" id=\"$st_patronyme\" value=\"$st_patronyme\"></label></div></div></td></tr>");
		}
		print("</table>");
		print("</div>");
		print("</div>");
		print('<div class="btn-group col-md-8 col-md-offset-2" role="group">');
		print('<button type="submit" class="btn btn-primary col-md-4 col-md-offset-4"><span class="glyphicon glyphicon-ok"></span> Compl&eacute;ter</button>');
		print('<button type="button" id="annuler" class="btn btn-primary col-md-4 col-md-offset-4"><span class="glyphicon glyphicon-home"></span> Retourner au menu</button>');
		print("</div>");
	}
	print("</form>");
}

print('<!DOCTYPE html>');
print("<head>");
print('<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" >');
print('<title>Base ' . SIGLE_ASSO . ': Gestion des variantes Patronymiques</title>');
print('<meta name="viewport" content="width=device-width, initial-scale=1.0">');
print("<link href='../assets/css/styles.css' type='text/css' rel='stylesheet'>");
print("<link href='../assets/css/bootstrap.min.css' rel='stylesheet'>");
print("<link href='../assets/css/jquery-ui.css' type='text/css' rel='stylesheet'>");
print("<link href='../assets/css/jquery-ui.structure.min.css' type='text/css' rel='stylesheet'>");
print("<link href='../assets/css/jquery-ui.theme.min.css' type='text/css' rel='stylesheet'> ");
print("<link href='../assets/css/select2.min.css' type='text/css' rel='stylesheet'>");
print("<link href='../assets/css/select2-bootstrap.min.css' type='text/css' rel='stylesheet'>");
print("<script src='../assets/js/jquery-min.js' type='text/javascript'></script>");
print("<script src='../assets/js/jquery.validate.min.js' type='text/javascript'></script>");
print("<script src='../assets/js/additional-methods.min.js' type='text/javascript'></script>");
print("<script src='../assets/js/jquery-ui.min.js' type='text/javascript'></script>");
print("<script src='../assets/js/select2.min.js' type='text/javascript'></script>");
print("<script src='../assets/js/bootstrap.min.js' type='text/javascript'></script>");
print('<link rel="shortcut icon" href="../assets/img/favicon.ico">');
?>
<script type='text/javascript'>
	$(document).ready(function() {
		$('#annuler').click(function() {
			window.location.href = '<?php echo basename(__FILE__) ?>';
		});

		$.validator.addMethod('require-one', function(value) {
			if ($('#resp01-sim').is(':checked')) {
				return $('.require-one:checked').size() > 0;
			} else {
				return true;
			}
		}, 'Sélectionner au moins une case');

		function maj_variantes(term, idf_groupe, majeure, variantes, cmt_retour) {
			$.ajax({
				url: "/ajax/variantes_patro.php",
				type: "GET",
				data: 'term=' + term,
				dataType: 'json',
				cache: false,
				timeout: 1000,
				//contentType: "application/x-www-form-urlencoded;charset=windows-1252",
				success: function(reponse) {
					var nb_reponses = reponse['nb_reponses'];
					$(cmt_retour).html('');
					if (nb_reponses == 1)
						$(cmt_retour).append('<span class="badge badge-success">' + nb_reponses + '</span> groupes trouve(s)');
					else
						$(cmt_retour).append('<span class="badge badge-primary">' + nb_reponses + '</span> groupes trouve(s)');
					$(cmt_retour).addClass("has-error").removeClass("has-success");

					if (nb_reponses == 1) {
						var liste_variantes = '';
						$.each(reponse['variantes'], function(index, valeur) {
							liste_variantes = liste_variantes + valeur + "\n";
						});
						$(variantes).html('');
						$(variantes).append(liste_variantes);
						$(idf_groupe).val(reponse['idf_groupe']);
						$(majeure).val(reponse['majeure']);
						$(cmt_retour).removeClass("has-error").addClass("has-success");
					}
				}
			});
		}
		$('#variante_a_chercher').autocomplete({
			minLength: 4,
			source: function(request, response) {
				$.ajax({
					url: "/ajax/variantes_patro.php",
					dataType: "json",
					data: {
						term: request.term
					},
					success: function(reponse) {
						response(reponse['variantes']);
						if (reponse['nb_reponses'] == 1) {
							var variantes = '';
							$.each(reponse['variantes'], function(index, valeur) {
								variantes = variantes + valeur + "\n";
							});
							$('#variantes').html('');
							$('#variantes').append(variantes);
							$('#idf_groupe').val(reponse['idf_groupe']);
							$('#majeure').val(reponse['majeure']);
						}
					}
				})
			},
			select: function(event, ui) {
				//$('#variante').val(ui.item.value);
				maj_variantes($('#variante_a_chercher').val(), '#idf_groupe', '#majeure', '#variantes', '#cmt_retour');
				return false;

			}
		});

		$("#variante_a_chercher").keyup(function() {
			maj_variantes($('#variante_a_chercher').val(), '#idf_groupe', '#majeure', '#variantes', '#cmt_retour');
		});

		$('#variante_a_fusionner').autocomplete({
			minLength: 4,
			source: function(request, response) {
				$.ajax({
					url: "/ajax/variantes_patro.php",
					dataType: "json",
					data: {
						term: request.term
					},
					success: function(reponse) {
						response(reponse['variantes']);
						if (reponse['nb_reponses'] == 1) {
							var variantes = '';
							$.each(reponse['variantes'], function(index, valeur) {
								variantes = variantes + valeur + "\n";
							});
							$('#variantes_a_fusionner').html('');
							$('#variantes_a_fusionner').append(variantes);
							$('#idf_groupe_a_fusionner').val(reponse['idf_groupe']);
							$('#majeure_a_fusionner').val(reponse['majeure']);
						}
					}
				})
			},
			select: function(event, ui) {
				$('#variante').val(ui.item.value);
				maj_variantes($('#variante_a_fusionner').val(), '#idf_groupe_a_fusionner', '#majeure_a_fusionner', '#variantes_a_fusionner', '#cmt_retour_a_fusionner');
				return false;

			}
		});

		$("#variante_a_fusionner").keyup(function() {
			maj_variantes($('#variante_a_fusionner').val(), '#idf_groupe_a_fusionner', '#majeure_a_fusionner', '#variantes_a_fusionner', '#cmt_retour_a_fusionner');
		});

		$("#variantes_patro").validate({
			rules: {
				majeure: {
					required: {
						depends: function(element) {
							return $("#mode").val() != 'SUPPRIMER_INUTILISES';
						}
					}
				},
				variantes: {
					required: {
						depends: function(element) {
							return $("#mode").val() != 'SUPPRIMER' && $("#mode").val() != 'SUPPRIMER_INUTILISES';
						}
					}
				},
				majeure_a_fusionner: {
					required: {
						depends: function(element) {
							return $("#mode").val() == 'FUSIONNER';
						}
					}
				},
			},
			messages: {
				majeure: {
					required: "Aucune majeure s&eacute;lectionn&eacute;e"
				},
				variantes: {
					required: "Pas de variantes d&eacute;finies"
				},
				majeure_a_fusionner: {
					required: "Aucune majeure s&eacute;lectionn&eacute;"
				}
			},
			submitHandler: function(form) {
				// do other things for a valid form
				switch ($("#mode").val()) {
					case "SUPPRIMER":
						if (confirm('Voulez-vous supprimer ce groupe ?')) {
							form.submit();
						}
						break;
					case "FUSIONNER":
						if (confirm('Voulez-vous fusionner ces groupes ?')) {
							form.submit();
						}
						break;
					default:
						form.submit();
				}

			},
			errorElement: "em",
			errorPlacement: function(error, element) {
				// Add the `help-block` class to the error element
				error.addClass("help-block");

				// Add `has-feedback` class to the parent div.form-group
				// in order to add icons to inputs
				element.parents(".col-md-10").addClass("has-feedback");

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
				$(element).parents(".col-md-10").addClass("has-error").removeClass("has-success");
				$(element).next("span").addClass("glyphicon-remove").removeClass("glyphicon-ok");
			},
			unhighlight: function(element, errorClass, validClass) {
				$(element).parents(".col-md-10").addClass("has-success").removeClass("has-error");
				$(element).next("span").addClass("glyphicon-ok").removeClass("glyphicon-remove");
			}
		});

		$("#modifier").click(function() {
			$("#mode").val('MODIFIER');
			$("#variantes_patro").submit();
		});

		$("#completer").click(function() {
			$("#mode").val('MENU_COMPLETER');
			$("#variantes_patro").submit();
		});

		$("#creer").click(function() {
			$("#mode").val('CREER');
			$("#variantes_patro").submit();
		});

		$("#supprimer").click(function() {
			$("#mode").val('SUPPRIMER');
			$("#variantes_patro").submit();
		});

		$("#fusionner").click(function() {
			$("#mode").val('FUSIONNER');
			$("#variantes_patro").submit();
		});

		$("#supprimer_inutilises").click(function() {
			$("#mode").val('SUPPRIMER_INUTILISES');
			$("#variantes_patro").submit();
		});

		$("#vider").click(function() {
			$('#idf_groupe').val('');
			$('#variante_a_chercher').val('');
			$('#majeure').val('');
			$('#variantes').html('');
			$('#idf_groupe_a_fusionner').val('');
			$('#variante_a_fusionner').val('');
			$('#majeure_a_fusionner').val('');
			$('#variantes_a_fusionner').html('');
		});

		$("#menu_completer").validate({
			rules: {
				"variantes[]": {
					required: true,
					minlength: 1
				}
			},
			messages: {
				'variantes[]': 'Choisir au moins un patronyme'
			},
			errorElement: "em",
			errorPlacement: function(error, element) {
				// Add the `help-block` class to the error element
				error.addClass("help-block");

				if (element.prop("type") === "checkbox") {
					error.insertAfter(element.parent("label"));
				} else {
					error.insertAfter(element);
				}
			},
			highlight: function(element, errorClass, validClass) {
				$(element).parents(".lib_erreur").addClass("has-error").removeClass("has-success");
			},
			unhighlight: function(element, errorClass, validClass) {
				$(element).parents(".lib_erreur").addClass("has-success").removeClass("has-error");
			}
		});

		$("#exporter").click(function() {
			window.location.href = 'AjoutVariantes.php?mode=EXPORT';
		});

		$('#annuler').click(function() {
			window.location.href = '<?php echo basename(__FILE__) ?>';
		});


		$('#majeure_a_chercher').select2({
			allowClear: true,
			placeholder: "Taper une initiale",
			"language": {
				"noResults": function() {
					return "Pas de résultats";
				}
			},
			ajax: {
				url: '../ajax/majeure_patro.php',
				dataType: 'json',
				processResults: function(data) {
					return {
						results: data
					};
				}
			}
		});

		$('#majeure_a_chercher').on('select2:select', function(e) {
			var majeure = e.params.data.text;
			maj_variantes(majeure, '#idf_groupe', '#majeure', '#variantes', '#cmt_retour');
		});

	});
</script>
<?php
print("</head>");
print("<body>");
print('<div class="container">');

require_once __DIR__ . '/../commun/menu.php';

switch ($gst_mode) {
	case 'AFFICHER':
		affiche_menu($connexionBD, $gi_idf_groupe);
		break;
	case 'CREER':
		$i_idf_groupe = $connexionBD->sql_select1("select max(idf_groupe) from variantes_patro");
		$i_idf_groupe++;
		$st_majeure = isset($_POST['majeure']) ? trim($_POST['majeure']) : '';
		$st_variantes = isset($_POST['variantes']) ? trim($_POST['variantes']) : '';
		$a_variantes = explode("\n", $st_variantes);
		ajoute_variantes($connexionBD, $i_idf_groupe, $st_majeure, $a_variantes);
		affiche_menu($connexionBD, $i_idf_groupe);
		break;
	case 'MODIFIER':
		$i_idf_groupe = isset($_POST['idf_groupe']) ? (int) $_POST['idf_groupe'] : null;
		$st_majeure = isset($_POST['majeure']) ? trim($_POST['majeure']) : '';
		$st_variantes = isset($_POST['variantes']) ? trim($_POST['variantes']) : '';
		if (isset($i_idf_groupe)) {
			modifie_variantes($connexionBD, $i_idf_groupe, $st_majeure, $st_variantes);
			affiche_menu($connexionBD, $i_idf_groupe);
		}
		break;
	case 'MENU_COMPLETER':
		$i_idf_groupe = isset($_POST['idf_groupe']) ? (int) $_POST['idf_groupe'] : null;
		if (isset($i_idf_groupe))
			affiche_menu_completer($connexionBD, $i_idf_groupe);
		break;
	case 'COMPLETER':
		$i_idf_groupe = isset($_POST['idf_groupe']) ? (int) $_POST['idf_groupe'] : null;
		$a_variantes = isset($_POST['variantes']) ? $_POST['variantes'] : '';
		if (isset($i_idf_groupe) && count($a_variantes) > 0) {
			complete_variantes($connexionBD, $i_idf_groupe, $a_variantes);
			affiche_menu($connexionBD, $i_idf_groupe);
		}
		break;
	case 'SUPPRIMER':
		$i_idf_groupe = isset($_POST['idf_groupe']) ? (int) $_POST['idf_groupe'] : null;
		if (isset($i_idf_groupe)) {
			$connexionBD->execute_requete("delete from variantes_patro where idf_groupe=$i_idf_groupe");
			$gst_infos = "Variante supprim&eacute;e";
			affiche_menu($connexionBD, $i_idf_groupe);
		}
		break;
	case 'FUSIONNER':
		$i_idf_groupe = isset($_POST['idf_groupe']) ? (int) $_POST['idf_groupe'] : '';
		$i_idf_groupe_a_fusionner = isset($_POST['idf_groupe_a_fusionner']) ? (int) $_POST['idf_groupe_a_fusionner'] : '';
		$connexionBD->execute_requete("update variantes_patro set majeure=0 where idf_groupe=$i_idf_groupe_a_fusionner");
		$connexionBD->execute_requete("update variantes_patro set idf_groupe=$i_idf_groupe where idf_groupe=$i_idf_groupe_a_fusionner");
		$gst_infos = "Variante fusionn&eacute;e";
		affiche_menu($connexionBD, $i_idf_groupe);
		break;
	case 'SUPPRIMER_INUTILISES':
		$a_patronymes_a_supprimer = $connexionBD->sql_select("select idf FROM `patronyme` WHERE idf not in (select idf_patronyme from `stats_patronyme`)");
		if (count($a_patronymes_a_supprimer) > 0) {
			$st_patronymes_a_supprimer = join(',', $a_patronymes_a_supprimer);
			$connexionBD->execute_requete("delete FROM `patronyme` WHERE idf in ($st_patronymes_a_supprimer)");
			$connexionBD->execute_requete("delete FROM `variantes_patro` WHERE patronyme not in (select libelle from `patronyme`)");
			if (empty($gst_erreurs)) $gst_infos = "Patronymes inutilis&eacute;s supprim&eacute;s";
		} else
			$gst_infos = "Pas de patronymes &agrave; supprimer";
		affiche_menu($connexionBD, $gi_idf_groupe);

		break;
	default:
}

print("</div>");
print("</body>");
print("</html>");
