
/*function VerifieSuppression(Formulaire,IdfElement) {
  var chaine="";
  // Un seul élément
	if (document.forms[Formulaire].elements[IdfElement].checked)	{
		 chaine+=document.forms[Formulaire].elements[IdfElement].id;
	}
	// Au moins deux éléments 
  for (var i = 0; i < document.forms[Formulaire].elements[IdfElement].length; i++)  {
      if (document.forms[Formulaire].elements[IdfElement][i].checked)      {
         chaine+=document.forms[Formulaire].elements[IdfElement][i].id+"\n";
      }
  }
  if (chaine=="")  {
     alert("Pas de liasse sélectionnée");
  }
  else  {
   	 Message="Etes-vous sûr de supprimer ces liasses :\n"+chaine+"?";
   	 if (confirm(Message))        {                                                                                                                                    
        document.forms[Formulaire].submit();                                                           
     }
  }
}*/

function VerifieSuppressionNotaires(Formulaire,IdfElement) {
  var chaine="";
  // Un seul élément
	if (document.forms[Formulaire].elements[IdfElement].checked)	{
		 chaine+=document.forms[Formulaire].elements[IdfElement].id;
	}
	// Au moins deux éléments 
  for (var i = 0; i < document.forms[Formulaire].elements[IdfElement].length; i++)  {
      if (document.forms[Formulaire].elements[IdfElement][i].checked)      {
         chaine+=document.forms[Formulaire].elements[IdfElement][i].id+"\n";
      }
  }
  if (chaine=="")  {
     alert("Pas de notaire sélectionné");
  }
  else  {
   	 Message="Etes-vous sûr de supprimer ces notaires :\n"+chaine+"?";
   	 if (confirm(Message))        {                                                                                                                                    
        document.forms[Formulaire].submit();                                                           
     }
  }
}

function VerifieSuppressionPeriodes(Formulaire,IdfElement) {
  var chaine="";
  // Un seul élément
	if (document.forms[Formulaire].elements[IdfElement].checked)	{
		 chaine+=document.forms[Formulaire].elements[IdfElement].id;
	}
	// Au moins deux éléments 
  for (var i = 0; i < document.forms[Formulaire].elements[IdfElement].length; i++)  {
      if (document.forms[Formulaire].elements[IdfElement][i].checked)      {
         chaine+=document.forms[Formulaire].elements[IdfElement][i].id+"\n";
      }
  }
  if (chaine=="")  {
     alert("Pas de période sélectionnée");
  }
  else  {
   	 Message="Etes-vous sûr de supprimer ces périodes :\n"+chaine+"?";
   	 if (confirm(Message))        {                                                                                                                                    
        document.forms[Formulaire].submit();                                                           
     }
  }
}
