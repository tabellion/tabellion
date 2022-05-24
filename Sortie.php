<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------

// require_once('Commun/Identification.php');
require_once __DIR__ .'/Commun/config.php';

session_start();

session_destroy();
                              
header("Location: $gst_url_sortie ");

