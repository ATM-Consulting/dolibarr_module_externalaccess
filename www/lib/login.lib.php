<?php
/**
 * Show Dolibarr default login page.
 * Part of this code is also duplicated into main.inc.php::top_htmlhead
 *
 * @param		Translate	$langs		Lang object (must be initialized by a new).
 * @param		Conf		$conf		Conf object
 * @param		Societe		$mysoc		Company object
 * @return		void
 */

function dol_loginfunction($langs,$conf,$mysoc)
{
    global $dolibarr_main_demo,$db;
    global $smartphone,$hookmanager;

    $langs->loadLangs(array("main","other","help"));


    $main_authentication=$conf->file->main_authentication;

    $session_name=session_name();	// Get current session name

    $dol_url_root = DOL_URL_ROOT;

    // Set cookie for timeout management
    $prefix=dol_getprefix('');
    $sessiontimeout='DOLSESSTIMEOUT_'.$prefix;
    if (! empty($conf->global->MAIN_SESSION_TIMEOUT)) setcookie($sessiontimeout, $conf->global->MAIN_SESSION_TIMEOUT, 0, "/", null, false, true);

    if (GETPOST('urlfrom', 'none')) $_SESSION["urlfrom"]=GETPOST('urlfrom', 'none');
    else $_SESSION["urlfrom"] = Context::urlOrigin();


    if (! GETPOST("username",'alpha')) $focus_element='username';
    else $focus_element='password';

    $demologin='';
    $demopassword='';
    if (! empty($dolibarr_main_demo))
    {
        $tab=explode(',',$dolibarr_main_demo);
        $demologin=$tab[0];
        $demopassword=$tab[1];
    }

    // Execute hook getLoginPageOptions (for table)
    $parameters=array('entity' => GETPOST('entity','int'));
    $reshook = $hookmanager->executeHooks('getLoginPageOptions',$parameters);    // Note that $action and $object may have been modified by some hooks.
    if (is_array($hookmanager->resArray) && ! empty($hookmanager->resArray)) {
        $morelogincontent = $hookmanager->resArray; // (deprecated) For compatibility
    } else {
        $morelogincontent = $hookmanager->resPrint;
    }

    // Execute hook getLoginPageExtraOptions (eg for js)
    $parameters=array('entity' => GETPOST('entity','int'));
    $reshook = $hookmanager->executeHooks('getLoginPageExtraOptions',$parameters);    // Note that $action and $object may have been modified by some hooks.
    $moreloginextracontent = $hookmanager->resPrint;

    // Login
    $login = (! empty($hookmanager->resArray['username']) ? $hookmanager->resArray['username'] : (GETPOST("username","alpha") ? GETPOST("username","alpha") : $demologin));
    $password = $demopassword;


    // Security graphical code
    $captcha=0;
    $captcha_refresh='';
    if (function_exists("imagecreatefrompng") && ! empty($conf->global->MAIN_SECURITY_ENABLECAPTCHA))
    {
        $captcha=1;
        $captcha_refresh=img_picto($langs->trans("Refresh"),'refresh','id="captcha_refresh_img"');
    }

	/**
	 *  LOGIN DISPLAY
	 */
    // Include login page template
    include __DIR__.'/../tpl/login.tpl.php';
}


/**
 *  Show HTTP header
 *
 *  @param  string  $contenttype    Content type. For example, 'text/html'
 *  @param	int		$forcenocache	Force disabling of cache for the page
 *  @return	void
 */
function top_httphead($contenttype='text/html', $forcenocache=0)
{
    global $conf;

    if ($contenttype == 'text/html' ) header("Content-Type: text/html; charset=".$conf->file->character_set_client);
    else header("Content-Type: ".$contenttype);
    // Security options
    header("X-Content-Type-Options: nosniff");  // With the nosniff option, if the server says the content is text/html, the browser will render it as text/html (note that most browsers now force this option to on)
    header("X-Frame-Options: SAMEORIGIN");      // Frames allowed only if on same domain (stop some XSS attacks)
    if (! empty($conf->global->MAIN_HTTP_CONTENT_SECURITY_POLICY))
    {
        // For example, to restrict script, object, frames or img to some domains
        // script-src https://api.google.com https://anotherhost.com; object-src https://youtube.com; child-src https://youtube.com; img-src: https://static.example.com
        // For example, to restrict everything to one domain, except object, ...
        // default-src https://cdn.example.net; object-src 'none'
        header("Content-Security-Policy: ".$conf->global->MAIN_HTTP_CONTENT_SECURITY_POLICY);
    }
    if ($forcenocache)
    {
        header("Cache-Control: no-cache, no-store, must-revalidate, max-age=0");
    }
}
