<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------

require_once __DIR__ . '/../../Commun/config.php';
require_once __DIR__ . '/../../Commun/constantes.php';
require_once __DIR__ . '/../../Commun/ConnexionBD.php';
require_once __DIR__ . '/../../Commun/Courriel.php';

$connexionBD = ConnexionBD::singleton($gst_serveur_bd, $gst_utilisateur_bd, $gst_mdp_utilisateur_bd, $gst_nom_bd);

$ga_tables = array('acte', 'chargement', 'commune_personne', 'demandes_adherent', 'document', 'groupe_prenoms', 'modification_acte', 'modification_personne', 'patronyme', 'personne', 'photos', 'prenom', 'prenom_simple', 'profession', 'releveur', 'rep_not_actes', 'rep_not_desc', 'rep_not_variantes', 'source', 'stats_cnx', 'stats_commune', 'stats_patronyme', 'tableau_kilometrique', 'type_acte', 'type_presence', 'union', 'variantes_patro', 'variantes_prenom');

$st_sujet = 'Optimisation de la base ' . SIGLE_ASSO;
$st_texte = '';
foreach ($ga_tables as $st_table) {
    print("Table $st_table<br>");
    list($usec, $sec) = explode(" ", microtime());
    $i_temp_prec = (float)$usec + (float)$sec;
    $connexionBD->execute_requete("optimize table `$st_table`");
    list($usec, $sec) = explode(" ", microtime());
    $i_temp_cour = (float)$usec + (float)$sec;
    $st_texte .= sprintf("Optimisation de <b>%s</b> en %d ms<br>\n", strtoupper($st_table), $i_temp_cour - $i_temp_prec);
    //$st_texte .= sprintf("Derniere erreur SQL=%s <br>\n",$connexionBD->msg_erreur());
}
$connexionBD->ferme();

$courriel = new Courriel($gst_rep_site, $gst_serveur_smtp, $gst_utilisateur_smtp, $gst_mdp_smtp, $gi_port_smtp);
$courriel->setExpediteur(EMAIL_DIRASSO, LIB_ASSO);
$courriel->setAdresseRetour(EMAIL_DIRASSO);
$courriel->setEnCopie(EMAIL_DIRASSO);

$courriel->setDestinataire(EMAIL_INFOASSO, '');
//$courriel->setDestinataire('fbouffanet@yahoo.fr','');

$courriel->setSujet($st_sujet);
$courriel->setTexte($st_texte);
if (!$courriel->envoie()) {
    print("<div class=\"alert alert-danger\">Le message n'a pu être envoyé. Erreur: " . $courriel->get_erreur() . "</div>");
}

print("Script terminé<br>");
