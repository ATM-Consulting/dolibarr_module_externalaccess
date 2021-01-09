<?php
/*
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FI8TNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *		\file       htdocs/theme/eldy/manifest.json.php
 *		\brief      File for The Web App
 */

//if (! defined('NOREQUIREUSER')) define('NOREQUIREUSER','1');	// Not disabled because need to load personalized language
//if (! defined('NOREQUIREDB'))   define('NOREQUIREDB','1');	// Not disabled to increase speed. Language code is found on url.
if (! defined('NOREQUIRESOC'))    define('NOREQUIRESOC', '1');
//if (! defined('NOREQUIRETRAN')) define('NOREQUIRETRAN','1');	// Not disabled because need to do translations
if (! defined('NOCSRFCHECK'))     define('NOCSRFCHECK', 1);
if (! defined('NOTOKENRENEWAL'))  define('NOTOKENRENEWAL', 1);
if (! defined('NOLOGIN'))         define('NOLOGIN', 1);          // File must be accessed by logon page so without login
//if (! defined('NOREQUIREMENU'))   define('NOREQUIREMENU',1);  // We need top menu content
if (! defined('NOREQUIREHTML'))   define('NOREQUIREHTML', 1);
if (! defined('NOREQUIREAJAX'))   define('NOREQUIREAJAX', '1');

require_once __DIR__.'/config.php';

$appli= !empty($conf->global->EACCESS_TITLE)?$conf->global->EACCESS_TITLE:$conf->global->MAIN_INFO_SOCIETE_NOM;

$primaryColor = !empty($conf->global->EACCESS_PRIMARY_COLOR)?$conf->global->EACCESS_PRIMARY_COLOR:'#F05F40';
$backgroundColor = !empty($conf->global->EACCESS_APPLI_COLOR)?$conf->global->EACCESS_APPLI_COLOR:$primaryColor;


$manifest = new stdClass();
$manifest->name = dol_htmlentities($appli, ENT_QUOTES);
$manifest->theme_color = "#ffffff";
$manifest->background_color = "#ffffff";
$manifest->display = "standalone";
$manifest->splash_pages = null;

if(!empty($conf->global->EACCESS_MANIFEST_ICON)){
	$icon = new stdClass();
	$icon->src = $conf->global->EACCESS_MANIFEST_ICON;
	$icon->sizes = "256x256";
	$icon->type = "image/png";
	$manifest->icons = array($icon);
}


print json_encode($manifest, JSON_PRETTY_PRINT);

