<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------
require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../Commun/commun.php';
require_once __DIR__ . '/../Commun/VerificationDroits.php';
verifie_privilege(DROIT_GENEABANK);
require_once __DIR__ . '/../Commun/Benchmark.php';

/**
 * Affiche le menu de sélection
 */
function AfficheMenu()
{

    print("<div align=center>");
    print('<div class="panel panel-primary">');
    print('<div class="panel-heading">Listes des exports Généabank</div>');
    print('<div class="panel-body">');
    print("<form  method=\"post\">");
    print('<input type="hidden" name="mode" value="EXPORT_UNIONS">');
    print('<button type=submit class="btn btn-primary col-md-4 col-md-offset-4">Export des unions</button>');
    print("</form>");
    print("<form  method=\"post\">");
    print('<input type="hidden" name="mode" value="EXPORT_INDEX_PATROS"><br>');
    print('<button type=submit class="btn btn-primary col-md-4 col-md-offset-4">Export des index patronymiques</button>');
    print("</form>");
    print("<form  method=\"post\">");
    print('<input type="hidden" name="mode" value="EXPORT_INDEX_COMMUNES"><br>');
    print('<button type=submit class="btn btn-primary col-md-4 col-md-offset-4">Export des index des communes</button>');
    print("</form>");
    print("<form  method=\"post\">");
    print('<input type="hidden" name="mode" value="EXPORT_COMPTEURS">');
    print('<button type=submit class="btn btn-primary col-md-4 col-md-offset-4">Mise &agrave; jour des compteurs adhérents</button>');
    print("</form></div></div>");
}

/**
 * Exporte la liste des couples pour Généabank dans le fichier
 * spécifié par  $pst_nom_fichier et $pst_nom_fichier
 * @param object $pconnexionBD Connexion à la base de donnée
 * @param string $pst_repertoire_export Répertoire de l'export
 * @return string Nom du fichier temporaire créé   
 * Exemple d'export :
 * ;CHARON;René;MONDO ?;Suzanne;gbkagcharente;décès ancien cjt
 * ;DELOR;Pierre;DUMAS DELAGE;Françoise;gbkagcharente;décès  
 */
function exporteUnions()
{
    global $connexionBD;
    $st_fichier = "../storage/gbk/gbkcpl.txt";
    $st_requete = "select trim(u.patronyme_epoux),trim(prn_epx.libelle),trim(u.patronyme_epouse),trim(prn_epse.libelle), case u.idf_type_acte when 3 then concat(ta.nom,' parents') when 4 then (case epx.idf_type_presence when 1 then (case epse.idf_type_presence when 5 then ta.nom end) when 5 then (case epse.idf_type_presence when 1 then concat(ta.nom,' ancien cjt') end) when 6 then (case epse.idf_type_presence when 7 then concat(ta.nom,' parents') end) end) when 1 then (case epx.idf_type_presence when 1 then (case epse.idf_type_presence when 1 then ta.nom when 5 then concat(ta.nom,' ancien cjt epse') end) when 5 then (case epse.idf_type_presence when 1 then concat(ta.nom,' ancien cjt epx') end) when 6 then (case epse.idf_type_presence when 7 then concat(ta.nom,' parents') end) end) else (case epx.idf_type_presence when 1 then (case epse.idf_type_presence when 1 then ta.nom when 5 then concat(ta.nom,' ancien cjt epse') end) when 5 then (case epse.idf_type_presence when 1 then concat(ta.nom,' ancien cjt epx') end) when 6 then(case epse.idf_type_presence when 7 then concat(ta.nom,' parents') end) end) end from `union` u join personne epx on (u.idf_epoux=epx.idf) join prenom prn_epx on (epx.idf_prenom=prn_epx.idf) join personne epse on (u.idf_epouse=epse.idf)  join prenom prn_epse on (epse.idf_prenom=prn_epse.idf) join type_acte ta on (u.idf_type_acte=ta.idf) join source s on (u.idf_source=s.idf) where s.publication_geneabank=1 and u.patronyme_epoux REGEXP '^[A-Za-z ()]+$' and u.patronyme_epouse REGEXP '^[A-Za-z ()]+$'";
    $connexionBD->desactive_cache();
    $connexionBD->execute_requete($st_requete);
    $pf = fopen($st_fichier, "a") or die("<div class=IMPORTANT>Impossible d'écrire $st_fichier</div>");
    while (list($st_patro_epx, $st_prn_epx, $st_patro_epse, $st_prn_epse, $st_cmt) = $connexionBD->ligne_suivante_resultat()) {
        $st_ligne = join(';', array('', $st_patro_epx, $st_prn_epx, $st_patro_epse, $st_prn_epse, IDF_ASSO_GBK, $st_cmt));
        fwrite($pf, "$st_ligne\r\n");
    }
    fclose($pf);
    return ($st_fichier);
}

/**
 * Exporte l'index des noms pour Généabank dans le fichier
 * spécifié par  $pst_nom_fichier et $pst_nom_fichier
 * Le fichier est au format Index Généanet 
 * @param object $pconnexionBD Connexion à la base de donnée
 * @param string $pst_idf_geneabank Identifiant Généabank de l'association
 * @param string $pst_repertoire_export Répertoire de l'export
 * @global $gst_code_dpt_geneabank Code département généabank (Ex: F16)
 * @global $gst_code_region_geneabank Code région généabank (Ex: PCH)
 * @global $gst_code_pays_geneabank Code pays généabank (Ex: FRA)
 * @global $gst_code_type_geneabank Source généabank (Ex: C pour acte original)      
 * Exemple d'export :
 * BATARD;décès;1777;1777;1;CLAIX;F16;PCH;FRA;C
 * BATARDE;décès;1791;1791;1;BECHERESSE;F16;PCH;FRA;C
 */
function exporteIndexPatros()
{   global $connexionBD;
    $st_requete =  "select p.libelle,ta.nom,sp.annee_min,sp.annee_max,sp.nb_personnes,ca.nom from stats_patronyme sp join patronyme p on (sp.idf_patronyme=p.idf) join commune_acte ca on (sp.idf_commune=ca.idf) join type_acte ta on (sp.idf_type_acte=ta.idf) join source s on (sp.idf_source=s.idf) where s.publication_geneabank=1 and p.libelle REGEXP '^[A-Za-z ()]+$' ";
    $connexionBD->desactive_cache();
    $connexionBD->execute_requete($st_requete);
    $st_fichier = "../storage/gbk/gbkpatros.txt";
    $pf = fopen($st_fichier, "a") or die("<div class=\"alert alert-danger\">Impossible d'écrire $st_fichier</div>");
    while (list($st_patro, $st_type_acte, $i_annee_min, $i_annee_max, $i_nb_personnes, $st_commune) = $connexionBD->ligne_suivante_resultat()) {
        $st_ligne = join(';', array($st_patro, $st_type_acte, $i_annee_min, $i_annee_max, $i_nb_personnes, $st_commune, CODE_DPT_GENEABANK, CODE_REGION_GENEABANK, CODE_PAYS_GENEABANK, CODE_TYPE_GENEABANK));
        fwrite($pf, "$st_ligne\r\n");
    }
    fclose($pf);
    return ($st_fichier);
}

/**
 * Affiche le résultat du fichier
 * @param string $pst_url_export Url du répertoire de l'export 
 * @param string $pst_nom_fichier Nom du fichier 
 */
function AfficheResultatFichier($pst_url_export, $pst_nom_fichier)
{
    print("<form  method=\"post\">");
    print("<label for=\"export_fichier\" class=\"col-form-label col-md-2\">Fichier crée:</label>");
    print('<div class="col-md-10">');
    print("<a id=export_fichier href=\"$pst_url_export/$pst_nom_fichier\">$pst_nom_fichier</a>");
    print('</div>');
    print('<input type="hidden" name="mode" value="MENU"/>');
    print('<button type=submit class="btn btn-primary col-md-4 col-md-offset-4">Revenir au menu Généabank</button>');
    print("</form>");
}


/**
 * Exporte l'index des noms pour Généabank dans le fichier
 * spécifié par  $pst_nom_fichier et $pst_nom_fichier
 * Le fichier est au format Index Généanet 
 * @param object $pconnexionBD Connexion à la base de donnée
 * @param string $pst_repertoire_export Répertoire de l'export
 * @param string $pst_nom_fichier
 * @param string $pst_url_export Url du répertoire de l'export  
 * @param string $st_prefixe_adherent_gbk préfixe adhérent Généabank
 * @param integer $pi_nb_demandes_gbk Nombre de demandes authorisées pour un adhérent par mois 
 */
function MajCompteurAdherents($pconnexionBD, $pst_repertoire_export, $pst_nom_fichier, $pst_url_export, $st_prefixe_adherent_gbk, $pi_nb_demandes_gbk)
{
    $a_liste_idf = $pconnexionBD->sql_select("select idf , annee_cotisation, statut from adherent where statut = 'B' or statut = 'I'   and annee_cotisation >=  YEAR( NOW()) order by idf ");
    print("<form  method=\"post\">");
    $pf = fopen("$pst_repertoire_export/$pst_nom_fichier", "w") or die("<div class=\"alert alert-danger\">Impossible d'écrire $pst_repertoire_export/$pst_nom_fichier</div>");
    print("<label for=\"cmds_gbk\" class=\"col-form-label col-md-2\">Mise &agrave; jour des compteurs adhérents Généabank</label>");
    print('<div class="col-md-10">');
    print("<textarea rows=18 cols=15 id=\"cmds_gbk\" class=\"form-control\">");
    foreach ($a_liste_idf as $i_idf) {
        $st_ligne = "SET $st_prefixe_adherent_gbk$i_idf $pi_nb_demandes_gbk\r\n";
        fwrite($pf, $st_ligne);
        print($st_ligne);
    }
    fclose($pf);
    print("</textarea></div>");

    print('<label for="export_fichier" class="col-form-label col-md-2">Fichier crée:</label>');
    print('<div class="col-md-10">');
    print("<a id=export_fichier href=\"$pst_url_export/$pst_nom_fichier\">$pst_nom_fichier</a>");
    print('</div>');
    print('<input type="hidden" name="mode" value="MENU"><br>');
    print('<button type=submit class="btn btn-primary col-md-4 col-md-offset-4">Revenir au menu Généabank</button>');
    print("</form>");
}

/**
 * Exporte l'index des communes pour Généabank dans le fichier
 * spécifié par  $pst_nom_fichier et $pst_nom_fichier
 * @param object $pconnexionBD Connexion à la base de donnée
 * @param string $pst_repertoire_export Répertoire de l'export
 * @param string $pst_nom_fichier
 * @param string $pst_url_export Url du répertoire de l'export   
 */
function ExporteIndexCommunes($pconnexionBD, $pst_repertoire_export, $pst_nom_fichier, $pst_url_export)
{
    global $gst_url_interrogation_geneabank;
    //IDF_ASSO_GBK
    $a_stats_commune = $pconnexionBD->sql_select_multiple("select left(ca.code_insee,2),ca.nom,ta.nom,sc.annee_min,sc.annee_max,sc.nb_actes from stats_commune sc join commune_acte ca on (sc.idf_commune=ca.idf) join type_acte ta on (sc.idf_type_acte=ta.idf) join source s on (sc.idf_source=s.idf) where s.publication_geneabank=1 order by ca.nom");
    print("<form  method=\"post\">");
    $pf = fopen("$pst_repertoire_export/$pst_nom_fichier", "w") or die("<div class=\"alert alert-danger\">Impossible d'écrire $pst_repertoire_export/$pst_nom_fichier</div>");
    print("<label for=\"index_commune\" class=\"col-form-label col-md-2\">Index des communes pour Généabank</label>");
    print('<div class="col-md-10">');
    print('<textarea id="index_commune" rows=18 cols=120 class="form-control">');
    foreach ($a_stats_commune as $a_stats) {
        list($i_dpt, $st_commune, $st_type_acte, $i_annee_min, $i_annee_max, $i_nb_actes) = $a_stats;
        $st_ligne = join(';', array(IDF_ASSO_GBK, $gst_url_interrogation_geneabank, PAYS_GENEABANK, $i_dpt, $st_commune, $st_type_acte, $i_annee_min, $i_annee_max, $i_nb_actes));
        fwrite($pf, "$st_ligne\n");
        print("$st_ligne\n");
    }
    fclose($pf);
    print("</textarea></div>");
    print('<label for="export_fichier" class="col-form-label col-md-2">Fichier crée:</label>');
    print('<div class="col-md-10">');
    print("<a id=export_fichier href=\"$pst_url_export/$pst_nom_fichier\">$pst_nom_fichier</a>");
    print('</div>');
    print('<input type="hidden" name="mode" value="MENU"/><br>');
    print('<button type=submit class="btn btn-primary col-md-4 col-md-offset-4">Revenir au menu Généabank</button>');
    print("</form>");
}

/*------------------------------------------------------------------------------
                            Corps du programme
 -----------------------------------------------------------------------------*/
print('<!DOCTYPE html>');
print("<head>\n");
print('<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" >');
print('<meta http-equiv="content-language" content="fr">');
print("<title>Gestion des export Geneabank</title>");
print('<meta name="viewport" content="width=device-width, initial-scale=1.0">');
print("<link href='../assets/css/styles.css' type='text/css' rel='stylesheet'>");
print("<link href='../assets/css/bootstrap.min.css' rel='stylesheet'>");
print("<script src='../assets/js/jquery-min.js' type='text/javascript'></script>");
print("<script src='../assets/js/bootstrap.min.js' type='text/javascript'></script>");
print('</head>');

print("<body>");
print('<div class="container">');

require_once __DIR__ . '/../Commun/menu.php';

$gst_mode = empty($_POST['mode']) ? 'MENU' : $_POST['mode'];

switch ($gst_mode) {
    case 'MENU':
        AfficheMenu();
        break;
    case 'EXPORT_UNIONS':
        $etape_prec = getmicrotime();
        $st_fichier_unions = exporteUnions();
        print('<div class="text-center">');
        print benchmark("Export Union : $st_fichier_unions");
        $zip = new ZipArchive();
        $st_chemin_zip = "$gst_repertoire_indexes_geneabank/$gst_index_couple_geneabank";
        if (file_exists($st_chemin_zip)) unlink($st_chemin_zip);
        if ($zip->open($st_chemin_zip, ZIPARCHIVE::CREATE) !== TRUE) {
            exit("<div class=\"alert alert-danger\">Impossible d'écrire <$st_chemin_zip></div\n");
        }
        $zip->addFile($st_fichier_unions, "cpl_" . IDF_ASSO_GBK);
        $zip->close();
        print benchmark("ZIP Union");

        unlink($st_fichier_unions);
        AfficheResultatFichier($gst_url_indexes_geneabank, $gst_index_couple_geneabank);
        print("</div>");
        break;
    case 'EXPORT_INDEX_PATROS':
        $etape_prec = getmicrotime();
        $st_fichier_indexes = exporteIndexPatros($connexionBD, IDF_ASSO_GBK, $gst_repertoire_indexes_geneabank);
        print('<div class="text-center">');
        print benchmark("Export Index : $st_fichier_indexes");
        $zip = new ZipArchive();
        $st_chemin_zip = "$gst_repertoire_indexes_geneabank/$gst_index_patros_geneabank";
        if (file_exists($st_chemin_zip)) unlink($st_chemin_zip);
        if ($zip->open($st_chemin_zip, ZIPARCHIVE::CREATE) !== TRUE) {
            exit("<div class=\"alert alert-danger\">Impossible d'écrire <$st_chemin_zip></div\n");
        }
        $zip->addFile($st_fichier_indexes, IDF_ASSO_GBK);
        $zip->close();
        print benchmark("ZIP Index");

        unlink($st_fichier_indexes);
        AfficheResultatFichier($gst_url_indexes_geneabank, $gst_index_patros_geneabank);
        print("</div>");
        break;
    case 'EXPORT_INDEX_COMMUNES':
        ExporteIndexCommunes($connexionBD, $gst_repertoire_indexes_geneabank, $gst_compteurs_communes_geneabank, $gst_url_indexes_geneabank);
        break;
    case 'EXPORT_COMPTEURS':
        MajCompteurAdherents($connexionBD, $gst_repertoire_indexes_geneabank, $gst_compteurs_adherents_geneabank, $gst_url_indexes_geneabank, PREFIXE_ADH_GBK, NB_POINTS_GBK);
        break;
    default:
        print("<div class=\"alert alert-danger\">Mode $gst_mode non reconnu</div");
}

print("</div></body></html>");
