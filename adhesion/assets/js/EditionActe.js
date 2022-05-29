// JavaScript Document
$(".recopie_patro").click(function() {
	var source= $(this).data('source');
	var cible= $(this).data('cible');
	$(cible).val($(source).val());
});

$(".recopie_commune").click(function() {
	var source= $(this).data('source');
	var cible= $(this).data('cible');
	$(cible).val(source);
});

$(".maj_deces").click(function() {
	var cible= $(this).data('cible');
  if ($(cible).val()=='')
   $(cible).val('†');
  else
   $(cible).val($(cible).val()+' †');
  
});

$(".maj_date_rep").click(function() {
	var jour_rep = $($(this).data('jour_rep')).val();
	var mois_rep = $($(this).data('mois_rep')).val();
	var annee_rep = $($(this).data('annee_rep')).val();
	var date_greg = $(this).data('date_greg');
	var date_rep = $(this).data('date_rep');
	var cmt = $(this).data('cmt');
	var id_fenetre = $(this).data('id_fenetre');
	if (jour_rep!='' && mois_rep!='' && annee_rep!='')
	{
		jour_rep=parseInt(jour_rep);
		mois_rep=parseInt(mois_rep);
		annee_rep=parseInt(annee_rep);
		if (jour_rep!=0)
		{
			if ((mois_rep==13) && jour_rep>6)
		  {
			 alert('Le mois Complementaires ne comporte que 6 jours');
		  }
		  else if(jour_rep>30)
		  {
			 alert('Le mois comporte au maximum 30 jours');
		  }
		  else 
		  {
			 var o_date_rep=new CalRep(jour_rep,mois_rep,annee_rep);
			 o_date_rep.convertir();
			 $(date_greg).val(o_date_rep.getDateGreg());
			 $(cmt).val($(cmt).val()+'° '+o_date_rep.getDateRepNim());
			 $(date_rep).val(o_date_rep.getDateRepNim());
       if ($(date_greg).val()!='')
			 {
				  $(date_greg).val(o_date_rep.getDateGreg());
			 }
			 $(id_fenetre).dialog("close");
		  }
    }
  }
  else
  {
    alert("Le jour, le mois ou l'année révolutionnaire est vide");
  }
});

$(function() {
  $(".popup_date_rep").dialog({
    autoOpen: false,
    width:600,
    height:100,
    closeText: "Fermer",
    title: "MAJ Date Republicaine"
  });
  
  $(".ouvre_popup").on("click", function() {
	var id_fenetre = $(this).data('id_fenetre');  
    $(id_fenetre).dialog("open");
  });

 $(".fermeture_fenetre").click(function(){
    window.close();
});

$('.ouvre_fenetre').click(function (event) {
   event.preventDefault();
 
   var $this = $(this);
   var url = $this.attr("href");
   var windowName = "popUp";
   var windowSize = $this.data("popup");
   window.open(url, windowName, windowSize);
});