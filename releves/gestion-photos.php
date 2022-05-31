<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------
//http://127.0.0.1:8888/Gestion_Photos.php

require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../Origin/PaginationTableau.php';

// Redirect to identification
if (!$session->isAuthenticated()) {
    $session->setAttribute('url_retour', '/administration/gestion-communes.php');
    header('HTTP/1.0 401 Unauthorized');
    header('Location: /se-connecter.php');
    exit;
}
if (!in_array('RELEVES', $user['privileges'])) {
    header('HTTP/1.0 401 Unauthorized');
    exit;
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

        $("#edition_photos").validate({
            rules: {
                fourchette: "required",

                nbr_photos: {
                    "required": true,
                    "integer": true
                },
                poids_photos: {
                    "required": true,
                    "integer": true
                },
                dt_prise: {
                    "dateITA": true
                }
            },
            messages: {
                fourchette: {
                    required: "La fourchette est obligatoire"
                },
                nbr_photos: {
                    required: "Le nombre de photos est obligatoire",
                    integer: "Le nombre de photos doit être un entier"
                },
                poids_photos: {
                    required: "Le poids des photos est obligatoire",
                    integer: "Le poids des photos  doit être un entier"
                },
                dt_prise: {
                    dateITA: "ceci n'est pas une date"
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

        $("#suppression_photos").validate({
            rules: {
                "supp[]": {
                    required: true,
                    minlength: 1
                }
            },
            messages: {
                "supp[]": "Merci de choisir au moins une photo à supprimer"
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
                var photos = '';
                $("input:checkbox").each(function() {
                    var $this = $(this);
                    if ($this.is(":checked")) {
                        photos = photos + ' ' + $this.attr("id");
                    }

                });
                if (confirm('Etes-vous sûr de supprimer les documents photos ' + photos + ' ?')) {
                    form.submit();
                }
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
    $gi_idf_photo = (int) $_GET['mod'];
} else
    $gi_idf_photo = isset($_POST['idf_photo']) ? (int) $_POST['idf_photo'] : 0;

$gi_session_num_page_cour =   isset($_SESSION['num_page_photos']) ? $_SESSION['num_page_photos'] : 1;
$gi_num_page_cour = empty($_GET['num_page']) ? $gi_session_num_page_cour : $_GET['num_page'];

/**
 * Affiche la liste des communes
 * @param object $pconnexionBD  connexion à la base de données
 * @param string $pst_commune_a_chercher commune à chercher 
 */
function menu_liste($pconnexionBD, $pst_commune_a_chercher)
{
    global $gi_num_page_cour, $gi_max_taille_upload;
    $st_requete = "SELECT DISTINCT (left( ca.nom, 1 )) AS init FROM `commune_acte` ca join `photos` p on (p.id_commune=ca.idf) ORDER BY init";
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
        $_SESSION['num_page_photos'] = $gi_num_page_cour;
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
    $st_requete = ($pst_commune_a_chercher == '') ? "select p.idf,ca.nom,p.fourchette,ca2.libelle from `photos` p join `commune_acte` ca  on (p.id_commune=ca.idf ) join `collection_acte` ca2 on (p.id_collection=ca2.idf) where ca.nom like '" . utf8_vers_cp1252($gc_initiale) . "%' order by ca.nom,p.fourchette,ca2.libelle" : "select p.idf,ca.nom,p.fourchette,ca2.libelle from `photos` p join `commune_acte` ca  on (p.id_commune=ca.idf ) join `collection_acte` ca2 on (p.id_collection=ca2.idf) where ca.nom like '" . utf8_vers_cp1252($pst_commune_a_chercher) . "' order by ca.nom,p.fourchette,ca2.libelle";
    print("<form   method=\"post\" >");
    print("<input type=hidden name=mode value=\"LISTE\">");
    print('<div class="form-row col-md-12">');
    print('<label for="commune_a_chercher"  class="col-form-label col-md-2">Commune:</label>');
    print('<div class="col-md-8">');
    print('<input name="commune_a_chercher" id="commune_a_chercher" value="" size="25" maxlength="25" type="Text" class="form-control" aria-describedby="aideCom">');
    print('<small id="aideCom" class="form-text text-muted">Vous pouvez mettre le caract&egrave;re "*" pour chercher sur une racine (ex.: saint*)</small>');
    print('</div>');
    print('<button type=submit class="btn btn-primary"><span class="glyphicon glyphicon-search"></span> Chercher</button>');
    print('</div>');
    print("</form><form   method=\"post\" id=\"suppression_photos\">");
    $a_liste_photos = $pconnexionBD->liste_valeur_par_clef($st_requete);
    $i_nb_photos = count($a_liste_photos);
    if ($i_nb_photos != 0) {
        $pagination = new PaginationTableau(basename(__FILE__), 'num_page', $i_nb_photos, NB_LIGNES_PAR_PAGE, DELTA_NAVIGATION, array('Commune', 'Fourchette', 'Collection', 'Modifier', 'Supprimer'));
        $pagination->init_param_bd($pconnexionBD, $st_requete);
        $pagination->init_page_cour($gi_num_page_cour);
        $pagination->affiche_entete_liens_navigation();
        $pagination->affiche_tableau_edition(basename(__FILE__));
        $pagination->affiche_entete_liens_navigation();
    } else
        print('<div class="row col-md-12  alert alert-danger">Pas de photos</div>');

    print("<input type=hidden name=mode value=SUPPRIMER>");
    print('<button type=submit class="btn btn-danger col-md-4 col-md-offset-4"><span class="glyphicon glyphicon-remove"></span> Supprimer les photos s&eacute;lectionn&eacute;es</button>');
    print("</form>");
    print("<form   method=\"post\">");
    print("<input type=hidden name=mode value=MENU_AJOUTER>");
    print('<button type=submit class="btn btn-primary col-md-4 col-md-offset-4"><span class="glyphicon glyphicon-plus"></span> Ajouter une photo</button>');
    print('</form>');
    print("<form enctype=\"multipart/form-data\"  method=\"post\" >");
    print("<input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"$gi_max_taille_upload\">");
    print('<input type="hidden" name="mode" value="CHARGER" />');
    print('<div class="row col-md-12">');
    print('<div class="form-group col-md-10">');
    print('<div class="custom-file">');
    print('<label for="Photos" class="custom-file-label col-md-5">Fichier<span class="alert alert-danger">CSV</span>des photos:</label>');
    print('<input name="Photos" id="Photos" type="file" class="custom-file-input col-md-5">');
    print('</div>');
    print('</div>');
    print('<button type=submit class="btn btn-primary col-md-2"><span class="glyphicon glyphicon-upload"></span> Charger les photos</button>');
    print('</div>');
    print('</form>');
}

/**
 * Affiche de la table d'édition
 * @param integer $pi_id_commune identifiant de la commune
 * @param string $pst_fourchette Fourchette de la photo
 * @param integer $pi_id_collection identifiant de la collection
 * @param integer $pi_nbr_photos nombres de photos
 * @param integer $pi_poids poids de photos 
 * @param integer $pid_auteur auteur de photos 
 * @param string $pcrel_pap Relevé papier (O|N)
 * @param string $pcrel_base Relevé Base (O|N)
 * @param string $pcrel_td Relevé TD (O|N) 
 * @param date $pdt_prise Date de prise de vue 
 * @param array $pa_communes liste des communes
 * @param array $pa_collections liste des collections    
 * @param array $pa_adherents liste des auteurs (adhérents) 
 */
function menu_edition($pi_id_commune, $pst_fourchette, $pi_id_collection, $pi_nbr_photos, $pi_poids, $pid_auteur, $pcrel_pap, $pcrel_base, $pcrel_td, $pdt_prise, $pa_communes, $pa_collections, $pa_adherents)
{
    $a_oui_non = array(0 => 'Non', 1 => 'Oui');
    if ($pdt_prise != '') {
        $pdt_prise = sprintf("%02s/%02s/%4s", substr($pdt_prise, 8, 2), substr($pdt_prise, 5, 2), substr($pdt_prise, 0, 4));
    }
    print('<div class="form-group row">');
    print('<label for="id_commune" class="col-form-label col-md-2">Commune</label>');
    print('<div class="col-md-10">');
    print("<select name=id_commune id=id_commune class=\"js-select-avec-recherche form-control\">" . chaine_select_options($pi_id_commune, $pa_communes) . "</select>");
    print('</div>');
    print('</div>');
    print('<div class="form-group row">');
    print('<label for="fourchette" class="col-form-label col-md-2">Fourchette (aaaa-aaaa)</label>');
    print('<div class="col-md-10">');
    print("<input type=\"text\" maxsize=20 size=14 name=fourchette id=fourchette value=\"$pst_fourchette\" class=\"form-control\">");
    print('</div>');
    print('</div>');
    print('<div class="form-group row">');
    print('<label for="id_collection" class="col-form-label col-md-2">Collection</label>');
    print('<div class="col-md-10">');
    print("<select name=id_collection id=id_collection class=\"js-select-avec-recherche form-control\">" . chaine_select_options($pi_id_collection, $pa_collections) . "</select>");
    print('</div>');
    print('</div>');
    print('<div class="form-group row">');
    print('<label for="nbr_photos" class="col-form-label col-md-2">Nombre de photos</label>');
    print('<div class="col-md-10">');
    print("<input type=\"text\" maxsize=5 size=5 name=nbr_photos id=nbr_photos value=\"$pi_nbr_photos\" class=\"form-control\">");
    print('</div>');
    print('</div>');
    print('<div class="form-group row">');
    print('<label for="poids_photos" class="col-form-label col-md-2">Poids des photos</label>');
    print('<div class="col-md-10">');
    print("<input type=\"text\" maxsize=5 size=5 name=poids_photos id=poids_photos value=\"$pi_poids\" class=\"form-control\">");
    print('</div>');
    print('</div>');
    print('<div class="form-group row">');
    print('<label for="id_auteur" class="col-form-label col-md-2">Auteur</label>');
    print('<div class="col-md-10">');
    print("<select name=id_auteur id=id_auteur class=\"js-select-avec-recherche form-control\">" . chaine_select_options($pid_auteur, $pa_adherents) . "</select>");
    print('</div>');
    print('</div>');
    print('<div class="form-group row">');
    print('<label for="rel_pap" class="col-form-label col-md-2">Relev&eacute; Papier</label>');
    print('<div class="col-md-10">');
    print("<select name=rel_pap id=rel_pap class=\"form-control\">" . chaine_select_options($pcrel_pap, $a_oui_non) . "</select>");
    print('</div>');
    print('</div>');
    print('<div class="form-group row">');
    print('<label for="rel_base" class="col-form-label col-md-2">Relev&eacute; Base</label>');
    print('<div class="col-md-10">');
    print("<select name=rel_base id=rel_base class=\"form-control\">" . chaine_select_options($pcrel_base, $a_oui_non) . "</select>");
    print('</div>');
    print('</div>');
    print('<div class="form-group row">');
    print('<label for="rel_td" class="col-form-label col-md-2">Relev&eacute; TD</label>');
    print('<div class="col-md-10">');
    print("<select name=rel_td id=rel_td class=\"form-control\">" . chaine_select_options($pcrel_td, $a_oui_non) . "</select>");
    print('</div>');
    print('</div>');
    print('<div class="form-group row">');
    print('<label for="dt_prise" class="col-form-label col-md-2">Date prise de vue (jj/mm/aaaa)</label>');
    print('<div class="col-md-10">');
    print("<input type=\"text\" maxsize=10 size=10 name=dt_prise id=dt_prise value=\"$pdt_prise\" class=\"form-control\">");
    print('</div>');
    print('</div>');
}

/** Affiche le menu de modification d'une collection de photos
 * @param object $pconnexionBD Identifiant de la connexion de base
 * @param integer $pi_idf_photo Identifiant de la collection de photo
 * @param array $pa_communes Liste des commmunes
 * @param array $pa_collections Liste des collections
 * @param array $pa_adherents Liste des adhérents
 */
function menu_modifier($pconnexionBD, $pi_idf_photo, $pa_communes, $pa_collections, $pa_adherents)
{
    $st_requete = "select `id_commune`,`fourchette`,`id_collection`,`nbr_photos`,`poids_total`,`id_auteur`,`releve_papier`,`releve_base`,`releve_td`,`date_prise` from `photos` where idf=$pi_idf_photo";
    list($i_id_commune, $st_fourchette, $i_id_collection, $i_nbr_photos, $i_poids, $id_auteur, $crel_pap, $crel_base, $crel_td, $dt_prise) = $pconnexionBD->sql_select_liste($st_requete);
    print("<form   method=\"post\" id=\"edition_photos\">");
    print("<input type=hidden name=mode value=MODIFIER>");
    print("<input type=hidden name=idf_photo value=$pi_idf_photo>");
    menu_edition($i_id_commune, $st_fourchette, $i_id_collection, $i_nbr_photos, $i_poids, $id_auteur, $crel_pap, $crel_base, $crel_td, $dt_prise, $pa_communes, $pa_collections, $pa_adherents);
    print('<div class="btn-group col-md-4 col-md-offset-4" role="group">');
    print('<button type=submit class="btn btn-primary"><span class="glyphicon glyphicon-ok"></span> Modifier</button>');
    print('<button type=button id=annuler class="btn btn-primary"><span class="glyphicon glyphicon-remove"></span> Annuler</button>');
    print('</div>');
    print('</form>');
}

/** Affiche le menu d'ajout d'une photo
 * @param array $pa_communes Liste des commmunes
 * @param array $pa_collections Liste des collections
 * @param array $pa_adherents Liste des adhérents
 */
function menu_ajouter($pa_communes, $pa_collections, $pa_adherents)
{
    print("<form   method=\"post\" id=\"edition_photos\">");
    print("<input type=hidden name=mode value=AJOUTER>");
    menu_edition(0, '', 0, '', '', 0, 0, 0, 0, '', $pa_communes, $pa_collections, $pa_adherents);
    print('<div class="btn-group col-md-4 col-md-offset-4" role="group">');
    print('<button type=submit class="btn btn-primary"><span class="glyphicon glyphicon-ok"></span> Ajouter</button>');
    print('<button type=button id=annuler class="btn btn-primary"><span class="glyphicon glyphicon-remove"></span> Annuler</button>');
    print('</div>');
    print('</form>');
}

/**
 * Charge les photos dans la base
 * @param object $pconnexionBD Connexion à la base
 */
function charge_photos($pconnexionBD)
{
    print('<div class="panel panel-primary">');
    print('<div class="panel-heading">>Chargement des photos</div>');
    print('<div class="panel-body">');
    $a_stats_nai = $pconnexionBD->liste_valeur_par_clef("select concat(ca.code_insee,lpad(ca.numero_paroisse,1,0)),sc.nb_actes from `stats_commune` sc join `commune_acte` ca on (sc.idf_commune=ca.idf) where sc.idf_type_acte=" . IDF_NAISSANCE . " and sc.idf_source=1");
    $st_fich_dest = tempnam("", "photos");
    if (!move_uploaded_file($_FILES['Photos']['tmp_name'], $st_fich_dest)) {
        print('<div class="alert alert-danger">Erreur de t&eacute;l&eacute;chargement:</div>');
        switch ($_FILES['Photos']['error']) {
            case 2:
                print("Fichier trop gros par rapport &agrave; MAX_FILE_SIZE");
                break;
            default:
                print("Erreur inconnue");
                print_r($_FILES);
        }
        exit;
    }
    $pf = fopen($st_fich_dest, "r") or die("Impossible de lire $st_fich_dest\n");
    $a_paroisses = $pconnexionBD->liste_valeur_par_doubles_clefs("select code_insee,numero_paroisse,idf from commune_acte");
    $a_nature = $pconnexionBD->liste_clef_par_valeur("select idf,libelle from collection_acte");
    //$st_fich_sortie = tempnam ("", "photos");
    //$sortie = fopen($st_fich_sortie, "w");

    $st_requete = "INSERT IGNORE INTO `photos` (id_commune,fourchette,id_collection,poids_total,nbr_photos,id_auteur,date_prise) VALUES ";
    $a_lignes = array();
    while ((list($i_num_paroisse, $st_nom_paroisse, $st_fourchette, $st_nature, $i_taille, $i_nbre, $st_auteur, $i_idf_adh, $st_date_prise) = fgetcsv($pf, 1000, ';')) !== FALSE) {
        // Saute les lignes sans numéro de paroisse
        if (!is_numeric($i_num_paroisse))
            continue;
        $st_code_insee = substr($i_num_paroisse, 0, 5);
        $i_num_paroisse2 = sprintf("%d", substr($i_num_paroisse, 5, 2));
        $st_nature = trim($st_nature);
        if (isset($a_paroisses[$st_code_insee][$i_num_paroisse2])) {
            if (isset($a_nature[$st_nature])) {
                // nettoyage des fourchettes
                list($i_annee_deb, $i_annee_fin) = explode('-', $st_fourchette, 2);
                $i_annee_deb = trim($i_annee_deb);
                $i_annee_fin = trim($i_annee_fin);
                $st_fourchette = "$i_annee_deb-$i_annee_fin";
                // mise en conformité de la date
                list($i_jour, $i_mois, $i_annee) = explode('/', $st_date_prise, 3);
                $st_date_prise = join('-', array($i_annee, $i_mois, $i_jour));
                list($i_idf_paroisse) = $a_paroisses[$st_code_insee][$i_num_paroisse2];
                if ($i_idf_adh == '') $i_idf_adh = '\N';
            } else {
                print("<div class=\"alert alert-danger\">Impossible de trouver le type de collection: $st_nature (Commune $st_nom_paroisse)</div>");
            }
        } else {
            print("<div class=\"alert alert-danger\">Impossible de trouver une commune($st_code_insee,$i_num_paroisse)</div>");
        }
    }
    fclose($pf);
    if (count($a_lignes) > 0) {
        $st_lignes = join(',', $a_lignes);
        $st_requete .= $st_lignes;
        try {
            $pconnexionBD->execute_requete($st_requete);
        } catch (Exception $e) {
            unlink($st_fich_sortie);
            die('<div class=\"row col-md-12 alert alert-danger\">Chargement photos impossible: ' . $e->getMessage() . "$st_requete</div>");
        }
    }
    unlink($st_fich_dest);
    print('<button type=button id=annuler class="btn btn-primary col-md-4 col-md-offset-4">Retour</button>');
    print('</form></div></div>');
}


require_once __DIR__ . '/../commun/menu.php';

$gst_commune_a_chercher = isset($_POST['commune_a_chercher']) ? trim($_POST['commune_a_chercher']) : '';

$ga_communes    =    $connexionBD->liste_valeur_par_clef("select idf,nom from `commune_acte` order by nom");
$ga_collections =    $connexionBD->liste_valeur_par_clef("select idf,libelle from `collection_acte` order by libelle");
$ga_auteurs     =   $connexionBD->liste_valeur_par_clef("select idf,concat(nom,'  ',prenom,' (',idf,')') from adherent order by nom,prenom");

switch ($gst_mode) {
    case 'LISTE':
        menu_liste($connexionBD, $gst_commune_a_chercher);
        break;
    case 'MENU_MODIFIER':
        menu_modifier($connexionBD, $gi_idf_photo, $ga_communes, $ga_collections, $ga_auteurs);
        break;
    case 'MODIFIER':
        $i_id_commune = (int) $_POST['id_commune'];
        $st_fourchette = trim($_POST['fourchette']);
        $i_id_collection = (int) $_POST['id_collection'];
        $i_nbr_photos = trim($_POST['nbr_photos']);
        $i_poids_photos = trim($_POST['poids_photos']);
        $i_id_auteur = (int) $_POST['id_auteur'];
        $i_rel_pap = (int) $_POST['rel_pap'];
        $i_rel_base = (int) $_POST['rel_base'];
        $i_rel_td = (int) $_POST['rel_td'];
        list($i_jour, $i_mois, $i_annee) = explode('/', $_POST['dt_prise'], 3);
        $c_date_prise = join('-', array($i_annee, $i_mois, $i_jour));
        $st_requete = "update `photos` set id_commune=$i_id_commune, fourchette='$st_fourchette',id_collection=$i_id_collection,nbr_photos=$i_nbr_photos,poids_total=$i_poids_photos,id_auteur=$i_id_auteur,releve_papier=$i_rel_pap,releve_base=$i_rel_base,releve_td=$i_rel_td, date_prise='$c_date_prise' where idf=$gi_idf_photo";
        $connexionBD->execute_requete($st_requete);

        menu_liste($connexionBD, $gst_commune_a_chercher);
        break;
    case 'MENU_AJOUTER':

        menu_ajouter($ga_communes, $ga_collections, $ga_auteurs);
        break;
    case 'AJOUTER':
        $i_id_commune = (int) $_POST['id_commune'];
        $st_fourchette = trim($_POST['fourchette']);
        $i_id_collection = (int) $_POST['id_collection'];
        $i_nbr_photos = trim($_POST['nbr_photos']);
        $i_poids_photos = trim($_POST['poids_photos']);
        $i_id_auteur = (int) $_POST['id_auteur'];
        $i_rel_pap = (int) $_POST['rel_pap'];
        $i_rel_base = (int) $_POST['rel_base'];
        $i_rel_td = (int) $_POST['rel_td'];
        if (!empty($_POST['dt_prise'])) {
            list($i_jour, $i_mois, $i_annee) = explode('/', $_POST['dt_prise'], 3);
            $c_date_prise = join('-', array($i_annee, $i_mois, $i_jour));
        } else
            $c_date_prise = null;
        $connexionBD->execute_requete("insert into photos(id_commune,fourchette,id_collection,nbr_photos,poids_total,id_auteur,releve_papier,releve_base,releve_td,date_prise) values($i_id_commune,'$st_fourchette',$i_id_collection,$i_nbr_photos,$i_poids_photos,$i_id_auteur,$i_rel_pap,$i_rel_base,$i_rel_td,'$c_date_prise')");
        menu_liste($connexionBD, $gst_commune_a_chercher);
        break;
    case 'SUPPRIMER':
        $a_liste_photos = $_POST['supp'];
        foreach ($a_liste_photos as $i_idf_photo) {
            $r_releve = $connexionBD->sql_select_multiple("select ph.id_commune, re.id_commune from `photos` ph join `releve_cours` re on (ph.id_commune = re.id_commune) where ph.idf = $i_idf_photo");
            if (count($r_releve) == 0) {
                $connexionBD->execute_requete("delete from `photos` where idf = $i_idf_photo");
            } else {
                print('<div class="alert alert-danger">Les relev&eacute;s suivants doivent &ecirc;tre supprim&eacute;s auparavant :</div>');
                $l_releve = $connexionBD->sql_select_multiple("select co.nom, ad.nom, re.fourchette from `commune_acte` co join `releve_cours` re on (re.id_commune = co.idf) join `adherent` ad on (re.id_adherent = ad.idf) join `photos` ph on (re.id_commune = ph.id_commune) where ph.idf = $i_idf_photo");
                print("<table table-bordered table-striped\">");
                print("<tr><th>Commune</th><th>Releveur</th><th>Fourchette</th></tr>\n");
                foreach ($l_releve as $l_relev) {
                    list($st_commune, $st_adherent, $st_fourchette) = $l_relev;
                    print("<tr><td>$st_commune</td><td>$st_adherent</td><td>$st_fourchette</td></tr>\n");
                }
                print("</table>");
            }
        }
        menu_liste($connexionBD, $gst_commune_a_chercher);
        break;
    case 'CHARGER':
        charge_photos($connexionBD);
        menu_liste($connexionBD, $gst_commune_a_chercher);
        break;
}
print('</div></body>');
