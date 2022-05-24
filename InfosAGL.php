<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------
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
print("<div>Ce mariage filiatif a &eacute;t&eacute; depos&eacute; par une autre association :</div><br>");
print("<div>Les Amiti&eacute;s G&eacute;n&eacute;alogiques Limousines, avec laquelle l'AGC a un accord direct de partenariat.
</div><br>");
print("<div>Pour obtenir cette filiation, merci d'en faire la demande &agrave; l'adresse <a href=\"mailto:agc-dir@genea16.net\">agc-dir@genea16.net</a>, en pr&eacute;cisant</div>");
print("<div>1) les nom des &eacute;poux, date et lieux du mariage demand&eacute; (copier/coller &agrave; partir de la base)</div>");
print("<div>2) vos NOM, pr&eacute;nom et Num&eacute;ro d'adh&eacute;rent AGC</div>");
print("<div>Nous transmettrons cette demande et sa r&eacute;ponse d&egrave;s que possible.</div><br>");
print("<div>G&eacute;n&eacute;@micalement</div><br>");
print("<div>Les gestionnaires de la base AGC</div>");
print('<div class="form-row">');
print('<button type="button" id=ferme class="btn btn-warning col-xs-4 col-xs-offset-4">Fermer la fen&ecirc;tre</button>');
print('</div>');
print("</body>");
print("</div></html>");
?>

