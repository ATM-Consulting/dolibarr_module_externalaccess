<?php

// Need global variable $title to be defined by caller (like dol_loginfunction)
// Caller can also set 	$morelogincontent = array(['options']=>array('js'=>..., 'table'=>...);

// Protection to avoid direct call of template
if (empty($conf) || ! is_object($conf))
{
	print "Error, template page can't be called as URL";
	exit;
}


header('Cache-Control: Public, must-revalidate');
header("Content-type: text/html; charset=".$conf->file->character_set_client);


// If we force to use jmobile, then we reenable javascript
if (! empty($conf->dol_use_jmobile)) $conf->use_javascript_ajax=1;

$php_self = dol_escape_htmltag($_SERVER['PHP_SELF']);
$php_self.= dol_escape_htmltag($_SERVER["QUERY_STRING"])?'?'.dol_escape_htmltag($_SERVER["QUERY_STRING"]):'';

$context = Context::getInstance();
$context->doNotDisplayHeaderBar = 1;
$context->doNotDisplayMenu = 1;

$action = GETPOST('action');

include __DIR__ .'/header.tpl.php';



include __DIR__ . '/menu.tpl.php';

?>

<header class="masthead text-center  d-flex">
<div class="container my-auto">
<?php


require __DIR__ .'/form.login.tpl.php';


?>
</div>
</header>


<?php include __DIR__ .'/footer.tpl.php';



