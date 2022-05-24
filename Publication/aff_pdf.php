<?php
require_once '../Commun/config.php'; 
require_once '../Commun/constantes.php';
require_once('../Commun/Identification.php');
require_once('../Commun/VerificationDroits.php');
verifie_privilege(DROIT_PUBLICATION);
require_once '../Commun/ConnexionBD.php';
require_once '../Commun/commun.php';
require_once '../Publication/fpdf/fpdf.php';

ob_start();// Enclenche la temporisation de sortie

$connexionBD = ConnexionBD::singleton($gst_serveur_bd,$gst_utilisateur_bd,$gst_mdp_utilisateur_bd,$gst_nom_bd);


$copy="L’achat des tables ne donne pas droit à copie ou reproduction.
Toute reproduction ou représentation intégrale, ou partielle, par quelque procédé que ce soit, des pages publiées dans la présente
publication, faite sans le consentement de l’A.G. C. 16, est illicite et constitue une contrefaçon.
Art. L. 122-4 et 5 L. 335-2 & s. du Code de la propriété intellectuelle.";
$today = date("M-y"); 
$message1 =  isset($_POST['message']) ? $_POST['message']: '';
$message = iconv('UTF-8', 'windows-1252', $message1);
$TypeActe1 =  isset($_POST['TypeActe']) ? $_POST['TypeActe'] : '' ;
$TypeActe = iconv('UTF-8', 'windows-1252', $TypeActe1);


function Mois_Annee ()  // PL 23/04/2014  Function pour affichage du mois en français
{
   $mois = array('', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre');
   $mois_numero = date("n");   // ou $mois_numero = date("n");    m donne 01 à 12, n donne 1 à 12
   $mois_complet = $mois[$mois_numero];

   $annee = date("Y");
   return $mois_complet." ".$annee;
}


function charge_csv(){

   $gst_repertoire_publication = $_SERVER['DOCUMENT_ROOT'].'/v4/Publication/telechargements';
   $st_export_nimv3 ="$gst_repertoire_publication/ExportNimV3.csv";

  $sqlcsv ="CREATE TEMPORARY TABLE IF NOT EXISTS tmp_publication (
  data0 text COLLATE latin1_general_ci NOT NULL,
  data1 text COLLATE latin1_general_ci NOT NULL,
  data2 text COLLATE latin1_general_ci NOT NULL,
  data3 text COLLATE latin1_general_ci NOT NULL,
  data4 text COLLATE latin1_general_ci NOT NULL,
  data5 text COLLATE latin1_general_ci NOT NULL,
  data6 text COLLATE latin1_general_ci NOT NULL,
  data7 text COLLATE latin1_general_ci NOT NULL,
  data8 text COLLATE latin1_general_ci NOT NULL,
  data9 text COLLATE latin1_general_ci NOT NULL,
  data10 text COLLATE latin1_general_ci NOT NULL,
  data11 text COLLATE latin1_general_ci NOT NULL,
  data12 text COLLATE latin1_general_ci NOT NULL,
  data13 text COLLATE latin1_general_ci NOT NULL,
  data14 text COLLATE latin1_general_ci NOT NULL,
  data15 text COLLATE latin1_general_ci NOT NULL,
  data16 text COLLATE latin1_general_ci NOT NULL,
  data17 text COLLATE latin1_general_ci NOT NULL,
  data18 text COLLATE latin1_general_ci NOT NULL,
  data19 text COLLATE latin1_general_ci NOT NULL,
  data20 text COLLATE latin1_general_ci NOT NULL,
  data21 text COLLATE latin1_general_ci NOT NULL,
  data22 text COLLATE latin1_general_ci NOT NULL,
  data23 text COLLATE latin1_general_ci NOT NULL,
  data24 text COLLATE latin1_general_ci NOT NULL,
  data25 text COLLATE latin1_general_ci NOT NULL,
  data26 text COLLATE latin1_general_ci NOT NULL,
  data27 text COLLATE latin1_general_ci NOT NULL,
  data28 text COLLATE latin1_general_ci NOT NULL,
  data29 text COLLATE latin1_general_ci NOT NULL,
  data30 text COLLATE latin1_general_ci NOT NULL,
  data31 text COLLATE latin1_general_ci NOT NULL,
  data32 text COLLATE latin1_general_ci NOT NULL,
  data33 text COLLATE latin1_general_ci NOT NULL,
  data34 text COLLATE latin1_general_ci NOT NULL,
  data35 text COLLATE latin1_general_ci NOT NULL,
  data36 text COLLATE latin1_general_ci NOT NULL,
  data37 text COLLATE latin1_general_ci NOT NULL,
  data38 text COLLATE latin1_general_ci NOT NULL,
  data39 text COLLATE latin1_general_ci NOT NULL,
  data40 text COLLATE latin1_general_ci NOT NULL,
  data41 text COLLATE latin1_general_ci NOT NULL,
  data42 text COLLATE latin1_general_ci NOT NULL,
  data43 text COLLATE latin1_general_ci NOT NULL,
  data44 text COLLATE latin1_general_ci NOT NULL,
  data45 text COLLATE latin1_general_ci NOT NULL,
  data46 text COLLATE latin1_general_ci NOT NULL,
  data47 text COLLATE latin1_general_ci NOT NULL,
  data48 text COLLATE latin1_general_ci NOT NULL,
  data49 text COLLATE latin1_general_ci NOT NULL,
  data50 text COLLATE latin1_general_ci NOT NULL,
  data51 text COLLATE latin1_general_ci NOT NULL,
  data52 text COLLATE latin1_general_ci NOT NULL,
  data53 text COLLATE latin1_general_ci NOT NULL,
  data54 text COLLATE latin1_general_ci NOT NULL,
  data55 text COLLATE latin1_general_ci NOT NULL,
  data56 text COLLATE latin1_general_ci NOT NULL,
  data57 text COLLATE latin1_general_ci NOT NULL,
  data58 text COLLATE latin1_general_ci NOT NULL,
  data59 text COLLATE latin1_general_ci NOT NULL,
  data60 text COLLATE latin1_general_ci NOT NULL,
  data61 text COLLATE latin1_general_ci NOT NULL,
  data62 text COLLATE latin1_general_ci NOT NULL,
  data63 text COLLATE latin1_general_ci NOT NULL,
  data64 text COLLATE latin1_general_ci NOT NULL,
  data65 text COLLATE latin1_general_ci NOT NULL,
  data66 text COLLATE latin1_general_ci NOT NULL,
  data67 text COLLATE latin1_general_ci NOT NULL,
  data68 text COLLATE latin1_general_ci NOT NULL,
  data69 text COLLATE latin1_general_ci NOT NULL,
  data70 text COLLATE latin1_general_ci NOT NULL
) ENGINE=CSV DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci";
global $connexionBD; 
$connexionBD->execute_requete($sqlcsv);

	$st_tmp_file = $_SERVER['DOCUMENT_ROOT'].'/v4/Publication/tmp/publication.txt';
  
   if (!copy($st_export_nimv3, $st_tmp_file))
       die("Impossible de copier $st_export_nimv3 en $st_tmp_file\n");

  $connexionBD->execute_requete("LOAD DATA INFILE '$st_tmp_file'  REPLACE INTO TABLE tmp_publication CHARACTER SET latin1 FIELDS TERMINATED BY ';' LINES TERMINATED BY '\n'");// Je recharge la table publication avec le CSV
  print 'fichier chargé';
   unlink($st_tmp_file);

       }//Fin charge_csv

class PDF extends FPDF
{
// En-tête
function Header()
  {
   if($this->PageNo()==1)
    {
        //Première page
        $this->Image('./img/logo1.jpg',20,12,150);// Logo
        $this->Ln(40);
    }

    else
    {
        //Pages suivantes
        global $titreHP;
        $this->SetFillColor(220,220,220);
        $this->SetFont('Times','I',8);// Police Times italique 8
		$this->Cell(0,5,$titreHP,0,1,'C',true);// Titre de la publication
    }


  }//Header()
//======================================
// Pied de page
function Footer()
  {
   if($this->PageNo()==1)
    { //Première page
    }
    else
    {   //Pages suivantes
    $this->SetY(-15);// Positionnement à 1,5 cm du bas
    $this->SetFont('Times','I',8);// Police Times italique 8

// PL 23/04/2014 remplacement affichage $today par appel la function Mois_Annee pour mois en français
//	$today = date("M-y");
   $today = Mois_Annee();
    $titreBP = "©".$today." Association Généalogique de la Charente  - Page ";
    $this->Cell(0,10,$titreBP.$this->PageNo().'/{nb}',0,0,'C');// Numero de page
    }
  }//Footer()
} //Fin de class

//Deb============================================
charge_csv();//charge le fichier en table

// Rajout PL *************************************************
// On récupére les données date et nb d'actes dans le fichier txt
    $gst_repertoire_publication = $_SERVER['DOCUMENT_ROOT'].'/v4/Publication/telechargements';
    $st_export_annee ="$gst_repertoire_publication/ExportAnnee.txt";
	$pa = fopen($st_export_annee, "r");
	$buffer = fgets($pa);
	list ($datemini,$datemaxi,$nbractes) = explode(";", $buffer);
	fclose($pa);
//echo $datemini."-".$datemaxi."-".$nbractes;
//************************************************************
//Analyse du fichier




$data = array();
$sql = "SELECT * FROM tmp_publication LIMIT 0, 1";//Lecture de la premiere ligne
$req=$connexionBD->execute_requete($sql);
$data=$connexionBD->ligne_suivante_resultat($req);

 //echo $data[5]["commune"];

//=============================================================================
$commune = $data[2];// nom de la commune
$chaine = $data[1];// N° de la commune
$titreN = substr ($chaine, strlen ($chaine) - 3);// N° de la commune
$type_actes_nimegue = $data[5];// Type Acte (B,D,M,V)
$image = "./img/image".$data[5].".jpg";//Titre
$image1 = "./img/image1".$data[5].".jpg";//Titre

 switch ($type_actes_nimegue)
{
    case "N": //selection sur les naissances
	$titre = "Baptêmes Naissances";
	//$titreHP = $titre." de ".utf8_encode($commune);
    $titreHP = $titre." de ".$commune;
	$pdf->titrehp = $titreHP;
	$titre3 = "Par ordre alphabétique";
	$sql = "SELECT * FROM tmp_publication ORDER BY `tmp_publication`.`data10` ASC";// tri sur le patronyme
	break;

	case "D"://selection sur les décès
	$titre = "Décès Sépulture";
	$titreHP = $titre." de ".$commune;
	$sql = "SELECT * FROM tmp_publication ORDER BY `tmp_publication`.`data10` ASC";// tri sur le patronyme
	$titre3 = "Par ordre alphabétique";
	break;

	case "M"://selection sur les mariagees
	$titre = "Mariages";
	$titreHP = $titre." de ".$commune;
	$sql = "SELECT * FROM tmp_publication ORDER BY `tmp_publication`.`data10` ASC";// tri sur le patronyme
	$titre3 = "Par ordre alphabétique";
	break;

	case "V"://selection sur les divers
	$titre = $TypeActe;
	$titreHP = $titre." de ".$commune;
	$sql = "SELECT * FROM tmp_publication ORDER BY `tmp_publication`.`data12` ASC";// tri sur le patronyme
	$titre3 = "Par ordre alphabétique";
	break;
	
	case "R"://selection sur les divers
	$titre = "Recensements";
	$titreHP = $titre." de ".$commune;
	$sql = "SELECT * FROM tmp_publication ORDER BY `data6`,`data7`,`data10`,`data11` ASC";// tri sur le année page maison ménage
	$titre3 = "Par année, maison,ménage";
	break;
	
}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Times','',8);
$pdf->SetMargins(20,5);

$req=$connexionBD->execute_requete($sql);
$ligne = 1; // compteur de ligne
while ($data=$connexionBD->ligne_suivante_resultat($req))
   {
      $ligne ++;
      if ($ligne == 2)
   	   {
	       $pdf->SetFont('Times','B',36);// Police Times gras 20
   	       $pdf->Cell(0,10,"   ".$commune,0,1,'C');//Nom de la commune
           $pdf->SetFont('Times','B',16);// Police Times gras 12
           $pdf->Ln(5);// saut de ligne de 50 mn
	       $pdf->Cell(0,10,$titre,0,1,'C');
	      //$pdf->Ln(10);// Saut de ligne
    	   $titre2 = 'Années '.$datemini.' à '.$datemaxi.'  soit '.$nbractes.' actes';
    	   $pdf->Cell(0,10,$titre2,0,1,'C');// Date
		   $pdf->SetFont('Times','',18);// Police Times gras 12// N°de paroisse
		   $pdf->Cell(0,10,$titreN,0,1,'C');// N°de paroisse
		   
		   $pdf->SetFont('Times','',16);// Police Times gras 12
		   $pdf->Cell(0,10,$titre3,0,1,'C');//Tri par ordre alphabétique
    	   $pdf->SetFont('Times','',8);
		   $pdf->Ln(10);// Saut de ligne
    	   $pdf->Image( $image1,60,120,100,80);// Logo
		   $pdf->Ln(100);
    	   $pdf->Cell(20);
    	   $pdf->MultiCell(150,4,$message,0,C);
    	   //$pdf->Ln(40);
		   $pdf->SetY(-45);// Positionnement à 1,5 cm du bas
    	   $pdf->Cell(20);
    	   $pdf->MultiCell(150,4,$copy,1,C);
    	   $pdf->AddPage();
	   	}

   switch ($type_actes_nimegue)
   {

   case "N":// $type_actes_nimegue = N
    //affichage de chaque champ de la ligne en question
  	//$l1 ="\n".$data[data10]."   ".$data[data11]."   sexe :  ".$data[data12]."   Le ".$data[data6]." ".$data[data7]."       ".$data[data13]."";
  	//$pdf->SetFont('Times','B',8 );//Passage en gras
	$pdf->Cell(50,3,$data[10].'  '.$data[11],0,0,L);
  	$pdf->Cell(20,3,'Sexe : '.$data[12],0,0,L);
	$pdf->Cell(10,3,$data[6].'  '.$data[7],0,1);
	$l1='';
  	if (empty($data[14])){} else {$l1= $l1."  - "."Père   ".$data[14]."   ".$data[15]."   ".$data[16]."   ".$data[17]."\n";}
  	if (empty($data[18])){} else {$l1= $l1."  - "."Mère   ".$data[18]."   ".$data[19]."   ".$data[20]."   ".$data[21]."\n";}
 	if (empty($data[22])){} else {$l1= $l1."  - "."Par/Tém1   ".$data[22]."   ".$data[23]."   ".$data[24]."\n";}
  	if (empty($data[25])){} else {$l1= $l1."  - "."Par/Tém2   ".$data[25]."   ".$data[26]."   ".$data[27]."\n";}
  	if (empty($data[28])){} else {$l1= $l1. $data[28]."\n";}
    $pdf->write(3,$l1);
  	$sep = "-------------------------------------------------------------------------------------------------------------------------------------------------\n";
  	$pdf->write(3,$sep);
      break;

  case "D":// $type_actes_nimegue = D
	//affichage de chaque champ de la ligne en question
	if (empty($data[2])) {$lieuorigine = "";} else {$lieuorigine = "lieu d origine ".$data[12];}  //$lieuorigine = $data[data12]
	if ($data[13] !== "") {$datenaiss ="né(e) le : ".$data[13];} else {$datenaiss = "";}//$datenaiss = $data[data13]
	if (empty($data[15])) {$ages = "";} else {$ages =" Age : ".$data[15]."";}//$ages = $data[data15]
	$commdef = $data[16];
	$prof = $data[17];
	$pdf->Cell(80,3,$data[10].'  '.$data[11],0,0,L);
	$pdf->Cell(20,3,'Sexe : '.$data[14],0,0,L);
	$pdf->Cell(10,3,$data[6].'  '.$data[7],0,1);
	$l1='';
	$infod= $lieuorigine." ".$ages."  ".$data[16]." ".$data[17];
	if (empty($infod)){} else {$l1= $infod."\n";}
	if (empty($data[18])){} else {$l1= $l1."  - "."Conjoint   ".$data[18]."   ".$data[19]."   ".$data[20]."   ".$data[21]."\n";}
	if (empty($data[22])){} else {$l1= $l1."  - "."Père   ".$data[22]."   ".$data[23]."   ".$data[24]."   ".$data[25]."\n";}
	if (empty($data[26])){} else {$l1= $l1."  - "."Mère   ".$data[26]."   ".$data[27]."   ".$data[28]."   ".$data[29]."\n";}
	if (empty($data[30])){} else {$l1= $l1."  - "."Tèm1   ".$data[30]."   ".$data[31]."   ".$data[32]."\n";}
	if (empty($data[33])){} else {$l1= $l1."  - "."Tèm2   ".$data[33]."   ".$data[34]."   ".$data[35]."\n";}
	if (empty($data[36])){} else {$l1= $l1. $data[36]."\n";}
  	$pdf->write(3,$l1);
  	$sep = "------------------------------------------------------------------------------------------------------------------------------------------------\n";
  	$pdf->write(3,$sep);
  break;

  case "M":// $type_actes_nimegue = M
   //affichage de chaque champ de la ligne en question
   // info epoux
	if (empty($data[12])) {$lieuorigine1 = "";} else {$lieuorigine1 = " Originaire de ".$data[12]." ";}  //$lieuorigine 
	//if ($data[data13]!=''or 0) {$datenaiss1 = "";} else {$datenaiss1 = " né le : ".$data[data13]." ";}
	if ($data[13]!=''or empty($data[13])) {$datenaiss1 = "";} else {$datenaiss1 = " né le : ".$data[13]." ";}//$datenaiss
	if (empty($data[14])) {$ages1 = "";} else {$ages1 =" Age : ".$data[14]." ans ";}//$ages 
	$info1= $lieuorigine1.$datenaiss1.$ages1;//Lieu origine + date naiss + age
	if (empty($data[16])) {$prof = "";} else {$prof = " Profession ".$data[16]." ";}  //Profession
	if (empty($data[15])) {$commentaire1 = "";} else {$commentaire1 = " ".$data[15]." ";}  //commentaire1
	$commentaireEpx = $prof.$commentaire;   
	$pdf->Cell(80,3,$data[10].'  '.$data[11],0,0,L);
	$pdf->Cell(10,3,$data[6].'  '.$data[7],0,1);
	$l1='';
	if (empty($info1)){} else {$l1= $l1."   - ".$info1."\n";}
	if (empty($commentaireEpx)){} else {$l1= $l1."   - ".$commentaireEpx."\n";}  
	if (empty($data[17])){} else {$l1= $l1."   - "."Veuf de : ".$data[17]." ".$data[18]." ".$data[19]."\n";}  
	if (empty($data[20])){} else {$l1= $l1."   - "."Père  : ".$data[20]." ".$data[21]." ".$data[22]." ".$data[23]."\n ";}  
	if (empty($data[24])){} else {$l1= $l1."   - "."Mère  : ".$data[24]." ".$data[25]." ".$data[26]." ".$data[27]." \n";} 
	
	//Info Epouse
	if (empty($data[30])) {$lieuorigine2 = "";} else {$lieuorigine2 = " Originaire de ".$data[30]." ";}  //$lieuorigine
	if ($data[31]!=''or empty($data[31])) {$datenaiss2 = "";} else {$datenaiss2 =" née le : ".$data[31]." ";}//$datenaiss 
	if (empty($data[32])) {$ages2 = "";} else {$ages2 =" Age : ".$data[32]." ans ";}//$ages 
	$info2= $lieuorigine2.$datenaiss2.$ages2;//Lieu origine + date naiss + age
	if (empty($data[34])) {$prof2 = "";} else {$prof = " Profession ".$data[34]." ";}  //Profession
	if (empty($data[33])) {$commentaire2 = "";} else {$commentaire2 = " ".$data[33]." ";}  //commentaire2
	$commentaireEp = $prof2.$commentaire2;   
	$l1 =$l1.$data[28]."   ".$data[29]."\n";
	if (empty($info2)){} else {$l1= $l1."   - ".$info2."\n";}
	if (empty($commentaireEp)){} else {$l1= $l1."   - ".$commentaireEp."\n";}  
	if (empty($data[35])){} else {$l1= $l1."   - "."Veuve de : ".$data[35]." ".$data[36]." ".$data[37]."\n";}  
	if (empty($data[38])){} else {$l1= $l1."   - "."Père  : ".$data[38]." ".$data[39]." ".$data[40]." ".$data[41]."\n";}  
	if (empty($data[42])){} else {$l1= $l1."   - "."Mère  : ".$data[42]." ".$data[43]." ".$data[44]." ".$data[45]."\n";} 
  	// Témoins
  	if (empty($data[46])){} else {$l1= $l1."    - "."Témoin 1  : ".$data[46]." ".$data[47]." ".$data[48]."\n ";}  
	if (empty($data[49])){} else {$l1= $l1."    - "."Témoin 2  : ".$data[49]." ".$data[50]." ".$data[51]."\n ";} 
  	if (empty($data[52])){} else {$l1= $l1."    - "."Témoin 3  : ".$data[52]." ".$data[53]." ".$data[54]."\n ";}  
	if (empty($data[55])){} else {$l1= $l1."    - "."Témoin 4  : ".$data[55]." ".$data[56]." ".$data[57]."\n ";} 
	if (empty($data[58])){} else {$l1= $l1."    - ".$data[58]."\n";}
	$pdf->write(3,$l1);
  	$sep = "-------------------------------------------------------------------------------------------------------------------------------------------------\n";
  	$pdf->write(3,$sep);

  break;


  case "V":// $type_actes_nimegue = V
    //affichage de chaque champ de la ligne en question
    // info type Acte & Notaire
	//if (empty($data[data9])) {$Notaire = "";} else {$Notaire = "               Notaire : ".$data[data9]." ";}  //$Notaire
	//if (empty($data[data11])) {$Type_Acte  = "";} else {$Type_Acte  ="     Acte de : ".$data[data11]." ";}//$Type_Acte
	//$pdf->Cell(80,3,$Notaire.'  '.$Type_Acte,0,1);
	if (empty($data[9])) {$Notaire = "";} else {$Notaire = "               Notaire : ".$data[9]." ";}  //$Notaire
	if (empty($data[8])) {$cote = "";} else {$cote = "               Cote : ".$data[8]." ";}  //$Cote
	if (empty($data[11])) {$Type_Acte  = "";} else {$Type_Acte  ="     Acte de : ".$data[11]." ";}//$Type_Acte
	$pdf->Cell(80,3,$Notaire.'  '.$cote.'   '.$Type_Acte,0,1);


  // info Intervenant1
	if (empty($data[15])) {$lieuorigine1 = "";} else {$lieuorigine1 = "Originaire de ".$data[15]." ";}  //$lieuorigine 
	if ($data[16]!=''or empty($data[16])) {$datenaiss1 = "";} else {$datenaiss1 ="né le : ".$data[16]." ";}//$datenaiss 
	if (empty($data[17])) {$ages1 = "";} else {$ages1 ="Age : ".$data[17]." ans ";}//$ages 
	$info1= $lieuorigine1.$datenaiss1.$ages1;//Lieu origine + date naiss + age
	if (empty($data[19])) {$prof = "";} else {$prof = "Profession ".$data[19]." ";}  //Profession
	if (empty($data[18])) {$commentaire1 = "";} else {$commentaire1 = " ".$data[18]." ";}  //commentaire1
	$commentaireEpx = $prof.$commentaire;   
	/*$l1 ="\n".$data[data12]."   ".$data[data13]."  ".$data[data11]."                 		Le ".$data[data6]." ".$data[data7]."";*/
	$pdf->Cell(80,3,$data[12].'  '.$data[13],0,0,L);
	$pdf->Cell(10,3,$data[6].'  '.$data[7],0,1);
	$l1='';
	if (empty($info1)){} else {$l1= $l1."  - ".$info1."\n";}
	if (empty($commentaireEpx)){} else {$l1= $l1."  - ".$commentaireEpx."\n";}  
	if (empty($data[20])){} else {$l1= $l1."  - "."Ex épouse : ".$data[20]." ".$data[21]." ".$data[22]."\n";}  
	if (empty($data[23])){} else {$l1= $l1."  - "."Père  : ".$data[23]." ".$data[24]." ".$data[25]." ".$data[26]."\n";}  
	if (empty($data[27])){} else {$l1= $l1."  - "."Mère  : ".$data[27]." ".$data[28]." ".$data[29]." ".$data[30]."\n";} 
	
	//Info Intervenant2
	if (empty($data[34])) {$lieuorigine2 = "";} else {$lieuorigine2 = "Originaire de ".$data[34]." ";}  //$lieuorigine
	if ($data[35]!=''or empty($data[35])) {$datenaiss2 = "";} else {$datenaiss2 ="née le : ".$data[35]." ";}//$datenaiss 
	if (empty($data[36])) {$ages2 = "";} else {$ages2 ="Age : ".$data[36]." ans ";}//$ages 
	$info2= $lieuorigine2.$datenaiss2.$ages2;//Lieu origine + date naiss + age
	if (empty($data[38])) {$prof2 = "";} else {$prof = "Profession ".$data[38]." ";}  //Profession
	if (empty($data[37])) {$commentaire2 = "";} else {$commentaire2 = " ".$data[37]." ";}  //commentaire2
	$commentaireEp = $prof2.$commentaire2;   
	$l1 =$l1.$data[31]."   ".$data[32]."\n";
	if (empty($info12)){} else {$l1= $l1."  - ".$info1."\n";}
	if (empty($commentaireEp)){} else {$l1= $l1."  - ".$commentaireEp."\n";}  
	if (empty($data[39])){} else {$l1= $l1."  - "."Ex époux : ".$data[39]." ".$data[40]." ".$data[41]."\n";}  
	if (empty($data[42])){} else {$l1= $l1."  - "."Père  : ".$data[42]." ".$data[43]." ".$data[45]." ".$data[44]."\n";}  
	if (empty($data[46])){} else {$l1= $l1."  - "."Mère  : ".$data[46]." ".$data[47]." ".$data[49]." ".$data[48]."\n";} 
  	
  	// Témoins
  	if (empty($data[50])){} else {$l1= $l1."\n"."  - "."Témoin 1  : ".$data[50]." ".$data[51]."  ".$data[52]."\n";} 
	if (empty($data[53])){} else {$l1= $l1."  - "."Témoin 2  : ".$data[53]." ".$data[54]."  ".$data[55]."\n";}  
	if (empty($data[56])){} else {$l1= $l1."  - "."Témoin 3  : ".$data[56]." ".$data[57]."  ".$data[58]."\n";} 
  	if (empty($data[59])){} else {$l1= $l1."  - "."Témoin 4  : ".$data[59]." ".$data[60]."  ".$data[61]."\n";}  
	$com = str_replace("§"," - ",$data[62]);
	if (empty($data[62])){} else {$l1= $l1."  - ".$com."\n";}
  	$pdf->write(3,$l1);
  	$sep = "-----------------------------------------------------------------------------------------------------------------------------------------------\n";
  	$pdf->write(3,$sep);
  break;
//=================================================== RECENSEMENT DEB==========================================
  case "R":// $type_actes_nimegue = R pour recensementt
    //affichage de chaque champ de la ligne en question

    //$pdf->Cell(50,3,"Année : ".$data[6]." Quartier : ".$data[8]." Rue : ".$data[9]." N° de maison : " .$data[10]." N° Ménage : ",0,0,L);
  	//$pdf->Cell(20,3,$data[11]." ".$data[12]." ".$data[13]."Age : ".$data[14]." "."Année naissance :".$data[15]." Profession :".$data[16],0,0,L);
	//
	$l1='';
  	$l1= $l1."Année : ".$data[6]." - Quartier : ".$data[8]." - Rue : ".$data[9]." - N° de maison : " .$data[10]." - N° Ménage : ".$data[11]."\n";
  	$l1= $l1."-".$data[12]." - ".$data[13]." - ".$data[14]." - Age : ".$data[15]." - né en :".$data[16]." à ".$data[17]." - Profession :".$data[18]."\n";
 	//if (empty($data[22])){} else {$l1= $l1."  - "."Par/T?m1   ".$data[22]."   ".$data[23]."   ".$data[24]."\n";}
  	//if (empty($data[25])){} else {$l1= $l1."  - "."Par/T?m2   ".$data[25]."   ".$data[26]."   ".$data[27]."\n";}
  	//if (empty($data[28])){} else {$l1= $l1. $data[28]."\n";}
    $pdf->write(3,$l1);
  	$sep = "-------------------------------------------------------------------------------------------------------------------------------------------------\n";
  	$pdf->write(3,$sep);


	
  break;
//===================================RECENSEMENT FIN===========================================
  
  }
}


switch ($type_actes_nimegue) { //Ajout du repertoire par Epouses ou Interv2

   case "M":
	$pdf->AddPage();
	$titre3 = "Par ordre alphabétique sur l'épouse";
	$pdf->SetFont('Times','',16);// Police Times gras 12
	$pdf->Cell(0,10,$titre3,0,1,'C');//Tri par ordre alphabétique
    $pdf->SetFont('Times','',8);
	//liste_epouses_m();
    $mar = "SELECT * FROM tmp_publication ORDER BY `tmp_publication`.`data28` ASC";
	$req=$connexionBD->execute_requete($mar);
    while ($data=$connexionBD->ligne_suivante_resultat($req))
      {
      $ligne ++;

      //$pdf->write(3,$l1);
      $pdf->SetLeftMargin(20);
      $pdf->Cell(40,3,'- '.$data[28]);
      $pdf->Cell(20,3,$data[29]);
      $pdf->Cell(10,3,'  X   -  ');
      $pdf->Cell(40,3,$data[10]);
      $pdf->Cell(20,3,$data[11]);
      $pdf->Cell(10,3,'     Le :');
      $pdf->Cell(10,3,$data[6],0,1);
  	   }
    break;

	case "V":
	 $pdf->AddPage();
	 $titre3 = "Par ordre alphabétique sur l'intervenant 2";
	 $pdf->SetFont('Times','',16);// Police Times gras 12
	 $pdf->Cell(0,10,$titre3,0,1,'C');//Tri par ordre alphabétique
     $pdf->SetFont('Times','',8);
     $div = "SELECT * FROM tmp_publication ORDER BY `tmp_publication`.`data31` ASC";//tri
     $req=$connexionBD->execute_requete($div);
	 while ($data=$connexionBD->ligne_suivante_resultat($req))
	  {
      $ligne ++;

      $pdf->SetLeftMargin(20);
      $pdf->Cell(40,3,'- '.$data[31]);
      $pdf->Cell(20,3,$data[32]);
      $pdf->Cell(10,3,'    &     -  ');
      $pdf->Cell(40,3,$data[12]);
      $pdf->Cell(20,3,$data[13]);
      $pdf->Cell(10,3,'     Le :');
      $pdf->Cell(10,3,$data[6],0,1);
      //$pdf->write(3,$l1);
  	   }
	break;
	 }

$today = date("m_d_y");
$nb_pages = $pdf->PageNo();// Nombre de pages
$nom_fichier = $commune.'_'.$type_actes_nimegue.'_'.$datemini.'_'.$datemaxi.'_'.$nb_pages.' Pages.PDF';


print $nom_fichier;
ob_end_clean(); // Détruit les données du tampon de sortie et éteint la temporisation de sortie
$pdf->Output($nom_fichier,'D');
?>
