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
    
    if (GETPOST('urlfrom','alpha')) $_SESSION["urlfrom"]=GETPOST('urlfrom','alpha');
    else unset($_SESSION["urlfrom"]);
    
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
    
    
    
    // Include login page template
    include __DIR__.'/../tpl/login.tpl.php';
    
    
}