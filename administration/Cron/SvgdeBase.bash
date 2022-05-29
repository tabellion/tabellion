#!/bin/bash
rep_svgde="/var/www/clients/client1/web3/backup/SvgdBD"
date=`date +%Y%m%d_%H%M%S`
nom_bd="admin_basev4"
fich_svgde="${rep_svgde}/${nom_bd}_${date}.sql.gz"
email_alerte="fbouffanet@yahoo.fr"
mysqldump -u admin -p`cat /etc/psa/.psa.shadow` ${nom_bd} | gzip > ${fich_svgde} 
if [[ $? -eq 0 ]]
then
echo "Sauvegarde faite dans le fichier ${fich_svgde}"
# nettoyage des fichiers de plus d'une semaine
find ${rep_svgde} -type f -name "${nom_bd}*" -mtime +7 -exec rm {} \;
else
# notification si la sauvegarde n'a pu etre faite
echo "Impossible de sauvegarder ${fich_svgde}" | mail -s "Sauvegarde impossible de ${nom_bd}" ${email_alerte}
fi
