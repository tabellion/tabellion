<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------

use PHPMailer\PHPMailer\PHPMailer;

require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';

class Courriel
{
	protected $courriel;
	protected $st_erreur;

	/*
	* Constructeur de classe
	* @param string	$pst_rep_site Chemin local du site
	* @param string $pst_serveur_smtp nom du serveur SMTP
	* @param string $pst_utilisateur_smtp Compte SMTP
	* @param string $pst_mdp_smtp Mot de passe SMTP
	* @param integer $pi_port_smtp port SMTP
	*/
	public function __construct($pst_rep_site, $pst_serveur_smtp = '', $pst_utilisateur_smtp = '', $pst_mdp_smtp = '', $pi_port_smtp = 587)
	{
		$this->courriel = new PHPMailer(true);
		$this->courriel->CharSet = 'UTF-8';
		$this->courriel->Encoding = 'base64';
		$this->courriel->isHTML(true);
		if (!empty($pst_serveur_smtp) && !empty($pst_utilisateur_smtp) && !empty($pst_mdp_smtp) && !empty($pi_port_smtp)) {
			//print("<div class=\"alert alert-warning\">Utilisation de SMTP</div>");
			//$this->courriel->SMTPDebug = SMTP::DEBUG_SERVER;
			$this->courriel->isSMTP();
			$this->courriel->Host       = $pst_serveur_smtp;
			$this->courriel->SMTPAuth   = true;
			$this->courriel->Username   = $pst_utilisateur_smtp;
			$this->courriel->Password   = $pst_mdp_smtp;
			$this->courriel->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
			$this->courriel->Port       = $pi_port_smtp;
			// pour réactiver la vérification du certificat, commenter les lignes ci-dessous
			$this->courriel->SMTPOptions = array(
				'ssl' => array(
					'verify_peer' => false,
					'verify_peer_name' => false,
					'allow_self_signed' => true
				)
			);
			$this->st_erreur = '';
		}
	}

	/*
	* Setter Expediteur
	* @param string $pst_adresse_expediteur email de l'expéditeur
	* @param string $pst_nom_expediteur nom de l'expéditeur
	*/
	public function setExpediteur($pst_adresse_expediteur, $pst_nom_expediteur)
	{
		$this->courriel->setFrom($pst_adresse_expediteur, $pst_nom_expediteur);
	}

	/*
	* Setter Adresse de retour
	* @param string $pst_adresse_retour adresse de retour
	*/
	public function setAdresseRetour($pst_adresse_retour)
	{
		$this->courriel->addReplyTo($pst_adresse_retour, $pst_adresse_retour);
	}

	/*
	* Setter Destinataire
	* @param string $pst_adresse_destinataire email du destinataire
	* @param string $pst_nom_destinataire nom du destinataire
	*/
	public function setDestinataire($pst_adresse_destinataire, $pst_nom_destinataire)
	{
		$this->courriel->addAddress($pst_adresse_destinataire, $pst_nom_destinataire);
	}

	/*
	* Setter EnCopie
	* @param string $pst_adresse_copie email en copie
	*/
	public function setEnCopie($pst_adresse_copie)
	{
		$this->courriel->addCC($pst_adresse_copie);
	}

	/*
	* Setter EnCopieCachée
	* @param string $pst_adresse_copie email en copie
	*/
	public function setEnCopieCachee($pst_adresse_copie)
	{
		$this->courriel->addBCC($pst_adresse_copie);
	}

	/*
	* Setter Sujet
	* @param string $pst_suject sujet
	*/
	public function setSujet($pst_sujet)
	{
		$this->courriel->Subject = $pst_sujet;
	}

	/*
	* Setter Texte
	* @param string $pst_texte texte du message
	*/
	public function setTexte($pst_texte)
	{
		$this->courriel->Body = $pst_texte;
	}

	/*
	* Setter Texte brut
	* @param string $pst_texte texte brut du message
	*/
	public function setTexteBrut($pst_texte)
	{
		$this->courriel->AltBody = $pst_texte;
	}

	/*
	* Envoie le message
	*/
	public function envoie()
	{
		try {
			$this->courriel->send();
			return true;
		} catch (Exception $e) {
			$this->courriel->st_erreur = $this->courriel->ErrorInfo;
			return false;
		}
	}

	/*
	* Sélecteur du message d'erreur
	* @return string message d'erreur
	*/

	public function get_erreur()
	{
		return $this->courriel->st_erreur;
	}
}
