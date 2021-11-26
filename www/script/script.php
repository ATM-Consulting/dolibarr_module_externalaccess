<?php

/************************************** **/
/** HERE USER DONT NEED TO BE CONNECTED **/
/************************************** **/

define('INC_FROM_SCRIPT', 1);
require __DIR__ .'/../config.php';

$action = GETPOST('action', 'alpha');

if($action === 'getlogo')
{
    if (! empty($mysoc->logo_small) && is_readable($conf->mycompany->dir_output.'/logos/thumbs/'.$mysoc->logo_small))
    {
        $logoFile = $conf->mycompany->dir_output.'/logos/thumbs/'.$mysoc->logo_small;
    }
    elseif (! empty($mysoc->logo) && is_readable($conf->mycompany->dir_output.'/logos/'.$mysoc->logo))
    {
        $logoFile = $conf->mycompany->dir_output.'/logos/'.$mysoc->logo;
    }

    if(!empty($logoFile))
    {
        $type=dol_mimetype($logoFile);
        if ($type)
        {
            top_httphead($type);
            header('Content-Disposition: inline; filename="'.basename($logoFile).'"');
        }
        else
        {
            top_httphead('image/png');
            header('Content-Disposition: inline; filename="'.basename($logoFile).'"');
        }


        $fullpath_original_file_osencoded=dol_osencode($logoFile);

        readfile($fullpath_original_file_osencoded);
    }
}

if($action == 'get-public-file'){
	outputEcmFile('', GETPOST('hash','aZ09'));
}

/** used for password recovery */
if($action == 'antispamimage'){

	/* START SESSION - imported from main.inc */

	// Set the handler of session
	if (ini_get('session.save_handler') == 'user') {
		require_once DOL_DOCUMENT_ROOT . '/core/lib/phpsessionindb.lib.php';
	}

	// Init session. Name of session is specific to Dolibarr instance.
	// Must be done after the include of filefunc.inc.php so global variables of conf file are defined (like $dolibarr_main_instance_unique_id or $dolibarr_main_force_https).
	// Note: the function dol_getprefix is defined into functions.lib.php but may have been defined to return a different key to manage another area to protect.
	$prefix = dol_getprefix('');
	$sessionname = 'DOLSESSID_'.$prefix;
	$sessiontimeout = 'DOLSESSTIMEOUT_'.$prefix;
	if (!empty($_COOKIE[$sessiontimeout])) ini_set('session.gc_maxlifetime', $_COOKIE[$sessiontimeout]);
	// This create lock, released by session_write_close() or end of page.
	// We need this lock as long as we read/write $_SESSION ['vars']. We can remove lock when finished.
	if (!defined('NOSESSION'))
	{
		session_set_cookie_params(0, '/', null, (empty($dolibarr_main_force_https) ? false : true), true); // Add tag secure and httponly on session cookie (same as setting session.cookie_httponly into php.ini). Must be called before the session_start.
		session_name($sessionname);
		session_start();
	}

	/* END SESSION */

	$length = 5;
	$letters = 'aAbBCDeEFgGhHJKLmMnNpPqQRsStTuVwWXYZz2345679';
	$number = strlen($letters);
	$string = '';
	for ($i = 0; $i < $length; $i++)
	{
		$string .= $letters[mt_rand(0, $number - 1)];
	}


	$sessionkey = 'dolexternal_antispam_value';
	$_SESSION[$sessionkey] = $string;

	$img = imagecreate(80, 32);
	if (empty($img))
	{
		dol_print_error('', "Problem with GD creation");
		exit;
	}

	// Define mime type
	top_httphead('image/png', 1);

	$background_color = imagecolorallocate($img, 250, 250, 250);
	$ecriture_color = imagecolorallocate($img, 0, 0, 0);
	imagestring($img, 4, 24, 8, $string, $ecriture_color);
	imagepng($img);
}
