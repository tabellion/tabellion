<?php

require_once __DIR__ . '/../app/bootstrap.php';

verifie_privilege(DROIT_NOTAIRES);

print('<!DOCTYPE html>');
print("<head>");
/* ------------------- modif title */
print("<title>Base AGC: Vos recherches d'actions sur les liasses</title>");
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
//print("<script src='./VerifieChampsRechercheActionLiasse.js' type='text/javascript'></script>");
print('<link rel="shortcut icon" href="../assets/img/favicon.ico">');
?>
<script type='text/javascript'>
	$(document).ready(function() {

		//$.fn.select2.defaults.set( "theme", "bootstrap" );

		//$(".js-select-avec-recherche").select2();

		jQuery.validator.addMethod(
			"cote_debut_fin",
			function(value, element) {
				var check = true;
				if (+$(element).val() < +$('#cote_debut').val()) {
					check = false;
				}
				return this.optional(element) || check;
			},
			"La première cote doit être inférieure à la dernière"
		);

		jQuery.validator.addMethod(
			"un_critere",
			function(value, element) {
				var check = true;
				if ($('#menu').val() == 'sans') {
					var sans_notaire = $('#sans_notaire').checked;
					var sans_periode = $('#sans_periode').checked;
					var sans_lieu = $('#sans_lieu').checked;
					if (!sans_notaire && !sans_periode && !sans_lieu) {
						check = false;
					}
				}
				return this.optional(element) || check;
			},
			"Cochez au moins un critère"
		);

		$("#critere").validate({
			rules: {
				sans_lieu: {
					un_critere: true
				},
				cote_fin: {
					cote_debut_fin: true
				}
			},
			messages: {
				sans_lieu: {
					un_critere: "Cochez au moins un critère"
				},
				cote_fin: {
					cote_debut_fin: "La première cote doit être inférieure à la dernière"
				}
			}
		});

		$("#btRaz").click(function() {
			var menu = $('#menu').val();
			switch (menu) {
				case 'releve':
					$('#cote_debut').val('');
					$('#cote_fin').val('');
					$("#commune").val(0).trigger('change');
					$('#forme_liasse').prop('selectedIndex', 0);
					$('#non_comm').prop('checked', false);
					$('#av_1793').prop('checked', false);
					$('#photo').prop('checked', false);
					$('#pas_photo').prop('checked', false);
					break;
				case 'pas_releve':
					$('#cote_debut').val('');
					$('#cote_fin').val('');
					$("#commune").val(0).trigger('change');
					$('#forme_liasse').prop('selectedIndex', 0);
					$('#non_comm').prop('checked', false);
					$('#av_1793').prop('checked', false);
					break;
				case 'publi_pap':
					$('#cote_debut').val('');
					$('#cote_fin').val('');
					$("#commune").val(0).trigger('change');
					$('#pas_publi_num').prop('checked', false);
					break;
				case 'publi_num':
					$('#cote_debut').val('');
					$('#cote_fin').val('');
					$("#commune").val(0).trigger('change');
					$('#publi_pap').prop('checked', false);
					$('#pas_publi_pap').prop('checked', false);
					break;
				case 'photo':
					$('#cote_debut').val('');
					$('#cote_fin').val('');
					$("#commune").val(0).trigger('change');
					$('#forme_liasse').prop('selectedIndex', 0);
					$('#non_comm').prop('checked', false);
					$('#pas_publi_pap').prop('checked', false);
					$('#pas_publi_num').prop('checked', false);
					$('#sans_photographe').prop('checked', false);
					$('#sans_date_photo').prop('checked', false);
					$('#avec_commentaire').prop('checked', false);
					break;
				case 'pas_photo':
					$('#cote_debut').val('');
					$('#cote_fin').val('');
					$("#commune").val(0).trigger('change');
					$('#forme_liasse').prop('selectedIndex', 0);
					$('#non_comm').prop('checked', false);
					$('#av_1793').prop('checked', false);
					break;
				case 'repert':
					$("#commune").val(0).trigger('change');
					$('#av_1793').prop('checked', false);
					break;
				case 'sans':
					$('#sans_notaire').prop('checked', false);
					$('#sans_periode').prop('checked', false);
					$('#sans_lieu').prop('checked', false);
					break;
				case 'non_comm':
					$("#commune").val(0).trigger('change');
					$('#av_1793').prop('checked', false);
					break;
				case 'program':
					$("#commune").val(0).trigger('change');
					$('#releve').prop('checked', false);
					$('#photo').prop('checked', false);
					break;
			}
		});

		$("#commune").select2({
			allowClear: true,
			placeholder: "Toutes"
		});

	});
</script>
<?php


print("</head>");

print("<body>");
print('<div class="container">');

require_once __DIR__ . '/../commun/menu.php';

if (isset($_POST['menu'])) {
	$_SESSION['menu_rla'] = $_POST['menu'];
} else {
	$_SESSION['menu_rla'] = isset($_SESSION['menu_rla']) ? $_SESSION['menu_rla'] : '';
}
$st_check_revele		= $_SESSION['menu_rla'] == 'releve' 	? 'checked' : '';
$st_check_pas_releve	= $_SESSION['menu_rla'] == 'pas_releve'	? 'checked' : '';
$st_check_publi_pap		= $_SESSION['menu_rla'] == 'publi_pap'	? 'checked' : '';
$st_check_publi_num		= $_SESSION['menu_rla'] == 'publi_num'	? 'checked' : '';
$st_check_photo			= $_SESSION['menu_rla'] == 'photo'		? 'checked' : '';
$st_check_pas_photo		= $_SESSION['menu_rla'] == 'pas_photo'	? 'checked' : '';
$st_check_repert		= $_SESSION['menu_rla'] == 'repert'		? 'checked' : '';
$st_check_sans			= $_SESSION['menu_rla'] == 'sans'		? 'checked' : '';
$st_check_non_comm		= $_SESSION['menu_rla'] == 'non_comm'	? 'checked' : '';
$st_check_program		= $_SESSION['menu_rla'] == 'program'	? 'checked' : '';
$st_check_publication	= $_SESSION['menu_rla'] == 'publication'	? 'checked' : '';
$st_check_complete		= $_SESSION['menu_rla'] == 'complete'	? 'checked' : '';

$st_titre['releve']		= "Liasses ayant été relevées";
$st_titre['pas_releve']	= "Liasses n'ayant pas été relevées";
$st_titre['publi_pap']	= "Liasses ayant fait l'objet d'une publication papier";
$st_titre['publi_num']	= "Liasses ayant fait l'objet d'une publication numérique";
$st_titre['photo']		= "Liasses ayant été photographiées";
$st_titre['pas_photo']	= "Liasses n'ayant pas été photographiées";
$st_titre['repert']		= "Répertoires";
$st_titre['sans']		= "Liasses sans notaire, dates ou lieu";
$st_titre['non_comm']	= "Liasses non communicables";
$st_titre['program']	= "Programmations";
$st_titre['publication']	= "Publications papier";
$st_titre['complete']	= "Liste complète";

print('<div align=center><form name="choixmenu" id="choixmenu" action="' . basename(filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_STRING)) . '" method="post">');
print('<div class="panel panel-primary">');
print('<div class="panel-heading">Recherches avancées sur les liasses</div>');
print('<div class="panel-body">');

print("<div class='form-row col-md-12'><div class='form-group col-md-3'>");
print("<input type='radio' name='menu' value='releve' " . $st_check_revele . " onclick='document.getElementById(\"choixmenu\").submit()'> " . $st_titre['releve']);
print("</div><div class='form-group col-md-5'>");
print("<input type='radio' name='menu' value='publi_pap' " . $st_check_publi_pap . " onclick='document.getElementById(\"choixmenu\").submit()'> " . $st_titre['publi_pap'] . "<br>");
print("</div><div class='form-group col-md-4'>");
print("<input type='radio' name='menu' value='photo' " . $st_check_photo . " onclick='document.getElementById(\"choixmenu\").submit()'> " . $st_titre['photo'] . "<br>");
print("</div></div>");

print("<div class='form-row col-md-12'><div class='form-group col-md-3'>");
print("<input type='radio' name='menu' value='pas_releve' " . $st_check_pas_releve . " onclick='document.getElementById(\"choixmenu\").submit()'> " . $st_titre['pas_releve'] . "<br>");
print("</div><div class='form-group col-md-5'>");
print("<input type='radio' name='menu' value='publi_num' " . $st_check_publi_num . " onclick='document.getElementById(\"choixmenu\").submit()'> " . $st_titre['publi_num'] . "<br>");
print("</div><div class='form-group col-md-4'>");
print("<input type='radio' name='menu' value='pas_photo' " . $st_check_pas_photo . " onclick='document.getElementById(\"choixmenu\").submit()'> " . $st_titre['pas_photo'] . "<br>");
print("</div></div>");

print("<div class='form-row col-md-12'><div class='form-group col-md-3'>");
print("<input type='radio' name='menu' value='repert' " . $st_check_repert . " onclick='document.getElementById(\"choixmenu\").submit()'> " . $st_titre['repert'] . "<br>");
print("</div><div class='form-group col-md-5'>");
print("<input type='radio' name='menu' value='sans' " . $st_check_sans . " onclick='document.getElementById(\"choixmenu\").submit()'> " . $st_titre['sans'] . "<br>");
print("</div><div class='form-group col-md-4'>");
print("<input type='radio' name='menu' value='non_comm' " . $st_check_non_comm . " onclick='document.getElementById(\"choixmenu\").submit()'> " . $st_titre['non_comm'] . "<br>");
print("</div></div>");

print("<div class='form-row col-md-12'><div class='form-group col-md-3'>");
print("<input type='radio' name='menu' value='program' " . $st_check_program . " onclick='document.getElementById(\"choixmenu\").submit()'> " . $st_titre['program'] . "<br>");
print("</div><div class='form-group col-md-5'>");
print("<input type='radio' name='menu' value='publication' " . $st_check_publication . " onclick='document.getElementById(\"choixmenu\").submit()'> " . $st_titre['publication'] . "<br>");
print("</div><div class='form-group col-md-4'>");
print("<input type='radio' name='menu' value='complete' " . $st_check_complete . " onclick='document.getElementById(\"choixmenu\").submit()'> " . $st_titre['complete'] . "<br>");
print("</div></div>");

print("</div></div></form>");

$a_serie_liasse = $connexionBD->liste_valeur_par_clef("SELECT serie_liasse, nom FROM serie_liasse order by ordre");
$a_serie_liasse = array(0 => '') + $a_serie_liasse;

$a_forme_liasse = $connexionBD->liste_valeur_par_clef("SELECT idf, nom FROM forme_liasse order by nom");
$a_forme_liasse = array(0 => '') + $a_forme_liasse;

$a_communes = $connexionBD->liste_valeur_par_clef("SELECT idf, nom FROM commune_acte order by nom");
$a_communes = array(0 => 'Toutes') + $a_communes;

if (isset($_POST['serie_liasse'])) {
	$_SESSION['serie_liasse'] = $_POST['serie_liasse'];
} elseif (isset($_GET['serie_liasse'])) {
	$_SESSION['serie_liasse'] = $_GET['serie_liasse'];
}
if (!isset($_SESSION['serie_liasse'])) {
	$_SESSION['serie_liasse'] = '2E';
}
$st_serie_liasse = $_SESSION['serie_liasse'];

$gst_cote_debut				= '';
$gst_cote_fin				= '';
$gst_repertoire				= 'non';
$gst_sans_notaire			= 'non';
$gst_sans_periode			= 'non';
$gst_sans_lieu				= 'non';
$gst_non_comm				= 'non';
$gst_releve					= 'non';
$gst_photo					= 'non';
$gst_pas_photo				= 'non';
$gst_pas_publi_num			= 'non';
$gst_publi_pap				= 'non';
$gst_pas_publi_pap			= 'non';
$gst_av_1793				= 'non';
$gst_sans_photographe		= 'non';
$gst_sans_date_photo		= 'non';
$gst_avec_commentaire		= 'non';
$gi_commune					= 0;
$gi_forme_liasse			= 0;

if ($_SESSION['menu_rla'] != '') {
	print('<div class="panel panel-primary">');
	print('<div class="panel-heading">' . $st_titre[$_SESSION['menu_rla']] . '</div>');
	print('<div class="panel-body">');
	print('<form id="critere" action="ReponsesActionsLiasse.php" method="post">');
	print("<input type=hidden name=menu id=menu value='" . $_SESSION['menu_rla'] . "'>");
	if ($_SESSION['menu_rla'] != 'publication') {
		print("<div class='form-row col-md-12'>" .
			"<div class='form-group col-md-4' align='right'><label class='col-form-label'>Série de liasses&nbsp;</label></div>" .
			"<div class='form-group col-md-4' align='left'><select name=serie_liasse id='serie_liasse' class='form-control' " .
			"     onChange='window.location=\"" . basename(filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_STRING)) . "?serie_liasse=\"+this.value;'>" .
			chaine_select_options($st_serie_liasse, $a_serie_liasse) . "</select></div></div>");
	}
	switch ($_SESSION['menu_rla']) {
		case 'releve':
			print("<div class='form-row col-md-12'>");
			print("<div class='form-group col-md-4' align='right'><label class='col-form-label'>Première cote&nbsp;</label></div>" .
				"<div class='form-group col-md-2'>" .
				"<input type=number name=cote_debut id=cote_debut size=5 maxlength='5' value='" . $gst_cote_debut . "' class='form-control'></div>");
			print("<div class='form-group col-md-2' align='right'><label class='col-form-label'>Dernière cote&nbsp;</label></div>" .
				"<div class='form-group col-md-2'>" .
				"<input type=number name=cote_fin id=cote_fin size=5 maxlength='5' value='" . $gst_cote_fin . "' class='form-control'></div>");
			print("</div>");
			print("<div class='form-row col-md-12'>");
			print("<div class='form-group col-md-4' align='right'><label class='col-form-label'>Commune&nbsp;</label></div>" .
				"<div class='form-group col-md-4' align='left'><select name=commune id='commune' class='js-select-avec-recherche form-control'>" .
				chaine_select_options($gi_commune, $a_communes) . "</select></div></div>");
			print("<div class='form-row col-md-12'>");
			print("<div class='form-group col-md-4' align='right'><label class='col-form-label'>Forme de liasses&nbsp;</label></div>" .
				"<div class='form-group col-md-3' align='left'><select name=forme_liasse id='forme_liasse' class='js-select-avec-recherche form-control'>" .
				chaine_select_options($gi_forme_liasse, $a_forme_liasse) . "</select></div></div>");
			print('<div class="form-row col-md-12">');
			print('<div class="form-group col-md-4" align="right"><label class="col-form-label">Restreindre aux liasses non communicables&nbsp;</label></div>' .
				'<div class="form-group col-md-1" align="left"><div class="form-check">' .
				'<input type="checkbox" class="form-check-input" name="non_comm" id="non_comm" value="1" ');
			if ($gst_non_comm == 1) {
				print('checked>');
			} else {
				print('unchecked>');
			}
			print("</div></div>");
			print('<div class="form-group col-md-4" align="right"><label class="col-form-label">Restreindre aux liasses antérieures à 1793&nbsp;</label></div>' .
				'<div class="form-group col-md-1" align="left"><div class="form-check">' .
				'<input type="checkbox" class="form-check-input" name="av_1793" id="av_1793" value="1" ');
			if ($gst_av_1793 == 1) {
				print('checked>');
			} else {
				print('unchecked>');
			}
			print("</div></div></div>");
			print('<div class="form-row col-md-12">');
			print('<div class="form-group col-md-4" align="right"><label class="col-form-label">Restreindre aux liasses photographiées&nbsp;</label></div>' .
				'<div class="form-group col-md-1" align="left"><div class="form-check">' .
				'<input type="checkbox" class="form-check-input" name="photo" id="photo" value="1" ');
			if ($gst_photo == 1) {
				print('checked>');
			} else {
				print('unchecked>');
			}
			print("</div></div>");
			print('<div class="form-group col-md-4" align="right"><label class="col-form-label">Restreindre aux liasses non photographiées&nbsp;</label></div>' .
				'<div class="form-group col-md-1" align="left"><div class="form-check">' .
				'<input type="checkbox" class="form-check-input" name="pas_photo" id="pas_photo" value="1" ');
			if ($gst_pas_photo == 1) {
				print('checked>');
			} else {
				print('unchecked>');
			}
			print("</div></div></div>");
			break;
		case 'pas_releve':
			print("<div class='form-row col-md-12'>");
			print("<div class='form-group col-md-4' align='right'><label class='col-form-label'>Première cote&nbsp;</label></div>" .
				"<div class='form-group col-md-2'>" .
				"<input type=number name=cote_debut id=cote_debut size=5 maxlength='5' value='" . $gst_cote_debut . "' class='form-control'onKeyPress='SoumissionAction(0,event)' ></div>");
			print("<div class='form-group col-md-2' align='right'><label class='col-form-label'>Dernière cote&nbsp;</label></div>" .
				"<div class='form-group col-md-2'>" .
				"<input type=number name=cote_fin id=cote_fin size=5 maxlength='5' value='" . $gst_cote_fin . "' class='form-control'onKeyPress='SoumissionAction(0,event)' ></div>");
			print("</div>");
			print("<div class='form-row col-md-12'>");
			print("<div class='form-group col-md-4' align='right'><label class='col-form-label'>Commune&nbsp;</label></div>" .
				"<div class='form-group col-md-4' align='left'><select name=commune id='commune' class='js-select-avec-recherche form-control'>" .
				chaine_select_options($gi_commune, $a_communes) . "</select></div></div>");
			print("<div class='form-row col-md-12'>");
			print("<div class='form-group col-md-4' align='right'><label class='col-form-label'>Forme de liasses&nbsp;</label></div>" .
				"<div class='form-group col-md-3' align='left'><select name=forme_liasse id='forme_liasse' class='js-select-avec-recherche form-control'>" .
				chaine_select_options($gi_forme_liasse, $a_forme_liasse) . "</select></div></div>");
			print('<div class="form-row col-md-12">');
			print('<div class="form-group col-md-4" align="right"><label class="col-form-label">Restreindre aux liasses non communicables&nbsp;</label></div>' .
				'<div class="form-group col-md-1" align="left"><div class="form-check">' .
				'<input type="checkbox" class="form-check-input" name=non_comm id=non_comm value="1" ');
			if ($gst_non_comm == 1) {
				print('checked>');
			} else {
				print('unchecked>');
			}
			print("</div></div>");
			print('<div class="form-group col-md-4" align="right"><label class="col-form-label">Restreindre aux liasses antérieures à 1793&nbsp;</label></div>' .
				'<div class="form-group col-md-1" align="left"><div class="form-check">' .
				'<input type="checkbox" class="form-check-input" name=av_1793 id=av_1793 value="1" ');
			if ($gst_av_1793 == 1) {
				print('checked>');
			} else {
				print('unchecked>');
			}
			print("</div></div></div>");
			break;
		case 'publi_pap':
			print("<div class='form-row col-md-12'>");
			print("<div class='form-group col-md-4' align='right'><label class='col-form-label'>Première cote&nbsp;</label></div>" .
				"<div class='form-group col-md-2'>" .
				"<input type=number name=cote_debut id=cote_debut size=5 maxlength='5' value='" . $gst_cote_debut . "' class='form-control'onKeyPress='SoumissionAction(0,event)' ></div>");
			print("<div class='form-group col-md-2' align='right'><label class='col-form-label'>Dernière cote&nbsp;</label></div>" .
				"<div class='form-group col-md-2'>" .
				"<input type=number name=cote_fin id=cote_fin size=5 maxlength='5' value='" . $gst_cote_fin . "' class='form-control'onKeyPress='SoumissionAction(0,event)' ></div>");
			print("</div>");
			print("<div class='form-row col-md-12'>");
			print("<div class='form-group col-md-4' align='right'><label class='col-form-label'>Commune&nbsp;</label></div>" .
				"<div class='form-group col-md-4' align='left'><select name=commune id='commune' class='js-select-avec-recherche form-control'>" .
				chaine_select_options($gi_commune, $a_communes) . "</select></div></div>");

			print('<div class="form-row col-md-12">');
			print('<div class="form-group col-md-4" align="right"><label class="col-form-label">Restreindre aux liasses non publiées numérique&nbsp;</label></div>' .
				'<div class="form-group col-md-1" align="left"><div class="form-check">' .
				'<input type="checkbox" class="form-check-input" name=pas_publi_num id=pas_publi_num value="1" ');
			if ($gst_pas_publi_num == 1) {
				print('checked>');
			} else {
				print('unchecked>');
			}
			print("</div></div></div>");
			break;
		case 'publi_num':
			print("<div class='form-row col-md-12'>");
			print("<div class='form-group col-md-4' align='right'><label class='col-form-label'>Première cote&nbsp;</label></div>" .
				"<div class='form-group col-md-2'>" .
				"<input type=number name=cote_debut id=cote_debut size=5 maxlength='5' value='" . $gst_cote_debut . "' class='form-control'onKeyPress='SoumissionAction(0,event)' ></div>");
			print("<div class='form-group col-md-2' align='right'><label class='col-form-label'>Dernière cote&nbsp;</label></div>" .
				"<div class='form-group col-md-2'>" .
				"<input type=number name=cote_fin id=cote_fin size=5 maxlength='5' value='" . $gst_cote_fin . "' class='form-control'onKeyPress='SoumissionAction(0,event)' ></div>");
			print("</div>");
			print("<div class='form-row col-md-12'>");
			print("<div class='form-group col-md-4' align='right'><label class='col-form-label'>Commune&nbsp;</label></div>" .
				"<div class='form-group col-md-4' align='left'><select name=commune id='commune' class='js-select-avec-recherche form-control'>" .
				chaine_select_options($gi_commune, $a_communes) . "</select></div></div>");

			print('<div class="form-row col-md-12">');
			print('<div class="form-group col-md-4" align="right"><label class="col-form-label">Restreindre aux liasses publiées papier&nbsp;</label></div>' .
				'<div class="form-group col-md-1" align="left"><div class="form-check">' .
				'<input type="checkbox" class="form-check-input" name=publi_pap id=publi_pap value="1" ');
			if ($gst_publi_pap == 1) {
				print('checked>');
			} else {
				print('unchecked>');
			}
			print("</div></div>");
			print('<div class="form-group col-md-4" align="right"><label class="col-form-label">Restreindre aux liasses non publiées papier&nbsp;</label></div>' .
				'<div class="form-group col-md-1" align="left"><div class="form-check">' .
				'<input type="checkbox" class="form-check-input" name=pas_publi_pap id=pas_publi_pap value="1" ');
			if ($gst_pas_publi_pap == 1) {
				print('checked>');
			} else {
				print('unchecked>');
			}
			print("</div></div></div>");
			break;
		case 'photo':
			print("<div class='form-row col-md-12'>");
			print("<div class='form-group col-md-4' align='right'><label class='col-form-label'>Première cote&nbsp;</label></div>" .
				"<div class='form-group col-md-2'>" .
				"<input type=number name=cote_debut id=cote_debut size=5 maxlength='5' value='" . $gst_cote_debut . "' class='form-control'onKeyPress='SoumissionAction(0,event)' ></div>");
			print("<div class='form-group col-md-2' align='right'><label class='col-form-label'>Dernière cote&nbsp;</label></div>" .
				"<div class='form-group col-md-2'>" .
				"<input type=number name=cote_fin id=cote_fin size=5 maxlength='5' value='" . $gst_cote_fin . "' class='form-control'onKeyPress='SoumissionAction(0,event)' ></div>");
			print("</div>");
			print("<div class='form-row col-md-12'>");
			print("<div class='form-group col-md-4' align='right'><label class='col-form-label'>Commune&nbsp;</label></div>" .
				"<div class='form-group col-md-4' align='left'><select name=commune id='commune' class='js-select-avec-recherche form-control'>" .
				chaine_select_options($gi_commune, $a_communes) . "</select></div></div>");
			print("<div class='form-row col-md-12'>");
			print("<div class='form-group col-md-4' align='right'><label class='col-form-label'>Forme de liasses&nbsp;</label></div>" .
				"<div class='form-group col-md-3' align='left'><select name=forme_liasse id='forme_liasse' class='js-select-avec-recherche form-control'>" .
				chaine_select_options($gi_forme_liasse, $a_forme_liasse) . "</select></div></div>");
			print('<div class="form-row col-md-12">');
			print('<div class="form-group col-md-4" align="right"><label class="col-form-label">Restreindre aux liasses non communicables&nbsp;</label></div>' .
				'<div class="form-group col-md-1" align="left"><div class="form-check">' .
				'<input type="checkbox" class="form-check-input" name=non_comm id=non_comm value="1" ');
			if ($gst_non_comm == 1) {
				print('checked>');
			} else {
				print('unchecked>');
			}
			print("</div></div>");
			print('<div class="form-group col-md-4" align="right"><label class="col-form-label">Restreindre aux liasses non publiées papier&nbsp;</label></div>' .
				'<div class="form-group col-md-1" align="left"><div class="form-check">' .
				'<input type="checkbox" class="form-check-input" name=pas_publi_pap id=pas_publi_pap value="1" ');
			if ($gst_pas_publi_pap == 1) {
				print('checked>');
			} else {
				print('unchecked>');
			}
			print("</div></div></div>");
			print('<div class="form-row col-md-12">');
			print('<div class="form-group col-md-4" align="right"><label class="col-form-label">Restreindre aux liasses non publiées numérique&nbsp;</label></div>' .
				'<div class="form-group col-md-1" align="left"><div class="form-check">' .
				'<input type="checkbox" class="form-check-input" name=pas_publi_num id=pas_publi_num value="1" ');
			if ($gst_pas_publi_num == 1) {
				print('checked>');
			} else {
				print('unchecked>');
			}
			print("</div></div>");
			print('<div class="form-group col-md-4" align="right"><label class="col-form-label">Restreindre aux liasses sans photographe&nbsp;</label></div>' .
				'<div class="form-group col-md-1" align="left"><div class="form-check">' .
				'<input type="checkbox" class="form-check-input" name=sans_photographe id=sans_photographe value="1" ');
			if ($gst_sans_photographe == 1) {
				print('checked>');
			} else {
				print('unchecked>');
			}
			print("</div></div></div>");
			print('<div class="form-row col-md-12">');
			print('<div class="form-group col-md-4" align="right"><label class="col-form-label">Restreindre aux liasses sans date de photo&nbsp;</label></div>' .
				'<div class="form-group col-md-1" align="left"><div class="form-check">' .
				'<input type="checkbox" class="form-check-input" name=sans_date_photo id=sans_date_photo value="1" ');
			if ($gst_sans_date_photo == 1) {
				print('checked>');
			} else {
				print('unchecked>');
			}
			print("</div></div>");
			print('<div class="form-group col-md-4" align="right"><label class="col-form-label">Liste avec commentaires&nbsp;</label></div>' .
				'<div class="form-group col-md-1" align="left"><div class="form-check">' .
				'<input type="checkbox" class="form-check-input" name=avec_commentaire id=avec_commentaire value="1" ');
			if ($gst_avec_commentaire == 1) {
				print('checked>');
			} else {
				print('unchecked>');
			}
			print("</div></div></div>");
			break;
		case 'pas_photo':
			print("<div class='form-row col-md-12'>");
			print("<div class='form-group col-md-4' align='right'><label class='col-form-label'>Première cote&nbsp;</label></div>" .
				"<div class='form-group col-md-2'>" .
				"<input type=number name=cote_debut id=cote_debut size=5 maxlength='5' value='" . $gst_cote_debut . "' class='form-control'onKeyPress='SoumissionAction(0,event)' ></div>");
			print("<div class='form-group col-md-2' align='right'><label class='col-form-label'>Dernière cote&nbsp;</label></div>" .
				"<div class='form-group col-md-2'>" .
				"<input type=number name=cote_fin id=cote_fin size=5 maxlength='5' value='" . $gst_cote_fin . "' class='form-control'onKeyPress='SoumissionAction(0,event)' ></div>");
			print("</div>");
			print("<div class='form-row col-md-12'>");
			print("<div class='form-group col-md-4' align='right'><label class='col-form-label'>Commune&nbsp;</label></div>" .
				"<div class='form-group col-md-4' align='left'><select name=commune id='commune' class='js-select-avec-recherche form-control'>" .
				chaine_select_options($gi_commune, $a_communes) . "</select></div></div>");
			print("<div class='form-row col-md-12'>");
			print("<div class='form-group col-md-4' align='right'><label class='col-form-label'>Forme de liasses&nbsp;</label></div>" .
				"<div class='form-group col-md-3' align='left'><select name=forme_liasse id='forme_liasse' class='js-select-avec-recherche form-control'>" .
				chaine_select_options($gi_forme_liasse, $a_forme_liasse) . "</select></div></div>");
			print('<div class="form-row col-md-12">');
			print('<div class="form-group col-md-4" align="right"><label class="col-form-label">Restreindre aux liasses non communicables&nbsp;</label></div>' .
				'<div class="form-group col-md-1" align="left"><div class="form-check">' .
				'<input type="checkbox" class="form-check-input" name=non_comm id=non_comm value="1" ');
			if ($gst_non_comm == 1) {
				print('checked>');
			} else {
				print('unchecked>');
			}
			print("</div></div>");
			print('<div class="form-group col-md-4" align="right"><label class="col-form-label">Restreindre aux liasses antérieures à 1793&nbsp;</label></div>' .
				'<div class="form-group col-md-1" align="left"><div class="form-check">' .
				'<input type="checkbox" class="form-check-input" name=av_1793 id=av_1793 value="1" ');
			if ($gst_av_1793 == 1) {
				print('checked>');
			} else {
				print('unchecked>');
			}
			print("</div></div></div>");
			break;
		case 'repert':
			print("<div class='form-row col-md-12'>");
			print("<div class='form-group col-md-4' align='right'><label class='col-form-label'>Commune&nbsp;</label></div>" .
				"<div class='form-group col-md-4' align='left'><select name=commune id='commune' class='js-select-avec-recherche form-control'>" .
				chaine_select_options($gi_commune, $a_communes) . "</select></div></div>");
			print('<div class="form-row col-md-12">');
			print('<div class="form-group col-md-4" align="right"><label class="col-form-label">Restreindre aux liasses antérieures à 1793&nbsp;</label></div>' .
				'<div class="form-group col-md-1" align="left"><div class="form-check">' .
				'<input type="checkbox" class="form-check-input" name=av_1793 id=av_1793 value="1" ');
			if ($gst_av_1793 == 1) {
				print('checked>');
			} else {
				print('unchecked>');
			}
			print("</div></div></div>");
			break;
		case 'sans':
			print('<div class="form-row col-md-12">');
			print('<div class="form-group col-md-4" align="right"><label class="col-form-label">Liasses sans notaire&nbsp;</label></div>' .
				'<div class="form-group col-md-1" align="left"><div class="form-check">' .
				'<input type="checkbox" class="form-check-input" name=sans_notaire id=sans_notaire value="1" ');
			if ($gst_sans_notaire == 1) {
				print('checked>');
			} else {
				print('unchecked>');
			}
			print("</div></div></div>");
			print('<div class="form-row col-md-12">');
			print('<div class="form-group col-md-4" align="right"><label class="col-form-label">Liasses sans date&nbsp;</label></div>' .
				'<div class="form-group col-md-1" align="left"><div class="form-check">' .
				'<input type="checkbox" class="form-check-input" name=sans_periode id=sans_periode value="1" ');
			if ($gst_sans_periode == 1) {
				print('checked>');
			} else {
				print('unchecked>');
			}
			print("</div></div></div>");
			print('<div class="form-row col-md-12">');
			print('<div class="form-group col-md-4" align="right"><label class="col-form-label">Liasses sans lieu&nbsp;</label></div>' .
				'<div class="form-group col-md-1" align="left"><div class="form-check">' .
				'<input type="checkbox" class="form-check-input" name=sans_lieu id=sans_lieu value="1" ');
			if ($gst_sans_lieu == 1) {
				print('checked>');
			} else {
				print('unchecked>');
			}
			print("</div></div></div>");
			break;
		case 'non_comm':
			print("<div class='form-row col-md-12'>");
			print("<div class='form-group col-md-4' align='right'><label class='col-form-label'>Commune&nbsp;</label></div>" .
				"<div class='form-group col-md-4' align='left'><select name=commune id='commune' class='js-select-avec-recherche form-control'>" .
				chaine_select_options($gi_commune, $a_communes) . "</select></div></div>");
			print('<div class="form-row col-md-12">');
			print('<div class="form-group col-md-4" align="right"><label class="col-form-label">Restreindre aux liasses antérieures à 1793&nbsp;</label></div>' .
				'<div class="form-group col-md-1" align="left"><div class="form-check">' .
				'<input type="checkbox" class="form-check-input" name=av_1793 id=av_1793 value="1" ');
			if ($gst_av_1793 == 1) {
				print('checked>');
			} else {
				print('unchecked>');
			}
			print("</div></div></div>");
			break;
		case 'program':
			print("<div class='form-row col-md-12'>");
			print("<div class='form-group col-md-4' align='right'><label class='col-form-label'>Commune&nbsp;</label></div>" .
				"<div class='form-group col-md-4' align='left'><select name=commune id='commune' class='js-select-avec-recherche form-control'>" .
				chaine_select_options($gi_commune, $a_communes) . "</select></div></div>");
			print('<div class="form-row col-md-12">');
			print('<div class="form-group col-md-4" align="right"><label class="col-form-label">Uniquement les programmations de relevés&nbsp;</label></div>' .
				'<div class="form-group col-md-1" align="left"><div class="form-check">' .
				'<input type="checkbox" class="form-check-input" name=releve id=releve value="1" ');
			if ($gst_releve == 1) {
				print('checked>');
			} else {
				print('unchecked>');
			}
			print("</div></div></div>");
			print('<div class="form-row col-md-12">');
			print('<div class="form-group col-md-4" align="right"><label class="col-form-label">Uniquement les programmations de photographies&nbsp;</label></div>' .
				'<div class="form-group col-md-1" align="left"><div class="form-check">' .
				'<input type="checkbox" class="form-check-input" name=photo id=photo value="1" ');
			if ($gst_photo == 1) {
				print('checked>');
			} else {
				print('unchecked>');
			}
			print("</div></div></div>");
			break;
	}
	print('<div class="btn-group col-md-9 col-md-offset-3" role="group">');
	print('<button type=submit class="btn btn-sm btn-primary" id="btRechercher"><span class="glyphicon glyphicon-search"></span>  Rechercher</button>');
	print('<button type=button class="btn btn-sm btn-warning" id="btRaz"><span class="glyphicon glyphicon-erase"></span> Effacer tous les Champs</button>');
	print('</div>');

	print("</form>");
	print('</div></div>');
}
print('</div></body></html>');
