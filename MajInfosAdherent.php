<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------
require_once __DIR__ . '/Commun/config.php';
require_once __DIR__ . '/Commun/constantes.php';
require_once __DIR__ . '/Commun/Identification.php';
require_once __DIR__ . '/Commun/VerificationDroits.php';
require_once __DIR__ . '/Commun/ConnexionBD.php';
require_once __DIR__ . '/Commun/commun.php';
require_once __DIR__ . '/Commun/Adherent.php';

$connexionBD = ConnexionBD::singleton($gst_serveur_bd, $gst_utilisateur_bd, $gst_mdp_utilisateur_bd, $gst_nom_bd);

$connexionBD->initialise_params(array(':ident' => $gst_ident));
$i_idf_adht_connecte = $connexionBD->sql_select1("select idf from adherent where ident=:ident");

$gst_mode = isset($_POST['mode']) ? $_POST['mode'] : 'MENU_MODIFIER';
$adherent = new Adherent($connexionBD, $i_idf_adht_connecte);

print('<!DOCTYPE html>');
print("<head>");
print('<link rel="shortcut icon" href="images/favicon.ico">');
print('<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">');
print('<meta http-equiv="content-language" content="fr">');
print('<meta name="viewport" content="width=device-width, initial-scale=1.0">');
print("<link href='css/styles.css' type='text/css' rel='stylesheet'>");
print("<link href='css/bootstrap.min.css' rel='stylesheet'>");
print("<link href='css/jquery-ui.css' type='text/css' rel='stylesheet'>\n");
print("<link href='css/jquery-ui.structure.min.css' type='text/css' rel='stylesheet'>\n");
print("<link href='css/jquery-ui.theme.min.css' type='text/css' rel='stylesheet'>\n");
print("<link href='css/select2.min.css' type='text/css' rel='stylesheet'>");
print("<link href='css/select2-bootstrap.min.css' type='text/css' rel='stylesheet'>");
print("<script src='js/jquery-min.js' type='text/javascript'></script>\n");
print("<script src='js/jquery-ui.min.js' type='text/javascript'></script>\n");
print("<script src='js/jquery.validate.min.js' type='text/javascript'></script>\n");
print("<script src='js/additional-methods.min.js' type='text/javascript'></script>\n");
print("<script src='js/select2.min.js' type='text/javascript'></script>");
print("<script src='js/bootstrap.min.js' type='text/javascript'></script>");
?>
<script type='text/javascript'>
    $(document).ready(function() {

        $.fn.select2.defaults.set("theme", "bootstrap");

        $(".js-select-avec-recherche").select2();

        $("#maj_infos_adherent").validate({
            <?php
            print $adherent->regles_validation();
            ?>,
            errorElement: "em",
            errorPlacement: function(error, element) {
                // Add the `help-block` class to the error element
                error.addClass("help-block");

                // Add `has-feedback` class to the parent div.form-group
                // in order to add icons to inputs
                element.parents(".col-md-8").addClass("has-feedback");

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
                $(element).parents(".col-md-8").addClass("has-error").removeClass("has-success");
                $(element).next("span").addClass("glyphicon-remove").removeClass("glyphicon-ok");
            },
            unhighlight: function(element, errorClass, validClass) {
                $(element).parents(".col-md-8").addClass("has-success").removeClass("has-error");
                $(element).next("span").addClass("glyphicon-ok").removeClass("glyphicon-remove");
            },
            submitHandler: function(form) {
                var nom = $("#nom").val().toUpperCase();
                $("#nom").val(nom);
                var prenom = $("#prenom").val();
                prenom = prenom.replace(/^\s+/g, '').replace(/\s+$/g, '');
                prenom = prenom.replace(/\s+/g, '-');
                prenom = prenom.substr(0, 1).toUpperCase() + prenom.substr(1);
                $("#prenom").val(prenom);
                form.submit();
            }
        });
        $("#maj_photo").validate({
            rules: {
                MaPhoto: {
                    required: true,
                    extension: "jpg|jpeg"
                }
            },
            messages: {
                MaPhoto: {
                    required: "La photo est obligatoire",
                    extension: "L'image doit être au format JPEG"
                },
            }
        });
    });
</script>
<?php
print("<title>Base " . SIGLE_ASSO . ": Vos informations personnelles</title>\n");
print('</head>');

/**
 * Affiche de la table d'édition
 * @param object $pconnexionBD
 * @param object $padherent objet adherent 
 * @param integer $pi_idf_adh identifiant de l'adhérent
 * @global array $ga_droits tableau des droits possibles classés par identifiant
 * @global array $ga_pays liste des pays
 * @global integer $gi_max_taille_upload taille maximale d'upload  
 * @global string $gst_rep_trombinoscope Répertoire du trombinoscope 
 * @global string $gst_url_trombinoscope Url du trombinoscope 
 */
function menu_edition_adherent($pconnexionBD, $padherent, $pi_idf_adh)
{
    global $ga_pays, $gi_max_taille_upload, $gst_rep_trombinoscope, $gst_url_trombinoscope;
    print("<form   id=\"maj_infos_adherent\" method=\"post\" class=\"form-horizontal\">\n");
    print("<input type=hidden name=mode value=MODIFIER>\n");
    print('<div class="row col-md-12">');

    print('<div class="col-md-6">');
    print($padherent->formulaire_infos_personnelles(false));
    print("</div>");

    print('<div class="col-md-6">');
    print($padherent->formulaire_aides_possibles());
    print($padherent->formulaire_origine());
    if (file_exists("$gst_rep_trombinoscope/$pi_idf_adh.jpg")) {
        print("<img src=\"$gst_url_trombinoscope/$pi_idf_adh.jpg\" width=115 height=132 alt=\"MaPhoto\" id=\"photo_adht\">");
    }
    print("</div></div>");

    print('<div class="row">');
    print('<button type=submit class="btn btn-primary col-md-offset-4 col-md-4"><span class="glyphicon glyphicon-save"></span> Modifier toutes vos informations</button>');
    print("</div>");
    print('</form>');

    print("<form enctype=\"multipart/form-data\" id=\"maj_photo\"  method=\"post\" class=\"form-horizontal\">");
    print("<input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"$gi_max_taille_upload\" >");
    print('<input type="hidden" name="mode" value="CHARGEMENT_PHOTO">');
    print('<div class="row">');
    print('<label for="MaPhoto" class="custom-file-label col-form-label col-md-2 col-md-offset-2">Photo au format JPEG</label>');
    print('<div class="col-md-3">');
    print('<input name="MaPhoto" id="MaPhoto" type="file" class="custom-file-input">');
    print('</div>');
    print('<div class="col-md-3">');
    print('<button type=submit class="btn btn-primary"><span class="glyphicon glyphicon-upload"></span> Charger la photo</button>');
    print('</div>');
    print('</div>');
    print('</form>');

    print("<form   method=\"post\">");
    print('<button type=submit class="btn btn-warning col-md-offset-4 col-md-4"><span class="glyphicon glyphicon-trash"></span> Supprimer la photo</button>');
    print('<input type="hidden" name="mode" value="SUPPRIMER_PHOTO">');
    print('</form>');
}

/**
 * Charge la photo de l'adhérent sur le site
 * @param integer $pi_idf_adh identifiant de l'adhérent 
 */
function maj_photo($pi_idf_adh)
{
    global $gst_rep_trombinoscope;
    if ($_FILES['MaPhoto']['type'] == "image/jpeg") {
        if (!move_uploaded_file($_FILES['MaPhoto']['tmp_name'], "$gst_rep_trombinoscope/$pi_idf_adh.jpg")) {
            print("<div class=\"alert alert-danger\">Erreur de t&eacute;l&eacute;chargement :</div><br>");
            switch ($_FILES['Variantes']['error']) {
                case 2:
                    print("Fichier trop gros par rapport � MAX_FILE_SIZE");
                    break;
                default:
                    print("Erreur inconnue");
                    print_r($_FILES);
            }
            exit;
        }
    } else
        print("<div class=\"alert alert-danger\">Type d'image " . $_FILES['MaPhoto']['type'] . " non accept&eacute;</div>");
}

print('<body>');
print('<div class="container">');

if (!isset($_SESSION['ident']))
    die("<div class=\"alert alert-danger\">Identifiant non reconnu</div>");
$gst_ident = $_SESSION['ident'];

require_once __DIR__ . '/Commun/menu.php';

switch ($gst_mode) {
    case 'MENU_MODIFIER':
        menu_edition_adherent($connexionBD, $adherent, $i_idf_adht_connecte);
        break;
    case 'MODIFIER':
        $adherent->initialise_depuis_formulaire();
        try {
            $adherent->modifie_infos_personnelles();
        } catch (Exception $e) {
            echo 'Exception reçue : ',  $e->getMessage(), "\n";
        }
        menu_edition_adherent($connexionBD, $adherent, $i_idf_adht_connecte);
        break;
    case 'CHARGEMENT_PHOTO':
        maj_photo($i_idf_adht_connecte);
        menu_edition_adherent($connexionBD, $adherent, $i_idf_adht_connecte);
        break;
    case 'SUPPRIMER_PHOTO':
        unlink("$gst_rep_trombinoscope/$i_idf_adht_connecte.jpg");
        menu_edition_adherent($connexionBD, $adherent, $i_idf_adht_connecte);
        break;
}

print("</div>");
print('</body></html>');
