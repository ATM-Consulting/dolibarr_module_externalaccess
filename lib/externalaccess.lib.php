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

**MENU*function*print_
order-Invoice-propal-projet-agenda-
 * N'est finalement pas utilisé, utiliser datatable en html5 plutot
 //>>Ligne646
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
    
    /*$head[$h][0] = dol_buildpath("/externalaccess/", 1);
    $head[$h][1] = $langs->trans("AccessPortail");
    $head[$h][2] = 'about';
    $h++;*/
    
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
    global $langs;
    if(!empty($filename) && file_exists($filename))
    {
        if(is_readable($filename) && is_file ( $filename ))
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
                header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
                header('Content-Length: ' . filesize($filename));
                
                readfile ($filename);
                exit();
            }
        }
        else
        {
            print $langs->trans('FileNotReadable');
        }
        
    }
    else
    {
        print $langs->trans('FileNotExists');
    }
}



//invoiceTable
function print_invoiceTable($socId = 0)
{
    global $langs,$db;
    $context = Context::getInstance();
    
    dol_include_once('compta/facture/class/facture.class.php');
    
    $langs->load('factures');
    
    
    $sql = 'SELECT rowid ';
    $sql.= ' FROM `'.MAIN_DB_PREFIX.'facture` f';
    $sql.= ' WHERE fk_soc = '. intval($socId);
    $sql.= ' AND fk_statut > 0';
    $sql.= ' ORDER BY f.datef DESC';
    
    $tableItems = $context->dbTool->executeS($sql);
    
    if(!empty($tableItems))
    {   print '<table id="invoice-list" class="table table-striped" >';
        
        print '<thead>';
        
        print '<tr>';
        print ' <th class="text-center" >'.$langs->trans('Ref').'</th>';
        print ' <th class="text-center" >'.$langs->trans('Date').'</th>';
        print ' <th class="text-center" >'.$langs->trans('DatePayLimit').'</th>';
        print ' <th class="text-center" >'.$langs->trans('Status').'</th>';
        if(!empty($conf->global->EACCESS_ACTIVATE_INVOICES_HT_COL)){
            print ' <th class="text-center" >'.$langs->trans('Amount_HT').'</th>';
        }
        print ' <th class="text-center" >'.$langs->trans('Amount_TTC').'</th>';
        print ' <th class="text-center" >'.$langs->trans('RemainderToPay').'</th>';
        print ' <th class="text-center" ></th>';
        print '</tr>';
        
        print '</thead>';
        
        print '<tbody>';
        foreach ($tableItems as $item)
        {
            $object = new Facture($db);
            $object->fetch($item->rowid);
	    load_last_main_doc($object);
            $dowloadUrl = $context->getRootUrl().'script/interface.php?action=downloadInvoice&id='.$object->id;
            //var_dump($object); exit;
            $totalpaye = $object->getSommePaiement();
            $totalcreditnotes = $object->getSumCreditNotesUsed();
            $totaldeposits = $object->getSumDepositsUsed();
            $resteapayer = price2num($object->total_ttc - $totalpaye - $totalcreditnotes - $totaldeposits, 'MT');
            
            if(!empty($object->last_main_doc)){
                $viewLink = '<a href="'.$dowloadUrl.'" target="_blank" >'.$object->ref.'</a>';
                $downloadLink = '<a class="btn btn-xs btn-primary" href="'.$dowloadUrl.'&amp;forcedownload=1" target="_blank" ><i class="fa fa-download"></i> '.$langs->trans('Download').'</a>';
            }
            else{
                $viewLink = $object->ref;
                $downloadLink =  $langs->trans('DocumentFileNotAvailable');
            }                     
            print '<tr >';
            print ' <td data-search="'.$object->ref.'" data-order="'.$object->ref.'" >'.$viewLink.'</td>';
            print ' <td data-search="'.$object->date.'" data-order="'.dol_print_date($object->date).'"  >'.dol_print_date($object->date).'</td>';
            print ' <td data-order="'.$object->date_lim_reglement.'"  >'.dol_print_date($object->date_lim_reglement).'</td>';
            print ' <td  >'.$object->getLibStatut(0).'</td>';
            
            if(!empty($conf->global->EACCESS_ACTIVATE_INVOICES_HT_COL)){
                print ' <td data-order="'.$object->multicurrency_total_ht.'" class="text-right" >'.price($object->multicurrency_total_ht)  .' '.$object->multicurrency_code.'</td>';
            }
            print ' <td data-order="'.$object->multicurrency_total_ttc.'" class="text-right" >'.price($object->multicurrency_total_ttc)  .' '.$object->multicurrency_code.'</td>';
            print ' <td data-order="'.$resteapayer.'" class="text-right" >'.price($resteapayer)  .' '.$object->multicurrency_code.'</td>';
            print ' <td  class="text-right" >'.$downloadLink.'</td>';
            print '</tr>';            
        }
        print '</tbody>';        
        print '</table>';
        $jsonUrl = $context->getRootUrl().'script/interface.php?action=getInvoicesList';
    ?>
    <script type="text/javascript" >
     $(document).ready(function(){
         $("#invoice-list").DataTable({            age": {"url": "<?php print $context->getRootUrl(); ?>vendor/data-tables/french.json"
             },
             responsive: true,
             columnDefs: [{
                 orderable: false,
                 "aTargets": [-1]
             },{
                 "bSearchable": false,
                 "aTargets": [-1, -2]
             }]
         });
     });
    </script>
    <?php 
    }
    else {
        print '<div class="info clearboth text-center" >';
        print  $langs->trans('EACCESS_Nothing');
        print '</div>';
    }


	    
}
	

function print_propalTable($socId = 0)
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
        print '<table id="propal-list" class="table table-striped" >';
        
        print '<thead>';
        
        print '<tr>';
        print ' <th class="text-center" >'.$langs->trans('Ref').'</th>';
        print ' <th class="text-center" >'.$langs->trans('Date').'</th>';
        print ' <th class="text-center" >'.$langs->trans('EndValidDate').'</th>';
        print ' <th class="text-center" >'.$langs->trans('Status').'</th>';
        print ' <th class="text-center" >'.$langs->trans('Amount_HT').'</th>';
        print ' <th class="text-center" ></th>';
        print '</tr>';
        
        print '</thead>';
        
        print '<tbody>';
        foreach ($tableItems as $item)
        {
            $object = new Propal($db);
            $object->fetch($item->rowid);
	    load_last_main_doc($object);
            $dowloadUrl = $context->getRootUrl().'script/interface.php?action=downloadPropal&id='.$object->id;
            
           
            if(!empty($object->last_main_doc)){
                $viewLink = '<a href="'.$dowloadUrl.'" target="_blank" >'.$object->ref.'</a>';
                $downloadLink = '<a class="btn btn-xs btn-primary" href="'.$dowloadUrl.'&amp;forcedownload=1" target="_blank" ><i class="fa fa-download"></i> '.$langs->trans('Download').'</a>';
            }
            else{
                $viewLink = $object->ref;
                $downloadLink =  $langs->trans('DocumentFileNotAvailable');
            }
            
            print '<tr>';
            print ' <td data-search="'.$object->ref.'" data-order="'.$object->ref.'"  >'.$viewLink.'</td>';
            print ' <td data-search="'.dol_print_date($object->date).'" data-order="'.$object->date.'" >'.dol_print_date($object->date).'</td>';
            print ' <td data-search="'.dol_print_date($object->fin_validite).'" data-order="'.$object->fin_validite.'" >'.dol_print_date($object->fin_validite).'</td>';
            print ' <td class="text-center" >'.$object->getLibStatut(0).'</td>';
            print ' <td data-order="'.$object->multicurrency_total_ht.'" class="text-right" >'.price($object->multicurrency_total_ht)  .' '.$object->multicurrency_code.'</td>';
            
            
            print ' <td  class="text-right" >'.$downloadLink.'</td>';
            
            
            print '</tr>';
            
        }
        print '</tbody>';
        
        print '</table>';
        ?>
    <script type="text/javascript" >
     $(document).ready(function(){
         $("#propal-list").DataTable({
             "language": {
                 "url": "<?php print $context->getRootUrl(); ?>vendor/data-tables/french.json"
             },

             responsive: true,
             columnDefs: [{
                 orderable: false,
                 "aTargets": [-1]
             },{
                 "bSearchable": false,
                 "aTargets": [-1, -2]
             }]
         });
     });
    </script>
    <?php 
    }
    else {
        print '<div class="info clearboth text-center" >';
        print  $langs->trans('EACCESS_Nothing');
        print '</div>';
    }
    
    
    
}




function print_projetsTable($socId = 1)
{
    global $langs,$db;
    $context = Context::getInstance();
    
    dol_include_once('projet/class/project.class.php');
    $langs->load('projet');
    
    
    $sql = 'SELECT rowid ';
    $sql.= ' FROM `'.MAIN_DB_PREFIX.'projet` projet';
    $sql.= ' WHERE fk_soc = '. intval($socId);
    $sql.= ' AND fk_statut > 0';
    $sql.= ' ORDER BY projet.datec DESC';
    
    $tableItems = $context->dbTool->executeS($sql);
    
    if(!empty($tableItems))
    {
        
        
        
        
        print '<table id="projet-list" class="table table-striped" >';
        
        print '<thead>';
        
        print '<tr>';
        print ' <th class="text-center" >'.$langs->trans('Ref').'</th>';
        print ' <th class="text-center" >'.$langs->trans('Date').'</th>';
        print ' <th class="text-center" >'.$langs->trans('DatePayLimit').'</th>';
        print ' <th class="text-center" >'.$langs->trans('Status').'</th>';
        /*if(!empty($conf->global->EACCESS_ACTIVATE_INVOICES_HT_COL)){
            print ' <th class="text-center" >'.$langs->trans('Amount_HT').'</th>';
        }*/
        //print ' <th class="text-center" >'.$langs->trans('Amount_TTC').'</th>';
        //print ' <th class="text-center" >'.$langs->trans('RemainderToPay').'</th>';
		print ' <th class="text-center" >'.$langs->trans('projet').'</th>';
		print ' <th class="text-center" >'.$langs->trans('Titre').'</th>';
        print ' <th class="text-center" >'.$langs->trans('Description').'</th>';
		print ' <th class="text-center" >'.$langs->trans('Lien de telechargement').'</th>';
        print '</tr>';
        
        print '</thead>';
        
        print '<tbody>';
        foreach ($tableItems as $item)
        {
            $object = new project($db);
            $object->fetch($item->rowid);
        load_last_main_doc($object);
            $dowloadUrl = $context->getRootUrl().'script/interface.php?action=downloadprojet&id='.$object->id;
            //var_dump($object); exit;
            /*$totalpaye = $object->getSommePaiement();
            $totalcreditnotes = $object->getSumCreditNotesUsed();
            $totaldeposits = $object->getSumDepositsUsed();
            $resteapayer = price2num($object->total_ttc - $totalpaye - $totalcreditnotes - $totaldeposits, 'MT');
            */
            if(!empty($object->last_main_doc)){
                $viewLink = '<a href="'.$dowloadUrl.'" target="_blank" >'.$object->ref.'</a>';
                $downloadLink = '<a class="btn btn-xs btn-primary" href="'.$dowloadUrl.'&amp;forcedownload=1" target="_blank" ><i class="fa fa-download"></i> '.$langs->trans('Download').'</a>';
            }
            else{
                $viewLink = $object->ref;
                $downloadLink =  $langs->trans('DocumentFileNotAvailable');
            }
            
            
            print '<tr >';
            print ' <td data-search="'.$object->ref.'" data-order="'.$object->ref.'" >'.$viewLink.'</td>';
			
            print ' <td data-search="'.$object->dateo.'" data-order="'.dol_print_date($object->dateo).'"  >'.dol_print_date($object->dateo).'</td>';
            //print ' <td data-order="'.$object->date_lim_reglement.'"  >'.dol_print_date($object->date_lim_reglement).'</td>';
          
			print ' <td data-search="'.$object->datec.'" data-order="'.dol_print_date($object->datec).'"  >'.dol_print_date($object->datec).'</td>';
		  
			print ' <td  >'.$object->getLibStatut(0).'</td>';
            print ' <td data-search="'.$object->title.'" data-order="'.$object->title.'" ></td>';
        
			print '<td  ></td>';
			print '<td  ></td>';
			print ' <td  class="text-right" >'.$downloadLink.'</td>';
            print '</tr>';
            
        }
        print '</tbody>';
        
        print '</table>';
        $jsonUrl = $context->getRootUrl().'script/interface.php?action=getprojetList';
	    
	    
	    
	    

function print_orderListTable($socId = 0)
{
    global $langs,$db;
    $context = Context::getInstance();
    
    dol_include_once('commande/class/commande.class.php');
    
    $langs->load('orders');
    
    
    $sql = 'SELECT rowid ';
    $sql.= ' FROM `'.MAIN_DB_PREFIX.'commande` c';
    $sql.= ' WHERE fk_soc = '. intval($socId);
    $sql.= ' AND fk_statut > 0';
    $sql.= ' ORDER BY c.date_commande DESC';
    
    $tableItems = $context->dbTool->executeS($sql);
    
    if(!empty($tableItems))
    {
        
        
        
        
        print '<table id="order-list" class="table table-striped" >';
        
        print '<thead>';
        
        print '<tr>';
        print ' <th class="text-center" >'.$langs->trans('Ref').'</th>';
        print ' <th class="text-center" >'.$langs->trans('Date').'</th>';
        print ' <th class="text-center" >'.$langs->trans('DateLivraison').'</th>';
        print ' <th class="text-center" >'.$langs->trans('Status').'</th>';
        print ' <th class="text-center" >'.$langs->trans('Amount_HT').'</th>';
        print ' <th class="text-center" ></th>';
        print '</tr>';
        
        print '</thead>';
        
        print '<tbody>';
        foreach ($tableItems as $item)
        {
            $object = new Commande($db);
            $object->fetch($item->rowid);
	    load_last_main_doc($object);
            $dowloadUrl = $context->getRootUrl().'script/interface.php?action=downloadCommande&id='.$object->id;
            
            if(!empty($object->last_main_doc)){
                $viewLink = '<a href="'.$dowloadUrl.'" target="_blank" >'.$object->ref.'</a>';
                $downloadLink = '<a class="btn btn-xs btn-primary" href="'.$dowloadUrl.'&amp;forcedownload=1" target="_blank" ><i class="fa fa-download"></i> '.$langs->trans('Download').'</a>';
            }
            else{
                $viewLink = $object->ref;
                $downloadLink =  $langs->trans('DocumentFileNotAvailable');
            }
            
            print '<tr>';
            print ' <td data-search="'.$object->ref.'" data-order="'.$object->ref.'"  >'.$viewLink.'</td>';
            print ' <td data-search="'.dol_print_date($object->date).'" data-order="'.$object->date.'" >'.dol_print_date($object->date).'</td>';
            print ' <td data-search="'.dol_print_date($object->date_livraison).'" data-order="'.$object->date_livraison.'" >'.dol_print_date($object->date_livraison).'</td>';
            print ' <td class="text-center" >'.$object->getLibStatut(0).'</td>';
            print ' <td data-order="'.$object->multicurrency_total_ht.'"  class="text-right" >'.price($object->multicurrency_total_ht)  .' '.$object->multicurrency_code.'</td>';
            
            
            print ' <td class="text-right" >'.$downloadLink.'</td>';
            
            
            print '</tr>';
            
        }
        print '</tbody>';
        
        print '</table>';
        ?>
        <script type="text/javascript" >
         $(document).ready(function(){
             $("#order-list").DataTable({
                 "language": {
                     "url": "<?php print $context->getRootUrl(); ?>vendor/data-tables/french.json"
                 },
        
                 responsive: true,
        
                 columnDefs: [{
                     orderable: false,
                     "aTargets": [-1]
                 }, {
                     "bSearchable": false,
                     "aTargets": [-1, -2]
                 }]
                 
             });
         });
        </script>
        <?php 
    }
    else {
        print '<div class="info clearboth text-center" >';
        print  $langs->trans('EACCESS_Nothing');
        print '</div>';
    }
    
    
    
    
}


function getService($label='',$icon='',$link='',$desc='')
{
    $res = '<div class="col-lg-3 col-sm-6 col-6 text-center">';
    $res.= '<div class="service-box mt-5 mx-auto">';
    $res.= !empty($link)?'<a href="'.$link.'" >':'';
    $res.= '<i class="fa fa-4x '.$icon.' text-primary mb-3 sr-icons"></i>';
    $res.= '<h5 class="mb-3">'.$label.'</h5>';
    $res.= '<p class="text-muted mb-0">'.$desc.'</p>';
    $res.= !empty($link)?'</a>':'';
    $res.= '</div>';
    $res.= '</div>';
    
    return $res;
}

function printService($label='',$icon='',$link='',$desc='')
{
    print getService($label,$icon,$link,$desc);
}

function printNav($Tmenu)
{
    $context = Context::getInstance();
    
    $menu = '';
    
    $itemDefault=array(
        'active' => false,
        'separator' => false,
    );
    
    foreach ($Tmenu as $item){
        
        $item = array_replace($itemDefault, $item); // applique les valeurs par default
        
        
        if($context->menuIsActive($item['id'])){
            $item['active'] = true;
        }
        
        
        if(!empty($item['overrride'])){
            $menu.= $item['overrride'];
        }
        elseif(!empty($item['children'])) 
        {
            
            $menuChildren='';
            $haveChildActive=false;
            
            foreach($item['children'] as $child){
                
                $item = array_replace($itemDefault, $item); // applique les valeurs par default
                
                if(!empty($child['separator'])){
                    $menuChildren.='<li role="separator" class="divider"></li>';
                }
                
                if($context->menuIsActive($child['id'])){
                    $child['active'] = true;
                    $haveChildActive=true;
                }
                
                
                $menuChildren.='<li class="dropdown-item" ><a href="'.$child['url'].'" class="'.($child['active']?'active':'').'" ">'. $child['name'].'</a></li>';
                
            }
            
            $active ='';
            if($haveChildActive || $item['active']){
                $active = 'active';
            }
            
            $menu.= '<li class="nav-item dropdown">';
            $menu.= '<a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">'. $item['name'].' <span class="caret"></span></a>';
            $menu.= '<ul class="dropdown-menu">'.$menuChildren.'</ul>';
            $menu.= '</li>';
            
        }
        else {
            $menu.= '<li class="nav-item"><a href="'.$item['url'].'" class="nav-link '.($item['active']?'active':'').'" >'. $item['name'].'</a></li>';
        }
        
    }
    
    return $menu;
}

function printSection($content = '', $id = '', $class = '')
{
    print '<section id="'. $id .'" class="'. $class .'" ><div class="container">';
    print $content;
    print '</div></section>';
}


function stdFormHelper($name='', $label='', $value = '', $mode = 'edit', $htmlentities = true, $param = array())
{
    $value = dol_htmlentities($value);
    
    $TdefaultParam = array(
        'type' => 'text',
        'class' => '',
        'valid' => 0, // is-valid: 1  is-invalid: -1
        'feedback' => '',
    );
    
    $param = array_replace($TdefaultParam, $param);
    
    
    print '<div class="form-group row">';
    print '<label for="staticEmail" class="col-4 col-form-label">'.$label;
    if(!empty($param['required']) && $mode!='readonly'){ print '*'; }
    print '</label>';
    
    print '<div class="col-8">';
    
    $class = 'form-control'.($mode=='readonly'?'-plaintext':'').' '.$param['class'];
    
    $feedbackClass='';
    if($param['valid']>0){
        $class .= ' is-valid';
        $feedbackClass='valid-feedback';
    }
    elseif($param['valid']<0){
        $class .= ' is-invalid';
        $feedbackClass='invalid-feedback';
    }
    
    $readonly = ($mode=='readonly'?'readonly':'');
    
    print '<input id="'.$name.'" name="'.$name.'" type="'.$param['type'].'" '.$readonly.' class="'.$class.'"  value="'.$value.'" ';
    if(!empty($param['required'])){
        print ' required ';
    }
    print ' >';
    
    if(!empty($param['help'])){
        print '<small class="text-muted">'.$param['help'].'</small>';
    }
    
    if(!empty($param['feedback'])){
        print '<div class="'.$feedbackClass.'">'.$param['error'].'</div>';
    }
    
    print '</div>';
    print '</div>';
}

/**
 *   	uasort callback function to Sort menu fields
 *
 *   	@param	array			$a    			PDF lines array fields configs
 *   	@param	array			$b    			PDF lines array fields configs
 *      @return	int								Return compare result
 *      
 *      // Sorting
 *      uasort ( $this->cols, array( $this, 'menuSort' ) );
 *      
 */
function menuSortInv($a, $b) {
    
    if(empty($a['rank'])){ $a['rank'] = 0; }
    if(empty($b['rank'])){ $b['rank'] = 0; }
    if ($a['rank'] == $b['rank']) {
        return 0;
    }
    return ($a['rank'] < $b['rank']) ? -1 : 1;
    
}

/**
 *   	uasort callback function to Sort menu fields
 *
 *   	@param	array			$a    			PDF lines array fields configs
 *   	@param	array			$b    			PDF lines array fields configs
 *      @return	int								Return compare result
 *
 *      // Sorting
 *      uasort ( $this->cols, array( $this, 'menuSort' ) );
 *
 */
function menuSort($a, $b) {
    
    if(empty($a['rank'])){ $a['rank'] = 0; }
    if(empty($b['rank'])){ $b['rank'] = 0; }
    if ($a['rank'] == $b['rank']) {
        return 0;
    }
    return ($a['rank'] > $b['rank']) ? -1 : 1;
    
}

















/*
 * N'est finalement pas utilisé, utiliser datatable en html5 plutot
 */
function print_invoiceList($socId = 0)
{
    global $langs,$db;
    $context = Context::getInstance();
    
    dol_include_once('compta/facture/class/facture.class.php');
    
    $sql = 'SELECT COUNT(*) ';
    $sql.= ' FROM `'.MAIN_DB_PREFIX.'facture` f';
    $sql.= ' WHERE fk_soc = '. intval($socId);
    $sql.= ' AND fk_statut > 0';
    $sql.= ' ORDER BY f.datef DESC';
    
    $countItems = $context->dbTool->getvalue($sql);
    
    if(!empty($countItems))
    {
        print '<table id="ajax-invoice-list" class="table table-striped" >';
        print '<thead>';
        
        print '<tr>';
        print ' <th>'.$langs->trans('Ref').'</th>';
        print ' <th>'.$langs->trans('Date').'</th>';
        print ' <th  class="text-right" >'.$langs->trans('Amount_HT').'</th>';
        //print ' <th  class="text-right" >'.$langs->trans('Status').'</th>';
        print ' <th  class="text-right" ></th>';
        print '</tr>';
        
        print '</thead>';
        print '</table>';
        
        $jsonUrl = $context->getRootUrl().'script/interface.php?action=getInvoicesList';
        ?>
<script type="text/javascript" >
 $(document).ready(function(){
     $("#ajax-invoice-list").DataTable({
         "language": {
             "url": "<?php print $context->getRootUrl(); ?>vendor/data-tables/french.json"
         },
         "ajax": '<?php print $jsonUrl; ?>',

         responsive: true,
    	 "columns": [
             { "data": "view"},
             { "data": "date"},
             { "data": "price"},
             //{ "data": "statut" },
             { "data": "forcedownload" }
         ],

         columnDefs: [{
             orderable: false,
             "aTargets": [-1]
         },{
             "bSearchable": false,
             "aTargets": [-1, -2]
         }]
         
     });
 });
</script>
<?php 
    }
    else {
        print '<div class="info clearboth text-center" >';
        print  $langs->trans('EACCESS_Nothing');
        print '</div>';
    }
}


/*
 * N'est finalement pas utilisé, utiliser datatable en html5 plutot
 */
function json_invoiceList($socId = 0, $limit=25, $offset=0)
{
    global $langs,$db;
    $context = Context::getInstance();
    
    $langs->load('factures');
    
    
    dol_include_once('compta/facture/class/facture.class.php');
    
    $JSON = array();
    
    
    $sql = 'SELECT rowid ';
    $sql.= ' FROM `'.MAIN_DB_PREFIX.'facture` f';
    $sql.= ' WHERE fk_soc = '. intval($socId);
    $sql.= ' AND fk_statut > 0';
    $sql.= ' LIMIT '.intval($offset).','.intval($limit);
    
    $tableItems = $context->dbTool->executeS($sql);
    
    if(!empty($tableItems))
    {
        foreach ($tableItems as $item)
        {
            
            $object = new Facture($db);
            $object->fetch($item->rowid);
            $dowloadUrl = $context->getRootUrl().'script/interface.php?action=downloadInvoice&id='.$object->id;
            
            
            $filename = DOL_DATA_ROOT.'/'.$object->last_main_doc;
            $disabled = false;
            $disabledclass='';
            if(empty($filename) || !file_exists($filename) || !is_readable($filename)){
                $disabled = true;
                $disabledclass=' disabled ';
            }
            
            $row = array(
                'view' => '<a href="'.$dowloadUrl.'" target="_blank" >'.$object->ref.'</a>',
                'ref' => $object->ref, // for order
                'time' => $object->date, // for order
                'amount' => $object->multicurrency_total_ttc, // for order
                'date' => dol_print_date($object->date),
                'price' => price($object->multicurrency_total_ttc).' '.$object->multicurrency_code,
                'ref' => '<a href="'.$dowloadUrl.'" target="_blank" >'.$object->ref.'</a>',
                'forcedownload' => '<a class="btn btn-xs btn-primary" href="'.$dowloadUrl.'&amp;forcedownload=1" target="_blank" ><i class="fa fa-download"></i> '.$langs->trans('Download').'</a>',
                //'statut' => $object->getLibStatut(0),
            );
            
            if($disabled){
                $row['ref'] = $object->ref;
                $row['link'] = $langs->trans('DocumentFileNotAvailable');
            }
            
            $JSON['data'][] = $row;
        }
        
    }
    
    return json_encode($JSON);
}


/*
 * N'est finalement pas utilisé, utiliser datatable en html5 plutot
 */
function print_orderList($socId = 0)
{
    global $langs,$db;
    $context = Context::getInstance();
    
    $sql = 'SELECT COUNT(*) ';
    $sql.= ' FROM `'.MAIN_DB_PREFIX.'commande` c';
    $sql.= ' WHERE fk_soc = '. intval($socId);
    $sql.= ' AND fk_statut > 0';
    $sql.= ' ORDER BY c.date_commande DESC';
    
    $countItems = $context->dbTool->getvalue($sql);
    
    if(!empty($countItems))
    {
        print '<table id="ajax-order-list" class="table table-striped" >';
        print '<thead>';
        
        print '<tr>';
        print ' <th>'.$langs->trans('Ref').'</th>';
        print ' <th>'.$langs->trans('Date').'</th>';
        print ' <th  class="text-right" >'.$langs->trans('Amount_HT').'</th>';
        print ' <th  class="text-right" >'.$langs->trans('Status').'</th>';
        print ' <th  class="text-right" ></th>';
        print '</tr>';
        
        print '</thead>';
        print '</table>';
        
        $jsonUrl = $context->getRootUrl().'script/interface.php?action=getOrdersList';
        ?>
<script type="text/javascript" >
 $(document).ready(function(){
     $("#ajax-order-list").DataTable({
         "language": {
             "url": "<?php print $context->getRootUrl(); ?>vendor/data-tables/french.json"
         },

         responsive: true,
         "ajax": '<?php print $jsonUrl; ?>',
    	 "columns": [
             { "data": "ref" },
             { "data": "date" },
             { "data": "price" },
             { "data": "statut" },
             { "data": "link" }
         ],

         columnDefs: [{
             orderable: false,
             "aTargets": [-1]
         }, {
             "bSearchable": false,
             "aTargets": [-1, -2]
         }]
         
     });
 });
</script>
<?php 
    }
    else {
        print '<div class="info clearboth text-center" >';
        print  $langs->trans('EACCESS_Nothing');
        print '</div>';
    }
}





/*
 * N'est finalement pas utilisé, utiliser datatable en html5 plutot
 */
function json_orderList($socId = 0, $limit=25, $offset=0)
{
    global $langs,$db;
    $context = Context::getInstance();
    
    $langs->load('orders');
    
    dol_include_once('commande/class/commande.class.php');
    
    $JSON = array();
    
    
    $sql = 'SELECT rowid ';
    $sql.= ' FROM `'.MAIN_DB_PREFIX.'commande` c';
    $sql.= ' WHERE fk_soc = '. intval($socId);
    $sql.= ' AND fk_statut > 0';
    $sql.= ' ORDER BY c.date_commande DESC';
    $sql.= ' LIMIT '.intval($offset).','.intval($limit);
    
    $tableItems = $context->dbTool->executeS($sql);
    
    if(!empty($tableItems))
    {
        foreach ($tableItems as $item)
        {
            
            $object = new Commande($db);
            $object->fetch($item->rowid);
            $dowloadUrl = $context->getRootUrl().'script/interface.php?action=downloadCommande&id='.$object->id;
            
            
            $filename = DOL_DATA_ROOT.'/'.$object->last_main_doc;
            $disabled = false;
            $disabledclass='';
            if(empty($object->last_main_doc) || !file_exists($filename) || !is_readable($filename)){
                $disabled = true;
                $disabledclass=' disabled ';
            }
            
            $row = array(
                //'ref' => $object->ref,//'<a href="'.$dowloadUrl.'" target="_blank" >'.$object->ref.'</a>', //
                'date' => dol_print_date($object->date),
                'price' => price($object->multicurrency_total_ttc).' '.$object->multicurrency_code,
                'ref' => '<a href="'.$dowloadUrl.'" target="_blank" >'.$object->ref.'</a>',
                'link' => '<a class="btn btn-xs btn-primary" href="'.$dowloadUrl.'&amp;forcedownload=1" target="_blank" ><i class="fa fa-download"></i> '.$langs->trans('Download').'</a>',
                'statut' => $object->getLibStatut(0)
            );
            
            if($disabled){
                $row['ref'] = $object->ref;
                $row['link'] = $langs->trans('DocumentFileNotAvailable');
            }
            
            $JSON['data'][] = $row;
        }
       
    }
    
    return json_encode($JSON);
}


/*
 * N'est finalement pas utilisé, utiliser datatable en html5 plutot
 */
function print_propalList($socId = 0)
{
    global $langs,$db;
    $context = Context::getInstance();
    
    dol_include_once('comm/propal/class/propal.class.php');
    
    $sql = 'SELECT COUNT(*) ';
    $sql.= ' FROM `'.MAIN_DB_PREFIX.'propal` p';
    $sql.= ' WHERE fk_soc = '. intval($socId);
    $sql.= ' AND fk_statut > 0';
    $sql.= ' ORDER BY p.datep DESC';
    
    $countItems = $context->dbTool->getvalue($sql);
    
    if(!empty($countItems))
    {
        print '<table id="ajax-propal-list" class="table table-striped" >';
        print '<thead>';
        
        print '<tr>';
        print ' <th>'.$langs->trans('Ref').'</th>';
        print ' <th>'.$langs->trans('Date').'</th>';
        print ' <th  class="text-right" >'.$langs->trans('Amount_HT').'</th>';
        print ' <th  class="text-right" >'.$langs->trans('Status').'</th>';
        print ' <th  class="text-right" >'.$langs->trans('DateFinValidite').'</th>';
        print ' <th  class="text-right" ></th>';
        print '</tr>';
        
        print '</thead>';
        print '</table>';
        
        $jsonUrl = $context->getRootUrl().'script/interface.php?action=getPropalsList';
        ?>
    <script type="text/javascript" >
     $(document).ready(function(){
         $("#ajax-propal-list").DataTable({
             "language": {
                 "url": "<?php print $context->getRootUrl(); ?>vendor/data-tables/french.json"
             },
             "ajax": '<?php print $jsonUrl; ?>',
    
             responsive: true,
        	 "columns": [
                 { "data": "ref" },
                 { "data": "date" },
                 { "data": "price" },
                 { "data": "statut" },
                 { "data": "fin_validite" },
                 { "data": "link" }
             ],
    
             columnDefs: [{
                 orderable: false,
                 "aTargets": [-1]
             }, {
                 "bSearchable": false,
                 "aTargets": [-1, -2]
             }]
             
         });
     });
    </script>
    <?php 
    }
    else {
        print '<div class="info clearboth text-center" >';
        print  $langs->trans('EACCESS_Nothing');
        print '</div>';
    }
}

/*
 * N'est finalement pas utilisé, utiliser datatable en html5 plutot
 */
function json_propalList($socId = 0, $limit=25, $offset=0)
{
    global $langs,$db;
    $context = Context::getInstance();
    
    $langs->load('orders');
    
    dol_include_once('comm/propal/class/propal.class.php');
    
    $JSON = array();
    
    
    $sql = 'SELECT rowid ';
    $sql.= ' FROM `'.MAIN_DB_PREFIX.'propal` p';
    $sql.= ' WHERE fk_soc = '. intval($socId);
    $sql.= ' AND fk_statut > 0';
    $sql.= ' ORDER BY p.datep DESC';
    $sql.= ' LIMIT '.intval($offset).','.intval($limit);
    
    $tableItems = $context->dbTool->executeS($sql);
    
    if(!empty($tableItems))
    {
        foreach ($tableItems as $item)
        {
            
            $object = new Propal($db);
            $object->fetch($item->rowid);
            $dowloadUrl = $context->getRootUrl().'script/interface.php?action=downloadPropal&id='.$object->id;
            
            $filename = DOL_DATA_ROOT.'/'.$object->last_main_doc;
            $disabled = false;
            $disabledclass='';
            if(empty($filename) ||  !file_exists($filename) || !is_readable($filename)){
                $disabled = true;
                $disabledclass=' disabled ';
            }
            
            
            $row = array(
                //'ref' => $object->ref,//'<a href="'.$dowloadUrl.'" target="_blank" >'.$object->ref.'</a>', //
                'date' => dol_print_date($object->date),
                'price' => price($object->multicurrency_total_ttc).' '.$object->multicurrency_code,
                'ref' => '<a class="'.$disabledclass.'" href="'.$dowloadUrl.'" target="_blank" >'.$object->ref.'</a>',
                'link' => '<a class="btn btn-xs btn-primary '.$disabledclass.'" href="'.$dowloadUrl.'&amp;forcedownload=1" target="_blank" ><i class="fa fa-download"></i> '.$langs->trans('Download').'</a>',
                'statut' => $object->getLibStatut(0),
                'fin_validite' => dol_print_date($object->fin_validite)
            );
            
            if($disabled){
                $row['ref'] = $object->ref;
                $row['link'] = $langs->trans('DocumentFileNotAvailable');
            }
            
            $JSON['data'][] = $row;
        }
        
    }
    
    return json_encode($JSON);
}

function load_last_main_doc(&$object) {

	global $conf;

	if(empty($object->last_main_doc)) {
		$last_main_doc = ($object->element == 'propal' ? 'propale' : $object->element).'/'.$object->ref.'/'.$object->ref.'.pdf';
		if(is_readable(DOL_DATA_ROOT.'/'.$last_main_doc) && is_file ( DOL_DATA_ROOT.'/'.$last_main_doc )) $object->last_main_doc = $last_main_doc;
	}

}

	    
	    
	    
	    
	    
	    
	    
	    
	    
	    
	    
	    
	    <?php
		    function print_agendaTable($socId = 1)
{
    global $langs,$db;
    $context = Context::getInstance();
    
    //dol_include_once('compta/facture/class/facture.class.php');
    dol_include_once('comm/action/class/actioncomm.class.php');
    $langs->load('actioncomm');
    
    
    $sql = 'SELECT id';
    $sql.= ' FROM `'.MAIN_DB_PREFIX.'actioncomm` ac';
    //$sql.= ' WHERE fk_contact = '. intval($socId);
    //$sql.= ' AND code = '.AC_OTH;
    $sql.= ' ORDER BY actioncomm.datec DESC';
    
    $tableItems = $context->dbTool->executeS($sql);
    
    if(!empty($tableItems))
    {
        
        
        
        
        print '<table id="actioncomm-list" class="table table-striped" >';
        
        print '<thead>';
        
        print '<tr>';
        print ' <th class="text-center" >'.$langs->trans('Ref').'</th>';
        print ' <th class="text-center" >'.$langs->trans('Date').'</th>';
        //print ' <th class="text-center" >'.$langs->trans('DatePayLimit').'</th>';
        //print ' <th class="text-center" >'.$langs->trans('Status').'</th>';
        //if(!empty($conf->global->EACCESS_ACTIVATE_INVOICES_HT_COL)){
         //   print ' <th class="text-center" >'.$langs->trans('Amount_HT').'</th>';
        //}
        print ' <th class="text-center" >'.$langs->trans('Code').'</th>';
        print ' <th class="text-center" >'.$langs->trans('Project').'</th>';
        

        print ' <th class="text-center" ></th>';
        print '</tr>';
        
        print '</thead>';
        
        print '<tbody>';
        foreach ($tableItems as $item)
        {
            $object = new actioncomm($db);
            $object->fetch($item->id);
        load_last_main_doc($object);
            $dowloadUrl = $context->getRootUrl().'script/interface.php?action=downloadagenda&id='.$object->id;
            //var_dump($object); exit;
            //$totalpaye = $object->getSommePaiement();
            //$totalcreditnotes = $object->getSumCreditNotesUsed();
            //$totaldeposits = $object->getSumDepositsUsed();
            //$resteapayer = price2num($object->total_ttc - $totalpaye - $totalcreditnotes - $totaldeposits, 'MT');
            
            if(!empty($object->last_main_doc)){
                $viewLink = '<a href="'.$dowloadUrl.'" target="_blank" >'.$object->ref_ext.'</a>';
                $downloadLink = '<a class="btn btn-xs btn-primary" href="'.$dowloadUrl.'&amp;forcedownload=1" target="_blank" ><i class="fa fa-download"></i> '.$langs->trans('Download').'</a>';
            }
            else{
                $viewLink = $object->ref_ext;
                $downloadLink =  $langs->trans('DocumentFileNotAvailable');
            }
            
            
            print '<tr >';
            
            print ' <td data-search="'.$object->ref_ext.'" data-order="'.$object->ref_ext.'" >'.$object->ref_ext.'</td>';

            print ' <td data-search="'.$object->datep.'" data-order="'.dol_print_date($object->datep).'"  >'.dol_print_date($object->datep).'</td>';

            print ' <td data-search="'.$object->datep2.'" data-order="'.dol_print_date($object->datep2).'"  >'.dol_print_date($object->datep2).'</td>';

            //print ' <td  >'.$object->getLibStatut(0).'</td>';

            //print ' <td  >'.$object->code.'</td>';

            //print ' <td data-search="'.$object->fk_project.'" data-order="'.$object->fk_project.'" ><A href="/dolibarr900/htdocs/externalaccess/www/tpl/fiche/details_project.php?rowid='.$object->fk_project.'"target="_blank">'.$object->fk_project.'<A></td>';


            //if(!empty($conf->global->EACCESS_ACTIVATE_INVOICES_HT_COL)){
                //print ' <td data-order="'.$object->multicurrency_total_ht.'" class="text-right" >'.price($object->multicurrency_total_ht)  //.' '.$object->multicurrency_code.'</td>';
           // }
            
            //print ' <td data-order="'.$object->multicurrency_total_ttc.'" class="text-right" >'.price($object->multicurrency_total_ttc)  .' '.$object->multicurrency_code.'</td>';
            //print ' <td data-order="'.$resteapayer.'" class="text-right" >'.price($resteapayer)  .' '.$object->multicurrency_code.'</td>';
            print ' <td  class="text-right" >'.$downloadLink.'</td>';
            print '</tr>';
            
        }
        print '</tbody>';
        
        print '</table>';
        $jsonUrl = $context->getRootUrl().'script/interface.php?action=getContractList';
    ?>
    <script type="text/javascript" >
     $(document).ready(function(){
         $("#invoice-list").DataTable({
             "language": {
                 "url": "<?php print $context->getRootUrl(); ?>vendor/data-tables/french.json"
             },

             responsive: true,
             columnDefs: [{
                 orderable: false,
                 "aTargets": [-1]
             },{
                 "bSearchable": false,
                 "aTargets": [-1, -2]
             }]
         });
     });
    </script>
    <?php 
    }
    else {
        print '<div class="info clearboth text-center" >';
        print  $langs->trans('EACCESS_Nothing');
        print '</div>';
    }


        
}
