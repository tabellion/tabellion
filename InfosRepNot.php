<?php
require_once('Commun/config.php');
require_once('Commun/constantes.php');
print('<!DOCTYPE html>');
print("<head>\n");
print('<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">');
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

require_once('Commun/constantes.php');

print("<div align=center>");
print("Ce CM est issu d'un r&eacute;pertoire de notaire et n'a pas &eacute;t&eacute; encore relev&eacute;<br>");
print("Merci de nous contacter &agrave l'adresse ");
print('<a href=mailto:'.EMAIL_DIRASSO.'?subject=Rep_Notaire_non_relev&eacute;>'.EMAIL_DIRASSO.'</a>');
print(" afin de connaitre la cote de la liasse correspondante d&eacute;pos&eacute;e aux Archives D&eacute;partementales de la Charente<br><br>");
print("<div class=\"alert alert-warning\">ATTENTION: les liasses d'un notaire sont souvent lacunaires et la mention d'un CM n'implique pas n&eacute;cessairement l'existence du CM dans la liasse.<br>");
print("Par ailleurs, pensez que l'ordre des &eacute;poux est parfois invers&eacute ou peut correspondre &agrave; un mariage double<br>");
print("Par exemple, la mention du CM BARRAUD-COUGNET dans le r&eacute;pertoire peut concerner l'&eacute;poux BARRAUD, l'&eacute;pouse COUGNET ou inversement (voire les deux)");
print("</div><br><br>");
print("Si vous avez l'occasion de vous rendre aux AD pour photographier un CM, pensez qu'en photographiant tous les CM de la liasse, vous ferez des heureux et faciliterez aussi le d&eacute;pouillement syst&eacute;matique de celle-ci");
print("</div>");
print('<div class="form-row">');
print('<button type="button" id=ferme class="btn btn-warning col-xs-4 col-xs-offset-4">Fermer la fen&ecirc;tre</button>');
print('</div>');
print("</body>");
print("</div></html>");
?>