<?php
$config = require_once __DIR__ . '/../../.env';
DEFINE('MERCHANT_ID', $config["MERCHANT_ID"]); // Merchant ID
DEFINE('ACCESS_KEY', $config["ACCESS_KEY"]); // Certificate key

DEFINE('PROXY_HOST', null); // Proxy URL (optional)
DEFINE('PROXY_PORT', null); // Proxy port number without 'quotes' (optional)
DEFINE('PROXY_LOGIN', ''); // Proxy login (optional)
DEFINE('PROXY_PASSWORD', ''); // Proxy password (optional)

DEFINE('PRODUCTION', TRUE); // Demonstration (FALSE) or production (TRUE) mode
