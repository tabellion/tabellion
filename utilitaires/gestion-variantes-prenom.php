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
if (!in_array('UTILITAIRE', $user['privileges'])) {
    header('HTTP/1.0 401 Unauthorized');
    exit;
}

$gst_get_mode = empty($_GET['mode']) ? 'AFFICHER' : $_GET['mode'];
$gst_mode = empty($_POST['mode']) ? $gst_get_mode : $_POST['mode'];
$gi_idf_groupe = empty($_POST['idf_groupe']) ? '' : $_POST['idf_groupe'];

switch ($gst_mode) {
    case 'EXPORT':

        header("Content-type: text/csv");
        header("Expires: 0");
        header("Pragma: public");
        header("Content-disposition: attachment; filename=\"groupe_prenoms.csv\"");
        $fh = @fopen('php://output', 'w');
        $a_liste_prenoms = $connexionBD->sql_select_multiple("select idf_groupe, libelle from variantes_prenom order by idf_groupe");
        foreach ($a_liste_prenoms as $i_idf_groupe => $a_variantes) {
            fputcsv($fh, $a_variantes, SEP_CSV);
        }
        fclose($fh);
        exit();
        break;
}


/**
 * Affiche le menu formulaire
 * @param object $pconnexionBD  Connexion à la base
 * @param integer $pi_idf_groupe Identifiant du groupe à ajouter 
 */
function affiche_menu($pconnexionBD, $pi_idf_groupe)
{
    global $gst_infos, $gst_erreurs;
    print('<form id="variantes_prenom" method="post">');

    if (!empty($gst_infos))
        print("<div id=\"infos\" class=\"alert alert-success\">$gst_infos</div>");
    if (!empty($gst_erreurs))
        print("<div id=\"erreurs\" class=\"alert alert-danger\">$gst_erreurs</div>");

    print('<div class="row col-md-12">');

    print('<div class="col-md-5">');

    print("<div id=\"cmt_retour\" ></div>");

    print("<label for=\"variante\" class=\"col-form-label col-md-2\">Variante &agrave; chercher:</label>");
    print("<div class='col-md-10'>");
    print("<input type=text name=\"variante\" id=\"variante\" value=\"\" class=\"form-control\" size=30>");
    print("</div>");

    print('<input type="hidden" name="mode" id="mode" value="EXPORT">');
    print("<input type=\"hidden\" name=\"idf_groupe\" id=\"idf_groupe\" value=\"$pi_idf_groupe\">");

    print('<div class="row col-md-12">');
    print("<textarea rows=20 cols=40 id=\"variantes\" name=\"variantes\" class=\"form-control col-md-8\">");
    if (!empty($pi_idf_groupe)) {
        $a_variantes = $pconnexionBD->sql_select("select libelle from variantes_prenom where idf_groupe=$pi_idf_groupe order by libelle");
        foreach ($a_variantes as $st_variante) {
            print(cp1252_vers_utf8($st_variante) . "\n");
        }
    }
    print("</textarea>");
    print("</div>");

    print("</div>"); // fin premier tiers

    print('<div class="col-md-3">');
    print('<div class="btn-group-vertical">');
    print("<button type=\"button\" id=\"creer\" class=\"btn btn-primary\"><span class=\"glyphicon glyphicon-plus\"></span>  Cr&eacute;er</button>");
    print("<button type=\"button\" id=\"modifier\" class=\"btn btn-primary\"><span class=\"glyphicon glyphicon-edit\"></span> Modifier</button>");
    print("<button type=\"button\" id=\"completer\" class=\"btn btn-primary\"><span class=\"glyphicon glyphicon-transfer\"></span> Compl&eacute;ter</button>");
    print("<button type=\"button\" id=\"supprimer\" class=\"btn btn-danger\"><span class=\"glyphicon glyphicon-trash\"></span> Supprimer</button>");
    print("<button type=\"button\" id=\"fusionner\" class=\"btn btn-warning\"><span class=\"glyphicon glyphicon-arrow-left\"></span>  Fusionner</button>");
    print("<button type=\"button\" id=\"vider\" class=\"btn btn-warning\"><span class=\"glyphicon glyphicon-erase\"></span> Vider</button>");
    print("<button type=\"button\" id=\"exporter\" class=\"btn btn-primary\"><span class=\"glyphicon glyphicon glyphicon-download-alt\"></span> Exporter les variantes</button>");
    print("<button type=\"button\" id=\"supprimer_inutilises\" class=\"btn btn-warning\"><span class=\"glyphicon glyphicon glyphicon-trash\"></span> Supprimer les pr&eacute;noms inutilis&eacute;s</button>");
    $a_prenoms = $pconnexionBD->sql_select("select libelle from prenom order by idf desc limit 20");
    if (count($a_prenoms) > 0) {
        print("<table class=\"table table-bordered table-striped\">");
        print("<tr><th>20 derniers<br>nouveaux pr&eacute;noms</th></tr>");
        foreach ($a_prenoms as $st_prenom) {
            print("<tr><td>" . cp1252_vers_utf8($st_prenom) . "</td></tr>");
        }
        print("</table>");
    }
    print("</div>");
    print("</div>"); // fin second tiers

    print('<div class="col-md-4">');
    print("<div id=\"cmt_retour_a_fusionner\" ></div>");
    print("<div id=\"cmt_retour_a_fusionner\" ></div>");

    print('<label for="variante_a_fusionner" class="col-form-label col-md-2">Variante &agrave; chercher (fusion):</label>');
    print('<div class="col-md-10">');
    print("<input type=text name=\"variante_a_fusionner\" id=\"variante_a_fusionner\" value=\"\" size=30 class=\"form-control\">");
    print('</div>');

    print("<input type=\"hidden\" name=\"idf_groupe_a_fusionner\" id=\"idf_groupe_a_fusionner\" value=\"\">");
    print('<div class="row col-md-12">');
    print("<textarea rows=20 cols=40 id=\"variantes_a_fusionner\" name=\"variantes_a_fusionner\" class=\"form-control col-md-8\"></textarea>");
    print("</div>");

    print("</div>"); // fin 3ème tiers
    print("</div>"); // fin ligne
    print('</form>');
}

/*
* Ajoute les variantes au groupe sélectionnées
* @param object $pconnexionBD  Connexion à la base
* @param integer $pi_idf_groupe identifiant du groupe
* @param array $pa_variantes liste des variantes à ajouter 
*/
function ajoute_variantes($pconnexionBD, $pi_idf_groupe, $pa_variantes)
{
    global $gst_infos, $gst_erreurs;
    $a_valeurs = array();
    $a_params = array();
    $i = 0;
    foreach ($pa_variantes as $st_variante) {
        $st_variante = ucfirst(strtolower(trim($st_variante)));
        if ($st_variante == "") continue;
        $a_params_precedents = $pconnexionBD->params();
        $pconnexionBD->initialise_params(array(':variante' => utf8_vers_cp1252($st_variante)));
        $i_nb_variantes = $pconnexionBD->sql_select1("select count(idf_groupe) from variantes_prenom where libelle = :variante collate latin1_general_ci");
        $pconnexionBD->initialise_params($a_params_precedents);
        if ($i_nb_variantes > 0)
            $gst_erreurs .= "Variante $st_variante d&eacute;j&agrave; r&eacute;f&eacute;renc&eacute;e. Elle ne sera pas ajout&eacute;e<br>";
        else {
            $a_params[":prenom$i"] = utf8_vers_cp1252($st_variante);
            $a_valeurs[] = sprintf("(%d,:prenom%d)", $pi_idf_groupe, $i);
            $i++;
        }
    }
    if (count($a_valeurs) > 0) {
        $st_requete = "insert variantes_prenom(idf_groupe,libelle) values ";
        $st_valeurs = join(',', $a_valeurs);
        $st_requete .= $st_valeurs;
        $pconnexionBD->ajoute_params($a_params);
        $pconnexionBD->execute_requete($st_requete);
    } else
        $gst_erreurs .= "Aucune variante cr&eacute;e<br>";
}

/*
* Affiche les variantes à compléter
* @param object $pconnexionBD Connexion à la base
* @param integer $pi_idf_groupe identifiant du groupe
*/
function affiche_menu_completer($pconnexionBD, $pi_idf_groupe)
{
    $i_precision = 5;
    $st_requete = "select ps1.libelle from prenom_simple ps1 left join variantes_prenom vp2 on (ps1.libelle=vp2.libelle collate latin1_general_cs) where round(phonex*pow(10,$i_precision)) in (select distinct round (phonex*pow(10,$i_precision)) from variantes_prenom vp join prenom_simple ps2 on (vp.libelle=ps2.libelle collate latin1_general_cs) where vp.idf_groupe=$pi_idf_groupe) and vp2.idf_groupe is null ";
    $a_prenoms = $pconnexionBD->sql_select($st_requete);
    if (count($a_prenoms) == 0) {
        print('<div id="erreur_variantes" class="alert alert-danger">Pas de variantes trouv&eacute;es</div>');
        print('<button type="button" id="annuler" class="btn btn-primary col-md-4 col-md-offset-4"><span class="glyphicon glyphicon-home"></span> Retourner au menu</button>');
    } else {
        print('<form id="menu_completer" method="post" action="' . basename(__FILE__) . '">');
        print('<input type="hidden" name="mode" id="mode" value="COMPLETER">');
        print("<input type=\"hidden\" name=\"idf_groupe\" id=\"idf_groupe\" value=\"$pi_idf_groupe\">");
        print('<table class="table table-bordered table-striped">');
        foreach ($a_prenoms as $st_prenom) {
            $st_prenom = cp1252_vers_utf8($st_prenom);
            print("<tr><td>$st_prenom</td><td><div class=\"lib_erreur\"><div class=\"checkbox\"><label><input type=checkbox id=\"st_prenom\" name=\"variantes[]\" class=\"form-check-input\" value=\"$st_prenom\"></label></div></div></td></tr>\n");
        }
        print('</table>');
        print('<div class="btn-group col-md-8 col-md-offset-2" role="group">');
        print('<button type="submit" class="btn btn-primary col-md-4 col-md-offset-4"><span class="glyphicon glyphicon-ok"></span> Compl&eacute;ter</button>');
        print('<button type="button" id="annuler" class="btn btn-primary col-md-4 col-md-offset-4"><span class="glyphicon glyphicon-home"></span> Retourner au menu</button>');
        print('</div>');
        print('</form>');
    }
}

print('<!DOCTYPE html>');
print("<head>");
print('<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" >');
print('<title>Base ' . SIGLE_ASSO . ': Gestion des variantes Prenom</title>');
print('<meta name="viewport" content="width=device-width, initial-scale=1.0">');
print("<link href='../assets/css/styles.css' type='text/css' rel='stylesheet'>");
print("<link href='../assets/css/bootstrap.min.css' rel='stylesheet'>");
print("<link href='../assets/css/jquery-ui.css' type='text/css' rel='stylesheet'>");
print("<link href='../assets/css/jquery-ui.structure.min.css' type='text/css' rel='stylesheet'>");
print("<link href='../assets/css/jquery-ui.theme.min.css' type='text/css' rel='stylesheet'> ");
print("<script src='../assets/js/jquery-min.js' type='text/javascript'></script>");
print("<script src='../assets/js/jquery.validate.min.js' type='text/javascript'></script>");
print("<script src='../assets/js/additional-methods.min.js' type='text/javascript'></script>");
print("<script src='../assets/js/jquery-ui.min.js' type='text/javascript'></script>");
print("<script src='../assets/js/bootstrap.min.js' type='text/javascript'></script>");
print('<link rel="shortcut icon" href="images/favicon.ico">');
?>
<script type='text/javascript'>
    $.validator.addMethod('require-one', function(value) {
        if ($('#resp01-sim').is(':checked')) {
            return $('.require-one:checked').size() > 0;
        } else {
            return true;
        }
    }, 'Sélectionner au moins une case');

    $(document).ready(function() {

        $('#annuler').click(function() {
            window.location.href = '<?php echo basename(__FILE__) ?>';
        });


        function maj_variantes(term, variantes, idf_groupe, cmt_retour) {
            $.ajax({
                url: "/ajax/variantes_prenom.php",
                type: "GET",
                data: 'term=' + term,
                dataType: 'json',
                cache: false,
                timeout: 1000,
                success: function(reponse) {
                    var nb_reponses = reponse['nb_reponses'];
                    $(cmt_retour).html('');
                    if (nb_reponses == 1)
                        $(cmt_retour).append('<span class="badge badge-success">' + nb_reponses + '</span> groupes trouve(s)');
                    else
                        $(cmt_retour).append('<span class="badge badge-primary">' + nb_reponses + '</span> groupes trouve(s)');
                    if (nb_reponses == 1) {
                        var liste_variantes = '';
                        $.each(reponse['variantes'], function(index, valeur) {
                            liste_variantes = liste_variantes + valeur + "\n";
                        });
                        $(variantes).html('');
                        $(variantes).append(liste_variantes);
                        $(idf_groupe).val(reponse['idf_groupe']);
                    }
                }
            });
        }
        $('#variante').autocomplete({
            minLength: 3,
            source: function(request, response) {
                $.ajax({
                    url: "/ajax/variantes_prenom.php",
                    dataType: "json",
                    data: {
                        term: request.term
                    },
                    success: function(reponse) {
                        response(reponse['variantes']);
                        if (reponse['nb_reponses'] == 1) {
                            var variantes = '';
                            $.each(reponse['variantes'], function(index, valeur) {
                                variantes = variantes + valeur + "\n";
                            });
                            $('#variantes').html('');
                            $('#variantes').append(variantes);
                            $('#idf_groupe').val(reponse['idf_groupe']);
                        }

                    }
                })
            },
            select: function(event, ui) {
                $('#variante').val(ui.item.value);
                maj_variantes($('#variante').val(), '#variantes', '#idf_groupe', '#cmt_retour');
                return false;

            }
        });

        $("#variante").keyup(function() {
            maj_variantes($('#variante').val(), '#variantes', '#idf_groupe', '#cmt_retour');
        });

        $('#variante_a_fusionner').autocomplete({
            minLength: 3,
            source: function(request, response) {
                $.ajax({
                    url: "../ajax/variantes_prenom.php",
                    dataType: "json",
                    data: {
                        term: request.term
                    },
                    success: function(reponse) {
                        console.log(reponse);
                        response(reponse['variantes']);
                        if (reponse['nb_reponses'] == 1) {
                            var variantes = '';
                            $.each(reponse['variantes'], function(index, valeur) {
                                variantes = variantes + valeur + "\n";
                            });
                            $('#variantes_a_fusionner').html('');
                            $('#variantes_a_fusionner').append(variantes);
                            $('#idf_groupe_a_fusionner').val(reponse['idf_groupe']);
                        }
                    }
                })
            },
            select: function(event, ui) {
                $('#variante_a_fusionner').val(ui.item.value);
                maj_variantes($('#variante_a_fusionner').val(), '#variantes_a_fusionner', '#idf_groupe_a_fusionner', '#cmt_retour_a_fusionner');
                return false;

            }
        });

        $("#variante_a_fusionner").keyup(function() {
            maj_variantes($('#variante_a_fusionner').val(), '#variantes_a_fusionner', '#idf_groupe_a_fusionner', '#cmt_retour_a_fusionner');
        });

        $("#variantes_prenom").validate({
            rules: {
                variantes: {
                    required: {
                        depends: function(element) {
                            return $("#mode").val() != 'SUPPRIMER' && $("#mode").val() != 'SUPPRIMER_INUTILISES';
                        }
                    }
                },
                variante_a_fusionner: {
                    required: {
                        depends: function(element) {
                            return $("#mode").val() == 'FUSIONNER';
                        }
                    }
                },
            },
            messages: {
                variantes: {
                    required: "Pas de variantes d&eacute;finies"
                },
                variante_a_fusionner: {
                    required: "Aucune variante &agrave; fusionner s&eacute;lectionn&eacute;e"
                }
            },
            submitHandler: function(form) {
                // do other things for a valid form
                switch ($("#mode").val()) {
                    case "SUPPRIMER":
                        if (confirm('Voulez-vous supprimer ce groupe ?')) {
                            form.submit();
                        }
                        break;
                    case "FUSIONNER":
                        if (confirm('Voulez-vous fusionner ces groupes ?')) {
                            form.submit();
                        }
                        break;
                    default:
                        form.submit();
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

        $("#modifier").click(function() {
            $("#mode").val('MODIFIER');
            $("#variantes_prenom").submit();
        });

        $("#completer").click(function() {
            $("#mode").val('MENU_COMPLETER');
            $("#variantes_prenom").submit();
        });

        $("#creer").click(function() {
            $("#mode").val('CREER');
            $("#variantes_prenom").submit();
        });

        $("#supprimer").click(function() {
            $("#mode").val('SUPPRIMER');
            $("#variantes_prenom").submit();
        });

        $("#fusionner").click(function() {
            $("#mode").val('FUSIONNER');
            $("#variantes_prenom").submit();
        });

        $("#menu_completer").validate({
            rules: {
                "variantes[]": {
                    required: true,
                    minlength: 1
                }
            },
            messages: {
                'variantes[]': 'Choisir au moins un pr&eacute;nom'
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
            }

        });

        $("#vider").click(function() {
            $('#idf_groupe').val('');
            $('#variante').val('');
            $('#variantes').html('');
            $('#idf_groupe_a_fusionner').val('');
            $('#variante_a_fusionner').val('');
            $('#variantes_a_fusionner').html('');
        });

        $("#exporter").click(function() {
            window.location.href = '<?php echo basename(__FILE__) ?>?mode=EXPORT';
        });

        $('#annuler').click(function() {
            window.location.href = '<?php echo basename(__FILE__)
                                    ?>';
        });

        $("#supprimer_inutilises").click(function() {
            $("#mode").val('SUPPRIMER_INUTILISES');
            $("#variantes_prenom").submit();
        });
    });
</script>
<?php
print("</head>");
print("<body>");
print('<div class="container">');

require_once __DIR__ . '/../commun/menu.php';

switch ($gst_mode) {
    case 'AFFICHER':
        affiche_menu($connexionBD, $gi_idf_groupe);
        break;
    case 'CREER':
        $st_variantes = isset($_POST['variantes']) ? trim($_POST['variantes']) : '';
        $a_variantes = explode("\n", $st_variantes);
        $i_idf_groupe = $connexionBD->sql_select1("select max(idf_groupe) from variantes_prenom");
        $i_idf_groupe++;
        ajoute_variantes($connexionBD, $i_idf_groupe, $a_variantes);
        if (empty($gst_erreurs)) $gst_infos = "Variante ajout&eacute;e";
        affiche_menu($connexionBD, $i_idf_groupe);
        break;
    case 'MODIFIER':
        $st_variantes = isset($_POST['variantes']) ? trim($_POST['variantes']) : '';
        $a_variantes = explode("\n", $st_variantes);
        $i_idf_groupe = isset($_POST['idf_groupe']) ? (int) $_POST['idf_groupe'] : null;
        if (isset($i_idf_groupe)) {
            $connexionBD->execute_requete("delete from variantes_prenom where idf_groupe=$i_idf_groupe");
            $gst_infos = "Variante modifi&eacute;e";
            if (empty($gst_erreurs)) ajoute_variantes($connexionBD, $i_idf_groupe, $a_variantes);
            affiche_menu($connexionBD, $i_idf_groupe);
        }
        break;
    case 'MENU_COMPLETER':
        $i_idf_groupe = isset($_POST['idf_groupe']) ? (int) $_POST['idf_groupe'] : null;
        if (isset($i_idf_groupe))
            affiche_menu_completer($connexionBD, $i_idf_groupe);
        break;
    case 'COMPLETER':
        $i_idf_groupe = isset($_POST['idf_groupe']) ? (int) $_POST['idf_groupe'] : null;
        $a_variantes = isset($_POST['variantes']) ? ($_POST['variantes']) : array();
        if (isset($i_idf_groupe) && count($a_variantes) > 0) {
            ajoute_variantes($connexionBD, $i_idf_groupe, $a_variantes);
            if (empty($gst_erreurs)) $gst_infos = "Variante compl&eacute;t&eacute;e";
            affiche_menu($connexionBD, $i_idf_groupe);
        } else {
            if (!isset($i_idf_groupe))
                $gst_erreurs = "Le groupe n'est pas d&eacute;fini";
            else if (count($a_variantes) == 0)
                $gst_erreurs = "La liste de variantes est vide";
            affiche_menu($connexionBD, $i_idf_groupe);
        }
        break;
    case 'SUPPRIMER':
        $i_idf_groupe = isset($_POST['idf_groupe']) ? (int) $_POST['idf_groupe'] : null;
        if (isset($i_idf_groupe)) {
            $connexionBD->execute_requete("delete from variantes_prenom where idf_groupe=$i_idf_groupe");
            if (empty($gst_erreurs)) $gst_infos = "Variante supprim&eacute;e";
            affiche_menu($connexionBD, $i_idf_groupe);
        }
        break;
    case 'FUSIONNER':
        $i_idf_groupe = isset($_POST['idf_groupe']) ? (int) $_POST['idf_groupe'] : '';
        $i_idf_groupe_a_fusionner = isset($_POST['idf_groupe_a_fusionner']) ? (int) $_POST['idf_groupe_a_fusionner'] : '';
        $connexionBD->execute_requete("update variantes_prenom set idf_groupe=$i_idf_groupe where idf_groupe=$i_idf_groupe_a_fusionner");
        if (empty($gst_erreurs)) $gst_infos = "Variantes fusionn&eacute;es";
        affiche_menu($connexionBD, $i_idf_groupe);
        break;
    case 'SUPPRIMER_INUTILISES':
        $a_prenoms_a_supprimer = $connexionBD->sql_select("select idf FROM `prenom` WHERE idf not in (select idf_prenom from `personne`)");
        if (count($a_prenoms_a_supprimer) > 0) {
            $st_prenoms_a_supprimer = join(',', $a_prenoms_a_supprimer);
            $connexionBD->execute_requete("delete FROM `prenom` WHERE idf in ($st_prenoms_a_supprimer)");
            $connexionBD->execute_requete("delete FROM `groupe_prenoms` WHERE idf_prenom not in (select idf from `prenom`)");
            $a_prenoms_simples_a_supprimer = $connexionBD->sql_select("select idf FROM `prenom_simple` WHERE idf not in (select idf_prenom_simple from `groupe_prenoms`)");
            if (count($a_prenoms_simples_a_supprimer) > 0) {
                $st_prenoms_simples_a_supprimer = join(',', $a_prenoms_simples_a_supprimer);
                $connexionBD->execute_requete("delete FROM `prenom_simple` WHERE idf in('$st_prenoms_simples_a_supprimer')");
            }
            $connexionBD->execute_requete("delete FROM `variantes_prenom` WHERE libelle not in (select libelle from `prenom_simple`)");
            if (empty($gst_erreurs)) $gst_infos = "Pr&eacute;noms inutilis&eacute;s supprim&eacute;s";
        } else
            $gst_infos = "Pas de pr&eacute;noms &agrave; supprimer";
        affiche_menu($connexionBD, $gi_idf_groupe);

        break;
    default:
        print("<div id=\"erreurs\" class=\"alert alert-danger\">Mode $gst_mode inconnu</div>");
}

print("</div>");
print("</body>");
print("</html>");
