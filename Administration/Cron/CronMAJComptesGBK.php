<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------


require_once __DIR__ . '/../../Commun/config.php';
require_once __DIR__ . '/../../Commun/constantes.php';
require_once __DIR__ . '/../../Origin/ConnexionBD.php';
require_once __DIR__ . '/../../Origin/Adherent.php';
require_once __DIR__ . '/../../Origin/Courriel.php';

$connexionBD = ConnexionBD::singleton($gst_serveur_bd, $gst_utilisateur_bd, $gst_mdp_utilisateur_bd, $gst_nom_bd);
$st_requete = "select idf , annee_cotisation, statut from adherent where statut in ('" . ADHESION_BULLETIN . "','" . ADHESION_INTERNET . "')   and annee_cotisation >=  YEAR( NOW( )) order by idf ";
$a_liste_idf = $connexionBD->sql_select($st_requete);

$st_cmd_gbk = '';
foreach ($a_liste_idf as $i_idf) {
	$st_cmd_gbk .= "set " . PREFIXE_ADH_GBK . $i_idf . " " . NB_POINTS_GBK . "\r\n";
}

if (!Adherent::execute_cmd_gbk($st_cmd_gbk)) {
	$st_message_erreur = Adherent::erreur_gbk();
	$st_requete = "select email_perso from adherent adht join privilege p on (adht.idf=p.idf_adherent) where p.droit='" . DROIT_GENEABANK . "' order by email_perso";
	$a_adresses = $connexionBD->sql_select($st_requete);
	$st_sujet = "Test Comptes GBK";
	$st_sujet = "Erreur lors de la maj des comptes GBK";
	$courriel = new Courriel($gst_rep_site, $gst_serveur_smtp, $gst_utilisateur_smtp, $gst_mdp_smtp, $gi_port_smtp);
	$courriel->setExpediteur(EMAIL_DIRASSO, LIB_ASSO);
	$courriel->setAdresseRetour(EMAIL_DIRASSO);
	$courriel->setEnCopie(EMAIL_DIRASSO);
	foreach ($ga_emails_gestbase as $st_email_destinataire) {
		$courriel->setDestinataire($st_email_destinataire, '');
	}
	$courriel->setSujet($st_sujet);
	$courriel->setTexte($st_message_erreur);
	if (!$courriel->envoie()) {
		print("<div class=\"alert alert-danger\">Le message n'a pu être envoyé. Erreur: " . $courriel->get_erreur() . "</div>");
	}
} else {
	print("<h3>MAJ r&eacute;ussie</h3>");
}
