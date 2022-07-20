<?php
/* <one line to give the program's name and a brief idea of what it does.>
 * Copyright (C) 2015 ATM Consulting <support@atm-consulting.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * 	\file		admin/externalaccess.php
 * 	\ingroup	externalaccess
 * 	\brief		This file is an example module setup page
 * 				Put some comments here
 */
// Dolibarr environment
$res = @include "../../main.inc.php"; // From htdocs directory
if (! $res) {
    $res = @include "../../../main.inc.php"; // From "custom" directory
}

// Libraries
require_once DOL_DOCUMENT_ROOT . "/core/lib/admin.lib.php";
require_once '../lib/externalaccess.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.form.class.php';

// Translations
$langs->load("externalaccess@externalaccess");

// Access control
if (! $user->admin) {
    accessforbidden();
}
$object = '';
// Parameters
$action = GETPOST('action', 'alpha');

// Initialize technical object to manage hooks of page. Note that conf->hooks_modules contains array of hook context
$hookmanager->initHooks(array('externalaccesssetup'));

/*
 * Actions
 */
if (preg_match('/set_(.*)/', $action, $reg))
{
	$code=$reg[1];
	$val = GETPOST($code,  'none');
	if (is_array($val)) $val = serialize($val);
	if (dolibarr_set_const($db, $code, $val, 'chaine', 0, '', $conf->entity) > 0)
	{
		header("Location: ".$_SERVER["PHP_SELF"]);
		exit;
	}
	else {
		dol_print_error($db);
	}
}

if (preg_match('/del_(.*)/', $action, $reg))
{
	$code=$reg[1];
	if (dolibarr_del_const($db, $code, 0) > 0)
	{
		Header("Location: ".$_SERVER["PHP_SELF"]);
		exit;
	}
	else {
		dol_print_error($db);
	}
}

/*
 * View
 */
$page_name = "externalaccessSetup";
llxHeader('', $langs->trans($page_name));

// Subheader
$linkback = '<a href="' . DOL_URL_ROOT . '/admin/modules.php">'
    . $langs->trans("BackToModuleList") . '</a>';
print load_fiche_titre($langs->trans($page_name), $linkback, "title_externalaccess@externalaccess");

// Configuration header
$head = externalaccessAdminPrepareHead();
dol_fiche_head(
    $head,
    'settings',
    $langs->trans("ModuleName"),
    1,
    "externalaccess@externalaccess"
);

if (!dol_include_once('/abricot/inc.core.php')) {
    print '<div class="error" >'. $langs->trans('AbricotNotFound'). ' <a href="http://wiki.atm-consulting.fr/index.php/Nos_modules_Dolibarr#Abricot" target="_blank">'. $langs->trans('AbricotWiki'). '</a></div>';
}

// Setup page goes here
$form=new Form($db);
$var=false;
print '<table class="noborder" width="100%">';
print "<tr class=\"liste_titre\">";
print "<td>".$langs->trans("Parameter")."</td>\n";
print '<td width="60" align="center">'.$langs->trans("Value")."</td>\n";
print "<td>&nbsp;</td>\n";
print "</tr>";

dol_include_once('externalaccess/www/class/context.class.php');
$context = Context::getInstance();
$link = '<a target="_blank" href="'.$context->getRootUrl().'" ><i class="fa fa-arrow-right" ></i> '.$langs->trans('AccessToCustomerGate').'</a>';
print_input_form_part('EACCESS_ROOT_URL', false, $link, array('size'=> 50, 'placeholder'=>'http://'), 'input', 'EACCESS_ROOT_URL_HELP');
print_input_form_part('EACCESS_TITLE', false, '', array('size'=> 50), 'input', 'EACCESS_TITLE_HELP');
print_input_form_part('EACCESS_GOBACK_URL', false, '', array('size'=> 50), 'input', 'EACCESS_GOBACK_URL_HELP');
print_on_off('EACCESS_ACTIVATE_FORGOT_PASSWORD_FEATURE');

print_title('ConfLinkedToContactInfos');
print_input_form_part('EACCESS_PHONE');
print_input_form_part('EACCESS_EMAIL', false, '', array('size'=> 20), 'input', 'EACCESS_EMAIL_HELP');
print_on_off('EACCESS_ADD_INFOS_COMMERCIAL_BAS_DE_PAGE', false, '', 'EACCESS_ADD_INFOS_COMMERCIAL_BAS_DE_PAGE_HELP');

/*
 * DESIGN
 */
print_title('ConfLinkedToDesign');
print_input_form_part('EACCESS_MAX_TOP_MENU', false, '', array('type'=>'number', 'min' => 0, 'step' => 1));
print_input_form_part('EACCESS_PRIMARY_COLOR', false, '', array('type'=>'color'), 'input', 'EACCESS_PRIMARY_COLOR_HELP');
print_on_off('EACCESS_NO_FULL_HEADBAR_FOR_HOME');
print_input_form_part('EACCESS_HEADER_IMG', false, '', array('size'=> 50, 'placeholder'=>'http://'), 'input', 'EACCESS_HEADER_IMG_HELP');
print_input_form_part('EACCESS_LOGIN_IMG', false, '', array('size'=> 50, 'placeholder'=>'http://'), 'input', 'EACCESS_LOGIN_IMG_HELP');
print_input_form_part('EACCESS_TOP_MENU_IMG', false, '', array('size'=> 50, 'placeholder'=>'http://'), 'input', 'EACCESS_TOP_MENU_IMG_HELP');
print_input_form_part('EACCESS_TOP_MENU_IMG_SHRINK', false, '', array('size'=> 50, 'placeholder'=>'http://'), 'input', 'EACCESS_TOP_MENU_IMG_SHRINK_HELP');
print_input_form_part('EACCESS_MANIFEST_ICON', false, '', array('size'=> 50, 'placeholder'=>'http://'), 'input', 'EACCESS_MANIFEST_ICON_HELP');
print_input_form_part('EACCESS_FAVICON_URL', false, '', array('size'=> 50, 'placeholder'=>'http://'), 'input', 'EACCESS_FAVICON_URL_HELP');

$parameters = array();
$reshook=$hookmanager->executeHooks('formDesignOptions', $parameters, $object, $action);    // Note that $action and $object may have been modified by hook
if ($reshook < 0) $context->setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
print $hookmanager->resPrint;


/*
 * ACTIVATE MODULES
 */

print_title('EACCESS_ACTIVATE_MODULES');
print_on_off('EACCESS_ACTIVATE_INVOICES', false, 'EACCESS_need_some_rights');
print_on_off('EACCESS_ACTIVATE_PROPALS', false, 'EACCESS_need_some_rights');
print_on_off('EACCESS_ACTIVATE_ORDERS', false, 'EACCESS_need_some_rights');
print_on_off('EACCESS_ACTIVATE_EXPEDITIONS', false, 'EACCESS_need_some_rights');
print_on_off('EACCESS_ACTIVATE_TICKETS', false, 'EACCESS_need_some_rights');
print_on_off('EACCESS_ACTIVATE_PROJECTS', false, 'EACCESS_need_some_rights');
print_on_off('EACCESS_ACTIVATE_TASKS', false, 'EACCESS_need_some_rights');
print_on_off('EACCESS_ACTIVATE_SUPPLIER_INVOICES', false, 'EACCESS_need_some_rights');
//_print_on_off('EACCESS_ACTIVATE_FORMATIONS');

$parameters = array();
$reshook=$hookmanager->executeHooks('formActivateModuleOptions', $parameters, $object, $action);    // Note that $action and $object may have been modified by hook
if ($reshook < 0) $context->setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
print $hookmanager->resPrint;


print_title('ConfLinkedToContents');
print_on_off('EACCESS_RESET_LASTMAINDOC_BEFORE_DOWNLOAD_PROPAL', false, '');

print_input_form_part('EACCESS_LOGIN_EXTRA_HTML', false, '', array(), 'textarea');
if (empty($conf->global->EACCESS_RGPD_MSG)){
    dolibarr_set_const($db, 'EACCESS_RGPD_MSG', $langs->trans('EACCESS_RGPD_MSG_default', $conf->global->MAIN_INFO_SOCIETE_NOM), 'chaine', 0, '', $conf->entity);
}

//Liaison entre la conf de Ticket et Portail pour avoir un message par défaut si vide
//if (empty($conf->global->TICKET_PUBLIC_TEXT_HELP_MESSAGE)){
//	dolibarr_set_const($db, 'TICKET_PUBLIC_TEXT_HELP_MESSAGE', $langs->trans('TicketPublicPleaseBeAccuratelyDescribe'), 'chaine', 0, '', $conf->entity);
//}

print_input_form_part('EACCESS_RGPD_MSG', false, '', array(), 'textarea');
print_input_form_part('TICKET_EXTERNAL_DESCRIPTION_MESSAGE', false, '', array(), 'textarea');
print_input_form_part('TICKET_PUBLIC_TEXT_HELP_MESSAGE', false, '', array(), 'textarea');

print_multiselect('EACCESS_LIST_ADDED_COLUMNS', false, array('ref_client'=>$langs->trans('ref_client')));

$TAddedColumnShipping = array(
	'shipping_method_id'=>$langs->trans('shipping_method_id'),
	'tracking_url'=>$langs->trans('tracking_url'),
	'linked-delivery-date_delivery-timestamp'=>$langs->trans('linked-delivery-date_delivery-timestamp')
);

$extrafields = new ExtraFields($db);
$extrafields->fetch_name_optionals_label('expedition');

if (!empty($extrafields->attributes['expedition']['label'])) {
	foreach ($extrafields->attributes['expedition']['label'] as $attribute => $label) {
		if (!empty($extrafields->attributes['expedition']['langfile'][$attribute])) {
			$langs->load($extrafields->attributes['expedition']['langfile'][$attribute]);
		}

		$TAddedColumnShipping['extrafields_'.$attribute] = $langs->trans($label).' - '.$langs->trans('Extrafields');
	}
}
print_multiselect('EACCESS_LIST_ADDED_COLUMNS_SHIPPING', false, $TAddedColumnShipping);

print_multiselect('EACCESS_LIST_ADDED_COLUMNS_PROJECT', false, array('budget_amount'=>$langs->trans('Budgets')));
$e = new ExtraFields($db);
$e->fetch_name_optionals_label('commande');
$TExtrafields_commande_list=array();
if (!empty($e->attributes['commande']['list'])) {
	$TExtrafields_commande = array_keys($e->attributes['commande']['list']);
	foreach ($TExtrafields_commande as $ef_name) {
		$TExtrafields_commande_list['EXTRAFIELD_' . $ef_name] = $e->attributes['commande']['label'][$ef_name];
	}
}
print_multiselect('EACCESS_LIST_ADDED_COLUMNS_ORDER', false, $TExtrafields_commande_list);


$e->fetch_name_optionals_label('propal');
$TExtrafields_propal_list=array();
if (!empty($e->attributes['propal']['list'])) {
	$TExtrafields_propal = array_keys($e->attributes['propal']['list']);
	foreach ($TExtrafields_propal as $ef_name) {
		$TExtrafields_propal_list['EXTRAFIELD_' . $ef_name] = $e->attributes['commande']['label'][$ef_name];
	}
}
print_multiselect('EACCESS_LIST_ADDED_COLUMNS_PROPAL', false, $TExtrafields_propal_list);

$e->fetch_name_optionals_label('ticket');
$TExtrafields_ticket_list=array();
if (!empty($e->attributes['ticket']['list'])) {
	$TExtrafields_ticket = array_keys($e->attributes['ticket']['list']);
	foreach ($TExtrafields_ticket as $ef_name) {
		$TExtrafields_ticket_list['EXTRAFIELD_' . $ef_name] = $e->attributes['ticket']['label'][$ef_name];
	}
}
print_multiselect('EACCESS_LIST_ADDED_COLUMNS_TICKET', false, $TExtrafields_ticket_list);

print_multiselect('EACCESS_CARD_ADDED_FIELD_TICKET', false, $TExtrafields_ticket_list);

print_title('Experimental');
print_on_off('EACCESS_SET_UPLOADED_FILES_AS_PUBLIC');

print '</table>';

/*
 * Add setup hook
 */

$parameters = array();
$reshook=$hookmanager->executeHooks('formMoreOptions', $parameters, $object, $action);    // Note that $action and $object may have been modified by hook
if ($reshook < 0) $context->setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
print $hookmanager->resPrint;


print dol_get_fiche_end(0);

llxFooter();

$db->close();

/**
 * @param string $title Title
 *
 * @return void
 */
function print_title($title = "")
{
    global $langs;
    print '<tr class="liste_titre">';
    print '<td>'.$langs->trans($title).'</td>'."\n";
    print '<td align="center" width="20">&nbsp;</td>';
    print '<td align="center" ></td>'."\n";
    print '</tr>';
}

/**
 * @param $confkey	string	name of conf in llx_const
 * @param $title	string 	label of conf
 * @param $desc		string 	description written in small text under title
 * @param $help		string	text for tooltip
 *
 * @return void
 */
function print_on_off($confkey, $title = false, $desc = '', $help = '')
{
    global $langs, $conf;

	$newToken = function_exists('newToken') ? newToken() : $_SESSION['newtoken'];

	print '<tr class="oddeven">';
    print '<td>'.($title?$title:$langs->trans($confkey));

    $form=new Form($db);

	if (empty($help) && !empty($langs->tab_translate[$confkey . '_HELP'])){
		$help = $confkey . '_HELP';
	}

	if (!empty($help)){
		print $form->textwithtooltip('', $langs->trans($help), 2, 1, img_help(1, ''));
	}

    if (!empty($desc))
    {
        print '<br><small>'.$langs->trans($desc).'</small>';
    }
    print '</td>';
    print '<td align="center" width="20">&nbsp;</td>';
    print '<td align="center">';
    print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
    print '<input type="hidden" name="token" value="'.$newToken.'">';
    print '<input type="hidden" name="action" value="set_'.$confkey.'">';
    print ajax_constantonoff($confkey);
    print '</form>';
    print '</td></tr>';
}

/**
 * @param $confkey Conf key
 * @param false $title Title
 * @param string $desc Description
 * @param array $metas metas
 * @param string $type Type
 * @param false $help help
 *
 * @return void
 */
function print_input_form_part($confkey, $title = false, $desc = '', $metas = array(), $type = 'input', $help = false)
{
    global $langs, $conf, $db;

	$newToken = function_exists('newToken') ? newToken() : $_SESSION['newtoken'];

    $form=new Form($db);

    $defaultMetas = array(
        'name' => $confkey
    );

    $colspan = '';
    if ($type!='textarea'){
        $defaultMetas['type']   = 'text';
        $defaultMetas['value']  = $conf->global->{$confkey};
    } else {
        $colspan = ' colspan="2"';
    }


    $metas = array_merge($defaultMetas, $metas);
    $metascompil = '';
    foreach ($metas as $key => $values)
    {
        $metascompil .= ' '.$key.'="'.$values.'" ';
    }

    print '<tr class="oddeven">';
    print '<td'.$colspan.'>';

	if (empty($help) && !empty($langs->tab_translate[$confkey . '_HELP'])){
		$help = $confkey . '_HELP';
	}

    if (!empty($help)){
        print $form->textwithtooltip(($title?$title:$langs->trans($confkey)), $langs->trans($help), 2, 1, img_help(1, ''));
    }
    else {
        print $title?$title:$langs->trans($confkey);
    }

    if (!empty($desc))
    {
        print '<br><small>'.$langs->trans($desc).'</small>';
    }


    if ($type!='textarea') {
        print '</td>';
        print '<td align="right" width="300">';
    }
    print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
    print '<input type="hidden" name="token" value="'.$newToken.'">';
    print '<input type="hidden" name="action" value="set_'.$confkey.'">';
    if ($type=='textarea'){
        include_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';
        $doleditor=new DolEditor($confkey, $conf->global->{$confkey}, '', 80, 'dolibarr_notes');
        print $doleditor->Create();
    }
	elseif ($type=='input'){
		print '<input '.$metascompil.'  />';
	}
	else {
		// custom
		print $type;
	}

    print '</td><td class="right">';
    print '<input type="submit" class="butAction" value="'.$langs->trans("Modify").'">';
    print '</form>';
    print '</td></tr>';
}

/**
 * Function used to print a multiselect
 * @param $confkey	string	name of conf in llx_const
 * @param $title	string	label of conf
 * @param $Tab		array	available values
 *
 * @return void
 */
function print_multiselect($confkey, $title, $Tab)
{

	global $langs, $form, $conf;

	$newToken = function_exists('newToken') ? newToken() : $_SESSION['newtoken'];

	print '<tr class="oddeven"><td>';
	print $title?$title:$langs->trans($confkey);
	print '</td>';
	print '<td align="right" width="300">';
	print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
	print '<input type="hidden" name="token" value="'.$newToken.'">';
	print '<input type="hidden" name="action" value="set_'.$confkey.'">';

	print $form->multiselectarray($confkey, $Tab, unserialize($conf->global->{$confkey}), '', 0, '', 0, '100%');

    print '</td><td class="right">';
    print '<input type="submit" class="butAction" value="'.$langs->trans("Modify").'">';
    print '</form>';
    print '</td></tr>';
}
