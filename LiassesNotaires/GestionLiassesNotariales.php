<?php
require_once __DIR__ . '/../Commun/config.php';
require_once __DIR__ . '/../Commun/constantes.php';
require_once __DIR__ . '/../Commun/Identification.php';

// La page est reservee uniquement aux gens ayant les droits d'import/export
require_once __DIR__ . '/../Commun/VerificationDroits.php';
verifie_privilege(DROIT_NOTAIRES);
require_once __DIR__ . '/../Commun/ConnexionBD.php';
require_once __DIR__ . '/../Commun/PaginationTableau.php';
require_once __DIR__ . '/../Commun/commun.php';

$connexionBD = ConnexionBD::singleton($gst_serveur_bd, $gst_utilisateur_bd, $gst_mdp_utilisateur_bd, $gst_nom_bd);

if (isset($_POST['mode'])) {
	$gst_m1 = $_POST['mode'];
} elseif (isset($_GET['mode'])) {
	$gst_m1 = $_GET['mode'];
} elseif (isset($_REQUEST['mode'])) {
	$gst_m1 = $_REQUEST['mode'];
} elseif (isset($_GET['num_page'])) {
	$gst_m1 = "VERIFIER_GROUPE_SUITE";
} else {
	$gst_m1 = 'LISTE';
}

$gst_mode = isset($_REQUEST['smode']) ? $_REQUEST['smode'] : $gst_m1;
if (isset($_GET['mod'])) {
	if (substr($_GET['mod'], 0, 3) == 'NOT') {
		$gst_mode = 'MENU_MODIFIER_NOTAIRE';
		$gi_idf_notaire = substr($_GET['mod'], 3, 10);
	} elseif (substr($_GET['mod'], 0, 3) == 'PER') {
		$gst_mode = 'MENU_MODIFIER_PERIODE';
		$gi_idf_periode = substr($_GET['mod'], 3, 10);
	} else {
		$gst_mode = 'MENU_MODIFIER';
		$gst_cote_liasse = $_GET['mod'];
	}
}
if (empty($gst_cote_liasse)) {
	if (isset($_POST['cote_liasse'])) {
		$gst_cote_liasse = $_POST['cote_liasse'];
	} else if (isset($_REQUEST['cote_liasse'])) {
		$gst_cote_liasse = $_REQUEST['cote_liasse'];
	}
}
$gi_num_page_cour = empty($_GET['num_page']) ? 1 : $_GET['num_page'];


print('<!DOCTYPE html>');
print("<head>");
print("<title>Gestion des actions sur les liasses notariales</title>");
print('<meta name="viewport" content="width=device-width, initial-scale=1.0">');
print('<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" >');
print('<meta http-equiv="content-language" content="fr">');
print("<link href='../css/styles.css' type='text/css' rel='stylesheet'>");
print("<link href='../css/bootstrap.min.css' rel='stylesheet'>");
print("<link href='../css/jquery-ui.css' type='text/css' rel='stylesheet'>");
print("<link href='../css/jquery-ui.structure.min.css' type='text/css' rel='stylesheet'>");
print("<link href='../css/jquery-ui.theme.min.css' type='text/css' rel='stylesheet'>");
print("<link href='../css/select2.min.css' type='text/css' rel='stylesheet'>");
print("<link href='../css/select2-bootstrap.min.css' type='text/css' rel='stylesheet'>");
print("<script src='../js/jquery-min.js' type='text/javascript'></script>");
print("<script src='../js/jquery.validate.min.js' type='text/javascript'></script>");
print("<script src='../js/additional-methods.min.js' type='text/javascript'></script>");
print("<script src='../js/jquery-ui.min.js' type='text/javascript'></script>");
print("<script src='../js/select2.min.js' type='text/javascript'></script>");
print("<script src='../js/bootstrap.min.js' type='text/javascript'></script>");
//print("<script src='./VerifieChampsGestionLiasseNot.js' type='text/javascript'></script>");
?>
<script type='text/javascript'>
	$(document).ready(function() {

		$.fn.select2.defaults.set("theme", "bootstrap");

		$(".js-select-avec-recherche").select2();

		// ------------------------------------------------------- Contrôles	
		jQuery.validator.addMethod("depose_avec_dept", function(value, element) {
				var check = false;
				if ($(element).is(':checked')) {
					check = $('#dept_depose_ad').val() != '';
				}
				return this.optional(element) || check;
			},
			"Le département doit être renseigné pour une liasse déposée aux AD"
		);

		jQuery.validator.addMethod("dept_avec_depose", function(value, element) {
				var check = false;
				if ($(element).val() != '') {
					check = $('#depose_ad').is(':checked');
				}
				return this.optional(element) || check;
			},
			"La case 'Déposée aux AD' doit être cochée quand le département est renseigné"
		);

		jQuery.validator.addMethod("annee_valide", function(value, element) {
				var check = true;
				var annee = $(element).val();
				if (isNaN(annee) && annee.substring(0, 3) != 'an ') {
					check = false;
				} else if (annee.substring(0, 3) == 'an ' &&
					annee != 'an I' && annee != 'an II' && annee != 'an III' && annee != 'an IV' &&
					annee != 'an V' && annee != 'an VI' && annee != 'an VII' && annee != 'an VIII' &&
					annee != 'an IX' && annee != 'an X' && annee != 'an XI' && annee != 'an XII' && annee != 'an XIII' && annee != 'an XIV') {
					check = false;
				} else {
					var a = annee * 1;
					if (a < 1000 || a > 2100) {
						check = false;
					}
				}
				return this.optional(element) || check;
			},
			"L'année doit être soit une année révolutionaire (an I, an II, ...), soit une année sur 4 chiffres"
		);

		jQuery.validator.addMethod("annee_mois_fin", function(value, element) {
				var check = true;
				var mois = $(element).val();
				var annee = $('#annee_fin').val();
				if (mois != "0") {
					if (annee == '') {
						check = false;
					}
				}
				return this.optional(element) || check;
			},
			"Saisir l'année de fin"
		);

		jQuery.validator.addMethod("mois_debut", function(value, element) {
				var check = true;
				var mois_debut = $(element).val();
				var annee_debut = $('#annee_debut').val();
				if (mois_debut != "0") {
					if (annee_debut.substring(0, 3) != 'an ') {
						if (isNaN(mois_debut)) {
							check = false;
						}
					} else {
						if (!isNaN(mois_debut)) {
							check = false;
						}
					}
				}
				return this.optional(element) || check;
			},
			"Incohérence entre l'année et le mois de début"
		);

		jQuery.validator.addMethod("mois_fin", function(value, element) {
				var check = true;
				var mois_fin = $(element).val();
				var annee_fin = $('#annee_fin').val();
				if (mois_fin != "0") {
					if (annee_fin.substring(0, 3) != 'an ') {
						if (isNaN(mois_fin)) {
							check = false;
						}
					} else {
						if (!isNaN(mois_fin)) {
							check = false;
						}
					}
				}
				return this.optional(element) || check;
			},
			"Incohérence entre l'année et le mois de fin"
		);

		jQuery.validator.addMethod("groupe_cotes", function(value, element) {
				var check = true;
				var groupe = $(element).val();
				var tab = groupe.split(',');
				for (bloc of tab) {
					var pos = bloc.indexOf('-');
					if (pos == -1) {
						if (isNaN(bloc)) {
							check = false;
							break;
						}
					} else {
						var sbloc1 = bloc.substr(0, pos);
						var sbloc2 = bloc.substr(pos + 1);
						if (sbloc2.indexOf('-') != -1) {
							check = false;
							break;
						}
						if (isNaN(sbloc1)) {
							check = false;
							break;
						}
						if (isNaN(sbloc2)) {
							check = false;
							break;
						}
					}
				}
				return this.optional(element) || check;
			},
			"La liste de cotes doit être composée de n1-n2 ou n1,n2,n3,... ou une combinaison des deux"
		);

		jQuery.validator.addMethod("commune_requise", function(value, element) {
				var check = true;
				if ($(element).val() == "0") {
					check = false;
				}
				return this.optional(element) || check;
			},
			"La commune de l'étude est obligatoire !!!"
		);

		jQuery.validator.addMethod("nom_prenom_commune", function(value, element) {
				var check = true;
				var nom = $('#nom').val();
				var prenom = $('#prenom').val();
				var commune = $('#idf_commune').val();
				var commentaire = $('#commentaire').val();
				if (commune == 0 && nom == '' && prenom == '' && commentaire == '') {
					check = false;
				}
				return this.optional(element) || check;
			},
			"Indiquer au moins le nom, le prénom, la commune ou le commentaire"
		);

		$("#idf_commune").select2({
			allowClear: true,
			placeholder: ""
		});
		$("#dept_depose_ad").select2({
			allowClear: true,
			placeholder: ""
		});
		$("#forme_liasse").select2({
			allowClear: true,
			placeholder: ""
		});


		// ------------------------------------------------------- Navigation	
		$("#btRetour").click(function() {
			$("#mode").val('LISTE');
		});

		$("#btRetourLiasse").click(function() {
			$("#mode").val('MENU_MODIFIER');
		});

		$("#btRetourPeriodes").click(function() {
			$("#mode").val('LISTE_PERIODE');
		});

		$("#btRetourNotaires").click(function() {
			$("#mode").val('LISTE_NOTAIRE');
		});

		// ------------------------------------------------------- Liasses	
		$("#btSupprimerLiasse").click(function() {
			var chaine = "";
			// Un seul élément
			if (document.forms['listeLiasses'].elements['supp[]'].checked) {
				chaine += document.forms['listeLiasses'].elements['supp[]'].id + "\n";
			}
			// Au moins deux éléments 
			for (var i = 0; i < document.forms['listeLiasses'].elements['supp[]'].length; i++) {
				if (document.forms['listeLiasses'].elements['supp[]'][i].checked) {
					chaine += document.forms['listeLiasses'].elements['supp[]'][i].id + "\n";
				}
			}
			if (chaine == "") {
				alert("Pas de liasse sélectionnée");
			} else {
				Message = "Etes-vous sûr de supprimer ces liasses :\n" + chaine + "?";
				if (confirm(Message)) {
					document.forms['listeLiasses'].submit();
				}
			}
		});

		$("#btAjoutLiasse").click(function() {
			$("#mode").val('MENU_AJOUTER');
		});

		$("#btAjouterLiasse").click(function() {
			$("#mode").val('AJOUTER');
		});

		$("#btAjoutGroupe").click(function() {
			$("#mode").val('MENU_AJOUTER_GROUPE');
		});

		$("#btAjouterGroupe").click(function() {
			var check = true;
			if ($("#cre_groupe_liasses").valid()) {
				var pcotes = "";
				var groupe = $('#numeros').val();
				var tab = groupe.split(',');
				for (bloc of tab) {
					var pos = bloc.indexOf('-');
					if (pos == -1) {
						pnum = "0000" + bloc;
						pcotes += $('#serie').val() + "-" + pnum.substr(pnum.length - 5) + ", ";
					} else {
						var sbloc1 = bloc.substr(0, pos);
						var sbloc2 = bloc.substr(pos + 1);
						for (i = parseInt(sbloc1); i <= parseInt(sbloc2); i++) {
							pnum = "0000" + i.toString();
							pcotes += $('#serie').val() + "-" + pnum.substr(pnum.length - 5) + ", ";
						}
					}
				}
				var l = pcotes.length;
				group = pcotes.substr(0, l - 2);
				if (check) {
					check = confirm("Vous avez demandé la création des liasses suivantes : " + group + "\nConfirmez-vous ?");
					if (check) {
						$("#cre_groupe_liasses").submit();
					}
				}
			}
		});

		$("#btValiderCorrectionGroupe").click(function() {
			$("#mode").val('CORRIGER_GROUPE');
		});


		$("#btModifierLiasse").click(function() {
			$("#mode").val('MODIFIER');
		});

		$("#cre_liasses").validate({
			rules: {
				numero: {
					required: true,
					integer: true
				},
				depose_ad: {
					depose_avec_dept: true
				},
				dept_depose_ad: {
					dept_avec_depose: true
				},
				forme_liasse: {
					required: true
				}
			},
			messages: {
				numero: {
					required: "Vous devez saisir le dernier chiffre du numéro de liasse",
					integer: "Vous devez saisir un chiffre"
				},
				depose_ad: {
					depose_avec_dept: "Le département doit être renseigné pour une liasse déposée aux AD"
				},
				dept_depose_ad: {
					dept_avec_depose: "La case 'Déposée aux AD' doit être cochée quand le département est renseigné"
				},
				forme_liasse: {
					required: "La forme de la liasse est obligatoire"
				}
			}
		});

		$("#maj_liasses").validate({
			rules: {
				depose_ad: {
					depose_avec_dept: true
				},
				dept_depose_ad: {
					dept_avec_depose: true
				},
				forme_liasse: {
					required: true
				}
			},
			messages: {
				depose_ad: {
					depose_avec_dept: "Le département doit être renseigné pour une liasse déposée aux AD"
				},
				dept_depose_ad: {
					dept_avec_depose: "La case 'Déposée aux AD' doit être cochée quand le département est renseigné"
				},
				forme_liasse: {
					required: "La forme de la liasse est obligatoire"
				}
			}
		});

		$("#cre_groupe_liasses").validate({
			rules: {
				numeros: {
					groupe_cotes: true,
					required: true
				},
				depose_ad: {
					depose_avec_dept: true
				},
				dept_depose_ad: {
					dept_avec_depose: true
				},
				nom: {
					required: true
				},
				idf_commune: {
					commune_requise: true
				}
			},
			messages: {
				numeros: {
					groupe_cotes: "La liste des numéros de liasses doit être composée de n1-n2 ou n1,n2,n3,... ou une combinaison des deux",
					required: "La liste des numéros de liasses est obligatoire"
				},
				depose_ad: {
					depose_avec_dept: "Le département doit être renseigné pour une liasse déposée aux AD"
				},
				dept_depose_ad: {
					dept_avec_depose: "La case 'Déposée aux AD' doit être cochée quand le département est renseigné"
				},
				nom: {
					required: "Le nom du notaire est obligatoire"
				},
				idf_commune: {
					commune_requise: "La commune de l'étude est obligatoire"
				}
			}
		});


		// ------------------------------------------------------- Périodes	
		$("#btSupprimerPeriodes").click(function() {
			var chaine = "";
			// Un seul élément
			if (document.forms['listePeriodes'].elements['supp[]'].checked) {
				chaine += document.forms['listePeriodes'].elements['supp[]'].id + "\n";
			}
			// Au moins deux éléments 
			for (var i = 0; i < document.forms['listePeriodes'].elements['supp[]'].length; i++) {
				if (document.forms['listePeriodes'].elements['supp[]'][i].checked) {
					chaine += document.forms['listePeriodes'].elements['supp[]'][i].id + "\n";
				}
			}
			if (chaine == "") {
				alert("Pas de période sélectionnée");
			} else {
				Message = "Etes-vous sûr de supprimer ces périodes :\n" + chaine + "?";
				if (confirm(Message)) {
					document.forms['listePeriodes'].submit();
				}
			}
		});

		$("#btAjoutPeriode").click(function() {
			$("#mode").val('MENU_AJOUTER_PERIODE');
		});

		$("#btAjouterPeriode").click(function() {
			$("#mode").val('AJOUTER_PERIODE');
		});

		$("#btModifierPeriode").click(function() {
			$("#mode").val('MODIFIER_PERIODE');
		});

		$("#maj_periode").validate({
			rules: {
				annee_debut: {
					required: true,
					annee_valide: true
				},
				mois_debut: {
					mois_debut: true
				},
				annee_fin: {
					annee_valide: true
				},
				mois_fin: {
					mois_fin: true,
					annee_mois_fin: true
				}
			},
			messages: {
				annee_debut: {
					required: "Saisir au moins l'année de début de période",
					annee_valide: "L'année doit être soit une année révolutionaire (an I, an II, ...), soit une année sur 4 chiffres"
				},
				mois_debut: {
					mois_debut: "Incohérence entre l'année et le mois"
				},
				annee_fin: {
					annee_valide: "L'année doit être soit une année révolutionaire (an I, an II, ...), soit une année sur 4 chiffres"
				},
				mois_fin: {
					mois_fin: "Incohérence entre l'année et le mois",
					annee_mois_fin: "Saisir l'année de fin"
				}
			}
		});

		// ------------------------------------------------------- Notaires	
		$("#btSupprimerNotaires").click(function() {
			var chaine = "";
			// Un seul élément
			if (document.forms['listeNotaires'].elements['supp[]'].checked) {
				chaine += document.forms['listeNotaires'].elements['supp[]'].id + "\n";
			}
			// Au moins deux éléments 
			for (var i = 0; i < document.forms['listeNotaires'].elements['supp[]'].length; i++) {
				if (document.forms['listeNotaires'].elements['supp[]'][i].checked) {
					chaine += document.forms['listeNotaires'].elements['supp[]'][i].id + "\n";
				}
			}
			if (chaine == "") {
				alert("Pas de notaire sélectionnée");
			} else {
				Message = "Etes-vous sûr de supprimer ces notaires :\n" + chaine + "?";
				if (confirm(Message)) {
					document.forms['listeNotaires'].submit();
				}
			}
		});

		$("#btAjoutNotaire").click(function() {
			$("#mode").val('MENU_AJOUTER_NOTAIRE');
		});

		$("#btAjouterNotaire").click(function() {
			$("#mode").val('AJOUTER_NOTAIRE');
		});

		$("#btModifierNotaire").click(function() {
			$("#mode").val('MODIFIER_NOTAIRE');
		});

		$("#maj_notaire").validate({
			rules: {
				nom: {
					required: true
				}
			},
			messages: {
				nom: {
					required: "Saisir au moins le nom du notaire"
				}
			}
		});

		$("#btCorrigerGroupe").click(function() {
			$("#mode").val('MENU_CORRIGER_GROUPE');
		});

		$("#cor_groupe_liasses").validate({
			rules: {
				numeros: {
					groupe_cotes: true,
					required: true
				},
				idf_commune: {
					nom_prenom_commune: true
				}
			},
			messages: {
				numeros: {
					groupe_cotes: "La liste des numéros de liasses doit être composée de n1-n2 ou n1,n2,n3,... ou une combinaison des deux",
					required: "La liste des numéros de liasses est obligatoire"
				},
				idf_commune: {
					nom_prenom_commune: "Indiquer au moins une information à corriger"
				}
			}
		});

	});
</script>
<?php
print('</head>');
print("<body>");
print('<div class="container">');

require_once __DIR__ . '/../Commun/menu.php';

$a_depts_depose_ad = array('' => '') + $connexionBD->liste_valeur_par_clef("SELECT idf,nom FROM departement order by nom");
//$a_depts_depose_ad[''] = '';
$a_formes_liasses = array(0 => '') + $connexionBD->liste_valeur_par_clef("SELECT idf,nom FROM forme_liasse order by nom");
$a_mois = array(
	"", "01" => "01", "02" => "02", "03" => "03", "04" => "04", "05" => "05", "06" => "06",
	"07" => "07", "08" => "08", "09" => "09", "10" => "10", "11" => "11", "12" => "12",
	"Vendémiaire" => "Vendémiaire", "Brumaire" => "Brumaire", "Frimaire" => "Frimaire",
	"Nivôse" => "Nivôse", "Pluviôse" => "Pluviôse", "Ventôse" => "Ventôse",
	"Germinal" => "Germinal", "Floréal" => "Floréal", "Prairial" => "Prairial",
	"Messidor" => "Messidor", "Thermidor" => "Thermidor", "Fructidor" => "Fructidor"
);
$a_communes = array(0 => '') + $connexionBD->liste_valeur_par_clef("SELECT idf,nom FROM commune_acte order by nom");
$a_toutes_communes = $a_communes + array(-9 => 'Commune inconnue');
//$a_communes[0] = '';

$a_serie_liasse = array(0 => '') + $connexionBD->liste_valeur_par_clef("SELECT serie_liasse, nom FROM serie_liasse order by ordre");
//$a_serie_liasse[0] = '';

require_once('GestionLiassesNotarialesFc.php');
require_once('GestionLiassesNotarialesFcPeriodes.php');
require_once('GestionLiassesNotarialesFcNotaires.php');
switch ($gst_mode) {
	case 'LISTE': {
			unset($_SESSION['groupe']);
			menu_liste($connexionBD);
			break;
		}
	case 'MENU_MODIFIER': {
			menu_modifier($connexionBD, $gst_cote_liasse, $a_depts_depose_ad, $a_formes_liasses);
			break;
		}
	case 'MODIFIER': {
			$st_info_compl = escape_apostrophe(trim($_POST['info_compl']));
			$st_in_liasse_depose_AD = empty($_POST['depose_ad']) ? 0 : $_POST['depose_ad'];
			$st_idf_dept_depose_AD = empty($_POST['dept_depose_ad']) ? null : $_POST['dept_depose_ad'];
			$st_in_liasse_consultable = empty($_POST['liasse_consult']) ? 0 : $_POST['liasse_consult'];
			$i_idf_forme_liasse = $_POST['forme_liasse'];
			$st_libelle = escape_apostrophe(trim($_POST['libelle']));
			//---- modif UTF8
			$st_libelle = mb_convert_encoding($st_libelle, 'cp1252', 'UTF8');
			$st_info_compl = mb_convert_encoding($st_info_compl, 'cp1252', 'UTF8');
			//---- fin modif UTF8
			$st_requete = "update liasse set " .
				"in_liasse_depose_AD='$st_in_liasse_depose_AD', " .
				"idf_dept_depose_AD='$st_idf_dept_depose_AD', in_liasse_consultable='$st_in_liasse_consultable', " .
				"idf_forme_liasse='$i_idf_forme_liasse', info_complementaires='$st_info_compl', libelle_liasse='$st_libelle' " .
				"where cote_liasse='" . $gst_cote_liasse . "'";
			$connexionBD->execute_requete($st_requete);
			menu_liste($connexionBD);
			break;
		}
	case 'MENU_AJOUTER': {
			menu_ajouter($connexionBD, $a_depts_depose_ad, $a_formes_liasses);
			break;
		}
	case 'AJOUTER': {
			$st_numero = $_POST['numero'];
			$st_init_dixm = $_SESSION['init_dixm'] == 'z' ? '0' : $_SESSION['init_dixm'];
			$st_init_mill = $_SESSION['init_mill'] == 'z' ? '0' : $_SESSION['init_mill'];
			$st_init_cent = $_SESSION['init_cent'] == 'z' ? '0' : $_SESSION['init_cent'];
			$st_init_dix  = $_SESSION['init_dix'] == 'z' ? '0' : $_SESSION['init_dix'];
			$st_cote = $_SESSION['serie_liasse'] . "-" . $st_init_dixm . $st_init_mill . $st_init_cent . $st_init_dix . $st_numero;
			$a_liasse = $connexionBD->sql_select_multipleUtf8("select cote_liasse from liasse where cote_liasse='" . $st_cote . "'");
			if (count($a_liasse) != 0) {
				print('<div align=center class="alert alert-danger">La liasse ' . $st_cote . ' existe déjà. Ajout impossible.</div><br>');
			} else {
				$st_type_serie = substr($_SESSION['serie_liasse'], 1, 1);
				$st_libelle = escape_apostrophe(trim($_POST['libelle']));
				$st_info_compl = escape_apostrophe(trim($_POST['info_compl']));
				$st_in_liasse_depose_AD = empty($_POST['depose_ad']) ? 0 : $_POST['depose_ad'];
				$st_idf_dept_depose_AD = empty($_POST['dept_depose_ad']) ? 0 : $_POST['dept_depose_ad'];
				$st_in_liasse_consultable = empty($_POST['liasse_consult']) ? 0 : $_POST['liasse_consult'];
				$i_idf_forme_liasse = $_POST['forme_liasse'];
				//---- modif UTF8
				$st_type_serie = mb_convert_encoding($st_type_serie, 'cp1252', 'UTF8');
				$st_libelle = mb_convert_encoding($st_libelle, 'cp1252', 'UTF8');
				$st_info_compl = mb_convert_encoding($st_info_compl, 'cp1252', 'UTF8');
				$st_in_liasse_depose_AD = mb_convert_encoding($st_in_liasse_depose_AD, 'cp1252', 'UTF8');
				$st_idf_dept_depose_AD = mb_convert_encoding($st_idf_dept_depose_AD, 'cp1252', 'UTF8');
				$st_in_liasse_consultable = mb_convert_encoding($st_in_liasse_consultable, 'cp1252', 'UTF8');
				//---- fin modif UTF8
				$st_requete = "insert into liasse (`cote_liasse`, `type_serie`, `in_liasse_depose_AD`, `idf_dept_depose_AD`, `in_liasse_consultable`, " .
					"                    `idf_forme_liasse`, `info_complementaires`, `libelle_liasse` ) " .
					"VALUES ('" . $st_cote . "', '" . $st_type_serie . "', " . $st_in_liasse_depose_AD . ", " . $st_idf_dept_depose_AD . ", " . $st_in_liasse_consultable . ", " .
					$i_idf_forme_liasse . ", '" . $st_info_compl . "', '" . $st_libelle . "')";
				$connexionBD->execute_requete($st_requete);
			}
			menu_modifier($connexionBD, $st_cote, $a_depts_depose_ad, $a_formes_liasses);
			//menu_liste($connexionBD);  
			break;
		}
	case 'MENU_AJOUTER_GROUPE': {
			menu_ajouter_groupe($connexionBD, $a_depts_depose_ad, $a_formes_liasses, $a_toutes_communes);
			break;
		}
	case 'AJOUTER_GROUPE': {
			$st_type_serie 				= substr($_SESSION['serie_liasse'], 1, 1);
			$st_info_compl 				= escape_apostrophe(trim($_POST['info_compl']));
			$st_in_liasse_depose_AD 	= empty($_POST['depose_ad']) ? 0 : $_POST['depose_ad'];
			$st_idf_dept_depose_AD 		= empty($_POST['dept_depose_ad']) ? 0 : $_POST['dept_depose_ad'];
			$st_in_liasse_consultable 	= empty($_POST['liasse_consult']) ? 0 : $_POST['liasse_consult'];
			$i_idf_forme_liasse 		= $_POST['forme_liasse'];
			$st_nom						= strtoupper(escape_apostrophe(trim($_POST['nom'])));
			$st_prenom					= empty($_POST['prenom']) ? '' : ucwords(escape_apostrophe(trim($_POST['prenom'])));
			$st_commentaire 			= empty($_POST['commentaire']) ? '' : escape_apostrophe(trim($_POST['commentaire']));
			$st_lieu					= empty($_POST['lieu']) ? '' : ucwords(escape_apostrophe(trim($_POST['lieu'])));
			$i_idf_commune				= $_POST['idf_commune'];
			//---- modif UTF8
			$st_type_serie 				= mb_convert_encoding($st_type_serie, 'cp1252', 'UTF8');
			$st_info_compl 				= mb_convert_encoding($st_info_compl, 'cp1252', 'UTF8');
			$st_in_liasse_depose_AD 	= mb_convert_encoding($st_in_liasse_depose_AD, 'cp1252', 'UTF8');
			$st_idf_dept_depose_AD 		= mb_convert_encoding($st_idf_dept_depose_AD, 'cp1252', 'UTF8');
			$st_in_liasse_consultable 	= mb_convert_encoding($st_in_liasse_consultable, 'cp1252', 'UTF8');
			$st_nom						= mb_convert_encoding($st_nom, 'cp1252', 'UTF8');
			$st_prenom					= mb_convert_encoding($st_prenom, 'cp1252', 'UTF8');
			$st_commentaire 			= mb_convert_encoding($st_commentaire, 'cp1252', 'UTF8');
			$st_lieu					= mb_convert_encoding($st_lieu, 'cp1252', 'UTF8');
			//---- fin modif UTF8
			$a_cotes = extraction_liste($_POST['numeros'], $_SESSION['serie_liasse']);
			$check = true;
			foreach ($a_cotes as $st_cote) {
				$a_liasse = $connexionBD->sql_select_multipleUtf8("select cote_liasse from liasse where cote_liasse='" . $st_cote . "'");
				if (count($a_liasse) != 0) {
					print('<div align=center class="alert alert-danger">La liasse ' . $st_cote . ' existe déjà. Ajout impossible.</div><br>');
					$check = false;
				} else {
					$st_requete = "insert into liasse (`cote_liasse`, `type_serie`, `in_liasse_depose_AD`, `idf_dept_depose_AD`, `in_liasse_consultable`, " .
						"                    `idf_forme_liasse`, `info_complementaires` ) " .
						"VALUES ('" . $st_cote . "', '" . $st_type_serie . "', " . $st_in_liasse_depose_AD . ", " . $st_idf_dept_depose_AD . ", " . $st_in_liasse_consultable . ", " .
						$i_idf_forme_liasse . ", '" . $st_info_compl . "')";
					$connexionBD->execute_requete($st_requete);
					$st_requete = "INSERT INTO `liasse_notaire`(`cote_liasse`, `nom_notaire`, `prenom_notaire`, " .
						"            `commentaire`, `libelle_lieu`, `idf_commune_etude`) " .
						"VALUES ('" . $st_cote . "', '" . $st_nom . "', '" . $st_prenom . "', '" . $st_commentaire . "', " .
						"        '" . $st_lieu . "', " . $i_idf_commune . ")";
					$connexionBD->execute_requete($st_requete);
					maj_libelle_notaire($connexionBD, $st_cote);
				}
			}
			if ($check) {
				menu_liste($connexionBD);
			}
			break;
		}
	case 'SUPPRIMER': {
			$a_liste_liasses = $_POST['supp'];
			foreach ($a_liste_liasses as $st_cote_liasse) {
				$a_liasse_notaire = $connexionBD->sql_select_multipleUtf8("select cote_liasse from liasse_notaire where cote_liasse='" . $st_cote_liasse . "'");
				$a_liasse_dates = $connexionBD->sql_select_multipleUtf8("select cote_liasse from liasse_dates where cote_liasse='" . $st_cote_liasse . "'");
				$a_liasse_photo = $connexionBD->sql_select_multipleUtf8("select cote_liasse from liasse_photo where cote_liasse='" . $st_cote_liasse . "'");
				$a_liasse_programmation = $connexionBD->sql_select_multipleUtf8("select cote_liasse from liasse_programmation where cote_liasse='" . $st_cote_liasse . "'");
				$a_liasse_publication_papier = $connexionBD->sql_select_multipleUtf8("select cote_liasse from liasse_publication_papier where cote_liasse='" . $st_cote_liasse . "'");
				$a_liasse_releve = $connexionBD->sql_select_multipleUtf8("select cote_liasse from liasse_releve where cote_liasse='" . $st_cote_liasse . "'");
				if (
					count($a_liasse_notaire) == 0 && count($a_liasse_dates) == 0 && count($a_liasse_photo) == 0 &&
					count($a_liasse_programmation) == 0 && count($a_liasse_publication_papier) == 0 && count($a_liasse_releve) == 0
				) {
					$connexionBD->execute_requete("delete from liasse where cote_liasse='" . $st_cote_liasse . "'");
				} else {
					print('<div align="center" class="alert alert-danger">Toutes les informations associ&eacute;es &agrave; la liasse ' . $st_cote_liasse .
						' doivent être supprimées auparavant</div><br>');
				}
			}
			menu_liste($connexionBD);
			break;
		}
		// gestion des périodes
	case 'LISTE_PERIODE': {
			menu_liste_periode($connexionBD, $gst_cote_liasse);
			break;
		}
	case 'MENU_MODIFIER_PERIODE': {
			list($gst_cote_liasse)
				= $connexionBD->sql_select_listeUtf8("select cote_liasse from liasse_dates where idf = " . $gi_idf_periode);
			menu_modifier_periode($connexionBD, $gst_cote_liasse, $gi_idf_periode, $a_mois);
			break;
		}
	case 'MODIFIER_PERIODE': {
			$st_cote_liasse	= $_POST['cote_liasse'];
			$i_idf_periode	= $_POST['idf_periode'];
			$st_annee_debut = $_POST['annee_debut'];
			$st_mois_debut = $a_mois[$_POST['mois_debut']];
			$st_annee_fin = $_POST['annee_fin'];
			$st_mois_fin = $a_mois[$_POST['mois_fin']];
			//---- modif UTF8
			$st_cote_liasse = mb_convert_encoding($st_cote_liasse, 'cp1252', 'UTF8');
			$st_annee_debut = mb_convert_encoding($st_annee_debut, 'cp1252', 'UTF8');
			$st_mois_debut = mb_convert_encoding($st_mois_debut, 'cp1252', 'UTF8');
			$st_annee_fin = mb_convert_encoding($st_annee_fin, 'cp1252', 'UTF8');
			$st_mois_fin = mb_convert_encoding($st_mois_fin, 'cp1252', 'UTF8');
			//---- fin modif UTF8
			//$st_date_debut = calculer_date_debut( $connexionBD, $st_annee_debut, $st_mois_debut, $a_mois);
			//$st_date_fin = calculer_date_fin( $connexionBD, $st_annee_debut, $st_mois_debut, $st_annee_fin, $st_mois_fin, $a_mois);
			$st_date_debut = calculer_date_debut($connexionBD, $st_annee_debut, $st_mois_debut);
			$st_date_fin = calculer_date_fin($connexionBD, $st_annee_debut, $st_mois_debut, $st_annee_fin, $st_mois_fin);
			$st_libelle = calculer_libelle_periode($st_annee_debut, $st_mois_debut, $st_annee_fin, $st_mois_fin);
			$st_requete = "update liasse_dates set " .
				"    annee_debut_periode='" . $st_annee_debut . "', mois_debut_periode='" . $st_mois_debut . "', " .
				"    annee_fin_periode='" . $st_annee_fin . "',mois_fin_periode='" . $st_mois_fin . "', " .
				"    date_debut_periode=str_to_date('" . $st_date_debut . "', '%Y-%m-%d'), " .
				"    date_fin_periode=str_to_date('" . $st_date_fin . "', '%Y-%m-%d'), " .
				"    libelle_periode='" . $st_libelle . "' " .
				"where idf=" . $i_idf_periode . "";
			$connexionBD->execute_requete($st_requete);
			maj_libelle_periode($connexionBD, $st_cote_liasse);
			menu_liste_periode($connexionBD, $st_cote_liasse, $a_mois);
			break;
		}
	case 'MENU_AJOUTER_PERIODE': {
			menu_ajouter_periode($gst_cote_liasse, $a_mois);
			break;
		}
	case 'AJOUTER_PERIODE': {
			$st_cote_liasse	= $_POST['cote_liasse'];
			$st_annee_debut = $_POST['annee_debut'];
			$st_mois_debut = $_POST['mois_debut'];
			$st_annee_fin = $_POST['annee_fin'];
			$st_mois_fin = $_POST['mois_fin'];
			//---- modif UTF8
			$st_cote_liasse = mb_convert_encoding($st_cote_liasse, 'cp1252', 'UTF8');
			$st_annee_debut = mb_convert_encoding($st_annee_debut, 'cp1252', 'UTF8');
			$st_mois_debut = mb_convert_encoding($st_mois_debut, 'cp1252', 'UTF8');
			$st_annee_fin = mb_convert_encoding($st_annee_fin, 'cp1252', 'UTF8');
			$st_mois_fin = mb_convert_encoding($st_mois_fin, 'cp1252', 'UTF8');
			//---- fin modif UTF8
			$st_date_debut = calculer_date_debut($connexionBD, $st_annee_debut, $st_mois_debut);
			$st_date_fin = calculer_date_fin($connexionBD, $st_annee_debut, $st_mois_debut, $st_annee_fin, $st_mois_fin);
			$st_libelle = calculer_libelle_periode($st_annee_debut, $st_mois_debut, $st_annee_fin, $st_mois_fin);
			$st_requete = "INSERT INTO `liasse_dates`(`cote_liasse`, `annee_debut_periode`, `mois_debut_periode`, " .
				"            `annee_fin_periode`, `mois_fin_periode`, `date_debut_periode`, " .
				"            `date_fin_periode`, `libelle_periode`) " .
				"VALUES ('" . $st_cote_liasse . "', '" . $st_annee_debut . "', '" . $st_mois_debut . "', " .
				"        '" . $st_annee_fin . "', '" . $st_mois_fin . "', str_to_date('" . $st_date_debut . "', '%Y-%m-%d'), " .
				"        str_to_date('" . $st_date_fin . "', '%Y-%m-%d'), '" . $st_libelle . "')";
			$connexionBD->execute_requete($st_requete);
			maj_libelle_periode($connexionBD, $st_cote_liasse);
			menu_liste_periode($connexionBD, $st_cote_liasse, $a_mois);
			break;
		}
	case 'SUPPRIMER_PERIODE': {
			$st_cote_liasse	= $_POST['cote_liasse'];
			$a_liste_periodes = $_POST['supp'];
			foreach ($a_liste_periodes as $st_idf) {
				$i_idf = substr($st_idf, 3, 6);
				$connexionBD->execute_requete("delete from liasse_dates where idf=" . $i_idf);
			}
			maj_libelle_periode($connexionBD, $st_cote_liasse);
			menu_liste_periode($connexionBD, $st_cote_liasse, $a_mois);
			break;
		}
		// gestion des notaires
	case 'LISTE_NOTAIRE': {
			menu_liste_notaire($connexionBD, $gst_cote_liasse, $a_communes);
			break;
		}
	case 'MENU_MODIFIER_NOTAIRE': {
			list($gst_cote_liasse)
				= $connexionBD->sql_select_listeUtf8("select cote_liasse from liasse_notaire where idf = " . $gi_idf_notaire);
			menu_modifier_notaire($connexionBD, $gst_cote_liasse, $gi_idf_notaire, $a_communes);
			break;
		}
	case 'MODIFIER_NOTAIRE': {
			$st_cote_liasse	= $_POST['cote_liasse'];
			$i_idf_notaire	= $_POST['idf_notaire'];
			$st_nom			= strtoupper(escape_apostrophe(trim($_POST['nom'])));
			$st_prenom		= ucwords(empty($_POST['prenom']) ? '' : escape_apostrophe(trim($_POST['prenom'])));
			$st_commentaire = empty($_POST['commentaire']) ? '' : escape_apostrophe(trim($_POST['commentaire']));
			$st_lieu		= ucwords(empty($_POST['lieu']) ? '' : escape_apostrophe(trim($_POST['lieu'])));
			$i_idf_commune	= $_POST['idf_commune'];
			//---- modif UTF8
			$st_cote_liasse = mb_convert_encoding($st_cote_liasse, 'cp1252', 'UTF8');
			$st_nom = mb_convert_encoding($st_nom, 'cp1252', 'UTF8');
			$st_prenom = mb_convert_encoding($st_prenom, 'cp1252', 'UTF8');
			$st_commentaire = mb_convert_encoding($st_commentaire, 'cp1252', 'UTF8');
			$st_lieu = mb_convert_encoding($st_lieu, 'cp1252', 'UTF8');
			//---- fin modif UTF8
			$st_requete = "update liasse_notaire set " .
				"nom_notaire='$st_nom', prenom_notaire='$st_prenom', commentaire='$st_commentaire', " .
				"libelle_lieu='$st_lieu',idf_commune_etude=$i_idf_commune " .
				"where idf=$i_idf_notaire";
			$connexionBD->execute_requete($st_requete);
			maj_libelle_notaire($connexionBD, $st_cote_liasse);
			menu_liste_notaire($connexionBD, $st_cote_liasse, $a_communes);
			break;
		}
	case 'MENU_AJOUTER_NOTAIRE': {
			menu_ajouter_notaire($gst_cote_liasse, $a_communes);
			break;
		}
	case 'AJOUTER_NOTAIRE': {
			$st_cote_liasse	= $_POST['cote_liasse'];
			$st_nom			= strtoupper(escape_apostrophe(trim($_POST['nom'])));
			$st_prenom		= ucwords(empty($_POST['prenom']) ? '' : escape_apostrophe(trim($_POST['prenom'])));
			$st_commentaire = empty($_POST['commentaire']) ? '' : escape_apostrophe(trim($_POST['commentaire']));
			$st_lieu		= ucwords(empty($_POST['lieu']) ? '' : escape_apostrophe(trim($_POST['lieu'])));
			$i_idf_commune	= $_POST['idf_commune'];
			//---- modif UTF8
			$st_cote_liasse = mb_convert_encoding($st_cote_liasse, 'cp1252', 'UTF8');
			$st_nom = mb_convert_encoding($st_nom, 'cp1252', 'UTF8');
			$st_prenom = mb_convert_encoding($st_prenom, 'cp1252', 'UTF8');
			$st_commentaire = mb_convert_encoding($st_commentaire, 'cp1252', 'UTF8');
			$st_lieu = mb_convert_encoding($st_lieu, 'cp1252', 'UTF8');
			//---- fin modif UTF8
			$st_requete = "INSERT INTO `liasse_notaire`(`cote_liasse`, `nom_notaire`, `prenom_notaire`, " .
				"            `commentaire`, `libelle_lieu`, `idf_commune_etude`) " .
				"VALUES ('" . $st_cote_liasse . "', '" . $st_nom . "', '" . $st_prenom . "', '" . $st_commentaire . "', " .
				"        '" . $st_lieu . "', " . $i_idf_commune . ")";
			$connexionBD->execute_requete($st_requete);
			maj_libelle_notaire($connexionBD, $st_cote_liasse);
			menu_liste_notaire($connexionBD, $st_cote_liasse, $a_communes);
			break;
		}
	case 'SUPPRIMER_NOTAIRE': {
			$st_cote_liasse	= $_POST['cote_liasse'];
			$a_liste_notaires = $_POST['supp'];
			foreach ($a_liste_notaires as $st_idf) {
				$i_idf = substr($st_idf, 3, 6);
				$connexionBD->execute_requete("delete from liasse_notaire where idf=$i_idf");
			}
			maj_libelle_notaire($connexionBD, $st_cote_liasse);
			menu_liste_notaire($connexionBD, $st_cote_liasse, $a_communes);
			break;
		}
	case 'MENU_CORRIGER_GROUPE': {
			menu_corriger_groupe($connexionBD, $a_communes);
			break;
		}
	case 'VERIFIER_GROUPE': {
			$_SESSION['groupe']['serie'] 	= $_POST['serie'];
			$_SESSION['groupe']['numeros'] 	= $_POST['numeros'];
			$_SESSION['groupe']['nom']		= strtoupper(escape_apostrophe(trim($_POST['nom'])));
			$_SESSION['groupe']['prenom']	= empty($_POST['prenom']) ? '' : ucwords(escape_apostrophe(trim($_POST['prenom'])));
			$_SESSION['groupe']['commune']	= $_POST['idf_commune'];
			$_SESSION['groupe']['commentaire'] 	= $_POST['commentaire'];
			$_SESSION['groupe']['cotes']	= extraction_liste($_POST['numeros'], $_SESSION['serie_liasse']);
			menu_confirmer_correction_groupe(
				$connexionBD,
				$a_communes,
				$_SESSION['groupe']['cotes'],
				$_SESSION['groupe']['numeros'],
				$_SESSION['groupe']['nom'],
				$_SESSION['groupe']['prenom'],
				$_SESSION['groupe']['commune'],
				$_SESSION['groupe']['commentaire']
			);
			break;
		}
	case 'VERIFIER_GROUPE_SUITE': {
			menu_confirmer_correction_groupe(
				$connexionBD,
				$a_communes,
				$_SESSION['groupe']['cotes'],
				$_SESSION['groupe']['numeros'],
				$_SESSION['groupe']['nom'],
				$_SESSION['groupe']['prenom'],
				$_SESSION['groupe']['commune'],
				$_SESSION['groupe']['commentaire']
			);
			break;
		}
	case 'CORRIGER_GROUPE': {
			$st_serie 				= $_POST['serie'];
			$st_numeros 			= $_POST['numeros'];
			$st_nom					= strtoupper(escape_apostrophe(trim($_POST['nom'])));
			$st_prenom				= empty($_POST['prenom']) ? '' : ucwords(escape_apostrophe(trim($_POST['prenom'])));
			$i_idf_commune			= $_POST['idf_commune'];
			$st_commentaire			= $_POST['commentaire'];
			//---- modif UTF8
			$st_nom					= mb_convert_encoding($st_nom, 'cp1252', 'UTF8');
			$st_prenom				= mb_convert_encoding($st_prenom, 'cp1252', 'UTF8');
			$st_commentaire			= escape_apostrophe(mb_convert_encoding($st_commentaire, 'cp1252', 'UTF8'));
			//---- fin modif UTF8
			$a_cotes = extraction_liste($st_numeros, $st_serie);
			$st_liste = compose_liste_in($a_cotes);
			if ($st_nom != "" || $st_prenom != "" || $i_idf_commune != 0 || $st_commentaire != "") {
				$st_requete = "update liasse_notaire set ";
				if ($st_nom != "") {
					$st_requete .= "nom_notaire='$st_nom', ";
				}
				if ($st_prenom != "") {
					$st_requete .= "prenom_notaire='$st_prenom', ";
				}
				if ($i_idf_commune != 0) {
					$st_requete .= "idf_commune_etude=" . $i_idf_commune . ", ";
				}
				if ($st_commentaire != "") {
					$st_requete .= "commentaire='$st_commentaire', ";
				}
				$i_len = strlen($st_requete);
				$st_requete = substr($st_requete, 0, $i_len - 2);
				$st_requete .= " where cote_liasse in (" . $st_liste . ")";
				$connexionBD->execute_requete($st_requete);
				foreach ($a_cotes as $st_cote_liasse) {
					maj_libelle_notaire($connexionBD, $st_cote_liasse);
				}
			}
			menu_liste($connexionBD);
			break;
		}
}
print('</div></body></html>');
