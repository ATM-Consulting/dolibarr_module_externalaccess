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
 *	\file		lib/externalaccess.lib.php
 *	\ingroup	externalaccess
 *	\brief		This file is an example module library
 *				Put some comments here
 */

function externalaccessAdminPrepareHead()
{
    global $langs, $conf;
    
    $langs->load("externalaccess@externalaccess");
    
    $h = 0;
    $head = array();
    
    $head[$h][0] = dol_buildpath("/externalaccess/admin/externalaccess_setup.php", 1);
    $head[$h][1] = $langs->trans("Parameters");
    $head[$h][2] = 'settings';
    $h++;
    $head[$h][0] = dol_buildpath("/externalaccess/admin/externalaccess_about.php", 1);
    $head[$h][1] = $langs->trans("About");
    $head[$h][2] = 'about';
    $h++;
    
    $head[$h][0] = dol_buildpath("/externalaccess/", 1);
    $head[$h][1] = $langs->trans("AccessPortail");
    $head[$h][2] = 'about';
    $h++;
    
    // Show more tabs from modules
    // Entries must be declared in modules descriptor with line
    //$this->tabs = array(
    //	'entity:+tabname:Title:@externalaccess:/externalaccess/mypage.php?id=__ID__'
    //); // to add new tab
    //$this->tabs = array(
    //	'entity:-tabname:Title:@externalaccess:/externalaccess/mypage.php?id=__ID__'
    //); // to remove a tab
    complete_head_from_modules($conf, $langs, $object, $head, $h, 'externalaccess');
    
    return $head;
}

function downloadFile($filename, $forceDownload = 0)
{
    if(file_exists($filename))
    {
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $filename);
        if($mime == 'application/pdf' && empty($forceDownload))
        {
            header('Content-type: application/pdf');
            header('Content-Disposition: inline; filename="' . basename($filename) . '"');
            header('Content-Transfer-Encoding: binary');
            header('Accept-Ranges: bytes');
            header('Content-Length: ' . filesize($filename));
            echo file_get_contents($filename);
            exit();
        }
        else {
            
            header("Content-Description: File Transfer");
            header("Content-Type: application/octet-stream");
            header("Content-Disposition: attachment; filename='" . basename($filename) . "'");
            
            readfile ($filename);
            exit();
        }
        
    }
    else
    {
        print $langs->trans('FileNotExists').$filename;
    }
}

function print_invoiceList($socId = 0)
{
    global $langs,$db;
    $context = Context::getInstance();
    
    dol_include_once('compta/facture/class/facture.class.php');
    
    
    
    $sql = 'SELECT rowid ';
    $sql.= ' FROM `'.MAIN_DB_PREFIX.'facture` f';
    $sql.= ' WHERE fk_soc = '. intval($socId);
    $sql.= ' AND fk_statut > 0';
    $sql.= ' ORDER BY f.datef DESC';
    
    $tableItems = $context->dbTool->executeS($sql);
    
    if(!empty($tableItems))
    {
        
        
        
        
        print '<table class="table table-striped" >';
        
        print '<thead>';
        
        print '<tr>';
        print ' <th>'.$langs->trans('Ref').'</th>';
        print ' <th>'.$langs->trans('Date').'</th>';
        print ' <th  class="text-right" >'.$langs->trans('Amount').'</th>';
        print ' <th  class="text-right" ></th>';
        print '</tr>';
        
        print '<thead>';
        
        print '<tbody>';
        foreach ($tableItems as $item)
        {
            $facture = new Facture($db);
            $facture->fetch($item->rowid);
            $dowloadUrl = $context->getRootUrl().'script/interface.php?action=downloadInvoice&id='.$facture->id;
            print '<tr>';
            print ' <td><a href="'.$dowloadUrl.'" target="_blank" >'.$facture->ref.'</a></td>';
            print ' <td>'.dol_print_date($facture->date).'</td>';
            print ' <td class="text-right" >'.price($facture->multicurrency_total_ttc)  .' '.$facture->multicurrency_code.'</td>';
            
            
            print ' <td  class="text-right" ><a class="btn btn-xs btn-primary" href="'.$dowloadUrl.'&amp;forcedownload=1" target="_blank" ><i class="fa fa-download"></i> '.$langs->trans('Download').'</a></td>';
            
            
            print '</tr>';
            
        }
        print '</tbody>';
        
        print '</table>';
    }
    else {
        print '<div class="info clearboth" >';
        print  $langs->trans('Nothing');
        print '</div>';
    }


	    
}
	


function print_propalList($socId = 0)
{
    global $langs,$db;
    $context = Context::getInstance();
    
    dol_include_once('comm/propal/class/propal.class.php');
    
    
    
    $sql = 'SELECT rowid ';
    $sql.= ' FROM `'.MAIN_DB_PREFIX.'propal` p';
    $sql.= ' WHERE fk_soc = '. intval($socId);
    $sql.= ' AND fk_statut > 0';
    $sql.= ' ORDER BY p.datep DESC';

    $tableItems = $context->dbTool->executeS($sql);
    
    if(!empty($tableItems))
    {
        
        
        
        
        print '<table class="table table-striped" >';
        
        print '<thead>';
        
        print '<tr>';
        print ' <th>'.$langs->trans('Ref').'</th>';
        print ' <th>'.$langs->trans('Date').'</th>';
        print ' <th  class="text-right" >'.$langs->trans('Amount').'</th>';
        print ' <th  class="text-right" ></th>';
        print '</tr>';
        
        print '<thead>';
        
        print '<tbody>';
        foreach ($tableItems as $item)
        {
            $object = new Propal($db);
            $object->fetch($item->rowid);
            $dowloadUrl = $context->getRootUrl().'script/interface.php?action=downloadPropal&id='.$object->id;
            print '<tr>';
            print ' <td><a href="'.$dowloadUrl.'" target="_blank" >'.$object->ref.'</a></td>';
            print ' <td>'.dol_print_date($object->date).'</td>';
            print ' <td class="text-right" >'.price($object->multicurrency_total_ttc)  .' '.$object->multicurrency_code.'</td>';
            
            
            print ' <td  class="text-right" ><a class="btn btn-xs btn-primary" href="'.$dowloadUrl.'&amp;forcedownload=1" target="_blank" ><i class="fa fa-download"></i> '.$langs->trans('Download').'</a></td>';
            
            
            print '</tr>';
            
        }
        print '</tbody>';
        
        print '</table>';
    }
    else {
        print '<div class="info clearboth" >';
        print  $langs->trans('Nothing');
        print '</div>';
    }
    
    
    
}


function print_orderList($socId = 0)
{
    global $langs,$db;
    $context = Context::getInstance();
    
    dol_include_once('commande/class/commande.class.php');
    
    
    
    $sql = 'SELECT rowid ';
    $sql.= ' FROM `'.MAIN_DB_PREFIX.'commande` c';
    $sql.= ' WHERE fk_soc = '. intval($socId);
    $sql.= ' AND fk_statut > 0';
    $sql.= ' ORDER BY c.date_commande DESC';
    
    $tableItems = $context->dbTool->executeS($sql);
    
    if(!empty($tableItems))
    {
        
        
        
        
        print '<table class="table table-striped" >';
        
        print '<thead>';
        
        print '<tr>';
        print ' <th>'.$langs->trans('Ref').'</th>';
        print ' <th>'.$langs->trans('Date').'</th>';
        print ' <th  class="text-right" >'.$langs->trans('Amount').'</th>';
        print ' <th  class="text-right" ></th>';
        print '</tr>';
        
        print '<thead>';
        
        print '<tbody>';
        foreach ($tableItems as $item)
        {
            $object = new Commande($db);
            $object->fetch($item->rowid);
            $dowloadUrl = $context->getRootUrl().'script/interface.php?action=downloadCommande&id='.$object->id;
            print '<tr>';
            print ' <td><a href="'.$dowloadUrl.'" target="_blank" >'.$object->ref.'</a></td>';
            print ' <td>'.dol_print_date($object->date).'</td>';
            print ' <td class="text-right" >'.price($object->multicurrency_total_ttc)  .' '.$object->multicurrency_code.'</td>';
            
            
            print ' <td  class="text-right" ><a class="btn btn-xs btn-primary" href="'.$dowloadUrl.'&amp;forcedownload=1" target="_blank" ><i class="fa fa-download"></i> '.$langs->trans('Download').'</a></td>';
            
            
            print '</tr>';
            
        }
        print '</tbody>';
        
        print '</table>';
    }
    else {
        print '<div class="info clearboth" >';
        print  $langs->trans('Nothing');
        print '</div>';
    }
    
    
    
    
}

function printService($label='',$icon='',$link='',$desc='')
{
    $res = '<div class="col-lg-3 col-md-6 text-center">';
    $res.= '<div class="service-box mt-5 mx-auto">';
    $res.= !empty($link)?'<a href="'.$link.'" >':'';
    $res.= '<i class="fa fa-4x '.$icon.' text-primary mb-3 sr-icons"></i>';
    $res.= '<h3 class="mb-3">'.$label.'</h3>';
    $res.= '<p class="text-muted mb-0">'.$desc.'</p>';
    $res.= !empty($link)?'</a>':'';
    $res.= '</div>';
    $res.= '</div>';
    
    print $res;
}
	