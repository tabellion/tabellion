<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------
//http://127.0.0.1:8888/Gestion_Chantiers.php
$gst_chemin = "../";

require_once __DIR__ . '/../Commun/Identification.php';
require_once __DIR__ . '/../Commun/config.php';
require_once __DIR__ . '/../Commun/constantes.php';
require_once __DIR__ . '/../Commun/VerificationDroits.php';
verifie_privilege(DROIT_RELEVES);
require_once __DIR__ . '/../Commun/ConnexionBD.php';
require_once __DIR__ . '/../Commun/PaginationTableau.php';
require_once __DIR__ . '/../Commun/commun.php';

$connexionBD = ConnexionBD::singleton($gst_serveur_bd, $gst_utilisateur_bd, $gst_mdp_utilisateur_bd, $gst_nom_bd);

$gst_mode = empty($_POST['mode']) ? 'LISTE' : $_POST['mode'];

switch ($gst_mode) {
    case 'EXPORT':
        header("Content-type: text/csv");
        header("Expires: 0");
        header("Pragma: public");
        header('Content-disposition: attachment; filename="Releves' . SIGLE_ASSO . '.csv"');
        $i_idf_statut_export =  isset($_POST['idf_statut_export']) ? $_POST['idf_statut_export'] : 0;
        exporte_liste_releves($connexionBD, $i_idf_statut_export);
        exit();
        break;
}
print('<!DOCTYPE html>');
print("<head>");
print('<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />');
print('<meta http-equiv="content-language" content="fr" /> ');
print('<meta name="viewport" content="width=device-width, initial-scale=1.0">');
print("<link href='../assets/css/styles.css' type='text/css' rel='stylesheet'>");
print("<link href='../assets/css/bootstrap.min.css' rel='stylesheet'>");
print("<link href='../assets/css/jquery-ui.css' type='text/css' rel='stylesheet'>");
print("<link href='../assets/css/jquery-ui.structure.min.css' type='text/css' rel='stylesheet'>");
print("<link href='../assets/css/jquery-ui.theme.min.css' type='text/css' rel='stylesheet'> ");
print("<link href='../assets/css/select2.min.css' type='text/css' rel='stylesheet'>");
print("<link href='../assets/css/select2-bootstrap.min.css' type='text/css' rel='stylesheet'>");
print("<script src='../assets/js/jquery-min.js' type='text/javascript'></script>");
print("<script src='../assets/js/jquery.validate.min.js' type='text/javascript'></script>");
print("<script src='../assets/js/additional-methods.min.js' type='text/javascript'></script>");
print("<script src='../assets/js/bootstrap.min.js' type='text/javascript'></script>");
print("<script src='../assets/js/select2.min.js' type='text/javascript'></script>");
?>
<script type='text/javascript'>
    $(document).ready(function() {
        $.fn.select2.defaults.set("theme", "bootstrap");

        $(".js-select-avec-recherche").select2();

        $('#idf_statut_visu').change(
            function() {
                $(this).closest('form').trigger('submit');
            });

        $('#idf_releveur').change(
            function() {
                $(this).closest('form').trigger('submit');
            });

        $('#annuler').click(function() {
            window.location.href = '<?php echo basename(__FILE__) ?>';
        });

        $('#aujourdhui').click(function() {
            var d = new Date();
            var jour = d.getDate();
            var mois = d.getMonth() + 1;
            var annee = d.getFullYear();
            if (jour < 10)
                jour = '0' + jour;
            if (mois < 10)
                mois = '0' + mois;
            $('#date_retour').val(jour + '/' + mois + '/' + annee);
        });

        $("#suppression_chantiers").validate({
            rules: {
                "supp[]": {
                    required: true,
                    minlength: 1
                }
            },
            messages: {
                "supp[]": "Merci de choisir au moins un chantier à supprimer"
            },
            errorElement: "em",
            errorPlacement: function(error, element) {
                // Add the `help-block` class to the error element
                error.addClass("help-block");

                if (element.prop("type") === "checkbox") {
                    error.insertAfter(element.parent("label"));
                } else {
                    error.insertAfter(element);
                }
            },
            highlight: function(element, errorClass, validClass) {
                $(element).parents(".lib_erreur").addClass("has-error").removeClass("has-success");
            },
            unhighlight: function(element, errorClass, validClass) {
                $(element).parents(".lib_erreur").addClass("has-success").removeClass("has-error");
            },
            submitHandler: function(form) {
                var chantiers = '';
                $("input:checkbox").each(function() {
                    var $this = $(this);
                    if ($this.is(":checked")) {
                        chantiers = chantiers + ' ' + $this.attr("id");
                    }
                });
                if (confirm('Etes-vous sûr de supprimer les chantiers ' + chantiers + ' ?')) {
                    form.submit();
                }
            }
        });

        jQuery.validator.addMethod(
            "dateITA",
            function(value, element) {
                var check = false;
                var re = /^\d{1,2}\/\d{1,2}\/\d{4}$/;
                if (re.test(value)) {
                    var adata = value.split('/');
                    var gg = parseInt(adata[0], 10);
                    var mm = parseInt(adata[1], 10);
                    var aaaa = parseInt(adata[2], 10);
                    var xdata = new Date(aaaa, mm - 1, gg);
                    if ((xdata.getFullYear() == aaaa) &&
                        (xdata.getMonth() == mm - 1) &&
                        (xdata.getDate() == gg))
                        check = true;
                    else
                        check = false;
                } else
                    check = false;
                return this.optional(element) || check;
            },
            "SVP, entrez une date correcte"
        );

        $("#edition_chantiers").validate({
            rules: {
                date_convention: {
                    "required": true,
                    "dateITA": true
                },
                date_envoi: {
                    "required": true,
                    "dateITA": true
                },
                date_retour: {
                    "dateITA": true
                },
                date_fin: {
                    "dateITA": true
                }
            },
            messages: {
                date_convention: {
                    required: "La date de la convention est obligatoire",
                    dateITA: "La date de la convention doit être de la forme : JJ/MM/AAAA"
                },
                date_envoi: {
                    required: "La date d'envoi est obligatoire",
                    dateITA: "La date d'envoi doit être de la forme : JJ/MM/AAAA"
                },
                date_retour: {
                    dateITA: "La date de retour doit être de la forme : JJ/MM/AAAA"
                },
                date_fin: {
                    dateITA: "La date de fin doit être de la forme : JJ/MM/AAAA"
                }
            },
            errorElement: "em",
            errorPlacement: function(error, element) {
                // Add the `help-block` class to the error element
                error.addClass("help-block");

                // Add `has-feedback` class to the parent div.form-group
                // in order to add icons to inputs
                element.parents(".col-md-10").addClass("has-feedback");

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
                $(element).parents(".col-md-10").addClass("has-error").removeClass("has-success");
                $(element).next("span").addClass("glyphicon-remove").removeClass("glyphicon-ok");
            },
            unhighlight: function(element, errorClass, validClass) {
                $(element).parents(".col-md-10").addClass("has-success").removeClass("has-error");
                $(element).next("span").addClass("glyphicon-ok").removeClass("glyphicon-remove");
            }
        });

    });
</script>
<?php
print('</head>');
print('<body>');
print('<div class="container">');
if (isset($_GET['mod'])) {
    if ($gst_mode != 'MODIFIER')
        $gst_mode = 'MENU_MODIFIER';
    $gi_idf_chantier = (int) $_GET['mod'];
} else
    $gi_idf_chantier = isset($_POST['idf_chantier']) ? (int) $_POST['idf_chantier'] : 0;
$i_session_num_page = isset($_SESSION['num_page_chantiers']) ? $_SESSION['num_page_chantiers'] : 1;
$gi_num_page_cour = empty($_GET['num_page']) ? $i_session_num_page : $_GET['num_page'];
$i_session_idf_statut = isset($_SESSION['idf_statut_session']) ? $_SESSION['idf_statut_session'] : 1;
$i_get_idf_statut = isset($_GET['idf_statut_visu']) ? (int) $_GET['idf_statut_visu'] : $i_session_idf_statut;
$gi_idf_statut = isset($_POST['idf_statut_visu']) ?  (int) $_POST['idf_statut_visu'] : $i_get_idf_statut;
$i_session_idf_releveur = isset($_SESSION['idf_releveur_session']) ? $_SESSION['idf_releveur_session'] : 0;
$gi_idf_releveur = isset($_POST['idf_releveur']) ?  (int) $_POST['idf_releveur'] : $i_session_idf_releveur;
$_SESSION['idf_statut_session'] = $gi_idf_statut;
$_SESSION['idf_releveur_session'] = $gi_idf_releveur;

/**
 * Affiche la liste des communes
 * @param object $rconnexionBD
 * @param integer $pi_idf_statut identifiant du statut à visualiser
 * @param integer $pi_idf_releveur_visu identifiant du releveur à visualiser 
 */
function menu_liste($rconnexionBD, $pi_idf_statut_visu, $pi_idf_releveur_visu)
{
    global $gi_num_page_cour, $ga_tbl_statut;
    print("<form   method=\"post\" >");
    print('<div class="form-group row col-md-12">');
    print('<label for=idf_statut_visu class="col-form-label col-md-2 col-md-offset-2">Statut:</label>');
    print('<div class="col-md-4">');
    print('<select name=idf_statut_visu id=idf_statut_visu class="form-control">');
    $_SESSION['num_page_chantiers'] = $gi_num_page_cour;
    foreach ($ga_tbl_statut as $i_index => $st_valeur) {
        if ($i_index == $pi_idf_statut_visu)
            print("<option value=\"$i_index\" selected>$st_valeur</option>");
        else
            print("<option value=\"$i_index\">$st_valeur</option>");
    }
    print('</select>');
    print('</div>');
    print('</div>'); // fin ligne

    $st_requete = empty($pi_idf_statut_visu) ? "select adht.idf,concat(adht.prenom,' ',adht.nom,' (',adht.idf,')') from adherent adht join chantiers c on (adht.idf=c.id_releveur) order by adht.prenom,adht.nom" : "select adht.idf,concat(adht.prenom,' ',adht.nom,' (',adht.idf,')') from adherent adht join chantiers c on (adht.idf=c.id_releveur) where c.statut=$pi_idf_statut_visu order by adht.prenom,adht.nom";
    $a_releveurs = $rconnexionBD->liste_valeur_par_clef($st_requete);

    print('<div class="form-group row col-md-12">');
    print('<label for=idf_releveur class="col-form-label col-md-2 col-md-offset-2">Releveur:</label>');
    print('<div class="col-md-4">');
    print('<select name=idf_releveur id=idf_releveur class="form-control js-select-avec-recherche"><option value="0" selected>Tous</option>');
    foreach ($a_releveurs as $i_idf_releveur => $st_releveur) {
        if ($i_idf_releveur == $pi_idf_releveur_visu)
            print("<option value=\"$i_idf_releveur\" selected>" . cp1252_vers_utf8($st_releveur) . "</option>");
        else
            print("<option value=\"$i_idf_releveur\">" . cp1252_vers_utf8($st_releveur) . "</option>");
    }
    print('</select>');
    print('</div></div>'); //fin ligne

    $st_requete =  "select count(distinct id_releveur ) from `chantiers` ch";
    $a_clauses = array();
    if (!empty($pi_idf_statut_visu))
        $a_clauses[] = "ch.statut=$pi_idf_statut_visu";
    if (!empty($pi_idf_releveur_visu))
        $a_clauses[] = "ch.id_releveur=$pi_idf_releveur_visu";
    if (count($a_clauses) > 0) {
        $st_clauses = join(' and ', $a_clauses);
        $st_requete .= " where $st_clauses";
    }
    $i_nb_reveleurs = $rconnexionBD->sql_select1($st_requete);
    print("<div class=\"form form-group col-md-12\"><div class=\"info text-center\"><div class=\"badge\">$i_nb_reveleurs</div> Releveurs distincts</div></div>");

    // Affichage des initiales
    $a_clauses = array();
    if (!empty($pi_idf_statut_visu))
        $a_clauses[] = "ch.statut=$pi_idf_statut_visu";
    if (!empty($pi_idf_releveur_visu))
        $a_clauses[] = "ch.id_releveur=$pi_idf_releveur_visu";
    $st_requete =  "SELECT DISTINCT (left( ca.nom, 1 )) AS init from `chantiers` ch join `documents` r on (ch.id_document = r.idf) join `commune_acte` ca  on (r.id_commune = ca.idf )";
    if (count($a_clauses) > 0) {
        $st_clauses = join(' and ', $a_clauses);
        $st_requete .= " where $st_clauses";
    }
    $st_requete .= " ORDER BY init";
    $a_initiales_communes = $rconnexionBD->sql_select($st_requete);
    if (count($a_initiales_communes) > 0) {
        $i_session_initiale = isset($_SESSION['initiale_statcom']) ? $_SESSION['initiale_statcom'] : $a_initiales_communes[0];
        $gc_initiale = empty($_GET['initiale_statcom']) ? $i_session_initiale : $_GET['initiale_statcom'];
        if (!in_array(utf8_vers_cp1252($gc_initiale), $a_initiales_communes))
            $gc_initiale = $a_initiales_communes[0];
        $_SESSION['initiale_statcom'] = $gc_initiale;
        print('<div class="text-center"><ul class="pagination">');
        foreach ($a_initiales_communes as $c_initiale) {
            if ($c_initiale == utf8_vers_cp1252($gc_initiale))
                print("<li class=\"page-item active\"><span class=\"page-link\">" . cp1252_vers_utf8($c_initiale) . "<span class=\"sr-only\">(current)</span></span></li>");
            else
                print("<li class=\"page-item\"><a href=\"" . basename(__FILE__) . "?initiale_statcom=" . cp1252_vers_utf8($c_initiale) . "&idf_statut_visu=$pi_idf_statut_visu\" class=\"page-item\">" . cp1252_vers_utf8($c_initiale) . "</a></li>");
        }
        print("</ul></div>");
        $st_requete = "select ch.idf, ca.nom, r.fourchette, (select case r.support when 1 then 'Acte authentique' when 2 then 'Photo' when 3 then 'Relev&eacute; papier' end), concat(ad.nom,'  ',ad.prenom,' (',ad.idf,')') from `chantiers` ch join `documents` r on (ch.id_document = r.idf) join `commune_acte` ca  on (r.id_commune = ca.idf ) join `adherent` ad on (ch.id_releveur = ad.idf) where ca.nom like '" . utf8_vers_cp1252($gc_initiale) . "%'";
        $a_clauses = array();
        if (!empty($pi_idf_statut_visu))
            $a_clauses[] = "ch.statut=$pi_idf_statut_visu";
        if (!empty($pi_idf_releveur_visu))
            $a_clauses[] = "ch.id_releveur=$pi_idf_releveur_visu";
        if (count($a_clauses) > 0) {
            $st_clauses = join(' and ', $a_clauses);
            $st_requete .= " and  $st_clauses";
        }
        $st_requete .= " order by ca.nom, ad.nom";
        $a_liste_chantiers = $rconnexionBD->liste_valeur_par_clef($st_requete);
        print("</form><form   method=\"post\" id=\"suppression_chantiers\">");
        $i_nb_chantiers = count($a_liste_chantiers);

        $pagination = new PaginationTableau(basename(__FILE__), 'num_page', $i_nb_chantiers, NB_LIGNES_PAR_PAGE, DELTA_NAVIGATION, array('Commune', 'Fourchette', 'Support', 'Releveur', 'Modifier', 'Supprimer'));
        $pagination->init_param_bd($rconnexionBD, $st_requete);
        $pagination->init_page_cour($gi_num_page_cour);
        $pagination->affiche_entete_liens_navigation();
        $pagination->affiche_tableau_edition(basename(__FILE__));
        $pagination->affiche_entete_liens_navigation();
    } else
        print('<div class="row col-md-12"><div class="alert alert-danger">Pas de chantiers</div></div>');
    print("<input type=hidden name=mode value=SUPPRIMER>");
    print('<button type=submit class="btn btn-danger col-md-4 col-md-offset-4"><span class="glyphicon glyphicon-trash"></span> Supprimer les chantiers s&eacute;lectionn&eacute;es</button>');
    print("</form>");
    print("<form   method=\"post\">");
    print("<input type=hidden name=mode value=MENU_AJOUTER>");
    print('<button type=submit class="btn btn-primary col-md-4 col-md-offset-4"><span class="glyphicon glyphicon-plus"></span> Ajouter un chantier</button>');
    print('</form>');

    print("<form   method=\"post\">");
    print("<input type=hidden name=mode value=EXPORT>");
    print('<div class="form-group row col-md-12">');
    print('<div class="input-group col-md-6">');
    print('<button type=submit class="btn btn-primary col-md-12"><span class="glyphicon glyphicon-download-alt"></span> Exporter la liste des relev&eacute;s</button>');
    print('<span class="input-group-addon">avec le statut:</span>');
    print('<label for="idf_statut_export" class="sr-only">Statut export</label>');
    print('<select name=idf_statut_export id=idf_statut_export class="form-control">');
    foreach ($ga_tbl_statut as $i_index => $st_valeur) {
        print("<option value=\"$i_index\">$st_valeur</option>");
    }
    print("</select>");
    print('</div>');
    print('</div>');
    print("</form>");
}
/*
   Fonction de composition du document de chantier pour la liste déroulante
*/
function chaine_select_options_chantier($pst_idf_choisi, $pa_tableau)
{
    $st_chaine_options = '';
    foreach ($pa_tableau as $st_idf => $st_val) {
        list($st_nom, $st_fourchette, $st_support) = $st_val;
        //$st_val = 'Commune : '.$st_nom.', Fourchette : '.$st_fourchette.',  '.$st_support;
        $st_val = $st_nom . ', Fourchette : ' . $st_fourchette . ',  ' . $st_support;
        $st_chaine_options .= ("$pst_idf_choisi" != '' && "$st_idf" == "$pst_idf_choisi") ? "<option value=\"$st_idf\" selected=\"selected\">" . cp1252_vers_utf8($st_val) . "</option>\n" : "<option value=\"$st_idf\">" . cp1252_vers_utf8($st_val) . "</option>\n";
    }
    return $st_chaine_options;
}
/**
 * Affiche de la table d'édition
 * @param integer $pi_id_document identifiant du registre
 * @param integer $pi_id_releveur identifiant de l'adherent
 * @param integer $pi_type_acte identifiant des types d'acte
 * @param string $pst_convention date de la convention
 * @param string $pst_envoi date d'envoi du chantier
 * @param string $pst_retour date de retour du chantier
 * @param string $pst_fin date de fin 
 * @param string $pst_comment_envoi commentaires envoi
 * @param string $pst_comment_retour commentaires retour
 * @param integer $pi_statut statut du chantier
 * @param array $pa_documents liste des documents
 * @param array $pa_adherents liste des releveurs (adhérents) 
 */
function menu_edition($pi_id_document, $pi_id_releveur, $pi_type_acte, $pst_convention, $pst_envoi, $pst_retour, $pst_fin, $pst_comment_envoi, $pst_comment_retour, $pi_statut, $pa_documents, $pa_adherents)
{
    global $ga_tbl_statut;
    print('<div class="form-group row">');
    print('<label for="id_document" class="col-form-label col-md-2">Document</label>');
    print('<div class="col-md-10">');
    print("<select name=id_document id=id_document class=\"js-select-avec-recherche form-control\">" . chaine_select_options_chantier($pi_id_document, $pa_documents) . "</select>");
    print('</div>');
    print('</div>');

    print('<div class="form-group row">');
    print('<label for="id_releveur" class="col-form-label col-md-2">Releveur</label>');
    print('<div class="col-md-10">');
    print("<select name=id_releveur id=id_releveur class=\"js-select-avec-recherche form-control\">" . chaine_select_options($pi_id_releveur, $pa_adherents) . "</select>");
    print('</div>');
    print('</div>');

    print('<div class="form-group row">');
    print('<label for="type_acte[]" class="col-form-label col-md-2">Type d\'acte</label>');
    print('<div class="col-md-10">');
    print('<div class="form-check">');
    $st_checked = $pi_type_acte & 1 ? 'checked' : '';
    print("<input type=checkbox name=type_acte[] id=type_bapteme value=\"B\" $st_checked class=\"form-check-input\">");
    print('<label class="form-check-label" for="type_bapteme">Bapt&ecirc;me</label>');
    print('</div>');
    print('<div class="form-check">');
    $st_checked = $pi_type_acte & 2 ? 'checked' : '';
    print("<input type=checkbox name=type_acte[] id=type_mariage value=\"M\" $st_checked class=\"form-check-input\">");
    print('<label class="form-check-label" for="type_mariage">Mariage</label>');
    print('</div>');
    print('<div class="form-check">');
    $st_checked = $pi_type_acte & 4 ? 'checked' : '';
    print("<input type=checkbox name=type_acte[] id=type_sepulture value=\"S\" $st_checked class=\"form-check-input\">");
    print('<label class="form-check-label" for="type_sepulture">S&eacute;pulture</label>');
    print('</div>');
    print('<div class="form-check">');
    $st_checked = $pi_type_acte & 8 ? 'checked' : '';
    print("<input type=checkbox name=type_acte[] id=type_divers value=\"V\" $st_checked class=\"form-check-input\">");
    print('<label class="form-check-label" for="type_divers">Divers</label>');
    print('</div>');
    print('</div>');
    print('</div>');

    $pst_convention = ($pst_convention != '00/00/0000') ? $pst_convention : '';
    print('<div class="form-group row">');
    print('<label for="date_convention" class="col-form-label col-md-2">Date convention (jj/mm/aaaa)</label>');
    print('<div class="col-md-10">');
    print("<input type=\"text\" name=date_convention id=date_convention value=\"$pst_convention\" size=10 maxsize=10 class=\"form-control\">");
    print('</div>');
    print('</div>');

    $pst_envoi = ($pst_envoi != '00/00/0000') ? $pst_envoi : '';
    print('<div class="form-group row">');
    print('<label for="date_envoi" class="col-form-label col-md-2">Date envoi du chantier (jj/mm/aaaa)</label>');
    print('<div class="col-md-10">');
    print("<input type=\"text\" name=date_envoi id=date_envoi value=\"$pst_envoi\" size=10 maxsize=10 class=\"form-control\">");
    print('</div>');
    print('</div>');

    $pst_retour = ($pst_retour != '00/00/0000') ? $pst_retour : '';
    print('<div class="form-group row">');
    print('<label for="date_retour" class="col-form-label col-md-2">Date retour du chantier (jj/mm/aaaa)</label>');
    print('<div class="col-md-10">');
    print('<div class="input-group">');
    print("<input type=\"text\" name=date_retour  id=date_retour value=\"$pst_retour\" size=10 maxsize=10 class=\"form-control\">");
    print("<span class=\"input-group-btn\"><button type=\"button\" id=aujourdhui class=\"btn btn-primary\"><span class=\"glyphicon glyphicon-calendar\"></span> Aujourd'hui</button></span>");
    print('</div>');
    print('</div>');
    print('</div>');

    $pst_fin = ($pst_fin != '00/00/0000') ? $pst_fin : '';
    print('<div class="form-group row">');
    print('<label for="date_fin" class="col-form-label col-md-2">Date fin du chantier (jj/mm/aaaa)</label>');
    print('<div class="col-md-10">');
    print("<input type=\"text\" name=date_fin value=\"$pst_fin\" size=10 maxsize=10 class=\"form-control\">");
    print('</div>');
    print('</div>');

    print('<div class="form-group row">');
    print('<label for="comment_envoi" class="col-form-label col-md-2">Commentaires envoi</label>');
    print('<div class="col-md-10">');
    print("<textarea name=comment_envoi id=comment_envoi cols=40 rows=10 class=\"form-control\">" . $pst_comment_envoi . "</textarea>");
    print('</div>');
    print('</div>');

    print('<div class="form-group row">');
    print('<label for="comment_retour" class="col-form-label col-md-2">Commentaires retour</label>');
    print('<div class="col-md-10">');
    print("<textarea name=comment_retour id=comment_retour  cols=40 rows=10 class=\"form-control\">" . $pst_comment_retour . "</textarea>");
    print('</div>');
    print('</div>');

    print('<div class="form-group row">');
    print('<label for="statut" class="col-form-label col-md-2">Statut du Chantier</label>');
    print('<div class="col-md-10">');
    print("<select name=statut id=statut class=\"form-control\">");
    foreach ($ga_tbl_statut as $i_index => $st_valeur) {
        if ($pi_statut == $i_index)
            print("<option value=\"$i_index\" selected=\"selected\">$st_valeur</option>");
        else
            print("<option value=\"$i_index\">$st_valeur</option>");
    }
    print("</select>");
    print('</div>');
    print('</div>');
}

/** Affiche le menu de modification des chantiers
 * @param object $rconnexionBD Identifiant de la connexion de base
 * @param integer $pi_idf_chantier Identifiant du chantier
 * @param array $pa_documents Liste des documents
 * @param array $pa_adherents Liste des adhérents (releveur)
 */
function menu_modifier($rconnexionBD, $pi_idf_chantier, $pa_documents, $pa_adherents)
{
    $st_requete = "select `id_document`,`id_releveur`,`type_acte`,DATE_FORMAT(`date_convention`,'%d/%m/%Y'),DATE_FORMAT(`date_envoi`,'%d/%m/%Y'),DATE_FORMAT(`date_retour`,'%d/%m/%Y'),DATE_FORMAT(`date_fin`,'%d/%m/%Y'), `comment_envoi`, `comment_retour`, `statut` from `chantiers` where idf=$pi_idf_chantier";
    list($i_id_document, $i_id_releveur, $i_type_acte, $st_convention, $st_envoi, $st_retour, $st_fin, $st_comment_envoi, $st_comment_retour, $i_statut) = $rconnexionBD->sql_select_liste($st_requete);
    print("<form   method=\"post\" id=\"edition_chantiers\">");
    print("<input type=hidden name=mode value=MODIFIER>");
    print("<input type=hidden name=idf_chantier value=$pi_idf_chantier>");

    menu_edition($i_id_document, $i_id_releveur, $i_type_acte, $st_convention, $st_envoi, $st_retour, $st_fin, cp1252_vers_utf8($st_comment_envoi), cp1252_vers_utf8($st_comment_retour), $i_statut, $pa_documents, $pa_adherents);
    print('<div class="btn-group col-md-4 col-md-offset-4" role="group">');
    print('<button type=submit class="btn btn-primary"><span class="glyphicon glyphicon-ok"></span> Modifier</button>');
    print('<button type=button id=annuler class="btn btn-primary"><span class="glyphicon glyphicon-remove"> Annuler</button>');
    print('</div>');
    print('</form>');
}

/** Affiche le menu d'ajout d'un chantier
 * @param array $pa_documents Liste des documents
 * @param array $pa_adherents Liste des adhérents (releveur)
 */
function menu_ajouter($pa_documents, $pa_adherents)
{
    print("<form   method=\"post\" id=\"edition_chantiers\">");
    print("<input type=hidden name=mode value=AJOUTER>");
    menu_edition(0, 0, 0, '', '', '', '', '', '', 1, $pa_documents, $pa_adherents);
    print('<div class="btn-group col-md-4 col-md-offset-4" role="group">');
    print('<button type=submit class="btn btn-primary"><span class="glyphicon glyphicon-ok"></span>  Ajouter</button>');
    print('<button type=button id=annuler class="btn btn-primary"><span class="glyphicon glyphicon-remove"> Annuler</button>');
    print('</div>');
    print('</form>');
}

/** Export la liste des relevés au format donnée
 * @param object $pconnexionBD Identifiant de la connexion de base
 * @param integer $pi_idf_stat_export identifiant du statut de l'export 
 */
function exporte_liste_releves($pconnexionBD, $pi_idf_stat_export)
{
    global $ga_tbl_statut;
    $ga_tbl_nature = array(0 => '', 1 => 'RPX catholiques', 2 => 'RPX protestants', 3 => 'Etat civil', 4 => 'Actes Notari&eacute; ');
    $st_requete = empty($pi_idf_stat_export) ? "select ca.nom,d.fourchette,d.nature_acte,concat(r.nom, ' ',r.prenom),DATE_FORMAT(c.date_retour,'%d/%m/%Y'),c.statut from chantiers c join documents d on (c.id_document=d.idf) join commune_acte ca on (d.id_commune=ca.idf) join releveur r on (c.id_releveur=r.idf_adherent) order by ca.nom" : "select ca.nom,d.fourchette,d.nature_acte,concat(r.nom, ' ',r.prenom),DATE_FORMAT(c.date_retour,'%d/%m/%Y') from chantiers c join documents d on (c.id_document=d.idf) join commune_acte ca on (d.id_commune=ca.idf)join releveur r on (c.id_releveur=r.idf_adherent) where c.statut=$pi_idf_stat_export order by c.date_retour desc";
    //die($st_requete);
    $a_liste_releves = $pconnexionBD->sql_select_multiple($st_requete);
    $fh = @fopen('php://output', 'w');
    if (count($a_liste_releves) > 0) {
        fputcsv($fh, array("Commune", "Fourchette", "Nature", "Releveur", "Dernière MAJ"));
        foreach ($a_liste_releves as $a_ligne) {
            if (empty($pi_idf_stat_export)) {
                list($st_com, $st_fourchette, $i_nature, $st_releveur, $st_date_retour, $i_statut) = $a_ligne;
                // Put the data into the stream
                fputcsv($fh, array($st_com, $st_fourchette, $ga_tbl_nature[$i_nature], $st_releveur, $st_date_retour, $ga_tbl_statut[$i_statut]));
            } else {
                list($st_com, $st_fourchette, $i_nature, $st_releveur, $st_date_retour) = $a_ligne;
                fputcsv($fh, array($st_com, $st_fourchette, $ga_tbl_nature[$i_nature], $st_releveur, $st_date_retour));
            }
        }
        fclose($fh);
    }
}

/*---------------------------------------------------------------------------
  Démarrage du programme
  ---------------------------------------------------------------------------*/
$ga_tbl_statut = array(0 => 'Tous', 1 => 'En cours', 2 => 'Termin&eacute;', 3 => 'Abandonn&eacute;');
require_once __DIR__ . '/../Commun/menu.php';
$ga_documents = $connexionBD->sql_select_multiple_par_idf("select r.idf, ca.nom, r.fourchette, (select case r.support when 1 then 'Acte authentique' when 2 then 'Photo' when 3 then 'Relev&eacute; papier' end) from `documents` r  join `commune_acte` ca  on (r.id_commune = ca.idf ) order by ca.nom");
$ga_communes  = $connexionBD->liste_valeur_par_clef("select idf,nom from `commune_acte` order by nom");
$ga_adherent  = $connexionBD->liste_valeur_par_clef("select idf,concat(nom,'  ',prenom,' (',idf,')') from adherent where statut in ('" . ADHESION_INTERNET . "','" . ADHESION_BULLETIN . "','" . ADHESION_SUSPENDU . "') order by nom,prenom");
switch ($gst_mode) {
    case 'LISTE':
        menu_liste($connexionBD, $gi_idf_statut, $gi_idf_releveur);
        break;
    case 'MENU_MODIFIER':
        menu_modifier($connexionBD, $gi_idf_chantier, $ga_documents, $ga_adherent);
        break;

    case 'MODIFIER':
        $i_id_document = (int) $_POST['id_document'];
        $i_id_releveur = (int) $_POST['id_releveur'];
        $a_types_actes = $_POST['type_acte'];
        $i_type_acte = 0;
        if (in_array('B', $a_types_actes)) $i_type_acte = $i_type_acte | 1;
        if (in_array('M', $a_types_actes)) $i_type_acte = $i_type_acte | 2;
        if (in_array('S', $a_types_actes)) $i_type_acte = $i_type_acte | 4;
        if (in_array('V', $a_types_actes)) $i_type_acte = $i_type_acte | 8;
        list($i_jour, $i_mois, $i_annee) = explode('/', $_POST['date_convention'], 3);
        $c_date_convention = join('-', array($i_annee, $i_mois, $i_jour));
        list($i_jour, $i_mois, $i_annee) = explode('/', $_POST['date_envoi'], 3);
        $c_date_envoi = join('-', array($i_annee, $i_mois, $i_jour));
        $c_date_retour = '';
        if ($_POST['date_retour'] != '') {
            list($i_jour, $i_mois, $i_annee) = explode('/', $_POST['date_retour'], 3);
            $c_date_retour = join('-', array($i_annee, $i_mois, $i_jour));
        }
        $c_date_fin = null;
        if ($_POST['date_fin'] != '') {
            list($i_jour, $i_mois, $i_annee) = explode('/', $_POST['date_fin'], 3);
            $c_date_fin = join('-', array($i_annee, $i_mois, $i_jour));
        }

        $st_comment_envoi = trim($_POST['comment_envoi']);
        $st_comment_retour = trim($_POST['comment_retour']);
        $i_statut = (int) $_POST['statut'];
        $connexionBD->initialise_params(array(':comment_envoi' => utf8_vers_cp1252($st_comment_envoi), ':comment_retour' => utf8_vers_cp1252($st_comment_retour)));
        $connexionBD->execute_requete("update `chantiers` set id_document=$i_id_document, id_releveur=$i_id_releveur, type_acte=$i_type_acte, date_convention='$c_date_convention', date_envoi='$c_date_envoi', date_retour='$c_date_retour',date_fin='$c_date_fin', comment_envoi=:comment_envoi, comment_retour=:comment_retour, statut=$i_statut where idf=$gi_idf_chantier");
        menu_liste($connexionBD, $gi_idf_statut, $gi_idf_releveur);
        break;
    case 'MENU_AJOUTER':
        menu_ajouter($ga_documents, $ga_adherent, $gi_idf_releveur);
        break;
    case 'AJOUTER':
        $i_id_document = (int) $_POST['id_document'];
        $i_id_releveur = (int) $_POST['id_releveur'];
        $a_types_actes = $_POST['type_acte'];
        $i_type_acte = 0;
        $c_date_fin = '';
        if (in_array('B', $a_types_actes)) $i_type_acte = $i_type_acte | 1;
        if (in_array('M', $a_types_actes)) $i_type_acte = $i_type_acte | 2;
        if (in_array('S', $a_types_actes)) $i_type_acte = $i_type_acte | 4;
        if (in_array('V', $a_types_actes)) $i_type_acte = $i_type_acte | 8;
        list($i_jour, $i_mois, $i_annee) = explode('/', $_POST['date_convention'], 3);
        $c_date_convention = join('-', array($i_annee, $i_mois, $i_jour));
        list($i_jour, $i_mois, $i_annee) = explode('/', $_POST['date_envoi'], 3);
        $c_date_envoi = join('-', array($i_annee, $i_mois, $i_jour));
        $c_date_retour = '';
        if ($_POST['date_retour'] != '') {
            list($i_jour, $i_mois, $i_annee) = explode('/', $_POST['date_retour'], 3);
            $c_date_retour = join('-', array($i_annee, $i_mois, $i_jour));
        }
        if ($_POST['date_fin'] != '') {
            list($i_jour, $i_mois, $i_annee) = explode('/', $_POST['date_fin'], 3);
            $c_date_fin = join('-', array($i_annee, $i_mois, $i_jour));
        }
        $st_comment_envoi = trim($_POST['comment_envoi']);
        $st_comment_retour = trim($_POST['comment_retour']);
        $i_statut = (int) $_POST['statut'];
        $connexionBD->initialise_params(array(':comment_envoi' => utf8_vers_cp1252($st_comment_envoi), ':comment_retour' => utf8_vers_cp1252($st_comment_retour)));
        $st_requete = "insert into chantiers (id_document, id_releveur, type_acte, date_convention, date_envoi, date_retour, date_fin,comment_envoi, comment_retour, statut) values ($i_id_document, $i_id_releveur, $i_type_acte, '$c_date_convention', '$c_date_envoi', '$c_date_retour','$c_date_fin',:comment_envoi , :comment_retour, $i_statut)";
        $connexionBD->execute_requete($st_requete);
        menu_liste($connexionBD, $gi_idf_statut, $gi_idf_releveur);
        break;
    case 'SUPPRIMER':
        $a_liste_supprime = isset($_POST['supp']) ? $_POST['supp'] :  array();
        foreach ($a_liste_supprime as $i_idf_chantier) {
            $connexionBD->execute_requete("delete from `chantiers` where idf = $i_idf_chantier");
        }
        menu_liste($connexionBD, $$gi_idf_statut, $gi_idf_releveur);
        break;
}
print('</div></body>');
