<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------‌

require_once __DIR__ . '/../Commun/config.php';
require_once __DIR__ . '/../Commun/Identification.php';
require_once __DIR__ . '/../Commun/constantes.php';
require_once __DIR__ . '/../Commun/commun.php';
require_once __DIR__ . '/../Commun/VerificationDroits.php';
verifie_privilege(DROIT_VALIDATION_TD);
require_once __DIR__ . '/../Commun/ConnexionBD.php';
require_once __DIR__ . '/../Commun/finediff.php';
require_once __DIR__ . '/../libs/phonex.cls.php';
require_once __DIR__ . '/chargement/CompteurActe.php';
require_once __DIR__ . '/chargement/Acte.php';
require_once __DIR__ . '/chargement/CompteurPersonne.php';
require_once __DIR__ . '/chargement/Personne.php';
require_once __DIR__ . '/chargement/Patronyme.php';
require_once __DIR__ . '/chargement/Prenom.php';
require_once __DIR__ . '/chargement/CommunePersonne.php';
require_once __DIR__ . '/chargement/Profession.php';
require_once __DIR__ . '/chargement/TypeActe.php';
require_once __DIR__ . '/chargement/Union.php';
require_once __DIR__ . '/chargement/StatsPatronyme.php';
require_once __DIR__ . '/chargement/StatsCommune.php';
require_once __DIR__ . '/chargement/ModificationActe.php';
require_once __DIR__ . '/chargement/ModificationPersonne.php';

$connexionBD = ConnexionBD::singleton($gst_serveur_bd, $gst_utilisateur_bd, $gst_mdp_utilisateur_bd, $gst_nom_bd);

$gi_idf_demandeur = null;
$gst_email_demandeur = '';
$gst_formulaire = '';
if (!empty($_SESSION['ident'])) {
    $st_requete = "select idf,email_perso from adherent where ident='" . $_SESSION['ident'] . "'";
    list($gi_idf_demandeur, $gst_email_demandeur) = $connexionBD->sql_select_liste($st_requete);
}

/*
* Construit la chaine permettant la validation des paramètres d'un formulaire
* @return string règles de validation
*/
function regles_validation()
{
    global $go_acte, $gst_email_demandeur;
    $a_filtres = $go_acte->getFiltresParametres();
    $ga_liste_personnes = $go_acte->getListePersonnes();
    $a_messages = array();
    $st_chaine = '';
    foreach ($ga_liste_personnes as $o_pers) {
        foreach ($o_pers->getFiltresParametres() as $st_param => $a_filtres_personne) {
            $a_filtres[$st_param] = $a_filtres_personne;
        }
    }
    foreach ($a_filtres as $st_param => $a_liste_tests) {
        $st_test =    "\t$st_param: { ";
        $st_message = "\t$st_param: { ";
        $a_tests = array();
        $a_msgs = array();
        foreach ($a_liste_tests as $a_test) {
            list($st_type_test, $st_valeur_test, $st_message_erreur) = $a_test;
            $a_tests[] = "\t\t$st_type_test: $st_valeur_test";
            $a_msgs[] = "\t\t$st_type_test: \"$st_message_erreur\"";
        }
        $st_test .= implode(",\n", $a_tests);
        $st_test .= "\n\t}";
        $st_message .= implode(",\n", $a_msgs);
        $st_message .= "\n\t}";
        $a_regles[] = $st_test;
        $a_messages[] = $st_message;
    }
    $st_chaine =    "rules: {\n" . implode(",\n", $a_regles) . "},\n";
    $st_chaine .= "messages: {\n" . implode(",\n", $a_messages) . "}\n";
    return  $st_chaine;
}

$gst_mode = isset($_REQUEST['MODE']) ? $_REQUEST['MODE'] : '';
$gst_adresse_retour = isset($_REQUEST['adresse_retour']) ?  $_REQUEST['adresse_retour'] : null;


if (isset($_REQUEST['idf_modification'])) {
    $gi_idf_modification = (int) $_REQUEST['idf_modification'];
    $go_acte = new ModificationActe($connexionBD, null, $gi_idf_modification, $gst_rep_site, $gst_serveur_smtp, $gst_utilisateur_smtp, $gst_mdp_smtp, $gi_port_smtp);
    $go_acte->charge($gi_idf_modification);
    if (empty($gst_mode)) {
        $a_filtres_acte = array();
        $go_acte->setFiltresParametres($a_filtres_acte);
        $a_params_completion_auto = array();
        $go_acte->setParamsCompletionAuto($a_params_completion_auto);
        if (empty($gst_mode)) {
            $gst_formulaire = $go_acte->formulaire_haut_acte();
            $gst_formulaire .= $go_acte->formulaire_liste_personnes();
            $gst_formulaire .= $go_acte->formulaire_bas_acte();
        }
    }
} else {
    die("<div class=\"alert alert-danger\">Pas d'identifiant d'acte sp&eacute;cifi&eacute;</div>");
}
?>
<!DOCTYPE html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='../assets/css/styles.css' type='text/css' rel='stylesheet'>
    <link href='../assets/css/bootstrap.min.css' rel='stylesheet'>
    <link href='../assets/css/jquery-ui.css' type='text/css' rel='stylesheet'>
    <link href='../assets/css/jquery-ui.structure.min.css' type='text/css' rel='stylesheet'>
    <link href='../assets/css/jquery-ui.theme.min.css' type='text/css' rel='stylesheet'>
    <link href='../assets/css/jquery-te-1.4.0.css' type='text/css' rel='stylesheet'>
    <meta http-equiv="content-language" content="fr">
    <script src='../assets/js/jquery-min.js' type='text/javascript'></script>
    <script src='../assets/js/jquery.validate.min.js' type='text/javascript'></script>
    <script src='../assets/js/additional-methods.min.js' type='text/javascript'></script>
    <script src='../assets/js/jquery-ui.min.js' type='text/javascript'></script>
    <script src='../assets/js/CalRep.js' type='text/javascript'></script>
    <script src='../assets/iviewer/jquery-ui.min.js' type='text/javascript'></script>
    <script src='../assets/iviewer/jquery.mousewheel.min.js' type='text/javascript'></script>
    <script src='../assets/iviewer/jquery.iviewer.js' type='text/javascript'></script>
    <script src='../assets/js/bootstrap.min.js' type='text/javascript'></script>
    <script src='../assets/js/jquery-te-1.4.0.min.js' type='text/javascript'></script>
    <link href='../assets/iviewer/jquery.iviewer.css' type='text/css' rel='stylesheet'>

    <style type="text/css">
        .diffDeleted span {
            border: 1px solid rgb(255, 192, 192);
            background: rgb(255, 224, 224);
        }

        .diffInserted span {
            border: 1px solid rgb(192, 255, 192);
            background: rgb(224, 255, 224);
        }

        del {
            border: 1px solid rgb(255, 192, 192);
            background: rgb(255, 224, 224);
        }

        ins {
            border: 1px solid rgb(192, 255, 192);
            background: rgb(224, 255, 224);
        }

        .viewer {
            width: 50%;
            height: 500px;
            border: 1px solid black;
            position: relative;
        }

        .wrapper {
            overflow: hidden;
        }
    </style>
    <script type='text/javascript'>
        $(document).ready(function() {
            <?php
            print file_get_contents('../js/dateITA.js');
            ?>
            $("#modification_acceptee").validate({
                <?php
                if (empty($gst_mode))
                    print regles_validation();
                ?>,
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
            <?php
            if (empty($gst_mode))
                print $go_acte->validation_formulaire_refus();
            ?>
            <?php
            if (empty($gst_mode)) {
                print $go_acte->fonctions_jquery_completion();
                print file_get_contents('../js/EditionActe.js');
            }
            ?>
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
    </script>
    <?php
    print("<title>Validation de modification d'un acte</title>");
    print("</head>\n");
    /******************************************************************************/
    /*                     CORPS DE LA PAGE                                   	  */
    /******************************************************************************/
    print("<body>\n");
    print('<div class="container">');

    require_once __DIR__ . '/../Commun/menu.php';

    print('<div class="panel panel-primary">');
    print("<div class=\"panel-heading\">Validation de modification d'un acte</div>");
    print('<div class="panel-body">');

    if (empty($gst_mode)) {
        print("<form id=\"modification_acceptee\" method=\"POST\" enctype=\"multipart/form-data\" >");
        print("<input type=\"hidden\" name=\"MODE\" value=\"VALIDATION\">");
        print("<input type=\"hidden\" name=\"idf_modification\" value=\"$gi_idf_modification\">");
        print($go_acte->infos_demandeur());
        print($go_acte->differences());
        print($go_acte->affichage_image_permalien(800, 800));
        print("<table class=\"table table-bordered\">");
        print($gst_formulaire);
        print("</table>");
        print $go_acte->visualisation_photos();
        print $go_acte->commentaires_demandeur();
        print('<div class="row">');
        print('<button type="submit" id="bouton_soum" class="btn btn-success col-md-4 col-md-offset-4"><span class="glyphicon glyphicon-ok"></span> Approuver la demande</button>');
        print('</div>');
        print("</form>");
        print($go_acte->formulaire_refus());
        $_SESSION['adresse_retour'] = $gst_adresse_retour;
    } else {
        $gi_idf_modification = isset($_REQUEST['idf_modification']) ? (int) $_REQUEST['idf_modification'] :  null;
        $gst_adresse_retour = isset($_SESSION['adresse_retour']) ? $_SESSION['adresse_retour'] : '';
        unset($_SESSION['adresse_retour']);
        list($i_idf_valideur, $st_nom_valideur, $st_prenom_valideur, $st_email_valideur) = $connexionBD->sql_select_liste("select idf,nom,prenom,email_perso from adherent where ident='" . $_SESSION['ident'] . "'");
        $st_nom_valideur = cp1252_vers_utf8($st_nom_valideur);
        $st_prenom_valideur = cp1252_vers_utf8($st_prenom_valideur);
        switch ($gst_mode) {
            case 'VALIDATION':
                $st_cmt_valideur = isset($_POST['cmt_valideur']) ? $_POST['cmt_valideur'] : 'ERREUR';
                $go_acte->accepte($i_idf_valideur, $st_nom_valideur, $st_prenom_valideur, $st_email_valideur, $st_cmt_valideur);
                break;
            case 'REFUS':
                $st_motif_refus = isset($_POST['motif_refus']) ? $_POST['motif_refus'] : 'ERREUR';
                $go_acte->refuse($i_idf_valideur, $st_nom_valideur, $st_prenom_valideur, $st_email_valideur, $st_motif_refus);
                break;
            default:
                die("Mode $gst_mode inconnu");
        }
    }
    print("<form action=\"$gst_url_site/$gst_adresse_retour\" method=\"POST\">");

    print('<button type="submit" class="btn btn-primary col-md-4 col-md-offset-4"><span class="glyphicon glyphicon-home"></span> Liste des demandes en attente</button>');
    print("</form>");
    print("</div></div>");
    print("</div></body></html>\n");
