<?php

// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------

// connexion BD doit avoir été chargé auparavant
$a_categories_menu =$connexionBD->sql_select_multiple("select libelle,script,droit from categorie_menu order by rang");
$a_elements_menu =$connexionBD->liste_valeur_par_doubles_clefs("select categorie, libelle, script,droit from element_menu order by categorie,rang");
if (isset($_SESSION['ident']))
  $a_privileges_utilisateur = $connexionBD->sql_select("select droit from privilege join adherent on (adherent.idf=privilege.idf_adherent) where ident=\"".$_SESSION['ident']."\""); 
else
  $a_privileges_utilisateur = array();

$gst_url_site;

print("<nav class=\"navbar navbar-default navbar-static-top\">\n");
print("<ul class=\"nav navbar-nav\">\n");
foreach ($a_categories_menu as $a_categorie)
{
   list($st_categorie,$st_script,$st_droit) = $a_categorie;
   if ($st_droit=='' || in_array($st_droit,$a_privileges_utilisateur))
   {        
      if (isset($a_elements_menu[strval($st_categorie)]))
      {
         print("<li class=\"dropdown\"><a data-toggle=\"dropdown\" href=\"$st_script\">$st_categorie<b class=\"caret\"></b></a>\n");
         $a_elements_categorie = $a_elements_menu[strval($st_categorie)]; 
         print("<ul class=\"dropdown-menu\">\n");
         foreach ($a_elements_categorie as $st_libelle => $a_elements)
         {
           list($st_script,$st_droit)= $a_elements;
           if ($st_droit=='' || in_array($st_droit,$a_privileges_utilisateur))
           {
              print("<li>");
              if ($st_script=='')
                 print("$st_libelle");
              else if (preg_match('/^http\:\/\//',$st_script))
                 print("<a href=\"$st_script\" target=\"_blank\">".cp1252_vers_utf8($st_libelle)."</a>");   
              else
                 print("<a href=\"$gst_url_site/$st_script\">".cp1252_vers_utf8($st_libelle)."</a>");
              print("</li>\n");   
           }
         }
         print("</ul></li>\n");
      }
      else
      {
        print("<li>");
        if ($st_script=='')        
          print("<a href=\"#\">$st_categorie</a>");
        else if (preg_match('/^http\:\/\//',$st_script))
          print("<a href=\"$st_script\" target=\"_blank\">".cp1252_vers_utf8($st_categorie)."</a>"); 
        else 
          print("<a href=\"$gst_url_site/$st_script\">".cp1252_vers_utf8($st_categorie)."</a>");
        print("</li>\n");   
      }  
   }   
}
print ('</ul></nav>');