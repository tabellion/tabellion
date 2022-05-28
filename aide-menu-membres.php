<?php
require_once __DIR__ . '/app/bootstrap.php';
require_once __DIR__ . '/Origin/PaginationTableau.php';

print('<!DOCTYPE html>');
print("<head>");
print('<meta http-equiv="Content-Type" content="text/html; charset=cp1252" />');
print('<meta http-equiv="content-language" content="fr" /> ');
print('<meta name="viewport" content="width=device-width, initial-scale=1.0">');
print("<link href='assets/css/styles.css' type='text/css' rel='stylesheet'>");
print("<link href='assets/css/bootstrap.min.css' rel='stylesheet'>");
print("<script src='assets/js/jquery-min.js' type='text/javascript'></script>");
print("<script src='assets/js/bootstrap.min.js' type='text/javascript'></script>");
print('</head>');
print("<body>");
print('<div class="container">');

require_once __DIR__ . '/commun/menu.php';

echo ("<img src=\"images/MenuMembre.png\">");
echo ("<img src=\"images/Reinscription.png\">");
echo ("<img src=\"images/NotifierReinscription.png\">");
print("</div></body>");
