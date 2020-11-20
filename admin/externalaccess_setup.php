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


$res = @include("../../main.inc.php"); // From htdocs directory
if (! $res) {
    $res = @include("../../../main.inc.php"); // From "custom" directory
}

// Libraries
require_once DOL_DOCUMENT_ROOT . "/core/lib/admin.lib.php";
require_once '../lib/externalaccess.lib.php';

// Translations
$langs->load("externalaccess@externalaccess");

// Access control
if (! $user->admin) {
    accessforbidden();
}

// Appel des class operationorder tri�s par statut et par type
dol_include_once('operationorder/class/operationorderstatus.class.php');
$statuslist= new Operationorderstatus($db);
//dol_include_once('operationorder/class/operationorder.class.php');
//$typelist= new OperationOrderType();

// Parameters
$action = GETPOST('action', 'alpha');

/*
 * Actions
 */
if (preg_match('/set_(.*)/',$action,$reg))
{
	$code=$reg[1];

	if (is_array(GETPOST($code))){
		$val=json_encode(GETPOST($code));
	}else{
		$val=GETPOST($code);
	}
	
	if (dolibarr_set_const($db, $code, $val, 'chaine', 0, '', $conf->entity) > 0)

	{
		header("Location: ".$_SERVER["PHP_SELF"]);
		exit;
	}
	else
	{
		dol_print_error($db);
	}
}

if (preg_match('/del_(.*)/',$action,$reg))
{
	$code=$reg[1];
	if (dolibarr_del_const($db, $code, 0) > 0)
	{
		Header("Location: ".$_SERVER["PHP_SELF"]);
		exit;
	}
	else
	{
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
    1
);

if(!dol_include_once('/abricot/inc.core.php')) {
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
//$context = new Context();
$link = '<a target="_blank" href="'.$context->getRootUrl().'" ><i class="fa fa-arrow-right" ></i> '.$langs->trans('AccessToCustomerGate').'</a>';
_print_input_form_part('EACCESS_ROOT_URL',false,$link, array('size'=> 50, 'placeholder'=>'http://'),'input','EACCESS_ROOT_URL_HELP');
_print_input_form_part('EACCESS_TITLE',false,'',array('size'=> 50),'input','EACCESS_TITLE_HELP');
_print_input_form_part('EACCESS_GOBACK_URL',false,'',array('size'=> 50),'input','EACCESS_GOBACK_URL_HELP');
_print_input_form_part('EACCESS_PHONE');
_print_input_form_part('EACCESS_EMAIL',false,'',array('size'=> 20),'input','EACCESS_EMAIL_HELP');


_print_input_form_part('EACCESS_PRIMARY_COLOR', false, '', array('type'=>'color'),'input','EACCESS_PRIMARY_COLOR_HELP');
_print_input_form_part('EACCESS_HEADER_IMG',false,'',array('size'=> 50, 'placeholder'=>'http://'),'input','EACCESS_HEADER_IMG_HELP');

_print_title('EACCESS_ACTIVATE_MODULES');
_print_on_off('EACCESS_ACTIVATE_INVOICES',false, 'EACCESS_need_some_rights');
_print_on_off('EACCESS_ACTIVATE_PROPALS',false, 'EACCESS_need_some_rights');
_print_on_off('EACCESS_ACTIVATE_ORDERS',false, 'EACCESS_need_some_rights');
_print_on_off('EACCESS_ACTIVATE_EXPEDITIONS',false, 'EACCESS_need_some_rights');
_print_on_off('EACCESS_ACTIVATE_TICKETS',false, 'EACCESS_need_some_rights');
_print_on_off('EACCESS_ACTIVATE_PROJECTS',false, 'EACCESS_need_some_rights');
//_print_on_off('EACCESS_ACTIVATE_FORMATIONS');
_print_on_off('EACCESS_ACTIVATE_OR',false, 'EACCESS_need_some_rights');
//Rafraichissement de la page automatique
?>
<script>
	$(document).ready(function() {
		$("#set_EACCESS_ACTIVATE_OR").click(function() {
			document.location.reload(true);
		});
	});
	$(document).ready(function() {
		$("#del_EACCESS_ACTIVATE_OR").click(function() {
			document.location.reload(true);
		});
	});
</script>
<?php

//module OR parametrable par statut
if (!empty($conf->global->EACCESS_ACTIVATE_OR)){
	print '<tr>';
	print '<td  id="coltitle-status-allowed" >'.$langs->trans('ETargetableStatus').'</td>';
	print '<form method="post" action="'.$_SERVER['PHP_SELF'].'">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="action" value="set_EACCESS_OR_STATUT_DISP">';
	print '<td  id="colval-status-allowed" >';
	$TStatus = $statuslist->fetchAll(0,false, array('entity'=> $conf->entity ));
	if(!empty($TStatus)){
		$TAvailableStatus = array();
		foreach ($TStatus as $key => $status){
			if($status->id != $statuslist->status){
				if($status->code == $statuslist->code){
					continue;
				}
			$TAvailableStatus[$status->code] = $status->label;
			}
		}
	// il faut reverifier vu que l'on a supprim�...
		if(!empty($TStatus)){
			$val=array();
			$val=json_decode($conf->global->EACCESS_OR_STATUT_DISP);
			print $form->multiselectarray('EACCESS_OR_STATUT_DISP', $TAvailableStatus, $val, $key_in_label = 0, $value_as_key = 0, '', 0, '100%', '', '', '', 1);	
		}
	}
	print "</td>";
	print '<td> <input type="submit" class="butAction" value="'.$langs->trans("Modify").'"> </td>';
	print '</form>';
	print "</tr>";
}

//module OR parametrable par type
if (!empty($conf->global->EACCESS_ACTIVATE_OR)){
	print '<tr>';
	print '<td  id="coltitle-status-allowed" >'.$langs->trans('ETargetableType').'</td>';
	print '<form method="post" action="'.$_SERVER['PHP_SELF'].'">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="action" value="set_EACCESS_OR_TYPE_DISP">';
	print '<td  id="colval-status-allowed" >';
	
	$TAvailableType = array();
	$sql = 'SELECT rowid, label';
	$sql .= ' FROM '.MAIN_DB_PREFIX.'c_operationorder_type';
	$resql = $db->query($sql);
	//print_r($resql);
	//exit;
	if ($resql){
		while ($typelist = $db->fetch_object($resql)){
			$TAvailableType [$typelist->rowid] = $typelist->label;
		}
	}
		// il faut reverifier vu que l'on a supprim�...
	if(!empty($TAvailableType)){
			$val=array();
			$val=json_decode($conf->global->EACCESS_OR_TYPE_DISP);
			print $form->multiselectarray('EACCESS_OR_TYPE_DISP', $TAvailableType, $val, $key_in_label = 0, $value_as_key = 0, '', 0, '100%', '', '', '', 1);
		} 
	print "</td>";
	print '<td> <input type="submit" class="butAction" value="'.$langs->trans("Modify").'"> </td>';
	print '</form>';
	print "</tr>";
}

//module disponibilit� des v�hicules parametrable par ??
_print_on_off('EACCESS_ACTIVATE_DISPONIBILITY',false, 'EACCESS_need_some_rights');

//texte ajustable
_print_input_form_part('EACCESS_LOGIN_EXTRA_HTML',false,'',array(),'textarea');
if(empty($conf->global->EACCESS_RGPD_MSG)){
    dolibarr_set_const($db,'EACCESS_RGPD_MSG',$langs->trans('EACCESS_RGPD_MSG_default',$conf->global->MAIN_INFO_SOCIETE_NOM), 'chaine', 0, '', $conf->entity) ;
}
_print_input_form_part('EACCESS_RGPD_MSG',false,'',array(),'textarea');


print '</table>';

dol_fiche_end(1);

llxFooter();

$db->close();


function _print_title($title="")
{
    global $langs;
    print '<tr class="liste_titre">';
    print '<td>'.$langs->trans($title).'</td>'."\n";
    print '<td align="center" width="20">&nbsp;</td>';
    print '<td align="center" ></td>'."\n";
    print '</tr>';
}

function _print_on_off($confkey, $title = false, $desc ='')
{
    global $langs, $conf;

    print '<tr class="oddeven">';
    print '<td>'.($title?$title:$langs->trans($confkey));
    if(!empty($desc))
    {
        print '<br><small>'.$langs->trans($desc).'</small>';
    }
    print '</td>';
    print '<td align="center" width="20">&nbsp;</td>';
    print '<td align="center">';
    print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
    print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
    print '<input type="hidden" name="action" value="set_'.$confkey.'">';
    print ajax_constantonoff($confkey);
    print '</form>';
    print '</td></tr>';
}

function _print_input_form_part($confkey, $title = false, $desc ='', $metas = array(), $type='input', $help = false)
{
    global $langs, $conf, $db;

    $form=new Form($db);

    $defaultMetas = array(
        'name' => $confkey
    );

    $colspan = '';
    if($type!='textarea'){
        $defaultMetas['type']   = 'text';
        $defaultMetas['value']  = $conf->global->{$confkey};
    } else {
        $colspan = ' colspan="2"';
    }


    $metas = array_merge ($defaultMetas, $metas);
    $metascompil = '';
    foreach ($metas as $key => $values)
    {
        $metascompil .= ' '.$key.'="'.$values.'" ';
    }

    print '<tr class="oddeven">';
    print '<td'.$colspan.'>';

    if(!empty($help)){
        print $form->textwithtooltip( ($title?$title:$langs->trans($confkey)) , $langs->trans($help),2,1,img_help(1,''));
    }
    else {
        print $title?$title:$langs->trans($confkey);
    }

    if(!empty($desc))
    {
        print '<br><small>'.$langs->trans($desc).'</small>';
    }


    if($type!='textarea') {
        print '</td>';
        print '<td align="right" width="300">';
    }
    print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
    print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
    print '<input type="hidden" name="action" value="set_'.$confkey.'">';
    if($type=='textarea'){
        include_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';
        $doleditor=new DolEditor($confkey, $conf->global->{$confkey}, '', 80, 'dolibarr_notes');
        print $doleditor->Create();
    }
    else {
        print '<input '.$metascompil.'  />';
    }

    print '</td><td class="right">';
    print '<input type="submit" value="'.$langs->trans("Modify").'">';
    print '</form>';
    print '</td></tr>';
}
