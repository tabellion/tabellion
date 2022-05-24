function VerifieChampsReleveur(Formulaire){
	document.forms[Formulaire].submit();
}


function VerifieChampsLienPubli(Formulaire){
	var selectElmt = document.getElementById('idf_publication');
	var idf_publication = selectElmt.options[selectElmt.selectedIndex].value;
	var ListeErreurs	= "";
	if ( idf_publication == 0 )   {
		ListeErreurs+="Saisir une publication papier\n";
	}
	if (ListeErreurs!= "")   {
		alert(ListeErreurs);
	}
	else   {
		document.forms[Formulaire].submit();
	}
}

function VerifieSuppressionLiensPublis(Formulaire,IdfElement){
	var chaine="";
	// Un seul élément
	if (document.forms['publi'].elements[IdfElement].checked)	{
		 chaine+=document.forms['publi'].elements[IdfElement].id;
	}
	// Au moins deux éléments 
	for (var i = 0; i < document.forms['publi'].elements[IdfElement].length; i++)  {
		if (document.forms['publi'].elements[IdfElement][i].checked)      {
			chaine+=document.forms['publi'].elements[IdfElement][i].id+"\n";
		}
	}
	if (chaine=="")  {
		alert("Pas de lien publication sélectionnée");
	}
	else  {
		Message="Etes-vous sûr de supprimer ces liens publications papiers :\n"+chaine+"?";
		if (confirm(Message))        {                                                                                                                                    
			document.forms['publi'].submit();                                                           
		}
	}
}


function VerifieChampsPubli(Formulaire){
	var titre				= document.forms[Formulaire].titre.value;
	var date_publication	= document.forms[Formulaire].date_publication.value;
	var ListeErreurs		= "";
	var jj				= date_publication.substring(0,2);
	var mm					= date_publication.substring(3,5);
	var aa					= date_publication.substring(6);
	var sep1				= date_publication.substring(2,3);
	var sep2				= date_publication.substring(5,6);
	if ( titre == "" || date_publication == "" )   {
		ListeErreurs+="Saisir le titre et la date de publication\n";
	}
	if ( titre.substring(0,1) < "A" || titre.substring(0,1) > "Z" ) {
			ListeErreurs+="Le premier caractère du titre doit être une lettre majuscule\n";
		}	
	if ( date_publication != "" ) {
		if ( isNaN(jj) || jj<1 || jj>31 ) {
			ListeErreurs+="Le jour de la date de publication n'est pas correct\n";
		}	
		else if ( isNaN(mm) || mm<1 || mm>12 ) {
			ListeErreurs+="Le mois de la date de publication n'est pas correct\n";
		}	
		else if ( isNaN(aa) || aa<1950 || aa>2100 ) {
			ListeErreurs+="L'année de la date de publication n'est pas correct\n";
		}	
		else if ( ( mm == 4 || mm == 6 || mm == 9 || mm == 11 ) && jj > 30 ) {
			ListeErreurs+="Le jour de la date de publication n'est pas correct\n";
		}	
		else if ( mm == 2 && (aa % 4) == 0 && jj > 29 ){
			ListeErreurs+="Le jour de la date de publication n'est pas correct\n";
		}	
		else if ( mm == 2 && (aa % 4) != 0 && jj > 28 ){
			ListeErreurs+="Le jour de la date de publication n'est pas correct\n";
		}		
		else if ( sep1 != "/" || sep2 != "/" ) {
			ListeErreurs+="Les jour, mois et année de la date de publication doivent être séparés par / \n";
		}
	}
	if (ListeErreurs!= "")   {
		alert(ListeErreurs);
	}
	else   {
		document.forms[Formulaire].submit();
	}
}

function VerifieSuppressionPublis(Formulaire,IdfElement){
	var chaine="";
	// Un seul élément
	if (document.forms['publi'].elements[IdfElement].checked)	{
		 chaine+=document.forms['publi'].elements[IdfElement].id;
	}
	// Au moins deux éléments 
	for (var i = 0; i < document.forms['publi'].elements[IdfElement].length; i++)  {
		if (document.forms['publi'].elements[IdfElement][i].checked)      {
			chaine+=document.forms['publi'].elements[IdfElement][i].id+"\n";
		}
	}
	if (chaine=="")  {
		alert("Pas de publication sélectionnée");
	}
	else  {
		Message="Etes-vous sûr de supprimer ces publications papiers :\n"+chaine+"?";
		if (confirm(Message))        {                                                                                                                                    
			document.forms['publi'].submit();                                                           
		}
	}
}

function VerifieChampsPhoto(Formulaire){
	var selectElmt = document.getElementById('idf_photographe');
	var idf_photographe = selectElmt.options[selectElmt.selectedIndex].value;
	var selectElmt = document.getElementById('idf_couverture_photo');
	var idf_couverture_photo = selectElmt.options[selectElmt.selectedIndex].value;
	var selectElmt = document.getElementById('idf_codif_photo');
	var idf_codif_photo = selectElmt.options[selectElmt.selectedIndex].value;
	var date_photo = document.forms[Formulaire].date_photo.value;
	var jj=date_photo.substring(0,2);
	var mm=date_photo.substring(3,5);
	var aa=date_photo.substring(6);
	var sep1=date_photo.substring(2,3);
	var sep2=date_photo.substring(5,6);
	var ListeErreurs	= "";
	/*if ( idf_photographe == 0 )   {
		ListeErreurs+="Saisir le photographe\n";
	}*/
	if ( date_photo == "" && idf_couverture_photo == 0 && idf_codif_photo == 0 )   {
		ListeErreurs+="Saisir au moins une information\n";
	}
	if ( date_photo != "" ) {
		if ( isNaN(jj) || jj<1 || jj>31 ) {
			ListeErreurs+="Le jour de la date de fin de relevé n'est pas correct\n";
		}	
		else if ( isNaN(mm) || mm<1 || mm>12 ) {
			ListeErreurs+="Le mois de la date de fin de relevé n'est pas correct\n";
		}	
		else if ( isNaN(aa) || aa<1980 || aa>2100 ) {
			ListeErreurs+="L'année de la date de fin de relevé n'est pas correct\n";
		}	
		else if ( ( mm == 4 || mm == 6 || mm == 9 || mm == 11 ) && jj > 30 ) {
			ListeErreurs+="Le jour de la date de fin de relevé n'est pas correct\n";
		}	
		else if ( mm == 2 && (aa % 4) == 0 && jj > 29 ){
			ListeErreurs+="Le jour de la date de fin de relevé n'est pas correct\n";
		}	
		else if ( mm == 2 && (aa % 4) != 0 && jj > 28 ){
			ListeErreurs+="Le jour de la date de fin de relevé n'est pas correct\n";
		}		
		else if ( sep1 != "/" || sep2 != "/" ) {
			ListeErreurs+="Les jour, mois et année de la date de fin de relevé doivent être séparés par / \n";
		}
	}
	if (ListeErreurs!= "")   {
		alert(ListeErreurs);
	}
	else   {
		document.forms[Formulaire].submit();
	}
}

function VerifieSuppressionPhotos(Formulaire,IdfElement){
	var chaine="";
  // Un seul élément
	if (document.forms['photo'].elements[IdfElement].checked)	{
		 chaine+=document.forms['photo'].elements[IdfElement].id;
	}
	// Au moins deux éléments 
  for (var i = 0; i < document.forms['photo'].elements[IdfElement].length; i++)  {
     if (document.forms['photo'].elements[IdfElement][i].checked)      {
         chaine+=document.forms['photo'].elements[IdfElement][i].id+"\n";
      }
  }
  if (chaine=="")  {
     alert("Pas de prise de photo sélectionnée");
  }
  else  {
   	 Message="Etes-vous sûr de supprimer ces prises de photo :\n"+chaine+"?";
   	 if (confirm(Message))        {                                                                                                                                    
        document.forms['photo'].submit();                                                           
     }
  }
}

function VerifieChampsProgram(Formulaire){
	var selectElmt = document.getElementById('idf_intervenant');
	var idf_intervenant = selectElmt.options[selectElmt.selectedIndex].value;
	var selectElmt = document.getElementById('idf_priorite');
	var idf_priorite = selectElmt.options[selectElmt.selectedIndex].value;
	var program_releve = document.forms[Formulaire].program_releve.checked;
	var program_photo = document.forms[Formulaire].program_photo.checked;
	var date_creation = document.forms[Formulaire].date_creation.value;
	var jjcre=date_creation.substring(0,2);
	var mmcre=date_creation.substring(3,5);
	var aacre=date_creation.substring(6);
	var sep1cre=date_creation.substring(2,3);
	var sep2cre=date_creation.substring(5,6);
	var date_echeance = document.forms[Formulaire].date_echeance.value;
	var jjech=date_echeance.substring(0,2);
	var mmech=date_echeance.substring(3,5);
	var aaech=date_echeance.substring(6);
	var sep1ech=date_echeance.substring(2,3);
	var sep2ech=date_echeance.substring(5,6);
	var date_reelle_fin = document.forms[Formulaire].date_reelle_fin.value;
	var jjfin=date_reelle_fin.substring(0,2);
	var mmfin=date_reelle_fin.substring(3,5);
	var aafin=date_reelle_fin.substring(6);
	var sep1fin=date_reelle_fin.substring(2,3);
	var sep2fin=date_reelle_fin.substring(5,6);
	var ListeErreurs	= "";
	if ( idf_intervenant == 0 && idf_priorite == 0 && ! program_releve && !program_photo )   {
		ListeErreurs+="Saisir au moins l'intervenant, la priorité et le type de programmation\n";
	}
	if ( date_creation != "" ) {
		if ( isNaN(jjcre) || jjcre<1 || jjcre>31 ) {
			ListeErreurs+="Le jour de la date de création n'est pas correct\n";
		}	
		else if ( isNaN(mmcre) || mmcre<1 || mmcre>12 ) {
			ListeErreurs+="Le mois de la date de création n'est pas correct\n";
		}	
		else if ( isNaN(aacre) || aacre<1980 || aacre>2100 ) {
			ListeErreurs+="L'année de la date de création n'est pas correct\n";
		}	
		else if ( ( mmcre == 4 || mmcre == 6 || mmcre == 9 || mmcre == 11 ) && jjcre > 30 ) {
			ListeErreurs+="Le jour de la date de création n'est pas correct\n";
		}	
		else if ( mmcre == 2 && (aacre % 4) == 0 && jjcre > 29 ){
			ListeErreurs+="Le jour de la date de création n'est pas correct\n";
		}	
		else if ( mmcre == 2 && (aacre % 4) != 0 && jjcre > 28 ){
			ListeErreurs+="Le jour de la date de création n'est pas correct\n";
		}		
		else if ( sep1cre != "/" || sep2cre != "/" ) {
			ListeErreurs+="Les jour, mois et année de la date de création doivent être séparés par / \n";
		}
	}
	if ( date_echeance != "" ) {
		if ( isNaN(jjech) || jjech<1 || jjech>31 ) {
			ListeErreurs+="Le jour de la date d'échéance n'est pas correct\n";
		}	
		else if ( isNaN(mmech) || mmech<1 || mmech>12 ) {
			ListeErreurs+="Le mois de la date d'échéance n'est pas correct\n";
		}	
		else if ( isNaN(aaech) || aaech<1980 || aaech>2100 ) {
			ListeErreurs+="L'année de la date d'échéance n'est pas correct\n";
		}	
		else if ( ( mmech == 4 || mmech == 6 || mmech == 9 || mmech == 11 ) && jjech > 30 ) {
			ListeErreurs+="Le jour de la date d'échéance n'est pas correct\n";
		}	
		else if ( mmech == 2 && (aaech % 4) == 0 && jjech > 29 ){
			ListeErreurs+="Le jour de la date d'échéance n'est pas correct\n";
		}	
		else if ( mmech == 2 && (aaech % 4) != 0 && jjech > 28 ){
			ListeErreurs+="Le jour de la date d'échéance n'est pas correct\n";
		}		
		else if ( sep1cre != "/" || sep2cre != "/" ) {
			ListeErreurs+="Les jour, mois et année de la date d'échéance doivent être séparés par / \n";
		}
	}
	if ( date_reelle_fin != "" ) {
		if ( isNaN(jjfin) || jjfin<1 || jjfin>31 ) {
			ListeErreurs+="Le jour de la date réelle de fin n'est pas correct\n";
		}	
		else if ( isNaN(mmfin) || mmfin<1 || mmfin>12 ) {
			ListeErreurs+="Le mois de la date réelle de fin n'est pas correct\n";
		}	
		else if ( isNaN(aafin) || aafin<1980 || aafin>2100 ) {
			ListeErreurs+="L'année de la date réelle de fin n'est pas correct\n";
		}	
		else if ( ( mmfin == 4 || mmfin == 6 || mmfin == 9 || mmfin== 11 ) && jjfin > 30 ) {
			ListeErreurs+="Le jour de la date réelle de fin n'est pas correct\n";
		}	
		else if ( mmfin == 2 && (aafin % 4) == 0 && jjfin > 29 ){
			ListeErreurs+="Le jour de la date réelle de fin n'est pas correct\n";
		}	
		else if ( mmfin == 2 && (aafin % 4) != 0 && jjfin > 28 ){
			ListeErreurs+="Le jour de la date réelle de fin n'est pas correct\n";
		}		
		else if ( sep1fin != "/" || sep2fin != "/" ) {
			ListeErreurs+="Les jour, mois et année de la date réelle de fin doivent être séparés par / \n";
		}
	}
	if (ListeErreurs!= "")   {
		alert(ListeErreurs);
	}
	else   {
		document.forms[Formulaire].submit();
	}
}

function VerifieSuppressionPrograms(Formulaire,IdfElement){
	var chaine="";
  // Un seul élément
	if (document.forms['program'].elements[IdfElement].checked)	{
		 chaine+=document.forms['program'].elements[IdfElement].id;
	}
	// Au moins deux éléments 
  for (var i = 0; i < document.forms['program'].elements[IdfElement].length; i++)  {
     if (document.forms['program'].elements[IdfElement][i].checked)      {
         chaine+=document.forms['program'].elements[IdfElement][i].id+"\n";
      }
  }
  if (chaine=="")  {
     alert("Pas de prise de program sélectionnée");
  }
  else  {
   	 Message="Etes-vous sûr de supprimer ces prises de program :\n"+chaine+"?";
   	 if (confirm(Message))        {                                                                                                                                    
        document.forms['program'].submit();                                                           
     }
  }
}

