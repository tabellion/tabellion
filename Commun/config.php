<?php


//Serveur ispconfi-02.genea16.net

$gst_serveur_bd  = "localhost";
$gst_utilisateur_bd = "to_complete";
$gst_mdp_utilisateur_bd = "to_complete";
$gst_nom_bd = "to_complete"; 
//$gst_url_site = 'https://'.$_SERVER["SERVER_NAME"];
$gst_url_site = 'https://adherents.genea16.net';
//$gst_url_serveur = 'https://'.$_SERVER["SERVER_NAME"];
//$gst_rep_site = $_SERVER['DOCUMENT_ROOT']."/v4";
$gst_rep_site = $_SERVER['DOCUMENT_ROOT'];
//$gst_url_readhesion = "$gst_url_site/v4/Readhesion/index.php";
$gst_url_readhesion = "$gst_url_site/Readhesion/index.php";
$gst_url_inscription = 'https://www.adherents.genea16.net/Inscription/';
$gst_url_interrogation_geneabank = "https://geneabank.genea16.net/index.php";

$gst_serveur_smtp = 'messagerie.genea16.net';
$gst_utilisateur_smtp = 'agc-dir@genea16.net';
$gst_mdp_smtp ='champbrun';
$gi_port_smtp = 587;

//G�n�aBank
$gst_administrateur_gbk = 'to_complete';
$gst_mdp_administrateur_gbk = 'to_complete';
$gst_url_indexes_geneabank = "$gst_url_site/IndexGeneaBank";
$gst_url_interrogation_geneabank = "https://geneabank.genea16.net/index.php";
$gst_url_reponse_gbk ="https://geneabank.genea16.net/AfficheActes.php";

$gst_repertoire_indexes_geneabank = "$gst_rep_site/IndexGeneaBank";
$gst_index_couple_geneabank = 'gbkcpl.zip';
$gst_index_patros_geneabank = 'gbkpatros.zip';
$gst_compteurs_adherents_geneabank = 'compteurs_adherent_gbk.txt';
$gst_compteurs_communes_geneabank = 'compteurs_communes_gbk.txt';

//commun V4
$gst_url_adhesion = "../Adhesion"; 
$gst_url_validation = "$gst_url_site/Adhesion/ValideInscription.php"; 
//$gst_url_site = "$gst_url_serveur";  
$gst_url_images = "$gst_url_site/images";
$gst_logo_association = "$gst_url_images/LogoAGC.jpg";
$gst_repertoire_telechargement = "$gst_rep_site/Administration/telechargements";
$gst_url_telechargement_actes = $gst_url_site."Administration/telechargements";
$gi_max_taille_upload = 6000000; // Taille maximale d'upload en octets
$gi_nb_max_reponses = 100;
$gst_rep_trombinoscope = "$gst_rep_site/trombinoscope";
$gst_url_trombinoscope = "$gst_url_site/trombinoscope";
$gst_url_sortie = "http://www.genea16.net";
$gst_emails_gestbase = "pascal.frebot@neuf.fr, jeanclaude.mignon@orange.fr, veillon.gensac@free.fr, fbouffanet@yahoo.fr";
$ga_emails_gestbase=array('pascal.frebot@neuf.fr', 'jeanclaude.mignon@orange.fr', 'veillon.gensac@free.fr', 'fbouffanet@yahoo.fr');
$gst_email_agcinfo ='agc-info@genea16.net';

// TD
$gst_rep_photos_td = "$gst_rep_site/TD/photos";
$gst_rep_photos_modifs = "$gst_rep_site/photos";

// Index AD
$gst_repertoire_indexes_AD = "$gst_rep_site/vitrine/IndexAD";
$gst_url_indexes_AD = "https://www.genea16.net/IndexAD";
$gst_rep_logs = "$gst_rep_site/logs";
$gst_time_zone ='Europe/Paris';
$gi_precision_prenom=7;
