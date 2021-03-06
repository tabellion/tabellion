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

// ========== Request
$gst_mode = $_POST['mode'] ?? 'LISTE_DEMANDES';
$id_user = $user['idf'];

/**
 * Affiche la liste des cantons choisis
 * @param object $pconnexionBD
 * @param integer $pi_idf_adherent Identifiant de l'adhérent
 */
function affiche_cantons_choisis($pconnexionBD, $pi_idf_adherent)
{
    global $gi_num_page_cour;

    print("<form   method=\"post\">");
    $st_requete = "select distinct c.idf,ca.idf_canton,c.nom from canton c left join cantons_adherent ca on (c.idf=ca.idf_canton and ca.idf_adherent=$pi_idf_adherent) order by c.nom";
    $a_liste_cantons = $pconnexionBD->sql_select_multiple_par_idf($st_requete);
    print("<table class=\"table table-bordered table-striped\">\n");
    print("<tr><th>&nbsp;</th><th>Canton</th></tr>");
    foreach ($a_liste_cantons as $i_idf_canton => $a_info) {
        list($i_idf_canton_selectionne, $st_canton) = $a_info;
        print("<tr><td>");
        if ($i_idf_canton == $i_idf_canton_selectionne) {
            print("<input type=checkbox name='cantons_choisis[]' id='canton_$i_idf_canton' value='$i_idf_canton' checked='checked' class=\"form-check-input\">");
        } else {
            print("<input type=checkbox name='cantons_choisis[]' id='canton_$i_idf_canton' value='$i_idf_canton' class=\"form-check-input\">");
        }
        print("</td><td>" . cp1252_vers_utf8($st_canton) . "</td></tr>\n");
    }
    print("</table>\n");
    print("<input type=hidden name='mode' value='MODIFICATION_CANTONS'>");
    print('<div class="form-group col-md-4 col-md-offset-4"><button type="submit" class="btn btn-primary">Mette &agrave; jour les cantons</button></div>');
    print('</form>');
}

/**
 * Affiche la liste des dernièeres demandes
 * @param object $pconnexionBD
 * @param string $pi_idf_adherent identifiant de l'adéhrent
 * param string $pst_info infos à afficher
 */
function affiche_dernieres_demandes($pconnexionBD, $pi_idf_adherent, $pst_info)
{
    $st_requete = "select count(*) from cantons_adherent where idf_adherent=$pi_idf_adherent";
    $i_nb_cantons_adherents = $pconnexionBD->sql_select1($st_requete);
    if ($i_nb_cantons_adherents != 0) {
        $a_commune_personne = $pconnexionBD->liste_valeur_par_clef("select idf, nom from commune_personne");
        $a_profession = $pconnexionBD->liste_valeur_par_clef("select idf, nom from profession");
        $a_type_acte = $pconnexionBD->liste_valeur_par_clef("select idf, nom from type_acte");
        $st_requete = "select a.idf,min(a.date) as date_acte,min(ta.nom),min(ca.nom) as commune,GROUP_CONCAT(DISTINCT concat(prenom.libelle,' ',p.patronyme) order by p.idf separator ' X '),concat(min(ddr.prenom),' ',min(ddr.nom)) as demandeur,min(ddr.email_perso),DATE_FORMAT(min(da.date_demande),'%d/%m/%Y %k:%i') as date_demande2 from  demandes_adherent da join acte a on (da.idf_acte=a.idf) join commune_acte ca on (a.idf_commune=ca.idf) join cantons_adherent c_adht on (ca.idf_canton=c_adht.idf_canton) join adherent adht on (c_adht.idf_adherent=adht.idf) join adherent ddr on (da.idf_adherent=ddr.idf) join type_acte ta on (da.idf_type_acte=ta.idf) join personne p on (p.idf_acte=a.idf) join prenom on (p.idf_prenom=prenom.idf) where adht.idf=$pi_idf_adherent and da.idf_type_acte not in (" . IDF_NAISSANCE . "," . IDF_DECES . ") and unix_timestamp(now()) - unix_timestamp(da.date_demande)<= 604800 and p.idf_type_presence=" . IDF_PRESENCE_INTV . " group by a.idf
order by commune, demandeur, date_demande2 desc";
        //print("Req=$st_requete<br>");
        $a_liste_demandes = $pconnexionBD->sql_select_multiple_par_idf($st_requete);
        print('<div class="panel panel-primary">');
        print("<div class=\"panel-heading\">Demandes des sept derniers jours selon vos cantons de préférence</div>");
        print('<div class="panel-body">');
        if (!empty($pst_info)) {
            print("<div class=\"alert alert-info\">$pst_info</div>");
        }
        print("<form   method=\"post\">");
        print("<input type=hidden name='mode' value='AFFICHAGE_CANTONS'>");
        print('<div class="form-group col-md-4 col-md-offset-4"><button type="submit" class="btn btn-primary">Modifier les cantons de préférence</button></div>');
        print('</form>');
        print('<div class="panel panel-default">');
        print('<div class="panel-heading">Liste des demandes de mariage/actes divers des autres adhérents</div>');
        print('<div class="panel-body">');
        if (count($a_liste_demandes) > 0) {
            print("<table class=\"table table-bordered table-striped table-sm\">\n");
            print("<tr>");
            print("<th>Date de l'acte</th>");
            print("<th>Type de l'acte</th>");
            print("<th>Commune de l'acte</th>");
            print("<th>Parties</th>");
            print("<th>Relevé sans témoins ni commentaires</th>");
            print("<th>Demandeur</th>");
            print("<th>Contacter</th>");
            print("<th>Date de la demande</th>");
            print("</tr>");
            foreach ($a_liste_demandes as $i_idf_acte => $a_groupe) {
                list($st_date, $st_type_acte, $st_commune, $st_parties, $st_demandeur, $st_email_demandeur, $st_date_dem) = $a_groupe;
                print("<tr>");
                print("<td>$st_date</td>");
                print("<td>" . cp1252_vers_utf8($st_type_acte) . "</td>");
                print("<td>" . cp1252_vers_utf8($st_commune) . "</td>");
                print("<td>" . cp1252_vers_utf8($st_parties) . "</td>");
                $o_acte = new Acte($pconnexionBD, null, null, null, null, null, null);
                $o_acte->charge($i_idf_acte);
                $st_description_acte = $o_acte->versChaineSansTemoins();

                print("<td><textarea class=\"form-control\">" . $st_description_acte . "</textarea></td>");
                print("<td>" . cp1252_vers_utf8($st_demandeur) . "</td>");

                print("<td align=\"center\"><a href=\"mailto:$st_email_demandeur?subject=Votre demande " . SIGLE_ASSO . ": " . cp1252_vers_utf8($st_parties) . " a " . cp1252_vers_utf8($st_commune) . "\"><span class=\"glyphicon glyphicon glyphicon-envelope\"></span></a></td>");
                print("<td>$st_date_dem</td>");
                print("</tr>\n");
            }
            print("</table>");
        } else {
            print("<div class=\"alert alert-danger\">Pas de demandes durant les 7 derniers jours</div>");
        }
        print("</div></div>");
        print("</div></div>");
    } else {
        print("<div class=\"alert alert-danger\">Aucun canton de préférence défini. Merci de les préciser</div>");
        print("<form method=\"post\">");
        print("<input type=hidden name='mode' value='AFFICHAGE_CANTONS'>");
        print('<div class="form-group col-md-4 col-md-offset-4"><button type="submit" class="btn btn-primary">Modifier les cantons de préférences</button></div>');
        print('</form>');
    }
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
    <title>Base <?= SIGLE_ASSO; ?> : Dernières Demandes</title>
</head>

<body>
    <div class="container">

        <?php require_once __DIR__ . '/commun/menu.php';

        switch ($gst_mode) {
            case 'AFFICHAGE_CANTONS':
                affiche_cantons_choisis($connexionBD, $id_user);
                break;
            case 'MODIFICATION_CANTONS':
                $a_cantons_choisis = isset($_POST['cantons_choisis']) ? $_POST['cantons_choisis'] :  array();
                $st_requete = "delete from cantons_adherent where idf_adherent=$id_user";
                $connexionBD->execute_requete($st_requete);
                $a_cantons = array();
                if (count($a_cantons_choisis) > 0) {
                    foreach ($a_cantons_choisis as $i_idf_canton) {
                        $a_cantons[] = "($id_user,$i_idf_canton)";
                    }
                    $st_cantons = join(',', $a_cantons);
                    $st_requete = "insert cantons_adherent(idf_adherent,idf_canton) values $st_cantons";
                    $connexionBD->execute_requete($st_requete);
                }
                affiche_dernieres_demandes($connexionBD, $id_user, "Liste mise &agrave; jour");
                break;
            case 'LISTE_DEMANDES':
                affiche_dernieres_demandes($connexionBD, $id_user, '');
                break;
        } ?>
    </div>
</body>

</html>