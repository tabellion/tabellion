<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------
require_once '../Commun/config.php';
require_once('../Commun/Identification.php');
require_once('../Commun/constantes.php');
require_once('../Commun/VerificationDroits.php');
verifie_privilege(DROIT_UTILITAIRES);
require_once '../Commun/commun.php';
require_once('../Commun/ConnexionBD.php');

$gst_url_cgcp     = "http://www.cgcp.asso.fr/./nos_donnees/manual_index.php?dept=16&page=";

$ga_mois          = array(1 => "Janvier",
                     2 => "F&eacute;vrier",
                     3 => "Mars",
                     4 => "Avril",
                     5 => "Mai",
                     6 => "Juin",
                     7 => "Juillet",
                     8 => "Ao&ucirc;t",
                     9 => "Septembre",
                     10 => "Octobre",
                     11 => "Novembre",
                     12 => "D&eacute;cembre"
);

$ga_nom_commune_cgcp_vers_insee_agc =array(
                      'Aignes-et-Puypéroux'=>160040,
							 'Aizecq'=>162421,
                      'Aubeville' => 160210,
                      'Auge'=>163391,
                      'Aunac'=>160230,
                      'Bayers'=>160330,
                      'Beaulieu' => 161191,
                      'Bignac'=>160430,
                      'Blanzac-Porcheresse' => 160461,
                      'Blanzac-Voulgézac' => 164200,
                      'Brie' =>160610,
							 'Chabanais-Notre-Dame-Grenord' =>160703,
							 'Chabanais-Saint-Pierre' => 160701,
							 'Chabanais-Saint-Sebastien' =>160702,
							 'Chambon'=>163371,
							 'Chantrezac'=>162881,
                      'Chavenat'=>160920,
					  'Chasseneuil-sur-Bonnieure'=>160850,
                      'Chenommet'=>160940,
							 'Cherves-de-Cognac'=>160971,
							 'Confolens-Saint-Barthelemy'=>161061,
							 'Confolens-Saint-Maxime'=>161062,
                      'Conzac' => 163013,
                      'Cressac-Saint-Genis' => 161151,
                      'Crouin'=>161022,
                      'Ebréon'=>161220,
					  'Ébréon'=>161220,
							 'Embourie'=>162531,
                      'Eraville'=>161290,
                      'Fleurignac' =>163791,
					  'Forêt-de-Tessé-La'=> 161420,
							 'Grand-Madieu-Le'=>161570,
                      'Graves' => 162971,
                      'Juillaguet'=>161720,
                      'Lamérac'=>161790,
							 'Laplaud'=>162882,
							 'Lézignac-sur-Goire'=>163372,
							 'Louzac'=>161932,
                      'Louzac-Saint-André' =>161931,
							 'Loubert'=>162883,
                      'Magdeleine'=>161162,
					  'Magdeleine-La'=> 161970,
                      'Magnac'=>161981,
                      'Mainfonds'=>162010,
							 'Messeux'=>162422,
                      'Montmoreau'=>162301,
							 'Moutardon'=>162423,
                      'Nanteuil-en-Vallée'=>162424,
							 'Négrat' => 163221,
							 'Négret'=>163081,
                      'Nonaville'=>162470,
							 'Pallue'=>161502,
							 'Petit-Madieu'=>162884,
					  'Paizay-Naudouin' => 162532,
                      'Péreuil'=>162570,
                      'Peudry'=>163341,
                      'Plaizac'=>162620,
                      'Plassac'=>162631,
							 'Porcheresse'=>160462,
							 'Pougné'=>162425,
							 'Richemont'=>160972,
                      'Roullet'=> 162871,
							 'Roumazières'=>162885,
					  'Roumazières-Loubert'=>162883,	 
                      'Saint-Amant'=>162940,
                      'Saint-Amant-de-Bonnieure'=>162960,
                      'Saint-André'=>161931,
                      'Saint-Aulais' => 163011,
                      'Saint-Christophe' => 160733,
					  //'Saint-Christophe (Chalais)'=>160733,
                      'Saint-Constant' => 163441,
                      'Saint-Cybard'=>160474,
                      'Saint-Estèphe' => 162872,
                      'Saint-Eutrope'=>163140,
                      'Saint-Florent' => 162813,
                      'Saint-Genis-de-Blanzac' => 161152,
							 'Saint-Germain-de-Confolens' => 163220,
							 'Saint-Gervais'=>162426,
                      'Saint-Hilaire' => 160285,
                      'Saint-Laurent-de-Belzagot'=>163280,
                      "Saint-Martial-d'Aubeterre" => 162842,
							 'Saint-Martin-de-Bourianne'=>160091,
                      'Saint-Martin-de-Cognac'=>161023,
							 'Saint-Maurice-des-Lions'=>163370,
                      'Saint-Médard-de-Rouillac'=>163392,
                      'Saint-Paul'=>164051,
                      'Saint-Projet'=>163442,										   
							 'Saint-Quentin-le-Brûlé' => 161821,
                      'Sainte-Colombe'=>163090,
                      'Sainte-Marie'=>160732,
                      'Sérignac'=> 160731,
                      'Sonneville'=>163710,
                      'Suau'=>163750,
                      'Temple'=>162861,
                      'Touzac'=>163860,
                      'Villars'=>161982,
					  'Villiers-le-Roux'=>164130,
                      'Viville'=>164170										   
);


$ga_nb_actes_cgcp=array();
$ga_commune_cgcp=array();
$ga_nai_cgcp=array();
$ga_mar_cgcp=array();
$ga_dec_cgcp=array();
$ga_div_cgcp=array();

$connexionBD = ConnexionBD::singleton($gst_serveur_bd,$gst_utilisateur_bd,$gst_mdp_utilisateur_bd,$gst_nom_bd);

$a_arrdt_cognac =array( 'Baignes-Sainte-Radegonde'=>
array(16025,16053,16079,16105,16179,16224,16276,16380,16384),
'Barbezieux-Saint-Hilaire' =>
array(16014,16028,16030,16040,16062,16074,16160,16176,16177,16178,16224,16301,16303,16338,16342,16360,16405),
'Brossac' =>
array(16048,16066,16091,16099,16161,16251,16256,16315,16331,16354,16357,16365),
'Châteauneuf-sur-Charente'=>
array(16013,16045,16050,16057,16090,16129,16204,16233,16247,16297,16351,16352,16386,16402,16417),
'Cognac-Nord'=>array(16058,16060,16097,16102,16218,16278,16304,16355),
'Cognac-Sud'=>array(16018,16089,16102,16152,16169,16193,16217,16330),
'Jarnac'=>array(16032,16088,16139,16145,16165,16167,16174,16216,16220,16243,16277,16349,16369,16387),
'Rouillac'=>array(16017,16043,16051,16109,16148,16156,16207,16208,16221,16228,16262,16286,16312,16339,16371,16395),
'Segonzac' =>
array(16010,16012,16056,16116,16150,16151,16153,16171,16186,16202,16316,16340,16343,16359,16366,16399)
);

$a_arrdt_angouleme =array(
'Angoulême'=> array(16015),
'Aubeterre-sur-Dronne'=>array(16020,16037,16049,16130,16180,16284,16227,16240,16260,16347,16350),
'Blanzac-Porcheresse'=>array(16021,16036,16041,16046,16072,16075,16101,16115,16133,16175,16201,16236,16257,16258,16263,16265,16319,16332,16420),
'Chalais'=>
array(16029,16034,16063,16073,16112,16117,16215,16222,16252,16279,16302,16346,16333,16367,16424),
'La-Couronne'=>
array(16113,16138,16244,16271,16287,16313,16341,16418),
'Gond-Pontouvre'=>
array(16026,16078,16154,16358),
'Hiersac'=>
array(16019,16077,16121,16123,16163,16187,16234,16298,16320,16348,16370,16388,16415),
'Montbron'=>array(16084,16124,16135,16137,16158,16203,16211,16223,16250,16290,16323,16353,16372,16421),
'Montmoreau-Saint-Cybard'=>array(16004,16052,16111,16118,16170,16230,16246,16254,16267,16294,16314,16328,16334,16362),
'La Rochefoucauld' => array(16003,16061,16067,16093,16107,16168,16209,16269,16274,16280,16281,16282,16344,16379,16406,16425),
'Ruelle-sur-Touvre' =>array(16166,16199,16232,16291,16385),
'St-Amant-de-Boixe' => array(16008,16011,16024,16081,16108,16200,16210,16226,16241,16295,16383,16393,16401,16412,16419,16423),
'Soyaux' => array(16055,16120,16146,16374,16422),
'Villebois-Lavalette'=> array(16119,16047,16082,16092,16103,16125,16143,16147,16162,16172,16198,16283,16285,16368,16382,16394,16408)
);

$a_arrdt_confolens =array(
'Aigre' =>array(16005,16027,16042,16083,16122,16144,16155,16185,16194,16248,16275,16317,16390,16397,16411),
'Chabanais' => array(16070,16071,16086,16100,16132,16134,16259,16270,16345,16363,16376),
'Champagne-Mouton'=>array(16007,16038,16054,16076,16087,16310,16389,16403),
'Confolens-Nord'=>array(16009,16016,16106,16128,16164,16181,16205,16264),
//'Confolens-Sud'=>array(16001,16055,16064,16131,16182,16337,16231,16322,16249,16306,16322),
'Confolens-Sud'=>array(16001,16064,16131,16182,16231,16322,16249,16306,16322,16337),
'Mansle' => array(16023,16033,16068,16069,16094,16095,16140,16141,16173,16184,16191,16196,16206,16237,16238,16272,16296,16300,16307,16309,16318,16326,16377,16392,16396,16414),
'Montemboeuf' => array(16096,16183,16188,16212,16213,16225,16239,16289,16293,16364,16398,16416),
'Ruffec' => array(16002,16031,16044,16104,16114,16235,16242,16268,16292,16321,16325,16356,16378,16400,16404,16410),
'St-Claud' => array(16035,16192,16085,16149,16157,16195,16214,16245,16255,16261,16288,16308,16329,16336,16375),
'Villefagnan' => array(16039,16059,16098,16110,16127,16136,16142,16189,16190,16197,16229,16253,16273,16335,16361,16373,16381,16391,16409,16413)
);

$a_lignes_cgcp = array();
$a_resultat_cgcp = array();

$gst_mode = empty($_POST['mode']) ? 'FORMULAIRE': $_POST['mode'] ;

if ($gst_mode=='COMPARAISON')
{
          $st_fich_dest = tempnam($gst_repertoire_telechargement,"index_CGCP");
       if (!move_uploaded_file($_FILES['IndexCGCP']['tmp_name'],$st_fich_dest)) 
       {
          print("<div class=\"alert alert-danger\">Erreur de t&eacute;l&eacute;chargement :</div>");
          switch($_FILES['IndexCGCP']['error'])
          { 
              case 2 : print("Fichier trop gros par rapport &agrave; MAX_FILE_SIZE");break;
              default : print("Erreur inconnue");print_r($_FILES);
          }
          //exit;
       }
       extrait_donnes_cgcp($st_fich_dest);

       $st_requete = "select concat(ca.code_insee,ca.numero_paroisse),ca.nom,CONCAT(sc.annee_min,'-',sc.annee_max) from  `commune_acte` ca left join `stats_commune` sc on (sc.idf_commune=ca.idf and sc.idf_source=1 and sc.idf_type_acte=1)";
       $ga_stats_mar_agc=$connexionBD->sql_select_multiple_par_idf($st_requete);
       $st_requete = "select concat(ca.code_insee,ca.numero_paroisse),ca.nom,CONCAT(sc.annee_min,'-',sc.annee_max) from  `commune_acte` ca left join `stats_commune` sc on (sc.idf_commune=ca.idf and sc.idf_source=1 and sc.idf_type_acte=3)";
       $ga_stats_nai_agc=$connexionBD->sql_select_multiple_par_idf($st_requete);
       $st_requete = "select concat(ca.code_insee,ca.numero_paroisse),ca.nom,CONCAT(sc.annee_min,'-',sc.annee_max) from  `commune_acte` ca left join `stats_commune` sc on (sc.idf_commune=ca.idf and sc.idf_source=1 and sc.idf_type_acte not in (1,3,4))";
       $ga_stats_div_agc=$connexionBD->sql_select_multiple_par_idf($st_requete);
       
      $st_requete = "select concat(ca.code_insee,ca.numero_paroisse),ca.nom,CONCAT(sc.annee_min,'-',sc.annee_max) from  `commune_acte` ca left join `stats_commune` sc   on (sc.idf_commune=ca.idf and sc.idf_source=1 and sc.idf_type_acte=4)";
      $ga_stats_dec_agc=$connexionBD->sql_select_multiple_par_idf($st_requete);
      $st_requete = "select concat(ca.code_insee,ca.numero_paroisse),ca.nom, sum(sc.nb_actes) from  `commune_acte` ca left join `stats_commune` sc   on (sc.idf_commune=ca.idf) group by ca.code_insee,ca.numero_paroisse";
      $ga_nb_actes_agc=$connexionBD->sql_select_multiple_par_idf($st_requete);
      $st_requete = "select concat(ca.code_insee,ca.numero_paroisse),ca.nom, sc.nb_actes from  `commune_acte` ca left join `stats_commune` sc   on (sc.idf_commune=ca.idf and sc.idf_source=1 and idf_type_acte=2)";
      $ga_stats_cm_agc=$connexionBD->sql_select_multiple_par_idf($st_requete);
      
      
      $st_page= "<div align=center>      <h1>        
        <font color=\"#0066FF\">Comparatif des d&eacute;pouillements filiatifs entre l'AGC et le CGCP           
          <br>    pour le d&eacute;partement de la Charente         
        </font></h1>  <h2>        
        <font color=\"#009999\">";
      $a_date = localtime(time());        
      $st_page.= sprintf("%d %s %4.4d",$a_date[3],$ga_mois[$a_date[4]+1],$a_date[5]+1900);
      $st_page .="        
        </font></h2>         
      <p>&nbsp;       
      </p>
<h3 align=\"left\">        
        <font color=\"#993333\">Quelques pr&eacute;cisions :         
        </font></h3>         
      <p align=\"left\">Ces donn&eacute;es sont issues des listes publiques des d&eacute;pouillements effectu&eacute;s par les deux associations et ne portent que sur les donn&eacute;es      filiatives:       
      </p>         
      <p align=\"left\"><a href=\"http://adherents.genea16.net/AfficheStatsCommune.php\">http://adherents.genea16.net/AfficheStatsCommune.php</a>         
        <br>    <a href=\"http://www.cgcp.asso.fr/nos_donnees/manual_index.php\">http://www.cgcp.asso.fr/nos_donnees/manual_index.php</a>       
      </p>         
      <p align=\"left\">Le CGCP int&eacute;gre quelques CM dans ses divers filiatifs alors que l'AGC pr&eacute;sente les CM dans une cat&eacute;gorie &agrave; part enti&egrave;re.               
      </p>
      <p align=\"left\">Les table d&eacute;c&eacute;nnales de mariage de l'AGC sont consultables librement sur son site. Celles du CGCP sont r&eacute;serv&eacute;es aux adhérents (relev&eacute;s non filiatifs)              
      </p>          
      <p align=\"left\">La liste des communes de référence est la liste du CGCP pour la Charente.       
        <br>Pour chaque commune du CGCP, une tentative de correspondance avec une commune de l'AGC est faite.       
        <br>Par contre, si l'AGC a effectué le dépouillement d'une commune qui n'est pas connue du CGCP, celle-ci ne figure pas dans le comparatif (Exemple: ANGOULEME (St Martial))        
        <br>Les fourchettes affich&eacute;es correspondent aux ann&eacutees de début et de fin. Consulter les d&eacute;pouillements de chaque site pour voir les &eacute;ventuels trous
      </p>
      <p align=\"left\">
      Une liste des dépouillements par canton est également disponible aux liens ci-dessous :
      </p>";
       
    //Angouleme
    $st_page .="<div align=\"left\">";
    $i=0;
    $st_page .="<h2 align=center><font color=coral>Arrondissement d'Angoulême</font></h2>";
    $st_page .= "<ul>";
    foreach ($a_arrdt_angouleme as $st_nom => $a_insee)
    {
      $st_page .="<li><a href=\"#angouleme_$i\">Canton de $st_nom</a></li>";
      $i++;
    }
    $st_page .="</ul></div>";
    $st_page .="<hr>";

   $st_page .="<h2 align=center><font color=coral>Arrondissement de Cognac</font></h2>";
   //Cognac
   $st_page .="<div align=\"left\">";
   $i=0;
   $st_page .="<ul>";
   foreach ($a_arrdt_cognac as $st_nom => $a_insee)
   {
     $st_page .="<li><a href=\"#cognac_$i\">Canton de $st_nom</a></li>";
     $i++;
   }
   $st_page .="</ul></div>";
   $st_page .="<hr>";

   $st_page .='<h2 align=center><font color=coral>Arrondissement de Confolens (zone de "prédilection" du CGCP)</font></h2>';
   //Confolens
   $st_page .="<div align=\"left\">";
   $i=0;
   $st_page .="<ul>";
   foreach ($a_arrdt_confolens as $st_nom => $a_insee)
   {
      $st_page .="<li><a href=\"#confolens_$i\">Canton de $st_nom </a></li>";
      $i++;
   }
   $st_page .="<hr>";
   $st_page .="           
      <table width=\"100%\" border=\"1\">             
        <tr>                 
          <td bgcolor=\"#C2CF68\" width=\"5%\">&nbsp;</td>                 
          <td width=\"95%\">Signifie qu'aucun relev&eacute; n'est disponible dans la cat&eacute;gorie &eacute;tudi&eacute;e (Naissances, Mariages, D&eacute;c&egrave;s) pour l'AGC alors que le CGCP dispose de d&eacute;pouillement sur la commune dans la m&ecirc;me cat&eacute;gorie</td>             
        </tr>             
        <tr>                 
          <td bgcolor=\"#9999FF\" width=\"5%\">&nbsp;</td>                 
          <td width=\"95%\">Signifie qu'aucun relev&eacute; n'est disponible dans la cat&eacute;gorie &eacute;tudi&eacute;e (Naissances, Mariages, D&eacute;c&egrave;s) pour le CGCP alors que l'AGC dispose de d&eacute;pouillement sur la commune dans la m&ecirc;me cat&eacute;gorie </td>             
        </tr>         
      </table>
      <br>      
      <br>"; 
   
   $st_page .='<div align=center>';
   $a_insee_dept=array_keys($ga_nb_actes_cgcp);
   $st_page .= tableau_comparaison($a_insee_dept);

   $st_page .="<h2 align=center><font color=coral>Arrondissement d'Angoulême </font></h3>";
   $i=0;
   foreach ($a_arrdt_angouleme as $st_canton => $a_insee)
   {
      $st_page .="<h3 align=center><font color=blue>Canton de <a name=\"angouleme_$i\">$st_canton</a></font></h3>";
      $st_page .= tableau_comparaison($a_insee);
      $i++;
   }
   $st_page .='<hr>';

   $st_page .="<h2 align=center><font color=coral>Arrondissement de Cognac </font></h3>";
   $i=0;
   foreach ($a_arrdt_cognac as $st_canton => $a_insee)
   {
      $st_page .="<h3 align=center><font color=blue>Canton de <a name=\"cognac_$i\">$st_canton</a></font></h3>";
      $st_page .=  tableau_comparaison($a_insee);
      $i++;
   }
   $st_page .='<hr>';

   $st_page .='<h2 align=center><font color=coral>Arrondissement de Confolens (Zone de "prédilection" du CGCP)</font></h3>';
   $i=0;
   foreach ($a_arrdt_confolens as $st_canton => $a_insee)
   {
      $st_page .="<h3 align=center><font color=blue>Canton de <a name=\"confolens_$i\">$st_canton</a></font></h3>";
      $st_page .=  tableau_comparaison($a_insee);
      $i++;
   }
   /*header("Content-type: text/csv");
   header("Expires: 0");
   header("Pragma: public");
   header("Content-disposition: attachment; filename=\"Comparatif_releves_AGC_CGCP.htm\"");
   */
   
}





function extrait_donnes_cgcp($pst_fichier)
{
   global $ga_nb_actes_cgcp,$ga_commune_cgcp,$ga_nai_cgcp,$ga_mar_cgcp,$ga_dec_cgcp,$ga_div_cgcp;
   if (($pf = fopen($pst_fichier, "r")) !== FALSE)
   {
      while ((list($i_insee,$st_commune,$st_type,$st_filiatif,$i_debut,$i_fin,$i_nb_actes) = fgetcsv($pf, 1000, ";")) !== FALSE) {
		  
         $ga_nb_actes_cgcp[$i_insee][$st_commune] = isset($ga_nb_actes_cgcp[$i_insee][$st_commune]) ? $ga_nb_actes_cgcp[$i_insee][$st_commune] + $i_nb_actes : $i_nb_actes;
         if (strtoupper($st_filiatif)!='N')
         {
			if ($i_debut==0 || $i_fin==0 )
				continue;
		    if ($st_type=='N')
            {
              if (isset($ga_nai_cgcp[$i_insee][$st_commune]))
                $ga_nai_cgcp[$i_insee][$st_commune].="<br>$i_debut-$i_fin";
              else
                $ga_nai_cgcp[$i_insee][$st_commune]="$i_debut-$i_fin";
            }
            if ($st_type=='M')
            {
              if (isset($ga_mar_cgcp[$i_insee][$st_commune]))
                $ga_mar_cgcp[$i_insee][$st_commune].="<br>$i_debut-$i_fin";
              else
                $ga_mar_cgcp[$i_insee][$st_commune]="$i_debut-$i_fin";
            }
            if ($st_type=='D')
            {
              if (isset($ga_dec_cgcp[$i_insee][$st_commune]))
                $ga_dec_cgcp[$i_insee][$st_commune].="<br>$i_debut-$i_fin";
              else
                $ga_dec_cgcp[$i_insee][$st_commune]="$i_debut-$i_fin";
            }
            if ($st_type=='V')
            {
              if (isset($ga_div_cgcp[$i_insee][$st_commune]))
                $ga_div_cgcp[$i_insee][$st_commune].="<br>$i_debut-$i_fin";
              else
                $ga_div_cgcp[$i_insee][$st_commune]="$i_debut-$i_fin";
            }
         }
      }                 
    }
    fclose($pf);

}

function td_comparaison($pst_val_agc,$pst_val_cgcp)
{
   $pst_val_cgcp = str_replace('|','<br>',$pst_val_cgcp);
   if ($pst_val_agc == "" && $pst_val_cgcp != "" && $pst_val_cgcp != "N/A")
      return sprintf("<td >&nbsp;</td><td bgcolor=\"#C2CF68\">%s</td>",$pst_val_cgcp);
   if ( $pst_val_agc != "" && $pst_val_cgcp == "")
      return sprintf("<td bgcolor=\"#9999FF\">%s</td><td>&nbsp;</td>",$pst_val_agc);
   if ($pst_val_cgcp== "") $pst_val_cgcp ="&nbsp;";
   if ($pst_val_agc== "") $pst_val_agc ="&nbsp;";
   return sprintf("<td>%s</td><td>%s</td>",$pst_val_agc,$pst_val_cgcp);  
}

function tableau_comparaison($pa_insee)
{
	global $ga_stats_nai_agc,$ga_stats_mar_agc,$ga_stats_dec_agc,$ga_stats_div_agc,$a_resultat_cgcp,$ga_nb_actes_agc,$ga_stats_cm_agc,$ga_nb_actes_cgcp,$ga_commune_cgcp,$ga_nai_cgcp,$ga_mar_cgcp,$ga_dec_cgcp,$ga_div_cgcp,$ga_nom_commune_cgcp_vers_insee_agc;
  
  $st_tableau = '<div align=center><table border=1>';
	$st_tableau .='<tr bgcolor="lightcyan">';
	$st_tableau .='<th rowspan=2>Code Insee</th><th rowspan=2>Commune</th><th colspan=2>Naissances</th><th colspan=2>Mariages filiatifs</th><th colspan=2>Divers filiatifs</th><th colspan=2>Décès</th><th rowspan=2>Nb CM AGC</th><th colspan=2>Nb total<br>d\'actes</th>';
	$st_tableau .='</tr><tr>';
	$st_tableau .='<th bgcolor="#9999FF">AGC</th><th bgcolor="#C2CF68">CGCP</th><th bgcolor="#9999FF">AGC</th><th bgcolor="#C2CF68">CGCP</th><th bgcolor="#9999FF">AGC</th><th bgcolor="#C2CF68">CGCP</th><th bgcolor="#9999FF">AGC</th><th bgcolor="#C2CF68">CGCP</th><th bgcolor="#9999FF">AGC</th><th bgcolor="#C2CF68">CGCP</th>';
	$st_tableau .='</tr>';
	$st_tableau .="\n";
	$i_tot_canton_agc = 0;
	$i_tot_canton_cgcp = 0;
	$a_paroisses = array();
	foreach ($pa_insee as $i_insee)
	{    
		if (isset($ga_nb_actes_cgcp[$i_insee]))
		{
			foreach ($ga_nb_actes_cgcp[$i_insee] as $st_commune_cgcp => $i_nb_actes_cgcp)
			{
				$i_insee_agc = array_key_exists($st_commune_cgcp,$ga_nom_commune_cgcp_vers_insee_agc) ? $ga_nom_commune_cgcp_vers_insee_agc[$st_commune_cgcp] : sprintf("%s%d",$i_insee,0);
				$st_commune_agc = array_key_exists($i_insee_agc,$ga_stats_mar_agc) ? $ga_stats_mar_agc[$i_insee_agc][0]: '';
				$st_tableau .= '<tr>';
				$st_tableau .= sprintf("<td>%05d</td>",$i_insee);
				if (strtoupper($st_commune_agc)!=strtoupper($st_commune_cgcp))
					$st_tableau .= sprintf("<td>%s<br>ou %s</td>",$st_commune_agc,$st_commune_cgcp);
				else
					$st_tableau .= sprintf("<td>%s</td>",$st_commune_cgcp);
				$i_nai_agc = array_key_exists($i_insee_agc,$ga_stats_nai_agc) ? $ga_stats_nai_agc[$i_insee_agc][1] : 0;
				$i_mar_agc = array_key_exists($i_insee_agc,$ga_stats_mar_agc) ? $ga_stats_mar_agc[$i_insee_agc][1] : 0;
				$i_dec_agc = array_key_exists($i_insee_agc,$ga_stats_dec_agc) ? $ga_stats_dec_agc[$i_insee_agc][1] : 0;
				$i_div_agc= array_key_exists($i_insee_agc,$ga_stats_div_agc) ? $ga_stats_div_agc[$i_insee_agc][1] : 0;
				$i_nb_actes_agc= array_key_exists($i_insee_agc,$ga_nb_actes_agc) ? $ga_nb_actes_agc[$i_insee_agc][1] : 0;
				$i_tot_canton_agc+=$i_nb_actes_agc;
				$i_nb_cm_agc= array_key_exists($i_insee_agc,$ga_stats_cm_agc) ? $ga_stats_cm_agc[$i_insee_agc][1] : '';
        
				$i_nai_cgcp = isset($ga_nai_cgcp[$i_insee][$st_commune_cgcp]) ? $ga_nai_cgcp[$i_insee][$st_commune_cgcp] : '';
				$i_mar_cgcp = isset($ga_mar_cgcp[$i_insee][$st_commune_cgcp]) ? $ga_mar_cgcp[$i_insee][$st_commune_cgcp] : '';
				$i_div_cgcp = isset($ga_div_cgcp[$i_insee][$st_commune_cgcp]) ?$ga_div_cgcp[$i_insee][$st_commune_cgcp] : '';
				$i_dec_cgcp = isset($ga_dec_cgcp[$i_insee][$st_commune_cgcp]) ?$ga_dec_cgcp[$i_insee][$st_commune_cgcp] : '';
				$i_tot_canton_cgcp +=$i_nb_actes_cgcp ;
				$st_tableau .= td_comparaison($i_nai_agc,$i_nai_cgcp);
				$st_tableau .= td_comparaison($i_mar_agc,$i_mar_cgcp);
				$st_tableau .= td_comparaison($i_div_agc,$i_div_cgcp);
				$st_tableau .= td_comparaison($i_dec_agc,$i_dec_cgcp);
				$st_tableau .= "<td>$i_nb_cm_agc</td>";
				$st_tableau .= "<td>$i_nb_actes_agc</td>";
				$st_tableau .= "<td>$i_nb_actes_cgcp</td>";
				$st_tableau .= "</tr>\n";
        	
			}
		}
	}
  $st_tableau .="<tr><th>Total</th><td colspan=10>&nbsp;</td><td>$i_tot_canton_agc</td><td>$i_tot_canton_cgcp</td></tr>";
	$st_tableau .= '</table></div><br>';
  return $st_tableau;
}

/**
 * Affiche le menu de selection
 * @global $gi_max_taille_upload Maximum de la taille  
 */
function affiche_menu() {
   global $gi_max_taille_upload;
   print('<div class="panel panel-primary">');
   print('<div class="panel-heading">Chargement de l\'inventaire du CGCP</div>');
   print('<div class="panel-body">');
   print("<form enctype=\"multipart/form-data\"  method=\"post\" >");
   print("<input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"$gi_max_taille_upload\">"); 
   print('<input type="hidden" name="mode" value="COMPARAISON" >');
   print('<div class="custom-file">');
   print('<label for="IndexCGCP" class="custom-file-label">Fichier <span class="alert alert-danger">CSV</span> des relev&eacute;s CGCP:</label>');
   print('<input name="IndexCGCP" id="IndexCGCP" type="file" class="custom-file-input">');
   print('</div>');
   print('<div class="form-group col-md-4 col-md-offset-4"><button type="submit" class="btn btn-primary">Compare les d&eacute;pouillements</button></div>'); 
   print('</form>');
   print('</div></div>');
} 

/******************************************************************************/
/*                         Corps du programme                                 */
/******************************************************************************/
print('<!DOCTYPE html>');
print("<head>");
print('<meta http-equiv="Content-Type" content="text/html; charset=windows-1252" >');
print('<meta http-equiv="content-language" content="fr">');
print("<title>Comparaison des depouillements en Charente</title>");
print('<meta name="viewport" content="width=device-width, initial-scale=1.0">');
print("<link href='../css/styles.css' type='text/css' rel='stylesheet'>");
print("<link href='../css/bootstrap.min.css' rel='stylesheet'>");
print("<script src='../js/jquery-min.js' type='text/javascript'></script>");
print("<script src='../js/bootstrap.min.js' type='text/javascript'></script>");
print('</head>');
print('<body>');
print('<div class="container">');
require_once("../Commun/menu.php");
switch($gst_mode)
{
   case 'FORMULAIRE' :       
      affiche_menu();
   break;     
   case 'COMPARAISON' :      
      print($st_page);
      $fh = @fopen('Comparatif_releves_AGC_CGCP.htm', 'w' );
      fwrite($fh,'<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0//EN"><html>');
      fwrite($fh,"<head>");
      fwrite($fh,'<meta http-equiv="Content-Type" content="text/html; charset=windows-1252" >');
      fwrite($fh,'<meta http-equiv="content-language" content="fr">');
      fwrite($fh,"<title>Comparaison des depouillements en Charente</title>");
      fwrite($fh,"<link href='Styles.css' type='text/css' rel='stylesheet'>");
      fwrite($fh,'</head>');
      fwrite($fh,'<body bgcolor="gainsboro">');  
      fwrite($fh,$st_page);
      fwrite($fh,'</body>');
      fwrite($fh,'</html>');
      fclose($fh);
      print("<div class=\"text-center\"><a href=\"./Comparatif_releves_AGC_CGCP.htm\">Fichier &agrave; t&eacute;l&eacute;charger</a></div>");        
}

print('</div></body>');
print('</html>');
?>