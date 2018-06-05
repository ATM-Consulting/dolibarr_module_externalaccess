<?php

define('INC_FROM_EXTERNAL_SCRIPT', 1);






require __DIR__.'/../lib/login.lib.php';
require __DIR__.'/../lib/externalaccess.lib.php';

require __DIR__.'/class/context.class.php';


// on cherche le main.inc de dolibarr pas celui de ce module
if(is_file(__DIR__.'/../main.inc.php'))$dir = __DIR__.'/../';
else  if(is_file(__DIR__.'/../../../main.inc.php'))$dir = __DIR__.'/../../../';
else $dir = __DIR__.'/../../';

include(__DIR__."/main.inc.php");

$context = Context::getInstance();

