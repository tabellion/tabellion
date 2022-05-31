<?php

require_once __DIR__ . '/app/bootstrap.php';
require_once __DIR__ . '/Origin/RequeteRecherche.php';

$gst_cote_liasse = $_REQUEST['cote_liasse'] ?? null; 

if (!$gst_cote_liasse) {
    die("Erreur: L'identifiant de l'acte est manquant");
}

$sql1 = "SELECT cote_liasse, in_liasse_depose_ad, idf_dept_depose_ad, in_liasse_consultable, info_complementaires 
		FROM liasse 
		WHERE cote_liasse=$gst_cote_liasse";
list($st_cote, $i_depose_ad, $st_idf_dept_depose_ad, $i_liasse_consult, $st_info_compl) = $connexionBD->sql_select_listeUtf8($sql1);

$a_depts_depose_ad = $connexionBD->liste_valeur_par_clefUtf8("SELECT idf, nom FROM departement ORDER BY nom");
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

?>
<!DOCTYPE html>
<html lang="fr">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta http-equiv="content-language" content="fr" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link href="assets/css/styles.css" type="text/css" rel="stylesheet">
	<link href="assets/css/bootstrap.min.css" rel="stylesheet">
	<script src="assets/js/jquery-min.js" type="text/javascript"></script>
	<script src="assets/js/bootstrap.min.js" type="text/javascript"></script>
	<title>Base <?= SIGLE_ASSO; ?> : Reponses a une recherche de liasse - Infos sur la liasse</title>
	<script type='text/javascript'>
		$(document).ready(function() {


			$('#fermer').click(function() {
				window.close();
			});

		});
	</script>
</head>

<body>
	<div class="container">
		<form method="post">
			<div class="text-center"><img src="<?= $gst_logo_association; ?>" alt="Logo <?= SIGLE_ASSO; ?>"></div>
			<div class="panel panel-primary">
				<div class="panel-heading">Informations sur la liasse <?= $st_cote; ?></div>
				<div class="panel-body">
					<table class="table table-bordered table-striped">
						<tr>
							<th>Liasse déposée aux AD</th>
							<td><?= $st_depose_ad; ?></td>
						</tr>
						<tr>
							<th>Département AD</th>
							<td><?= $st_dept_depose_ad; ?></td>
						</tr>
						<tr>
							<th>Liasse consultable</th>
							<td><?= $st_liasse_consult; ?></td>
						</tr>
						<tr>
							<th>Informations complémentaires</th>
							<td><?= $st_info_compl; ?></td>
						</tr>
					</table>
				</div>
				<button type="button" id="fermer" class="btn btn-warning col-xs-4 col-xs-offset-4">
					<span class="glyphicon glyphicon-remove"></span> Fermer la fenêtre
				</button>
		</form>
	</div>
</body>

</html>