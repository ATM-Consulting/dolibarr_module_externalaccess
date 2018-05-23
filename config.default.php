<?php
	
if(is_file(__DIR__.'/../main.inc.php'))$dir = __DIR__.'/../';
else  if(is_file(__DIR__.'/../../../main.inc.php'))$dir = __DIR__.'/../../../';
else $dir = __DIR__.'/../../';


	if(!defined('INC_FROM_DOLIBARR') && defined('INC_FROM_CRON_SCRIPT') ) {
		include($dir."master.inc.php");
	}
	elseif(!defined('INC_FROM_DOLIBARR')) {
		include($dir."main.inc.php");
	} else {
		global $dolibarr_main_db_host, $dolibarr_main_db_name, $dolibarr_main_db_user, $dolibarr_main_db_pass;
	}

	if(!defined('DB_HOST')) {
		define('DB_HOST',$dolibarr_main_db_host);
		define('DB_NAME',$dolibarr_main_db_name);
		define('DB_USER',$dolibarr_main_db_user);
		define('DB_PASS',$dolibarr_main_db_pass);
		define('DB_DRIVER',$dolibarr_main_db_type);
	}

	if(!dol_include_once('/abricot/inc.core.php')) {
		print $langs->trans('AbricotNotFound'). ' : <a href="http://wiki.atm-consulting.fr/index.php/Accueil#Abricot" target="_blank">Abricot</a>';
		exit;
	}

	

