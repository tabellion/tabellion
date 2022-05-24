 <?php
/*
Donne la liste des derniers fichiers créés ET modifiés.
Très utile en cas de piratage pour savoir quels fichiers sont ajoutés et ceux qui ont été modifiés. Utile pour comprendre le comportement d'un script ou d'un CMS et voir quels fichiers ont été manipulés.

Mettez ce script dans votre hébergement, ouvrez-le avec votre navigateur web, donnez le nombre de jours représentant la période à vérifier, puis le nom du dossier à analyser.
Ce script ne va donner la liste que des dossiers à partir du chemin /home/votreloginftp/www/ de votre hébergement mutualisé chez OVH.

Crédits: Les 4/5 du code sont l'oeuvre de Linda MacPhee-Cobb (http://timestocome.com)
*/

    $go_back = 0;                        // affiche résultat ou non
    $i = 0;                                // compteur de boucle
    $dir_count = 0;                        // initialisation de la boucle
    $date = time();                        // date et heure actuelle
    $one_day = 86400;                    // nombre de secondes pour une journée
    $days = preg_replace("/[^0-9]/i",'', $_POST["jours"]);    // nombre de jours à vérifier
    $path = preg_replace("/[^_A-Za-z0-9-\.%\/]/i",'', $_POST["chemin"]);    // chemin de fichier absolu (avec nettoyage contre piratage)
    $path = preg_replace("/\.\.\//",'', $path);    // on interdit la commande ../
    define('ABSPATH', dirname(__FILE__));
    $path = ABSPATH.$path;    // chemin de fichier absolu de votre compte OVH du genre /home/loginftp/www/ etc.
    $directories_to_read[$dir_count] = $path;
    
    // Formulaire pour remonter le temps
    print "<html><body><h3>Contr&ocirc;le des derniers fichiers modifi&eacute;s <br />dans votre h&eacute;bergement.</h3>";
    print "<table><tr><td>";
    print "<form method=\"post\">";
    print "<tr><td>Nombre de jours &agrave; v&eacute;rifier 1-99: </td>";
    print "<td>&nbsp;&nbsp;<input type=\"text\" name=\"jours\" maxlength=\"2\" size=\"2\"></td></tr>";
    print "<tr><td>Nom du r&eacute;pertoire &agrave; contr&ocirc;ler: </td>";
    print "<td>".ABSPATH." <input type=\"text\" name=\"chemin\" maxlength=\"80\" size=\"30\" value=\"/\" > (mettre un / &agrave; la fin)</td></tr>";
    print "<tr><td> </td><td><input type=\"submit\" value=\" V&eacute;rifier Fichiers \">";
    print "</form>";
    print "</td></tr></table>";
    // Affichage du résultat
    $go_back = $one_day * $days;
    print "<br /> Retour sur les <strong>" . ($go_back/$one_day) ."</strong> derniers jours. <br /><br />";

    if ( $go_back > 0 ){
        print "<table><tr><th>Nom du Fichier</th><th>Date de modification</th></tr>";
        $diff = $date - $go_back;
        
        while ( $i <= $dir_count ){
            $current_directory = $directories_to_read[$i];
        
            // obtenir info fichier
            $read_path = opendir( $directories_to_read[$i] );
            while ( $file_name = readdir( $read_path)){
                if (( $file_name != '.' )&&( $file_name != '..' )){
                    if ( is_dir( $current_directory . "/"  . $file_name ) == "dir" ){
                        // besoin d'obtenir tous les fichiers d'un répertoire
                        $d_file_name = "$current_directory" . "$file_name";
                        $dir_count++;
                        $directories_to_read[$dir_count] = $d_file_name . "/";
                    }else{
                        $file_name = "$current_directory" . "$file_name";                                
                        // Si temps modifiés plus récent que x jours, affiche, sinon, passe
                        if ( (filemtime( $file_name)) > $diff  ){
                            print "<tr><td> $file_name </td>";
                            $date_changed = filemtime( $file_name );
                            $pretty_date = date("d/m/Y H:i:s", $date_changed);
                            print  "<td> ::: $pretty_date</td></tr>" ;
                        }
                    }
                }
            }
            closedir ( $read_path );
            $i++;    
        }
            print "</table>";    
            print "</body></html>";    
    } // if go_back > 0 )            
?> 