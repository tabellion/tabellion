<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------
require_once __DIR__ . '/app/bootstrap.php';

$gst_type_recherche         = isset($_GET['recherche']) ? $_GET['recherche'] : '';


$gi_idf_source        = $_GET['idf_src'] ?? '0';
$gi_idf_commune       = $_GET['idf_ca'] ?? '0';
$gi_rayon             = '';
$gi_idf_type_acte     = $_GET['idf_ta'] ?? '0';
$gi_annee_min         = $_GET['a_min'] ?? '';
$gi_annee_max         = $_GET['a_max'] ?? '';

$gst_nom_epx          = '';
$gst_prenom_epx       = '';
$gst_variantes_epx    = 'oui';
$gst_nom_epse         = '';
$gst_prenom_epse      = '';
$gst_variantes_epse   = 'oui';
$gi_idf_type_presence = '0';
$gst_sexe             = '0';
$gst_nom              = $_GET['nom'] ?? '';
$gst_prenom           = '';
$gst_variantes        = isset($_GET['var']) && $_GET['var'] == 'N' ? '' : 'oui';
$gst_paroisses_rattachees = 'oui';
$gst_commentaires     = '';

$gst_releve_mois_min  = '';
$gst_releve_annee_min   = '';
$gst_releve_mois_max  = '';
$gst_releve_annee_max   = '';
$gst_releve_type         = 0;
$gst_releve_tous_patronymes = '';


$a_communes_acte = $connexionBD->liste_valeur_par_clef("SELECT idf, nom FROM commune_acte ORDER BY nom");
$a_types_acte = $connexionBD->liste_valeur_par_clef("SELECT idf, nom FROM type_acte ORDER BY nom");
$a_types_presence = $connexionBD->liste_valeur_par_clef("SELECT idf, nom FROM type_presence ORDER BY nom");
$a_types_presence[0] = 'Toutes';
$a_sources = $connexionBD->liste_valeur_par_clef("SELECT idf, nom FROM source ORDER BY nom");

print('<!DOCTYPE html><html lang="fr">');
print("<head>");
print('<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" >');
print('<meta http-equiv="content-language" content="fr"> ');
print('<title>Base ' . SIGLE_ASSO . ': Vos recherches</title>');
print('<meta name="viewport" content="width=device-width, initial-scale=1.0">');
print("<link href='assets/css/styles.css' type='text/css' rel='stylesheet'>");
print("<link href='assets/css/bootstrap.min.css' rel='stylesheet'>");
print("<link href='assets/css/jquery-ui.css' type='text/css' rel='stylesheet'>");
print("<link href='assets/css/jquery-ui.structure.min.css' type='text/css' rel='stylesheet'>");
print("<link href='assets/css/jquery-ui.theme.min.css' type='text/css' rel='stylesheet'> ");
print("<link href='assets/css/select2.min.css' type='text/css' rel='stylesheet'>");
print("<link href='assets/css/select2-bootstrap.min.css' type='text/css' rel='stylesheet'>");
print("<script src='assets/js/jquery-min.js' type='text/javascript'></script>");
print("<script src='assets/js/jquery.validate.min.js' type='text/javascript'></script>");
print("<script src='assets/js/additional-methods.min.js' type='text/javascript'></script>");
print("<script src='assets/js/jquery-ui.min.js' type='text/javascript'></script>");
print("<script src='assets/js/select2.min.js' type='text/javascript'></script>");
print("<script src='assets/js/bootstrap.min.js' type='text/javascript'></script>");
print('<link rel="shortcut icon" href="assets/img/favicon.ico">');
?>
<script type='text/javascript'>
    $(document).ready(function() {

        $('.raz').click(function() {
            $("#idf_source_recherches_communes").val('').trigger('change');
            $("#idf_type_acte_recherches_communes").val('').trigger('change');
            $("#idf_commune_recherches_communes").val('').trigger('change');
            $('#paroisses_rattachees_recherches_communes').prop('checked', true);
            $("#rayon_recherches_communes").val('');
            $("#annee_min_recherches_communes").val('');
            $("#annee_max_recherches_communes").val('');
            $("#nom_epx").val('');
            $("#prenom_epx").val('');
            $('#variantes_epx').prop('checked', true);
            $("#nom_epse").val('');
            $("#prenom_epse").val('');
            $('#variantes_epse').prop('checked', true);
            $("#nom").val('');
            $("#prenom").val('');
            $('#variantes').prop('checked', true);
            $("#idf_type_presence").val('');
            $("#sexe").val('');
            $("#commentaires").val('');
            $("#releve_mois_min_communes").val('');
            $("#releve_mois_max_communes").val('');
            $("#releve_annee_min_communes").val('');
            $("#releve_tous_patronymes_communes").prop('checked', false);
            $('#releve_tous_patronymes_communes').trigger('change');
        });

        $('#nom_epx').autocomplete({
            source: './ajax/patronyme.php',
            minLength: 3
        });

        $('#nom_epse').autocomplete({
            source: './ajax/patronyme.php',
            minLength: 3
        });

        $('#nom').autocomplete({
            source: './ajax/patronyme.php',
            minLength: 3
        });

        $.fn.select2.defaults.set("theme", "bootstrap");

        $("#idf_source_recherches_communes").select2({
            allowClear: true,
            placeholder: "Toutes"
        });
        $("#idf_type_acte_recherches_communes").select2({
            allowClear: true,
            placeholder: "Tous"
        });
        $("#idf_commune_recherches_communes").select2({
            allowClear: true,
            placeholder: "Toutes"
        });

        $.validator.addMethod('plusGrand', function(value, element, param) {
            if (this.optional(element)) return true;
            var annee_max = $(param).val();
            if (jQuery.trim(annee_max).length == 0) return true;
            var i = parseInt(value);
            var j = parseInt(annee_max);
            return i >= j;
        }, "l'année maximale doit être plus grande que l'année minimale");

        $.validator.addMethod('verifDate', function(value, element, param) {

            var annee = parseFloat($(param).val());
            value = parseFloat(value);

            if (jQuery.trim(annee).length > 0 && !isNaN(annee)) {
                if (isNaN(value)) {
                    return false;
                }
            }
            return true;

        }, "La période est invalide.");

        $.validator.addMethod('plusGrandReleve', function(value, element, param) {
            if (this.optional(element)) return true;
            var annee_max = $(param).val();
            if (jQuery.trim(annee_max).length == 0) return true;

            var start = new Date();
            start.setDate(1);
            start.setMonth(jQuery('#releve_mois_min_communes').val() - 1);
            start.setYear(jQuery('#releve_annee_min_communes').val());
            start.setHours(0);
            start.setMinutes(0);
            start.setSeconds(0);

            var end = new Date();
            end.setMonth(jQuery('#releve_mois_max_communes').val() - 1);
            end.setYear(jQuery('#releve_annee_max_communes').val());
            end.setDate(31);
            end.setHours(0);
            end.setMinutes(0);
            end.setSeconds(0);

            return end >= start;
        }, "la date maximale doit être plus grande que la date minimale");

        jQuery.validator.addMethod("libelle_joker", function(value, element) {
            var libelle = value.replace(/\*+/g, '*');
            return (libelle != '*' && libelle != '!') || ((libelle == '*' || libelle == '!') && $("#idf_commune_recherches_communes").val() != '');
        }, 'La commune doit être spécifiée quand le caractère joker ou ! est utilisé');

        jQuery.validator.addMethod("joker_interdit", function(value, element) {
            var libelle = value.replace(/\*+/g, '*');
            return libelle != '*';
        }, 'Le joker est interdit');

        //validation rules
        $("#recherches_communes").validate({
            ignore: [],
            rules: {
                annee_min: {
                    integer: true,
                    minlength: 4
                },
                annee_max: {
                    integer: true,
                    minlength: 4,
                    plusGrand: '#annee_min_recherches_communes'
                },
                releve_mois_min_communes: {
                    integer: true,
                    maxlength: 2,
                    verifDate: '#releve_annee_min_communes'
                },
                releve_mois_max_communes: {
                    integer: true,
                    maxlength: 2,
                    verifDate: '#releve_annee_max_communes'
                },
                releve_annee_min_communes: {
                    required: {
                        depends: function(element) {
                            return $('#releve_tous_patronymes_communes').is(":checked");
                        }
                    },
                    integer: true,
                    minlength: 4
                },
                releve_annee_max_communes: {
                    required: {
                        depends: function(element) {
                            return $('#releve_tous_patronymes_communes').is(":checked");
                        }
                    },
                    integer: true,
                    minlength: 4,
                    plusGrandReleve: '#releve_annee_min_communes'
                },
                idf_commune_recherches_communes: {
                    required: {
                        depends: function(element) {
                            return $("#rayon_recherches_communes").val() != '' || $('#releve_tous_patronymes_communes').is(":checked");
                        }
                    }
                },
                rayon: {
                    integer: true
                }
            },
            messages: {
                annee_min: {
                    integer: "L'année doit être un entier",
                    minlength: "L'année doit comporter 4 chiffes"
                },
                annee_max: {
                    integer: "L'année doit être un entier",
                    minlength: "L'année doit comporter 4 chiffes"
                },
                releve_annee_min_communes: {
                    integer: "L'année doit être un entier",
                    minlength: "L'année doit comporter 4 chiffes",
                    required: "L'année est obligatoire"
                },
                releve_annee_max_communes: {
                    integer: "L'année doit être un entier",
                    minlength: "L'année doit comporter 4 chiffes",
                    required: "L'année est obligatoire"
                },
                idf_commune_recherches_communes: {
                    required: "Une commune doit être remplie si le rayon est non vide ou si tous les patronymes sont choisis"
                },
                rayon: {
                    integer: "Le rayon doit être un entier"
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

        jQuery.validator.addMethod("patro_recherche", function(value, element) {
            var patro = value.replace(/\*+/g, '*');
            return this.optional(element) || (patro == '*' || patro.length >= 3 || patro == '!');
        }, "Le patronyme doit comporter au moins 3 caractères (* comprises) ou correspondre à * ou ! exactement");

        //validation rules
        $("#recherches_couple").validate({
            rules: {
                nom_epx: {
                    required: true,
                    patro_recherche: true,
                    libelle_joker: true
                },
                nom_epse: {
                    required: true,
                    patro_recherche: true,
                    libelle_joker: true
                },
                prenom_epx: {
                    required: {
                        depends: function(element) {
                            return $("#nom_epx").val() == '*';
                        }
                    },
                    joker_interdit: true
                },
                prenom_epse: {
                    required: {
                        depends: function(element) {
                            return $("#nom_epse").val() == '*';
                        }
                    },
                    joker_interdit: true
                }
            },
            messages: {
                nom_epx: {
                    required: "Le nom de l'époux est obligatoire"
                },
                nom_epse: {
                    required: "Le nom de l'épouse est obligatoire"
                },
                prenom_epx: {
                    required: "Le prénom de l'époux est obligatoire si son patronyme est vide",
                    joker_interdit: "Un joker unique de recherche est interdit. Préciser au moins une partie du prénom ou utiliser la recherche par personne si la recherche porte sur une seule personne"
                },
                prenom_epse: {
                    required: "Le prénom de l'épouse est obligatoire si son patronyme est vide",
                    joker_interdit: "Un joker unique de recherche est interdit. Préciser au moins une partie du prénom ou utiliser la recherche par personne si la recherche porte sur une seule personne"
                }
            },
            errorElement: "em",
            errorPlacement: function(error, element) {
                // Add the `help-block` class to the error element
                error.addClass("help-block");

                // Add `has-feedback` class to the parent div.form-group
                // in order to add icons to inputs
                element.parents(".col-md-2").addClass("has-feedback");

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
                $(element).parents(".col-md-2").addClass("has-error").removeClass("has-success");
                $(element).next("span").addClass("glyphicon-remove").removeClass("glyphicon-ok");
            },
            unhighlight: function(element, errorClass, validClass) {
                $(element).parents(".col-md-2").addClass("has-success").removeClass("has-error");
                $(element).next("span").addClass("glyphicon-ok").removeClass("glyphicon-remove");
            },
            submitHandler: function(form) {
                if ($("#recherches_communes").valid()) {
                    $("#idf_source_recherches_couple").val($("#idf_source_recherches_communes").val());
                    $("#idf_type_acte_recherches_couple").val($("#idf_type_acte_recherches_communes").val());
                    $("#idf_commune_recherches_couple").val($("#idf_commune_recherches_communes").val());
                    if ($('#paroisses_rattachees_recherches_communes').is(':checked'))
                        $("#paroisses_rattachees_recherches_couple").val("oui");
                    $("#rayon_recherches_couple").val($("#rayon_recherches_communes").val());
                    $("#annee_min_recherches_couple").val($("#annee_min_recherches_communes").val());
                    $("#annee_max_recherches_couple").val($("#annee_max_recherches_communes").val());
                    $("#releve_mois_min_couple").val($("#releve_mois_min_communes").val());
                    $("#releve_mois_max_couple").val($("#releve_mois_max_communes").val());
                    $("#releve_annee_min_couple").val($("#releve_annee_min_communes").val());
                    $("#releve_annee_max_couple").val($("#releve_annee_max_communes").val());
                    $("#releve_type_couple").val($("#releve_type_communes").val());
                    form.submit();
                }
            }
        });

        $("#recherches_personne").validate({
            rules: {
                nom: {
                    required: true,
                    patro_recherche: true,
                    libelle_joker: true
                },
                prenom: {
                    libelle_joker: true,
                    required: {
                        depends: function(element) {
                            return $("#idf_commune_recherches_communes").val() == '' && $("#idf_source_recherches_communes").val() != 4;
                        }
                    }
                }
            },
            messages: {
                nom: {
                    required: "Le nom est obligatoire"
                },
                prenom: {
                    required: "Le pr&eacute;nom est obligatoire si aucune commune/paroisse n'est s&eacute;lectionn&eacute;e. Veuillez choisir une commune avec un rayon de recherche"
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
            },
            submitHandler: function(form) {
                if ($("#recherches_communes").valid()) {
                    $("#idf_source_recherches_personne").val($("#idf_source_recherches_communes").val());
                    $("#idf_type_acte_recherches_personne").val($("#idf_type_acte_recherches_communes").val());
                    $("#idf_commune_recherches_personne").val($("#idf_commune_recherches_communes").val());
                    if ($('#paroisses_rattachees_recherches_communes').is(':checked'))
                        $("#paroisses_rattachees_recherches_personne").val("oui");
                    $("#rayon_recherches_personne").val($("#rayon_recherches_communes").val());
                    $("#annee_min_recherches_personne").val($("#annee_min_recherches_communes").val());
                    $("#annee_max_recherches_personne").val($("#annee_max_recherches_communes").val());
                    $("#releve_mois_min_personne").val($("#releve_mois_min_communes").val());
                    $("#releve_mois_max_personne").val($("#releve_mois_max_communes").val());
                    $("#releve_annee_min_personne").val($("#releve_annee_min_communes").val());
                    $("#releve_annee_max_personne").val($("#releve_annee_max_communes").val());
                    $("#releve_type_personne").val($("#releve_type_communes").val());
                    form.submit();
                }
            }
        });

        $("#recherches_tous_patronymes").validate({
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
            },
            submitHandler: function(form) {
                if ($("#recherches_communes").valid()) {
                    $("#idf_source_recherches_tous_patronymes").val($("#idf_source_recherches_communes").val());
                    $("#idf_type_acte_recherches_tous_patronymes").val($("#idf_type_acte_recherches_communes").val());
                    $("#idf_commune_recherches_tous_patronymes").val($("#idf_commune_recherches_communes").val());
                    if ($('#paroisses_rattachees_recherches_communes').is(':checked'))
                        $("#paroisses_rattachees_recherches_tous_patronymes").val("oui");
                    $("#rayon_recherches_tous_patronymes").val($("#rayon_recherches_communes").val());
                    $("#annee_min_recherches_tous_patronymes").val($("#annee_min_recherches_communes").val());
                    $("#annee_max_recherches_tous_patronymes").val($("#annee_max_recherches_communes").val());
                    $("#releve_mois_min_tous_patronymes").val($("#releve_mois_min_communes").val());
                    $("#releve_mois_max_tous_patronymes").val($("#releve_mois_max_communes").val());
                    $("#releve_annee_min_tous_patronymes").val($("#releve_annee_min_communes").val());
                    $("#releve_annee_max_tous_patronymes").val($("#releve_annee_max_communes").val());
                    $("#releve_type_tous_patronymes").val($("#releve_type_communes").val());
                    form.submit();
                }
            }
        });

        $('#echange_patros').click(function() {
            var nom_epx = $("#nom_epx").val();
            $("#nom_epx").val($("#nom_epse").val());
            $("#nom_epse").val(nom_epx);
        });

        function setMaxDateReleve(el) {
            if (!isNaN(parseFloat($(el).val()))) {
                if (isNaN(parseFloat($('#releve_mois_max_communes').val())) && isNaN(parseFloat($('#releve_annee_max_communes').val()))) {
                    $('#releve_mois_max_communes').val($('#releve_mois_max_communes').attr('data-max'));
                    $('#releve_annee_max_communes').val($('#releve_annee_max_communes').attr('data-max'));
                }
            }
        }

        $('#releve_mois_min_communes').change(function(e) {
            setMaxDateReleve($(this));
        });

        $('#releve_annee_min_communes').change(function(e) {
            setMaxDateReleve($(this));
        });

        $('#releve_tous_patronymes_communes').change(function() {
            if ($(this).is(":checked")) {

                //'checked' event code
                $('.pave-couple').hide();
                $('#recherches_couple').hide();
                $('.pave-personne').hide();
                $('#recherches_personne').hide();
                $('.pave-tous-patronymes').show();
                $('#recherches_tous_patronymes').show();;
            } else {
                //'unchecked' event code
                $('.pave-tous-patronymes').hide();
                $('#recherches_tous_patronymes').hide();
                $('.pave-couple').show();
                $('#recherches_couple').show();
                $('.pave-personne').show();
                $('#recherches_personne').show();
            }
        });

        $('#releve_tous_patronymes_communes').trigger('change');

        $('#idf_commune_recherches_communes').on('select2:unselecting', function(e) {
            $("#rayon_recherches_communes").val('');
        });

    });
</script>
<?php
print("</head>");
print("<body>");
print('<div class="container">');

require_once __DIR__ . '/commun/menu.php';

print('<div class="panel-group">');
print('<div class="panel-body">');
print('<form id="recherches_communes" class="form-inline">');
print("<input type=hidden name=recherche value=\"\">");
print('<div class="form-row col-md-12">');
print('<div class="form-group form-group col-md-4"><label for="idf_source_recherches_communes">Source</label><select name="idf_source_recherches_communes" id="idf_source_recherches_communes" class="form-control">');
print(chaine_select_options($gi_idf_source, array('' => 'Toutes') + $a_sources));
print('</select></div>');

print('<div class="form-group col-md-offset-2 col-md-6"><label for="idf_type_acte_recherches_communes">Type d\'acte</label><select name="idf_type_acte_recherches_communes" id="idf_type_acte_recherches_communes" class="form-control">');
print(chaine_select_options($gi_idf_type_acte, array('' => 'Tous') + $a_types_acte));
print('</select>');
print('</div></div>');

print('<div class="form-row col-md-12">');
print('<div class="form-group col-md-6 "><label for="idf_commune_recherches_communes">Commune/Paroisse</label><span class=\"lib_erreur\"><select name="idf_commune_recherches_communes" id="idf_commune_recherches_communes" class="form-control">');
$a_toutes_communes = array('' => 'Toutes') + $a_communes_acte;
print(chaine_select_options($gi_idf_commune, $a_toutes_communes));
print('</select></span></div>');

print('<div class="form-check col-md-3">');

if ($gst_paroisses_rattachees == '')
    print('<input type=checkbox name=paroisses_rattachees id="paroisses_rattachees_recherches_communes" value=oui class="form-check-input">');
else
    print('<input type=checkbox name=paroisses_rattachees id="paroisses_rattachees_recherches_communes" value=oui checked class="form-check-input" >');
print('<label for="paroisses_rattachees_recherches_communes" class="form-check-label">Paroisses rattach&eacute;es</label>');
print('</div>');

print("<div class=\"form-group col-md-3\"><div class=\"input-group \"><span class=\"input-group-addon\">Rayon de recherche:</span><label for=\"rayon_recherches_communes\" class=\"sr-only\">Rayon</label><span class=\"lib_erreur\"><input type=text name=rayon id='rayon_recherches_communes' size=2 maxlength=2 value=\"$gi_rayon\" class=\"form-control\"></span><span class=\"input-group-addon\">Km</span></div></div>");

print('</div>');

print('<div class="form-row col-md-12">');
print('<div class="input-group col-md-offset-4 col-md-4 ">');
print("<span class=\"input-group-addon\">Ann&eacute;es de</span><div class=\"lib_erreur\"><input type=text name=annee_min id=\"annee_min_recherches_communes\" size=4 value=\"$gi_annee_min\" class=\"form-control\"></div>");
print("<span class=\"input-group-addon\">&agrave;</span><div class=\"lib_erreur\"><input type=text name=annee_max size=4 id=\"annee_max_recherches_communes\" value=\"$gi_annee_max\" class=\"form-control \"></div>");
print('</div>');

print('</div>');

/* dates de releves */
// date_default_timezone_set($gst_time_zone); // NB: Doit utiliser le timezone du serveur

print('<div class="form-row col-md-12">');

print('<div class="form-group col-md-10 lib_erreur">');
print("<label class=\"sr-only\" for=\"releve_type_communes\">Actes</label><div class=\"input-group\"><span class=\"input-group-addon\">actes</span><select id=\"releve_type_communes\" name=\"releve_type_communes\" class=\"form-control form-control-sm\">");
$options = array(0 => 'publiés', 1 => "modifiés");
print(chaine_select_options($gst_releve_type, $options, false));
print("</select></div>");

print("<label class=\"sr-only\" for=\"releve_mois_min_communes\">entre</label><div class=\"input-group\"><span class=\"input-group-addon\">entre</span><select id=\"releve_mois_min_communes\" name=\"releve_mois_min_communes\" class=\"form-control form-control-sm\">");

for ($i = 1; $i <= 12; $i++) {
    $mois[$i] = str_pad($i, 2, '0', STR_PAD_LEFT);
}
$a_mois = array('' => 'Mois') + $mois;
print(chaine_select_options($gst_releve_mois_min, $a_mois));
print("</select></div>");

print("<label class=\"sr-only\" for=\"releve_annee_min_communes\">Ann&eacute;e Min</label><div class=\"input-group\"><span class=\"input-group-addon\">Ann&eacute;e Min</span><input type=\"text\" name=\"releve_annee_min_communes\" id=\"releve_annee_min_communes\" size=\"4\" maxlength=\"4\" value=\"$gst_releve_annee_min\" class=\"form-control form-control-sm\"></div>");

print("<label class=\"sr-only\" for=\"releve_mois_max_communes\">et</label><div class=\"input-group \"><span class=\"input-group-addon\">et</span><select id=\"releve_mois_max_communes\" name=\"releve_mois_max_communes\" data-max=\"" . date('n') . "\" class=\"form-control form-control-sm\">");
print(chaine_select_options($gst_releve_mois_max, $a_mois));
print("</select></div>");

print("<label class=\"sr-only\" for=\"releve_annee_max_communes\">Ann&eacute;e Max</label><div class=\"input-group\"><span class=\"input-group-addon\">Ann&eacute;e Max</span><input type=\"text\" name=\"releve_annee_max_communes\" id=\"releve_annee_max_communes\" size=\"4\" maxlength=\"4\" value=\"$gst_releve_annee_max\" data-max=\"" . date('Y') . "\" class=\"form-control form-control-sm\"></div>");
print('</div>');

print('<div class="form-check col-md-2">');
$checked = ($gst_releve_tous_patronymes) ? ' checked="checked" ' : '';
print("<input type=\"checkbox\" name=\"releve_tous_patronymes\" id=\"releve_tous_patronymes_communes\" " . $checked . " value=\"1\" class=\"form-check-input\" ><label  for=\"releve_tous_patronymes_communes\" class=\"form-check-label\" >Tous patronymes</label>");
print('</div>');

print('</div>');

print("</form>");
print('</div>');

print('<div class="pave-couple panel panel-primary">');
print('<div class="panel-heading">Recherche par couple</div>');

print('<div class="panel-body">');
print('<form id="recherches_couple" method="post" action="recherche-reponse.php">');
print('<input type="hidden" name="type_recherche" value="couple">');
print('<input type="hidden" id="idf_source_recherches_couple" name="idf_source_recherche">');
print('<input type="hidden" id="idf_type_acte_recherches_couple" name="idf_type_acte_recherche">');
print('<input type="hidden" id="idf_commune_recherches_couple" name="idf_commune_recherche">');
print('<input type="hidden" id="rayon_recherches_couple" name="rayon">');
print('<input type="hidden" id="paroisses_rattachees_recherches_couple" name="paroisses_rattachees">');
print('<input type="hidden" id="annee_min_recherches_couple" name="annee_min">');
print('<input type="hidden" id="annee_max_recherches_couple" name="annee_max">');
print('<input type="hidden" id="releve_mois_min_couple" name="releve_mois_min">');
print('<input type="hidden" id="releve_annee_min_couple" name="releve_annee_min">');
print('<input type="hidden" id="releve_mois_max_couple" name="releve_mois_max">');
print('<input type="hidden" id="releve_annee_max_couple" name="releve_annee_max">');
print('<input type="hidden" id="releve_type_couple" name="releve_type">');

print('<div class="form-row col-md-12">');

print("<label for=\"nom_epx\" class=\"col-form-label col-md-2\">Nom Epoux</label>");
print('<div class="col-md-2">');
print("<input type=text id=nom_epx name=nom_epx size=15 maxlength=30 value=\"$gst_nom_epx\" class=\"form-control\">");
print('</div>');

print("<label for=\"prenom_epx\" class=\"col-form-label col-md-2\">Pr&eacute;nom Epoux</label>");
print('<div class="col-md-2">');
print("<input type=text name=prenom_epx id=prenom_epx size=15 maxlength=30 value=\"$gst_prenom_epx\" class=\"form-control\">");
print('</div>');

print('<div class="form-check col-md-4">');
if ($gst_variantes_epx == '')
    print('<input type=checkbox name=variantes_epx id=variantes_epx value="oui" class="form-check-input">');
else
    print('<input type=checkbox name=variantes_epx id=variantes_epx value="oui" checked class="form-check-input">');
print('<label for="variantes_epx" class="form-check-label">Recherche par variantes connues</label>');
print('</div>');

print('</div>'); //fin ligne

print('<div class="form-row col-md-12">');

print("<label for=\"nom_epse\" class=\"col-form-label col-md-2\">Nom Epouse</label>");
print('<div class="col-md-2">');
print('<div class="input-group">');
print('<span class="input-group-addon">');
print('<span class="glyphicon glyphicon-random"  id="echange_patros"></span>');
print('</span>');
print("<input type=text id=nom_epse name=nom_epse size=15 maxlength=30 value=\"$gst_nom_epse\" class=\"form-control\">");
print('</div>');
print('</div>');

print("<label for=\"prenom_epse\" class=\"col-form-label col-md-2\">Pr&eacute;nom Epouse</label>");
print('<div class="col-md-2">');
print("<input type=text name=prenom_epse id=prenom_epse size=15 maxlength=30 value=\"$gst_prenom_epse\" class=\"form-control\">");
print('</div>');

print('<div class="col-md-4">');
if ($gst_variantes_epse == '')
    print('<input type=checkbox name=variantes_epse id=variantes_epse value="oui class="form-check-input">');
else
    print('<input type=checkbox name=variantes_epse id=variantes_epse value="oui"  checked class="form-check-input">');
print('<label for="variantes_epse" class="form-check-label">Recherche par variantes connues</label>');
print('</div>');

print('</div>'); // fin ligne

print('<div class="form-row">');

print('<div class="btn-group col-md-6 col-md-offset-4" role="group">');
print('<button class="btn btn-primary" type=submit name=Rechercher><span class="glyphicon glyphicon-search"></span> Rechercher le couple</button>');
print('<button class="btn btn-warning raz" type=button name="raz"><span class="glyphicon glyphicon-erase"></span> Effacer tous les Champs</button>');
print('</div>');

print('</div>');
print('</form>');
print('</div>');
print('</div>'); // fin pave-couple

print('<div class="pave-personne panel panel-primary">');
print('<div class="panel-heading">Recherche par personne</div>');
print('<div class="panel-body">');

print('<form id="recherches_personne" method="post" action="recherche-reponse.php">');
print('<input type="hidden" name="type_recherche" value="personne">');
print('<input type="hidden" id="idf_source_recherches_personne" name="idf_source_recherche">');
print('<input type="hidden" id="idf_type_acte_recherches_personne" name="idf_type_acte_recherche">');
print('<input type="hidden" id="idf_commune_recherches_personne" name="idf_commune_recherche">');
print('<input type="hidden" id="rayon_recherches_personne" name="rayon">');
print('<input type="hidden" id="paroisses_rattachees_recherches_personne" name="paroisses_rattachees">');
print('<input type="hidden" id="annee_min_recherches_personne" name="annee_min">');
print('<input type="hidden" id="annee_max_recherches_personne" name="annee_max">');
print('<input type="hidden" id="releve_mois_min_personne" name="releve_mois_min">');
print('<input type="hidden" id="releve_annee_min_personne" name="releve_annee_min">');
print('<input type="hidden" id="releve_mois_max_personne" name="releve_mois_max">');
print('<input type="hidden" id="releve_annee_max_personne" name="releve_annee_max">');
print('<input type="hidden" id="releve_type_personne" name="releve_type">');

$ga_sexe[0] = 'Tous';
print('<div class="form-row">');

print('<label for="sexe" class="col-form-label col-md-1">Sexe</label>');
print('<div class="col-md-2">');
print('<div class="input-group">');
print('<span class="input-group-addon">');
print('<span class="glyphicon glyphicon-info-sign" data-toggle="collapse" data-target="#aideTP" aria-expanded="false"></span>');
print('</span>');
print('<select name="sexe" id="sexe" class="form-control">');
print(chaine_select_options($gst_sexe, $ga_sexe));
print('</select>');
print('</div>');
print('</div>');

print("<label for=\"nom\" class=\"col-form-label col-md-1\">Nom</label>");
print('<div class="col-md-2 lib_erreur">');
print("<input type=text name=nom id=nom size=15 maxlength=30 value=\"$gst_nom\" class=\"form-control\">");
print('</div>');

print("<label for=\"prenom\" class=\"col-form-label col-md-1\">Pr&eacute;nom</label>");
print('<div class="col-md-2 lib_erreur">');
print("<input type=text name=prenom id=prenom size=15 maxlength=30 value=\"$gst_prenom\" class=\"form-control\">");
print('</div>');

print('<label for="idf_type_presence" class="col-form-label col-md-1">Type de<br>pr&eacute;sence</label>');
print('<div class="col-md-2">');
print('<select name="idf_type_presence" id="idf_type_presence" class="form-control" aria-describedby="aideTP">');
print(chaine_select_options($gi_idf_type_presence, $a_types_presence));
print('</select>');
print('</div>');

print('</div>'); // fin ligne

print('<div class="form-row col-md-12">');
print('<div id="aideTP" class="collapse alert alert-warning">Nim&egrave;gue ne renseignant pas le sexe d\'un parrain, t&eacute;moin ou marraine, ne pas le sp&eacute;cifier dans une recherche de ce type</div>');
print('</div>');

print('<div class="form-row col-md-12">');
print("<label for=\"commentaires\" class=\"col-form-label col-md-3\">Recherche libre dans les commentaires</label>");
print('<div class="col-md-5">');
print("<input type=text name=commentaires id=commentaires size=40 maxlength=40 value=\"$gst_commentaires\" class=\"form-control\">");
print('</div>');
print('<div class="col-md-1">');
if ($gst_variantes == '')
    print('<input type=checkbox name=variantes id=variantes value=oui class="form-check-input">');
else
    print('<input type=checkbox name=variantes id=variantes value=oui checked class="form-check-input">');
print('</div>');
print('<label for="variantes" class="form-check-label col-md-2">Recherche par variantes connues</label>');

print('</div>'); //fin ligne

print('<div class="form-row">');

print('<div class="btn-group col-md-6 col-md-offset-4" role="group">');
print('<button type=submit name=Rechercher class="btn btn-primary"><span class="glyphicon glyphicon-search"></span>  Rechercher la personne</button>');
print('<button type=button name="raz" class="btn btn-warning raz"><span class="glyphicon glyphicon-erase"></span> Effacer tous les Champs</button>');
print('</div>');

print('</div>'); //fin ligne

print("</form>");
print('</div>');
print('</div>');  // fin pavé

print('<div class="pave-tous-patronymes panel panel-primary">');
print('<div class="panel-heading">Recherche sur tous les patronymes</div>');
print('<div class="panel-body">');

print('<form id="recherches_tous_patronymes" method="post" action="recherche-reponse.php">');
print('<input type="hidden" name="type_recherche" value="tous_patronymes">');
print('<input type="hidden" id="idf_source_recherches_tous_patronymes" name="idf_source_recherche">');
print('<input type="hidden" id="idf_type_acte_recherches_tous_patronymes" name="idf_type_acte_recherche">');
print('<input type="hidden" id="idf_commune_recherches_tous_patronymes" name="idf_commune_recherche">');
print('<input type="hidden" id="rayon_recherches_tous_patronymes" name="rayon">');
print('<input type="hidden" id="paroisses_rattachees_recherches_tous_patronymes" name="paroisses_rattachees">');
print('<input type="hidden" id="annee_min_recherches_tous_patronymes" name="annee_min">');
print('<input type="hidden" id="annee_max_recherches_tous_patronymes" name="annee_max">');
print('<input type="hidden" id="releve_mois_min_tous_patronymes" name="releve_mois_min">');
print('<input type="hidden" id="releve_annee_min_tous_patronymes" name="releve_annee_min">');
print('<input type="hidden" id="releve_mois_max_tous_patronymes" name="releve_mois_max">');
print('<input type="hidden" id="releve_annee_max_tous_patronymes" name="releve_annee_max">');
print('<input type="hidden" id="releve_type_tous_patronymes" name="releve_type">');

print('<div class="form-row">');
print('<div class="btn-group" role="group">');
print('<button type=submit name=Rechercher class="btn btn-primary"><span class="glyphicon glyphicon-search"></span> Rechercher les patronymes</button>');
print('<button type=button name="raz" class="btn btn-warning raz"><span class="glyphicon glyphicon-erase"></span> Effacer tous les Champs</button>');
print('</div>');
print('</div>'); //fin ligne

print("</form>");
print('</div>'); // fin body pavé
print("</div>"); // fin pavé

print("</div>"); // fin panel-group
print("</div>"); // fin container

print("</body>");
print("</html>");
