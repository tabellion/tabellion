<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------
require_once __DIR__ . '/app/bootstrap.php';
require_once __DIR__ . '/Commun/commun.php';
require_once __DIR__ . '/Commun/phonex.cls.php';
require_once __DIR__ . '/Commun/Courriel.php';
require_once __DIR__ . '/Administration/chargement/Acte.php';
require_once __DIR__ . '/Administration/chargement/CompteurActe.php';
require_once __DIR__ . '/Administration/chargement/Personne.php';
require_once __DIR__ . '/Administration/chargement/CompteurPersonne.php';
require_once __DIR__ . '/Administration/chargement/Prenom.php';
require_once __DIR__ . '/Administration/chargement/TypeActe.php';
require_once __DIR__ . '/Administration/chargement/CommunePersonne.php';
require_once __DIR__ . '/Administration/chargement/Profession.php';

function getRecapitulatifMessage($pst_type, $pi_max, $pi_compteur)
{
    switch ($pst_type) {
        case IDF_NAISSANCE:
            $pst_type = "naissance";
            break;
        case IDF_DECES:
            $pst_type = "d&eacute;c&eacute;s";
            break;
        default:
            $pst_type = "mariages et actes divers";
            break;
    }
    return sprintf("<br><div class=\"row text-center\">Il vous reste <div class=\"badge badge-warning\">%d</div> demandes de $pst_type dans ce mois</div>", $pi_max - $pi_compteur);
}

function getContentBottom($pst_type, $pst_email_adht, $pi_idf_acte)
{
    $st_msg = '';
    if (!empty(EMAIL_FORUM)) {
        $st_prefixe_asso = commence_par_une_voyelle(SIGLE_ASSO) ? "de l'" : "du ";
        switch ($pst_type) {
            case IDF_NAISSANCE:
                $st_msg = "";
            case IDF_DECES:
                $st_msg = "";
            default:
                $st_msg = "<blockquote class=\"blockquote\"><p class=\"row text-justify\">Vous pouvez mettre vos commentaires dans la cellule ci-dessous qui paraitra sur le forum &agrave; la suite de la r&eacute;ponse de la base. Votre adresse <span class=\"label label-danger\">$pst_email_adht</span> doit &ecirc;tre inscrite sur le forum Google Groupes $st_prefixe_asso" . SIGLE_ASSO . "<br>
                                <span class=\"label label-danger\">Sans cela, votre demande ne pourra &ecirc;tre prise en compte</span></p></blockquote>
                                <form id=\"envoi_forum\" method=post>
                                <input type=\"hidden\" name=\"mode\" value=\"ENVOI_FORUM\">
                                <input type=\"hidden\" name=\"idf_acte\" value=\"$pi_idf_acte\">
								<div class=\"lib_erreur\">
                                <textarea cols=\"40\" rows=\"6\" name=\"commentaire\" class=\"form-control\"></textarea>
								</div>
                                </form>";
                break;
        }
    }
    return $st_msg;
}

print('<!DOCTYPE html>');
print('<html lang="fr">');
print("<head>\n");
print('<meta name="viewport" content="width=device-width, initial-scale=1.0">');
print('<meta http-equiv="Content-Type" content="text/html; charset=windows-1252" >');
print('<meta http-equiv="content-language" content="fr"> ');
print("<link href='assets/css/styles.css' type='text/css' rel='stylesheet'>");
print("<link href='assets/css/bootstrap.min.css' rel='stylesheet'>");
print("<script src='assets/js/jquery-min.js' type='text/javascript'></script>");
print("<script src='assets/js/jquery.validate.min.js' type='text/javascript'></script>");
print("<script src='assets/js/additional-methods.min.js' type='text/javascript'></script>");
print("<script src='assets/js/jQuery.print.js' type='text/javascript'></script>");
print("<script src='assets/js/bootstrap.min.js' type='text/javascript'></script>");
?>
<script type='text/javascript'>
    $(document).ready(function() {
        $("#envoi_forum").validate({
            rules: {
                commentaire: "required"
            },
            messages: {
                commentaire: "Le commentaire est obligatoire"
            },
            errorElement: "em",
            errorPlacement: function(error, element) {
                // Add the `help-block` class to the error element
                error.addClass("help-block");

                // Add `has-feedback` class to the parent div.form-group
                // in order to add icons to inputs
                element.parents(".lib_erreur").addClass("has-feedback");

                if (element.prop("type") === "checkbox") {
                    error.insertAfter(element.parent("label"));
                } else {
                    error.insertAfter(element);
                }
                // Add the span element, if doesn't exists, and apply the icon classes to it.
                if (!element.next("span")[0]) {
                    $("<span class='glyphicon glyphicon-remove form-control-feedback'></span>").insertAfter(element);
                }
            },
            success: function(label, element) {
                // Add the span element, if doesn't exists, and apply the icon classes to it.
                if (!$(element).next("span")[0]) {
                    $("<span class='glyphicon glyphicon-ok form-control-feedback'></span>").insertAfter($(element));
                }
            },
            highlight: function(element, errorClass, validClass) {
                $(element).parents(".lib_erreur").addClass("has-error").removeClass("has-success");
                $(element).next("span").addClass("glyphicon-remove").removeClass("glyphicon-ok");
            },
            unhighlight: function(element, errorClass, validClass) {
                $(element).parents(".lib_erreur").addClass("has-success").removeClass("has-error");
                $(element).next("span").addClass("glyphicon-ok").removeClass("glyphicon-remove");
            }
        });
        $("#bouton_envoi").click(function() {
            $('#envoi_forum').submit()();
        });

        $("#bouton_fermeture").click(function() {
            window.close();
        });

        $("#bouton_impression").click(function() {
            $("#texte_acte").print({
                iframe: false,
                append: "Relev&eacute; provenant de: <?php print(LIB_ASSO); ?>"
            });
        });
    });
</script>
<?php
print('<title>Infos acte</title>');
print('</head>');
print('<body>');
print('<div class="container">');

print("<div class=\"text-center\"><img src=\"$gst_logo_association\" alt='Logo " . SIGLE_ASSO . "'></div>");

if (isset($_REQUEST['idf_acte'])) {
    $gi_idf_acte = (int) $_REQUEST['idf_acte'];
} else
    die("Erreur: L'identifiant de l'acte est manquant");



list($i_idf_adherent, $i_max_nai, $i_max_dec, $i_max_mar_div, $st_prenom_adht, $st_nom_adht, $st_email_adht) = $connexionBD->sql_select_liste("select idf,max_nai,max_dec,max_mar_div,prenom,nom,email_forum from adherent where ident='" . $_SESSION['ident'] . "'");
$i_idf_commune = $connexionBD->sql_select1("select idf_commune from acte where idf=$gi_idf_acte");

$a_profession = $connexionBD->liste_valeur_par_clef("select idf, nom from profession");
list($i_idf_type_acte, $i_idf_commune) = $connexionBD->sql_select_liste("select idf_type_acte,idf_commune from acte where idf=$gi_idf_acte");
$gst_adresse_ip = $_SERVER['REMOTE_ADDR'];

if (empty($_POST['mode'])) {
    $result = $connexionBD->sql_select_stats_actes($i_idf_adherent, $gi_idf_acte, $i_idf_type_acte);
    $i_nb_ddes = $result['counter_type'];
    $i_nb_ddes_acte = $result['counter_acte'];

    switch ($i_idf_type_acte) {
        case IDF_NAISSANCE:
            $i_max = $i_max_nai;
            break;
        case IDF_DECES:
            $i_max = $i_max_dec;
            break;
        default:
            $i_max = $i_max_mar_div;
            break;
    }
    if ($i_max - $i_nb_ddes > 0) {
        $o_acte = new Acte($connexionBD, null, null, null, null, null, null);
        $o_acte->charge($gi_idf_acte);
        $st_description_acte = $o_acte->versChaine();
        $i_nb_lignes = $o_acte->getNbLignes();
        $st_permalien =  $o_acte->getUrl();
        $st_source = $o_acte->getSource();
        print("<h3>Source: $st_source</h3>");
        print('<div id="texte_acte" class="text-center">');
        print("<textarea rows=$i_nb_lignes cols=80 class=\"form-control\">");
        print($st_description_acte);
        print("</textarea>");
        if (!empty($st_permalien))
            print("<br><a href=\"$st_permalien\" target=\"_blank\" class=\"btn btn-primary col-xs-4 col-xs-offset-4\"><span class=\"glyphicon glyphicon-picture\"></span>Lien vers les AD</a><br>");
        print("</div>");

        if ($i_nb_ddes_acte == 0) {
            $i_nb_ddes++;
            $st_requete = "insert into demandes_adherent(idf_adherent,adresse_ip,idf_commune,idf_acte,idf_type_acte,date_demande) values($i_idf_adherent,'$gst_adresse_ip',$i_idf_commune,$gi_idf_acte,$i_idf_type_acte,now())";
            $connexionBD->execute_requete($st_requete);
        }
        print(getRecapitulatifMessage($i_idf_type_acte, $i_max, $i_nb_ddes));
        print(getContentBottom($i_idf_type_acte, $st_email_adht, $gi_idf_acte));
    } else {
        print('<div class="alert alert-danger">Vous avez atteint votre quota. Merci d\'attendre le prochain mois</div>');
    }
} else {
    $st_requete = "select a.date,ta.nom,ca.nom,GROUP_CONCAT(concat(prn.libelle,' ',p.patronyme) order by p.idf separator ' X ') from acte a join commune_acte ca on (a.idf_commune=ca.idf) join type_acte ta on (a.idf_type_acte=ta.idf) join personne p on (p.idf_acte=a.idf) join prenom prn on (p.idf_prenom=prn.idf)  where a.idf=$gi_idf_acte and p.idf_type_presence=" . IDF_PRESENCE_INTV . " group by a.idf";
    list($st_date, $st_type_acte, $st_commune, $st_personnes) = $connexionBD->sql_select_liste($st_requete);
    $st_titre = cp1252_vers_utf8($st_personnes) . " le $st_date à " . cp1252_vers_utf8($st_commune);
    $a_commune_personne = $connexionBD->liste_valeur_par_clef("select idf, nom from commune_personne");
    $a_type_acte = $connexionBD->liste_valeur_par_clef("select idf, nom from type_acte");
    $o_acte = new Acte($connexionBD, null, null, null, null, null, null);
    $o_acte->charge($gi_idf_acte);
    $st_description_acte = $o_acte->versChaine();
    $i_nb_lignes = $o_acte->getNbLignes();
    $st_permalien =  $o_acte->getUrl();
    $st_releve_html = str_replace(array("\r", "\n"), '', nl2br(htmlentities($st_description_acte, ENT_COMPAT, 'UTF-8')));

    $st_commentaire = $_POST['commentaire'];

    $st_prenom_adht = cp1252_vers_utf8($st_prenom_adht);
    $st_nom_adht = cp1252_vers_utf8($st_nom_adht);

    $st_debut_msg_html  = "Bonjour<br /><br />";
    $st_debut_msg_html .= "Demande d'information ";
    $st_debut_msg_html .= "ci-dessous trouv&eacute;e dans les tables du site<br /><br />";

    $st_fin_msg_html = "<br />\n<div>Commentaire: <br />" . html_entity_decode(stripslashes($st_commentaire), ENT_COMPAT, 'UTF-8') . "</div><br />";
    $st_fin_msg_html .= "<br />\nMerci<br />";
    $st_message_html =   $st_debut_msg_html . $st_releve_html . $st_fin_msg_html;
    $st_message_texte = html_entity_decode(str_ireplace(array("<br>", "<br />"), "\r\n", $st_message_html), ENT_COMPAT, 'UTF-8');

    $courriel = new Courriel($gst_rep_site, $gst_serveur_smtp, $gst_utilisateur_smtp, $gst_mdp_smtp, $gi_port_smtp);
    $courriel->setExpediteur($st_email_adht, "$st_prenom_adht $st_nom_adht");
    $courriel->setAdresseRetour($st_email_adht);
    $courriel->setDestinataire(EMAIL_FORUM, "Forum " . SIGLE_ASSO);
    $courriel->setSujet("DI: $st_titre");
    $courriel->setTexte($st_message_html);
    $courriel->setTexteBrut($st_message_texte);
    if ($courriel->envoie())
        print('<div class="alert alert-success">La demande d\'information a &eacute;t&eacute; envoy&eacute;e</div>');
    else {
        $st_erreur = $courriel->get_erreur();
        print("<div class=\"alert alert-danger\">Le message n'a pu être envoyé. Erreur: $st_erreur</div>");
        $pf = @fopen("$gst_rep_logs/di_non_envoyees.log", 'a');
        date_default_timezone_set($gst_time_zone);
        list($i_sec, $i_min, $i_heure, $i_jmois, $i_mois, $i_annee, $i_j_sem, $i_j_an, $b_hiver) = localtime();
        $i_mois++;
        $i_annee += 1900;
        $st_date_log = sprintf("%02d/%02d/%04d %02d:%02d:%02d", $i_jmois, $i_mois, $i_annee, $i_heure, $i_min, $i_sec);
        $st_chaine_log = join(';', array($st_date_log, $_SESSION['ident'], $gst_adresse_ip, $st_prenom_adht, $st_nom_adht, $st_email_adht, $st_erreur));
        @fwrite($pf, "$st_chaine_log\n");
        @fclose($pf);
    }
}

print('<div class="btn-group-vertical btn-group-xs col-xs-8 col-xs-offset-2" role="group" aria-label="Groupe de demandes">');
if (!empty(EMAIL_FORUM))
    print('<button type="button" id="bouton_envoi" class="btn btn-primary"><span class="glyphicon glyphicon-send"></span> Envoyer une remarque sur le forum</button>');
print('<button type=button id="bouton_impression" class="btn btn-primary"><span class="glyphicon glyphicon-print"></span> Imprimer</button>');
print('<button type=button id="bouton_fermeture" class="btn btn-warning"><span class="glyphicon glyphicon-remove"></span> Fermer la fen&ecirc;tre</button>');
print('</div>');
print('</div>');
print('</body></HTML>');
