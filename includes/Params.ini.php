<?php
//Permet l'affichage ou non des erreurs développeurs
//Booléen, true affiche les erreurs, false les masque
define("DEVMODE",true);

//Paramètres de base
//define("DOMAIN","http://127.0.0.1");
define("WEBDIR","complete_url");
define("IMGDIR",WEBDIR."img/");
define("DBPRE","xxx_");

// données pour la connexion à la base de données local
define("DB_HOST","localhost");
define("DB_USER","");
define("DB_PASS","");
define("DB_BASE","");

//Sels de Cryptographie
define('SALT_BEFORE','what');
define('SALT_AFTER','work');

// clés API et paramètres du serveur
define("FB_ID","");
define("FB_KEY","");

//Démarrer les sessions
session_start();

// affiche toutes les erreurs et warnings PHP
ini_set('display_errors',1);
ini_set('error_reporting', E_ALL & ~ E_STRICT);

//Module Login
define('MODLOGIN_ACTIVATE',false);
define('MODLOGIN_OBJECT','');
define('MODLOGIN_LOGIN','');
define('MODLOGIN_PASSWORD','');

//Regexp Courants
define("REGEXP_MAIL", '/^[a-zA-Z0-9]+([\.\-\_][a-zA-Z0-9]+)*\@[a-zA-Z0-9]+([\.\-\_][a-zA-Z0-9]+)*$/');
define("REGEXP_DATE", '/^[0-3][0-9]\/((0[1-9])|(1[0-2]))\/[0-9]{4}$/');

?>

