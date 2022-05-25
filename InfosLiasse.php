<?php

require_once __DIR__ . '/app/bootstrap.php';
require_once __DIR__ . '/Commun/commun.php';
require_once __DIR__ . '/RequeteRecherche.php';
require_once __DIR__ . '/Commun/VerificationDroits.php';

$gst_cote_liasse = isset($_REQUEST['cote_liasse']) ? $_REQUEST['cote_liasse'] : '';

list($st_cote, $i_depose_ad, $st_idf_dept_depose_ad, $i_liasse_consult, $st_info_compl)
	= $connexionBD->sql_select_listeUtf8("select cote_liasse, in_liasse_depose_ad, idf_dept_depose_ad, " .
		"       in_liasse_consultable, info_complementaires " .
		"from liasse " .
		"where cote_liasse='" . $gst_cote_liasse . "'");
$a_depts_depose_ad = $connexionBD->liste_valeur_par_clefUtf8("SELECT idf,nom FROM departement order by nom");
if ($i_depose_ad == 1) {
	$st_depose_ad = "Oui";
	$st_dept_depose_ad = $a_depts_depose_ad[$st_idf_dept_depose_ad];
} else {
	$st_depose_ad = "Non";
	$st_dept_depose_ad = '';
}
if ($i_liasse_consult == 1)
	$st_liasse_consult = "Oui";
else
	$st_liasse_consult = "Non";

print('<!DOCTYPE html>');
print("<head>\n");
print('<meta http-equiv="content-language" content="fr">');
print('<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" >');
print('<link rel="shortcut icon" href="assets/img/favicon.ico">');
print("<link href='assets/css/styles.css' type='text/css' rel='stylesheet'>");
print("<link href='assets/css/bootstrap.min.css' rel='stylesheet'>");
print("<script src='assets/js/jquery-min.js' type='text/javascript'></script>");
print("<script src='assets/js/bootstrap.min.js' type='text/javascript'></script>");
print('<title>Base ".SIGLE_ASSO.": Reponses a une recherche de liasse - Infos sur la liasse</title>');
?>
<script type='text/javascript'>
	$(document).ready(function() {


		$('#fermer').click(function() {
			window.close();
		});

	});
</script>
<?php
print('</head>');
print("<body>");
print('<div class="container">');
print("<form   method=\"post\">");
print("<div class=\"text-center\"><img src=\"$gst_logo_association\" alt='Logo " . SIGLE_ASSO . "'></div>");
print('<div class="panel panel-primary">');
print("<div class=\"panel-heading\">Informations sur la liasse $st_cote</div>");
print('<div class="panel-body">');
print("<table class=\"table table-bordered table-striped\">");
print("<tr><th>Liasse déposée aux AD</th><td>" . $st_depose_ad . "</td></tr>");
print("<tr><th>Département AD</th><td>" . $st_dept_depose_ad . "</td></tr>");
print("<tr><th>Liasse consultable</th><td>" . $st_liasse_consult . "</td></tr>");
print("<tr><th>Informations complémentaires</th><td>" . $st_info_compl . "</td></tr>");
print("</table>");
print("</div>");
print('<button type="button" id="fermer" class="btn btn-warning col-xs-4 col-xs-offset-4"><span class="glyphicon glyphicon-remove"></span> Fermer la fen&ecirc;tre</button>');
print('</form></div></body></html>');
