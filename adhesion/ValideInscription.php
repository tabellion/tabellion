<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------
//http://127.0.0.1:8888/ValideInscription.php
//http://127.0.0.1:8888/ValideInscription.php?alea=3969&idf_agc=0&idf_prov=4578

require_once __DIR__ . '/commun/config.php';
require_once __DIR__ . '/commun/constantes.php';
require_once __DIR__ . '/commun/ConnexionBD.php';
require_once __DIR__ . '/commun/commun.php';
require_once __DIR__ . '/commun/Adherent.php';

$connexionBD = ConnexionBD::singleton($gst_serveur_bd, $gst_utilisateur_bd, $gst_mdp_utilisateur_bd, $gst_nom_bd);

$alea = isset($_GET['alea']) ? (int) $_GET['alea'] : '0';
$idf_agc = isset($_GET['idf_agc']) ? (int) $_GET['idf_agc'] : '0';
$idf_prov = isset($_GET['idf_prov']) ? (int) $_GET['idf_prov'] : '0';

/*
Lorsque l'adhérent clique sur le lien, le script ValideInscription.php est exécuté
On va vérifier que le clef de la table correspond à la clef fournie
par l'adhérent (clef + numéro d'adhérent).
Si c'est le cas, on poursuit l'inscription sinon, on affiche un message d'erreur.
*/
$connexionBD->initialise_params(array(':idf_prov' => $idf_prov, ':alea' => $alea));
$st_requete = "select idf, ins_nom, ins_prenom,ins_cp,ins_pays,ins_email_perso from inscription_prov where idf = :idf_prov and ins_alea = :alea";
$a_retour_prov = $connexionBD->sql_select_liste($st_requete);
if (empty($a_retour_prov))   // Pas d'enregistrement
{
    $mess = "Erreur d'acces a l'inscription provisoire (1)";
    $host  = $_SERVER['HTTP_HOST'];
    $extra = 'Inscription.php';
    header("Location: $gst_url_inscription?erreur=$mess");
    exit;
}
// On l'a trouvé, on va plus loin
else {
    list($gi_idf_ins, $gst_nom_ins, $gst_prenom_ins, $gst_cp_ins, $gst_pays_ins, $gst_email_ins) = $a_retour_prov;
    print('<!DOCTYPE html>');
    print("<head>");
    print('<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">');
    print('<meta http-equiv="content-language" content="fr">');
    print('<meta name="viewport" content="width=device-width, initial-scale=1.0">');
    print("<link href='assets/css/styles.css' type='text/css' rel='stylesheet'>");
    print("<link href='assets/css/bootstrap.min.css' rel='stylesheet'>");
    print("<script src='assets/js/jquery-min.js' type='text/javascript'></script>");
    print("<script src='assets/js/bootstrap.min.js' type='text/javascript'></script>");

    print('<title>Validation de votre Inscription en ligne</title>');
    print('</head>');
    print("<body>\n");
    print('<div class="container">');

    require_once __DIR__ . '/commun/menu.php';

    $mess = "";
    // Essaye de récupérer l'ancien numéro de l'adhérent si le numéro n'est pas saisie et son adresse email correspond à un adhérent connu
    if (empty($idf_agc)) {
        $connexionBD->initialise_params(array(':email' => $gst_email_ins));
        $st_requete = "select idf,mdp from adherent where email_perso=:email";
        $a_retour = $connexionBD->sql_select_liste($st_requete);
        if (is_array($a_retour) && count($a_retour) > 0) {
            list($idf_agc, $st_mdp) = $a_retour;
            $connexionBD->initialise_params(array(':idf_agc' => $idf_agc, ':mdp' => $st_mdp, ':idf_prov' => $idf_prov));
            $st_requete = "update `inscription_prov` set ins_idf_agc = :idf_agc,ins_mdp=:mdp where idf = :idf_prov";
            $connexionBD->execute_requete($st_requete);
        }
    }

    if (!empty($idf_agc))   // recherche de l'adhérent
    {
        $st_requete = "select max(idf) from adherent";
        $i_max_idf = $connexionBD->sql_select1($st_requete);
        $connexionBD->initialise_params(array(':idf_agc' => $idf_agc));
        $st_requete = "select idf, nom, prenom, statut,annee_cotisation, cp,pays from adherent where idf = :idf_agc";
        $a_retour_adh = $connexionBD->sql_select_liste($st_requete);
        /*
	  Si l'ancien numéro d'inscription est défini mais n'existe pas
      dans la table des adhérents, on garde également le numéro.
      */
        if (empty($a_retour_adh) && $idf_agc < $i_max_idf)   // numéro agc inexistant
        {
            $mess = "Votre inscription sera valid&eacute;e avec le num&eacute;ro " . $idf_agc . " que vous avez saisi (2)\n";
        } else {
            list($gi_idf, $gst_nom, $gst_prenom, $gst_status, $gi_annee_cotisation, $gst_cp, $gst_pays) = $a_retour_adh;
            $aujourdhui = getdate();
            /*
         Si l'ancien numéro d'inscription est défini mais est déjà associé
         à un adhérent qui n'est pas suspendu alors on le met à nul.
         L'adhérent sera inscrit sous un nouveau numéro
         Si la date d'inscription est après septembre, on considère qu'il s'agit d'une tentative de réadhésion
         */
            if ($gst_status != 'S' && $aujourdhui['mon'] <= 9)  // Statut de l'adhérent non suspendu --> on remet à 0 dans l'inscription provisoire
            {
                $connexionBD->initialise_params(array(':idf_prov' => $idf_prov));
                $st_requete = "update `inscription_prov` set ins_idf_agc = '0' where idf = :idf_prov";
                $connexionBD->execute_requete($st_requete);
                $mess = "Votre inscription sera valid&eacute;e avec un nouveau num&eacute;ro (3)\n";
            }
            /*
         Si l'ancien numéro d'inscription est défini et correspond à un
         adhérent suspendu et que les noms et prénoms coïncident
         avec le nom de l'adhérent, on garde le numéro sinon on le met à nul
        */ else                        // Statut de l'adhérent suspendu  --> même nom et prénom alors on garde le numéro
            {
                setlocale(LC_CTYPE, 'fr_FR.UTF8');
                $st_nom_ins = trim(str_replace('-', ' ', strtoupper(iconv("UTF-8", "ASCII//TRANSLIT", $gst_nom_ins))));
                $st_nom = trim(str_replace('-', ' ', strtoupper(iconv("UTF-8", "ASCII//TRANSLIT", $gst_nom))));
                $st_prenom_ins = trim(str_replace('-', ' ', strtoupper(iconv("UTF-8", "ASCII//TRANSLIT", $gst_prenom_ins))));
                $st_prenom = trim(str_replace('-', ' ', strtoupper(iconv("UTF-8", "ASCII//TRANSLIT", $gst_prenom))));

                if (
                    $st_nom_ins == $st_nom &&   // nom et prénom identiques
                    $st_prenom_ins == $st_prenom
                ) {
                    $mess = "Votre inscription sera valid&eacute;e avec le num&eacute;ro " . $idf_agc . " que vous avez saisi ou que nous avons retrouv&eacute; (4)";
                    $connexionBD->initialise_params(array(':idf_agc' => $idf_agc));
                    $st_requete = "update  `inscription_prov` i_p join `adherent` adh on (i_p.ins_idf_agc=adh.idf) set i_p.ins_mdp=adh.mdp where i_p.ins_idf_agc=:idf_agc ";
                    $connexionBD->execute_requete($st_requete);
                } else {
                    // on remet à 0 dans l'inscription provisoire
                    $st_requete = "update `inscription_prov` set ins_idf_agc = '0' where idf = :idf_prov";
                    $connexionBD->initialise_params(array(':idf_prov' => $idf_prov));
                    $connexionBD->execute_requete($st_requete);
                    $mess = "Votre inscription sera valid&eacute;e avec un nouveau num&eacute;ro (5)\n";
                }
            }
        }
    }
    // Tout est ok, on poursuit vers le paiement en ligne de l'inscription.
}

print('<div class="panel panel-primary">');
print('<div class="panel-heading">Validation de votre inscription</div>');
print('<div class="panel-body">');
print("<div class=\"alert alert-info\">$mess</div>\n");
print("<form method=\"post\" action=\"Payement.php\">\n");

print('<div class="row">');
print('<div class="form-group col-md-6">');
print("<label for=\"nom_adht\" class=\"col-md-2 col-form-label\">Nom</label>");
print("<div class=\"col-md-4\"><input type=\"text\" readonly class=\"form-control-plaintext\" id=\"nom_adht\" value=\"$gst_nom_ins\"></div>");
print('</div>');
print('<div class="form-group col-md-6">');
print("<label for=\"prenom_adht\" class=\"col-md-2 col-form-label\">Pr&eacute;nom</label>");
print("<div class=\"col-md-4\"><input type=\"text\" readonly class=\"form-control-plaintext\" id=\"prenom_adht\" value=\"$gst_prenom_ins\"></div>");
print('</div>');
print('</div>');

print('<div class="row">');
print('<div class="form-group col-md-6">');
print("<label for=\"cp_adht\" class=\"col-md-2 col-form-label\">Code Postal</label>");
print("<div class=\"col-md-4\"><input type=\"text\" readonly class=\"form-control-plaintext\" id=\"cp_adht\" value=\"$gst_cp_ins\"></div>");
print('</div>');
print('<div class="form-group col-md-6">');
print("<label for=\"pays_adht\" class=\"col-md-2 col-form-label\">Pays</label>");
print("<div class=\"col-md-4\"><input type=\"text\" readonly class=\"form-control-plaintext\" id=\"pays_adht\" value=\"$gst_pays_ins\"></div>");
print('</div>');
print('</div>');

print("<input type=hidden name=nom value=\"$gst_nom_ins\">\n");
print("<input type=hidden name=prenom value=\"$gst_prenom_ins\">\n");
print("<input type=hidden name=idf_prov value=\"$idf_prov\">\n");
print("<input type=hidden name=idf_agc value=\"$idf_agc\">\n");
print("<input type=hidden name=type value=\"" . TYPE_INSCRIPTION . "\">\n");
$adherent = new Adherent($connexionBD, $idf_agc);
print($adherent->formulaire_type_inscription($gst_pays_ins, $gst_cp_ins));
print $adherent->formulaire_aides_possibles();
print $adherent->formulaire_origine();
print('<button type=submit class="btn btn-primary col-md-offset-4 col-md-4">Proc&eacute;der au r&egrave;glement</button>');
print("</div></div>");
print('</div></body></html>');
