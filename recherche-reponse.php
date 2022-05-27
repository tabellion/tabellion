<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------
require_once __DIR__ . '/app/bootstrap.php';
require_once __DIR__ . '/Commun/commun.php';
require_once __DIR__ . '/RequeteRecherche.php';
require_once __DIR__ . '/Commun/PaginationTableau.php';
require_once __DIR__ . '/Commun/Benchmark.php';
require_once __DIR__ . '/Commun/VerificationDroits.php';

// ======== Default
$per_page_options = array(NB_LIGNES_PAR_PAGE => NB_LIGNES_PAR_PAGE, 50 => 50, 100 => 100);
$per_page = 25;

/* if (isset($_POST)) {
    echo '<pre>';
    print_r($_POST);
    echo '</pre>';
}
exit; */
// ================

$current_page = $_GET['page'] ?? 1;
$gi_num_page = empty($_REQUEST['num_page']) ? 1 : (int) $_REQUEST['num_page'];
if (isset($_GET['per_page']) && in_array($_GET['per_page'], array_keys($per_page_options))) {
    $per_page = $_GET['per_page'];
}

$a_requetes           = array();
$a_clauses            = array();

$gst_type_recherche     = $_POST['type_recherche'] ?? null;
$gi_idf_source          = $_POST['idf_source_recherche'] ?? null;
$gi_idf_commune         = $_POST['idf_commune_recherche'] ?? null;
$gst_paroisses_rattachees = $_POST['paroisses_rattachees'] ?? null;
$gi_rayon               = $_POST['rayon'] ?? null;
$gi_idf_type_acte       = $_POST['idf_type_acte_recherche'] ?? null;
$gi_annee_min           = $_POST['annee_min'] ?? null;
$gi_annee_max           = $_POST['annee_max'] ?? null;

$gi_releve_mois_min     = $_POST['releve_mois_min'] ?? null;
$gi_releve_mois_max     = $_POST['releve_mois_max'] ?? null;
$gi_releve_annee_min    = $_POST['releve_annee_min'] ?? null;
$gi_releve_annee_max    = $_POST['releve_annee_max'] ?? null;
$releve_type            = $_POST['releve_type'] ?? null;

$gst_adresse_ip         = $_SERVER['REMOTE_ADDR'];
$st_criteres            = '';

$requeteRecherche = new RequeteRecherche($connexionBD);


if (empty($gst_logo_association))
    $gi_largeur_page = 600;
else {
    $headers = @get_headers($gst_logo_association);
    if (strpos($headers[0], '404') === false) {
        list($i_largeur_logo, $i_hauteur_logo, $st_type_logo, $st_attributs_logo) = getimagesize($gst_logo_association);
        if ($i_largeur_logo <= 400)
            $gi_largeur_page = (int) round($i_largeur_logo / 100) * 200;
        else
            $gi_largeur_page = (int) round($i_largeur_logo / 100) * 120;
    } else {
        print("<div class='alert alert-warning'>Impossible de charger $gst_logo_association</div>\n");
        $gi_largeur_page = 600;
    }
}




/*
* Renvoie la chaine représentant la partie "recherches communes"  de la recherche
* @param object $pconnexionBD Connexion à la BD
* @param string $pst_titre titre de la recherche
* @param integer $pi_idf_type_acte identifiant du type d'acte recherché
* @param integer $pi_annee_min année minimale de la recherche
* @param integer $pi_annee_max année maximale de la recherche
* @param integer $pi_idf_source source utilisée
* @param integer $pi_idf_commune identifiant de la commune recherchée
* @param integer $pi_rayon rayon de recherche
* @global object $requeteRecherche objet requête recherche
* @global string $st_criteres critères utilisés pour la recherche
*/
function rappel_recherches_communes($pst_titre, $pi_idf_type_acte, $pi_annee_min, $pi_annee_max, $pi_idf_source, $pi_idf_commune, $pi_rayon)
{
    global $connexionBD, $requeteRecherche;
    global $st_criteres;
    $a_params_precedents = $connexionBD->params();
    print("<div class=\"panel panel-primary col-md-4\">");
    print("<div class=\"panel-heading\">Vos critères de recherche</div>");
    print("<div class=\"panel-body\">");
    $st_criteres = "$pst_titre\n";
    if (!empty($pi_idf_type_acte)) {
        $st_type_acte = $connexionBD->sql_select1("select nom from type_acte where idf=$pi_idf_type_acte");
        $st_criteres .= "Type d'acte: " . cp1252_vers_utf8($st_type_acte) . "\n";
    }
    if ($pi_annee_min != '' && $pi_annee_max != '')
        $st_criteres .= " de $pi_annee_min à $pi_annee_max";
    else if ($pi_annee_min != '')
        $st_criteres .= " à partir de $pi_annee_min";
    else if ($pi_annee_max != '')
        $st_criteres .= " jusqu'en $pi_annee_max";
    $st_criteres .= "\n";
    $st_nom_commune =  $pi_idf_commune != 0 ? $connexionBD->sql_select1("SELECT nom FROM commune_acte WHERE idf=$pi_idf_commune") : 'Pas de commune selectionnée';

    if (!empty($pi_idf_source)) {
        $st_type_acte = $connexionBD->sql_select1("select nom from source where idf=$pi_idf_source");
        $st_criteres .= "Source sélectionnée: " . cp1252_vers_utf8($st_type_acte) . "\n";
    }
    $st_criteres .= "Commune sélectionnée: " . cp1252_vers_utf8($st_nom_commune) . "\n";
    $st_bloc_rappel = nl2br($st_criteres);
    $st_communes_voisines = join("\n", array_values($requeteRecherche->communes_voisines()));
    if (count(array_values($requeteRecherche->communes_voisines())) > 1) {
        $st_bloc_rappel .= "<div class=\"form-group\"><label for=\"communes_voisines\">Paroisses voisines ou rattachées";
        if (!empty($pi_rayon)) {
            $st_bloc_rappel .= "(avec recherches dans un rayon de $pi_rayon km)\n";
        }
        $st_bloc_rappel .= "</label>";
        $st_bloc_rappel .= "<textarea rows=6 cols=40 id=\"communes_voisines\" class=\"form-control\">" . cp1252_vers_utf8($st_communes_voisines) . "</textarea></div>";
    }
    $st_bloc_rappel .= "</div>";
    $st_bloc_rappel .= "</div>";
    $connexionBD->initialise_params($a_params_precedents);
    return $st_bloc_rappel;
}

function getDebutDateReleve($mois, $annee)
{
    if ($mois > 0 && $mois <= 12 && $annee <= date('Y')) {
        return strtotime($annee . '-' . str_pad($mois, 2, '0', STR_PAD_LEFT) . '-01');
    }

    return 0;
}

function getFinDateReleve($mois, $annee)
{
    if ($mois > 0 && $mois <= 12 && $annee < date('Y')) {
        return strtotime($annee . '-' . str_pad($mois, 2, '0', STR_PAD_LEFT) . '-31');
    }

    return time();
}
?>
<!DOCTYPE html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="content-language" content="fr">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="shortcut icon" href="assets/img/favicon.ico">
<link href="assets/css/styles.css" type="text/css" rel="stylesheet">
<link href="assets/css/styles.css" type="text/css" rel="stylesheet">
<link href="assets/css/bootstrap.min.css" rel="stylesheet">
<script src="assets/js/jquery-min.js" type="text/javascript"></script>
<script src="assets/js/bootstrap.min.js" type="text/javascript"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $('a.popup').click(function() {
            var url = $(this).attr("href");
            var windowName = "InfosActe";
            var windowSize = 'width=<?php print($gi_largeur_page); ?>,height=600,resizable=yes,scrollbars=yes';
            window.open(url, windowName, windowSize);
            return false;
        });
        $('#per-page').on('change', function() {
            window.location.href = "<?php print basename(__FILE__); ?>?per_page=" + $(this).val();
        });

        $(function() {
            $('[data-toggle="tooltip"]').tooltip()
        })
    });
</script>
<title>Base <?= SIGLE_ASSO; ?> : Reponses a une recherche</title>
</head>
<body>
<div class="container">

<?php require_once __DIR__ . '/Commun/menu.php';

switch ($gst_type_recherche) {
    case 'couple':
        $st_libelle_commentaire     = 'Couple recherché';
        $gst_nom_epx                = $_POST['nom_epx'] ?? null;
        $gst_prenom_epx             = $_POST['prenom_epx'] ?? null;
        $gst_variantes_epx          = $_POST['variantes_epx'] ?? null;
        $gst_nom_epse               = $_POST['nom_epse'] ?? null;
        $gst_prenom_epse            = $_POST['prenom_epse'] ?? null;
        $gst_variantes_epse         = $_POST['variantes_epse'] ?? null;
        $st_variantes_epx_trouvees  = '';
        $st_variantes_epse_trouvees = '';
        $st_communes_voisines       = '';
        $gst_nom_epx  = preg_replace('/\*+/', '*', $gst_nom_epx);
        $gst_nom_epse  = preg_replace('/\*+/', '*', $gst_nom_epse);
        if ((($gst_nom_epx == '*' && empty($gst_prenom_epx))  || ($gst_nom_epse == '*' && empty($gst_prenom_epse))) && empty($gi_idf_commune)) {
            print(nl2br("La recherche par joker * seul n'est autorisée que si une paroisse est choisie<br>"));
            print("<a href=" . PAGE_RECHERCHE . " class=\"RetourReponses\">Nouvelle Recherche</a><br>");
            exit();
        }
        $st_erreur_nom = '';
        if (($gst_nom_epx != '*') && ($gst_nom_epx != '!') && strlen(str_replace('*', '', $gst_nom_epx)) < 2)
            $st_erreur_nom = "<div class='alert alert-danger'>Le nom de l'époux doit comporter au moins trois caractères</div>\n";
        if (($gst_nom_epse != '*' && $gst_nom_epse != '!') && strlen(str_replace('*', '', $gst_nom_epse)) < 2)
            $st_erreur_nom .= "<div class='alert alert-danger'>Le nom de l'épouse doit comporter au moins trois caractères</div>\n";
        if (($gst_nom_epx == '*') && ($gst_nom_epse == '*'))
            $st_erreur_nom .= "<div class='alert alert-danger'>Au moins un des noms ne doit pas correspondre au caractère joker \"*\"</div>\n";
        if ($st_erreur_nom != '') {
            print(nl2br($st_erreur_nom));
            print("<a href=" . PAGE_RECHERCHE . " class=\"btn btn-primary col-md-4 col-md-offset-4\">Nouvelle Recherche</a><br>");
            exit();
        }

        

        //===================== Log
        $pf = @fopen("logs/requetes_couple.log", 'a');
        date_default_timezone_set($gst_time_zone);
        list($i_sec, $i_min, $i_heure, $i_jmois, $i_mois, $i_annee, $i_j_sem, $i_j_an, $b_hiver) = localtime();
        $i_mois++;
        $i_annee += 1900;
        $st_date_log = sprintf("%02d/%02d/%04d %02d:%02d:%02d", $i_jmois, $i_mois, $i_annee, $i_heure, $i_min, $i_sec);
        $st_chaine_log = join(';', array($st_date_log, $session->getAttribute('ident'), $gst_adresse_ip, $gst_nom_epx, $gst_prenom_epx, $gst_nom_epse, $gst_prenom_epse, $gi_idf_commune, $gi_rayon, $gi_annee_min, $gi_annee_max));
        @fwrite($pf, "$st_chaine_log\n");
        @fclose($pf);
        $st_tables_prenom_epx = '';
        $i_nb_prenoms_epx = 1;
        $st_variantes_prenoms_epx = '';
        $a_clauses_recherche = array();
        $gst_nom_epx  = str_replace('*', '%', $gst_nom_epx);
        $a_clauses_recherche[] = "u.patronyme_epoux " . $requeteRecherche->clause_droite_patronyme($gst_nom_epx, $gst_variantes_epx, 1);
        $st_variantes_epx_trouvees = join("\n", $requeteRecherche->variantes_trouvees());
        $gst_nom_epse  = str_replace('*', '%', $gst_nom_epse);
        $a_clauses_recherche[] = "u.patronyme_epouse " . $requeteRecherche->clause_droite_patronyme($gst_nom_epse, $gst_variantes_epse, 2);
        $st_variantes_epse_trouvees = join("\n", $requeteRecherche->variantes_trouvees());
        if (!empty($gst_prenom_epx)) {
            $gst_prenom_epx  = str_replace('*', '%', $gst_prenom_epx);
            $a_prenoms_simples_epx = preg_split('/[,\s\/\=\&\-]+/', $gst_prenom_epx);
            foreach ($a_prenoms_simples_epx as $st_prenom) {
                $a_clauses_recherche[] = "prn_simple_epx$i_nb_prenoms_epx.libelle " . $requeteRecherche->clause_droite_prenom($st_prenom, $gst_variantes_epx, $i_nb_prenoms_epx);
                $st_variantes_prenoms_epx .= join("\n", $requeteRecherche->variantes_prenoms());
                $st_tables_prenom_epx .= " join `groupe_prenoms` gp$i_nb_prenoms_epx on (prn_p1.idf=gp$i_nb_prenoms_epx.idf_prenom)  join `prenom_simple` prn_simple_epx$i_nb_prenoms_epx on (gp$i_nb_prenoms_epx.idf_prenom_simple =prn_simple_epx$i_nb_prenoms_epx.idf) ";
                $i_nb_prenoms_epx++;
            }
        }
        $st_tables_prenom_epse = '';
        $st_variantes_prenoms_epse = '';
        if (!empty($gst_prenom_epse)) {
            $gst_prenom_epse  = str_replace('*', '%', $gst_prenom_epse);
            $a_prenoms_simples_epse = preg_split('/[,\s\/\=\&\-]+/', $gst_prenom_epse);
            $i_nb_prenoms_epse = $i_nb_prenoms_epx;
            foreach ($a_prenoms_simples_epse as $st_prenom) {
                $a_clauses_recherche[] = "prn_simple_epse$i_nb_prenoms_epse.libelle " . $requeteRecherche->clause_droite_prenom($st_prenom, $gst_variantes_epse, $i_nb_prenoms_epse);
                $st_variantes_prenoms_epse .= join("\n", $requeteRecherche->variantes_prenoms());
                $st_tables_prenom_epse .= " join `groupe_prenoms` gp$i_nb_prenoms_epse on (prn_p2.idf=gp$i_nb_prenoms_epse.idf_prenom) join `prenom_simple` prn_simple_epse$i_nb_prenoms_epse on (gp$i_nb_prenoms_epse.idf_prenom_simple=prn_simple_epse$i_nb_prenoms_epse.idf) ";
                $i_nb_prenoms_epse++;
            }
        }
        if (!empty($gi_idf_source)) $a_clauses_recherche[] = "a.idf_source=$gi_idf_source";
        if (!empty($gi_idf_type_acte)) $a_clauses_recherche[] = "a.idf_type_acte=$gi_idf_type_acte";
        if (!empty($gi_annee_min)) $a_clauses_recherche[] = "a.annee>=$gi_annee_min";
        if (!empty($gi_annee_max)) $a_clauses_recherche[] = "a.annee<=$gi_annee_max";
        if (!empty($gi_idf_commune)) $a_clauses_recherche[] = "u.idf_commune " . $requeteRecherche->clause_droite_commune($gi_idf_commune, $gi_rayon, $gst_paroisses_rattachees);

        // Dates de relève
        $releve_col = ($releve_type == 0) ? 'created' : 'changed';
        if (!empty($gi_releve_mois_min) && !empty($gi_releve_annee_min)) $a_clauses_recherche[] = " a." . $releve_col . ">=" . getDebutDateReleve($gi_releve_mois_min, $gi_releve_annee_min);
        if (!empty($gi_releve_mois_max) && !empty($gi_releve_annee_max)) $a_clauses_recherche[] = " a." . $releve_col . "<=" . getFinDateReleve($gi_releve_mois_max, $gi_releve_annee_max);

        $gst_requete_actes = "select distinct u.idf_acte,concat(IFNULL(prn_p1.libelle,''),' ',u.patronyme_epoux,' (',tp1.nom,') x ',IFNULL(prn_p2.libelle,''),' ',u.patronyme_epouse,' (',tp2.nom,')'),a.annee,a.mois,a.jour from `union` u join `acte` a on (u.idf_acte=a.idf) join `personne` p1 on (u.idf_epoux=p1.idf) left join `prenom` prn_p1 on (p1.idf_prenom=prn_p1.idf)  $st_tables_prenom_epx join `personne` p2 on (u.idf_epouse=p2.idf) left join `prenom` prn_p2 on (p2.idf_prenom=prn_p2.idf) $st_tables_prenom_epse join type_acte ta on (a.idf_type_acte=ta.idf) join commune_acte ca on (u.idf_commune=ca.idf) join `type_presence` tp1 on (p1.idf_type_presence=tp1.idf) join `type_presence` tp2 on (p2.idf_type_presence=tp2.idf) where ";

        $gst_requete_parties = "select distinct u.idf_acte,min(ta.nom),min(ca.nom),if (a.idf_type_acte=" . IDF_RECENS . ",GROUP_CONCAT(distinct concat(IFNULL(prn_parties.libelle,''),' ',parties.patronyme) order by parties.idf separator '<br>'),GROUP_CONCAT(distinct concat(IFNULL(prn_parties.libelle,''),' ',parties.patronyme) order by parties.idf separator ' X ')) as parties,a.date,a.idf_type_acte,a.cote,min(u.idf_source),a.details_supplementaires,m_a.statut,a.annee,a.mois,a.jour,a.created,a.changed from `union` u join `acte` a on (u.idf_acte=a.idf) join `personne` parties on (a.idf=parties.idf_acte and parties.idf_type_presence=" . IDF_PRESENCE_INTV . ") left join prenom prn_parties on (parties.idf_prenom=prn_parties.idf) join type_acte ta on (a.idf_type_acte=ta.idf) join commune_acte ca on (u.idf_commune=ca.idf)  left join modification_acte m_a on (a.idf=m_a.idf_acte and m_a.statut='A') where ";
        $st_clauses_actes = implode(" and ", $a_clauses_recherche);
        $gst_requete_actes = "$gst_requete_actes $st_clauses_actes order by a.annee,a.mois,a.jour";

        //FBOprint("Req=$gst_requete_actes<br>");

        if (!empty($gst_variantes_epx) || !empty($st_variantes_prenoms_epx)) {
            print("<div class=\"panel panel-primary col-md-4\">");
            print("<div class=\"panel-heading\">Variantes connues pour l'époux</div>");
            print("<div class=\"panel-body\">");
            print('<form>');
            print('<div class="form-row">');
            if ($st_variantes_epx_trouvees != "")
                print("<div class=\"form-group col-md-6\"><label for=\"variantes_patros_epx\">Patronyme:</label><textarea class=\"form-control\" rows=8 cols=20 id=\"variantes_patros_epx\">" . cp1252_vers_utf8($st_variantes_epx_trouvees) . "</textarea></div>");
            else
                print("<div class=\"col-md-4\">Pas de variantes patronymiques connues</div>");
            if ($st_variantes_prenoms_epx != "")
                print("<div class=\"form-group col-md-6\"><label for=\"variantes_prenoms_epx\">Prénom:</label><textarea class=\"form-control\" rows=8 cols=20 id=\"variantes_prenoms_epx\">" . cp1252_vers_utf8($st_variantes_prenoms_epx) . "</textarea></div>");
            else
                print("<div class=\"col-md-6\">Pas de variantes de prénoms connues</div>");
            print("</div>"); // fin ligne
            print("</form>");
            print("</div>");
            print("</div>");
        } else
            print("<div class=\"row col-md-4\"></div>");
        print(rappel_recherches_communes("Recherche du couple: $gst_prenom_epx $gst_nom_epx X $gst_prenom_epse $gst_nom_epse", $gi_idf_type_acte, $gi_annee_min, $gi_annee_max, $gi_idf_source, $gi_idf_commune, $gi_rayon));
        if (!empty($gst_variantes_epse) ||  !empty($st_variantes_prenoms_epse)) {
            print("<div class=\"panel panel-primary col-md-4\">");
            print("<div class=\"panel-heading\">Variantes connues pour l'épouse</div>");
            print("<div class=\"panel-body\">");
            print('<form>');
            print('<div class="form-row">');
            if ($st_variantes_epse_trouvees != "")
                print("<div class=\"form-group col-md-6\"><label for=\"variantes_patros_epse\">Patronyme:</label><textarea class=\"form-control\" id=\"variantes_patros_epse\" rows=8 cols=20>" . cp1252_vers_utf8($st_variantes_epse_trouvees) . "</textarea></div>");
            else
                print("<div class=\"col-md-6\">Pas de variantes patronymiques connues</div>");
            if ($st_variantes_prenoms_epse != "")
                print("<div class=\"form-group col-md-6\"><label for=\"variantes_prenoms_epse\">Prénom:</label><textarea class=\"form-control\" rows=8 cols=20 id=\"variantes_prenoms_epse\">" . cp1252_vers_utf8($st_variantes_prenoms_epse) . "</textarea></div>");
            else
                print("<div class=\"col-md-6\">Pas de variantes de prénoms connues</div>");
            print("</div>"); // fin ligne
            print("</form>");
            print("</div>");
            print("</div>");
        } else
            print("<div class=\"row col-md-4\"></div>");
        break;
    case 'personne':
    case 'tous_patronymes':
        $st_libelle_commentaire = 'Personne recherchée';
        $gst_nom                = $_POST['nom'] ?? null;
        $gst_prenom             = $_POST['prenom'] ?? null;
        $gst_variantes          = $_POST['variantes'] ?? null;
        $gi_idf_type_presence   = $_POST['idf_type_presence'] ?? null;
        $gst_sexe               = $_POST['sexe'] ?? null;
        $gst_commentaires       = $_POST['commentaires'] ?? null;

        if ($gst_type_recherche == 'personne') {
            $gst_nom  = preg_replace('/\*+/', '*', $gst_nom);
            if ($gst_nom == '*' && empty($gi_idf_commune)) {
                print(nl2br("La recherche par joker * seul n'est autorisée que si une paroisse est choisie<br>"));
                print("<a href=" . PAGE_RECHERCHE . " class=\"RetourReponses\">Nouvelle Recherche</a><br>");
                exit();
            }
            if (($gst_nom != '*') && ($gst_nom != '!') && strlen($gst_nom) < 3) {
                print("<div>Le nom $gst_nom doit comporter au moins trois caractères</div>\n");
                print("<div><a href=" . PAGE_RECHERCHE . "?recherche=nouvelle class=\"RetourReponses\">Commencer une nouvelle recherche</a><br></div>");
                exit();
            }
        }

        $pf = @fopen("logs/requetes_personne.log", 'a');
        // date_default_timezone_set($gst_time_zone); NB: doit prendre le timezone du serveur
        list($i_sec, $i_min, $i_heure, $i_jmois, $i_mois, $i_annee, $i_j_sem, $i_j_an, $b_hiver) = localtime();
        $i_mois++;
        $i_annee += 1900;
        $st_date_log = sprintf("%02d/%02d/%04d %02d:%02d:%02d", $i_jmois, $i_mois, $i_annee, $i_heure, $i_min, $i_sec);
        $st_chaine_log = join(';', array($st_date_log, $session->getAttribute('ident'), $gst_adresse_ip, $gst_nom, $gst_prenom, $gi_idf_commune, $gi_rayon, $gi_annee_min, $gi_annee_max, $gst_commentaires));
        @fwrite($pf, "$st_chaine_log\n");
        @fclose($pf);
        $st_tables_prenom = '';
        $st_variantes_prenoms = '';
        $a_clauses_recherche = array();
        if ($gst_type_recherche == 'personne') {
            $gst_nom  = str_replace('*', '%', $gst_nom);
            $a_clauses_recherche[] = "p.patronyme " . $requeteRecherche->clause_droite_patronyme($gst_nom, $gst_variantes, 1);
            if ($gst_prenom != '') {
                $gst_prenom = str_replace('*', '%', $gst_prenom);
                $a_prenoms_simples = preg_split('/[,\s\/\=\&\-]+/', $gst_prenom);
                $a_clauses_prenoms =  array();
                $i_nb_prenoms = 1;
                foreach ($a_prenoms_simples as $st_prenom) {
                    $a_clauses_recherche[] = "prn_simple$i_nb_prenoms.libelle " . $requeteRecherche->clause_droite_prenom($st_prenom, $gst_variantes, $i_nb_prenoms);
                    $st_variantes_prenoms .= join("\n", $requeteRecherche->variantes_prenoms());
                    $st_tables_prenom .= " join `groupe_prenoms` gp$i_nb_prenoms on (prn.idf=gp$i_nb_prenoms.idf_prenom) join `prenom_simple` prn_simple$i_nb_prenoms on (gp$i_nb_prenoms.idf_prenom_simple=prn_simple$i_nb_prenoms.idf) ";
                    $i_nb_prenoms++;
                }
            }
        }
        if (!empty($gst_sexe)) $a_clauses_recherche[] = "p.sexe='$gst_sexe'";
        if ($gi_idf_source != 0) $a_clauses_recherche[] = "a.idf_source=$gi_idf_source";
        if ($gi_idf_type_acte == IDF_UNION)
            $a_clauses_recherche[] = "a.idf_type_acte in (" . IDF_MARIAGE . "," . IDF_CM . ")";
        else if ($gi_idf_type_acte != 0) $a_clauses_recherche[] = "a.idf_type_acte=$gi_idf_type_acte";
        if ($gi_annee_min != '') $a_clauses_recherche[] = "a.annee>=$gi_annee_min";
        if ($gi_annee_max != '') $a_clauses_recherche[] = "a.annee<=$gi_annee_max";
        if ($gi_idf_commune != 0)
            $a_clauses_recherche[] = "a.idf_commune " . $requeteRecherche->clause_droite_commune($gi_idf_commune, $gi_rayon, $gst_paroisses_rattachees);
        if (!empty($gi_idf_type_presence)) $a_clauses_recherche[] = "p.idf_type_presence=$gi_idf_type_presence";
        if (!empty($gst_commentaires))  $a_clauses_recherche[] = "match(a.commentaires,p.commentaires) against('$gst_commentaires' IN BOOLEAN MODE)";

        // Dates de relèves
        $releve_col = ($releve_type == 0) ? 'created' : 'changed';
        if (!empty($gi_releve_mois_min) && !empty($gi_releve_annee_min)) $a_clauses_recherche[] = " a." . $releve_col . ">=" . getDebutDateReleve($gi_releve_mois_min, $gi_releve_annee_min);
        if (!empty($gi_releve_mois_max) && !empty($gi_releve_annee_max)) $a_clauses_recherche[] = " a." . $releve_col . "<=" . getFinDateReleve($gi_releve_mois_max, $gi_releve_annee_max);
        $gst_requete_actes = "select distinct idf_acte,concat(ifnull(prn.libelle,''),' ',p.patronyme,' (',tp.nom,')'),a.annee,a.mois,a.jour from `personne` p left join `prenom` prn on (p.idf_prenom=prn.idf) $st_tables_prenom left  join `acte` a on (p.idf_acte=a.idf) join type_acte ta on (a.idf_type_acte=ta.idf) join commune_acte ca on (a.idf_commune=ca.idf) join `type_presence` tp on (p.idf_type_presence=tp.idf) where ";

        $gst_requete_parties = "select distinct a.idf,min(ta.nom),min(ca.nom),if (a.idf_type_acte=" . IDF_RECENS . ",GROUP_CONCAT(distinct concat(IFNULL(prn_parties.libelle,''),' ',parties.patronyme) order by parties.idf separator '<br>'),GROUP_CONCAT(distinct concat(IFNULL(prn_parties.libelle,''),' ',parties.patronyme) order by parties.idf separator ' X ')) as parties,a.date,a.idf_type_acte,a.cote,min(a.idf_source),a.details_supplementaires,m_a.statut,a.annee,a.mois,a.jour,a.created,a.changed from `acte` a join `personne` parties on (a.idf=parties.idf_acte and parties.idf_type_presence=" . IDF_PRESENCE_INTV . ") left join prenom prn_parties on (parties.idf_prenom=prn_parties.idf) join type_acte ta on (a.idf_type_acte=ta.idf) join commune_acte ca on (a.idf_commune=ca.idf) left join modification_acte m_a on (a.idf=m_a.idf_acte and m_a.statut='A') where ";

        if ($gst_type_recherche == 'personne') {
            $st_variantes_trouvees = join("\n", $requeteRecherche->variantes_trouvees());
        }

        $st_clauses_actes = implode(" and ", $a_clauses_recherche);
        //$gst_requete_actes = "$gst_requete_actes $st_clauses_actes";
        $gst_requete_actes = "$gst_requete_actes $st_clauses_actes order by a.annee,a.mois,a.jour";
        if (!empty($gst_variantes) || !empty($st_variantes_prenoms)) {
            print("<div class=\"panel panel-primary col-md-4\">");
            print("<div class=\"panel-heading\">Variantes connues</div>");
            print("<div class=\"panel-body\">");
            print('<form>');
            print('<div class="form-row">');

            if ($st_variantes_trouvees != "")
                print("<div class=\"form-group col-md-6\"><label for=\"variantes_patros\">Patronyme:</label><textarea class=\"form-control\" id=\"variantes_patros\" rows=8 cols=20>" . cp1252_vers_utf8($st_variantes_trouvees) . "</textarea></div>");
            else
                print("<div class=\"col-md-6\">Pas de variantes connues</div>");
            if ($st_variantes_prenoms != "")
                print("<div class=\"form-group col-md-6\"><label for=\"variantes_prenoms\">Prénom:</label><textarea class=\"form-control\" id=\"variantes_prenoms\" rows=8 cols=20>" . cp1252_vers_utf8($st_variantes_prenoms) . "</textarea></div>");
            else
                print("<div class=\"col-md-6\">Pas de variantes connues</div>");
            print("</div>"); // fin ligne
            print("</form>");
            print("</div>");
            print("</div>");
        }
        print(rappel_recherches_communes("Recherche de la personne: $gst_prenom $gst_nom", $gi_idf_type_acte, $gi_annee_min, $gi_annee_max, $gi_idf_source, $gi_idf_commune, $gi_rayon));
        print("<div class=\"col-md-4\"></div>");

        //FBOprint("Req=$gst_requete_actes<br>");
        break;
    default:
        print("<span class=\"label label-danger\">Erreur: mode $gst_type_recherche inconnu</span>");
}

$etape_prec = getmicrotime();
//FBOprint("Req actes=$gst_requete_actes<br>");
$a_params_precedents = $connexionBD->params();
$a_actes_recherches = $connexionBD->sql_select_multiple($gst_requete_actes);
$connexionBD->initialise_params($a_params_precedents);

$a_acte_vers_recherche = array();
foreach ($a_actes_recherches as $a_recherche) {
    list($i_idf_acte, $st_recherche, $i_annee, $i_mois, $i_jour) = $a_recherche;
    if (array_key_exists($i_idf_acte, $a_acte_vers_recherche))
        $a_acte_vers_recherche[$i_idf_acte] .= "<br>$st_recherche";
    else
        $a_acte_vers_recherche[$i_idf_acte] = $st_recherche;
}
$a_actes_trouves = array_keys($a_acte_vers_recherche);
$i_nb_reponses = count($a_actes_trouves);
$a_actes_page_courante = array_splice($a_actes_trouves, $current_page * $per_page, $per_page);

$ga_sources = $connexionBD->sql_select_multiple_par_idf("select idf,script_demande,utilise_ds,icone_info,icone_ninfo,icone_index from source");

$i_temps_recherche = temps_ecoule_en_ms("Temps de recherche");
if ($i_temps_recherche > 10000) {
    // enregistre les requêtes de plus de 10s
    $a_communes_acte = $connexionBD->liste_valeur_par_clef("SELECT idf,nom FROM commune_acte order by nom");
    $connexionBD->initialise_params(array(':ident' => $session->getAttribute('ident')));
    $st_adherent = cp1252_vers_utf8($connexionBD->sql_select1("SELECT concat(prenom,' ',nom,' (',idf,')') FROM adherent where ident=:ident"));
    $pf = @fopen("logs/requetes_lentes.log", 'a');
    $st_date_log = sprintf("%02d/%02d/%04d %02d:%02d:%02d", $i_jmois, $i_mois, $i_annee, $i_heure, $i_min, $i_sec);
    $st_parties = ($gst_type_recherche == 'couple') ? "$gst_nom_epx $gst_prenom_epx X $gst_nom_epse $gst_prenom_epse (Var pat epx=$gst_variantes_epx, Var pat epse=$gst_variantes_epse)" : "$gst_nom $gst_prenom (Var=$gst_variantes)";
    $st_commune = array_key_exists($gi_idf_commune, $a_communes_acte) ? cp1252_vers_utf8($a_communes_acte[$gi_idf_commune]) : '';

    $st_chaine_log = join(';', array($st_date_log, $st_adherent, $gst_type_recherche, $st_parties, $st_commune, $gi_rayon, $gi_annee_min, $gi_annee_max, $i_temps_recherche));
    @fwrite($pf, "$st_chaine_log\n");
    fclose($pf);
}
print('<div class="text-center row col-md-12">' . "Temps de la recherche" . ' : ' . $i_temps_recherche . 'ms</div>');

print("<div class=\"row col-md-12 text-center\"><span class=\"badge\">$i_nb_reponses</span> occurrence(s) trouvée(s). ");
print('<div id="curseur" class="infobulle"></div>');
print("<div class='form-group col-md-2 col-md-offset-5'>");
print '<label for="per-page">Nombre de résultats par page</label>';
print '<select id="per-page" name="per_page" class="form-control">';
foreach ($per_page_options as $key => $value) {
    $elected = ($value == $per_page) ? ' selected="selected" ' : '';
    print '<option value="' . $key . '" ' . $elected . '>' . $value . '</option>';
}
print '</select>';
print("</div></div>");

if ($i_nb_reponses > 0) {
    $a_params_precedents = $connexionBD->params();
    $st_actes_page_courante = join(',', $a_actes_page_courante);
    $gst_requete_parties = "$gst_requete_parties a.idf in($st_actes_page_courante) group by a.idf order by annee,mois,jour";
    //FBOprint("R=$gst_requete_parties<br>");
    $a_actes = $connexionBD->sql_select_multiple($gst_requete_parties);
    $connexionBD->initialise_params($a_params_precedents);

    foreach ($a_actes as $a_acte) {
        list($i_idf_acte, $st_type_acte, $st_commune, $st_parties, $st_date, $i_idf_type_acte, $st_cote, $i_idf_source, $i_details, $st_tdm, $i_annee, $i_mois, $i_jour, $date_creation, $date_modification) = $a_acte;
        list($st_script_demande, $i_utilise_detail, $st_icone_info, $st_icone_ninfo, $st_icone_index) = $ga_sources[$i_idf_source];
        if (!empty($date_creation) && !empty($date_modification))
            $releve = 'Date de publication: ' . date('d/m/Y', $date_creation) . '<br>Date de modification: ' . date('d/m/Y', $date_modification);
        else
            $releve = '';
        if ($i_utilise_detail == 1) {
            if ($i_idf_source == IDF_SOURCE_TD) {
                switch ($i_details) {
                    case 1:
                        $st_icone_td = $st_icone_info;
                        $st_detail = "<a href=\"$st_script_demande?idf_acte=$i_idf_acte\" data-toggle=\"tooltip\" data-html=\"true\" title=\"$st_cote<br>$releve\" class=\"popup\"><img src=\"./images/$st_icone_td\" border=0 alt=\"infos\" ></a>";
                        break;
                    case 2:
                        $st_icone_td = $st_icone_index;
                        $st_detail = "<a href=\"PropositionModification.php?idf_acte=$i_idf_acte\" target=\"_blank\" data-toggle=\"tooltip\" data-html=\"true\"  title=\"$st_cote<br>$releve\"><img src=\"./images/$st_icone_td\" border=0 alt=\"infos\" ></a>";
                        break;
                    default:
                        $st_icone_td = $st_icone_ninfo;
                        $st_detail = "<a href=\"$st_script_demande?idf_acte=$i_idf_acte\" target=\"_blank\" data-toggle=\"tooltip\" data-html=\"true\"  title=\"$releve\" class=\"popup\"><img src=\"./images/$st_icone_td\" border=0 alt=\"infos\" ></a>";
                }
            } else if ($i_details == 1)
                $st_detail = "<a href=\"$st_script_demande?idf_acte=$i_idf_acte\" data-toggle=\"tooltip\" data-html=\"true\"  title=\"$releve\" class=\"popup\"><img src=\"./images/$st_icone_info\" border=0 alt=\"infos\"></a>";
            else if ($i_details == 2)
                $st_detail = "<img src=\"./images/$st_icone_index\" border=0 alt=\"$st_cote\" data-toggle=\"tooltip\" data-html=\"true\"  title=\"$st_cote<br> $releve\" class=\"popup\">";
            else
                $st_detail = "<img src=\"./images/$st_icone_ninfo\" alt=\"pas d'infos\" data-toggle=\"tooltip\" data-html=\"true\" title=\"Le relevé ne comporte pas de renseignements supplémentaires que ceux déjà affichés\">";
        } else
            $st_detail = "<a href=\"$st_script_demande?idf_acte=$i_idf_acte\" data-toggle=\"tooltip\"  data-html=\"true\" title=\"$releve\" class=\"popup\"><img src=\"./images/$st_icone_info\" border=0 alt=\"infos\"></a>";

        if ($gst_type_recherche == 'tous_pat') {
            if (a_droits($session->getAttribute('ident'), DROIT_CHARGEMENT))
                $a_tableau[] =  array($st_type_acte, $st_parties, $st_commune, $st_date, $st_detail, "<a href=\"./Administration/ModifieActe.php?idf_acte=$i_idf_acte\"><span class=\"glyphicon glyphicon-edit\"></a>");
            else
                $a_tableau[] =  array($st_type_acte, $st_parties, $st_commune, $st_date, $st_detail);
        } else {
            if (a_droits($session->getAttribute('ident'), DROIT_CHARGEMENT))
                $a_tableau[] =  array($st_type_acte, $st_parties, $st_commune, $st_date, $a_acte_vers_recherche[$i_idf_acte], $st_detail, "<a href=\"./Administration/ModifieActe.php?idf_acte=$i_idf_acte\"><span class=\"glyphicon glyphicon-edit\"></span></a>");
            else
                $a_tableau[] =  array($st_type_acte, $st_parties, $st_commune, $st_date, $a_acte_vers_recherche[$i_idf_acte], $st_detail);
        }
    }


    if ($gst_type_recherche == 'tous_pat') {
        if (a_droits($session->getAttribute('ident'), DROIT_CHARGEMENT))
            $pagination = new PaginationTableau(basename(__FILE__), 'num_page', count($a_tableau), $per_page, 100, array('Type d\'acte', 'Parties', 'Commune', 'Date', '', ''));
        else
            $pagination = new PaginationTableau(basename(__FILE__), 'num_page', count($a_tableau), $per_page, 100, array('Type d\'acte', 'Parties', 'Commune', 'Date', ''));
    } else {
        if (a_droits($session->getAttribute('ident'), DROIT_CHARGEMENT))
            $pagination = new PaginationTableau(basename(__FILE__), 'num_page', count($a_tableau), $per_page, 100, array('Type d\'acte', 'Parties', 'Commune', 'Date', $st_libelle_commentaire, '', ''));
        else
            $pagination = new PaginationTableau(basename(__FILE__), 'num_page', count($a_tableau), $per_page, 100, array('Type d\'acte', 'Parties', 'Commune', 'Date', $st_libelle_commentaire, ''));
    }

    $pagination->init_page_cour($gi_num_page);
    $pagination->affiche_entete_liens_navigation();
    $pagination->affiche_tableau_simple($a_tableau);
    print $pagination->get_pagination(basename(__FILE__), $i_nb_reponses, $per_page, $current_page);
} else {
    print('<div class="row col-md-12 alert alert-danger">');
    print("Aucun résultat<br>");
    print("Vérifiez que vous n'avez pas mis trop de contraintes (commune,type d'acte,...)<br>");
    print("</div>");
    print("<div class=\"row col-md-12 alert alert-info\">");
    print("Rappel de vos critères: <br>");
    print(nl2br($st_criteres));
    print("</div>");
}

print('<div class="row">');
print('<div class="btn-group col-md-offset-3 col-md-6" role="group">');
print('<a class="btn btn-primary" href="' . PAGE_RECHERCHE . '" role="button"><span class="glyphicon glyphicon-search"></span>  Revenir à la page de recherche</a>');
print('<a class="btn btn-warning" href="' . PAGE_RECHERCHE . '?recherche=nouvelle" role="button"><span class="glyphicon glyphicon-erase"></span> Commencer une nouvelle recherche</a>');
print("</div>");
print("</div>");

print("</div>");
print("</body></html>");
