// Verification des champs du menu recherche
function VerifieChampsRechercheAction(Formulaire)
{
	var ListeErreurs = "";
	var menu = document.forms['critere'].menu.value;
	if( menu == 'pas_releve' || menu == 'pas_photo' ){
		var cote_debut = document.forms['critere'].cote_debut.value;
		var cote_fin   = document.forms['critere'].cote_fin.value;
		if( cote_debut != '' && isNaN(cote_debut ) ){
			ListeErreurs+="La cote de début doit être un nombre\n";
		}
		if( cote_fin != '' && isNaN(cote_fin ) ){
			ListeErreurs+="La cote de fin doit être un nombre\n";
		}
	}
	if( menu == 'sans' ){
		var sans_notaire = document.forms['critere'].sans_notaire.checked;
		var sans_periode = document.forms['critere'].sans_periode.checked;
		var sans_lieu = document.forms['critere'].sans_lieu.checked;
		if( ! sans_notaire  && ! sans_periode && ! sans_lieu ){
			ListeErreurs+="Cochez au moins un critère\n";
		}
	}
	if (ListeErreurs!= "")	{
		alert(ListeErreurs);
	}
	else {
		document.forms['critere'].action ="ReponsesActionsLiasse.php";
		document.forms['critere'].submit();
	}
}

function RazChampsAction(Formulaire)
{
	var menu = document.forms['critere'].menu.value;
	switch(menu) {
		case 'releve':
			document.forms['critere'].repertoire.checked=false;
			document.forms['critere'].non_comm.checked=false;
			break;
		case 'pas_releve':
			document.forms['critere'].cote_debut.value="";
			document.forms['critere'].cote_fin.value="";
			document.forms['critere'].repertoire.checked=false;
			document.forms['critere'].non_comm.checked=false;
			document.forms['critere'].av_1793.checked=false;
			break;
		case 'photo':
			document.forms['critere'].repertoire.checked=false;
			document.forms['critere'].non_comm.checked=false;
			break;
		case 'pas_photo':
			document.forms['critere'].cote_debut.value="";
			document.forms['critere'].cote_fin.value="";
			document.forms['critere'].repertoire.checked=false;
			document.forms['critere'].non_comm.checked=false;
			document.forms['critere'].av_1793.checked=false;
			break;
		case 'repert':
			document.forms['critere'].av_1793.checked=false;
			break;
		case 'sans':
			document.forms['critere'].sans_notaire.checked=false;
			document.forms['critere'].sans_periode.checked=false;
			document.forms['critere'].sans_lieu.checked=false;
			break;
		case 'non_comm':
			document.forms['critere'].av_1793.checked=false;
			break;
		case 'program':
			document.forms['critere'].releve.checked=false;
			document.forms['critere'].photo.checked=false;
			break;
	}
}

function SoumissionAction(Formulaire,Evt) 
{   
  if (Evt.keyCode == 13)  {     
      VerifieChampsRechercheAction('critere'); 
  }
}

function SoumissionSimple(Formulaire,Evt) 
{   
  if (Evt.keyCode == 13)  {     
      VerifieChampsRecherche(0,'RechercheSimple'); 
  }
}

