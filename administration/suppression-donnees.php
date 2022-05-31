<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------

require_once __DIR__ . '/../app/bootstrap.php';

// Redirect to identification
if (!$session->isAuthenticated()) {
    $session->setAttribute('url_retour', '/administration/gestion-communes.php');
    header('HTTP/1.0 401 Unauthorized');
    header('Location: /se-connecter.php');
    exit;
}
if (!in_array('CHGMT_EXPT', $user['privileges'])) {
    header('HTTP/1.0 401 Unauthorized');
    exit;
}


/** Renvoie le nombre d'actes comportant des permaliens non remplis
 * param integer $pi_idf_commune_acte identifiant de la commune 
 * param integer $pc_idf_type_acte identifiant du type d'acte
 **/
function nombre_permaliens($pi_idf_commune_acte, $pc_idf_type_acte)
{
    global $connexionBD, $gst_types_acte;
    if ($pc_idf_type_acte == 'DIV')
        $st_requete = "select count(idf) from acte where idf_commune=$pi_idf_commune_acte and idf_type_acte not in $gst_types_acte and url !=''";

    else
        $st_requete = "select count(idf) from acte where idf_commune=$pi_idf_commune_acte and idf_type_acte=$pc_idf_type_acte and url !=''";
    return $connexionBD->sql_select1($st_requete);
}

print('<!DOCTYPE html>');
print("<head>");
print('<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" >');
print('<meta http-equiv="content-language" content="fr">');
print('<meta name="viewport" content="width=device-width, initial-scale=1.0">');
print("<link href='../assets/css/styles.css' type='text/css' rel='stylesheet'>");
print("<link href='../assets/css/bootstrap.min.css' rel='stylesheet'>");
print("<link href='../assets/css/jquery-ui.css' type='text/css' rel='stylesheet'>");
print("<link href='../assets/css/jquery-ui.structure.min.css' type='text/css' rel='stylesheet'>");
print("<link href='../assets/css/jquery-ui.theme.min.css' type='text/css' rel='stylesheet'> ");
print("<link href='../assets/css/select2.min.css' type='text/css' rel='stylesheet'> ");
print("<link href='../assets/css/select2-bootstrap.min.css' type='text/css' rel='stylesheet'> ");
print("<script src='../assets/js/jquery-min.js' type='text/javascript'></script>");
print("<script src='../assets/js/jquery.validate.min.js' type='text/javascript'></script>");
print("<script src='../assets/js/additional-methods.min.js' type='text/javascript'></script>");
print("<script src='../assets/js/jquery-ui.min.js' type='text/javascript'></script>");
print("<script src='../assets/js/select2.min.js' type='text/javascript'></script>");
print("<script src='../assets/js/bootstrap.min.js' type='text/javascript'></script>");
?>
<script type='text/javascript'>
    $(document).ready(function() {

        $.fn.select2.defaults.set("theme", "bootstrap");

        $(".js-select-avec-recherche").select2();

        $("#autre_type_acte").change(function() {
            $('#type_autre').prop('checked', true);
        });

        $.validator.addMethod('plusGrand', function(value, element, param) {
            if (this.optional(element)) return true;
            var annee_max = $(param).val();
            if (jQuery.trim(annee_max).length == 0) return true;
            var i = parseInt(value);
            var j = parseInt(annee_max);
            return i >= j;
        }, "l'année maximale doit être plus grande que l'année minimale");

        $("#formulaire_suppression").validate({
            rules: {
                idf_source: "required",
                idf_commune_acte: "required",
                annee_min: "integer",
                annee_max: {
                    integer: true,
                    plusGrand: '#annee_min'
                }
            },
            messages: {
                idf_source: {
                    required: "Une source doit être choisie"
                },
                idf_commune_acte: {
                    required: "Une commune doit être choisie"
                },
                annee_min: {
                    integer: "l'année minimale doit être un entier"
                },
                annee_max: {
                    integer: "l'année maximale doit être un entier"
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
            },
            submitHandler: function(form) {
                var source = $('#idf_source option:selected').text();
                var lib_type_acte = '';
                switch (parseInt($('input[name=type_acte]:checked', form).val())) {
                    case 1:
                        lib_type_acte = 'Baptême/Naissance';
                        $("#idf_type_acte").val(<?php echo IDF_NAISSANCE; ?>);
                        break;
                    case 2:
                        lib_type_acte = 'Mariage';
                        $("#idf_type_acte").val(<?php echo IDF_MARIAGE; ?>);
                        break;
                    case 3:
                        lib_type_acte = 'Sépulture/Décès';
                        $("#idf_type_acte").val(<?php echo IDF_DECES; ?>);
                        break;
                    case 4:
                        lib_type_acte = 'Tous divers Nimègue';
                        $("#idf_type_acte").val('DIV');
                        break;
                    case 5:
                        lib_type_acte = 'Recensement';
                        $("#idf_type_acte").val(<?php echo IDF_RECENS; ?>);
                        break;
                    case 6:
                        lib_type_acte = $('#autre_type_acte option:selected').text();
                        $("#idf_type_acte").val($('#autre_type_acte option:selected').val());
                        break;
                }
                console.log($("#idf_type_acte").val());
                var commune = $('#idf_commune_acte option:selected').text();
                var annee_min = $('#annee_min').val();
                var annee_max = $('#annee_max').val();
                var intervalle = '';
                if (annee_min != '' && annee_max != '') {
                    intervalle = 'de ' + annee_min + ' à ' + annee_max;
                } else if (annee_min != '') {
                    intervalle = 'à partir de ' + annee_min;
                } else if (annee_max != '') {
                    intervalle = "jusqu'en " + annee_max;
                }
                var question = 'Etes-vous sûr de supprimer les actes (' + lib_type_acte + ')' + ' de la commune ' + commune + '  et de la source ' + source;
                if (intervalle != '') {
                    question = question + ' (' + intervalle + ')';
                }
                question = question + ' ?';
                if (confirm(question)) {
                    form.submit();
                }
            }
        });


    });
</script>
<?php
print("<title>Suppression des donnees</title>");
print("</head>");
print("<body>");
print('<div class="container">');

require_once __DIR__ . '/../commun/menu.php';

$gst_mode = empty($_POST['mode']) ? 'FORMULAIRE' : $_POST['mode'];
$gi_idf_source = empty($_POST['idf_source']) ? 1 : $_POST['idf_source'];
$i_session_idf_commune_acte = isset($_SESSION['idf_commune_acte']) ? $_SESSION['idf_commune_acte'] : 0;
$gi_idf_commune_acte = empty($_POST['idf_commune_acte']) ? $i_session_idf_commune_acte : $_POST['idf_commune_acte'];
$gc_idf_type_acte = empty($_POST['idf_type_acte']) ? 0 : $_POST['idf_type_acte'];

$gst_types_acte = '(' . IDF_MARIAGE . ',' . IDF_NAISSANCE . ',' . IDF_DECES . ',' . IDF_RECENS . ')';

$gi_annee_min = empty($_POST['annee_min']) ? '' : (int) $_POST['annee_min'];
$gi_annee_max = empty($_POST['annee_max']) ? '' : (int) $_POST['annee_max'];


switch ($gst_mode) {
    case 'FORMULAIRE':
        $a_sources = $connexionBD->liste_valeur_par_clef("select idf,nom from source order by nom");
        $a_communes_acte = $connexionBD->liste_valeur_par_clef("select idf,nom from commune_acte order by nom");
        $a_types_acte = $connexionBD->liste_valeur_par_clef("select idf,nom from type_acte where idf not in(" . IDF_NAISSANCE . ',' . IDF_MARIAGE . ',' . IDF_DECES . ',' . IDF_RECENS . ") order by nom");
        unset($a_types_acte[IDF_UNION]);
        print('<div class="panel panel-primary">');
        print('<div class="panel-heading">Suppression des donn&eacute;es d\'une commune/paroisse</div>');
        print('<div class="panel-body">');
        print("<form enctype=\"multipart/form-data\" id=\"formulaire_suppression\"  method=\"post\">");
        print('<input type="hidden" name="mode" value="SUPPRESSION">');
        print('<div class="form-row col-md-12">');
        print('<label for="idf_source" class="col-form-label col-md-2 col-md-offset-3">Source:</label>');
        print('<div class="col-md-4">');
        print('<select name=idf_source id=idf_source class="js-select-avec-recherche form-control">');
        print(chaine_select_options($gi_idf_source, $a_sources));
        print('</select></div></div>');
        print('<div class="form-row col-md-12">');
        print('<label for="idf_commune_acte" class="col-form-label col-md-2 col-md-offset-3">Commune:</label>');
        print('<div class="col-md-4">');
        print('<select name=idf_commune_acte id=idf_commune_acte class="js-select-avec-recherche form-control">');
        print(chaine_select_options($gi_idf_commune_acte, $a_communes_acte));
        print('</select></div></div>');
        print('<div class="form-row col-md-12">');
        print('<label for="col_type_acte" class="col-form-label col-md-2 col-md-offset-3">Type d\'acte:</label>');
        print('<div class="col-md-4 input-group" id="col_type_acte">');
        print('<label class="radio">');
        print('<input value="1" type="radio" name="type_acte" id="type_naissance" >Bapt&ecirc;me/Naissance');
        print('</label>');
        print('<label class="radio">');
        print('<input value="2" type="radio" name="type_acte" id="type_mariage" checked="checked">Mariage');
        print('</label>');
        print('<label class="radio">');
        print('<input value="3" type="radio" name="type_acte" id="type_deces" >S&eacute;pulture/D&eacute;c&eacute;s');
        print('</label>');
        print('<label class="radio">');
        print('<input value="4" type="radio" name="type_acte" id="type_divers" >Tous divers Nim&egrave;gue');
        print('</label>');
        print('<label class="radio">');
        print('<input value="5" type="radio" name="type_acte" id="type_recens" >Recensement');
        print('</label>');
        print('<label class="radio">');
        print('<div class="input-group">');
        print('<input value="6" type="radio" name="type_acte" id="type_autre" >');
        print('<span class="input-group-addon">Autre:</span>');
        print('<select name=autre_type_acte id=autre_type_acte class="js-select-avec-recherche form-control">');
        print(chaine_select_options($gc_idf_type_acte, $a_types_acte));
        print('</select>');
        print('</div>');
        print('</label>');
        print('<input type="hidden" name="idf_type_acte" id="idf_type_acte" value="' . IDF_MARIAGE . '">');
        print('</div></div>');
        print('<div class="form-row col-md-12">');
        print('<label for="annee_min" class="col-form-label col-md-2 col-md-offset-3">Annee minimale:</label>');
        print('<div class="col-md-4">');
        print('<input type=text name=annee_min id=annee_min size=4 maxlength=4 class="form-control"></div></div>');
        print('<div class="form-row col-md-12">');
        print('<label for="annee_max" class="col-form-label col-md-2 col-md-offset-3">Annee maximale:</label>');
        print('<div class="col-md-4">');
        print('<input type=text name=annee_max id=annee_max size=4 maxlength=4 class="form-control"></div></div>');
        print('<div class="form-row">');
        print('<button type=submit class="btn btn-danger col-md-offset-4 col-md-4"><span class="glyphicon glyphicon-trash"> Supprimer les actes</button>');
        print('</div>');
        print('</form></div></div>');
        break;
    case 'SUPPRESSION':
        $_SESSION['idf_commune_acte'] = $gi_idf_commune_acte;
        $i_nb_permaliens = nombre_permaliens($gi_idf_commune_acte, $gc_idf_type_acte);
        if ($i_nb_permaliens != 0) {
            print("<div class=\"alert alert-danger\">");
            print("Confirmation des suppressions:<br>");
            print("$i_nb_permaliens permalien(s) r&eacute;f&eacute;renc&eacute;(s)</div>");
            print("<form id=\"suppression\"  method=\"post\">");
            print('<input type="hidden" name="mode" value="SUPPRESSION_CONFIRMEE" >');
            print("<input type=\"hidden\" name=\"idf_source\" value=$gi_idf_source >");
            print("<input type=\"hidden\" name=\"idf_commune_acte\" value=$gi_idf_commune_acte >");
            print("<input type=\"hidden\" name=\"idf_type_acte\" value=$gc_idf_type_acte >");
            print("<input type=\"hidden\" name=\"annee_min\" value=$gi_annee_min >");
            print("<input type=\"hidden\" name=\"annee_max\" value=$gi_annee_max >");
            print('<div class="form-row">');
            print('<button type=submit class="btn btn-danger col-md-offset-4 col-md-4"><span class="glyphicon glyphicon-trash"> Confirmer la suppression</button>');
            print('</div>');
            print('</form>');
            print("<form  method=\"post\" >");
            print('<input type="hidden" name="mode" value="FORMULAIRE" />');
            print('<div class="form-row">');
            print('<button type=submit class="btn btn-primary col-md-offset-4 col-md-4">Annuler</button>');
            print('</div>');
            break;
        }

    case 'SUPPRESSION_CONFIRMEE':
        print("<form id=\"suppression_confirmee\"  method=\"post\">");
        print('<input type="hidden" name="mode" value="FORMULAIRE" />');
        print("<div class=\"align-center\">");
        print("Suppression des statistiques<br>");
        $i_temps_courant = time();
        if ($gc_idf_type_acte != 'DIV') {
            $connexionBD->execute_requete("delete from stats_commune where idf_source=$gi_idf_source and idf_commune=$gi_idf_commune_acte and idf_type_acte=$gc_idf_type_acte");
            $connexionBD->execute_requete("delete from stats_patronyme where idf_source=$gi_idf_source and idf_commune=$gi_idf_commune_acte and idf_type_acte=$gc_idf_type_acte");
            print("Dur&eacute;e: " . (time() - $i_temps_courant) . " s<br>");
            $i_temps_courant = time();

            print("Suppression des personnes<br>");

            $st_requete = "delete personne from personne,acte  where personne.idf_acte=acte.idf and idf_source=$gi_idf_source and idf_commune=$gi_idf_commune_acte and idf_type_acte=$gc_idf_type_acte";
            if ($gi_annee_min != '')
                $st_requete .= " and annee>=$gi_annee_min";
            if ($gi_annee_max != '')
                $st_requete .= " and annee<=$gi_annee_max";
            $connexionBD->execute_requete($st_requete);
            print("Dur&eacute;e: " . (time() - $i_temps_courant) . " s<br>");
            $i_temps_courant = time();

            print("Suppression des unions<br>");
            $st_requete = "delete u from `union` u,  acte a  where u.idf_acte=a.idf and u.idf_source=$gi_idf_source and u.idf_commune=$gi_idf_commune_acte and u.idf_type_acte=$gc_idf_type_acte";
            if ($gi_annee_min != '')
                $st_requete .= " and a.annee>=$gi_annee_min";
            if ($gi_annee_max != '')
                $st_requete .= " and a.annee<=$gi_annee_max";
            $connexionBD->execute_requete($st_requete);

            print("Dur&eacute;e: " . (time() - $i_temps_courant) . " s<br>");
            $i_temps_courant = time();
            print("Suppression des actes<br>");
            $st_requete = "delete from acte where idf_source=$gi_idf_source and idf_commune=$gi_idf_commune_acte and idf_type_acte=$gc_idf_type_acte";
            if ($gi_annee_min != '')
                $st_requete .= " and annee>=$gi_annee_min";
            if ($gi_annee_max != '')
                $st_requete .= " and annee<=$gi_annee_max";

            $connexionBD->execute_requete($st_requete);

            print("Dur&eacute;e: " . (time() - $i_temps_courant) . " s<br>");
            print("Nombre d'actes d&eacute;truits: " . $connexionBD->nb_lignes_affectees() . "<br><br>");
            $i_temps_courant = time();
            if ($gi_annee_min != '' || $gi_annee_max != '') {
                print("Recalcul des statistiques<br>");
                $st_requete = "insert into `stats_patronyme` (idf_patronyme,idf_commune,idf_type_acte,idf_source,annee_min,annee_max,nb_personnes) select pat.idf,$gi_idf_commune_acte,$gc_idf_type_acte,$gi_idf_source,min(a.annee),max(a.annee),count(p.patronyme) from personne p join patronyme pat on (p.patronyme=pat.libelle) join acte a on (p.idf_acte=a.idf) where a.idf_commune=$gi_idf_commune_acte and a.idf_type_acte=$gc_idf_type_acte and a.idf_source=$gi_idf_source and a.annee!=0 and a.annee!=9999 group by p.patronyme,a.idf_commune,a.idf_type_acte,a.idf_source ";
                $connexionBD->execute_requete($st_requete);
                $st_requete = "insert into `stats_commune` (idf_commune,idf_type_acte,idf_source,annee_min,annee_max,nb_actes) select $gi_idf_commune_acte,$gc_idf_type_acte,$gi_idf_source,min(a.annee),max(a.annee),count(a.idf) from acte a where a.idf_commune=$gi_idf_commune_acte and a.idf_type_acte=$gc_idf_type_acte and a.idf_source=$gi_idf_source and a.annee!=0 and a.annee!=9999 group by a.idf_commune";
                $connexionBD->execute_requete($st_requete);
                print("Dur&eacute;e: " . (time() - $i_temps_courant) . " s<br>");
            }
        } else {
            $connexionBD->execute_requete("delete from stats_commune where idf_source=$gi_idf_source and idf_commune=$gi_idf_commune_acte and idf_type_acte not in $gst_types_acte");
            $connexionBD->execute_requete("delete from stats_patronyme where idf_source=$gi_idf_source and idf_commune=$gi_idf_commune_acte and idf_type_acte not in $gst_types_acte");
            print("Dur&eacute;e: " . (time() - $i_temps_courant) . " s<br>");
            $i_temps_courant = time();

            print("Suppression des personnes<br>");
            $st_requete = "delete personne from personne, acte where personne.idf_acte=acte.idf and idf_source=$gi_idf_source and idf_commune=$gi_idf_commune_acte and idf_type_acte not in $gst_types_acte";
            if ($gi_annee_min != '')
                $st_requete .= " and annee>=$gi_annee_min";
            if ($gi_annee_max != '')
                $st_requete .= " and annee<=$gi_annee_max";
            $connexionBD->execute_requete($st_requete);
            print("Dur&eacute;e: " . (time() - $i_temps_courant) . " s<br>");
            $i_temps_courant = time();

            print("Suppression des unions<br>");
            $st_requete = "delete u from `union` u, acte a where u.idf_acte=a.idf and u.idf_source=$gi_idf_source and u.idf_commune=$gi_idf_commune_acte and u.idf_type_acte not in $gst_types_acte";
            if ($gi_annee_min != '')
                $st_requete .= " and a.annee>=$gi_annee_min";
            if ($gi_annee_max != '')
                $st_requete .= " and a.annee<=$gi_annee_max";
            $connexionBD->execute_requete($st_requete);

            print("Dur&eacute;e: " . (time() - $i_temps_courant) . " s<br>");
            $i_temps_courant = time();
            print("Suppression des actes<br>");
            $st_requete = "delete from acte where idf_source=$gi_idf_source and idf_commune=$gi_idf_commune_acte and idf_type_acte not in $gst_types_acte";
            if ($gi_annee_min != '')
                $st_requete .= " and annee>=$gi_annee_min";
            if ($gi_annee_max != '')
                $st_requete .= " and annee<=$gi_annee_max";
            $connexionBD->execute_requete($st_requete);

            print("Dur&eacute;e: " . (time() - $i_temps_courant) . " s<br>");
            print("Nombre d'actes d&eacute;truits: " . $connexionBD->nb_lignes_affectees() . "<br><br>");
            $i_temps_courant = time();
            if ($gi_annee_min != '' || $gi_annee_max != '') {
                print("Recalcul des statistiques<br>");
                $st_requete = "insert into `stats_patronyme` (idf_patronyme,idf_commune,idf_type_acte,idf_source,annee_min,annee_max,nb_personnes) select pat.idf,$gi_idf_commune_acte,a.idf_type_acte,$gi_idf_source,min(a.annee),max(a.annee),count(p.patronyme) from personne p join patronyme pat on (p.patronyme=pat.libelle) join acte a on (p.idf_acte=a.idf) where a.idf_commune=$gi_idf_commune_acte and a.idf_type_acte not in $gst_types_acte and a.idf_source=$gi_idf_source and a.annee!=0 and a.annee!=9999 group by p.patronyme,a.idf_commune,a.idf_type_acte,a.idf_source ";
                $connexionBD->execute_requete($st_requete);
                $st_requete = "insert into `stats_commune` (idf_commune,idf_type_acte,idf_source,annee_min,annee_max,nb_actes) select $gi_idf_commune_acte,a.idf_type_acte,$gi_idf_source,min(a.annee),max(a.annee),count(a.idf) from acte a where a.idf_commune=$gi_idf_commune_acte and a.idf_type_acte not in $gst_types_acte and a.idf_source=$gi_idf_source and a.annee!=0 and a.annee!=9999 group by a.idf_commune";
                $connexionBD->execute_requete($st_requete);
                print("Dur&eacute;e: " . (time() - $i_temps_courant) . " s<br>");
            }
        }
        print('<div class="form-row">');
        print('<button type=submit class="btn btn-primary col-md-offset-4 col-md-4">Menu Suppression</button>');
        print('</div>');
        print("</form>");

        break;
    default:
        print("mode $gst_mode inconnu");
}

print("</div></body></html>");
