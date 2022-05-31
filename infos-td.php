<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------
require_once __DIR__ . '/app/bootstrap.php';
require_once __DIR__ . '/Origin/Acte.php';
require_once __DIR__ . '/Origin/CompteurActe.php';
require_once __DIR__ . '/Origin/Personne.php';
require_once __DIR__ . '/Origin/Prenom.php';
require_once __DIR__ . '/Origin/CompteurPersonne.php';
require_once __DIR__ . '/Origin/TypeActe.php';
require_once __DIR__ . '/Origin/CommunePersonne.php';
require_once __DIR__ . '/Origin/Profession.php';

// ============= Request
$gi_idf_acte = $_GET['idf_acte'] ?? null;

if (!$gi_idf_acte) {
    die("Erreur: L'identifiant de l'acte est manquant");
}

$i_idf_commune = $connexionBD->sql_select1("SELECT idf_commune FROM acte WHERE idf=$gi_idf_acte");
$a_profession = $connexionBD->liste_valeur_par_clef("SELECT idf, nom FROM profession");
$sql1 = "SELECT idf_type_acte, idf_commune, idf_source, year(now())-annee FROM acte WHERE idf=$gi_idf_acte";
list($i_idf_type_acte, $i_idf_commune, $i_idf_source, $i_age_acte) = $connexionBD->sql_select_liste($sql1);
$gst_adresse_ip = $_SERVER['REMOTE_ADDR'];


/*
* Enregistre des informations de statistiques naissances et décès dans un fichier journal
* @param string $pst_nom_fichier nom du fichier journal
* @param string $pst_ident identifiant de l'adhérent
* @param string $pst_adresse_ip adresse ip de l'adhérent
* @param string $pi_idf_commune identifiant de la commune demandée
*/
function enregistre_journal($pst_nom_fichier, $pst_ident, $pst_adresse_ip, $pi_idf_commune)
{
    global $gst_time_zone;
    $pf = @fopen($pst_nom_fichier, 'a');
    date_default_timezone_set('Europe/Paris');
    list($i_sec, $i_min, $i_heure, $i_jmois, $i_mois, $i_annee, $i_j_sem, $i_j_an, $b_hiver) = localtime();
    $i_mois++;
    $i_annee += 1900;
    $st_date_log = sprintf("%02d/%02d/%04d %02d:%02d:%02d", $i_jmois, $i_mois, $i_annee, $i_heure, $i_min, $i_sec);
    $st_chaine_log = join(';', array($st_date_log, trim($pst_ident), $pst_adresse_ip, $pi_idf_commune));
    @fwrite($pf, "$st_chaine_log\n");
    @fclose($pf);
}

?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta http-equiv="content-language" content="fr" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="assets/css/styles.css" type="text/css" rel="stylesheet">
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <script src="assets/js/jquery-min.js" type="text/javascript"></script>
    <script src="assets/js/bootstrap.min.js" type="text/javascript"></script>
    <title>Infos TD</title>
    <script type='text/javascript'>
        $(document).ready(function() {

            $("#ferme").click(function() {
                window.close();
            });

        });
    </script>

</head>

<body>
    <div class="container">
        <div class="text-center">
            <a href="<?= $gst_url_sortie; ?>" target="_blank">
                <img src="<?= $gst_logo_association; ?>" style="border: 0;" alt="Logo <?= SIGLE_ASSO; ?>">
            </a>
        </div>
        <div class="panel panel-primary">
            <div class="panel-heading">Ce relevé est issu d'une table décennale</div>
            <div class="panel-body">
                <?php
                if ($i_idf_source == IDF_SOURCE_TD) {
                    if ($i_age_acte > 100) {
                        $st_ident = $session->getAttribute('ident');
                        switch ($i_idf_type_acte) {
                            case IDF_NAISSANCE:
                                enregistre_journal("logs/requetes_td_naissances.log", $st_ident, $gst_adresse_ip, $i_idf_commune);
                                break;
                            case IDF_DECES:
                                enregistre_journal("logs/requetes_td_deces.log", $st_ident, $gst_adresse_ip, $i_idf_commune);
                                break;
                            default:
                                enregistre_journal("logs/requetes_td_mariages.log", $st_ident, $gst_adresse_ip, $i_idf_commune);
                        }
                        $o_acte = new Acte($connexionBD, null, null, null, null, null, null);
                        $o_acte->charge($gi_idf_acte);
                        $i_details_supp = $o_acte->getDetailsSupplementaires();
                        $st_description_acte = $o_acte->versChaine();
                        $i_nb_lignes = $o_acte->getNbLignes();
                        $st_permalien =  $o_acte->getUrl();
                        print("<textarea rows=$i_nb_lignes cols=80 class=\"form-control\">");
                        print($st_description_acte);
                        print("</textarea>");
                        if (!empty($st_permalien))
                            print("<a href=\"$st_permalien\" target=\"_blank\" class=\"btn btn-primary col-xs-4 col-xs-offset-4\"><span class=\"glyphicon glyphicon-picture\"></span> Lien vers les AD</a></div>");
                        print("<form  action=\"PropositionModification.php\" id=\"PropositionModification\" method=\"post\" target=\"_blank\">\n");
                        print("<input type=\"hidden\" name=\"idf_acte\" value=\"$gi_idf_acte\">");
                        print('<button type="submit" class="btn btn-primary col-xs-8 col-xs-offset-2"><span class="glyphicon glyphicon-edit"></span> Compléter en détail le relevé</button>');
                        print("</form>");
                    } else {
                        print('<div class="alert alert-danger">Cet acte de moins de 100 ans ne peut être consulté</div>');
                    }
                } else {
                    print('<div class="alert alert-danger">Cet acte ne provient pas d\'une table décennale</div>');
                } ?>
                <button type="button" id="ferme" class="btn btn-warning col-xs-4 col-xs-offset-4">
                    <span class="glyphicon glyphicon-remove-sign"></span>
                    Fermer la fenêtre
                </button>
            </div>
        </div>
    </div>
</body>

</html>