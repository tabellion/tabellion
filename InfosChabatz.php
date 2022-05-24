<?php
require_once('Commun/config.php');
require_once('Commun/constantes.php');
print('<!DOCTYPE html>');
print("<head>\n");
print('<meta http-equiv="Content-Type" content="text/html; charset=windows-1252">');
print('<meta http-equiv="content-language" content="fr">');
print('<meta name="viewport" content="width=device-width, initial-scale=1.0">');
print("<link href='css/styles.css' type='text/css' rel='stylesheet'>");
print("<link href='css/bootstrap.min.css' rel='stylesheet'>");
print("<script src='js/jquery-min.js' type='text/javascript'></script>\n");
print("<script src='js/bootstrap.min.js' type='text/javascript'></script>");
?>
<script type='text/javascript'>
$(document).ready(function() {
	$("#ferme").click(function(){
		window.close();
	});	
});
</script>
<?php	
print('</head>');

print("<body>");
print('<div class="container">');
print("<div class=\"text-center\"><img src=\"$gst_logo_association\" alt='Logo ".SIGLE_ASSO."'></div>");
print("<div>Ce relev&eacute; de mariage a été depos&eacute; par une autre association:<br></div");
print("<div><br>L'association Chabatz d'entrar avec laquelle l'AGC a un accord de partenariat par le biais des AGL (Amiti&eacute;s G&eacute;n&eacute;alogiques du Limousin).<br></div>");
print("<div><br>Le relev&eacute; est un relev&eacute; de Table D&eacute;cennale, il ne comporte donc pas de filiation.<br></div>");
print("<div><br>Pour en savoir plus, vous êtes invit&eacute;s à rentrer en contact directement avec:<br></div>");
print("<div>Chabatz d'entrar : <a href=\"http://cdentrar.free.fr\" target=\"_blank\">http://cdentrar.free.fr</a><br></div>");
print("<div><br>Gene@micalement</div>");
print("<div><br>Les gestionnaires de la base AGC</div>");
print('<div class="form-row">');
print('<button type="button" id=ferme class="btn btn-warning col-xs-4 col-xs-offset-4">Fermer la fen&ecirc;tre</button>');
print('</div>');
print("</body>");
print("</div></html>");
?>

