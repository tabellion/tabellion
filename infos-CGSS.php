<?php

require_once __DIR__ . '/app/bootstrap.php';

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
	<script type='text/javascript'>
		$(document).ready(function() {
			$("#ferme").click(function() {
				window.close();
			});
		});
	</script>

</head>

<body>
	<div class="container">
		<div class="text-center"><img src="<?= $gst_logo_association; ?>" alt="Logo <?= SIGLE_ASSO; ?>"></div>
		<div>Ce mariage filiatif a été deposé par une autre association :</div><br>
		<div>
			Le Cercle Généalogique de Sud Saintonge avec lequel l'AGC a un accord direct de partenariat.
		</div><br>
		<div>Pour obtenir cette filiation, merci d'en faire la demande à l'adresse
			<a href="mailto:<?= EMAIL_DIRASSO; ?>?subject=Rep_Notaire_non_relevé"><?= EMAIL_DIRASSO; ?></a>, en précisant
		</div>
		<div>1) les nom des époux, date et lieux du mariage demandé (copier/coller à partir de la base)</div>
		<div>2) vos NOM, prénom et Numéro d'adhérent AGC</div>
		<div>Nous transmettrons cette demande et sa réponse dès que possible.</div><br>
		<div>Géné@micalement</div><br>
		<div>Les gestionnaires de la base AGC</div>
		<div class="form-row">
			<button type="button" id=ferme class="btn btn-warning col-xs-4 col-xs-offset-4">Fermer la fenêtre</button>
		</div>
</body>

</html>