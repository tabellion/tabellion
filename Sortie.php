<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------
require_once('Commun/Identification.php');
require_once('Commun/config.php');
unset($_SESSION['ident']);
unset($_SESSION['mdp']);
unset($_SESSION['idf_source']);
unset($_SESSION['idf_commune']);
unset($_SESSION['rayon']);
unset($_SESSION['idf_type_acte']);
unset($_SESSION['annee_min']);
unset($_SESSION['annee_max']);
unset($_SESSION['nom']);
unset($_SESSION['prenom']);
unset($_SESSION['idf_type_presence']);
unset($_SESSION['sexe']);
unset($_SESSION['variantes']);
unset($_SESSION['nom_epx']);
unset($_SESSION['prenom_epx']);
unset($_SESSION['variantes_epx']);
unset($_SESSION['nom_epse']);
unset($_SESSION['prenom_epse']);
unset($_SESSION['variantes_epse']);
unset($_SESSION['tri']);
unset($_SESSION['patronyme']);
unset($_SESSION['mode']);
unset($_SESSION['statut_listadh']);
unset($_SESSION['idf_source_recherche']);
unset($_SESSION['idf_commune_recherche']);
unset($_SESSION['idf_type_acte_recherche']);
unset($_SESSION['num_page_statcom']);
unset($_SESSION['initiale_statcom']);
unset($_SESSION['num_page_patcom']);
unset($_SESSION['initiale_patcom']);
                              
header("Location: $gst_url_sortie ");
?>
