<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------
//http://127.0.0.1:8888/Gestion_Documents.php

$gst_chemin = "../";

require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../Origin/PaginationTableau.php';

verifie_privilege(DROIT_RELEVES);

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
print("<script src='../assets/js/jquery-ui.min.js' type='text/javascript'></script>");
print("<script src='../assets/js/additional-methods.min.js' type='text/javascript'></script>");
print("<script src='../assets/js/bootstrap.min.js' type='text/javascript'></script>");
print("<script src='../assets/js/select2.min.js' type='text/javascript'></script>");

?>
<script type='text/javascript'>
    $(document).ready(function() {
        $.fn.select2.defaults.set("theme", "bootstrap");

        $(".js-select-avec-recherche").select2();

        $('#commune_a_chercher').autocomplete({
            source: function(request, response) {
                $.getJSON("../ajax/commune_acte.php", {
                    term: request.term,
                    idf_source: 0
                }, response);
            },
            minLength: 3
        });

        $('#annuler').click(function() {
            window.location.href = '<?php echo basename(__FILE__) ?>';
        });

        $("#suppression_documents").validate({
            rules: {
                "supp[]": {
                    required: true,
                    minlength: 1
                }
            },
            messages: {
                "supp[]": "Merci de choisir au moins un document à supprimer"
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
                var documents = '';
                $("input:checkbox").each(function() {
                    var $this = $(this);
                    if ($this.is(":checked")) {
                        documents = documents + ' ' + $this.attr("id");
                    }

                });
                if (confirm('Etes-vous sûr de supprimer les documents ' + documents + ' ?')) {
                    form.submit();
                }
            }
        });

        $("#edition_documents").validate({
            rules: {
                fourchette: "required",
                quantite: {
                    "integer": true
                }
            },
            messages: {
                fourchette: {
                    required: "La fourchette est obligatoire"
                },
                quantite: {
                    integer: "Le nombre de documents doit être un entier"
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

$gst_mode = empty($_POST['mode']) ? 'LISTE' : $_POST['mode'];
if (isset($_GET['mod'])) {
    if ($gst_mode != 'MODIFIER')
        $gst_mode = 'MENU_MODIFIER';
    $gi_idf = (int) $_GET['mod'];
} else
    $gi_idf_releve = isset($_POST['idf']) ? (int) $_POST['idf'] : 0;

$gi_num_page_cour = empty($_GET['num_page']) ? 1 : $_GET['num_page'];

/**
 * Affiche la liste des communes
 * @param object $pconnexionBD  connexion a la base de donnees
 * @param string $pst_commune_a_chercher commune a chercher 
 * @global array  $ga_tbl_support libelle des supports d'acte
 * @global array  $ga_tbl_collection libelle des collections d'acte 
 */
function menu_liste($pconnexionBD, $pst_commune_a_chercher)
{
    global $gi_num_page_cour;
    global $ga_tbl_support, $ga_tbl_nature, $ga_tbl_collection;
    $st_requete = "SELECT DISTINCT (left( ca.nom, 1 )) AS init FROM `commune_acte` ca join `documents` p on (p.id_commune=ca.idf) ORDER BY init";
    $a_initiales_communes = $pconnexionBD->sql_select($st_requete);

    if (count($a_initiales_communes) > 0) {
        if ($pst_commune_a_chercher == '') {
            $i_session_initiale = isset($_SESSION['initiale_com']) ? $_SESSION['initiale_com'] : $a_initiales_communes[0];
            $gc_initiale = empty($_GET['initiale_com']) ? $i_session_initiale : $_GET['initiale_com'];
        } else {
            $gc_initiale = strtoupper(substr($pst_commune_a_chercher, 0, 1));
            if ($gc_initiale == '*') $gc_initiale = $a_initiales_communes[0];
        }
        if (!in_array(utf8_vers_cp1252($gc_initiale), $a_initiales_communes))
            $gc_initiale = $a_initiales_communes[0];
        $_SESSION['initiale_com'] = $gc_initiale;
        print('<div class="text-center"><ul class="pagination">');
        foreach ($a_initiales_communes as $c_initiale) {
            if ($c_initiale == utf8_vers_cp1252($gc_initiale))
                print("<li class=\"page-item active\"><span class=\"page-link\">" . cp1252_vers_utf8($c_initiale) . "<span class=\"sr-only\">(current)</span></span></li>");
            else
                print("<li class=\"page-item\"><a href=\"" . basename(__FILE__) . "?initiale_com=" . cp1252_vers_utf8($c_initiale) . "\" class=\"page-item\">" . cp1252_vers_utf8($c_initiale) . "</a></li>");
        }
        print("</ul></div>");
    } else
        $gc_initiale = '%';

    $pst_commune_a_chercher = str_replace('*', '%', $pst_commune_a_chercher);
    $st_requete = ($pst_commune_a_chercher == '') ? "select p.idf,ca.nom,p.fourchette, p.nature_acte, p.support, p.collection from `documents` p join `commune_acte` ca on (p.id_commune=ca.idf ) where ca.nom like '" . utf8_vers_cp1252($gc_initiale) . "%' order by ca.nom,p.fourchette,p.support" : " select p.idf,ca.nom,p.fourchette, p.nature_acte, p.support, p.collection  from `documents` p join `commune_acte` ca on (p.id_commune=ca.idf ) where ca.nom like '" . utf8_vers_cp1252($pst_commune_a_chercher) . "' order by ca.nom,p.fourchette,p.support";
    print("<form   method=\"post\" >");
    print("<input type=hidden name=mode value=\"LISTE\">");
    print('<div class="form-row col-md-12">');
    print('<label for="commune_a_chercher"  class="col-form-label col-md-2">Commune:</label>');
    print('<div class="col-md-8">');
    print('<input name="commune_a_chercher" id="commune_a_chercher" value="" size="25" maxlength="25" type="text" class="form-control" aria-describedby="aideCom">');
    print('<small id="aideCom" class="form-text text-muted">Vous pouvez mettre le caract&egrave;re "*" pour chercher sur une racine (ex.: saint*)</small>');
    print('</div>');
    print('<button type=submit class="btn btn-primary"><span class="glyphicon glyphicon-search"></span> Chercher</button>');
    print('</div>');

    $a_liste_documents = $pconnexionBD->sql_select_multiple($st_requete);
    print("</form><form   method=\"post\" id=\"suppression_documents\">");
    $a_lignes = array();
    $i_nb_documents = count($a_liste_documents);
    if ($i_nb_documents != 0) {
        $pagination = new PaginationTableau(basename(__FILE__), 'num_page', $i_nb_documents, NB_LIGNES_PAR_PAGE, DELTA_NAVIGATION, array('Commune', 'Fourchette', 'Nature', 'Support', 'Collection', 'Modifier', 'Supprimer'));
        $pagination->init_param_bd($pconnexionBD, $st_requete);
        $pagination->init_page_cour($gi_num_page_cour);
        $pagination->affiche_entete_liens_navigation();
        $pagination->affiche_tableau_edition(basename(__FILE__));
        $pagination->affiche_entete_liens_navigation();
    } else
        print('<div class="row col-md-12 alert alert-danger">Pas de documents</div>');
    print('<button type=submit class="btn btn-danger col-md-4 col-md-offset-4"><span class="glyphicon glyphicon-trash"></span> Supprimer les documents s&eacute;lectionn&eacute;s</button>');
    print("</form>");
    print("<form   method=\"post\">");
    print("<input type=hidden name=mode value=MENU_AJOUTER>");
    print('<button type=submit class="btn btn-primary col-md-4 col-md-offset-4"><span class="glyphicon glyphicon-plus"></span>  Ajouter un document</button>');
    print('</form>');
}

/**
 * Affiche de la table d'édition
 * @param integer $pi_id_commune identifiant de la commune
 * @param integer $pi_type acte identifiant des types d'acte
 * @param integer $pi_nature_acte identifiant la nature de l'acte
 * @param string  $pst_fourchette Fourchette de la photo
 * @param integer $pi_support identifiant de la support de l'acte
 * @param integer $pi_collection identifiant de la collection
 * @param integer $pi_quantite nombres de documents
 * @param string  $pst_auteur auteur de documents 
 * @param array   $pa_communes liste des communes
 * @global array  $ga_tbl_support libelle des supports d'acte
 * @global array  $ga_tbl_collection libelle des collections d'acte
 */
function menu_edition($pi_id_commune, $pi_type_acte, $pi_nature_acte, $pst_fourchette, $pi_support, $pi_collection, $pi_quantite, $pst_auteur, $pa_communes)
{
    global $ga_tbl_support, $ga_tbl_nature, $ga_tbl_collection;
    print('<div class="form-group row">');
    print('<label for="id_commune" class="col-form-label col-md-2">Commune</label>');
    print('<div class="col-md-10">');
    print("<select name=id_commune class=\"js-select-avec-recherche form-control\">" . chaine_select_options($pi_id_commune, $pa_communes) . "</select>");
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
    print("<input type=checkbox name=type_acte[] id= type_divers value=\"V\" $st_checked class=\"form-check-input\">");
    print('<label class="form-check-label" for="type_divers">Divers</label>');
    print('</div>');
    print('</div>');
    print('</div>');
    print('<div class="form-group row">');
    print('<label for="fourchette" class="col-form-label col-md-2">Fourchette (aaaa-aaaa)</label>');
    print('<div class="col-md-10">');
    print("<input type=\"text\" maxsize=20 size=9 name=fourchette id=fourchette value=\"$pst_fourchette\" class=\"form-control\">");
    print('</div>');
    print('</div>');

    print('<div class="form-group row">');
    print('<label for="type_nature" class="col-form-label col-md-2">Nature de l\'acte</label>');
    print('<div class="col-md-10">');
    print("<select name=type_nature id=type_nature class=\"form-control\">");
    foreach ($ga_tbl_nature as $i_index => $st_valeur) {
        if ($pi_nature_acte == $i_index)
            print("<option value=\"$i_index\" selected=\"selected\">$st_valeur</option>");
        else
            print("<option value=\"$i_index\">$st_valeur</option>");
    }
    print("</select>");
    print('</div>');
    print('</div>');

    print('<div class="form-group row">');
    print('<label for="type_support" class="col-form-label col-md-2">support</label>');
    print('<div class="col-md-10">');
    print("<select name=type_support id=type_support class=\"form-control\">");
    foreach ($ga_tbl_support as $i_index => $st_valeur) {
        if ($pi_support == $i_index)
            print("<option value=\"$i_index\" selected=\"selected\">$st_valeur</option>");
        else
            print("<option value=\"$i_index\">$st_valeur</option>");
    }
    print("</select>");
    print('</div>');
    print('</div>');

    print('<div class="form-group row">');
    print('<label for="collection" class="col-form-label col-md-2">Collection</label>');
    print('<div class="col-md-10">');
    print("<select name=collection id=collection class=\"form-control\">");
    foreach ($ga_tbl_collection as $i_index => $st_valeur) {
        if ($pi_collection == $i_index)
            print("<option value=\"$i_index\" selected=\"selected\">$st_valeur</option>");
        else
            print("<option value=\"$i_index\">$st_valeur</option>");
    }
    print("</select>");
    print('</div>');
    print('</div>');

    print('<div class="form-group row">');
    print('<label for="quantite" class="col-form-label col-md-2">Nombre de documents</label>');
    print('<div class="col-md-10">');
    print("<input type=\"text\" maxsize=5 size=5 name=quantite id=quantite value=\"$pi_quantite\" class=\"form-control\">");
    print('</div>');
    print('</div>');

    print('<div class="form-group row">');
    print('<label for="auteur" class="col-form-label col-md-2">Auteur</label>');
    print('<div class="col-md-10">');
    print("<input type=text name=auteur id=auteur size=40 value=\"$pst_auteur\" class=\"form-control\">");
    print('</div>');
    print('</div>');
}

/** Affiche le menu de modification d'une collection de documents
 * @param object $pconnexionBD Identifiant de la connexion de base
 * @param integer $pi_idf Identifiant de la collection de photo
 * @param array $pa_communes Liste des commmunes
 */
function menu_modifier($pconnexionBD, $pi_idf, $pa_communes)
{
    $st_requete = "select `id_commune`,`type_acte`,`nature_acte`,`fourchette`,`support`,`collection`,`quantite`,`auteur` from `documents` where idf=$pi_idf";
    list($i_id_commune, $i_type_acte, $i_nature_acte, $st_fourchette, $i_support, $i_collection, $i_quantite, $st_auteur) = $pconnexionBD->sql_select_liste($st_requete);
    print("<form   method=\"post\" id=\"edition_documents\">");
    print("<input type=hidden name=mode value=MODIFIER>");
    print("<input type=hidden name=idf value=$pi_idf>");
    menu_edition($i_id_commune, $i_type_acte, $i_nature_acte, $st_fourchette, $i_support, $i_collection, $i_quantite, $st_auteur, $pa_communes);
    print('<div class="btn-group col-md-4 col-md-offset-4" role="group">');
    print('<button type=submit class="btn btn-primary"><span class="glyphicon glyphicon-ok"></span> Modifier</button>');
    print('<button type=button id=annuler class="btn btn-primary"><span class="glyphicon glyphicon-remove"></span> Annuler</button>');
    print('</div>');
    print('</form>');
}

/** Affiche le menu d'ajout d'une photo
 * @param array $pa_communes Liste des commmunes
 */
function menu_ajouter($pa_communes)
{
    print("<form   method=\"post\" id=\"edition_documents\">");
    print("<input type=hidden name=mode value=AJOUTER>");
    menu_edition(0, 0, 0, '', 0, 0, 0, '', $pa_communes);
    print('<div class="btn-group col-md-4 col-md-offset-4" role="group">');
    print('<button type=submit class="btn btn-primary"><span class="glyphicon glyphicon-ok"></span> Ajouter</button>');
    print('<button type=button id=annuler class="btn btn-primary"><span class="glyphicon glyphicon-remove"></span> Annuler</button>');
    print('</div>');
    print('</form>');
}

$ga_tbl_support = array(0 => '', 1 => 'Acte authentique', 2 => 'Photo', 3 => 'Relev&eacute; papier');
$ga_tbl_nature = array(0 => '', 1 => 'RPX catholiques', 2 => 'RPX protestants', 3 => 'Etat civil', 4 => 'Actes Notari&eacute; ');
$ga_tbl_collection = array(0 => '', 1 => 'AD Depot communes', 2 => 'AD Greffe', 3 => 'AD Notaire', 4 => 'Mairie', 5 => 'AM', 6 => 'Internet', 7 => 'Notaire Etude', 8 => 'Archives Diocesaines', 9 => 'F (documents familiaux)');

require_once __DIR__ . '/../commun/menu.php';
$gst_commune_a_chercher = isset($_POST['commune_a_chercher']) ? trim($_POST['commune_a_chercher']) : '';
$gi_num_page_cour = empty($_GET['num_page']) ? 1 : $_GET['num_page'];

$ga_communes    =    $connexionBD->liste_valeur_par_clef("select idf,nom from `commune_acte` order by nom");


switch ($gst_mode) {
    case 'LISTE':
        menu_liste($connexionBD, $gst_commune_a_chercher);
        break;
    case 'MENU_MODIFIER':
        menu_modifier($connexionBD, $gi_idf, $ga_communes);
        break;
    case 'MODIFIER':

        $i_id_commune = (int) $_POST['id_commune'];
        $gi_idf = (int) $_POST['idf'];
        $a_types_actes = $_POST['type_acte'];
        $i_type_acte = 0;
        if (in_array('B', $a_types_actes)) $i_type_acte = $i_type_acte | 1;
        if (in_array('M', $a_types_actes)) $i_type_acte = $i_type_acte | 2;
        if (in_array('S', $a_types_actes)) $i_type_acte = $i_type_acte | 4;
        if (in_array('V', $a_types_actes)) $i_type_acte = $i_type_acte | 8;
        $i_nature_acte = (int) $_POST['type_nature'];
        $st_fourchette = trim($_POST['fourchette']);
        $i_support = (int) $_POST['type_support'];
        $i_collection = (int) $_POST['collection'];
        $i_quantite = trim($_POST['quantite']);
        $st_auteur = trim($_POST['auteur']);
        $connexionBD->initialise_params(array(':auteur' => $st_auteur));
        $st_requete = "update `documents` set id_commune=$i_id_commune, type_acte=$i_type_acte, nature_acte=$i_nature_acte, fourchette='$st_fourchette',support=$i_support, collection=$i_collection,quantite=$i_quantite, auteur=:auteur where idf=$gi_idf";
        $connexionBD->execute_requete($st_requete);

        menu_liste($connexionBD, null);
        break;
    case 'MENU_AJOUTER':

        menu_ajouter($ga_communes);
        break;
    case 'AJOUTER':
        $i_id_commune = (int) $_POST['id_commune'];
        $a_types_actes = $_POST['type_acte'];
        $i_type_acte = 0;
        if (in_array('B', $a_types_actes)) $i_type_acte = $i_type_acte | 1;
        if (in_array('M', $a_types_actes)) $i_type_acte = $i_type_acte | 2;
        if (in_array('S', $a_types_actes)) $i_type_acte = $i_type_acte | 4;
        if (in_array('V', $a_types_actes)) $i_type_acte = $i_type_acte | 8;
        $i_nature_acte = (int) $_POST['type_nature'];
        $st_fourchette = trim($_POST['fourchette']);
        $i_support = (int) $_POST['type_support'];
        $i_collection = (int) $_POST['collection'];
        $i_quantite = trim($_POST['quantite']);
        $st_auteur = trim($_POST['auteur']);
        $connexionBD->initialise_params(array(':auteur' => $st_auteur));
        $connexionBD->execute_requete("insert into documents(id_commune,type_acte,nature_acte,fourchette,support,collection,quantite,auteur) values($i_id_commune, $i_type_acte, $i_nature_acte,'$st_fourchette',$i_support,$i_collection,$i_quantite,:auteur)");
        menu_liste($connexionBD, null);
        break;
    case 'SUPPRIMER':
        $a_liste_documents = $_POST['supp'];
        foreach ($a_liste_documents as $i_idf) {
            $r_chantier = $connexionBD->sql_select("select * from `chantiers` where id_document = $i_idf");
            if (count($r_chantier) == 0) {
                $connexionBD->execute_requete("delete from `documents` where idf = $i_idf");
            } else {
                print("<div class=\"alert alert-danger\">Les chantiers suivants doivent &ecirc;tre supprim&eacute;s auparavant:</div>");
                $l_chantier = $connexionBD->sql_select_multiple("select co.nom, ad.nom, re.fourchette 
			 from `commune_acte` co 
			 join `documents`    re on (re.id_commune = co.idf) 
			 join `chantiers`    ch on (ch.id_document = re.idf) 
			 join `adherent`     ad on (ch.id_releveur = ad.idf) 
			 where re.idf = $i_idf");
                print("<table class=\"table-bordered table-striped\">");
                print("<thead><tr><th>Commune</th><th>Adh&eacute;rent</th><th>Fourchette</th></tr></thead><tbody>\n");
                foreach ($l_chantier as $l_chant) {
                    list($st_commune, $st_adherent, $st_fourchette) = $l_chant;
                    print("<tr><td>$st_commune</td><td>$st_adherent</td><td>$st_fourchette</td></tr>\n");
                }
                print("</tbody></table>");
            }
        }
        menu_liste($connexionBD, null);
        break;
}
print('</div></body>');
