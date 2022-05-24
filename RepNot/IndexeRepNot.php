<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------
require_once __DIR__ . '/../Commun/config.php';
require_once __DIR__ . '/../Commun/Identification.php';
require_once __DIR__ . '/../Commun/constantes.php';
require_once __DIR__ . '/../Commun/ConnexionBD.php';
require_once __DIR__ . '/../Commun/commun.php';
require_once __DIR__ . '/../Commun/VerificationDroits.php';
require_once __DIR__ . '/commun_rep_not.php';

$connexionBD = ConnexionBD::singleton($gst_serveur_bd, $gst_utilisateur_bd, $gst_mdp_utilisateur_bd, $gst_nom_bd);

$gst_loc_rep = $gst_rep_site . DIRECTORY_SEPARATOR . 'RepNot' . DIRECTORY_SEPARATOR . 'photos';

/*
* Renvoie la liste des photos d'un répertoire
* @param string $pst_chemin 
* @return array liste des photos 
*/
function liste_photos($pst_chemin)
{
	$a_fichiers = array();
	$a_fichiers_tries = array();
	//print("C=$pst_chemin<BR>");
	if (is_dir($pst_chemin)) {
		if ($dh = opendir($pst_chemin)) {
			while (($st_fichier = readdir($dh)) !== false) {
				//print("F=$st_fichier<br>\n");
				$a_infos = pathinfo($pst_chemin . DIRECTORY_SEPARATOR . $st_fichier);
				if (in_array(strtoupper($a_infos['extension']), array('GIF', 'JPG', 'JPEG', 'PNG')))
					$a_fichiers[] = $st_fichier;
			}
			closedir($dh);
		}
		$i = 1;
		sort($a_fichiers);
		foreach ($a_fichiers as $st_fichier) {
			$a_fichiers_tries[$i] = $st_fichier;
			$i++;
		}
	}
	return $a_fichiers_tries;
}

/*
* Affiche la barre de navigation parmi les photos
* @param object $pconnexionBD connexion à la BD 
* @param integer $pi_idf_rep identifiant du répertoire
* @param integer $pi_page_courante page courante
* @param array $pa_photos liste des photos 
*/
function affiche_barre_navigation($pconnexionBD, $pi_idf_rep, $pi_page_courante, $pa_photos)
{
	$i_nb_photos = count($pa_photos);
	if ($pi_page_courante > 0 && $pi_page_courante <= $i_nb_photos) {
		print('<div class="form-row col-md-12"><div class="text-center"> <ul class="pagination input-group">');
		print("<form id=\"barre_navigation\" name=\"barre_navigation\" method=\"POST\" >");
		print('<div class="input-group text-center">');
		if ($pi_page_courante > 1) {
			// Affichage de la navigation gauche
			print('<div class="input-group-btn">');
			print('<button id="vue_gauche" class="btn btn-primary"><span class="glyphicon glyphicon-triangle-left"></span></button>');
			print('</div>');
		}
		// Affichage de l'accès à la page directement
		print("<input type=hidden name=\"idf_rep\" value=\"$pi_idf_rep\">");
		print('<div class="input-group">');
		print("<span class=\"input-group-addon\">Page courante:</span>");
		print("<input type=\"textfield\" size=\"2\" maxlength=\"4\" id=\"page_crte\" name=\"page_crte\" value=\"$pi_page_courante\" class=\"form-control\">");
		print('</span>');
		print("<span class=\"input-group-addon\">/$i_nb_photos photos</span>");
		if ($pi_page_courante < $i_nb_photos) {
			$i_page_suiv = $pi_page_courante + 1;
			// Affichage de la navigation droite
			print('<div class="input-group-btn">');
			print('<button id="vue_droite" class="btn btn-primary"><span class="glyphicon glyphicon-triangle-right"></span></button>');
			print('</div>');
		}
		print("</div>");
		print("</form></ul></div></div>");
		// Met à jour la bd avec la page sélectionnée
		$st_requete = "update rep_not_desc set page_courante=$pi_page_courante where idf_repertoire=$pi_idf_rep";
		$pconnexionBD->execute_requete($st_requete);
	} else {
		print("<div class=\"alert alert-danger\">Il n'existe pas de page $pi_page_courante dans le lot du r&eacute;pertoire s&eacute;lectionn&eacute;</div>");
	}
	print('</ul></div>');
}

/*
* Affiche la grille de saisie
* @param integer $pi_format format de la grille (0 sans prénom|1 avec prénom)
* @param integer $pi_idf_repertoire identifiant du répertoire
* @param integer $pi_page_courante page courante
* @param integer $pi_annee_courante année courante
* @param integer $pi_mois_courant mois courant 
*/
function affiche_grille_saisie($pi_format, $pi_idf_repertoire, $pi_page_courante, $pi_annee_courante, $pi_mois_courant)
{
	global $ga_mois, $ga_mois_revolutionnaires, $ga_annees_revolutionnaires;
	$a_mois_rep_not = $ga_mois;
	$a_mois_rep_not[0] = 'Sans Mois';
	print("<div>\n");
	print("<form id=saisie name=grille_saisie>");
	print("<table class=\"table table-bordered\">\n");
	print("<tr><th>Ann&eacute;e</th><td class=\"lib_erreur\"><input type=\"text\" id=\"annee\" name=\"annee\" value=\"$pi_annee_courante\" class=\"form-control\"></td>\n");
	print("<th>Mois</th><td class=\"lib_erreur\"><select id=\"mois\" name=\"mois\" class=\"form-control\">" . chaine_select_options($pi_mois_courant, $a_mois_rep_not, false) . "</select></td><th>Jour</th><td><input type=\"textfield\" id=\"jour\" name=\"jour\"></td><th>Sans date</th><td><input type=\"checkbox\" name=\"sans_date\" value=\"1\" id=\"sans_date\"></td></tr></tr>\n");
	print("<tr><th >Type d'acte</th><td class=\"lib_erreur\"><input type=\"text\" id=\"type_acte\" name=\"type_acte\" maxlength=40 size=20 style=\"text-transform: capitalize;\" class=\"form-control\"></td><td colspan=6>&nbsp;</td></tr>\n");
	switch ($pi_format) {
		case 0:
			// Prénom et nom
			print("<tr>");
			print("<th>Nom1</th><td class=\"lib_erreur\"><input type=\"text\" id=\"nom1\" name=\"nom1\" class=\"patro_ou_paroisse form-control\" maxlength=40 size=20 style=\"text-transform: uppercase;\"></td>");
			print("<th>Pr&eacute;nom1</th><td><input type=\"text\" id=\"prenom1\" name=\"prenom1\" maxlength=30 size=20 style=\"text-transform: capitalize;\" class=\"form-control\"></td>");
			print("<th>Nom2</th><td><input type=\"text\" id=\"nom2\" name=\"nom2\" maxlength=40 size=20 style=\"text-transform: uppercase;\" class=\"form-control\"></td>");
			print("<th>Pr&eacute;nom2</th><td><input type=\"text\" id=\"prenom2\" name=\"prenom2\" maxlength=30 size=20 style=\"text-transform: capitalize;\" class=\"form-control\"></td>");
			print("</tr>\n");
			break;
		case 1:
			// Uniquement nom
			print("<tr>");
			print("<th>Nom1</th><td class=\"lib_erreur\"><input type=\"text\" id=\"nom1\" name=\"nom1\" class=\"patro_ou_paroisse form-control\" maxlength=40 size=20></td>");
			print("<th>Nom2</th><td><input type=\"text\" id=\"nom2\" name=\"nom2\" maxlength=40 size=20 class=\"form-control\"></td>");
			print("</tr>\n");
			break;
		default:
	}
	print("<tr>");
	print("<th>Paroisse</th><td class=\"lib_erreur\"><input type=\"text\" id=\"paroisse\" name=\"paroisse\" class=\"patro_ou_paroisse form-control\" maxlength=40 size=20></td>\n");
	print("<th>Commentaires</th><td colspan=3><input type=\"text\" id=\"commentaires\" name=\"commentaires\" maxlength=255 size=80 class=\"form-control\"></td><td colspan=2 align=\"right\">\n");
	print("<input type=\"hidden\" name=\"idf_rep\" id=\"idf_rep\" value=\"$pi_idf_repertoire\">");
	print("<input type=\"hidden\" name=\"idf_acte_cour\" id=\"idf_acte_cour\" value=\"\">");
	print("<input type=\"hidden\" name=\"page\" value=\"$pi_page_courante\">");
	print("<input type=\"hidden\" name=\"sid\" value=\"" . session_id() . "\">");
	print("<button type=\"submit\" class=\"btn btn-primary\">Ajouter/Modifier</button>");
	print("</td></tr>");
	$st_chaine_date_rep = '<div class="col-md-2">';
	$st_chaine_date_rep .= "<input type=\"text\" name=\"jour_rep\" id=\"jour_rep\"  size=\"2\" maxlength=\"2\" value=\"\" class=\"form-control\">";
	$st_chaine_date_rep .= '</div>';
	$st_chaine_date_rep .= '<div class="col-md-4">';
	$st_chaine_date_rep .= ' <select name="mois_rep" id="mois_rep" class="form-control">';
	$st_chaine_date_rep .= '<option value=""></option>';
	$st_chaine_date_rep .= chaine_select_options(null, $ga_mois_revolutionnaires, false);
	$st_chaine_date_rep .= '</select>';
	$st_chaine_date_rep .= '</div>';
	$st_chaine_date_rep .= '<div class="col-md-2">';
	$st_chaine_date_rep .= ' <select name="annee_rep" id="annee_rep" class="form-control">';
	$st_chaine_date_rep .= '<option value=""></option>';
	$st_chaine_date_rep .= chaine_select_options(null, $ga_annees_revolutionnaires, false);
	$st_chaine_date_rep .= '</select>';
	$st_chaine_date_rep .= '</div>';
	$st_chaine = "<tr><th>Date r&eacute;publicaine</th><td colspan=4><div class=\"row form-group\">$st_chaine_date_rep</div></td><td colspan=3><button type=button id=maj_date class=\"btn btn-primary\">maj de la date</button></td></tr>";
	print($st_chaine);
	print("</table>\n");
	print("</form>");
	print("</div>\n");
	print("<div class=\"text-center\"><br>La paroisse ne doit &ecirc;tre saisie que s'il s'agit d'un acte capitulaire (Exemple: Assembl&eacute;e des collecteurs de taille de Nanclars)</div>");
}

/*
* Affiche la liste des actes déjà saisis pour le mois et année courante
* @param object $pconnexionBD connexion à la BD
* @param integer $pi_idf_repertoire identifiant du répertoire
* @param integer $pi_format format de la grille (0 sans prénom|1 avec prénom)
* @param integer $pi_page_courante page courante
* @param integer $pi_annee_courante année courante
* @param integer $pi_mois_courant mois courant 
*/
function affiche_actes_saisis($pconnexionBD, $pi_idf_repertoire, $pi_format, $pi_page_courante, $pi_annee_courante, $pi_mois_courant)
{
	global $ga_mois;
	print('<div id="actes_saisis" class="text-center">');
	if (!empty($pi_idf_repertoire) && !empty($pi_annee_courante) && !empty($pi_mois_courant)) {
		$st_requete = "select idf_acte,jour,date_rep,`type`,nom1,prenom1,nom2,prenom2,paroisse, commentaires from rep_not_actes where idf_repertoire=$pi_idf_repertoire and annee=$pi_annee_courante and mois=$pi_mois_courant order by jour desc";
		$a_actes = $pconnexionBD->sql_select_multiple($st_requete);
		if (count($a_actes) > 0) {
			print(sprintf("<div class=\"div_centre\">%s %4d</div>", $ga_mois[$pi_mois_courant], $pi_annee_courante));
			print('<table class="table table-bordered table-striped">');
			print("<tr><thead><th>Ann&eacute;e</th><th>Mois</th><th>Jour</th><th>DateRep</th><th>Type d'acte</th>");
			switch ($pi_format) {
				case 0:
					print("<th>Nom1</th><th>Prenom1</th><th>Nom2</th><th>Prenom2</th>");
					break;
				case 1:
					print("<th>Nom1</th><th>Nom2</th>");
					break;
				default:
			}
			print("<th>Paroisse</th><th>Commentaires</th><th colspan=2>&nbsp;</th></tr></thead><tbody>");
			foreach ($a_actes as $a_acte) {
				list($i_idf_acte, $i_jour, $st_date_rep, $st_type, $st_nom1, $st_prenom1, $st_nom2, $st_prenom2, $st_paroisse, $st_commentaires) = $a_acte;
				if ($pi_mois_courant == 0)
					print sprintf("<tr><td>%4d</td><td>Sans Mois</td><td>%d</td><td>&nbsp;</td><td>%s</td>", $pi_annee_courante, $i_jour, $st_date_rep, cp1252_vers_utf8($st_type));
				else
					print sprintf("<tr><td>%4d</td><td>%s</td><td>%d</td><td>%s</td><td>%s</td>", $pi_annee_courante, $ga_mois[$pi_mois_courant], $i_jour, $st_date_rep, cp1252_vers_utf8($st_type));
				switch ($pi_format) {
					case 0:
						print("<td>" . cp1252_vers_utf8($st_nom1) . "</td><td>" . cp1252_vers_utf8($st_prenom1) . "</td><td>" . cp1252_vers_utf8($st_nom2) . "</td><td>" . cp1252_vers_utf8($st_prenom2) . "</td>");
						break;
					case 1:
						print("<td>Nom1</td><td>Nom2</td>");
						break;
					default:
				}
				print("<td>" . cp1252_vers_utf8($st_paroisse) . "</td><td>" . cp1252_vers_utf8($st_commentaires) . "</td><td><span class=\"glyphicon glyphicon-edit elem_edition\" id=\"$i_idf_acte\"></span></td><td><span class=\"glyphicon glyphicon-trash elem_suppression\" id=\"$i_idf_acte\"></span>
			   </td></tr>");
			}
			print('</tbody></table>');
		}
	}
	print("</div>");
}

/******************************************************************************/
/*                     CORPS DU PROGRAMME                                     */
/******************************************************************************/

print('<!DOCTYPE html>');
print("<head>\n");
print('<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">');
print('<meta http-equiv="content-language" content="fr"> ');
print('<meta name="viewport" content="width=device-width, initial-scale=1.0">');
print("<link href='../css/styles.css' type='text/css' rel='stylesheet'>");
print("<link href='../css/bootstrap.min.css' rel='stylesheet'>");
print("<link href='../css/jquery-ui.css' type='text/css' rel='stylesheet'>");
print("<link href='../css/jquery-ui.structure.min.css' type='text/css' rel='stylesheet'>");
print("<link href='../css/jquery-ui.theme.min.css' type='text/css' rel='stylesheet'> ");
print("<script src='../js/jquery-min.js' type='text/javascript'></script>");
print("<script src='../js/jquery.validate.min.js' type='text/javascript'></script>");
print("<script src='../js/additional-methods.min.js' type='text/javascript'></script>");
print("<script src='../js/jquery-ui.min.js' type='text/javascript'></script>");
print("<script src='../js/bootstrap.min.js' type='text/javascript'></script>");
print("<script src='../js/CalRep.js' type='text/javascript'></script>");
print("<script src='../js/iviewer/jquery-ui.min.js' type='text/javascript'></script>");
print("<script src='../js/iviewer/jquery.mousewheel.min.js' type='text/javascript'></script>");
print("<script src='../js/iviewer/jquery.iviewer.js' type='text/javascript'></script>");
print("<link href='../js/iviewer/jquery.iviewer.css' type='text/css' rel='stylesheet'>");

?>
<script type='text/javascript'>
	$(document).ready(function() {
		inialise_actes_saisis();

		function MajActesSaisis(json, textStatus, jqXHR) {
			$('#actes_saisis').empty();
			if (json.length > 0) {
				var mois = $("#mois option:selected").text();
				var annee = $("#annee").val();
				$('#actes_saisis').append("<table class=\"table table-bordered table-striped\"></table>");
				var table = $('#actes_saisis').children();
				table.append("<thead><tr><th>Date</th><th>DateRep</th><th>Type d'acte</th><th>Nom1</th><th>Prenom1</th><th>Nom2</th><th>Prenom2</th><th>Paroisse</th><th>Commentaires</th><th colspan=2>&nbsp;</th></tr></thead><tbody>");
				$.each(json, function(key, val) {
					var suppression = '<span class="elem_suppression glyphicon glyphicon-trash" id="' + val['idf_acte'] + '"></span>';
					var edition = '<span class="elem_edition glyphicon glyphicon-edit" id="' + val['idf_acte'] + '"></span>';
					table.append("<tr><td>" + val['date'] + "</td><td>" + val['date_rep'] + "</td><td>" + val['type'] + "</td><td>" + val['nom1'] + "</td><td>" + val['prenom1'] + "</td><td>" + val['nom2'] + "</td><td>" + val['prenom2'] + "</td><td>" + val['paroisse'] + "</td><td>" + val['commentaires'] + "</td><td>" + edition + "</td><td>" + suppression + "</td></tr>");
				});
				table.append("</tbody>");
				$('#actes_saisis table').before('<div class="text-center">' + mois + ' ' + annee + "</div>");
				$('#actes_saisis').show();
				inialise_actes_saisis();
			}
		};

		function MajActe(json, textStatus, jqXHR) {
			if (json.length > 0) {
				donnees = $.parseJSON(json);
				$('#idf_acte_cour').val(donnees.idf_acte);
				$('#jour').val(donnees.jour);
				$('#mois').val(donnees.mois);
				$('#annee').val(donnees.annee);
				$('#type_acte').val(donnees.type);
				$('#nom1').val(donnees.nom1);
				$('#prenom1').val(donnees.prenom1);
				$('#nom2').val(donnees.nom2);
				$('#prenom2').val(donnees.prenom2);
				$('#paroisse').val(donnees.paroisse);
				$('#commentaires').val(donnees.commentaires);
				$('#erreur').removeClass("alert alert-danger");
				$('#erreur').html('');
			}
		};
		$("#barre_navigation").validate({
			rules: {
				page_crte: {
					required: true,
					number: true
				}
			},
			messages: {
				page_crte: {
					required: "La page est obligatoire",
					number: "La page doit être un nombre"
				}
			}
		});
		$("#vue_gauche").click(function() {
			if ($.isNumeric($("#page_crte").val())) {
				$("#page_crte").val(parseInt($("#page_crte").val()) - 1);
				$("#barre_navigation").submit();
			} else {
				alert('La page courante doit être un entier');
			}
		});
		$("#vue_droite").click(function() {
			if ($.isNumeric($("#page_crte").val())) {
				$("#page_crte").val(parseInt($("#page_crte").val()) + 1);
				$("#barre_navigation").submit();
			} else {
				alert('La page courante doit être un entier');
			}
		});
		$("#saisie").validate({
			rules: {
				annee: {
					required: true,
					number: true
				},
				jour: {
					required: true,
					number: true,
					max: 31
				},
				type_acte: {
					required: true
				},
				nom1: {
					required: true,
					maxlength: 40
				},
				nom1: {
					require_from_group: [1, ".patro_ou_paroisse"]
				},
				paroisse: {
					require_from_group: [1, ".patro_ou_paroisse"]
				}
			},
			messages: {
				annee: {
					required: "L'année est obligatoire",
					number: "L'année doit être un nombre"
				},
				jour: {
					required: "Le jour est obligatoire",
					number: "Le jour doit être un nombre",
					max: "Un mois comporte au maximum 31 jours"
				},
				type_acte: {
					required: "Le type d'acte est obligatoire"
				},
				nom1: {
					require_from_group: "Le premier nom ou bien la paroisse est obligatoire"
				},
				paroisse: {
					require_from_group: "Le premier nom ou bien la paroisse est obligatoire"
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
			},
			submitHandler: function(form) {
				$.ajax({
					type: "POST",
					url: "./ajax/edite_acte.php",
					data: $(form).serialize(),
					timeout: 3000,
					success: function(response) {
						if (response == "") {
							$('#idf_acte_cour').val('');
							$('#jour').val('');
							$('#jour_rep').val('');
							$('#mois_rep').val('');
							$('#annee_rep').val('');
							$('#type_acte').val('');
							$('#nom1').val('');
							$('#prenom1').val('');
							$('#nom2').val('');
							$('#prenom2').val('');
							$('#paroisse').val('');
							$('#commentaires').val('');
							$('#erreur').removeClass("alert alert-danger");
							$('#erreur').html('');
							$('#jour').focus();

							$.ajax({
								url: './ajax/actes_saisis.php',
								data: 'idf_rep=' + $('#idf_rep').val() + '&annee=' + $('#annee').val() + '&mois=' + $('#mois').val(),
								dataType: 'json',
								cache: false,
								success: MajActesSaisis
							});
						} else {
							$('#erreur').html(response);
							$('#erreur').addClass("alert alert-danger");
							$('#erreur').show();
						}
					},
					error: function() {
						$('#erreur').html('Ajout Impossible');
						$('#erreur').addClass("alert alert-danger");
						$('#erreur').show();
					}
				});
				return false;
			}
		});
		$("#mois").change(function() {
			$.ajax({
				url: './ajax/actes_saisis.php',
				data: 'idf_rep=' + $('#idf_rep').val() + '&annee=' + $('#annee').val() + '&mois=' + $('#mois').val(),
				dataType: 'json',
				cache: false,
				success: MajActesSaisis
			});
		});
		$("#annee").blur(function() {
			$.ajax({
				url: './ajax/actes_saisis.php',
				data: 'idf_rep=' + $('#idf_rep').val() + '&annee=' + $('#annee').val() + '&mois=' + $('#mois').val(),
				dataType: 'json',
				cache: false,
				success: MajActesSaisis
			});
		});
		$("#sans_date").change(function() {
			if ($(this).is(":checked")) {
				$('#jour').val('0');
				$('#annee').val('9999');
			} else {
				$('#jour').val('');
				$('#annee').val('');
			}
		});
		$('#type_acte').autocomplete({
			source: './ajax/type_acte.php',
			minLength: 2
		});
		$('#nom1').autocomplete({
			source: './ajax/patronyme.php',
			minLength: 2
		});
		$('#nom2').autocomplete({
			source: './ajax/patronyme.php',
			minLength: 2
		});
		$('#prenom1').autocomplete({
			source: './ajax/prenom.php',
			minLength: 2
		});
		$('#prenom2').autocomplete({
			source: './ajax/prenom.php',
			minLength: 2
		});
		$('#paroisse').autocomplete({
			source: './ajax/paroisse.php',
			minLength: 3
		});

		function inialise_actes_saisis() {
			$('span.elem_suppression').click(function() {
				var $this = $(this);
				$.ajax({
					type: 'GET',
					url: './ajax/supprime_acte.php',
					data: 'sid=<?php echo session_id(); ?>&idf_rep=' + $('#idf_rep').val() + '&idf_acte=' + $this.attr('id'),
					success: function() {
						$this.closest("tr").remove();
					}
				});
			});
			$('span.elem_edition').click(function() {
				var $this = $(this);
				$.ajax({
					type: 'GET',
					url: './ajax/infos_acte.php',
					data: 'sid=<?php echo session_id(); ?>&idf_rep=' + $('#idf_rep').val() + '&idf_acte=' + $this.attr('id'),
					success: MajActe
				});
			});
		}

		$('#maj_date').click(function() {
			if ($('#jour_rep').val() != '' && $('#mois_rep').val() != '' && $('#annee_rep').val() != '') {
				var jour_rep = parseInt($('#jour_rep').val());
				var mois_rep = parseInt($('#mois_rep').val());
				var annee_rep = parseInt($('#annee_rep').val());
				if (jour_rep != 0) {
					if ((mois_rep == 13) && jour_rep > 6) {
						alert('Le mois Complementaires ne comporte que 6 jours');
					} else if (jour_rep > 30) {
						alert('Le mois comporte au maximum 30 jours');
					} else {
						if (mois_rep == 13)
							var date_rep = new CalRep(jour_rep + 30, 12, annee_rep);
						else
							var date_rep = new CalRep(jour_rep, mois_rep, annee_rep);
						date_rep.convertir();
						$('#jour').val(date_rep.getJourGreg());
						$('#mois').val(date_rep.getMoisGreg());
						$('#annee').val(date_rep.getAnneeGreg());
					}
				}
			} else {
				alert("Le jour, le mois ou l'année révolutionnaire est vide");
			}
		});

	});
</script>
<style>
	.viewer {
		width: 95%;
		height: 400px;
		border: 1px solid black;
		position: relative;
		margin-left: auto;
		margin-right: auto;
	}

	.wrap {
		width: 100%;
		margin: 0 auto;
		display: inline-block;
	}

	div.div_centre {
		margin-top: 5px;
		margin-bottom: 5px;
		text-align: center;
	}
</style>
<?php

print("<title>Indexation d'un repertoire de notaire</title>");
print('</head>');
print('<body>');
print('<div class="container">');

require_once __DIR__ . '/../Commun/menu.php';

print('<div class="panel panel-primary">');
print('<div class="panel-heading">Indexation d\'un r&eacute;pertoire de notaire</div>');
print('<div class="panel-body">');
print("<div id=erreur></div>");
if (isset($_REQUEST['idf_rep'])) {
	$gi_idf_rep = (int) $_REQUEST['idf_rep'];
	$st_requete = "select page_courante,annee_courante,mois_courant,idf_releveur,publication from rep_not_desc where idf_repertoire=$gi_idf_rep";
	list($gi_page_crte, $gi_anneee_crte, $gi_mois_crt, $gi_idf_releveur, $gc_publication) = $connexionBD->sql_select_liste($st_requete);
	if (!empty($_SESSION['ident'])) {
		$st_requete = "select idf from adherent where ident='" . $_SESSION['ident'] . "'";
		$gi_idf_adherent = $connexionBD->sql_select1($st_requete);
		if (!empty($gi_idf_adherent)) {
			if ($gi_idf_adherent == $gi_idf_releveur || empty($gi_idf_releveur)) {
				if ($gc_publication == 'N') {
					$gi_fmt_rep = 0;
					$ga_photos = liste_photos($gst_loc_rep . DIRECTORY_SEPARATOR . $gi_idf_rep);
					if (isset($_REQUEST['page_crte'])) {
						$gi_page_crte =  (int) $_REQUEST['page_crte'];
					}
					if (empty($gi_page_crte)) {
						$gi_page_crte = 1;
					}
					print("<div>");
					affiche_barre_navigation($connexionBD, $gi_idf_rep, $gi_page_crte, $ga_photos);
					print(sprintf("<div id=\"photo1\" class=\"viewer\"></div><br><script type='text/javascript'>var iv1 = $(\"#photo1\").iviewer({src: \"./photos/%d/%s\",zoom :\"fit\"});</script>", $gi_idf_rep, $ga_photos[$gi_page_crte]));
					affiche_grille_saisie($gi_fmt_rep, $gi_idf_rep, $gi_page_crte, $gi_anneee_crte, $gi_mois_crt);
					affiche_actes_saisis($connexionBD, $gi_idf_rep, $gi_fmt_rep, $gi_page_crte, $gi_anneee_crte, $gi_mois_crt);
					print("</div>");
				} else {
					print("<div class=\"alert alert-danger\">Le r&eacute;pertoire est d&eacute;j&agrave; publi&eacute !</div>");
				}
			} else {
				print("<div class=\"alert alert-danger\">L'adh&eacute;rent n'est pas le releveur</div>");
			}
		}
	} else {
		print("<div class=\"alert alert-danger\">L'adh&eacute;rent n'est pas identifi&eacute;</div>");
	}
} else {
	print("<div class=\"alert alert-danger\">Identifiant de r&eacute;pertoire non sp&eacute;cifi&eacute;</div>");
}
print('</div></div>');
print('</div></body></html>');
