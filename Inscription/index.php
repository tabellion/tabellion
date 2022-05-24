<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------
require_once("../Commun/config.php");
require_once("../Commun/constantes.php");
require_once("../Commun/ConnexionBD.php");
require_once("../Commun/commun.php");
require_once("../Commun/Courriel.php");

$gst_chemin = ($_SERVER['HTTP_HOST']=='inscription.genea16.net')? "https://adherents.genea16.net": '..';
$cryptinstall="$gst_chemin/Commun/crypt/cryptographp.fct.php";

$gst_url_site = ($_SERVER['HTTP_HOST']=='inscription.genea16.net')? "https://adherents.genea16.net": '..';

include $cryptinstall;
//print(dirname($cryptinstall));

print('<!DOCTYPE html>');
print("<head>");
print('<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">');
print('<meta http-equiv="content-language" content="fr">');
print('<meta name="viewport" content="width=device-width, initial-scale=1.0">');
print("<link href='$gst_url_site/css/styles.css' type='text/css' rel='stylesheet'>");
print("<link href='$gst_url_site/css/bootstrap.min.css' rel='stylesheet'>");
print("<script src='$gst_url_site/js/jquery-min.js' type='text/javascript'></script>");
print("<link href='$gst_url_site/css/jquery-ui.css' type='text/css' rel='stylesheet'>");
print("<link href='$gst_url_site/css/jquery-ui.structure.min.css' type='text/css' rel='stylesheet'>");
print("<link href='$gst_url_site/css/jquery-ui.theme.min.css' type='text/css' rel='stylesheet'> ");
print("<script src='$gst_url_site/js/jquery.validate.min.js' type='text/javascript'></script>");
print("<script src='$gst_url_site/js/additional-methods.min.js' type='text/javascript'></script>");
print("<script src='$gst_url_site/js/jquery-ui.min.js' type='text/javascript'></script>");
print("<script src='$gst_url_site/js/bootstrap.min.js' type='text/javascript'></script>");
?>
<script type='text/javascript'>
$(document).ready(function() {
$("#inscription").validate({
	rules: {
     nom: "required",
     prenom: "required",
     adr1: "required",
     cp: "required",
     commune: "required",
     email_perso: {
      required:true,
      email: true
     },
     site_web: "url",
     idf_agc: "integer",
     code: "required"
  },  
  messages: {
		nom: {
			required: "Le patronyme est obligatoire"
		},
		prenom: {
			required: "Le prénom est obligatoire"
		},
    adr1: {
			required: "L'adresse est vide. Remplir la première ligne"
		},
    cp: {
			required: "Le code postal est obligatoire"
		},
    commune: {
			required: "La commune est obligatoire"
		},
    email_perso: {
			required: "L'email est obligatoire",
      email: "Ce n'est pas un email"
		},
    site_web: {
			url: "Ceci n'est pas l'adresse d'un site"
		},
    idf_agc: {
      integer: "L'identifiant doit être un entier"
    },
    code: {
			required: "Le code est obligatoire"
		}
	},
    errorElement: "em",
	errorPlacement: function ( error, element ) {
		// Add the `help-block` class to the error element
		error.addClass( "help-block" );

		// Add `has-feedback` class to the parent div.form-group
		// in order to add icons to inputs
		element.parents( ".col-md-8" ).addClass( "has-feedback" );

		if ( element.prop( "type" ) === "checkbox" ) {
				error.insertAfter( element.parent( "label" ) );
		} else {
				error.insertAfter( element );
		}

		// Add the span element, if doesn't exists, and apply the icon classes to it.
		if ( !element.next( "span" )[ 0 ] ) {
			 $( "<span class='glyphicon glyphicon-remove form-control-feedback'></span>" ).insertAfter( element );
		}
	},
	success: function ( label, element ) {
		// Add the span element, if doesn't exists, and apply the icon classes to it.
		if ( !$( element ).next( "span" )[ 0 ] ) {
			 $( "<span class='glyphicon glyphicon-ok form-control-feedback'></span>" ).insertAfter( $( element ) );
		}
	},
	highlight: function ( element, errorClass, validClass ) {
			$( element ).parents( ".col-md-8" ).addClass( "has-error" ).removeClass( "has-success" );
			$( element ).next( "span" ).addClass( "glyphicon-remove" ).removeClass( "glyphicon-ok" );
	},
	unhighlight: function ( element, errorClass, validClass ) {
			$( element ).parents( ".col-md-8" ).addClass( "has-success" ).removeClass( "has-error" );
			$( element ).next( "span" ).addClass( "glyphicon-ok" ).removeClass( "glyphicon-remove" );
	}
    ,	
	submitHandler: function(form) {
			var nom =$("#nom").val().toUpperCase();
      $("#nom").val(nom);
			var prenom=$("#prenom").val();
      prenom= prenom.replace(/^\s+/g,'').replace(/\s+$/g,'');
      prenom=prenom.replace(/\s+/g,'-');
      prenom=prenom.toLowerCase(); 
			$("#prenom").val(prenom);
      form.submit();     
	}      
});
});   
</script>
<?php
print('<title>Inscription en ligne</title>');
print('</head>');
print('<body>');
print('<div class="container">');

$gst_mode = empty($_POST['mode']) ? 'GRILLE': $_POST['mode'] ;
$st_erreur = isset( $_GET['erreur']) ? $_GET['erreur'] : ''; 

if ($st_erreur!="")
{
  // traitement d'une erreur en provenance de valideInscription
  print("<div class=\"alert alert-danger\">$st_erreur</div>");
}

/*
* Envoie un mail à l'adhérent
* @param string $pst_ins_nom nom de l'adhérent
* @param string $pst_ins_prenom prénom de l'adhérent
* @param string $pst_ins_email_perso email de l'adhérent
* @param string $pst_ins_idf_agc identifiant AGC provisoire
* @param string $pst_ins_alea alea
* @param string $int_idf_prov Identifiant provisoire
* @global string $gst_url_validation URL de validation
* @global string $gst_rep_site Répertoire du site 
* @return retour de l'envoi de mail
*/
function envoie_mail ($dt_ins_date, $pst_ins_nom, $pst_ins_prenom, $pst_ins_email_perso, $pst_ins_idf_agc, $pst_ins_alea, $int_idf_prov)
{
  global $gst_url_validation,$gst_rep_site,$gst_serveur_smtp,$gst_utilisateur_smtp,$gst_mdp_smtp,$gi_port_smtp;
  $jour = sprintf ("%02s/%02s/%4s", substr($dt_ins_date,8,2), substr($dt_ins_date,5,2), substr($dt_ins_date,0,4));  
  $st_prefixe_asso = commence_par_une_voyelle(SIGLE_ASSO) ? "a l'": "au " ;
  $st_sujet    = "Pre-inscription $st_prefixe_asso".SIGLE_ASSO;
  $st_message_html  = "Bonjour ". cp1252_vers_utf8($pst_ins_prenom). " " . cp1252_vers_utf8($pst_ins_nom) . ", \n\n";
  $st_message_html .= "Vous vous &ecirc;tes pr&eacute;-inscrit ce jour, le " .$jour. " $st_prefixe_asso".SIGLE_ASSO."\n";
  $st_message_html .= "Nous vous remercions et vous demandons de nous confirmer votre inscription en cliquant sur ce lien suivant: \n\n";
  $st_message_html .= "<a href=\"$gst_url_validation?alea=$pst_ins_alea&amp;idf_agc=$pst_ins_idf_agc&amp;idf_prov=$int_idf_prov\">$gst_url_validation?alea=$pst_ins_alea&amp;idf_agc=$pst_ins_idf_agc&amp;idf_prov=$int_idf_prov</a>";
  $a_time = localtime();
  if ($a_time[4]>5)
  {
      $st_message_html .= "\n\n <font color=\"red\">Toute nouvelle adh&eacute;sion $st_prefixe_asso".SIGLE_ASSO." enregistr&eacute;e (par courrier ou en ligne) apr&egrave;s le 1er octobre comptera pour l'ann&eacute;e suivante. Le temps entre la date d'adh&eacute;sion et la fin de l'ann&eacute;e en cours est donc gracieusement offert par l'Association.</div>\n\n";
  }
  $st_prefixe_asso = commence_par_une_voyelle(SIGLE_ASSO) ? "de l'": "du " ;
  $st_message_html .= "\n\n Les responsables $st_prefixe_asso".SIGLE_ASSO;
  $st_message_html = nl2br($st_message_html);
  $courriel = new Courriel($gst_rep_site,$gst_serveur_smtp,$gst_utilisateur_smtp,$gst_mdp_smtp,$gi_port_smtp);
  $courriel->setExpediteur(EMAIL_DIRASSO,LIB_ASSO);
  $courriel->setAdresseRetour(EMAIL_DIRASSO);
  $courriel->setEnCopieCachee(EMAIL_DIRASSO);
  $courriel->setDestinataire($pst_ins_email_perso,"$pst_ins_prenom $pst_ins_nom");
  $courriel->setSujet($st_sujet);
  $courriel->setTexte($st_message_html);
  if (!$courriel->envoie())
  {
	 print("<div class=\"alert alert-danger\">Le message n'a pu être envoyé. Erreur: ".$courriel->get_erreur()."</div>");
	 return false;
  }
  return true;
}

/**
 * Affiche de la table d'édition
 
 * @param string $pst_ins_date     uniquement en validation sur l'enregistrement
 * @param string $pst_ins_nom
 * @param string $pst_ins_prenom
 * @param string $pst_ins_adr1
 * @param string $pst_ins_adr2
 * @param string $pst_ins_cp
 * @param string $pst_ins_commune
 * @param string $pst_ins_pays
 * @param string $pst_ins_email_perso
 * @param string $pst_ins_site_web
 * @param string $pst_ins_telephone
 * @param string $pst_ins_cache
 * @param string $pst_ins_idf_agc

 */ 
function menu_edition($pst_ins_nom, $pst_ins_prenom, $pst_ins_adr1, $pst_ins_adr2, $pst_ins_cp, $pst_ins_commune, $pst_ins_pays,
                      $pst_ins_email_perso, $pst_ins_site_web, $pst_ins_telephone, $pst_ins_cache, $pst_ins_idf_agc)
{
   global $ga_pays,$ga_tarifs;
   print('<div class="form-group row">');
   print("<label for=\"nom\" class=\"col-md-4 col-form-label control-label\">Nom</label>");
   print('<div class="col-md-8">');
   print("<input type=text maxlength=20 size=20 name=nom id=nom value=\"$pst_ins_nom\" class=\"form-control\">");
   print('</div></div>');
   print('<div class="form-group row">');
   print("<label for=\"prenom\" class=\"col-md-4 col-form-label control-label\">Pr&eacute;nom</label>");
   print('<div class="col-md-8">');
   print("<input type=text maxlength=30 size=20 name=prenom id=prenom value=\"$pst_ins_prenom\" class=\"form-control\">");
   print('</div></div>');
   print('<div class="form-group row">');
   print("<label for=\"idf_agc\" class=\"col-md-4 col-form-label control-label\">Num&eacute;ro ".SIGLE_ASSO." (si d&eacute;j&agrave; adh&eacute;rent)</label>");
   print('<div class="col-md-8">');
   print("<input type=text maxlength=10 size=10 name=idf_agc value=\"$pst_ins_idf_agc\" class=\"form-control\">");
   print('</div></div>');
   print('<div class="form-group row">');
   print("<label for=\"adr1\" class=\"col-md-4 col-form-label control-label\">Adresse 1</label>");
   print('<div class="col-md-8">');
   print("<input type=text maxlength=40 size=40 name=adr1 id=adr1 value=\"$pst_ins_adr1\" class=\"form-control\">");
   print('</div></div>');
   print('<div class="form-group row">');
   print("<label for=\"adr2\" class=\"col-md-4 col-form-label\">Adresse 2</label>");
   print('<div class="col-md-8">');
   print("<input type=text maxlength=40 size=40 name=adr2 id=adr2 value=\"$pst_ins_adr2\" class=\"form-control\">");
   print('</div></div>');
   print('<div class="form-group row">');
   print("<label for=\"cp\" class=\"col-md-4 col-form-label control-label\">Code Postal</label>");
   print('<div class="col-md-8">');
   print("<input type=text maxlength=12 size=12 name=cp id=cp value=\"$pst_ins_cp\" class=\"form-control\">");
   print('</div></div>');
   print('<div class="form-group row">');
   print("<label for=\"commune\" class=\"col-md-4 col-form-label control-label\">Localit&eacute;</label>");
   print('<div class="col-md-8">');
   print("<input type=text maxlength=40 size=20 name=commune id=commune value=\"$pst_ins_commune\" class=\"form-control\">");
   print('</div></div>');
   print('<div class="form-group row">');
   print("<label for=\"pays\" class=\"col-md-4 col-form-label control-label\">Pays</label>");
   print('<div class="col-md-8">');
   print("<select name=pays id=pays class=\"form-control\">");
   for($i=0; $i<sizeof($ga_pays); $i++) 
   {
	   if ($ga_pays[$i] == "France")
         print("<option selected=\"selected\" value=\"$ga_pays[$i]\"> $ga_pays[$i]</option>");
      else
         print("<option  value=\"$ga_pays[$i]\">$ga_pays[$i]</option>");  	
   }
   print("</select></div>");
   print('</div>');
   print('<div class="form-group row">');
   print("<input type=checkbox name=confidentiel id=confidentiel value=\"$pst_ins_cache\" checked class=\"form-check-input col-md-2\">");
   print('<div class="col-md-10">');
   print("<label for=\"confidentiel\" class=\"form-check-label\">Cochez et l'adresse devient invisible aux adh&eacute;rents</label>");
   print('</div></div>');
   print('<div class="form-group row">');
   print("<label for=\"site_web\" class=\"col-md-4 col-form-label control-label\">Site web</label>");
   print('<div class="col-md-8">');
   print("<input type=text maxlength=60 size=40 name=site_web id=site_web value=\"$pst_ins_site_web\" class=\"form-control\">");
   print('</div></div>');
   
   print('<div class="form-group row">');
   print("<label for=\"email_perso\" class=\"col-md-4 col-form-label control-label\">Email personnel</label>");
   print('<div class="col-md-8">');
   print("<input type=text maxlength=60 size=40 name=email_perso id=email_perso value=\"$pst_ins_email_perso\" class=\"form-control\">");
   print('</div></div>');
   
   print('<div class="form-group row">');
   print("<label for=\"telephone\" class=\"col-md-4 col-form-label control-label\">T&eacute;l&eacute;phone</label>");
    print('<div class="col-md-8">');
   print("<input type=text maxlength=20 size=14 name=telephone id=telephone value=\"$pst_ins_telephone\" aria-describedby=\"UsageTelephone\" class=\"form-control\">");
   $st_prefixe_asso = commence_par_une_voyelle(SIGLE_ASSO) ? "de l'": "du " ; 
   print("<small id=\"UsageTelephone\">Donn&eacute;es accessibles uniquement aux gestionnaires $st_prefixe_asso".SIGLE_ASSO."</small>");
   print('</div></div>');
   print('<div class="form-group row">');
    
   print('<label for="code" class="col-md-4 col-form-label control-label">Veuillez recopier tous les chiffres du code qui suit:</label>');
   print('<div class="col-md-8">');
   print('<input type="text" size="4" name="code" id="code" class="form-control">');
   dsp_crypt(0,1);
   print('</div></div>');
   print("<label for=\"type_adhesion\">Votre cotisation</label>");
   print("<ul class=\"list-group\" id=\"type_adhesion\">");
	
   print("<li class=\"list-group-item\">Cotisation d'adh&eacute;sion minimale: ".$ga_tarifs['internet']." euros</li>");
   print("<li class=\"list-group-item\">Avec le bulletin: <br>");
   print($ga_tarifs['bulletin_metro']." euros pour la France,<br>");
   print($ga_tarifs['bulletin_etranger']." euros pour les autres pays (DOM/TOM inclus)</li>");
   print("</ul>");
   print("<blockquote>");
   print("<div>Un email de confirmation va vous &ecirc;tre envoy&eacute; &agrave; votre adresse d'inscription. Si vous ne l'avez pas re&ccedil;u, v&eacute;rifier que celui-ci n'est pas dans vos ind&eacute;sirables</div>");
   print("<div>La r&eacute;ception de cet email est indispensable pour poursuivre le processus d'adh&eacute;sion ou r&eacute;-adh&eacute;sion. Vous pourrez ensuite choisir lors du paiement le type d'inscription voulu (Internet ou Bulletin)</div>");
   print("<blockquote>");
}


/** Affiche le menu d'ajout d'un adhérent
 */ 
function saisie_adherent($pst_ins_nom, $pst_ins_prenom, $pst_ins_adr1, $pst_ins_adr2, $pst_ins_cp, $pst_ins_commune, $pst_ins_pays,
                      $pst_ins_email_perso, $pst_ins_site_web, $pst_ins_telephone, $pst_ins_cache, $pst_ins_idf_agc)
{
   global $gst_url_readhesion;
   print('<div class="panel panel-primary">');
   print("<div class=\"panel-heading\">INSCRIPTION EN LIGNE</div>");
   print('<div class="panel-body">');
   print("<div class=\"alert alert-warning\">Si vous &ecirc;tes d&eacute;j&agrave; adh&eacute;rent cette ann&eacute;e, merci d'utiliser le formulaire de re-adh&eacute;sion depuis l'onglet \"Compte Personnel\" en lieu et place de celui-ci.<br /> Vous pouvez y acc&eacute;der directement en cliquant sur le lien ci-dessous:<br /> <a href=\"$gst_url_readhesion\">$gst_url_readhesion</a></div>");
   $a_time = localtime();
   if ($a_time[4]>5)
   {
      $st_prefixe_asso = commence_par_une_voyelle(SIGLE_ASSO) ? "l'": "le " ; 
	  print("<div class=\"alert alert-danger\">Toute nouvelle adh&eacute;sion &agrave; $st_prefixe_asso".SIGLE_ASSO." enregistr&eacute;e (par courrier ou en ligne) apr&egrave;s le 1er octobre comptera pour l'ann&eacute;e suivante. Le temps entre la date d'adh&eacute;sion et la fin de l'ann&eacute;e en cours est donc gracieusement offert par l'Association.<br><br></div>");
   }
   print("<form   method=\"post\" id=\"inscription\">");
   print("<input type=hidden name=mode value=\"INSCRIPTION\">");
   menu_edition($pst_ins_nom, $pst_ins_prenom, $pst_ins_adr1, $pst_ins_adr2, $pst_ins_cp, $pst_ins_commune, $pst_ins_pays,
                      $pst_ins_email_perso, $pst_ins_site_web, $pst_ins_telephone, $pst_ins_cache, $pst_ins_idf_agc);
   print('<div class="form-group col-md-4 col-md-offset-4"><button type="submit" class="btn btn-primary">Valider l\'inscription</button></div>');
   print('</form></div></div>');
}

/**
 * Met à jour l'inscription dans table temporaire
 * @param string $pst_ins_date Date d'inscription
 * @param string $pst_ins_nom  Nom de l'adhérent
 * @param string $pst_ins_prenom Prénom de l'adhérent
 * @param string $pst_ins_adr1   Ligne Adresse 1
 * @param string $pst_ins_adr2   Ligne Adresse 2
 * @param string $pst_ins_cp     Code Postal
 * @param string $pst_ins_commune  Commune
 * @param string $pst_ins_pays    Pays
 * @param string $pst_ins_email_perso Email adhérent
 * @param string $pst_ins_site_web Site Web
 * @param string $pst_ins_telephone Téléphone
 * @param string $pst_ins_cache   Confidentialité (O|N)
 * @param string $pst_ins_idf_agc Identifiant AGC
 */ 
function preinscrit_adherent($pst_ins_date,$pst_ins_nom,$pst_ins_prenom,$pst_ins_adr1,$pst_ins_adr2,$pst_ins_cp,$pst_ins_commune,$pst_ins_pays,$pst_ins_email_perso,$pst_ins_site_web,$pst_ins_telephone,$pst_ins_cache,$pst_ins_idf_agc,$pst_ins_alea)
{
   global $connexionBD;    	  
	 $pst_ins_valid = "0";
   
   if (strlen($pst_ins_telephone)==10)	   
   $pst_ins_telephone = wordwrap($pst_ins_telephone,2,' ',true);
   $pst_ins_nom=utf8_vers_cp1252($pst_ins_nom);
   $pst_ins_prenom=utf8_vers_cp1252($pst_ins_prenom);
   $pst_ins_adr1=utf8_vers_cp1252($pst_ins_adr1);
   $pst_ins_adr2=utf8_vers_cp1252($pst_ins_adr2);   
   $pst_ins_commune=utf8_vers_cp1252($pst_ins_commune);
   $pst_ins_pays=utf8_vers_cp1252($pst_ins_pays);
	$connexionBD->initialise_params(array(':ins_date'=>$pst_ins_date,':nom'=>$pst_ins_nom,':prenom'=>$pst_ins_prenom,':adr1'=>$pst_ins_adr1,':adr2'=>$pst_ins_adr2,':ins_pays'=>$pst_ins_pays,':ins_cp'=>$pst_ins_cp,':ins_commune'=>$pst_ins_commune,':ins_telephone'=>$pst_ins_telephone,':ins_email_perso'=>$pst_ins_email_perso,':ins_site_web'=>$pst_ins_site_web,':ins_cache'=>$pst_ins_cache,':ins_idf_agc'=>$pst_ins_idf_agc,':ins_alea'=>$pst_ins_alea,':ins_valid'=>$pst_ins_valid));
     $connexionBD->execute_requete("insert into inscription_prov(ins_date, ins_nom, ins_prenom, ins_adr1, ins_adr2, ins_cp, ins_commune, ins_pays, ins_email_perso, ins_site_web, ins_telephone, ins_cache, ins_idf_agc, ins_alea, ins_valid,ins_type) values(:ins_date, :nom, :prenom, :adr1, :adr2, :ins_cp, :ins_commune, :ins_pays,:ins_email_perso, :ins_site_web, :ins_telephone, :ins_cache, :ins_idf_agc, :ins_alea, :ins_valid,'".TYPE_INSCRIPTION."')");
} 
 
/*---------------------------------------------------------------------------     
  Démarrage du programme
  ---------------------------------------------------------------------------*/

$connexionBD = ConnexionBD::singleton($gst_serveur_bd,$gst_utilisateur_bd,$gst_mdp_utilisateur_bd,$gst_nom_bd);
if ($_SERVER['HTTP_HOST']=='inscription.genea16.net')
	print("<div class=\"alert  alert-danger\">Merci d'utiliser l'adresse <a href=\"$gst_url_site/Inscription/index.php\">$gst_url_site/Inscription/index.php</a> &agrave; la place de cette page</div>");

switch ($gst_mode) {
  case 'GRILLE' : 
     saisie_adherent('','','','','','','','','','','O','0'); 
  break;
  case 'INSCRIPTION' : 
     
     if (get_magic_quotes_gpc())
     {
        if (ini_get('magic_quotes_sybase'))
        {
           $pst_ins_nom = strtoupper(trim($_POST['nom']));
           $pst_ins_prenom = ucfirst(trim($_POST['prenom']));
           $pst_ins_adr1 = trim($_POST['adr1']);
           $pst_ins_adr2 = trim($_POST['adr2']);
           $pst_ins_cp = $_POST['cp'];
           $pst_ins_commune = strtoupper(trim($_POST['commune']));
           $pst_ins_pays = $_POST['pays'];
           $pst_ins_email_perso = trim($_POST['email_perso']);
           $pst_ins_site_web = trim($_POST['site_web']);
           $pst_ins_telephone = trim($_POST['telephone']);
           $pst_ins_cache = $_POST['confidentiel'];
	         $pst_ins_idf_agc = (int) $_POST['idf_agc'];
        }
        else
        {
           $pst_ins_nom = strtoupper(stripslashes(trim($_POST['nom'])));
           $pst_ins_prenom = ucfirst(stripslashes(trim($_POST['prenom'])));
           $pst_ins_adr1 = stripslashes(trim($_POST['adr1']));
           $pst_ins_adr2 = stripslashes(trim($_POST['adr2']));
           $pst_ins_cp = stripslashes($_POST['cp']);
           $pst_ins_commune = strtoupper(stripslashes(trim($_POST['commune'])));
           $pst_ins_pays = stripslashes($_POST['pays']);
           $pst_ins_email_perso = stripslashes(trim($_POST['email_perso']));
           $pst_ins_site_web = stripslashes(trim($_POST['site_web']));
           $pst_ins_telephone = stripslashes(trim($_POST['telephone']));
           $pst_ins_cache = stripslashes($_POST['confidentiel']);
	         $pst_ins_idf_agc = (int) $_POST['idf_agc'];
        }
     }
     else
     {
        $pst_ins_nom = strtoupper(trim($_POST['nom']));
        $pst_ins_prenom = ucfirst(trim($_POST['prenom']));
        $pst_ins_adr1 = trim($_POST['adr1']);
        $pst_ins_adr2 = trim($_POST['adr2']);
        $pst_ins_cp = $_POST['cp'];
        $pst_ins_commune = strtoupper(trim($_POST['commune']));
        $pst_ins_pays = $_POST['pays'];
        $pst_ins_email_perso = trim($_POST['email_perso']);
        $pst_ins_site_web = trim($_POST['site_web']);
        $pst_ins_telephone = trim($_POST['telephone']);
        $pst_ins_cache = $_POST['confidentiel'];
	      $pst_ins_idf_agc = (int) $_POST['idf_agc'];
     }
     if (empty($pst_ins_nom)) die("Nom vide");
     if (empty($pst_ins_prenom)) die("Pr&eacute;om vide");
     if (empty($pst_ins_cp)) die("Code postal vide");
     if (empty($pst_ins_commune)) die("Commune vide");
     if (empty($pst_ins_pays)) die("Pays vide");
     if (empty($pst_ins_email_perso)) die("Email vide");
     mt_srand ((float) microtime() * 1000000);
	   $pst_ins_alea = mt_rand(1000,10000); 
     $dt_ins_date = date("Y-m-d");  
     if (chk_crypt($_POST['code']))	
	 //if (true)	 
     {
        preinscrit_adherent($dt_ins_date,$pst_ins_nom,$pst_ins_prenom,$pst_ins_adr1,$pst_ins_adr2,$pst_ins_cp,$pst_ins_commune,$pst_ins_pays,$pst_ins_email_perso,$pst_ins_site_web,$pst_ins_telephone,$pst_ins_cache,$pst_ins_idf_agc,$pst_ins_alea);
        if (envoie_mail($dt_ins_date, $pst_ins_nom, $pst_ins_prenom, $pst_ins_email_perso, $pst_ins_idf_agc, $pst_ins_alea, $connexionBD->dernier_idf_insere()))
			print("<div class=\"alert alert-success\">Veuillez consulter votre messagerie et suivre les indications du mail qui vous a &eacute;t&eacute; adress&eacute;</div>");
		else
			print("<div class=\"alert alert-danger\">Probleme d'envoi sur votre mail</div>"); 			
     }
     else
     {
        print("<div class=\"alert alert-danger\">Mauvais code !!!</div>");
        saisie_adherent($pst_ins_nom, $pst_ins_prenom, $pst_ins_adr1, $pst_ins_adr2, $pst_ins_cp, $pst_ins_commune, $pst_ins_pays,$pst_ins_email_perso, $pst_ins_site_web, $pst_ins_telephone, $pst_ins_cache, $pst_ins_idf_agc);
     } 
  break;
}  
print('</div></body></html>');
?>
