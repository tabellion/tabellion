<?php

require_once('../Commun/Identification.php');
require_once('../Commun/commun.php');
require_once('../Commun/constantes.php');
require_once('../Commun/ConnexionBD.php');

print('<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0//EN"><html>');
print("<head>");
print('<meta name="viewport" content="width=device-width, initial-scale=1.0">');
print('<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" >');
print('<meta http-equiv="content-language" content="fr">');
print('<title>Base AGC: Vos recherches de liasses notariales</title>');
print("<link href='../Commun/Styles.css' type='text/css' rel='stylesheet'>");
print("<script src='VerifieChampsRechercheLiasse.js' type='text/javascript'></script>\n");
print("<script src='../Commun/jquery-min.js' type='text/javascript'></script>");
print("<script src='../Commun/menu.js' type='text/javascript'></script>");//
print('<link rel="shortcut icon" href="images/favicon.ico">');
print("</head>");
print("<body>");

$connexionBD = ConnexionBD::singleton($gst_serveur_bd,$gst_utilisateur_bd,$gst_mdp_utilisateur_bd,$gst_nom_bd);
$gst_type_recherche         = isset($_GET['recherche']) ? $_GET['recherche'] : '';

if ($gst_type_recherche=='nouvelle')
{
  $gi_idf_dept				= isset($_GET['idf_dept']) ? (int) $_GET['idf_dept']: '0';
  $gi_idf_commune			= isset($_GET['idf_ca']) ? (int) $_GET['idf_ca']: '0';
  $gi_rayon					= '';
  $gi_annee_min				= isset($_GET['a_min']) ? (int) $_GET['a_min']:'';
  $gi_annee_max				= isset($_GET['a_max']) ? (int) $_GET['a_max']:'';

  $gst_paroisses_rattachees	= 'oui';
  $gst_nom_notaire			= '';
  $gst_prenom_notaire		= '';
  $gst_idf_serie_liasse     = '2E';
  $gst_cote_debut			= '';
  $gst_cote_fin				= '';
  $gst_repertoire			= 'non';
  $gst_sans_notaire			= 'non';
  $gst_sans_periode			= 'non';
  $gst_liasse_releve		= 'non';
  }
else
{
  $gi_idf_dept				= isset($_SESSION['idf_dept_recherche_rls']) ? $_SESSION['idf_dept_recherche_rls']: '0'; 
  $gi_idf_commune			= isset($_SESSION['idf_commune_recherche_rls']) ? $_SESSION['idf_commune_recherche_rls']: '0'; 
  $gi_rayon					= isset($_SESSION['rayon_rls']) ? $_SESSION['rayon_rls']: '';
  $gi_annee_min				= isset($_SESSION['annee_min_rls']) ? $_SESSION['annee_min_rls']: '';
  $gi_annee_max				= isset($_SESSION['annee_max_rls']) ? $_SESSION['annee_max_rls']: '';

  $gst_paroisses_rattachees	= isset($_SESSION['paroisses_rattachees_rls']) ? $_SESSION['paroisses_rattachees_rls']: 'oui';
  $gst_nom_notaire			= isset($_SESSION['nom_notaire_rls']) ? $_SESSION['nom_notaire_rls']: '';
  $gst_prenom_notaire		= isset($_SESSION['prenom_notaire_rls']) ? $_SESSION['prenom_notaire_rls']: '';
  $gst_idf_serie_liasse		= isset($_SESSION['idf_serie_liasse_rls']) ? $_SESSION['idf_serie_liasse_rls']: ''; 
  $gst_cote_debut			= isset($_SESSION['cote_debut_rls']) ? $_SESSION['cote_debut_rls']: '';
  $gst_cote_fin				= isset($_SESSION['cote_fin_rls']) ? $_SESSION['cote_fin_rls']: '';
  $gst_repertoire			= isset($_SESSION['repertoire_rls']) ? $_SESSION['repertoire_rls']: 'non';
  $gst_sans_notaire			= isset($_SESSION['sans_notaire_rls']) ? $_SESSION['sans_notaire_rls']: 'non';
  $gst_sans_periode			= isset($_SESSION['sans_periode_rls']) ? $_SESSION['sans_periode_rls']: 'non';
  $gst_liasse_releve		= isset($_SESSION['liasse_releve_rls']) ? $_SESSION['liasse_releve_rls']: 'non';
  }

unset($_SESSION['idf_dept_recherche_rls']);
unset($_SESSION['idf_commune_recherche_rls']);
unset($_SESSION['rayon_rls']);
unset($_SESSION['paroisses_rattachees_rls']);
unset($_SESSION['annee_min_rls']);
unset($_SESSION['annee_max_rls']);
unset($_SESSION['nom_notaire_rls']);
unset($_SESSION['prenom_notaire_rls']);
unset($_SESSION['idf_serie_liasse_rls']);
unset($_SESSION['cote_debut_rls']);
unset($_SESSION['cote_fin_rls']);
unset($_SESSION['repertoire_rls']);
unset($_SESSION['sans_notaire_rls']);
unset($_SESSION['sans_periode_rls']);
unset($_SESSION['liasse_releve_rls']);

$a_dept = $connexionBD->liste_valeur_par_clefUtf8("SELECT idf,nom FROM departement order by idf");
$a_dept[0] = 'Tous';

$a_communes_acte = $connexionBD->liste_valeur_par_clefUtf8("SELECT idf,nom FROM commune_acte order by nom");
$a_communes_acte[0] = 'Toutes';
$a_communes_acte[-9] = 'Commune inconnue';

$a_serie_liasse = $connexionBD->liste_valeur_par_clefUtf8("SELECT serie_liasse, nom FROM serie_liasse order by ordre");
						 
print('<form id="recherche" method="post">');

print('<div style="text-align:center">');
print('   <br>S&eacute;rie liasses bip : ');
print('   <select name="idf_serie_liasse" id="idf_serie_liasse">');
print(    chaine_select_options($gst_idf_serie_liasse,$a_serie_liasse));
print('   </select>');
print('   <br>');
print('</div>');

print('<div style="text-align:center">');
print('   <br>D&eacute;partement : ');
print('   <select name="idf_dept" >');
print(    chaine_select_options($gi_idf_dept,$a_dept));
print('   </select>');
print('   Commune/Paroisse : ');
print('   <select name="idf_commune_recherche" >');
print(    chaine_select_options($gi_idf_commune,$a_communes_acte));
print('   </select>');
print('   Rayon de recherche : ');
print("   <input type=text name=rayon size=2 MAXLENGTH=2 value=\"$gi_rayon\"> Km");
print('   Paroisses rattach&eacute;es: ');
if ($gst_paroisses_rattachees=='')
   print('   <input type=checkbox name=paroisses_rattachees value=oui >');
else
   print('   <input type=checkbox name=paroisses_rattachees value=oui checked>');
print('   <br>');
print('</div>');

print("<div style=\"text-align:center\">");
print("   <br>Ann&eacute;es de <input type=text name=annee_min size =4 value=\"$gi_annee_min\">");
print("   &agrave; <input type=text name=annee_max size =4 value=\"$gi_annee_max\">");
print('   &nbsp;&nbsp;&nbsp;/&nbsp;&nbsp;&nbsp;Liasses sans date: ');
if ($gst_sans_periode=='non')
   print('   <input type=checkbox name=sans_periode value=oui unchecked >');
else
   print('   <input type=checkbox name=sans_periode value=oui checked>');
print('   <br>');
print('</div>');

print('<div style="text-align:center">');
print('   <br>Nom Notaire: ');
print("   <input type=text name=nom_notaire size=15 MAXLENGTH=30 value=\"$gst_nom_notaire\" onKeyPress=\"SoumissionSimple(0,event)\">");
print('   Pr&eacute;nom Notaire: ');
print("   <input type=text name=prenom_notaire size=15 MAXLENGTH=30 value=\"$gst_prenom_notaire\" onKeyPress=\"SoumissionSimple(0,event)\"> ");
print('   &nbsp;&nbsp;&nbsp;/&nbsp;&nbsp;&nbsp;Liasses sans notaire: ');
if ($gst_sans_notaire=='non')
   print('   <input type=checkbox name=sans_notaire value=oui unchecked >');
else
   print('   <input type=checkbox name=sans_notaire value=oui checked>');
print('   <br>');
print('</div>');
print('<br>');
print('<div style="text-align:center">');
print('   Premi&egrave;re cote: ');
print("   <input type=text name=cote_debut size=5 MAXLENGTH=5 value=\"$gst_cote_debut\" onKeyPress=\"SoumissionSimple(0,event)\">");
print('   Derni&egrave;re cote: ');
print("   <input type=text name=cote_fin size=5 MAXLENGTH=5 value=\"$gst_cote_fin\" onKeyPress=\"SoumissionSimple(0,event)\"> ");
print('   <br><br>');
print('</div>');

print('<div style="text-align:center">');
print('   R&eacute;pertoires: ');
if ($gst_repertoire=='non')
	print('   <input type=checkbox name=repertoire value=oui unchecked >');
else
	print('   <input type=checkbox name=repertoire value=oui checked>');
print('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Liasses relev&eacute;es : ');
if ($gst_liasse_releve=='non')
	print('   <input type=checkbox name=liasse_releve value=oui unchecked >');
else
	print('   <input type=checkbox name=liasse_releve value=oui checked>');
print('   <br>');
print('</div>');

print('<div style="text-align:center"><br>');
print('   <input type=button name=Rechercher value="Rechercher" onClick="VerifieChampsRecherche(0,\'RechercheSimple\')">');
print('   <input type=button value="Effacer tous les Champs"  onClick="RazChamps(0)">');
print('</div> ');
print("</form>");
print("</body>");
print("</html>");
$connexionBD->ferme(); 
?>