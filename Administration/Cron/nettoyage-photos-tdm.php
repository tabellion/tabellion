<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------
// repertoire a nettoyer
$gst_repertoire = $_SERVER['DOCUMENT_ROOT'] . '/TD/photos';
$gi_delai_suppression = 14 * 86400; // en secondes

$repertoire = opendir($gst_repertoire);

while (false !== ($st_nom_fichier = readdir($repertoire))) {
    $st_fichier = "$gst_repertoire/$st_nom_fichier";
    $i_age_fichier = time() - filemtime($st_fichier);
    if ($st_nom_fichier != ".." and $st_nom_fichier != "." and !is_dir($st_fichier) and $i_age_fichier > $gi_delai_suppression)
        unlink($st_fichier);
    //print("$st_fichier: $i_age_fichier s<br>");   
}
