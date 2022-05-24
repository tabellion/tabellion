<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------
require_once __DIR__ . '/Commun/config.php';
require_once __DIR__ . '/Commun/constantes.php';
require_once __DIR__ . '/Commun/Identification.php';
require_once __DIR__ . '/Commun/ConnexionBD.php';
require_once __DIR__ . '/Commun/commun.php';

$gi_idf_commune_acte = isset($_POST['idf_commune_acte']) ? (int) $_POST['idf_commune_acte'] : '';
$gc_idf_type_acte = isset($_POST['idf_type_acte']) ? (int) $_POST['idf_type_acte'] : '';
$gi_annee_min = isset($_POST['annee_min']) ? (int) $_POST['annee_min'] : '';
$gi_annee_max = isset($_POST['annee_max']) ? (int) $_POST['annee_max'] : '';
$gi_rayon = isset($_POST['rayon']) ? (int) $_POST['rayon'] : '';
$ga_rayons = array(0 => '', 1 => '1 Km', 2 => '2 Km', 3 => '3 Km', 4 => '4 Km', 5 => '5 Km', 6 => '6 Km', 7 => '7 Km', 8 => '8 Km', 9 => '9 Km', 10 => '10 Km');
$connexionBD = ConnexionBD::singleton($gst_serveur_bd, $gst_utilisateur_bd, $gst_mdp_utilisateur_bd, $gst_nom_bd);
$a_communes_acte = $connexionBD->liste_valeur_par_clef("select idf,nom from commune_acte order by nom");
$a_types_acte = $connexionBD->liste_valeur_par_clef("select idf,nom from type_acte where idf in (" . IDF_MARIAGE . ',' . IDF_NAISSANCE . ',' . IDF_DECES . ") order by nom");

print('<!DOCTYPE html>');
print("<head>");
print('<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">');
print('<meta http-equiv="content-language" content="fr">');
print('<meta name="viewport" content="width=device-width, initial-scale=1.0">');
print("<link href='css/styles.css' type='text/css' rel='stylesheet'>");
print("<link href='css/bootstrap.min.css' rel='stylesheet'>");

print("<link href='css/jquery-ui.css' type='text/css' rel='stylesheet'>");
print("<link href='css/jquery-ui.structure.min.css' type='text/css' rel='stylesheet'>");
print("<link href='css/jquery-ui.theme.min.css' type='text/css' rel='stylesheet'>");
print("<link href='css/select2.min.css' type='text/css' rel='stylesheet'>");
print("<link href='css/select2-bootstrap.min.css' type='text/css' rel='stylesheet'>");
print("<script src='js/jquery-min.js' type='text/javascript'></script>");
print("<script src='js/jquery.validate.min.js' type='text/javascript'></script>");
print("<script src='js/additional-methods.min.js' type='text/javascript'></script>");
print("<script src='js/jquery-ui.min.js' type='text/javascript'></script>");

print("<script src='js/select2.min.js' type='text/javascript'></script>");
print("<script src='js/Chart.min.js' type='text/javascript'></script>");
print("<script src='js/bootstrap.min.js' type='text/javascript'></script>");

?>

<script type='text/javascript'>
	$(document).ready(function() {
		$.fn.select2.defaults.set("theme", "bootstrap");

		$(".js-select-avec-recherche").select2();

		function DessineGraphe(reponse) {
			var labels = reponse["labels"];
			var ensemble_donnees = reponse["donnees"];
			var ctx = document.getElementById('MonGraphe').getContext('2d');
			var myChart = new Chart(ctx, {
				title: 'test_graphe',
				type: 'line',
				data: {
					labels: labels,
					datasets: ensemble_donnees
				},
				options: {
					scales: {
						xAxes: [{
							display: true,
							scaleLabel: {
								display: true,
								labelString: 'Annees'
							}
						}],
						yAxes: [{
							display: true,
							scaleLabel: {
								display: true,
								labelString: 'Nombre'
							}
						}]
					}
				}
			});
		}

		$.validator.addMethod('plusGrand', function(value, element, param) {
			if (this.optional(element)) return true;
			var annee_max = $(param).val();
			if (jQuery.trim(annee_max).length == 0) return true;
			var i = parseInt(value);
			var j = parseInt(annee_max);
			return i >= j;
		}, "l'année maximale doit être plus grande que l'année minimale");

		//validation rules
		$("#stats_nmd").validate({
			rules: {
				annee_min: {
					required: true,
					integer: true,
					minlength: 4
				},
				annee_max: {
					required: true,
					integer: true,
					minlength: 4,
					plusGrand: '#annee_min'
				}
			},
			messages: {
				annee_min: {
					required: "L'année minimale est obligatoire",
					integer: "L'année doit être un entier",
					minlength: "L'année doit comporter 4 chiffes"
				},
				annee_max: {
					required: "L'année maximale est obligatoire",
					integer: "L'année doit être un entier",
					minlength: "L'année doit comporter 4 chiffes"
				}
			},
			errorElement: "em",
			errorPlacement: function(error, element) {
				// Add the `help-block` class to the error element
				error.addClass("help-block");

				// Add `has-feedback` class to the parent div.form-group
				// in order to add icons to inputs
				element.parents(".col-md-4").addClass("has-feedback");

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
				$(element).parents(".col-md-4").addClass("has-error").removeClass("has-success");
				$(element).next("span").addClass("glyphicon-remove").removeClass("glyphicon-ok");
			},
			unhighlight: function(element, errorClass, validClass) {
				$(element).parents(".col-md-4").addClass("has-success").removeClass("has-error");
				$(element).next("span").addClass("glyphicon-ok").removeClass("glyphicon-remove");
			},
			submitHandler: function(form) {
				$.ajax({
					type: "GET",
					url: "./ajax/stats_nmd.php",
					data: 'idf_commune_acte=' + $('#idf_commune_acte').val() + '&idf_type_acte=' + $('#idf_type_acte').val() + '&annee_min=' + $('#annee_min').val() + '&annee_max=' + $('#annee_max').val() + '&rayon=' + $('#rayon').val(),
					dataType: 'json',
					cache: false,
					success: function(reponse) {
						DessineGraphe(reponse);
					},
					error: function() {
						console.log('une erreur est survenue');
						return false;
					}
				});
				return false;
			}
		});

	});
</script>

<style>
	#chart-container {
		width: 100%;
		height: auto;
	}
</style>
<?php
print("<title>Stats NMD</title>");
print("</head>");
print("<body>");
print('<div class="container">');

require_once __DIR__ . '/Commun/menu.php';

print('<div class="panel panel-primary">');
print('<div class="panel-heading">Statistiques NMD d\'une commune/paroisse</div>');
print('<div class="panel-body">');

print("<form id='stats_nmd'  method=\"post\">");
print('<input type="hidden" name="mode" value="STATS">');
print('<div class="form-group row">');
print('<label for="idf_commune_acte" class="col-form-label col-md-4">Commune:</label>');
print('<div class="col-md-4">');
print('<select name=idf_commune_acte id=idf_commune_acte class="js-select-avec-recherche form-control">');
print(chaine_select_options($gi_idf_commune_acte, $a_communes_acte));
print('</select></div>');
print('</div>');
print('<div class="form-group row">');
print('<label for="idf_type_acte" class="col-form-label col-md-4">Type d\'acte:</label>');
print('<div class="col-md-4">');
print('<select name=idf_type_acte id=idf_type_acte class="form-control">');
print(chaine_select_options($gc_idf_type_acte, $a_types_acte));
print('</select></div>');
print('</div>');

print('<div class="form-group row">');
print('<label for="rayon" class="col-form-label col-md-4">Rayon:</label>');
print('<div class="col-md-4">');
print('<select name=rayon id=rayon class="form-control">');
foreach ($ga_rayons as $i_rayon => $st_label) {
	if ($gi_rayon == $i_rayon)
		print("<option value=$i_rayon selected=\"selected\">$st_label</option>");
	else
		print("<option value=$i_rayon>$st_label</option>");
}
print('</select></div></div>');

print('<div class="form-group row">');
print('<label for="annee_min" class="col-form-label col-md-4">Annee minimale:</label>');
print('<div class="col-md-4">');
print("<input type=text name=annee_min id=annee_min size=4 maxlength=4 value=\"$gi_annee_min\" class=\"form-control\">");
print('</div></div>');
print('<div class="form-group row">');
print('<label for="annee_max" class="col-form-label col-md-4">Annee maximale:</label>');
print('<div class="col-md-4">');
print("<input type=text name=annee_max id=annee_max size=4 maxlength=4 value=\"$gi_annee_max\" class=\"form-control\">");
print('</div></div>');

print('<div class="form-row">');
print('<button type=submit name=Rechercher class="btn btn-primary col-md-4 col-md-offset-4"><span class="glyphicon glyphicon-stats"></span> Afficher les Statistiques</button>');
print('</div></div></div>');
print('</form>');

print('<div>');
print('<canvas id="MonGraphe" width="900" height="500"></canvas>');
print('</div>');
print('</div>'); // fin panel body
print("</div></body></html>");
