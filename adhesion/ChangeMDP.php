<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------
require_once __DIR__ . '/app/bootstrap.php';
require_once __DIR__ . '/Commun/commun.php';
require_once __DIR__ . '/Commun/VerificationDroits.php';
require_once __DIR__ . '/Commun/Adherent.php';
require_once __DIR__ . '/Commun/Courriel.php';

if (!isset($_SESSION['ident']))
    die("<div class=\"alert aler-danger\" Identifiant non reconnu</div>");
$gst_ident = $_SESSION['ident'];

$connexionBD->initialise_params(array(':ident' => $gst_ident));
$i_idf_adht_connecte = $connexionBD->sql_select1("select idf from adherent where ident=:ident");

$gst_mode = isset($_POST['mode']) ? $_POST['mode'] : 'MENU_MODIFIER';
$adherent = new Adherent($connexionBD, $i_idf_adht_connecte);

print('<!DOCTYPE html>');
print("<head>");
print('<link rel="shortcut icon" href="assets/img/favicon.ico">');
print('<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">');
print('<meta http-equiv="content-language" content="fr">');
print('<meta name="viewport" content="width=device-width, initial-scale=1.0">');

print("<link href='assets/css/styles.css' type='text/css' rel='stylesheet'>");
print("<link href='assets/css/bootstrap.min.css' rel='stylesheet'>");
print("<script src='assets/js/jquery-min.js' type='text/javascript'></script>\n");
print("<link href='assets/css/jquery-ui.css' type='text/css' rel='stylesheet'>\n");
print("<link href='assets/css/jquery-ui.structure.min.css' type='text/css' rel='stylesheet'>\n");
print("<link href='assets/css/jquery-ui.theme.min.css' type='text/css' rel='stylesheet'>\n");
print("<script src='assets/js/jquery.validate.min.js' type='text/javascript'></script>\n");
print("<script src='assets/js/additional-methods.min.js' type='text/javascript'></script>\n");
print("<link href='assets/css/select2.min.css' type='text/css' rel='stylesheet'> ");
print("<script src='assets/js/jquery-ui.min.js' type='text/javascript'></script>\n");
print("<script src='assets/js/select2.min.js' type='text/javascript'></script>");
print("<script src='assets/js/bootstrap.min.js' type='text/javascript'></script>");
?>
<script type='text/javascript'>
    $(document).ready(function() {

        $("#change_mdp").validate({
            rules: {
                mdp_courant: {
                    required: true,
                    pattern: /^\w+$/,
                    minlength: 8,
                    maxlength: 12
                },
                nouveau_mdp: {
                    required: true,
                    pattern: /^\w+$/,
                    minlength: 8,
                    maxlength: 12
                },
                nouveau_mdp2: {
                    required: true,
                    pattern: /^\w+$/,
                    minlength: 8,
                    maxlength: 12,
                    equalTo: "#nouveau_mdp"
                }
            },
            messages: {
                mdp_courant: {
                    required: "Le mot de passe courant est obligatoire",
                    pattern: "Le mot de passe ne doit contenir que des lettres et des chiffres",
                    minlength: "Le mot de passe doit contenir au minimum 8 caractères",
                    maxlength: "Le mot de passe doit contenir au maximum 12 caractères"
                },
                nouveau_mdp: {
                    required: "Le nouveau mot de passe est obligatoire",
                    pattern: "Le mot de passe ne doit contenir que des lettres et des chiffres",
                    minlength: "Le mot de passe doit contenir au minimum 8 caractères",
                    maxlength: "Le mot de passe doit contenir au maximum 12 caractères"
                },
                nouveau_mdp2: {
                    required: "Le second nouveau mot de passe est obligatoire",
                    pattern: "Le mot de passe ne doit contenir que des lettres et des chiffres",
                    minlength: "Le mot de passe doit contenir au minimum 8 caractères",
                    maxlength: "Le mot de passe doit contenier au maximum 12 caractères",
                    equalTo: "le second mot de passe est différent du premier"
                }
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
    });
</script>
<?php
print("<title>Base " . SIGLE_ASSO . ": Changer votre mot de passe</title>\n");
print('</head>');

/**
 * Affiche de la table d'édition
 * @param object $pconnexionBD
 * @param object $padherent objet adherent 
 * @param integer $pi_idf_adh identifiant de l'adhérent

 */
function menu_change_mdp($pconnexionBD, $padherent, $pi_idf_adh)
{
    print('<div class="panel panel-primary">');
    print('<div class="panel-heading">Modifier votre mot de passe</div>');
    print('<div class="panel-body">');
    print("<form   id=\"change_mdp\" method=\"post\" class=\"form-horizontal\">\n");
    print("<input type=hidden name=mode value=MODIFIER>\n");
    print('<div class="form-group row">');
    print("<label for=\"mdp_courant\" class=\"form-col-label col-md-4 col-md-offset-2\">Votre mot de passe courant</label>");
    print('<div class="col-md-4">');
    print('<div class="col-md-6">');
    print('<div class="input-group">');
    print('<span class="input-group-addon">');
    print('<span class="glyphicon glyphicon-lock"></span>');
    print('</span>');
    print('<div class="lib_erreur">');
    print("<input type=\"password\" id=\"mdp_courant\" name=\"mdp_courant\" maxlength=12 class=\"form-control\"/>");
    print('</div></div></div></div>');
    print('<div class="form-group row">');
    print("<label for=\"nouveau_mdp\" class=\"form-col-label col-md-4 col-md-offset-2\">Votre nouveau mot de passe</label>");
    print('<div class="col-md-4">');
    print('<div class="col-md-6">');
    print('<div class="input-group">');
    print('<span class="input-group-addon">');
    print('<span class="glyphicon glyphicon-lock"></span>');
    print('</span>');
    print('<div class="lib_erreur">');
    print("<input type=\"password\" id=\"nouveau_mdp\" name=\"nouveau_mdp\" maxlength=12 class=\"form-control\" >");
    print('</div></div></div></div>');
    print('<div class="form-group row">');
    print("<label for=\"nouveau_mdp2\" class=\"form-col-label col-md-4 col-md-offset-2\">Retapez votre nouveau mot de passe</label>");
    print('<div class="col-md-4">');
    print('<div class="col-md-6">');
    print('<div class="input-group">');
    print('<span class="input-group-addon lib_erreur">');
    print('<span class="glyphicon glyphicon-lock"></span>');
    print('</span>');
    print('<div class="lib_erreur">');
    print("<input type=\"password\" id=\"nouveau_mdp2\"  name=\"nouveau_mdp2\" maxlength=12 class=\"form-control\"/></td></tr>");
    print('</div></div></div></div>');
    print('<div class="form-row">');
    print('<button type=submit class="btn btn-primary col-md-4 col-md-offset-4"><span class="glyphicon glyphicon-ok"></span> Modifier votre mot de passe</button>');
    print('</div></div>');
    print('</form></div>');
}

print('<body>');
print('<div class="container">');
if (!isset($_SESSION['ident']))
    die("<div class=\"text-center alert alert-danger\">Identifiant non reconnu</div>\n");
$gst_ident = $_SESSION['ident'];

require_once __DIR__ . '/Commun/menu.php';

switch ($gst_mode) {
    case 'MENU_MODIFIER':
        menu_change_mdp($connexionBD, $adherent, $i_idf_adht_connecte);
        break;
    case 'MODIFIER':
        $gst_mdp_courant = utf8_vers_cp1252(substr(trim($_POST['mdp_courant']), 0, 12));
        $gst_nouveau_mdp = utf8_vers_cp1252(substr(trim($_POST['nouveau_mdp']), 0, 12));
        $st_requete = "select mdp from adherent where ident='$gst_ident'";
        $st_mdp_hash = $connexionBD->sql_select1($st_requete);
        if (password_verify($gst_mdp_courant, $st_mdp_hash)) {
            try {
                $adherent->change_mdp($gst_nouveau_mdp);
            } catch (Exception $e) {
                print("<div class=\"text-center alert alert-danger\">Impossible de changer le mot de passe</div>" . $e->getMessage());
            }
            print("<div class=\"text-center alert alert-success\">Mot de passe chang&eacute;</div>");
        } else {
            print("<div class=\"text-center alert alert-danger\>Le mot de passe courant ne correspond pas &agrave; celui de l'identifiant connect&eacute;. Le mot de passe n'a pas &eacute;t&eacute; chang&eacute;</div>");
            menu_change_mdp($connexionBD, $adherent, $i_idf_adht_connecte);
        }
        break;
}

print("</div>");
print('</body></html>');
