<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------

require_once __DIR__ . '/../Commun/config.php';
require_once __DIR__ . '/../Commun/constantes.php';
require_once __DIR__ . '/../Commun/Identification.php';
require_once __DIR__ . '/../Commun/VerificationDroits.php';
verifie_privilege(DROIT_CHARGEMENT);
require_once __DIR__ . '/../Commun/ConnexionBD.php';
require_once __DIR__ . '/../Commun/commun.php';
require_once __DIR__ . '/../Commun/Courriel.php';

$connexionBD = ConnexionBD::singleton($gst_serveur_bd, $gst_utilisateur_bd, $gst_mdp_utilisateur_bd, $gst_nom_bd);

print('<!DOCTYPE html>');
print("<Head>\n");
print('<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" >');
print('<meta http-equiv="content-language" content="fr">');
print('<meta name="viewport" content="width=device-width, initial-scale=1.0">');
print("<link href='../assets/css/styles.css' type='text/css' rel='stylesheet'>");
print("<link href='../assets/css/jquery-te-1.4.0.css' type='text/css' rel='stylesheet'>");
print("<link href='../assets/css/bootstrap.min.css' rel='stylesheet'>");
print("<link href='../assets/css/select2.min.css' type='text/css' rel='stylesheet'> ");
print("<link href='../assets/css/select2-bootstrap.min.css' type='text/css' rel='stylesheet'> ");
print("<script src='../assets/js/jquery-min.js' type='text/javascript'></script>");
print("<script src='../assets/js/select2.min.js' type='text/javascript'></script>");
print("<script src='../assets/js/bootstrap.min.js' type='text/javascript'></script>");
print("<script src='../assets/js/jquery-te-1.4.0.min.js' type='text/javascript'></script>");
?>
<script type='text/javascript'>
    $(document).ready(function() {

        $.fn.select2.defaults.set("theme", "bootstrap");

        $(".js-select-avec-recherche").select2();

        $('#notification').submit(function() {
            var source = $('#idf_source option:selected').text();
            var type_acte = $('#idf_type_acte option:selected').text();
            var commune = $('#idf_commune option:selected').text();
            var c = confirm("Etes-vous sûr de notifier le chargement des actes de " + type_acte + " de la commune " + commune + " (source " + source + ") ?");
            return c;
        });

        $("textarea.jqte_edit").jqte();


        // settings of status
        var jqteStatus = true;
        $(".status").click(function() {
            jqteStatus = jqteStatus ? false : true;
            $('textarea.jqte_edit').jqte({
                "status": jqteStatus
            })
        });
    });
</script>
<?php

print("<title>Notification des chargements</title>");
print('</Head>');
print("<body>");
print('<div class="container">');

/**
 * Affiche le menu de selection de la commune a notifier
 * @param object $pconnexionBD object connexion BD
 * @param integer $pi_idf_source identifiant de la source
 * @param integer $pi_idf_commune identifiant de la commune
 * @param integer $pi_idf_type_acte identifiant du type d'acte   
 */

function AfficheSelectionNotification($pconnexionBD, $pi_idf_source, $pi_idf_commune, $pi_idf_type_acte)
{
    global $ga_types_nimegue;
    $a_sources = $pconnexionBD->liste_valeur_par_clef("SELECT idf,nom FROM source order by nom");
    $a_communes = $pconnexionBD->liste_valeur_par_clef("SELECT idf,nom FROM commune_acte order by nom");
    print('<div class="panel panel-primary">');
    print('<div class="panel-heading">Notification du chargement des donn&eacute;es d\'une commune/paroisse</div>');
    print('<div class="panel-body">');
    print("<form  id=\"notification\" method=\"post\">");

    print("<input type=hidden name=mode value=EDITION_NOTIFICATION>");
    print('<div class="form-row col-md-12">');
    print('<label for="idf_source" class="col-form-label col-md-2 col-md-offset-3">Source:</label>');
    print('<div class="col-md-4">');
    print('<select name=idf_source id=idf_source class="js-select-avec-recherche form-control">');
    print(chaine_select_options($pi_idf_source, $a_sources));
    print('</select></div></div>');
    print('<div class="form-row col-md-12">');
    print('<label for="idf_commune" class="col-form-label col-md-2 col-md-offset-3">Commune:</label>');
    print('<div class="col-md-4">');
    print('<select name=idf_commune id=idf_commune class="js-select-avec-recherche form-control">');
    print(chaine_select_options($pi_idf_commune, $a_communes));
    print('</select></div></div>');
    print('<div class="form-row col-md-12">');
    print('<label for="idf_type_acte" class="col-form-label col-md-2 col-md-offset-3">Type d\'acte:</label>');
    print('<div class="col-md-4">');
    print('<select name=idf_type_acte id=idf_type_acte class="js-select-avec-recherche form-control">');
    print(chaine_select_options($pi_idf_type_acte, $ga_types_nimegue, false));
    print('</select></div></div>');
    print('<div class="form-row">');
    print('<button type=submit class="btn btn-primary col-md-offset-4 col-md-4"><span class="glyphicon glyphicon-envelope"></span> Notifier un chargement</button>');
    print('</div>');
    print("</form></div></div>");
}

/**
 * Affiche le menu de selection de la commune a notifier
 * @param object $pconnexionBD object connexion BD
 * @param integer $pi_idf_source identifiant de la source
 * @param integer $pi_idf_commune identifiant de la commune 
 * @param integer $pi_idf_type_acte identifiant du type d'acte
 * @global string $gst_url_site adresse du site  
 */

function AfficheEditionNotification($pconnexionBD, $pi_idf_source, $pi_idf_commune, $pi_idf_type_acte)
{
    global $gst_url_site;

    print("<form  method=\"post\">");
    $st_texte = "Bonjour,<br /><br />";
    $st_texte .= "Nous vous informons que la base " . SIGLE_ASSO . " a &eacute;t&eacute; mise &agrave; jour:<br /><br />";
    if ($pi_idf_type_acte == IDF_MARIAGE || $pi_idf_type_acte == IDF_NAISSANCE || $pi_idf_type_acte == IDF_DECES) {
        $st_requete = "select s.nom,ca.nom,c.nom,ta.nom, sc.annee_min,sc.annee_max,sc.nb_actes from stats_commune sc join commune_acte ca on (sc.idf_commune=ca.idf) join type_acte ta on (sc.idf_type_acte=ta.idf) join source s on (sc.idf_source=s.idf) left join canton c on (ca.idf_canton=c.idf) where sc.idf_source=$pi_idf_source and sc.idf_commune=$pi_idf_commune and sc.idf_type_acte=$pi_idf_type_acte";
        list($st_source, $st_commune, $st_canton, $st_type_acte, $i_annee_min, $i_annee_max, $i_nb_actes)  = $pconnexionBD->sql_select_liste($st_requete);
        $st_libelle_canton = ($st_canton != '') ? "(canton de $st_canton)" : '';
        $st_texte .= "La source '$st_source' de la commune/paroisse <b>" . cp1252_vers_utf8($st_commune) . "</b> " . cp1252_vers_utf8($st_libelle_canton) . " a &eacute;t&eacute; mise &agrave; jour sur la p&eacute;riode: ";
        $st_texte .= "<u><a href=\"$gst_url_site/AfficheStatsCommune.php?idf_source=$pi_idf_source&idf_commune=$pi_idf_commune&idf_type_acte=$pi_idf_type_acte\">$i_annee_min</u>-<u>$i_annee_max</a></u><br />";
        $st_texte .= "Le d&eacute;tail des ann&eacute;es disponibles est consultable en cliquant sur l'intervalle des années sur-ligné en bleu ci-dessus.<br /><br />";
        $st_texte .= "La commune comporte d&eacute;sormais $i_nb_actes actes de type: " . cp1252_vers_utf8($st_type_acte) . "<br />";
    } else {
        $st_requete = "select s.nom,ca.nom,c.nom,ta.nom, sc.annee_min,sc.annee_max,sc.nb_actes from stats_commune sc join commune_acte ca on (sc.idf_commune=ca.idf) join type_acte ta on (sc.idf_type_acte=ta.idf) join source s on (sc.idf_source=s.idf) left join canton c on (ca.idf_canton=c.idf) where sc.idf_source=$pi_idf_source and sc.idf_commune=$pi_idf_commune";
        list($st_source, $st_commune, $st_canton, $st_type_acte, $i_annee_min, $i_annee_max, $i_nb_actes)  = $pconnexionBD->sql_select_liste($st_requete);
        $a_stats_type_acte = $pconnexionBD->sql_select_multiple("select ta.idf,ta.nom, sc.annee_min,sc.annee_max,sc.nb_actes from stats_commune sc join type_acte ta on (sc.idf_type_acte=ta.idf)  where sc.idf_source=$pi_idf_source and sc.idf_commune=$pi_idf_commune and sc.idf_type_acte not in (" . IDF_MARIAGE . "," . IDF_NAISSANCE . "," . IDF_DECES . ") order by ta.nom");
        $st_texte .= "Les actes divers de la commune/paroisse de <b>" . cp1252_vers_utf8($st_commune) . "</b> (Source: " . cp1252_vers_utf8($st_source) . ") sont maintenant disponibles: <br /><br />";
        $st_texte .= "<table border=1><tr><th>Type d'acte</th><th>Ann&eacute;es</th><th>Nombre d'actes</th>";
        foreach ($a_stats_type_acte as $a_ligne) {
            $st_texte .= "<tr>";
            list($i_idf_type_acte, $st_type_acte, $i_annee_min, $i_annee_max, $i_nb_actes) = $a_ligne;
            // les td sont séparés par des espaces pour qu'une séparation soit visible dans le message au format texte (suppression des balises HTML)
            if ($i_annee_min == $i_annee_max)
                $st_texte .= "<td>" . cp1252_vers_utf8($st_type_acte) . "</td> <td><a href=\"$gst_url_site/AfficheStatsCommune.php?idf_source=$pi_idf_source&idf_commune=$pi_idf_commune&idf_type_acte=$i_idf_type_acte\">$i_annee_min</a></td> <td>$i_nb_actes actes</td>";
            else
                $st_texte .= "<td>" . cp1252_vers_utf8($st_type_acte) . "</td> <td><a href=\"$gst_url_site/AfficheStatsCommune.php?idf_source=$pi_idf_source&idf_commune=$pi_idf_commune&idf_type_acte=$i_idf_type_acte\">$i_annee_min-$i_annee_max</a></td> <td>$i_nb_actes actes</td>";
            $st_texte .= "</tr>\n";
        }
        $st_texte .= "</table><br />";
        $st_texte .= "Le d&eacute;tail des ann&eacute;es disponibles est consultable en cliquant sur les intervalle des années sur-lignés en bleu ci-dessus<br /><br />";
    }
    $st_texte .= "Pour rappel, la liste compl&egrave;te des d&eacute;pouillements se trouve &agrave; l'adresse: <a href=\"$gst_url_site/AfficheStatsCommune.php\">$gst_url_site/AfficheStatsCommune.php</a><br /><br />";
    $st_texte .= "Cordialement <br /><br />";
    $st_texte .= "	Les responsables de la base " . SIGLE_ASSO;
    print('<textarea name="texte" id="texte" class="jqte_edit form-control" rows=20 cols=80>' . $st_texte . '</textarea>');
    print("<input type=hidden name=mode value=ENVOI_NOTIFICATION>");
    print('<div class="form-row">');
    print('<button type=submit class="btn btn-primary col-md-offset-4 col-md-4"><span class="glyphicon glyphicon-envelope"></span> Envoyer la notification</button>');
    print("</div>");
    print("</form>");
    print("<form   method=\"post\">");
    print("<input type=hidden name=mode value=\"SELECTION_NOTIFICATION\">");
    print('<div class="form-row">');
    print('<button type=submit class="btn btn-primary col-md-offset-4 col-md-4">Annuler</button>');
    print("</div>");
    print('</form>');
}

/**
 * Envoie la notification sur le forum
 * @param object $pconnexionBD object connexion BD
 * @param integer $pi_idf_commune identifiant de la commune 
 * @param integer $pi_idf_type_acte identifiant du type d'acte
 * @param string $pst_msg_html message html à envoyer 
 * @global string $gst_url_site adresse du site  
 */
function EnvoieNotification($pconnexionBD, $pi_idf_commune, $pi_idf_type_acte, $pst_msg_html)
{
    global $gst_rep_site, $gst_serveur_smtp, $gst_utilisateur_smtp, $gst_mdp_smtp, $gi_port_smtp;
    $st_commune = $pconnexionBD->sql_select1("select nom from commune_acte where idf=$pi_idf_commune");
    $st_type_acte = $pconnexionBD->sql_select1("select nom from type_acte where idf=$pi_idf_type_acte");
    switch ($pi_idf_type_acte) {
            // meme sujet pour les naissances, mariages et deces
        case IDF_MARIAGE:
        case IDF_NAISSANCE:
        case IDF_DECES:
            $st_sujet = "MAJ Base " . SIGLE_ASSO . ": " . cp1252_vers_utf8($st_type_acte) . " de la commune de " . cp1252_vers_utf8($st_commune);
            break;
        default:
            $st_sujet = "MAJ Base " . SIGLE_ASSO . ": Actes divers de la commune de " . cp1252_vers_utf8($st_commune);
    }

    list($st_nom, $st_prenom, $st_email) = $pconnexionBD->sql_select_liste("select nom,prenom,email_forum from adherent where ident='" . $_SESSION['ident'] . "'");
    $st_nom_expediteur = cp1252_vers_utf8($st_prenom) . " " . cp1252_vers_utf8($st_nom);
    $st_msg_html = '<style>table {
  width: 100%;
}

th {
  height: 50px;
  background-color: #1e6ca0;
  color: white;
}
th, td {
  padding: 15px;
  text-align: left;
}
tr:nth-child(even) {background-color: #f2f2f2;}
</style>' . $pst_msg_html;
    $st_message_texte = html_entity_decode(str_ireplace(array("<br>", "<br />", "<hr />", "<hr>"), "\r\n", $pst_msg_html), ENT_COMPAT, 'UTF-8');

    $courriel = new Courriel($gst_rep_site, $gst_serveur_smtp, $gst_utilisateur_smtp, $gst_mdp_smtp, $gi_port_smtp);
    $courriel->setExpediteur($st_email, $st_nom_expediteur);
    $courriel->setDestinataire(EMAIL_FORUM, "Forum " . SIGLE_ASSO);
    $courriel->setSujet($st_sujet);
    $courriel->setTexte($st_msg_html);
    $courriel->setTexteBrut($st_message_texte);
    if ($courriel->envoie())
        print('<div class="alert alert-success">Notification envoy&eacute;e avec succ&egrave;s sur le forum</div>');
    else
        print("<div class=\"alert alert-danger\">Le message n'a pu être envoyé. Erreur: " . $courriel->get_erreur() . "</div>");
}

require_once __DIR__ . '/../Commun/menu.php';

$i_session_idf_source = isset($_SESSION['idf_source']) ? $_SESSION['idf_source'] : '1';
$gi_idf_source = empty($_POST['idf_source']) ? $i_session_idf_source : $_POST['idf_source'];


$i_session_idf_commune = isset($_SESSION['idf_commune']) ? $_SESSION['idf_commune'] : '1';
$gi_idf_commune = empty($_POST['idf_commune']) ? $i_session_idf_commune : $_POST['idf_commune'];

$i_session_idf_type_acte = isset($_SESSION['idf_type_acte']) ? $_SESSION['idf_type_acte'] : '';
$gi_idf_type_acte = empty($_POST['idf_type_acte']) ? $i_session_idf_type_acte : $_POST['idf_type_acte'];

$i_session_idf_type_acte_nimegue = isset($_SESSION['idf_type_acte_nimegue']) ? $_SESSION['idf_type_acte_nimegue'] : 0;
$gi_idf_type_acte_nimegue = empty($_POST['idf_type_acte_nimegue']) ? $i_session_idf_type_acte_nimegue : $_POST['idf_type_acte_nimegue'];


$gst_mode = empty($_POST['mode']) ? 'SELECTION_NOTIFICATION' : $_POST['mode'];

switch ($gst_mode) {
    case 'SELECTION_NOTIFICATION':
        AfficheSelectionNotification($connexionBD, $gi_idf_source, $gi_idf_commune, $gi_idf_type_acte);
        break;
    case 'EDITION_NOTIFICATION':


        $_SESSION['idf_source'] = $gi_idf_source;
        $_SESSION['idf_commune'] = $gi_idf_commune;
        $_SESSION['idf_type_acte'] = $gi_idf_type_acte;
        $_SESSION['idf_type_acte_nimegue'] = $gi_idf_type_acte_nimegue;
        if ($gi_idf_type_acte_nimegue == 0)
            AfficheEditionNotification($connexionBD, $gi_idf_source, $gi_idf_commune, $gi_idf_type_acte);
        else {
            // le type d'acte Nimegue Divers etant assimile a celui de CM, aucun besoin
            // de correspondance => je sais, c'est de la bidouille honteuse
            AfficheEditionNotification($connexionBD, $gi_idf_source, $gi_idf_commune, $gi_idf_type_acte_nimegue);
        }
        break;
    case 'ENVOI_NOTIFICATION':
        $gst_texte = $_POST['texte'];


        if ($gi_idf_type_acte_nimegue == 0) {
            // Retour a la page de gestion de donnees
            print("<form  method=\"post\">");
            print("<input type=hidden name=mode value=SELECTION_NOTIFICATION>");
            EnvoieNotification($connexionBD, $gi_idf_commune, $gi_idf_type_acte, $gst_texte);
            print('<div class="form-row">');
            print('<button type=submit class="btn btn-primary col-md-offset-4 col-md-4">Retour au menu de notification</button>');
            print("</div>");
            print("</form>");
        } else {

            print("<form action=\"GestionDonnees.php\" method=\"post\">");
            print("<input type=hidden name=mode value=FORMULAIRE>");
            EnvoieNotification($connexionBD, $gi_idf_commune, $gi_idf_type_acte_nimegue, $gst_texte);
            print('<div class="form-row">');
            print('<button type=submit class="btn btn-primary col-md-offset-4 col-md-4">Retour au menu chargement/export</button>');
            print("</div>");
            print("</form>");
        }
        unset($_SESSION['idf_type_acte_nimegue']);
        break;
    default:
        print("Mode $gst_mode non reconnu");
}

print("</div></body></html>");
