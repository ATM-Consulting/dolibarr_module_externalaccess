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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * 	\file		externalaccess/admin/externalaccess_setup.php
 * 	\ingroup	externalaccess
 * 	\brief		setup page for module externalaccess
 *
 */

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
	$res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
}
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME']; $tmp2 = realpath(__FILE__); $i = strlen($tmp) - 1; $j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) { $i--; $j--; }
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1))."/main.inc.php")) {
	$res = @include substr($tmp, 0, ($i + 1))."/main.inc.php";
}
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php")) {
	$res = @include dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php";
}
// Try main.inc.php using relative path
if (!$res && file_exists("../../main.inc.php")) {
	$res = @include "../../main.inc.php";
}
if (!$res && file_exists("../../../main.inc.php")) {
	$res = @include "../../../main.inc.php";
}
if (! $res) {
	die("Include of main fails");
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
$action = GETPOST('action', 'aZ09');
$backtopage = GETPOST('backtopage', 'alpha');

// Initialize technical object to manage hooks of page. Note that conf->hooks_modules contains array of hook context
$hookmanager->initHooks(array('externalaccesssetup'));


$error = 0;
$setupnotempty = 0;

// Set this to 1 to use the factory to manage constants. Warning, the generated module will be compatible with version v15+ only
$useFormSetup = 1;

if (!class_exists('FormSetup')) {
	// For retrocompatibility Dolibarr < 16.0
	if (floatval(DOL_VERSION) < 16.0 && !class_exists('FormSetup')) {
		require_once __DIR__.'/../backport/v16/core/class/html.formsetup.class.php';
	} else {
		require_once DOL_DOCUMENT_ROOT.'/core/class/html.formsetup.class.php';
	}
}

$formSetup = new FormSetup($db);
$form = new Form($db);

/*
 * GENERAL PARAM
 */

// Url d'accès public
$item = $formSetup->newItem('EACCESS_ROOT_URL');
$item->fieldAttr = array('type'=>'url', 'placeholder'=>'http://');
dol_include_once('externalaccess/www/class/context.class.php');
$context = Context::getInstance();
$item->fieldOutputOverride = '<a target="_blank" href="'.$context->getControllerUrl().'" ><i class="fa fa-arrow-right" ></i>  '.$context->getControllerUrl().'</a>';
if(!empty($conf->entity) && $conf->entity > 1 && !getDolGlobalString('EACCESS_ROOT_URL')){
	$item->fieldOutputOverride = '<div class="error">'.$langs->trans('MultiEntityConfEAccessRootUrlMissing').'</div>';
}

$item->helpText = $langs->trans('EACCESS_ROOT_URL_HELP');

// Titre de l'accès extérieur
$item = $formSetup->newItem('EACCESS_TITLE');
$item->helpText = $langs->trans('EACCESS_TITLE_HELP');

// Url du lien de retour
$item = $formSetup->newItem('EACCESS_GOBACK_URL');
$item->helpText = $langs->trans('EACCESS_GOBACK_URL_HELP');
$item->fieldAttr = array('type'=>'url', 'placeholder'=>'http://');

// Url du lien de retour
$formSetup->newItem('EACCESS_ACTIVATE_FORGOT_PASSWORD_FEATURE')->setAsYesNo();



/*
 * Paramètres liés au contact
 */

$formSetup->newItem('ConfLinkedToContactInfos')->setAsTitle();

// Téléphone de contact
$item = $formSetup->newItem('EACCESS_PHONE');
$item->fieldAttr = array('type'=>'phone');

// Email de contact
$item = $formSetup->newItem('EACCESS_EMAIL');
$item->helpText = $langs->trans('EACCESS_EMAIL_HELP');
$item->fieldAttr = array('type'=>'mail');

// Email de contact
$item = $formSetup->newItem('EACCESS_ADD_INFOS_COMMERCIAL_BAS_DE_PAGE');
$item->helpText = $langs->trans('EACCESS_ADD_INFOS_COMMERCIAL_BAS_DE_PAGE_HELP');
$item->setAsYesNo();



/*
 * DESIGN
 */
$formSetup->newItem('ConfLinkedToDesign')->setAsTitle();

// Regroupement automatique des entrées du menu du haut dans des groupes si le nombre d'éléments affichés dépasse la valeur suivante
$item = $formSetup->newItem('EACCESS_MAX_TOP_MENU');
$item->helpText = $langs->transnoentities('EACCESS_MAX_TOP_MENU_HELP');
$item->fieldAttr = array('type'=>'number', 'min' => 0, 'step' => 1);

// Couleur principale du thème
$item = $formSetup->newItem('EACCESS_PRIMARY_COLOR');
$item->setAsColor();
$item->helpText = $langs->transnoentities('EACCESS_PRIMARY_COLOR_HELP');

// Couleur principale du thème
$item = $formSetup->newItem('EACCESS_NO_FULL_HEADBAR_FOR_HOME')->setAsYesNo();


// Url de l'image de fond
$item = $formSetup->newItem('EACCESS_HEADER_IMG');
$item->helpText = $langs->transnoentities('EACCESS_HEADER_IMG_HELP');
$item->fieldAttr = array('list'=>'img-for-eaccess_header_img');
$item->fieldInputOverride = $item->generateInputField();
$item->fieldInputOverride.= '<datalist id="img-for-eaccess_header_img">';
for ($i = 1; $i <= 4; $i++) {
	$fileName = 'header_0'. $i.'.jpg';
	$item->fieldInputOverride.= '<option value="../img/'.$fileName.'"></option>';
	$item->helpText.= '<hr/>'.'../img/'.$fileName.' <img style="max-height:32px; vertical-align:middle;" src="'.dol_buildpath('externalaccess/www/img/'.$fileName, 1).'" />';
}
$item->fieldInputOverride.= '</datalist>';


// Url du logo
$item = $formSetup->newItem('EACCESS_LOGIN_IMG');
$item->fieldAttr = array('type'=>'url', 'size'=> 50, 'placeholder'=>'http://');
$item->helpText = $langs->transnoentities('EACCESS_LOGIN_IMG_HELP');

// Url du logo pour le menu
$item = $formSetup->newItem('EACCESS_TOP_MENU_IMG');
$item->fieldAttr = array('type'=>'url', 'size'=> 50, 'placeholder'=>'http://');
$item->helpText = $langs->transnoentities('EACCESS_TOP_MENU_IMG_HELP');

// Url du logo de substitution
$item = $formSetup->newItem('EACCESS_TOP_MENU_IMG_SHRINK');
$item->fieldAttr = array('type'=>'url', 'size'=> 50, 'placeholder'=>'http://');
$item->helpText = $langs->transnoentities('EACCESS_TOP_MENU_IMG_SHRINK_HELP');

// Url de l'icône d'application
$item = $formSetup->newItem('EACCESS_MANIFEST_ICON');
$item->fieldAttr = array('type'=>'url', 'size'=> 50, 'placeholder'=>'http://');
$item->helpText = $langs->transnoentities('EACCESS_MANIFEST_ICON_HELP');

// Url de la favicon
$item = $formSetup->newItem('EACCESS_FAVICON_URL');
$item->fieldAttr = array('type'=>'url', 'size'=> 50, 'placeholder'=>'http://');
$item->helpText = $langs->transnoentities('EACCESS_FAVICON_URL_HELP');


/*
 * ACTIVATE MODULES
 */


$formSetup->newItem('EACCESS_ACTIVATE_MODULES')->setAsTitle();
$formSetup->newItem('EACCESS_ACTIVATE_INVOICES')->setAsYesNo()->helpText = $langs->trans('EACCESS_need_some_rights');
$formSetup->newItem('EACCESS_ACTIVATE_PROPALS')->setAsYesNo()->helpText = $langs->trans('EACCESS_need_some_rights');
$formSetup->newItem('EACCESS_ACTIVATE_ORDERS')->setAsYesNo()->helpText = $langs->trans('EACCESS_need_some_rights');
$formSetup->newItem('EACCESS_ACTIVATE_EXPEDITIONS')->setAsYesNo()->helpText = $langs->trans('EACCESS_need_some_rights');
$formSetup->newItem('EACCESS_ACTIVATE_TICKETS')->setAsYesNo()->helpText = $langs->trans('EACCESS_need_some_rights');
$formSetup->newItem('EACCESS_ACTIVATE_PROJECTS')->setAsYesNo()->helpText = $langs->trans('EACCESS_need_some_rights');
$formSetup->newItem('EACCESS_ACTIVATE_TASKS')->setAsYesNo()->helpText = $langs->trans('EACCESS_need_some_rights');
$formSetup->newItem('EACCESS_ACTIVATE_SUPPLIER_INVOICES')->setAsYesNo()->helpText = $langs->trans('EACCESS_need_some_rights');

/*
 * Paramètres liés au contenu
 */

$formSetup->newItem('ConfLinkedToContents')->setAsTitle();

// Forcer l'utilisation du modèle par défaut pour le téléchargement des propositions commerciales
$formSetup->newItem('EACCESS_RESET_LASTMAINDOC_BEFORE_DOWNLOAD_PROPAL')->setAsYesNo();


// Texte supplémentaire affiché sur la page de connexion.
$item = $formSetup->newItem('EACCESS_LOGIN_EXTRA_HTML')->setAsHtml();

// Message RGPD sur le formulaire des informations personnelles
if (!getDolGlobalString('EACCESS_RGPD_MSG') && getDolGlobalString('MAIN_INFO_SOCIETE_NOM')){
	dolibarr_set_const($db, 'EACCESS_RGPD_MSG', $langs->trans('EACCESS_RGPD_MSG_default', getDolGlobalString('MAIN_INFO_SOCIETE_NOM')), 'chaine', 0, '', $conf->entity);
}
$item = $formSetup->newItem('EACCESS_RGPD_MSG')->setAsHtml();

// Texte par défaut du champ description à la création d'un ticket
$item = $formSetup->newItem('TICKET_EXTERNAL_DESCRIPTION_MESSAGE')->setAsHtml();


// Texte d'aide pour la saisie du message
$item = $formSetup->newItem('TICKET_PUBLIC_TEXT_HELP_MESSAGE')->setAsHtml();

// Colonnes supplémentaires à afficher sur les listes
$item = $formSetup->newItem('EACCESS_LIST_ADDED_COLUMNS')->setAsMultiSelect(
	array(
		'ref_client'=>$langs->trans('ref_client')
	)
);

// Colonnes supplémentaires à afficher sur les listes d'expeditions

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

$item = $formSetup->newItem('EACCESS_LIST_ADDED_COLUMNS')->setAsMultiSelect($TAddedColumnShipping);


// Colonnes supplémentaires à afficher sur les listes des projets
$item = $formSetup->newItem('EACCESS_LIST_ADDED_COLUMNS_PROJECT')->setAsMultiSelect(
	array(
		'budget_amount'=>$langs->trans('Budgets')
	)
);

// Colonnes supplémentaires à afficher sur les listes de commandes
$formSetup->newItem('EACCESS_LIST_ADDED_COLUMNS_ORDER')->setAsMultiSelect(getExtrafieldElementList('commande'));

// Colonnes supplémentaires à afficher sur les listes de commandes
$formSetup->newItem('EACCESS_LIST_ADDED_COLUMNS_PROPAL')->setAsMultiSelect(getExtrafieldElementList('propal'));


//Colonnes supplémentaires à afficher sur les listes de tickets
$formSetup->newItem('EACCESS_LIST_ADDED_COLUMNS_TICKET')->setAsMultiSelect(getExtrafieldElementList('ticket'));

// Champs supplémentaires à afficher sur les fiches tickets
$formSetup->newItem('EACCESS_CARD_ADDED_FIELD_TICKET')->setAsMultiSelect(getExtrafieldElementList('ticket'));

// Activer ou non l'email pour le suivi
$formSetup->newItem('EACCESS_FOLLOW_UP_EMAIL')->setAsYesNo();

// Activer ou non la sévérité
$formSetup->newItem('EACCESS_SEVERITY')->setAsYesNo();

/*
 * EXPERIMENTAL
 */

$formSetup->newItem('ExperimentalParams')->setAsTitle();

// Tickets : Lors de l'ajout de pièces jointes depuis le portail, générer un lien de partage sur ces fichiers
$item = $formSetup->newItem('EACCESS_SET_UPLOADED_FILES_AS_PUBLIC')->setAsYesNo();
$item->helpText = $langs->trans('EACCESS_SET_UPLOADED_FILES_AS_PUBLIC_HELP');





/*
 * Actions
 */

// For retrocompatibility Dolibarr < 15.0
if ( versioncompare(explode('.', DOL_VERSION), array(15)) < 0 && $action == 'update' && !empty($user->admin)) {
	$formSetup->saveConfFromPost();
}

include DOL_DOCUMENT_ROOT.'/core/actions_setmoduleoptions.inc.php';




/*
 * View
 */

$form = new Form($db);
$help_url = '';
$page_name = "externalaccessSetup";
llxHeader('', $langs->trans($page_name), $help_url);

// Subheader
$linkback = '<a href="'.($backtopage ? $backtopage : DOL_URL_ROOT.'/admin/modules.php?restore_lastsearch_values=1').'">'.$langs->trans("BackToModuleList").'</a>';
print load_fiche_titre($langs->trans($page_name), $linkback, 'title_externalaccess@externalaccess');


// Configuration header
$head = externalaccessAdminPrepareHead();
print dol_get_fiche_head(
    $head,
    'settings',
    $langs->trans("ModuleName"),
    -1,
    "externalaccess@externalaccess"
);

// Setup page goes here
echo '<span class="opacitymedium">'.$langs->trans("ExternalAccessSetupPage").'</span><br><br>';

if ($action == 'edit') {
	print $formSetup->generateOutput(true);
	print '<br>';
} elseif (!empty($formSetup->items)) {
	print $formSetup->generateOutput();
	print '<div class="tabsAction">';
	print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?action=edit&token='.newToken().'">'.$langs->trans("Modify").'</a>';
	print '</div>';
} else {
	print '<br>'.$langs->trans("NothingToSetup");
}



/*
 * Add setup hook
 */

$parameters = array();
$reshook=$hookmanager->executeHooks('formMoreOptions', $parameters, $object, $action);    // Note that $action and $object may have been modified by hook
if ($reshook < 0) $context->setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
print $hookmanager->resPrint;


print dol_get_fiche_end(-1);

llxFooter();

$db->close();


// Functions

function getExtrafieldElementList($element) {
	global $db;
	$e = new ExtraFields($db);
	$e->fetch_name_optionals_label($element);
	$TExtrafields_list = [];
	if(! empty($e->attributes[$element]['list'])) {
		$TExtrafields_commande = array_keys($e->attributes[$element]['list']);
		foreach($TExtrafields_commande as $ef_name) {
			$TExtrafields_list['EXTRAFIELD_'.$ef_name] = $e->attributes[$element]['label'][$ef_name];
		}
	}
	return $TExtrafields_list;
}
