
// Verification des champs du menu recherche
function VerifieChampsRecherche(Formulaire,Mode)
{
	var ListeErreurs	= "";
	var selectElmt = document.getElementById('idf_serie_liasse');
	var idf_serie_liasse = selectElmt.options[selectElmt.selectedIndex].text;
	var cote_debut = document.forms[Formulaire].cote_debut.value;
	var cote_fin   = document.forms[Formulaire].cote_fin.value;
	var sans_notaire = document.forms[Formulaire].sans_notaire.checked;
	var nom_notaire = document.forms[Formulaire].nom_notaire.value;
	var sans_periode = document.forms[Formulaire].sans_periode.checked;
	var annee_min = document.forms[Formulaire].annee_min.value;
	   
	switch (Mode)
	{
		case 'RechercheSimple' :
			document.forms[Formulaire].action ="ReponsesLiasseSimple.php";
			break;
		case 'RechercheAvancee' :
			document.forms[Formulaire].action ="ReponsesLiasseAvancee.php";
			break;                
		default: alert('Type de recherche inconnu :' + document.forms[Formulaire].recherche.value); 
	}
	if ( idf_serie_liasse == '' )   {
		ListeErreurs+="Sélectionner la série de liasses\n";
	}
	if( cote_debut != '' && isNaN(cote_debut ) ){
		ListeErreurs+="La cote de début doit être un nombre\n";
	}
	if( cote_fin != '' && isNaN(cote_fin ) ){
		ListeErreurs+="La cote de fin doit être un nombre\n";
	}
	if ( sans_notaire )   {
		if (nom_notaire!="")   {
			ListeErreurs+="Ne pas cocher 'liasses sans notaire' si vous saisissez un nom de notaire\n";
		}
	}
	if ( sans_periode )   {
		if (annee_min!="")   {
			ListeErreurs+="Ne pas cocher 'liasses sans date' si vous saisissez une année\n";
		}
	}

	if (ListeErreurs!= "") 	{
		alert(ListeErreurs);
	}
	else 	{
		document.forms[Formulaire].submit();
	}
}

function RazChamps(Formulaire)
{
   document.forms[Formulaire].annee_min.value="";
   document.forms[Formulaire].annee_max.value="";
   document.forms[Formulaire].rayon.value="";
   document.forms[Formulaire].nom_notaire.value ="";
   document.forms[Formulaire].prenom_notaire.value="";
   document.forms[Formulaire].idf_serie_liasse.selectedIndex = '';
   document.forms[Formulaire].cote_debut.value="";
   document.forms[Formulaire].cote_fin.value="";
   document.forms[Formulaire].idf_commune_recherche.selectedIndex = document.forms[Formulaire].idf_commune_recherche.length-2;
   document.forms[Formulaire].paroisses_rattachees.checked=true;
   document.forms[Formulaire].repertoire.checked=false;
   document.forms[Formulaire].sans_notaire.checked=false;
   document.forms[Formulaire].sans_periode.checked=false;
}


function SoumissionSimple(Formulaire,Evt) 
{   
  if (Evt.keyCode == 13)  {     
      VerifieChampsRecherche(0,'RechercheSimple'); 
  }
}

function SoumissionAvancee(Formulaire,Evt) 
{   
  if (Evt.keyCode == 13)  {     
      VerifieChampsRecherche(0,'RechercheAvancee'); 
  }
}


