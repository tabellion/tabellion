#!/bin/bash
#/var/www/clients/client1/web12/backup/Cron/SvgdeBases_PF.bash 
#racine
cd /
#Copie des bases dans un répertoire de sauvegarde accesible en ftp
cp -uv /var/backup/web12/db_dbgenea16drupal_*.sql.gz /var/www/clients/client1/web12/backup/Sgd_Drupal/ > /var/www/clients/client1/web12/backup/result.txt
cp -uv /var/backup/web12/db_dbgenea16v4_*.sql.gz /var/www/clients/client1/web12/backup/SvgdBD/ >> /var/www/clients/client1/web12/backup/result.txt
cp -uv /var/backup/web24/db_dbgenea16expo_*.sql.gz /var/www/clients/client1/web12/backup/SvgdExpo/ >> /var/www/clients/client1/web12/backup/result.txt
# nettoyage des fichiers de plus d'une semaine
find /var/www/clients/client1/web12/backup/Sgd_Drupal/ -type f -name "AssociationGnalogiquedelaCharente-*" -mtime +7 -exec rm {} \; >> /var/www/clients/client1/web12/backup/result.txt
find /var/www/clients/client1/web12/backup/Sgd_Drupal/ -type f -name "db_dbgenea16drupal_*" -mtime +7 -exec rm {} \; >> /var/www/clients/client1/web12/backup/result.txt
find /var/www/clients/client1/web12/backup/SvgdBD/ -type f -name "db_dbgenea16v4_*" -mtime +7 -exec rm {} \; >> result.txt
find /var/www/clients/client1/web12/backup/SvgdExpo/ -type f -name "db_dbgenea16expo_*" -mtime +7 -exec rm {} \; >> /var/www/clients/client1/web12/backup/result.txt 
# Modification des droits
chown 5004:5004 /var/www/clients/client1/web12/backup/Sgd_Drupal/db_dbgenea16drupal_* >> /var/www/clients/client1/web12/backup/result.txt
chown 5004:5004 /var/www/clients/client1/web12/backup/SvgdBD/db_dbgenea16v4_* >> /var/www/clients/client1/web12/backup/result.txt 
chown 5004:5004 /var/www/clients/client1/web12/backup/SvgdExpo/db_dbgenea16expo_* >> /var/www/clients/client1/web12/backup/result.txt
# envoi du mail
mail -s "cron SvdeBases_PF" pascal.frebot@neuf.fr < /var/www/clients/client1/web12/backup/result.txt