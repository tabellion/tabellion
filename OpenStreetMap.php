<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------
require_once('Commun/config.php');
require_once('Commun/constantes.php');
require_once('Commun/ConnexionBD.php');

$gf_pi=3.14159265359;

if (isset($_GET['idf_commune']))
{
  $gi_idf_commune = (int) $_GET['idf_commune'];
}

else
 die("Erreur: L'identifiant de commune est manquant");

$connexionBD = ConnexionBD::singleton($gst_serveur_bd,$gst_utilisateur_bd,$gst_mdp_utilisateur_bd,$gst_nom_bd);

try {
    list($st_commune,$f_lat_rad,$f_lon_rad)=$connexionBD->sql_select_liste("select nom, latitude,longitude from commune_acte where idf=$gi_idf_commune");
    if (is_null($st_commune))
    {
       $error = "Cette commune n'existe pas";
       throw new Exception($error);
    }
    $f_lat_deg=$f_lat_rad*180/$gf_pi;
    $f_lon_deg=$f_lon_rad*180/$gf_pi;
    
}
catch (Exception $e) {
    die("ERREUR : $e");
}
?>

<!DOCTYPE html>
<html>
    <head>
    <meta http-equiv="content-language" content="fr">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='css/styles.css' type='text/css' rel='stylesheet'>
    <link href='css/bootstrap.min.css' rel='stylesheet'>
    <script src='js/jquery-min.js' type='text/javascript'></script>
    <script src='js/bootstrap.min.js' type='text/javascript'></script>
    <script type='text/javascript'>
    $(document).ready(function() {
	$("#ferme").click(function(){
		window.close();
	});	
});
</script>
        <meta charset="utf-8">
        <!-- Nous chargeons les fichiers CDN de Leaflet. Le CSS AVANT le JS -->
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.3.1/dist/leaflet.css" integrity="sha512-Rksm5RenBEKSKFjgI3a41vrjkw4EVPlJ3+OiI65vTjIdo9brlAacEuKOiQ5OFh7cOI1bkDwLqdLw3Zg0cRJAAQ==" crossorigin="" />
        <style type="text/css">
            #map{ /* la carte DOIT avoir une hauteur sinon elle n'apparaît pas */
                width:800px;
                height:600px;
                background-position:center center;
                background-repeat:no-repeat;
            }
        </style>
       <?php print("<title>API OpenStreeMap - $st_commune </title>");?>
    </head>
    <body>
     <div class="container">
     <div class="text-center"><img src="<?php print($gst_logo_association); ?>"></div>
      <?php print("<div class=\"text-center\">".htmlentities($st_commune,ENT_COMPAT,'cp1252')."</div><br>\n");?> 
       <div align=center>
        <div id="map">
	    <!-- Ici s'affichera la carte -->
	</div>     </div>
   



        <!-- Fichiers Javascript -->
        <script src="https://unpkg.com/leaflet@1.3.1/dist/leaflet.js" integrity="sha512-/Nsx9X4HebavoBvEBuyp3I7od5tA0UzAxs+j83KgC8PU0kgB4XiK4Lfe4y4cgBtaRJQEIFCW+oC506aPT2L1zw==" crossorigin=""></script>
	<script type="text/javascript">
            // On initialise la latitude et la longitude de Paris (centre de la carte)
            var lat = <?php echo json_encode($f_lat_deg); ?>;
            var lon = <?php echo json_encode($f_lon_deg); ?>;
            var ville = <?php echo json_encode($st_commune); ?>;
            var macarte = null;
            // Fonction d'initialisation de la carte
           
            function initMap() {
                // Créer l'objet "macarte" et l'insèrer dans l'élément HTML qui a l'ID "map"
                macarte = L.map('map').setView([lat, lon], 11);
                // Leaflet ne récupère pas les cartes (tiles) sur un serveur par défaut. Nous devons lui préciser où nous souhaitons les récupérer. Ici, openstreetmap.fr
                L.tileLayer('https://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png', {
                    // Il est toujours bien de laisser le lien vers la source des données
                    attribution: 'données © <a href="//osm.org/copyright">OpenStreetMap</a>/ODbL - rendu <a href="//openstreetmap.fr">OSM France</a>',
                    minZoom: 1,
                    maxZoom: 20
                }).addTo(macarte);
                // Nous ajoutons un marqueur
                var marker = L.marker([lat, lon]).addTo(macarte);
                // Nous ajoutons la popup. A noter que son contenu (ici la variable ville) peut être du HTML
				marker.bindPopup(ville);
            }
           
            window.onload = function(){
		// Fonction d'initialisation qui s'exécute lorsque le DOM est chargé
		initMap(); 
            };
        
        </script>
      <div class="form-row">
         <button type="button" id=ferme class="btn btn-warning col-xs-4 col-xs-offset-4">Fermer la fen&ecirc;tre</button>
      </div>'
    </body>
</html>