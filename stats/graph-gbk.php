<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------
require_once __DIR__ . '/../app/bootstrap.php';

require_once("/usr/share/jpgraph/jpgraph.php");
require_once("/usr/share/jpgraph/jpgraph_line.php");
require_once("/usr/share/jpgraph/jpgraph_error.php");

$ga_couleurs = array(
    "#FF7F50", "#00008B", "#BDB76B", "#8FBC8F", "#00BFFF", "#FFD700", "#4B0082", "#D3D3D3", "#FFA07A", "#7B68EE", "#6B8E23", "#EEE8AA", "#FFC0CB", "#BC8F8F", "#A0522D", "#708090", "#00FF7F", "#FF6347", "#EE82EE", "#F0F8FF",
    "#FF7F50", "#00008B", "#BDB76B", "#8FBC8F", "#00BFFF", "#FFD700", "#4B0082", "#D3D3D3", "#FFA07A", "#7B68EE", "#6B8E23", "#EEE8AA"
);

$gi_annee = isset($_GET['annee']) ? (int) $_GET['annee'] : 2011;
$gi_idf_canton = isset($_GET['idf_canton']) ? (int) $_GET['idf_canton'] : 1;

$st_requete = "select ca.nom,month(date_demande) as mois, count(*) from stats_gbk sg join commune_acte ca on (sg.idf_commune=ca.idf) join canton c on (ca.idf_canton=c.idf) where c.idf=$gi_idf_canton and year(date_demande)=$gi_annee group by ca.nom, month(date_demande) order by ca.nom, mois";

//die("REQ=$st_requete");

$a_stats = $connexionBD->liste_valeur_par_doubles_clefs($st_requete);
if (count($a_stats) > 0) {
    // Creation du graphique
    $graph = new Graph(1200, 800, 'auto');
    $graph->SetScale("textlin");
    $graph->SetShadow();
    $a_mois = array('Jan', 'Fev', 'Mar', 'Apr', 'Mai', 'Juin', 'Juil', 'Aout', 'Sept', 'Oct', 'Nov', 'Dec');
    $graph->xaxis->SetTickLabels($a_mois);
    $graph->xaxis->SetLabelAngle(90);
    $graph->xaxis->title->Set("Mois");
    $graph->yaxis->title->Set("Nombre de demandes");
    $graph->title->Set("Statistiques par canton $gi_annee");

    $graph->title->SetFont(FF_FONT1, FS_BOLD);
    $graph->yaxis->title->SetFont(FF_FONT1, FS_BOLD);
    $graph->xaxis->title->SetFont(FF_FONT1, FS_BOLD);
    $graph->xaxis->title->Align('center', 'top');
    $graph->xaxis->SetTitleMargin(25);
    $graph->yaxis->SetTitleMargin(60);
    $i = 0;
    $a_series = array();
    foreach ($a_stats as $st_commune => $a_mois_donnees) {
        $a_donnees = array();
        for ($i_mois = 1; $i_mois <= 12; $i_mois++) {
            if (array_key_exists($i_mois, $a_mois_donnees))
                $a_donnees[] = $a_mois_donnees[$i_mois][0];
            else
                $a_donnees[] = null;
        }
        $a_series[$i] = new LinePlot($a_donnees);
        $a_series[$i]->SetColor($ga_couleurs[$i]);
        $graph->Add($a_series[$i]);
        $a_series[$i]->SetLegend($st_commune);
        $i++;
    }
    $graph->legend->Pos(0.85, 0.07, "left", "top");
    $graph->Stroke();
}
