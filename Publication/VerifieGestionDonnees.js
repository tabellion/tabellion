// Verification des champs du menu recherche
function VerifieChargement(Formulaire)
{
   var ListeErreurs	= "";
   var ChaineConfirmation = "";
   var fichier	= document.forms[Formulaire].FichNim.value;
   var idf_source	= document.forms[Formulaire].idf_source.selectedIndex;
   var idf_commune_acte	= document.forms[Formulaire].idf_commune_acte.selectedIndex;
   var idf_releveur	= document.forms[Formulaire].idf_releveur.selectedIndex;
   var idf_type_acte	= document.forms[Formulaire].idf_type_acte.selectedIndex;
   var idf_version_nimegue	= document.forms[Formulaire].idf_version_nimegue.selectedIndex;
   if (idf_source==-1)
   {
      ListeErreurs+="Aucune source selectionnee\n";
   }
   if (idf_commune_acte==-1)
   {
      ListeErreurs+="Aucune commune_acte selectionnee\n";
   }
   if (idf_version_nimegue==-1)
   {
      ListeErreurs+="Aucune version nimegue selectionnee\n";
   }
   if (fichier=="")
   {
      ListeErreurs+="Aucun fichier selectionne\n";
   }
   if (ListeErreurs!= "")
   {
      alert(ListeErreurs);
      return false;
   }
   ChaineConfirmation += "Source: ";
   ChaineConfirmation += document.forms[Formulaire].idf_source.options[idf_source].text ;
   ChaineConfirmation += "\n";
   ChaineConfirmation += "Commune: ";
   ChaineConfirmation += document.forms[Formulaire].idf_commune_acte.options[idf_commune_acte].text ;
   ChaineConfirmation += "\n";
   ChaineConfirmation += "Releveur: ";
   ChaineConfirmation += document.forms[Formulaire].idf_releveur.options[idf_releveur].text ;
   ChaineConfirmation += "\n";
   ChaineConfirmation += "Type d'acte: ";
   ChaineConfirmation += document.forms[Formulaire].idf_type_acte.options[idf_type_acte].text ;
   ChaineConfirmation += "\n";
   ChaineConfirmation += "Version de Nimegue: ";
   ChaineConfirmation += document.forms[Formulaire].idf_version_nimegue.options[idf_version_nimegue].text ;
   ChaineConfirmation += "\n";
   if (window.confirm("Souhaitez-vous charger le fichier avec les paramètres suivants ?\n" +ChaineConfirmation))
   {
       document.forms[Formulaire].mode.value='CHARGEMENT';
       document.forms[Formulaire].submit();
   }
}

function Exporte(Formulaire,Mode)
{
   var ListeErreurs	= "";
   var ChaineConfirmation = "";
   var idf_source	= document.forms[Formulaire].idf_source.selectedIndex;
   var idf_commune_acte	= document.forms[Formulaire].idf_commune_acte.selectedIndex;
   if (idf_source==-1)
   {
      ListeErreurs+="Aucune source selectionnee\n";
   }
   if (idf_commune_acte==-1)
   {
      ListeErreurs+="Aucune commune_acte selectionnee\n";
   }
    if (ListeErreurs!= "")
   {
      alert(ListeErreurs);
      return false;
   }
   document.forms[Formulaire].mode.value=Mode;
   document.forms[Formulaire].submit();
}


