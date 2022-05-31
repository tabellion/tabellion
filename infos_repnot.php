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

		<?php require_once __DIR__ . '/commun/menu.php'; ?>

		<div class="text-center">
			<p>
				Ce CM est issu d'un répertoire de notaire et n'a pas été encore relevé<br>
				Merci de nous contacter à l'adresse
				<a href="mailto:<?= EMAIL_DIRASSO; ?>?subject=Rep_Notaire_non_relevé"><?= EMAIL_DIRASSO; ?></a>
				afin de connaitre la cote de la liasse correspondante déposée aux Archives Départementales de la Charente.
			</p>

			<div class="alert alert-warning">
				<p>
					ATTENTION: les liasses d'un notaire sont souvent lacunaires
					et la mention d'un CM n'implique pas nécessairement l'existence du CM dans la liasse.
				</p>
				<p>
					Par ailleurs, pensez que l'ordre des époux est parfois invers&eacute ou peut correspondre à; un mariage double.
				</p>
				<p>
					Par exemple, la mention du CM BARRAUD-COUGNET dans le répertoire peut concerner l'époux BARRAUD,
					l'épouse COUGNET ou inversement (voire les deux)
				</p>
			</div>
			<p>
				Si vous avez l'occasion de vous rendre aux AD pour photographier un CM, pensez qu'en photographiant
				tous les CM de la liasse, vous ferez des heureux et faciliterez aussi le dépouillement systématique de celle-ci
			</p>
		</div>
		<div class="form-row">
			<button type="button" id=ferme class="btn btn-warning col-xs-4 col-xs-offset-4">Fermer la fenêtre</button>
		</div>
</body>

</html>