<?php require __DIR__ .'/config.php';


// This can happen only with a bookmark or forged url call.
if (!empty($_SESSION["dol_authmode"]) && ($_SESSION["dol_authmode"] == 'forceuser' || $_SESSION["dol_authmode"] == 'http'))
{
    unset($_SESSION["dol_login"]);
	die("Applicative disconnection should be useless when connection was made in mode ".$_SESSION["dol_authmode"]);
}

global $conf, $langs, $user;

// Appel des triggers
include_once DOL_DOCUMENT_ROOT . '/core/class/interfaces.class.php';
$interface=new Interfaces($db);
$result=$interface->run_triggers('USER_LOGOUT',$user,$user,$langs,$conf);
if ($result < 0) { $error++; }
// Fin appel triggers

// Define url to go after disconnect
$urlfrom=empty($_SESSION["urlfrom"])?'':$_SESSION["urlfrom"];

// Define url to go
$url=$context->rootUrl."index.php";		// By default go to login page
if ($urlfrom) $url=$urlfrom;

// Destroy session
$prefix=dol_getprefix('');
$sessionname='DOLSESSID_'.$prefix;
$sessiontimeout='DOLSESSTIMEOUT_'.$prefix;
if (! empty($_COOKIE[$sessiontimeout])) ini_set('session.gc_maxlifetime',$_COOKIE[$sessiontimeout]);
session_name($sessionname);
session_destroy();
dol_syslog("End of session ".$sessionname);

// Not sure this is required
unset($_SESSION['dol_login']);
unset($_SESSION['dol_entity']);

if (GETPOST('noredirect', 'none')) return;
header("Location: ".$url);		// Default behaviour is redirect to index.php page
