<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------

require_once __DIR__ . '/commun/config.php';
require_once __DIR__ . '/commun/constantes.php';
require_once __DIR__ . '/commun/ConnexionBD.php';
require_once __DIR__ . '/commun/commun.php';
require_once __DIR__ . '/commun/Adherent.php';

$gst_mode = empty($_POST['mode']) ? 'FORMULAIRE' : $_POST['mode'];

print('<!DOCTYPE html>');
print("<head>");
print('<link rel="shortcut icon" href="images/favicon.ico">');
print('<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">');
print('<meta http-equiv="content-language" content="fr">');
print('<meta name="viewport" content="width=device-width, initial-scale=1.0">');
print("<title>Creation d'un nouveau mot de passe</title>");
print("<link href='../css/styles.css' type='text/css' rel='stylesheet'>");
print("<link href='../css/bootstrap.min.css' rel='stylesheet'>");
print("<script src='../js/jquery-min.js' type='text/javascript'></script>\n");
print("<script src='../js/jquery.validate.min.js' type='text/javascript'></script>\n");
print("<script src='../js/jqueryadditional-methods.min.js' type='text/javascript'></script>\n");
print("<script src='../js/bootstrap.min.js' type='text/javascript'></script>");
?>
<script type='text/javascript'>
    $(document).ready(function() {
        $("#demande_nouveau_mdp").validate({
            rules: {
                email_adht: {
                    required: true,
                    email: true,
                }
            },
            messages: {
                email_adht: {
                    required: "L'adresse email est obligatoire",
                    email: "Ce n'est pas un email"
                }
            },
            errorElement: "em",
            errorPlacement: function(error, element) {
                // Add the `help-block` class to the error element
                error.addClass("help-block");

                // Add `has-feedback` class to the parent div.form-group
                // in order to add icons to inputs
                element.parents(".col-md-4").addClass("has-feedback");

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
                $(element).parents(".col-md-4").addClass("has-error").removeClass("has-success");
                $(element).next("span").addClass("glyphicon-remove").removeClass("glyphicon-ok");
            },
            unhighlight: function(element, errorClass, validClass) {
                $(element).parents(".col-md-4").addClass("has-success").removeClass("has-error");
                $(element).next("span").addClass("glyphicon-ok").removeClass("glyphicon-remove");
            }

        });

        $("#ferme").click(function() {
            window.close();
        });
    });
</script>
<?php

/**
 * Affiche le menu d'interrogation 
 */
function affiche_menu()
{
    print('<div class="panel panel-primary">');
    print('<div class="panel-heading">Demande d\'un nouveau mot de passe</div>');
    print('<div class="panel-body">');
    print("<form  method=\"post\" id=\"demande_nouveau_mdp\" class=\"form-horizontal\">");
    print('<div class="form-row">');
    $st_prefixe_asso = commence_par_une_voyelle(SIGLE_ASSO) ? "l'" : "le ";
    print("<label for=\"email_adht\" class=\"col-md-6 col-form-label\">Votre adresse e-mail connue par $st_prefixe_asso" . SIGLE_ASSO . ":</label>");
    print('<div class="col-md-4 col-md-offset-4">');
    print("<input type=\"text\" name=\"email_adht\" id=\"email_adht\" size=\"30\" maxlength=\"60\" class=\"form-control\">\n");
    print("</div></div>");
    print('<div class="form-row">');
    print('<div class="col-xs-3 col-xs-offset-1">');
    print('<div class="btn-group-vertical " role="group">');
    print('<button type=submit class="btn btn-primary"><span class="glyphicon glyphicon-lock"></span> Demander un nouveau mot de passe</button>');
    print('<button type="button" id=ferme class="btn btn-warning"><span class="glyphicon glyphicon-remove"></span> Annuler</button>');
    print("</div></div></div>");
    print("<input type=\"hidden\" name=\"mode\" value=\"DEMANDE\">\n");
    print('</form></div>');
}

/**
 * Renvoie le mot de passe à l'utilisateur si son email est reconnu, sinon renvoie à la première page.
 * @param string $pst_email Email perso de l'adhérent
 * @global string $gst_serveur_bd
 * @global string $gst_utilisateur_bd
 * @global string $gst_nom_bd
 * @global string $gst_mdp_utilisateur_bd   
 */
function verifie_demande($pst_email)
{
    global $gst_serveur_bd, $gst_utilisateur_bd, $gst_mdp_utilisateur_bd, $gst_nom_bd;
    global $gst_url_inscription;
    print('<div class="row">');
    print('<div class="panel panel-primary">');
    print('<div class="panel-heading">Demande d\'un nouveau mot de passe</div>');
    print('<div class="panel-body">');
    $connexionBD = ConnexionBD::singleton($gst_serveur_bd, $gst_utilisateur_bd, $gst_mdp_utilisateur_bd, $gst_nom_bd);
    $connexionBD->ajoute_params(array(':email_perso' => $pst_email));
    $st_requete = "SELECT idf FROM adherent where email_perso=:email_perso";
    $i_idf = $connexionBD->sql_select1($st_requete);
    if (!empty($i_idf)) {
        $adherent = new Adherent($connexionBD, $i_idf);
        if ($adherent->demande_nouveau_mdp()) {
            print("<div class=\"alert alert-success\">Un email dont le titre est \"Demande d'un nouveau mot de passe " . SIGLE_ASSO . "\" a &eacute;t&eacute; envoy&eacute; &agrave; l'adresse $pst_email<br>");
            print("Vous devrez confirmer votre demande en cliquant sur le lien contenu dans ce mail<br>");
            print("Merci</div>");
        } else {
            $st_prefixe_asso = commence_par_une_voyelle(SIGLE_ASSO) ? "de l'" : "du ";
            print("<div class=\"alert alert-warning\">Vous n'&ecirc;tes pas ou plus adh&eacute;rent $st_prefixe_asso" . SIGLE_ASSO . "</div>");
            if (isset($gst_url_inscription)) {
                print("<div class=\"text-center\">Veuillez vous r&eacute;inscrire en utilisant l'adresse suivante:<br>");
                print("<a href=\"$gst_url_inscription\">$gst_url_inscription</a><br>");
                print("Merci de votre fidelit&eacute;<br></div>");
            }
        }
    } else {
        print("<div class=\"alert alert-danger\">Votre adresse email n'a pas &eacute;t&eacute; reconnue</div>");
    }
    print('</div></div></div>');
    print('<div class="row">');
    print('<button type="button" id="ferme" class="btn btn-warning col-xs-4 col-xs-offset-4"><span class="glyphicon glyphicon-remove"></span> Fermer la  fenêtre</button>');
    print('</div>');
}

print('</head>');
print('<body>');
print('<div class="container">');

switch ($gst_mode) {
    case 'FORMULAIRE':
        affiche_menu();
        break;
    case 'DEMANDE':
        // le champ est netttoyé et tronqué à la valeur maximale de la base
        $st_email = substr(trim($_POST['email_adht']), 0, 60);
        verifie_demande($st_email);
        break;
}
print('</div></body></html>');
