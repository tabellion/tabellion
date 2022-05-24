<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------
function getmicrotime() {
    // découpe le tableau de microsecondes selon les espaces
    list($usec, $sec) = explode(" ",microtime());

    // replace dans l'ordre
    return ((float)$usec + (float)$sec);
}

/*
* Renvoie le temps ecoulé en ms
*/
function temps_ecoule_en_ms()
{
   global $etape_prec;
   return ($etape_prec) ? round((getmicrotime() - $etape_prec)*1000) : 0;
}

/**
* @desc Affiche le temps écoulé (en microsecondes) depuis la dernière étape.
* L'argument $nom_etape permet de spécifier ce qui est mesuré (ex. "page de stats" ou "requête numéro 7")
*/
function benchmark ($nom_etape) 
{
	global $etape_prec;
	$temps_ecoule = temps_ecoule_en_ms();
	$retour = '<div class="text-center row col-md-12">' . $nom_etape . ' : ' . $temps_ecoule . 'ms</div>';
	$etape_prec = getmicrotime();
	return $retour;
}
?>
