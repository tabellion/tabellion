<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------
require_once '../Commun/config.php';
require_once '../Commun/constantes.php';
require_once('../Commun/Identification.php');
require_once('../Commun/VerificationDroits.php');

verifie_privilege(DROIT_UTILITAIRES);

require_once '../Commun/ConnexionBD.php';

print('<!DOCTYPE html>');
print("<head>");
print('<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" >');
print('<meta http-equiv="content-language" content="fr">');
print("<title>Utilisation des tables</title>");
print('<meta name="viewport" content="width=device-width, initial-scale=1.0">');
print("<link href='../css/styles.css' type='text/css' rel='stylesheet'>");
print("<link href='../css/bootstrap.min.css' rel='stylesheet'>");
print("<script src='../js/jquery-min.js' type='text/javascript'></script>");
print("<script src='../js/bootstrap.min.js' type='text/javascript'></script>");
print('</head>');
print('<body>');
print('<div class="container">');
$connexionBD = ConnexionBD::singleton($gst_serveur_bd,$gst_utilisateur_bd,$gst_mdp_utilisateur_bd,$gst_nom_bd);
require_once("../Commun/menu.php");

$st_requete = "show table status";
$a_stats_table_sql = $connexionBD->sql_select_multiple_par_idf($st_requete);

$ga_max_val = array (
                     "int(8) unsigned" => 4294967295,
                     "mediumint(8) unsigned"=> 16777215,
                     "mediumint(9)"=> 8388607,
                     "smallint(5) unsigned" => 65535,
                     "tinyint(4)" => 128,
                     "tinyint(3) unsigned" => 255
                    ); 
 
function taille_formatee($pi_taille)
{
   if ($pi_taille>=1048576)
     return sprintf("%2.2f Mo",$pi_taille/1048576);
   else if ($pi_taille>=1024)
     return sprintf("%2.2f Ko",$pi_taille/1024);
   else
     return sprintf("%d octets",$pi_taille);  
} 

print("<table class=\"table table-bordered table-striped\">");
print("<tr>");
print("<th>Table</th>");
print("<th>Nbre de lignes</th>");
print("<th>Taille donn&eacute;es</th>");
print("<th>Taille index</th>");
print("<th>Clefs</th>");
print("<th>Valeur courante<br> clef</th>");
print("<th>Valeur max</th>");
print("<th>% d'occupation clef</th>");
print("</tr>");
foreach ($a_stats_table_sql as $st_table => $a_lignes)
{
   $st_requete = "SHOW COLUMNS FROM `$st_table`";
   $a_colonnes_table = $connexionBD->sql_select_multiple_par_idf($st_requete);
   print("<tr>");
   print("<td>$st_table</td>");
   print("<td>".$a_lignes[3]."</td>"); // Nbr de lignes
   // taille donnees  
   print("<td>".taille_formatee($a_lignes[5])."</td>");
   // taille index
   print("<td>".taille_formatee($a_lignes[7])."</td>");
   $st_clefs = '';
   $i_max_clef = null;
   $i_nb_clefs= 0;
   foreach ($a_colonnes_table as $st_colonne => $a_attributs)
   {
      if($a_attributs[2]=='PRI')
      {
         $st_clefs .= "<span style=\"color: grey;\">$st_colonne </span>" . $a_attributs[0]."<br>";
         if (array_key_exists($a_attributs[0],$ga_max_val))
         {  
             $i_max_clef=$ga_max_val[$a_attributs[0]];
         }
         $i_nb_clefs++;   
      } 
   }
   print("<td>$st_clefs</td>");
   
   if (!is_null($i_max_clef) && $i_max_clef>0 && $i_nb_clefs==1)
   {
      $st_requete = "select `AUTO_INCREMENT` from  information_schema.tables where table_schema='$gst_nom_bd' and table_name= '$st_table'";
      $i_val_clef = $connexionBD->sql_select1($st_requete);
      print("<td>$i_val_clef</td><td>$i_max_clef</td>");
      $f_perc = $i_val_clef/$i_max_clef*100;
      if ($f_perc<80)
        print(sprintf("<td bgcolor=green>%2.2f %%</td>",$f_perc));
      else if ($f_perc<90)
        print(sprintf("<td bgcolor=orange>%2.2f %%</td>",$f_perc));
      else
        print(sprintf("<td bgcolor=red>%2.2f %%</td>",$f_perc));
   }  
   else
     print("<td>N/A</td><td>N/A</td><td>N/A</td>");
   print("</tr>");
}
print("</table>"); 
 
print('</div></body></html>');


?>
