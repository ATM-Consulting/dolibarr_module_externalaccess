<?php
// For optional tuning. Enabled if environment variable MAIN_SHOW_TUNING_INFO is defined.
$micro_start_time=0;
if (! empty($_SERVER['MAIN_SHOW_TUNING_INFO']))
{
	list($usec, $sec) = explode(" ", microtime());
	$micro_start_time=((float) $usec + (float) $sec);
	// Add Xdebug code coverage
	//define('XDEBUGCOVERAGE',1);
	if (defined('XDEBUGCOVERAGE')) {
		xdebug_start_code_coverage();
	}
}



/**
 * Security: SQL Injection and XSS Injection (scripts) protection (Filters on GET, POST, PHP_SELF).
 *
 * @param		string		$val		Value
 * @param		string		$type		1=GET, 0=POST, 2=PHP_SELF
 * @return		int						>0 if there is an injection, 0 if none
 */
function test_sql_and_script_inject($val, $type)
{
	$inj = 0;
	// For SQL Injection (only GET are used to be included into bad escaped SQL requests)
	if ($type == 1)
	{
		$inj += preg_match('/updatexml\(/i',	 $val);
		$inj += preg_match('/delete\s+from/i',	 $val);
		$inj += preg_match('/create\s+table/i',	 $val);
		$inj += preg_match('/insert\s+into/i', 	 $val);
		$inj += preg_match('/select\s+from/i', 	 $val);
		$inj += preg_match('/into\s+(outfile|dumpfile)/i',  $val);
	}
	if ($type != 2)	// Not common, we can check on POST
	{
		$inj += preg_match('/update.+set.+=/i',  $val);
		$inj += preg_match('/union.+select/i', 	 $val);
		$inj += preg_match('/(\.\.%2f)+/i',		 $val);
	}
	// For XSS Injection done by adding javascript with script
	// This is all cases a browser consider text is javascript:
	// When it found '<script', 'javascript:', '<style', 'onload\s=' on body tag, '="&' on a tag size with old browsers
	// All examples on page: http://ha.ckers.org/xss.html#XSScalc
	// More on https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet
	$inj += preg_match('/<script/i', $val);
	$inj += preg_match('/<iframe/i', $val);
	$inj += preg_match('/<audio/i', $val);
	$inj += preg_match('/Set\.constructor/i', $val);	// ECMA script 6
	if (! defined('NOSTYLECHECK')) $inj += preg_match('/<style/i', $val);
	$inj += preg_match('/base[\s]+href/si', $val);
	$inj += preg_match('/<.*onmouse/si', $val);       // onmousexxx can be set on img or any html tag like <img title='...' onmouseover=alert(1)>
	$inj += preg_match('/onerror\s*=/i', $val);       // onerror can be set on img or any html tag like <img title='...' onerror = alert(1)>
	$inj += preg_match('/onfocus\s*=/i', $val);       // onfocus can be set on input text html tag like <input type='text' value='...' onfocus = alert(1)>
	$inj += preg_match('/onload\s*=/i', $val);        // onload can be set on svg tag <svg/onload=alert(1)> or other tag like body <body onload=alert(1)>
	$inj += preg_match('/onloadstart\s*=/i', $val);   // onload can be set on audio tag <audio onloadstart=alert(1)>
	$inj += preg_match('/onclick\s*=/i', $val);       // onclick can be set on img text html tag like <img onclick = alert(1)>
	$inj += preg_match('/onscroll\s*=/i', $val);      // onscroll can be on textarea
	//$inj += preg_match('/on[A-Z][a-z]+\*=/', $val);   // To lock event handlers onAbort(), ...
	$inj += preg_match('/&#58;|&#0000058|&#x3A/i', $val);		// refused string ':' encoded (no reason to have it encoded) to lock 'javascript:...'
	//if ($type == 1)
	//{
		$inj += preg_match('/javascript:/i', $val);
		$inj += preg_match('/vbscript:/i', $val);
	//}
	// For XSS Injection done by adding javascript closing html tags like with onmousemove, etc... (closing a src or href tag with not cleaned param)
	if ($type == 1) $inj += preg_match('/"/i', $val);		// We refused " in GET parameters value
	if ($type == 2) $inj += preg_match('/[;"]/', $val);		// PHP_SELF is a file system path. It can contains spaces.
	return $inj;
}

/**
 * Return true if security check on parameters are OK, false otherwise.
 *
 * @param		string			$var		Variable name
 * @param		string			$type		1=GET, 0=POST, 2=PHP_SELF
 * @return		boolean|null				true if there is no injection. Stop code if injection found.
 */
function analyseVarsForSqlAndScriptsInjection(&$var, $type)
{
	if (is_array($var))
	{
		foreach ($var as $key => $value)	// Warning, $key may also be used for attacks
		{
			if (analyseVarsForSqlAndScriptsInjection($key, $type) && analyseVarsForSqlAndScriptsInjection($value, $type))
			{
				//$var[$key] = $value;	// This is useless
			}
			else
			{
				print 'Access refused by SQL/Script injection protection in main.inc.php (type='.htmlentities($type).' key='.htmlentities($key).' value='.htmlentities($value).' page='.htmlentities($_SERVER["REQUEST_URI"]).')';
				exit;
			}
		}
		return true;
	}
	else
	{
		return (test_sql_and_script_inject($var, $type) <= 0);
	}
}


// Check consistency of NOREQUIREXXX DEFINES
if ((defined('NOREQUIREDB') || defined('NOREQUIRETRAN')) && ! defined('NOREQUIREMENU'))
{
	print 'If define NOREQUIREDB or NOREQUIRETRAN are set, you must also set NOREQUIREMENU or not set them';
	exit;
}

// Sanity check on URL
if (! empty($_SERVER["PHP_SELF"]))
{
	$morevaltochecklikepost=array($_SERVER["PHP_SELF"]);
	analyseVarsForSqlAndScriptsInjection($morevaltochecklikepost,2);
}
// Sanity check on GET parameters
if (! defined('NOSCANGETFORINJECTION') && ! empty($_SERVER["QUERY_STRING"]))
{
	$morevaltochecklikeget=array($_SERVER["QUERY_STRING"]);
	analyseVarsForSqlAndScriptsInjection($morevaltochecklikeget,1);
}
// Sanity check on POST
if (! defined('NOSCANPOSTFORINJECTION'))
{
	analyseVarsForSqlAndScriptsInjection($_POST,0);
}

// This is to make Dolibarr working with Plesk
if (! empty($_SERVER['DOCUMENT_ROOT']) && substr($_SERVER['DOCUMENT_ROOT'], -6) !== 'htdocs')
{
	set_include_path($_SERVER['DOCUMENT_ROOT'] . '/htdocs');
}

// Include the conf.php and functions.lib.php
require_once   $dir.'/filefunc.inc.php';

// If there is a POST parameter to tell to save automatically some POST parameters into cookies, we do it.
// This is used for example by form of boxes to save personalization of some options.
// DOL_AUTOSET_COOKIE=cookiename:val1,val2 and  cookiename_val1=aaa cookiename_val2=bbb will set cookie_name with value json_encode(array('val1'=> , ))
if (! empty($_POST["DOL_AUTOSET_COOKIE"]))
{
	$tmpautoset=explode(':',$_POST["DOL_AUTOSET_COOKIE"],2);
	$tmplist=explode(',',$tmpautoset[1]);
	$cookiearrayvalue=array();
	foreach ($tmplist as $tmpkey)
	{
		$postkey=$tmpautoset[0].'_'.$tmpkey;
		//var_dump('tmpkey='.$tmpkey.' postkey='.$postkey.' value='.$_POST[$postkey]);
		if (! empty($_POST[$postkey])) $cookiearrayvalue[$tmpkey]=$_POST[$postkey];
	}
	$cookiename=$tmpautoset[0];
	$cookievalue=json_encode($cookiearrayvalue);
	//var_dump('setcookie cookiename='.$cookiename.' cookievalue='.$cookievalue);
	setcookie($cookiename, empty($cookievalue)?'':$cookievalue, empty($cookievalue)?0:(time()+(86400*354)), '/', null, false, true);	// keep cookie 1 year and add tag httponly
	if (empty($cookievalue)) unset($_COOKIE[$cookiename]);
}


// Init session. Name of session is specific to Dolibarr instance.
// Note: the function dol_getprefix may have been redefined to return a different key to manage another area to protect.
$prefix=dol_getprefix('');

$sessionname='DOLSESSID_'.$prefix;
$sessiontimeout='DOLSESSTIMEOUT_'.$prefix;
if (! empty($_COOKIE[$sessiontimeout])) ini_set('session.gc_maxlifetime',$_COOKIE[$sessiontimeout]);
session_name($sessionname);
session_set_cookie_params(0, '/', null, false, true);   // Add tag httponly on session cookie (same as setting session.cookie_httponly into php.ini). Must be called before the session_start.
// This create lock, released when session_write_close() or end of page.
// We need this lock as long as we read/write $_SESSION ['vars']. We can remove lock when finished.
if (! defined('NOSESSION'))
{
	session_start();
	if (ini_get('register_globals'))    // Deprecated in 5.3 and removed in 5.4. To solve bug in using $_SESSION
	{
		foreach ($_SESSION as $key=>$value)
		{
			if (isset($GLOBALS[$key])) unset($GLOBALS[$key]);
		}
	}
}

// Init the 5 global objects, this include will make the new and set properties for: $conf, $db, $langs, $user, $mysoc
require_once $dir.'/master.inc.php';

// Activate end of page function
register_shutdown_function('dol_shutdown');

// Detection browser
if (isset($_SERVER["HTTP_USER_AGENT"]))
{
	$tmp=getBrowserInfo($_SERVER["HTTP_USER_AGENT"]);
	$conf->browser->name=$tmp['browsername'];
	$conf->browser->os=$tmp['browseros'];
	$conf->browser->version=$tmp['browserversion'];
	$conf->browser->layout=$tmp['layout'];     // 'classic', 'phone', 'tablet'
	$conf->browser->phone=$tmp['phone'];	   // TODO deprecated, use ->layout
	$conf->browser->tablet=$tmp['tablet'];	   // TODO deprecated, use ->layout
	//var_dump($conf->browser);

	if ($conf->browser->layout == 'phone') $conf->dol_no_mouse_hover=1;
	if ($conf->browser->layout == 'phone') $conf->global->MAIN_TESTMENUHIDER=1;
}

// Force HTTPS if required ($conf->file->main_force_https is 0/1 or https dolibarr root url)
// $_SERVER["HTTPS"] is 'on' when link is https, otherwise $_SERVER["HTTPS"] is empty or 'off'
if (! empty($conf->file->main_force_https) && (empty($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] != 'on'))
{
	$newurl='';
	if (is_numeric($conf->file->main_force_https))
	{
		if ($conf->file->main_force_https == '1' && ! empty($_SERVER["SCRIPT_URI"]))	// If SCRIPT_URI supported by server
		{
			if (preg_match('/^http:/i',$_SERVER["SCRIPT_URI"]) && ! preg_match('/^https:/i',$_SERVER["SCRIPT_URI"]))	// If link is http
			{
				$newurl=preg_replace('/^http:/i','https:',$_SERVER["SCRIPT_URI"]);
			}
		}
		else	// Check HTTPS environment variable (Apache/mod_ssl only)
		{
			$newurl=preg_replace('/^http:/i','https:',DOL_MAIN_URL_ROOT).$_SERVER["REQUEST_URI"];
		}
	}
	else
	{
		// Check HTTPS environment variable (Apache/mod_ssl only)
		$newurl=$conf->file->main_force_https.$_SERVER["REQUEST_URI"];
	}
	// Start redirect
	if ($newurl)
	{
		dol_syslog("main.inc: dolibarr_main_force_https is on, we make a redirect to ".$newurl);
		header("Location: ".$newurl);
		exit;
	}
	else
	{
		dol_syslog("main.inc: dolibarr_main_force_https is on but we failed to forge new https url so no redirect is done", LOG_WARNING);
	}
}

/*
 * Ajout du Context et des controllers avant la partie login
 */
$context = Context::getInstance();

// Loading of additional presentation includes
if (! defined('NOREQUIREHTML')) require_once DOL_DOCUMENT_ROOT .'/core/class/html.form.class.php';	    // Need 660ko memory (800ko in 2.2)
if (! defined('NOREQUIREAJAX') && $conf->use_javascript_ajax) require_once DOL_DOCUMENT_ROOT.'/core/lib/ajax.lib.php';	// Need 22ko memory

// If install or upgrade process not done or not completely finished, we call the install page.
if (! empty($conf->global->MAIN_NOT_INSTALLED) || ! empty($conf->global->MAIN_NOT_UPGRADED))
{
	dol_syslog("main.inc: A previous install or upgrade was not complete. Redirect to install page.", LOG_WARNING);
	header("Location: ".$context->rootUrl."/install/index.php");
	exit;
}
// If an upgrade process is required, we call the install page.
if ((! empty($conf->global->MAIN_VERSION_LAST_UPGRADE) && ($conf->global->MAIN_VERSION_LAST_UPGRADE != DOL_VERSION))
|| (empty($conf->global->MAIN_VERSION_LAST_UPGRADE) && ! empty($conf->global->MAIN_VERSION_LAST_INSTALL) && ($conf->global->MAIN_VERSION_LAST_INSTALL != DOL_VERSION)))
{
	$versiontocompare=empty($conf->global->MAIN_VERSION_LAST_UPGRADE)?$conf->global->MAIN_VERSION_LAST_INSTALL:$conf->global->MAIN_VERSION_LAST_UPGRADE;
	require_once DOL_DOCUMENT_ROOT .'/core/lib/admin.lib.php';
	$dolibarrversionlastupgrade=preg_split('/[.-]/',$versiontocompare);
	$dolibarrversionprogram=preg_split('/[.-]/',DOL_VERSION);
	$rescomp=versioncompare($dolibarrversionprogram,$dolibarrversionlastupgrade);
	if ($rescomp > 0)   // Programs have a version higher than database. We did not add "&& $rescomp < 3" because we want upgrade process for build upgrades
	{
		dol_syslog("main.inc: database version ".$versiontocompare." is lower than programs version ".DOL_VERSION.". Redirect to install page.", LOG_WARNING);
		header("Location: ".$context->rootUrl."/install/index.php");
		exit;
	}
}

// Creation of a token against CSRF vulnerabilities
if (! defined('NOTOKENRENEWAL'))
{
	// roulement des jetons car cree a chaque appel
	if (isset($_SESSION['newtoken'])) $_SESSION['token'] = $_SESSION['newtoken'];

	// Save in $_SESSION['newtoken'] what will be next token. Into forms, we will add param token = $_SESSION['newtoken']
	$token = dol_hash(uniqid(mt_rand(),TRUE)); // Generates a hash of a random number
	$_SESSION['newtoken'] = $token;
}
if ((! defined('NOCSRFCHECK') && empty($dolibarr_nocsrfcheck) && ! empty($conf->global->MAIN_SECURITY_CSRF_WITH_TOKEN))
	|| defined('CSRFCHECK_WITH_TOKEN'))	// Check validity of token, only if option MAIN_SECURITY_CSRF_WITH_TOKEN enabled or if constant CSRFCHECK_WITH_TOKEN is set
{
	if ($_SERVER['REQUEST_METHOD'] == 'POST' && ! GETPOST('token','alpha')) // Note, offender can still send request by GET
	{
		print "Access refused by CSRF protection in main.inc.php. Token not provided.\n";
		print "If you access your server behind a proxy using url rewriting, you might check that all HTTP header is propagated (or add the line \$dolibarr_nocsrfcheck=1 into your conf.php file).\n";
		die;
	}
	if ($_SERVER['REQUEST_METHOD'] === 'POST')  // This test must be after loading $_SESSION['token'].
	{
		if (GETPOST('token', 'alpha') != $_SESSION['token'])
		{
			dol_syslog("Invalid token in ".$_SERVER['HTTP_REFERER'].", action=".GETPOST('action','aZ09').", _POST['token']=".GETPOST('token','alpha').", _SESSION['token']=".$_SESSION['token'], LOG_WARNING);
			//print 'Unset POST by CSRF protection in main.inc.php.';	// Do not output anything because this create problems when using the BACK button on browsers.
			unset($_POST);
		}
	}
}

// Disable modules (this must be after session_start and after conf has been loaded)
if (GETPOST('disablemodules','alpha'))  $_SESSION["disablemodules"]=GETPOST('disablemodules','alpha');
if (! empty($_SESSION["disablemodules"]))
{
	$disabled_modules=explode(',',$_SESSION["disablemodules"]);
	foreach($disabled_modules as $module)
	{
		if ($module)
		{
			if (empty($conf->$module)) $conf->$module=new stdClass();
			$conf->$module->enabled=false;
			if ($module == 'fournisseur')		// Special case
			{
				$conf->supplier_order->enabled=0;
				$conf->supplier_invoice->enabled=0;
			}
		}
	}
}



/*
 * Phase authentication / login
 */
$langs->load('externalaccess@externalaccess');
$login='';
if (! defined('NOLOGIN') && !empty($context->controllerInstance->accessNeedLoggedUser))
{
	// $authmode lists the different means of identification to be tested in order of preference.
	// Example: 'http', 'dolibarr', 'ldap', 'http,forceuser', '...'

	if (defined('MAIN_AUTHENTICATION_MODE'))
	{
		$dolibarr_main_authentication = constant('MAIN_AUTHENTICATION_MODE');
	}
	else
	{
		// Authentication mode
		if (empty($dolibarr_main_authentication)) $dolibarr_main_authentication='http,dolibarr';
		// Authentication mode: forceuser
		if ($dolibarr_main_authentication == 'forceuser' && empty($dolibarr_auto_user)) $dolibarr_auto_user='auto';
	}
	// Set authmode
	$authmode=explode(',',$dolibarr_main_authentication);

	// No authentication mode
	if (! count($authmode))
	{
		$langs->load('main');
		dol_print_error('',$langs->trans("ErrorConfigParameterNotDefined",'dolibarr_main_authentication'));
		exit;
	}

	// If login request was already post, we retrieve login from the session
	// Call module if not realized that his request.
	// At the end of this phase, the variable $login is defined.
	$resultFetchUser='';
	$test=true;
	if (! isset($_SESSION["dol_login"]))
	{
		// It is not already authenticated and it requests the login / password
		include_once DOL_DOCUMENT_ROOT.'/core/lib/security2.lib.php';

		$dol_dst_observed=GETPOST("dst_observed",'int',3);
		$dol_dst_first=GETPOST("dst_first",'int',3);
		$dol_dst_second=GETPOST("dst_second",'int',3);
		$dol_screenwidth=GETPOST("screenwidth",'int',3);
		$dol_screenheight=GETPOST("screenheight",'int',3);
		$dol_hide_topmenu=GETPOST('dol_hide_topmenu','int',3);
		$dol_hide_leftmenu=GETPOST('dol_hide_leftmenu','int',3);
		$dol_optimize_smallscreen=GETPOST('dol_optimize_smallscreen','int',3);
		$dol_no_mouse_hover=GETPOST('dol_no_mouse_hover','int',3);
		$dol_use_jmobile=GETPOST('dol_use_jmobile','int',3);
		//dol_syslog("POST key=".join(array_keys($_POST),',').' value='.join($_POST,','));

		// If in demo mode, we check we go to home page through the public/demo/index.php page
		if (! empty($dolibarr_main_demo) && $_SERVER['PHP_SELF'] == $context->rootUrl.'/index.php')  // We ask index page
		{
			if (empty($_SERVER['HTTP_REFERER']) || ! preg_match('/public/',$_SERVER['HTTP_REFERER']))
			{
				dol_syslog("Call index page from another url than demo page (call is done from page ".$_SERVER['HTTP_REFERER'].")");
				$url='';
				$url.=($url?'&':'').($dol_hide_topmenu?'dol_hide_topmenu='.$dol_hide_topmenu:'');
				$url.=($url?'&':'').($dol_hide_leftmenu?'dol_hide_leftmenu='.$dol_hide_leftmenu:'');
				$url.=($url?'&':'').($dol_optimize_smallscreen?'dol_optimize_smallscreen='.$dol_optimize_smallscreen:'');
				$url.=($url?'&':'').($dol_no_mouse_hover?'dol_no_mouse_hover='.$dol_no_mouse_hover:'');
				$url.=($url?'&':'').($dol_use_jmobile?'dol_use_jmobile='.$dol_use_jmobile:'');
				$url=$context->rootUrl.'/public/demo/index.php'.($url?'?'.$url:'');
				header("Location: ".$url);
				exit;
			}
		}

		// Verification security graphic code
		if (GETPOST("username","alpha",2) && ! empty($conf->global->MAIN_SECURITY_ENABLECAPTCHA))
		{
			$sessionkey = 'dol_antispam_value';
			$ok=(array_key_exists($sessionkey, $_SESSION) === TRUE && (strtolower($_SESSION[$sessionkey]) == strtolower($_POST['code'])));

			// Check code
			if (! $ok)
			{
				dol_syslog('Bad value for code, connexion refused');
				$langs->load('main');
				$langs->load('errors');

				$_SESSION["dol_loginmesg"]=$langs->trans("ErrorBadValueForCode");
				$test=false;

				// TODO @deprecated Remove this. Hook must be used, not this trigger.
				$user->trigger_mesg='ErrorBadValueForCode - login='.GETPOST("username","alpha",2);
				// Call of triggers
				include_once DOL_DOCUMENT_ROOT . '/core/class/interfaces.class.php';
				$interface=new Interfaces($db);
				$result=$interface->run_triggers('USER_LOGIN_FAILED',$user,$user,$langs,$conf);
				if ($result < 0) {
					$error++;
				}
				// End Call of triggers

				// Hooks on failed login
				$action='';
				$hookmanager->initHooks(array('login'));
				$parameters=array('dol_authmode'=>$dol_authmode, 'dol_loginmesg'=>$_SESSION["dol_loginmesg"]);
				$reshook=$hookmanager->executeHooks('afterLoginFailed',$parameters,$user,$action);    // Note that $action and $object may have been modified by some hooks
				if ($reshook < 0) $error++;

				// Note: exit is done later
			}
		}

		$usertotest		= (! empty($_COOKIE['login_dolibarr']) ? $_COOKIE['login_dolibarr'] : GETPOST("username","alpha",2));
		$passwordtotest	= GETPOST('password','none',2);
		$entitytotest	= (GETPOST('entity','int') ? GETPOST('entity','int') : (!empty($conf->entity) ? $conf->entity : 1));

		// Define if we received data to test the login.
		$goontestloop=false;
		if (isset($_SERVER["REMOTE_USER"]) && in_array('http',$authmode)) $goontestloop=true;
		if ($dolibarr_main_authentication == 'forceuser' && ! empty($dolibarr_auto_user)) $goontestloop=true;
		if (GETPOST("username","alpha",2) || ! empty($_COOKIE['login_dolibarr']) || GETPOST('openid_mode','alpha',1)) $goontestloop=true;

		if (! is_object($langs)) // This can occurs when calling page with NOREQUIRETRAN defined, however we need langs for error messages.
		{
			include_once DOL_DOCUMENT_ROOT.'/core/class/translate.class.php';
			$langs=new Translate("",$conf);
			$langcode=(GETPOST('lang','aZ09',1)?GETPOST('lang','aZ09',1):(empty($conf->global->MAIN_LANG_DEFAULT)?'auto':$conf->global->MAIN_LANG_DEFAULT));
			if (defined('MAIN_LANG_DEFAULT')) $langcode=constant('MAIN_LANG_DEFAULT');
			$langs->setDefaultLang($langcode);
		}

		// Validation of login/pass/entity
		// If ok, the variable login will be returned
		// If error, we will put error message in session under the name dol_loginmesg
		if ($test && $goontestloop)
		{
			$login = checkLoginPassEntity($usertotest,$passwordtotest,$entitytotest,$authmode);
			if ($login)
			{
				$dol_authmode=$conf->authmode;	// This properties is defined only when logged, to say what mode was successfully used
				$dol_tz=$_POST["tz"];
				$dol_tz_string=$_POST["tz_string"];
				$dol_tz_string=preg_replace('/\s*\(.+\)$/','',$dol_tz_string);
				$dol_tz_string=preg_replace('/,/','/',$dol_tz_string);
				$dol_tz_string=preg_replace('/\s/','_',$dol_tz_string);
				$dol_dst=0;
				if (isset($_POST["dst_first"]) && isset($_POST["dst_second"]))
				{
					include_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
					$datenow=dol_now();
					$datefirst=dol_stringtotime($_POST["dst_first"]);
					$datesecond=dol_stringtotime($_POST["dst_second"]);
					if ($datenow >= $datefirst && $datenow < $datesecond) $dol_dst=1;
				}
				//print $datefirst.'-'.$datesecond.'-'.$datenow.'-'.$dol_tz.'-'.$dol_tzstring.'-'.$dol_dst; exit;
			}

			if (! $login)
			{
				dol_syslog('Bad password, connexion refused',LOG_DEBUG);
				$langs->load('main');
				$langs->load('errors');

				// Bad password. No authmode has found a good password.
				// We set a generic message if not defined inside function checkLoginPassEntity or subfunctions
				if (empty($_SESSION["dol_loginmesg"])) $_SESSION["dol_loginmesg"]=$langs->trans("ErrorBadLoginPassword");

				// TODO @deprecated Remove this. Hook must be used, not this trigger.
				$user->trigger_mesg=$langs->trans("ErrorBadLoginPassword").' - login='.GETPOST("username","alpha",2);
				// Call of triggers
				include_once DOL_DOCUMENT_ROOT.'/core/class/interfaces.class.php';
				$interface=new Interfaces($db);
				$result=$interface->run_triggers('USER_LOGIN_FAILED',$user,$user,$langs,$conf,GETPOST("username","alpha",2));
				if ($result < 0) {
					$error++;
				}
				// End Call of triggers

				// Hooks on failed login
				$action='';
				$hookmanager->initHooks(array('login'));
				$parameters=array('dol_authmode'=>$dol_authmode, 'dol_loginmesg'=>$_SESSION["dol_loginmesg"]);
				$reshook=$hookmanager->executeHooks('afterLoginFailed',$parameters,$user,$action);    // Note that $action and $object may have been modified by some hooks
				if ($reshook < 0) $error++;

				// Note: exit is done in next chapter
			}
		}

		// End test login / passwords
		if (! $login || (in_array('ldap',$authmode) && empty($passwordtotest)))	// With LDAP we refused empty password because some LDAP are "opened" for anonymous access so connexion is a success.
		{
			// No data to test login, so we show the login page
			dol_syslog("--- Access to ".$_SERVER["PHP_SELF"]." showing the login form and exit");
			if (defined('NOREDIRECTBYMAINTOLOGIN')) return 'ERROR_NOT_LOGGED';
			else dol_loginfunction($langs,$conf,(! empty($mysoc)?$mysoc:''));
			exit;
		}

		$resultFetchUser=$user->fetch('', $login, '', 1, ($entitytotest > 0 ? $entitytotest : -1));
		if ($resultFetchUser <= 0)
		{
			dol_syslog('User not found, connexion refused');
			session_destroy();
			session_name($sessionname);
			session_set_cookie_params(0, '/', null, false, true);   // Add tag httponly on session cookie
			session_start();    // Fixing the bug of register_globals here is useless since session is empty

			if ($resultFetchUser == 0)
			{
				$langs->load('main');
				$langs->load('errors');

				$_SESSION["dol_loginmesg"]=$langs->trans("ErrorCantLoadUserFromDolibarrDatabase",$login);

				// TODO @deprecated Remove this. Hook must be used, not this trigger.
				$user->trigger_mesg='ErrorCantLoadUserFromDolibarrDatabase - login='.$login;
			}
			if ($resultFetchUser < 0)
			{
				$_SESSION["dol_loginmesg"]=$user->error;

				// TODO @deprecated Remove this. Hook must be used, not this trigger.
				$user->trigger_mesg=$user->error;
			}

			// Call triggers
			include_once DOL_DOCUMENT_ROOT . '/core/class/interfaces.class.php';
			$interface=new Interfaces($db);
			$result=$interface->run_triggers('USER_LOGIN_FAILED',$user,$user,$langs,$conf);
			if ($result < 0) {
				$error++;
			}
			// End call triggers

			// Hooks on failed login
			$action='';
			$hookmanager->initHooks(array('login'));
			$parameters=array('dol_authmode'=>$dol_authmode, 'dol_loginmesg'=>$_SESSION["dol_loginmesg"]);
			$reshook=$hookmanager->executeHooks('afterLoginFailed',$parameters,$user,$action);    // Note that $action and $object may have been modified by some hooks
			if ($reshook < 0) $error++;

			$paramsurl=array();
			if (GETPOST('textbrowser','int')) $paramsurl[]='textbrowser='.GETPOST('textbrowser','int');
			if (GETPOST('nojs','int'))        $paramsurl[]='nojs='.GETPOST('nojs','int');
			if (GETPOST('lang','aZ09'))       $paramsurl[]='lang='.GETPOST('lang','aZ09');
			header('Location: '.$context->rootUrl.'/index.php'.(count($paramsurl)?'?'.implode('&',$paramsurl):''));
			exit;
		}
	}
	else
	{
		// We are already into an authenticated session
		$login=$_SESSION["dol_login"];
		$entity=$_SESSION["dol_entity"];
		dol_syslog("- This is an already logged session. _SESSION['dol_login']=".$login." _SESSION['dol_entity']=".$entity, LOG_DEBUG);

		$resultFetchUser=$user->fetch('', $login, '', 1, ($entity > 0 ? $entity : -1));
		if ($resultFetchUser <= 0)
		{
			// Account has been removed after login
			dol_syslog("Can't load user even if session logged. _SESSION['dol_login']=".$login, LOG_WARNING);
			session_destroy();
			session_name($sessionname);
			session_set_cookie_params(0, '/', null, false, true);   // Add tag httponly on session cookie
			session_start();    // Fixing the bug of register_globals here is useless since session is empty

			if ($resultFetchUser == 0)
			{
				$langs->load('main');
				$langs->load('errors');

				$_SESSION["dol_loginmesg"]=$langs->trans("ErrorCantLoadUserFromDolibarrDatabase",$login);

				// TODO @deprecated Remove this. Hook must be used, not this trigger.
				$user->trigger_mesg='ErrorCantLoadUserFromDolibarrDatabase - login='.$login;
			}
			if ($resultFetchUser < 0)
			{
				$_SESSION["dol_loginmesg"]=$user->error;

				// TODO @deprecated Remove this. Hook must be used, not this trigger.
				$user->trigger_mesg=$user->error;
			}

			// TODO @deprecated Remove this. Hook must be used, not this trigger.
			// Call triggers
			include_once DOL_DOCUMENT_ROOT . '/core/class/interfaces.class.php';
			$interface=new Interfaces($db);
			$result=$interface->run_triggers('USER_LOGIN_FAILED',$user,$user,$langs,$conf);
			if ($result < 0) {
				$error++;
			}
			// End call triggers

			// Hooks on failed login
			$action='';
			$hookmanager->initHooks(array('login'));
			$parameters=array('dol_authmode'=>$dol_authmode, 'dol_loginmesg'=>$_SESSION["dol_loginmesg"]);
			$reshook=$hookmanager->executeHooks('afterLoginFailed',$parameters,$user,$action);    // Note that $action and $object may have been modified by some hooks
			if ($reshook < 0) $error++;

			$paramsurl=array();
			if (GETPOST('textbrowser','int')) $paramsurl[]='textbrowser='.GETPOST('textbrowser','int');
			if (GETPOST('nojs','int'))        $paramsurl[]='nojs='.GETPOST('nojs','int');
			if (GETPOST('lang','aZ09'))       $paramsurl[]='lang='.GETPOST('lang','aZ09');
			header('Location: '.$context->rootUrl.'/index.php'.(count($paramsurl)?'?'.implode('&',$paramsurl):''));
			exit;
		}
		else
		{
		    // Initialize technical object to manage hooks of page. Note that conf->hooks_modules contains array of hook context
		    $hookmanager->initHooks(array('main'));

		    // Code for search criteria persistence.
		    if (! empty($_GET['save_lastsearch_values']))    // Keep $_GET here
		    {
			    $relativepathstring = preg_replace('/\?.*$/','',$_SERVER["HTTP_REFERER"]);
			    $relativepathstring = preg_replace('/^https?:\/\/[^\/]*/','',$relativepathstring);     // Get full path except host server
			    // Clean $relativepathstring
			    if ($context->rootUrl) $relativepathstring = preg_replace('/^'.preg_quote($context->rootUrl,'/').'/', '', $relativepathstring);
			    $relativepathstring = preg_replace('/^\//', '', $relativepathstring);
			    $relativepathstring = preg_replace('/^custom\//', '', $relativepathstring);
			    //var_dump($relativepathstring);

			    // We click on a link that leave a page we have to save search criteria. We save them from tmp to no tmp
			    if (! empty($_SESSION['lastsearch_values_tmp_'.$relativepathstring]))
			    {
				    $_SESSION['lastsearch_values_'.$relativepathstring]=$_SESSION['lastsearch_values_tmp_'.$relativepathstring];
				    unset($_SESSION['lastsearch_values_tmp_'.$relativepathstring]);
			    }
		    }

		    $action = '';
		    $reshook = $hookmanager->executeHooks('updateSession', array(), $user, $action);
		    if ($reshook < 0) {
			    setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
		    }
		}
	}

	// Is it a new session that has started ?
	// If we are here, this means authentication was successfull.
	if (! isset($_SESSION["dol_login"]))
	{
		// New session for this login has started.
		$error=0;

		// Store value into session (values always stored)
		$_SESSION["dol_login"]=$user->login;
		$_SESSION["dol_authmode"]=isset($dol_authmode)?$dol_authmode:'';
		$_SESSION["dol_tz"]=isset($dol_tz)?$dol_tz:'';
		$_SESSION["dol_tz_string"]=isset($dol_tz_string)?$dol_tz_string:'';
		$_SESSION["dol_dst"]=isset($dol_dst)?$dol_dst:'';
		$_SESSION["dol_dst_observed"]=isset($dol_dst_observed)?$dol_dst_observed:'';
		$_SESSION["dol_dst_first"]=isset($dol_dst_first)?$dol_dst_first:'';
		$_SESSION["dol_dst_second"]=isset($dol_dst_second)?$dol_dst_second:'';
		$_SESSION["dol_screenwidth"]=isset($dol_screenwidth)?$dol_screenwidth:'';
		$_SESSION["dol_screenheight"]=isset($dol_screenheight)?$dol_screenheight:'';
		$_SESSION["dol_company"]=$conf->global->MAIN_INFO_SOCIETE_NOM;
		$_SESSION["dol_entity"]=$conf->entity;
		// Store value into session (values stored only if defined)
		if (! empty($dol_hide_topmenu))         $_SESSION['dol_hide_topmenu']=$dol_hide_topmenu;
		if (! empty($dol_hide_leftmenu))        $_SESSION['dol_hide_leftmenu']=$dol_hide_leftmenu;
		if (! empty($dol_optimize_smallscreen)) $_SESSION['dol_optimize_smallscreen']=$dol_optimize_smallscreen;
		if (! empty($dol_no_mouse_hover))       $_SESSION['dol_no_mouse_hover']=$dol_no_mouse_hover;
		if (! empty($dol_use_jmobile))          $_SESSION['dol_use_jmobile']=$dol_use_jmobile;

		dol_syslog("This is a new started user session. _SESSION['dol_login']=".$_SESSION["dol_login"]." Session id=".session_id());

		$db->begin();

		$user->update_last_login_date();

		$loginfo = 'TZ='.$_SESSION["dol_tz"].';TZString='.$_SESSION["dol_tz_string"].';Screen='.$_SESSION["dol_screenwidth"].'x'.$_SESSION["dol_screenheight"];


		// Hooks on successfull login
		$action='';
		$hookmanager->initHooks(array('externallogin'));
		$parameters=array('dol_authmode'=>$dol_authmode, 'dol_loginfo'=>$loginfo);
		$reshook=$hookmanager->executeHooks('afterLogin',$parameters,$user,$action);    // Note that $action and $object may have been modified by some hooks
		if ($reshook < 0) $error++;

		if ($error)
		{
			$db->rollback();
			session_destroy();
			dol_print_error($db,'Error in some hooks afterLogin (or old trigger USER_LOGIN)');
			exit;
		}
		else
		{
			$db->commit();
		}

		// Change landing page if defined.
		if (GETPOST('urlfrom', 'none')){
			$landingpage = GETPOST('urlfrom', 'none');
		}
		elseif(!empty($_SESSION["urlfrom"])){
			$landingpage = $_SESSION["urlfrom"];
			unset($_SESSION["urlfrom"]);
		}
		else{
			$landingpage = false;
		}

		if (! empty($landingpage))    // Example: /index.php
		{

			if (Context::urlOrigin() != $landingpage)   // not already on landing page (avoid infinite loop)
			{
				header('Location: '.$landingpage);
				exit;
			}
		}
	}


	// If user admin, we force the rights-based modules
	if ($user->admin)
	{
		$user->rights->user->user->lire=1;
		$user->rights->user->user->creer=1;
		$user->rights->user->user->password=1;
		$user->rights->user->user->supprimer=1;
		$user->rights->user->self->creer=1;
		$user->rights->user->self->password=1;
	}


}






if (! defined('NOREQUIRETRAN'))
{
	if (! GETPOST('lang','aZ09'))	// If language was not forced on URL
	{
		// If user has chosen its own language
		if (! empty($user->conf->MAIN_LANG_DEFAULT))
		{
			// If different than current language
			//print ">>>".$langs->getDefaultLang()."-".$user->conf->MAIN_LANG_DEFAULT;
			if ($langs->getDefaultLang() != $user->conf->MAIN_LANG_DEFAULT)
			{
				$langs->setDefaultLang($user->conf->MAIN_LANG_DEFAULT);
			}
		}
	}
}

if (! defined('NOLOGIN') && !empty($context->controllerInstance->accessNeedLoggedUser))
{
	// If the login is not recovered, it is identified with an account that does not exist.
	// Hacking attempt?
	if (! $user->login) accessforbidden();

	// Check if user is active
	if ($user->statut < 1)
	{
		// If not active, we refuse the user
		$langs->load("other");
		dol_syslog("Authentification ko as login is disabled");
		accessforbidden($langs->trans("ErrorLoginDisabled"));
		exit;
	}

	// Load permissions
	$user->getrights();
}


dol_syslog("--- Access to ".$_SERVER["PHP_SELF"].' - action='.GETPOST('action','az09').', massaction='.GETPOST('massaction','az09'));
//Another call for easy debugg
//dol_syslog("Access to ".$_SERVER["PHP_SELF"].' GET='.join(',',array_keys($_GET)).'->'.join(',',$_GET).' POST:'.join(',',array_keys($_POST)).'->'.join(',',$_POST));

// Load main languages files
if (! defined('NOREQUIRETRAN'))
{
	$langs->load("main");
	$langs->load("dict");
}
