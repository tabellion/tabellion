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
		<div>Ce relevé de mariage a été deposé par une autre association:<br></div>
		<div><br>L'association Chabatz d'entrar avec laquelle l'AGC a un accord de partenariat par le biais des AGL (Amitiés Généalogiques du Limousin).<br></div>
		<div><br>Le relevé est un relevé de Table Décennale, il ne comporte donc pas de filiation.<br></div>
		<div><br>Pour en savoir plus, vous êtes invités à rentrer en contact directement avec:<br></div>
		<div>Chabatz d'entrar : <a href="http://cdentrar.free.fr" target="_blank">http://cdentrar.free.fr</a><br></div>
		<div><br>Gene@micalement</div>
		<div><br>Les gestionnaires de la base AGC</div>
		<div class="form-row">
			<button type="button" id=ferme class="btn btn-warning col-xs-4 col-xs-offset-4">Fermer la fenêtre</button>
		</div>
</body>

</html>