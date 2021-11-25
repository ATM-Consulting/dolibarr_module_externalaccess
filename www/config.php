<?php

define('INC_FROM_EXTERNAL_SCRIPT', 1);





require __DIR__.'/lib/login.lib.php';
require __DIR__.'/../lib/externalaccess.lib.php';
require __DIR__.'/lib/externalaccess.lib.php';
require __DIR__.'/class/context.class.php';


// on cherche le main.inc de dolibarr pas celui de ce module
if(is_file(__DIR__.'/../main.inc.php'))$dir = __DIR__.'/../';
else  if(is_file(__DIR__.'/../../../main.inc.php'))$dir = __DIR__.'/../../../';
else $dir = __DIR__.'/../../';

if(defined('INC_FROM_SCRIPT')) {
    include($dir."master.inc.php");
}
else {
    include(__DIR__."/main.inc.php");
}


if (empty($conf->externalaccess->enabled))
{
	header("HTTP/1.0 404 Not Found");
	echo '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN"><html><head><title>Not Found</title></head><body><h1>Not Found</h1></body></html>';
	exit;
}

require __DIR__.'/lib/retroCompatibility.lib.php';


