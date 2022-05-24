<?php
	DEFINE( 'PAYMENT_CURRENCY', 978 ); // Default payment currency (ex: 978 = EURO)
	DEFINE( 'ORDER_CURRENCY', PAYMENT_CURRENCY );
	DEFINE( 'SECURITY_MODE', '' ); // Protocol (ex: SSL = HTTPS)
	DEFINE( 'LANGUAGE_CODE', '' ); // Payline pages language
	DEFINE( 'PAYMENT_ACTION', 101 ); // Default payment method
	DEFINE( 'PAYMENT_MODE', 'CPT' ); // Default payment mode
	DEFINE( 'CANCEL_URL', 'https://adherents.genea16.net/Inscription/index.php'); // Default cancel URL
	DEFINE( 'NOTIFICATION_URL','https://adherents.genea16.net/Adhesion/TraitementAdhesion.php'); // Default notification URL
	DEFINE( 'RETURN_URL', 'https://adherents.genea16.net'); // Default return URL
	DEFINE( 'CUSTOM_PAYMENT_TEMPLATE_URL', ''); // Default payment template URL
	DEFINE( 'CUSTOM_PAYMENT_PAGE_CODE', '' );
	//HOMOLOGATION
    //DEFINE( 'CONTRACT_NUMBER', '1234567' ); // Contract type default (ex: 001 = CB, 003 = American Express...)
	//DEFINE( 'CONTRACT_NUMBER_LIST', '12345678' ); // Contract type multiple values (separator: ;)
	//PRODUCTION
    DEFINE( 'CONTRACT_NUMBER', '3110381' ); // Contract type default (ex: 001 = CB, 003 = American Express...)
	DEFINE( 'CONTRACT_NUMBER_LIST', '3110381;12345678' ); // Contract type multiple values (separator: ;)
  
  DEFINE( 'SECOND_CONTRACT_NUMBER_LIST', '' ); // Contract type multiple values (separator: ;)
	
	// Chemin d'accès au répertoire des fichiers WSDL
	DEFINE( 'WSDL_PATH', './wsdl/' );
		
	// Durées du timeout d'appel des webservices
	DEFINE( 'PRIMARY_CALL_TIMEOUT', 15);
	DEFINE( 'SECONDARY_CALL_TIMEOUT', 15 );
	
	// Nombres de tentatives sur les chaines primaire et secondaire par transaction
	DEFINE( 'PRIMARY_MAX_FAIL_RETRY', 1 );
	DEFINE( 'SECONDARY_MAX_FAIL_RETRY', 2 );
	
	// Durées d'attente avant le rejoue de la transaction
	DEFINE( 'PRIMARY_REPLAY_TIMER', 15 );
	DEFINE( 'SECONDARY_REPLAY_TIMER', 15 );
		
	DEFINE( 'PAYLINE_ERR_CODE', '02101,02102,02103' ); // Codes erreurs payline qui signifie l'échec de la transaction
	DEFINE( 'PAYLINE_WS_SWITCH_ENABLE',  ''); // Nom des services web autorisés à basculer
	DEFINE( 'PAYLINE_SWITCH_BACK_TIMER', 600 ); // Durées d'attente pour rebasculer en mode nominal
	DEFINE( 'PRIMARY_TOKEN_PREFIX', '1' ); // Préfixe du token sur le site primaire
	DEFINE( 'SECONDARY_TOKEN_PREFIX', '2' ); // Préfixe du token sur le site secondaire
	DEFINE( 'INI_FILE' , './properties/HighDefinition.ini'); // Chemin du fichier ini
	DEFINE( 'PAYLINE_ERR_TOKEN', '02317,02318' ); // Préfixe du token sur le site primaire
?>