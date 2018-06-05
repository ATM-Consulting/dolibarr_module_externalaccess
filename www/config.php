<?php

define('INC_FROM_EXTERNAL_SCRIPT', 1);

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
    
    // Title
    $appli=constant('DOL_APPLICATION_TITLE');
    $title=$appli.' '.constant('DOL_VERSION');
    if (! empty($conf->global->MAIN_APPLICATION_TITLE)) $title=$conf->global->MAIN_APPLICATION_TITLE;
    $titletruedolibarrversion=constant('DOL_VERSION');	// $title used by login template after the @ to inform of true Dolibarr version
    
  
    // Select templates dir
    if (! empty($conf->modules_parts['tpl']))	// Using this feature slow down application
    {
        $dirtpls=array_merge($conf->modules_parts['tpl'],array('/core/tpl/'));
        foreach($dirtpls as $reldir)
        {
            $tmp=dol_buildpath($reldir.'login.tpl.php');
            if (file_exists($tmp)) { $template_dir=preg_replace('/login\.tpl\.php$/','',$tmp); break; }
        }
    }
    else
    {
        $template_dir = DOL_DOCUMENT_ROOT."/core/tpl/";
    }
    
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
    
    // Extra link
    $forgetpasslink=0;
    $helpcenterlink=0;
    if (empty($conf->global->MAIN_SECURITY_DISABLEFORGETPASSLINK) || empty($conf->global->MAIN_HELPCENTER_DISABLELINK))
    {
        if (empty($conf->global->MAIN_SECURITY_DISABLEFORGETPASSLINK))
        {
            $forgetpasslink=1;
        }
        
        if (empty($conf->global->MAIN_HELPCENTER_DISABLELINK))
        {
            $helpcenterlink=1;
        }
    }
    
    // Home message
    $main_home='';
    if (! empty($conf->global->MAIN_HOME))
    {
        $substitutionarray=getCommonSubstitutionArray($langs);
        complete_substitutions_array($substitutionarray, $langs);
        $texttoshow = make_substitutions($conf->global->MAIN_HOME, $substitutionarray, $langs);
        
        $main_home=dol_htmlcleanlastbr($texttoshow);
    }
    
    // Google AD
    $main_google_ad_client = ((! empty($conf->global->MAIN_GOOGLE_AD_CLIENT) && ! empty($conf->global->MAIN_GOOGLE_AD_SLOT))?1:0);
    
    // Set jquery theme
    $dol_loginmesg = (! empty($_SESSION["dol_loginmesg"])?$_SESSION["dol_loginmesg"]:'');
    $favicon=dol_buildpath('/theme/'.$conf->theme.'/img/favicon.ico',1);
    if (! empty($conf->global->MAIN_FAVICON_URL)) $favicon=$conf->global->MAIN_FAVICON_URL;
    $jquerytheme = 'base';
    if (! empty($conf->global->MAIN_USE_JQUERY_THEME)) $jquerytheme = $conf->global->MAIN_USE_JQUERY_THEME;
    
    // Set dol_hide_topmenu, dol_hide_leftmenu, dol_optimize_smallscreen, dol_no_mouse_hover
    $dol_hide_topmenu=GETPOST('dol_hide_topmenu','int');
    $dol_hide_leftmenu=GETPOST('dol_hide_leftmenu','int');
    $dol_optimize_smallscreen=GETPOST('dol_optimize_smallscreen','int');
    $dol_no_mouse_hover=GETPOST('dol_no_mouse_hover','int');
    $dol_use_jmobile=GETPOST('dol_use_jmobile','int');
    
    // Include login page template
    include __DIR__.'/tpl/login.tpl.php';
    
    
    $_SESSION["dol_loginmesg"] = '';
}




require __DIR__.'/../lib/externalaccess.lib.php';

require __DIR__.'/class/context.class.php';


// on cherche le main.inc de dolibarr pas celui de ce module
if(is_file(__DIR__.'/../main.inc.php'))$dir = __DIR__.'/../';
else  if(is_file(__DIR__.'/../../../main.inc.php'))$dir = __DIR__.'/../../../';
else $dir = __DIR__.'/../../';

include(__DIR__."/main.inc.php");

$context = Context::getInstance();

