<?php

require_once __DIR__ . '/app/bootstrap.php';

// ========= Request
$gst_type_recherche = $_GET['recherche'] ?? '';

if ($gst_type_recherche == 'nouvelle') {
	$id_departement				= $_GET['idf_dept'] ?? 0;
	$id_commune					= $_GET['idf_ca'] ?? 0;
	$rayon						= null;
	$annee_min					= $_GET['a_min'] ?? null;
	$annee_max					= $_GET['a_max'] ?? null;
	$gst_paroisses_rattachees	= 'oui';
	$gst_nom_notaire			= null;
	$gst_prenom_notaire			= null;
	$gst_variantes				= 'oui';
	$gst_idf_serie_liasse     	= '2E';
	$gst_cote_debut				= null;
	$gst_cote_fin				= null;
	$gst_repertoire				= 'non';
	$gst_sans_notaire			= 'non';
	$gst_sans_periode			= 'non';
	$gst_liasse_releve			= 'non';
}

$departements = array(0 => 'Tous') + $connexionBD->liste_valeur_par_clef("SELECT idf, nom FROM departement ORDER BY idf");
$communes = array(0 => 'Toutes') + $connexionBD->liste_valeur_par_clef("SELECT idf, nom FROM commune_acte ORDER BY nom") + array(-9 => 'Commune inconnue');

$a_serie_liasse = $connexionBD->liste_valeur_par_clef("SELECT serie_liasse, nom FROM serie_liasse ORDER BY ordre");

print('<!DOCTYPE html><html lang="fr">');
print("<head>");
print('<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" >');
print('<meta http-equiv="content-language" content="fr"> ');
print('<title>Base ' . SIGLE_ASSO . ': Vos recherches de liasses notariales</title>');
print('<meta name="viewport" content="width=device-width, initial-scale=1.0">');
print("<link href='assets/css/styles.css' type='text/css' rel='stylesheet'>");
print("<link href='assets/css/bootstrap.min.css' rel='stylesheet'>");
print('<link rel="shortcut icon" href="assets/img/favicon.ico">');
print("<link href='assets/css/jquery-ui.css' type='text/css' rel='stylesheet'>");
print("<link href='assets/css/jquery-ui.structure.min.css' type='text/css' rel='stylesheet'>");
print("<link href='assets/css/jquery-ui.theme.min.css' type='text/css' rel='stylesheet'>");
print("<link href='assets/css/select2.min.css' type='text/css' rel='stylesheet'>");
print("<link href='assets/css/select2-bootstrap.min.css' type='text/css' rel='stylesheet'>");
print("<script src='assets/js/jquery-min.js' type='text/javascript'></script>");
print("<script src='assets/js/jquery.validate.min.js' type='text/javascript'></script>");
print("<script src='assets/js/additional-methods.min.js' type='text/javascript'></script>");
print("<script src='assets/js/jquery-ui.min.js' type='text/javascript'></script>");
print("<script src='assets/js/select2.min.js' type='text/javascript'></script>");
print("<script src='assets/js/bootstrap.min.js' type='text/javascript'></script>");
?>
<script type='text/javascript'>
	$(document).ready(function() {

		$.fn.select2.defaults.set("theme", "bootstrap");

		$(".js-select-avec-recherche").select2();


		$.validator.addMethod('plusGrand', function(value, element, param) {
			if (this.optional(element)) return true;
			var annee_max = $(param).val();
			if (jQuery.trim(annee_max).length == 0) return true;
			var i = parseInt(value);
			var j = parseInt(annee_max);
			return i >= j;
		}, "l'année maximale doit être plus grande que l'année minimale");

		jQuery.validator.addMethod(
			"notaire_sans_notaire",
			function(value, element) {
				var check = false;
				if ($(element).is(':checked')) {
					check = $('#nom_notaire').val() == '';
				}
				return this.optional(element) || check;
			},
			"L'&eacute;lement doit &ecirc;tre vide si la case est coch&eacute;e"
		);

		jQuery.validator.addMethod(
			"annee_min_sans_periode",
			function(value, element) {
				var check = false;
				if ($(element).is(':checked')) {
					check = $('#annee_min').val() == '';
				}
				return this.optional(element) || check;
			},
			"L'&eacute;lement doit &circ;tre vide si la case est coch&eacute;e"
		);

		jQuery.validator.addMethod(
			"annee_max_sans_periode",
			function(value, element) {
				var check = false;
				if ($(element).is(':checked')) {
					check = $('#annee_max').val() == '';
				}
				return this.optional(element) || check;
			},
			"L'&eacute;lement doit &circ;tre vide si la case est coch&eacute;e"
		);

		$("#recherche_liasses").validate({
			rules: {
				cote_debut: {
					integer: true
				},
				cote_fin: {
					integer: true
				},
				annee_min: {
					integer: true,
					minlength: 4
				},
				annee_max: {
					integer: true,
					minlength: 4,
					plusGrand: '#annee_min'
				},
				rayon: {
					integer: true
				},
				sans_periode: {
					annee_min_sans_periode: true,
					annee_max_sans_periode: true
				},
				sans_notaire: {
					notaire_sans_notaire: true
				}

			},
			messages: {
				cote_debut: {
					integer: "La cote doit &ecirc;tre un entier"
				},
				cote_fin: {
					integer: "La cote doit &ecirc;tre un entier"
				},
				annee_min: {
					integer: "L'ann&eacute;e doit &ecirc;tre un entier",
					minlength: "L'ann&eacute;e doit comporter 4 chiffes",
				},
				annee_max: {
					integer: "L'ann&eacute;e doit &ecirc;tre un entier",
					minlength: "L'ann&eacute;e doit comporter 4 chiffes",
					plusGrand: "L'ann&eacute;e max doit &ecirc;tre plus grande que l'ann&eacute;e min"
				},
				rayon: {
					integer: "Le rayon doit &ecirc;tre un entier"
				},
				sans_periode: {
					annee_min_sans_periode: "Ne pas cocher 'liasses sans date' si vous saisissez une ann&eacute;e",
					annee_max_sans_periode: "Ne pas cocher 'liasses sans date' si vous saisissez une ann&eacute;e"
				},
				sans_notaire: {
					notaire_sans_notaire: "Ne pas cocher 'liasses sans notaire' si vous saisissez un nom de notaire"
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

		$("#raz").click(function() {
			$('#idf_serie_liasse').prop('selectedIndex', 0);
			$('#idf_dept').prop('selectedIndex', 0);
			$('#idf_commune_recherche').prop('selectedIndex', 0);
			$("#idf_commune_recherche").val(0).trigger('change');
			$('#rayon_recherches_communes').val('');
			$('#annee_min').val('');
			$('#annee_max').val('');
			$('#nom_notaire').val('');
			$('#prenom_notaire').val('');
			$('#cote_debut').val('');
			$('#cote_fin').val('');
			$('#paroisses_rattachees_recherches_communes').prop('checked', true);
			$('#variantes').prop('checked', true);
			$('#sans_notaire').prop('checked', false);
			$('#sans_periode').prop('checked', false);
			$('#repertoire').prop('checked', false);
			$('#liasse_releve').prop('checked', false);
		});

		$("#idf_commune_recherche").select2({
			allowClear: true,
			placeholder: "Toutes"
		});

	});
</script>
<?php
print("</head>");

print("<body>");
print('<div class="container">');

require_once __DIR__ . '/commun/menu.php';

print('<form id="recherche_liasses" method="post" class="form-inline" action="ReponsesLiasseSimple.php">');

// --------série & département
print('<div class="form-row col-md-12">');
print('<div class="form-group col-md-6">' .
	'<label for="idf_serie_liasse" class="form-col-label">Série liasses&nbsp</label>' .
	'<select name="idf_serie_liasse" id="idf_serie_liasse" class="form-control">' .
	chaine_select_options($gst_idf_serie_liasse, $a_serie_liasse) .
	'</select></div>');
print('<div class="form-group col-md-4"><label for="idf_dept">Département&nbsp</label>' .
	'<select name="idf_dept" id="idf_dept" class="form-control">' . chaine_select_options($id_departement, $departements) . '</select></div>');
print('<br></div>');
print('<div class="form-row col-md-12">&nbsp</div>');

// ---------Commune +++
print('<div class="form-row col-md-12">');
print('<div class="form-group col-md-6"><label for="idf_commune_recherche">Commune/Paroisse&nbsp</label>' .
	'<select name="idf_commune_recherche" id="idf_commune_recherche" class="js-select-avec-recherche form-control">' .
	chaine_select_options($id_commune, $communes) . '</select></div>');
print('<div class="form-group col-md-4">' .
	'<div class="input-group"><span class="input-group-addon">Rayon de recherche:</span>' .
	'<label for="rayon_recherches_communes" class="sr-only">Rayon</label>' .
	'<div class="lib_erreur"><input type=text name=rayon id="rayon_recherches_communes" size=2 maxlength=2 value="' . $rayon . '" class="form-control"></div>' .
	'<span class="input-group-addon">Km</span></div></div>');
print('<div class="form-check"><label for="paroisses_rattachees_recherches_communes" class="form-check-label">Paroisses rattachées&nbsp');
if ($gst_paroisses_rattachees == '')
	print('<input type=checkbox class="form-check-input" name=paroisses_rattachees id="paroisses_rattachees_recherches_communes" value=oui>');
else
	print('<input type=checkbox class="form-check-input" name=paroisses_rattachees id="paroisses_rattachees_recherches_communes" value=oui checked>');
print('</label>');
print('</div>');
print('</div>');
print('<div class="form-row col-md-12">&nbsp</div>');

// -------------Dates
print('<div class="form-row col-md-12">');
print('<div class="form-group col-md-4 col-md-offset-2 lib_erreur">');
print('<label for="annee_min" class="col-form-label">Années de&nbsp</label>');
print("<input type=text name=annee_min id=annee_min size=4 value=\"$annee_min\" class=\"form-control\">");
print('</div>');
print('<div class="form-group col-md-4 lib_erreur">');
print('<label for="annee_max" class="col-form-label">&agrave;&nbsp</label>');
print("<input type=text name=annee_max id=annee_max size =4 value=\"$annee_max\" class=\"form-control\">");
print('</div></div>');
print('<div class="form-row col-md-12">&nbsp</div>');

// -----------Notaire
print('<div class="form-row col-md-12">');
print('<div class="form-group col-md-4 col-md-offset-2">');
print('<label for="nom_notaire" class="col-form-label">Nom Notaire&nbsp</label>');
print("<input type=text name=nom_notaire id=nom_notaire size=15 maxlength=30 value=\"$gst_nom_notaire\" class=\"form-control\">");
print('</div>');

print('<div class="form-group col-md-4">');
print('<label for="prenom_notaire" class="col-form-label">Prénom Notaire&nbsp</label>');
print("<input type=text name=prenom_notaire id=prenom_notaire size=15 maxlength=30 value=\"$gst_prenom_notaire\" class=\"form-control\">");
print('</div>');

print('<div class="form-check"><label for="variantes" class="form-check-label">Variantes connues&nbsp');
if ($gst_variantes == 'non')
	print('<input type=checkbox class="form-check-input" name=variantes id="variantes" value=oui>');
else
	print('<input type=checkbox class="form-check-input" name=variantes id="variantes" value=oui checked>');
print('</label>');
print('</div>');

print('</div>');
print('<div class="form-row col-md-12">&nbsp</div>');

// ------------Cotes
print('<div class="form-row col-md-12">');
print('<div class="form-group col-md-4 col-md-offset-2 lib_erreur">');
print('<label for="cote_debut" class="col-form-label">Première cote&nbsp</label>');
print("<input type=text name=cote_debut id=cote_debut size=5 maxlength=5 value=\"$gst_cote_debut\" class=\"form-control\">");
print('</div>');

print('<div class="form-group col-md-4 lib_erreur">');
print('<label for="cote_fin" class="col-form-label">Dernière cote&nbsp</label>');
print("<input type=text name=cote_fin id=cote_fin size=5 maxlength=5 value=\"$gst_cote_fin\" class=\"form-control\">");
print('</div>');
print('</div>');
print('<div class="form-row col-md-12">&nbsp</div>');

// ------------Sans ...
print('<div class="form-row col-md-12">');

print('<div class="form-check col-md-4 col-md-offset-2">');
print('<div class="form-check ">');
print('<label for="sans_periode" class="form-check-label col-form-label">Liasses sans date&nbsp');
if ($gst_sans_periode == 'non')
	print('   <input type=checkbox name=sans_periode id=sans_periode value=oui unchecked class="form-check-input">');
else
	print('   <input type=checkbox name=sans_periode id=sans_periode value=oui checked class="form-check-input">');
print('</label></div>');
print('</div>');

print('<div class="form-check col-md-4">');
print('<div class="form-check ">');
print('<label for="sans_notaire" class="form-check-label col-form-label">Liasses sans notaire&nbsp');
if ($gst_sans_notaire == 'non')
	print('   <input type=checkbox name=sans_notaire id=sans_notaire value=oui unchecked class="form-check-input">');
else
	print('   <input type=checkbox name=sans_notaire id=sans_notaire value=oui checked class="form-check-input">');
print('</label></div>');

print('</div>');
print('<div class="form-row col-md-12">&nbsp</div>');

// ------------Répertoires
print('<div class="form-row col-md-12">');
print('<div class="form-check col-md-4 col-md-offset-2">');
print('<label for="repertoire" class="form-check-label">Répertoires&nbsp</label>');
if ($gst_repertoire == 'non')
	print('   <input type=checkbox name=repertoire id=repertoire value=oui unchecked class="form-control form-check-input">');
else
	print('   <input type=checkbox name=repertoire id=repertoire value=oui checked class="form-control form-check-input">');
print('</div>');

print('<div class="form-check col-md-4">');
print('<label for="liasse_releve" class="form-check-label">Liasses relevées (CM retranscrits)&nbsp</label>');
if ($gst_liasse_releve == 'non')
	print('   <input type=checkbox name=liasse_releve id=liasse_releve value=oui unchecked class="form-control form-check-input">');
else
	print('   <input type=checkbox name=liasse_releve id=liasse_releve value=oui checked class="form-control form-check-input">');
print('</div>');

print('</div>');
print('<div class="form-row col-md-12">&nbsp</div>');

// boutons
print('<div class="btn-group col-md-4 col-md-offset-4" role="group">');
print('<button type=submit name=Rechercher class="btn btn-primary"><span class="glyphicon glyphicon-search"></span> Rechercher</button>');
print('<button type=button  id="raz" class="btn btn-warning raz"><span class="glyphicon glyphicon-erase"></span> Effacer tous les Champs</button>');
print('</div>');

print("</form>");
print('</div>');
print("</body>");
print("</html>");
