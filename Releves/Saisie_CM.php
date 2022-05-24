<?php

/*
CREATE TABLE IF NOT EXISTS `cm_notaires`
(
  `idf` smallint(5) unsigned NOT NULL auto_increment,
  `notaire` varchar(20),
  `paroisse` varchar(20),
  `cote` varchar(10),
  `ref_perso` varchar(10),
  `date_cm` varchar(10),
  `epx_nom` varchar(25),
  `epx_prenom` varchar(25),
  `epx_prof` varchar(25),
  `epx_nom_pere` varchar(25),
  `epx_prenom_pere` varchar(25),
  `epx_prof_pere` varchar(25),
  `epx_nom_mere` varchar(25),
  `epx_prenom_mere` varchar(25),
  `epx_village` varchar(25),
  `epx_paroisse` varchar(25),
  `epx_temoins` text,
  `epe_nom` varchar(25),
  `epe_prenom` varchar(25),
  `epe_nom_pere` varchar(25),
  `epe_prenom_pere` varchar(25),
  `epe_prof_pere` varchar(25),
  `epe_nom_mere` varchar(25),
  `epe_prenom_mere` varchar(25),
  `epe_village` varchar(25),
  `epe_paroisse` varchar(25),
  `epe_temoins` text,
   PRIMARY KEY (`idf`)
);
*/

$gst_chemin = "../";
require_once __DIR__ . '/../Commun/config.php';
require_once __DIR__ . '/../Commun/constantes.php';
require_once __DIR__ . '/../Commun/Identification.php';
require_once __DIR__ . '/../Commun/VerificationDroits.php';
//verifie_privilege(DROIT_UTILITAIRES);
require_once __DIR__ . '/../Commun/ConnexionBD.php';
require_once __DIR__ . '/../Commun/PaginationTableau.php';
require_once __DIR__ . '/../Commun/commun.php';

print("<head>");
print('<meta http-equiv="Content-Type" content="text/html; charset=cp1252" />');
print('<meta http-equiv="content-language" content="fr" /> ');
print("<link href='../assets/css/styles.css' type='text/css' rel='stylesheet'/>");
print("<script src='../assets/js/jquery-min.js' type='text/javascript'></script>");
//print("<script src='$gst_chemin/Commun/menu.js' type='text/javascript'></script>");
?>
<script type='text/javascript'>
    function isBisextile(date_a_verifier) {

        // On s�pare la date en 3 variables pour v�rification, parseInt() converti du texte en entier
        j = parseInt(date_a_verifier.split("/")[0], 10); // jour
        m = parseInt(date_a_verifier.split("/")[1], 10); // mois
        a = parseInt(date_a_verifier.split("/")[2], 10); // ann�e

        // D�finition du dernier jour de f�vrier
        // Ann�e bissextile si annn�e divisible par 4 et que ce n'est pas un si�cle, ou bien si divisible par 400
        if (a % 4 == 0 && a % 100 != 0 || a % 400 == 0) fev = 29;
        else fev = 28;

        // Nombre de jours pour chaque mois
        nbJours = new Array(31, fev, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);

        // Enfin, retourne vrai si le jour est bien entre 1 et le bon nombre de jours, idem pour les mois, sinon retourn faux
        return (m >= 1 && m <= 12 && j >= 1 && j <= nbJours[m - 1]);
    }

    function VerifieChamps(Formulaire) {
        var date_ptn = /^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$/;
        var date_cm = document.forms[Formulaire].date_cm.value;
        var ListeErreurs = "";
        if (date_cm == "") {
            ListeErreurs += "La date du contrat est obligatoire\n";
        }
        if (!date_ptn.test(date_cm)) {
            ListeErreurs += "La date du contrat doit �tre de la forme : JJ/MM/AAAA\n";
        }
        if (!isBisextile(date_cm)) {
            ListeErreurs += "La date du contrat n'est pas correcte\n";
        }
        if (ListeErreurs != "") {
            alert(ListeErreurs);
        } else {
            document.forms[Formulaire].submit();
        }
    }
</script>
<?php
print('<title>Saisie des CM</title>');
print('</head>');
print('<body>');

$gst_mode = empty($_POST['mode']) ? 'LISTE' : $_POST['mode'];
if (isset($_GET['mod'])) {
    $gst_mode = 'MENU_MODIFIER';
    $gi_idf_contrats = (int) $_GET['mod'];
} else
    $gi_idf_contrats = isset($_POST['idf_contrats']) ? (int) $_POST['idf_contrats'] : 0;

$gi_num_page_cour = empty($_GET['num_page']) ? 1 : $_GET['num_page'];

// Retourne la premi�re lettre en majuscule et les autres en minuscule
function Maj_et_Min($str)
{
    $str = strtolower($str);
    $tmp_tab = explode(" ", $str);
    for ($i = 0; $i < sizeof($tmp_tab); $i++) {
        $tmp_tab[$i] = ucfirst($tmp_tab[$i]);
    }
    return implode(" ", $tmp_tab);
}

/**
 * Affiche la liste des contrats
 * @param object $rconnexionBD
 */
function menu_liste($rconnexionBD)
{
    global $gi_num_page_cour;
    $st_requete = "select idf,epx_nom, epx_prenom, epe_nom, epe_prenom, concat(substring(date_cm,9,2),'/',substring(date_cm,6,2),'/',substring(date_cm,1,4)) from `cm_notaires` order by epx_nom, epe_nom, date_cm";
    $a_liste_contrats = $rconnexionBD->liste_valeur_par_clef($st_requete);
    if (count($a_liste_contrats) != 0) {
        $pagination = new PaginationTableau(basename(__FILE__), 'num_page', $rconnexionBD->nb_lignes(), NB_LIGNES_PAR_PAGE, DELTA_NAVIGATION, array('Nom �poux', 'Pr�nom �poux', 'Nom �pouse', 'Pr�nom �pouse', 'Date du contrat', 'Modifier'));
        $pagination->init_param_bd($rconnexionBD, $st_requete);
        $pagination->init_page_cour($gi_num_page_cour);
        $pagination->affiche_entete_liens_navigation();
        print("<br>");
        $pagination->affiche_tableau_edition(basename(__FILE__));
        print("<br>");
        $pagination->affiche_entete_liens_navigation();
    } else
        print("<div align=center>Pas de contrat</div><BR>");

    print("<form   method=\"post\">");
    print("<input type=hidden name=mode value=MENU_AJOUTER>");
    print("<div align=center><input type=submit value=\"Ajouter un contrat\"></div>");
    print('</form>');
}

/**
 * Affiche de la table d'�dition
 * @param string $pst_notaire nom du notaire
 * @param string $pst_paroisse paroisse du notaire
 * @param string $pst_cote cote du cm aux ad
 * @param string $pst_ref_perso r�f�rence perso du document
 * @param string $pst_date_cm date du cm
 
 * @param string $pst_epx_nom nom de l'�poux
 * @param string $pst_epx_prenom pr�nom de l'�poux
 * @param string $pst_epx_prof profession de l'�poux
 * @param string $pst_epx_nom_pere nom du p�re de l'�poux
 * @param string $pst_epx_prenom_pere pr�nom du p�re de l'�poux
 * @param string $pst_epx_prof_pere profession du p�re de l'�poux
 * @param string $pst_epx_nom_mere nom de la m�re de l'�poux
 * @param string $pst_epx_prenom_mere pr�nom de la m�re de l'�poux
 * @param string $pst_epx_village village de l'�poux
 * @param string $pst_epx_paroisse paroisse de l'�poux
 * @param string $pst_epx_temoins t�moins de l'�poux
 
 * @param string $pst_epe_nom nom de l'�pouse
 * @param string $pst_epe_prenom pr�nom de l'�pouse
 * @param string $pst_epe_nom_pere nom du p�re de l'�pouse
 * @param string $pst_epe_prenom_pere pr�nom du p�re de l'�pouse
 * @param string $pst_epe_prof_pere profession du p�re de l'�pouse
 * @param string $pst_epe_nom_mere nom de la m�re de l'�pouse
 * @param string $pst_epe_prenom_mere pr�nom de la m�re de l'�pouse
 * @param string $pst_epe_village village de l'�pouse
 * @param string $pst_epe_paroisse paroisse de l'�pouse
 * @param string $pst_epe_temoins t�moins de l'�pouse
 */
function menu_edition(
    $pst_notaire,
    $pst_paroisse,
    $pst_cote,
    $pst_ref_perso,
    $pst_date_cm,
    $pst_epx_nom,
    $pst_epx_prenom,
    $pst_epx_prof,
    $pst_epx_nom_pere,
    $pst_epx_prenom_pere,
    $pst_epx_prof_pere,
    $pst_epx_nom_mere,
    $pst_epx_prenom_mere,
    $pst_epx_village,
    $pst_epx_paroisse,
    $pst_epx_temoins,
    $pst_epe_nom,
    $pst_epe_prenom,
    $pst_epe_nom_pere,
    $pst_epe_prenom_pere,
    $pst_epe_prof_pere,
    $pst_epe_nom_mere,
    $pst_epe_prenom_mere,
    $pst_epe_village,
    $pst_epe_paroisse,
    $pst_epe_temoins
) {
    print("<span style=\"font-weight: bold;\"><br></span>");
    print("<table style=\"text-align: left; width: 917px; height: 98px;\" border=\"1\" cellpadding=\"2\" cellspacing=\"2\">");
    print("<caption><big><big><span style=\"font-weight: bold;\">Saisie &nbsp;des contrats&nbsp;de &nbsp;mariage</span></big></big></caption>");
    print("<tbody><tr><td style=\"text-align: left;\" \"background-color: rgb(60, 20, 255);\">");
    print("Nom du notaire <input name=notaire value=\"$pst_notaire\" size=25> &nbsp;&nbsp;");
    print("Paroisse de l'&eacute;tude <input name=paroisse value=\"$pst_paroisse\" size=25><BR>");
    print("Cote AD &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input name=cote value=\"$pst_cote\" size=25> &nbsp;&nbsp;");
    print("Ref perso &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <input name=ref_perso value=\"$pst_ref_perso\" size=25><BR><BR>");
    if ($pst_date_cm != '') {
        $pst_date_cm = sprintf("%02s/%02s/%4s", substr($pst_date_cm, 8, 2), substr($pst_date_cm, 5, 2), substr($pst_date_cm, 0, 4));
    }
    print("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Date du contrat <input name=date_cm value=\"$pst_date_cm\" size=10>");
    print("</td></tr></tbody></table><br>");
    print("<table style=\"text-align: left; width: 1005px; height: 577px;\" border=\"1\" cellpadding=\"2\" cellspacing=\"2\">");
    print("<tbody><tr><td style=\"background-color: rgb(153, 255, 255);\">");
    print("Nom de l'&eacute;poux &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <input name=epx_nom value=\"$pst_epx_nom\" size=25><BR>");
    print("Pr&eacute;nom de l'&eacute;poux &nbsp;&nbsp;&nbsp;&nbsp;<input name=epx_prenom value=\"$pst_epx_prenom\" size=25><BR>");
    print("Profession de l'&eacute;poux <input name=epx_prof value=\"$pst_epx_prof\" size=25><BR><BR>");
    print("Nom du p&egrave;re &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input name=epx_nom_pere value=\"$pst_epx_nom_pere\" size=25><BR>");
    print("Pr&eacute;nom du p&egrave;re &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input name=epx_prenom_pere value=\"$pst_epx_prenom_pere\" size=25><BR>");
    print("Profession du p&egrave;re &nbsp;&nbsp;&nbsp; <input name=epx_prof_pere value=\"$pst_epx_prof_pere\" size=25><BR><BR>");
    print("Nom de la m&egrave;re &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input name=epx_nom_mere value=\"$pst_epx_nom_mere\" size=25><BR>");
    print("Pr&eacute;nom de la m&egrave;re &nbsp;&nbsp;&nbsp;&nbsp;<input name=epx_prenom_mere value=\"$pst_epx_prenom_mere\" size=25><BR><BR>");
    print("Village &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <input name=epx_village value=\"$pst_epx_village\" size=25><BR>");
    print("Paroisse &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <input name=epx_paroisse value=\"$pst_epx_paroisse\" size=25><BR><BR>");
    print("T&eacute;moins <br> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <textarea name=epx_temoins cols=40 rows=12>" . $pst_epx_temoins . "</textarea>");
    print("</td><td style=\"background-color: rgb(255, 204, 255);\">");
    print("Nom de l'&eacute;pouse &nbsp;&nbsp;&nbsp; <input name=epe_nom value=\"$pst_epe_nom\" size=25><BR>");
    print("Pr&eacute;nom de l'&eacute;pouse <input name=epe_prenom value=\"$pst_epe_prenom\" size=25><BR><BR>");
    print("Nom du p&egrave;re &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input name=epe_nom_pere value=\"$pst_epe_nom_pere\" size=25><BR>");
    print("Pr&eacute;nom du p&egrave;re &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input name=epe_prenom_pere value=\"$pst_epe_prenom_pere\" size=25><BR>");
    print("Profession du p&egrave;re &nbsp;&nbsp;<input name=epe_prof_pere value=\"$pst_epe_prof_pere\" size=25><BR><BR>");
    print("Nom de la m&egrave;re &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input name=epe_nom_mere value=\"$pst_epe_nom_mere\" size=25><BR>");
    print("Pr&eacute;nom de la m&egrave;re &nbsp;&nbsp;<input name=epe_prenom_mere value=\"$pst_epe_prenom_mere\" size=25><BR><BR>");
    print("Village &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <input name=epe_village value=\"$pst_epe_village\" size=25><BR>");
    print("Paroisse &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <input name=epe_paroisse value=\"$pst_epe_paroisse\" size=25><BR><BR>");
    print("T&eacute;moins <br> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <textarea name=epe_temoins  cols=40 rows=12>" . $pst_epe_temoins . "</textarea>");
    print("</td></tr></tbody></table><br>");
}

/** Affiche le menu de modification des contrats
 * @param object $rconnexionBD Identifiant de la connexion de base
 * @param integer $pi_idf_contrats Identifiant du contrat
 */
function menu_modifier($rconnexionBD, $pi_idf_contrats)
{
    $st_requete = "select notaire,paroisse,cote,ref_perso,date_cm,epx_nom,epx_prenom,epx_prof,epx_nom_pere,epx_prenom_pere,epx_prof_pere,epx_nom_mere,	epx_prenom_mere,	epx_village,epx_paroisse, epx_temoins,epe_nom,epe_prenom,epe_nom_pere,epe_prenom_pere,epe_prof_pere,epe_nom_mere,	epe_prenom_mere,	epe_village,epe_paroisse, epe_temoins from `cm_notaires` where idf=$pi_idf_contrats";
    list(
        $st_notaire, $st_paroisse, $st_cote, $st_ref_perso, $st_date_cm, $st_epx_nom, $st_epx_prenom, $st_epx_prof, $st_epx_nom_pere, $st_epx_prenom_pere, $st_epx_prof_pere, $st_epx_nom_mere,
        $st_epx_prenom_mere, $st_epx_village, $st_epx_paroisse, $st_epx_temoins, $st_epe_nom, $st_epe_prenom, $st_epe_nom_pere, $st_epe_prenom_pere, $st_epe_prof_pere, $st_epe_nom_mere,
        $st_epe_prenom_mere, $st_epe_village, $st_epe_paroisse, $st_epe_temoins
    ) = $rconnexionBD->sql_select_liste($st_requete);
    print("<form   method=\"post\" onSubmit=\"return VerifieChamps(0)\">");
    print("<input type=hidden name=mode value=MODIFIER>");
    print("<input type=hidden name=idf_contrats value=$pi_idf_contrats>");
    print("<div align=center>");
    menu_edition(
        $st_notaire,
        $st_paroisse,
        $st_cote,
        $st_ref_perso,
        $st_date_cm,
        $st_epx_nom,
        $st_epx_prenom,
        $st_epx_prof,
        $st_epx_nom_pere,
        $st_epx_prenom_pere,
        $st_epx_prof_pere,
        $st_epx_nom_mere,
        $st_epx_prenom_mere,
        $st_epx_village,
        $st_epx_paroisse,
        $st_epx_temoins,
        $st_epe_nom,
        $st_epe_prenom,
        $st_epe_nom_pere,
        $st_epe_prenom_pere,
        $st_epe_prof_pere,
        $st_epe_nom_mere,
        $st_epe_prenom_mere,
        $st_epe_village,
        $st_epe_paroisse,
        $st_epe_temoins
    );
    print("</div><br>");
    print("<div align=center><input type=button value=\"Modifier\" ONCLICK=VerifieChamps(0)></div>");
    print('</form>');
    print("<form   method=\"post\">");
    print("<input type=hidden name=mode value=LISTE>");
    print("<div align=center>");
    print("<div align=center><input type=submit value=\"Annuler\")></div>");
    print('</form>');
}

/** Affiche le menu d'ajout d'un chantier
 * @param array $pa_documents Liste des documents
 * @param array $pa_adherents Liste des adh�rents (releveur)
 */
function menu_ajouter()
{
    print("<form   method=\"post\" onSubmit=\"return VerifieChamps(0)\">");
    print("<input type=hidden name=mode value=AJOUTER>");
    print("<div align=center>");
    menu_edition('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '');
    print("</div><br>");
    print("<div align=center><input type=button value=\"Ajouter\" ONCLICK=VerifieChamps(0)></div>");
    print('</form>');
    print("<form   method=\"post\">");
    print("<input type=hidden name=mode value=LISTE>");
    print("<div align=center>");
    print("<div align=center><input type=submit value=\"Annuler\")></div>");
    print('</form>');
}

/*---------------------------------------------------------------------------
  D�marrage du programme
  ---------------------------------------------------------------------------*/

$connexionBD = ConnexionBD::singleton($gst_serveur_bd, $gst_utilisateur_bd, $gst_mdp_utilisateur_bd, $gst_nom_bd);

switch ($gst_mode) {
    case 'LISTE':
        menu_liste($connexionBD);
        break;
    case 'MENU_MODIFIER':
        menu_modifier($connexionBD, $gi_idf_contrats);
        break;

    case 'MODIFIER':
        $st_notaire = strtoupper(trim($_POST['notaire']));
        $st_paroisse = Maj_et_Min(trim($_POST['paroisse']));
        $st_cote = trim($_POST['cote']);
        $st_ref_perso = trim($_POST['ref_perso']);
        list($i_jour, $i_mois, $i_annee) = explode('/', $_POST['date_cm'], 3);
        $c_date_cm = join('-', array($i_annee, $i_mois, $i_jour));

        $st_epx_nom = strtoupper(trim($_POST['epx_nom']));
        $st_epx_prenom = Maj_et_Min(trim($_POST['epx_prenom']));
        $st_epx_prof = Maj_et_Min(trim($_POST['epx_prof']));
        $st_epx_nom_pere = strtoupper(trim($_POST['epx_nom_pere']));
        $st_epx_prenom_pere = Maj_et_Min(trim($_POST['epx_prenom_pere']));
        $st_epx_prof_pere = Maj_et_Min(trim($_POST['epx_prof_pere']));
        $st_epx_nom_mere  = strtoupper(trim($_POST['epx_nom_mere']));
        $st_epx_prenom_mere = Maj_et_Min(trim($_POST['epx_prenom_mere']));
        $st_epx_village  = trim($_POST['epx_village']);
        $st_epx_paroisse  = Maj_et_Min(trim($_POST['epx_paroisse']));
        $st_epx_temoins  = trim($_POST['epx_temoins']);

        $st_epe_nom = strtoupper(trim($_POST['epe_nom']));
        $st_epe_prenom  = Maj_et_Min(trim($_POST['epe_prenom']));
        $st_epe_nom_pere  = strtoupper(trim($_POST['epe_nom_pere']));
        $st_epe_prenom_pere  = Maj_et_Min(trim($_POST['epe_prenom_pere']));
        $st_epe_prof_pere = Maj_et_Min(trim($_POST['epe_prof_pere']));
        $st_epe_nom_mere  = strtoupper(trim($_POST['epe_nom_mere']));
        $st_epe_prenom_mere  = Maj_et_Min(trim($_POST['epe_prenom_mere']));
        $st_epe_village  = trim($_POST['epe_village']);
        $st_epe_paroisse  = Maj_et_Min(trim($_POST['epe_paroisse']));
        $st_epe_temoins = trim($_POST['epe_temoins']);
        $st_requete = "update `cm_notaires` set notaire='$st_notaire', paroisse='$st_paroisse', cote='$st_cote', ref_perso='$st_ref_perso', date_cm='$c_date_cm', 
	 epx_nom='$st_epx_nom', epx_prenom='$st_epx_prenom', epx_prof='$st_epx_prof', epx_nom_pere='$st_epx_nom_pere', epx_prenom_pere='$st_epx_prenom_pere',
	 epx_prof_pere='$st_epx_prof_pere', epx_nom_mere='$st_epx_nom_mere', epx_prenom_mere='$st_epx_prenom_mere', epx_village='$st_epx_village', epx_paroisse='$st_epx_paroisse',
	 epx_temoins='$st_epx_temoins', epe_nom='$st_epe_nom', epe_prenom='$st_epe_prenom', epe_nom_pere='$st_epe_nom_pere', epe_prenom_pere='$st_epe_prenom_pere', epe_prof_pere='$st_epe_prof_pere',
	 epe_nom_mere='$st_epe_nom_mere', epe_prenom_mere='$st_epe_prenom_mere', epe_village='$st_epe_village', epe_paroisse='$st_epe_paroisse', epe_temoins='$st_epe_temoins' where idf=$gi_idf_contrats";
        $connexionBD->execute_requete($st_requete);

        menu_liste($connexionBD);
        break;
    case 'MENU_AJOUTER':
        menu_ajouter();
        break;
    case 'AJOUTER':
        echo 'ajouter';
        $st_notaire = strtoupper(trim($_POST['notaire']));
        $st_paroisse = Maj_et_Min(trim($_POST['paroisse']));
        $st_cote = trim($_POST['cote']);
        $st_ref_perso = trim($_POST['ref_perso']);
        list($i_jour, $i_mois, $i_annee) = explode('/', $_POST['date_cm'], 3);
        $c_date_cm = join('-', array($i_annee, $i_mois, $i_jour));

        $st_epx_nom = strtoupper(trim($_POST['epx_nom']));
        $st_epx_prenom = Maj_et_Min(trim($_POST['epx_prenom']));
        $st_epx_prof = Maj_et_Min(trim($_POST['epx_prof']));
        $st_epx_nom_pere = strtoupper(trim($_POST['epx_nom_pere']));
        $st_epx_prenom_pere = Maj_et_Min(trim($_POST['epx_prenom_pere']));
        $st_epx_prof_pere = Maj_et_Min(trim($_POST['epx_prof_pere']));
        $st_epx_nom_mere  = strtoupper(trim($_POST['epx_nom_mere']));
        $st_epx_prenom_mere = Maj_et_Min(trim($_POST['epx_prenom_mere']));
        $st_epx_village  = trim($_POST['epx_village']);
        $st_epx_paroisse  = Maj_et_Min(trim($_POST['epx_paroisse']));
        $st_epx_temoins  = trim($_POST['epx_temoins']);

        $st_epe_nom = strtoupper(trim($_POST['epe_nom']));
        $st_epe_prenom  = Maj_et_Min(trim($_POST['epe_prenom']));
        $st_epe_nom_pere  = strtoupper(trim($_POST['epe_nom_pere']));
        $st_epe_prenom_pere  = Maj_et_Min(trim($_POST['epe_prenom_pere']));
        $st_epe_prof_pere = Maj_et_Min(trim($_POST['epe_prof_pere']));
        $st_epe_nom_mere  = strtoupper(trim($_POST['epe_nom_mere']));
        $st_epe_prenom_mere  = Maj_et_Min(trim($_POST['epe_prenom_mere']));
        $st_epe_village  = trim($_POST['epe_village']);
        $st_epe_paroisse  = Maj_et_Min(trim($_POST['epe_paroisse']));
        $st_epe_temoins = trim($_POST['epe_temoins']);

        $connexionBD->execute_requete("insert into cm_notaires (notaire, paroisse, cote, ref_perso, date_cm, epx_nom, epx_prenom, epx_prof, epx_nom_pere, epx_prenom_pere, epx_prof_pere, epx_nom_mere, epx_prenom_mere, epx_village, epx_paroisse, epx_temoins, epe_nom, epe_prenom, epe_nom_pere, epe_prenom_pere, epe_prof_pere, epe_nom_mere, epe_prenom_mere, epe_village, epe_paroisse, epe_temoins ) values ('$st_notaire', '$st_paroisse', '$st_cote', '$st_ref_perso', '$c_date_cm', '$st_epx_nom', '$st_epx_prenom', '$st_epx_prof', '$st_epx_nom_pere', '$st_epx_prenom_pere', '$st_epx_prof_pere', '$st_epx_nom_mere', '$st_epx_prenom_mere', '$st_epx_village', '$st_epx_paroisse', '$st_epx_temoins', '$st_epe_nom', '$st_epe_prenom', '$st_epe_nom_pere', '$st_epe_prenom_pere', '$st_epe_prof_pere', '$st_epe_nom_mere', '$st_epe_prenom_mere', '$st_epe_village', '$st_epe_paroisse','$st_epe_temoins')");
        menu_liste($connexionBD);
        break;
}
print('</body>');
