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

include_once __DIR__ . '/ticket.lib.php';

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


function print_invoiceTable($socId = 0)
{
    global $langs, $db, $conf;
    $context = Context::getInstance();

    dol_include_once('compta/facture/class/facture.class.php');

    $langs->load('factures');


    $sql = 'SELECT rowid ';
    $sql.= ' FROM `'.MAIN_DB_PREFIX.'facture` f';
    $sql.= ' WHERE fk_soc = '. intval($socId);
    $sql.= ' AND fk_statut > 0';
    $sql.= ' AND entity IN ('.getEntity("invoice").')'; //Compatibility with Multicompany
    $sql.= ' ORDER BY f.datef DESC';

    $tableItems = $context->dbTool->executeS($sql);

    if(!empty($tableItems))
    {
        print '<table id="invoice-list" class="table table-striped" >';
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
                $downloadLink = '<a class="btn btn-xs btn-primary btn-strong" href="'.$dowloadUrl.'&amp;forcedownload=1" target="_blank" ><i class="fa fa-download"></i> '.$langs->trans('Download').'</a>';
            }
            else{
                $viewLink = $object->ref;
                $downloadLink =  $langs->trans('DocumentFileNotAvailable');
            }


            print '<tr >';
            print ' <td data-search="'.$object->ref.'" data-order="'.$object->ref.'" >'.$viewLink.'</td>';
            print ' <td data-order="'.$object->date.'" data-search="'.dol_print_date($object->date).'"  >'.dol_print_date($object->date).'</td>';
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

function print_projetsTable($socId = 1)
{
    global $langs,$db;
    $context = Context::getInstance();

    //dol_include_once('compta/facture/class/facture.class.php');
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
        print ' <th class="text-center" >'.$langs->trans('Budget').'</th>';
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

function print_propalTable($socId = 0)
{
    global $langs,$db;
    $context = Context::getInstance();

    dol_include_once('comm/propal/class/propal.class.php');



    $sql = 'SELECT rowid ';
    $sql.= ' FROM `'.MAIN_DB_PREFIX.'propal` p';
    $sql.= ' WHERE fk_soc = '. intval($socId);
    $sql.= ' AND fk_statut > 0';
    $sql.= ' AND entity IN ('.getEntity("propal").')';//Compatibility with Multicompany
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
            $downloadUrl = $context->getRootUrl().'script/interface.php?action=downloadPropal&id='.$object->id;


            if(!empty($object->last_main_doc)){
                $viewLink = '<a href="'.$downloadUrl.'" target="_blank" >'.$object->ref.'</a>';
                $downloadLink = '<a class="btn btn-xs btn-primary btn-strong" href="'.$downloadUrl.'&amp;forcedownload=1" target="_blank" ><i class="fa fa-download"></i> '.$langs->trans('Download').'</a>';
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
    $sql.= ' AND entity IN ('.getEntity("order").')';//Compatibility with Multicompany
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
                $downloadLink = '<a class="btn btn-xs btn-primary btn-strong" href="'.$dowloadUrl.'&amp;forcedownload=1" target="_blank" ><i class="fa fa-download"></i> '.$langs->trans('Download').'</a>';
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


function print_expeditionTable($socId = 0)
{
	global $langs,$db;
	$context = Context::getInstance();

	include_once DOL_DOCUMENT_ROOT . '/expedition/class/expedition.class.php';
	include_once DOL_DOCUMENT_ROOT . '/core/lib/pdf.lib.php';

	$langs->load('sendings');

	$sql = 'SELECT rowid ';
	$sql.= ' FROM `'.MAIN_DB_PREFIX.'expedition` ';
	$sql.= ' WHERE fk_soc = '. intval($socId);
	$sql.= ' AND fk_statut > 0';
    	$sql.= ' AND entity IN ('.getEntity("expedition").')';//Compatibility with Multicompany
	$sql.= ' ORDER BY date_expedition DESC';

	$tableItems = $context->dbTool->executeS($sql);

	if(!empty($tableItems))
	{
		print '<table id="expedition-list" class="table table-striped" >';
		print '<thead>';
		print '<tr>';
		print ' <th class="text-center" >'.$langs->trans('Ref').'</th>';
		print ' <th class="text-center" >'.$langs->trans('pdfLinkedDocuments').'</th>';
		print ' <th class="text-center" >'.$langs->trans('DateLivraison').'</th>';
		print ' <th class="text-center" >'.$langs->trans('Status').'</th>';
		print ' <th class="text-center" ></th>';
		print '</tr>';
		print '</thead>';
		print '<tbody>';
		foreach ($tableItems as $item)
		{
			$object = new Expedition($db);
			$object->fetch($item->rowid);
			load_last_main_doc($object);
			$dowloadUrl = $context->getRootUrl().'script/interface.php?action=downloadExpedition&id='.$object->id;

			if(!empty($object->last_main_doc)){
				$viewLink = '<a href="'.$dowloadUrl.'" target="_blank" >'.$object->ref.'</a>';
				$downloadLink = '<a class="btn btn-xs btn-primary btn-strong" href="'.$dowloadUrl.'&amp;forcedownload=1" target="_blank" ><i class="fa fa-download"></i> '.$langs->trans('Download').'</a>';
			}
			else{
				$viewLink = $object->ref;
				$downloadLink =  $langs->trans('DocumentFileNotAvailable');
			}

			$reftoshow = '';
			$reftosearch = '';
			$linkedobjects = pdf_getLinkedObjects($object,$langs);
			if (! empty($linkedobjects))
			{
				foreach($linkedobjects as $linkedobject)
				{
				    if(!empty($reftoshow)){
						$reftoshow.= ', ';
						$reftosearch.= ' ';
                    }
					$reftoshow.= $linkedobject["ref_value"]; //$linkedobject["ref_title"].' : '.
					$reftosearch.= $linkedobject["ref_value"];
				}
			}
			print '<tr>';
			print ' <td data-search="'.$object->ref.'" data-order="'.$object->ref.'"  >'.$viewLink.'</td>';
			print ' <td data-search="'.$reftosearch.'" data-order="'.$reftosearch.'"  >'.$reftoshow.'</td>';
			print ' <td data-search="'.dol_print_date($object->date_delivery).'" data-order="'.$object->date_delivery.'" >'.dol_print_date($object->date_delivery).'</td>';
			print ' <td class="text-center" >'.$object->getLibStatut(0).'</td>';
			print ' <td class="text-right" >'.$downloadLink.'</td>';
			print '</tr>';

		}
		print '</tbody>';

		print '</table>';
		?>
        <script type="text/javascript" >
            $(document).ready(function(){
                $("#expedition-list").DataTable({
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

function print_orTable($socId = 0)
{
	global $langs,$db,$conf;
	$context = Context::getInstance();
	
	include_once DOL_DOCUMENT_ROOT . '/custom/operationorder/class/operationorder.class.php';
	include_once DOL_DOCUMENT_ROOT . '/core/lib/pdf.lib.php';
	
	$allowedstatus = array();
	$allowedstatus = json_decode($conf->global->EACCESS_OR_STATUT_DISP);
		$filterstatut='';
		foreach ($allowedstatus as $statut){
			$filterstatut .= "'".$statut."',";
		}
		$filterstatut=substr($filterstatut,0,-1);
		
	$allowedtypes = array();
	$allowedtypes = json_decode($conf->global->EACCESS_OR_TYPE_DISP);
		$filtertype='';
		foreach ($allowedtypes as $type){
			$filtertype .= $type.',';
		}
		$filtertype=substr($filtertype,0,-1);
		
	$langs->load('sendings');
		
	$sql = 'SELECT o.rowid as id, t.label as type, o.last_main_doc as last_main_doc, o.ref as ref, v.immatriculation as immatriculation, ';
	$sql .= 'o.date_creation as date_creation, (IF(o.total_ht_part IS NOT NULL, o.total_ht_part, 0)+IF(o.total_ht_mo IS NOT NULL, o.total_ht_mo, 0)+IF(o.total_ht_external IS NOT NULL, o.total_ht_external, 0)) as total_ht  , s.label as statut ';
	$sql.= ' FROM `'.MAIN_DB_PREFIX.'operationorder` as o';
	$sql.= ' INNER JOIN `'.MAIN_DB_PREFIX.'operationorder_status` as s ON s.rowid=o.status';
	$sql.= ' INNER JOIN `'.MAIN_DB_PREFIX.'c_operationorder_type` as t ON t.rowid=o.fk_c_operationorder_type';
	$sql.= ' INNER JOIN `'.MAIN_DB_PREFIX.'operationorder_extrafields` as es ON es.fk_object=o.rowid';
	$sql.= ' INNER JOIN `'.MAIN_DB_PREFIX.'dolifleet_vehicule` as v ON v.rowid=es.fk_dolifleet_vehicule';
	$sql.= ' WHERE o.fk_soc = '. intval($socId);
	$sql.= ' AND s.code IN ('.$filterstatut.") ";
	$sql.= ' AND fk_c_operationorder_type IN ('.$filtertype.") ";
	$sql.= ' AND o.entity IN ('.getEntity("operationorder").')';//Compatibility with Multicompany
	$sql.= ' ORDER BY o.date_creation DESC';
	//print $sql;exit;
	$resql=$db->query($sql);
	
	if(!empty($resql))
	{		
		print '<table id="operationorder-list" class="table table-striped" >';		
		print '<thead>';		
		print '<tr>';
		print ' <th class="text-center" >'.$langs->trans('Ref').'</th>';
		print ' <th class="text-center" >'.$langs->trans('vehicule').'</th>';
		print ' <th class="text-center" >'.$langs->trans('DateCreation').'</th>';
		print ' <th class="text-center" >'.$langs->trans('montantHT').'</th>';
		print ' <th class="text-center" >'.$langs->trans('Type').'</th>';
		print ' <th class="text-center" >'.$langs->trans('Status').'</th>';
		print ' <th class="text-center" ></th>';
		print '</tr>';		
		print '</thead>';	
		
		print '<tbody>';
		while ($object = $db->fetch_object($resql)){
		$or=new OperationOrder ($db);
		$or->fetch($object->id);
			
			//print_r ($object); exit;
			load_last_main_doc($or);
			$dowloadUrl = $context->getRootUrl().'script/interface.php?action=downloadOperationOrder&id='.$object->id;
			
			if(!empty($object->last_main_doc)){
				$viewLink = '<a href="'.$dowloadUrl.'" target="_blank" >'.$object->ref.'</a>';
				$downloadLink = '<a class="btn btn-xs btn-primary btn-strong" href="'.$dowloadUrl.'&amp;forcedownload=1" target="_blank" ><i class="fa fa-download"></i> '.$langs->trans('Download').'</a>';
			}
			else{
				$viewLink = $object->ref;
				$downloadLink =  $langs->trans('DocumentFileNotAvailable');
			}			
			$reftoshow = '';
			$reftosearch = '';
			$linkedobjects = pdf_getLinkedObjects($or,$langs);
			if (! empty($linkedobjects))
			{
				foreach($linkedobjects as $linkedobject)
				{
					if(!empty($reftoshow)){
						$reftoshow.= ', ';
						$reftosearch.= ' ';
					}
					$reftoshow.= $linkedobject["ref_value"]; //$linkedobject["ref_title"].' : '.
					$reftosearch.= $linkedobject["ref_value"];
				}
			}			
			print '<tr>';
			print ' <td data-search="'.$object->ref.'" data-order="'.$object->ref.'"  >'.$viewLink.'</td>';
			print ' <td data-search="'.$object->immatriculation.'" data-order="'.$object->immatriculation.'"  >'.$object->immatriculation.'</td>';
			print ' <td data-search="'.dol_print_date($object->date_creation).'" data-order="'.$object->date_creation.'" >'.dol_print_date($object->date_creation).'</td>';
			print ' <td data-search="'.$object->total_ht.'" data-order="'.$object->total_ht.'"  class="text-right" >'.price($object->total_ht, 0, '', 1, 2, 2)  .' </td>';
			print ' <td data-search="'.$object->type.'" data-order="'.$object->type.'"  >'.$object->type.'</td>';
			print ' <td class="text-center" >'.$or->getLibStatut(0).'</td>';			
			print ' <td class="text-right" >'.$downloadLink.'</td>';			
			print '</tr>';			
		}
		print '</tbody>';
		print '</table>';
		?>
        <script type="text/javascript" >
            $(document).ready(function(){
                $("#operationorder-list").DataTable({
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

function print_disponibilityListTable($socId = 0)
{
	global $langs,$db;
	$timestamp=dol_mktime (0, 0, 0, date("m" ), date("d"), date("Y"));
	$dateDebut = date ('Y-m-d H:i:s', $timestamp-604800);
	$dateFin = date ('Y-m-d 23:59:59', $timestamp+1209600);
	$context = Context::getInstance();
	
	include_once DOL_DOCUMENT_ROOT . '/custom/dolifleet/class/vehicule.class.php';
	include_once DOL_DOCUMENT_ROOT . '/core/lib/pdf.lib.php';
	
	$langs->load('sendings');
	
	$searchvh=GETPOST("searchvh");
	$searchtype=GETPOST("searchtype");
	$sql = 'SELECT v.rowid as id, v.immatriculation as immatriculation, t.label as type';
	$sql.= ' FROM `'.MAIN_DB_PREFIX.'dolifleet_vehicule` as v';
	$sql.= ' INNER JOIN `'.MAIN_DB_PREFIX.'c_dolifleet_vehicule_type` as t ON t.rowid=v.fk_vehicule_type';	
	$sql.= ' WHERE v.fk_soc = '. intval($socId);
	if($searchvh){
		$sql.= " AND v.immatriculation LIKE '%" .$searchvh . "%'";
	}
	if($searchtype){
		$sql.= " AND t.label LIKE '%" .$searchtype . "%'";
	}
	$sql.= ' AND v.status=1';
	$sql.= ' ORDER BY v.immatriculation DESC';
	
	$planning= array();
	$planning=calculPlanning($socId, $dateDebut, $dateFin);
	
	$resql=$db->query($sql);
	if(!empty($resql))
	{
		print '<table id="disponibility-list" class="table table-striped" >';
		print '<thead>';
		print '<tr>';
		print ' <th style="border-left: 1px solid #dee2e6" class="text-center" > '.$langs->trans('vehicule').' </th>';
		print ' <th style="border-right: 1px solid #dee2e6" class="text-center" > '.$langs->trans('vehiculetype').' </th>';
		
		for ($i=-7; $i<15; $i++){
			$date = date ('d /m', $timestamp+($i*86400));
			if ($date==date ('d /m', $timestamp)){
				print '<th style="border: 1px solid #F05F40" colspan="2" class="text-center" >'.$date.'</th>';
			}else{
			print ' <th style="border-left: 1px solid #dee2e6" colspan="2" class="text-center" >'.$date.'</th>';
			}
		}

		print '</tr>';
		print '<tr>';
		print '<form method="post" action="'.$_SERVER["PHP_SELF"].'?controller=disponibility">';
		print ' <th style="border-left: 1px solid #dee2e6" class="text-center" ><input  style="width:75px" type="text" name="searchvh" value="'.$searchvh.'"></th>';
		print ' <th style="border-right: 1px solid #dee2e6; white-space: nowrap;" class="text-center" ><input style="width:75px" type="text" name="searchtype" value="'.$searchtype.'"><button name="button"><i class="fa fa-search"></i></button> </th>';
		print '</form>';
		for ($i=1; $i<23; $i++){	
			if ($i==7 || $i==8){	
				$color= '#F05F40';
			}else{
				$color= '#dee2e6';
			}
		print ' <th style="border-left: 1px solid #dee2e6" class="text-center" >am</th>';
		print ' <th style="border-right: 1px solid '.$color.'" class="text-center" >pm</th>';
		}
		print '</tr>';
		print '</thead>';
		
		print '<tbody>';

		while ($object = $db->fetch_object($resql)){
			$dispo=new doliFleetVehicule($db);
			$dispo->fetch($object->id);

			print '<tr>';
			print ' <td style="border-left: 1px solid #dee2e6" data-search="'.$object->immatriculation.'" data-order="'.$object->immatriculation.'"  >'.$object->immatriculation.'</td>';
			print ' <td style="border-right: 1px solid #dee2e6" data-search="'.$object->type.'" data-order="'.$object->type.'"  >'.$object->type.'</td>';
			
			for ($i=-7; $i<15; $i++){
				$date = date ('Ymd', $timestamp+($i*86400));
				
				for ($j=1; $j<3; $j++){
					if ($j==1){
						print ' <td ';
						//afficher les statuts atelier du matin
						$bgcolor='';
						if($planning[$object->immatriculation][$date]["matin"]['color']){
            				$bgcolor= 'bgcolor="'.$planning[$object->immatriculation][$date]["matin"]['color'].'"';
						} 
						print $bgcolor.' style="border-left: 1px solid #dee2e6" class="text-center" >';					
						$infobulle="";
						$path="";
						//afficher les dates prévisionnelles
						if($planning[$object->immatriculation][$date]["matin"]['picto']){
							$infobulle=$planning[$object->immatriculation][$date]["matin"]['picto'];
							$path = DOL_URL_ROOT.'/theme/eldy/img/error.png';
							print '<img class="inline-block" src="'.$path.'" title="'.$infobulle.'">';						
						}
						print '</td>';						
					}else{
						print ' <td ';
						//afficher les statuts atelier de l'après midi
						$bgcolor='';
						if($planning[$object->immatriculation][$date]["après-midi"]['color']){
							$bgcolor= 'bgcolor="'.$planning[$object->immatriculation][$date]["après-midi"]['color'].'"';
						}
						print $bgcolor.' style="border-right: 1px solid';
						//materialiser la date du jour
						if ($date==date ('Ymd', $timestamp) || $date==date ('Ymd', $timestamp-86400)){
							print ' #F05F40" class="text-center" >';
						}else{
							print '	#dee2e6" class="text-center" >';
						}
					print '</td>';
					}
				}				
			}
			print '</tr>';
		}
		print '</tbody>';
		print '</table>';
	}
	else {
		print '<div class="info clearboth text-center" >';
		print  $langs->trans('EACCESS_Nothing');
		print '</div>';
	}
}

function calculPlanning($socId, $dateDebut, $dateFin){
	global $db, $conf;	
	dol_include_once('operationorder/lib/operationorder.lib.php');
	dol_include_once('core/lib/date.lib.php');
	
	//récupération de la couleur de l'état quand le vehicule est à l'atelier
	$sql = 'SELECT v.rowid as id, v.immatriculation as immatriculation, t.label as type, s.color as color';
	$sql .= ', o.planned_date as planned_date, o.time_planned_t as time_planned, o.entity as entity ';
	$sql.= ' FROM `'.MAIN_DB_PREFIX.'dolifleet_vehicule` as v';
	$sql.= ' INNER JOIN `'.MAIN_DB_PREFIX.'c_dolifleet_vehicule_type` as t ON t.rowid=v.fk_vehicule_type';
	$sql .= ' INNER JOIN `'.MAIN_DB_PREFIX.'operationorder_extrafields` as es ON v.rowid=es.fk_dolifleet_vehicule';
	$sql .= ' INNER JOIN `'.MAIN_DB_PREFIX.'operationorder` as o ON es.fk_object=o.rowid';
	$sql.= ' INNER JOIN `'.MAIN_DB_PREFIX.'operationorder_status` as s ON s.rowid=o.status';
	$sql.= ' WHERE v.fk_soc = '. intval($socId);
	$sql.= " AND o.planned_date BETWEEN '".$dateDebut."' AND '". $dateFin."'";
	$sql.= ' AND o.planned_date IS NOT NULL';
	$sql.= ' AND v.entity IN ('.getEntity("disponibility").')';//Compatibility with Multicompany
	$sql.= ' ORDER BY v.immatriculation DESC';
	
	$oldconf=$conf->entity;
	
	$planning=array();
	$resql = $db->query($sql);
	while ($object = $db->fetch_object($resql)){		
		$conf->entity=$object->entity;			
		$startTime=dol_stringtotime($object->planned_date); //format timestamp
		$endTime= calculateEndTimeEventByBusinessHours($startTime, $object->time_planned); // formet timestamp
		
		$plannedTime = $startTime;
		$i=0;
		while ($plannedTime<$endTime){			
			$jour=date("Ymd", $plannedTime);
			$heure=date("Hi", $plannedTime);
			if ($heure>0 && $heure<1201){
				$planning[$object->immatriculation][$jour]["matin"]['color']=$object->color;
			}
			else{
				$planning[$object->immatriculation][$jour]["après-midi"]['color']=$object->color;
			}			
			$i+=3600;
			$plannedTime+=$i;
		}	
	}	
	$conf->entity=$oldconf;
	 
	//récupération des dates prévisionnelles de maintenance à programmer
	$sql = 'SELECT a.id as id, v.immatriculation as immatriculation, p.label as product, a.percent as percent';
	$sql .= ', a.datep as datep, a.code as code ';
	$sql.= ' FROM `'.MAIN_DB_PREFIX.'actioncomm` as a';
	$sql.= ' INNER JOIN `'.MAIN_DB_PREFIX.'dolifleet_vehicule` as v ON v.rowid=a.fk_element';
	$sql .= ' INNER JOIN `'.MAIN_DB_PREFIX.'actioncomm_extrafields` as ef ON a.id=ef.fk_object';
	$sql .= ' INNER JOIN `'.MAIN_DB_PREFIX.'product` as p ON ef.fk_product=p.rowid';
	$sql.= ' WHERE a.fk_soc = '. intval($socId);
	$sql.= " AND a.datep BETWEEN '".$dateDebut."' AND '". $dateFin."'";
	$sql.= ' AND a.percent="0" AND a.code="AC_OR"';
	$sql.= ' ORDER BY v.immatriculation DESC';

	$resql = $db->query($sql);
	while ($object = $db->fetch_object($resql)){
		$datePlann = dol_stringtotime($object->datep);
		$jour=date("Ymd", $datePlann);
		$planning[$object->immatriculation][$jour]["matin"]['picto'].=$object->product." ". chr(13);		
	}
	return $planning;
}


function getService($label='',$icon='',$link='',$desc='', $disabled = false)
{
    $iconClass = 'text-primary';

    if($disabled){
		$link='';
		$iconClass = 'text-muted';
    }

    $res = '<div class="col-lg-3 col-sm-6 col-6 text-center">';
    $res.= '<div class="service-box mt-5 mx-auto '.($disabled?'service-box-disabled':'').'">';
    $res.= !empty($link)?'<a href="'.$link.'" >':'';
    $res.= '<i class="fa fa-4x '.$icon.' '.$iconClass.' mb-3 sr-icons"></i>';
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
 * N'est finalement pas utilisÃ©, utiliser datatable en html5 plutot
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
    $sql.= ' AND entity IN ('.getEntity("invoice").')';//Compatibility with Multicompany
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
 * N'est finalement pas utilisÃ©, utiliser datatable en html5 plutot
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
    $sql.= ' AND entity IN ('.getEntity("invoice").')';//Compatibility with Multicompany
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
                'forcedownload' => '<a class="btn btn-xs btn-primary btn-strong" href="'.$dowloadUrl.'&amp;forcedownload=1" target="_blank" ><i class="fa fa-download"></i> '.$langs->trans('Download').'</a>',
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
 * N'est finalement pas utilisÃ©, utiliser datatable en html5 plutot
 */
function print_orderList($socId = 0)
{
    global $langs,$db;
    $context = Context::getInstance();

    $sql = 'SELECT COUNT(*) ';
    $sql.= ' FROM `'.MAIN_DB_PREFIX.'commande` c';
    $sql.= ' WHERE fk_soc = '. intval($socId);
    $sql.= ' AND fk_statut > 0';
    $sql.= ' AND entity IN ('.getEntity("order").')';//Compatibility with Multicompany
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
 * N'est finalement pas utilisÃ©, utiliser datatable en html5 plutot
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
    $sql.= ' AND entity IN ('.getEntity("order").')';//Compatibility with Multicompany
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
                'link' => '<a class="btn btn-xs btn-primary btn-strong" href="'.$dowloadUrl.'&amp;forcedownload=1" target="_blank" ><i class="fa fa-download"></i> '.$langs->trans('Download').'</a>',
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
 * N'est finalement pas utilisÃ©, utiliser datatable en html5 plutot
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
    $sql.= ' AND entity IN ('.getEntity('propal').')';//Compatibility with Multicompany
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
 * N'est finalement pas utilisÃ©, utiliser datatable en html5 plutot
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
    $sql.= ' AND entity IN ('.getEntity('propal').')'; //Compatibility with Multicompany
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
                'link' => '<a class="btn btn-xs btn-primary btn-strong '.$disabledclass.'" href="'.$dowloadUrl.'&amp;forcedownload=1" target="_blank" ><i class="fa fa-download"></i> '.$langs->trans('Download').'</a>',
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
		$last_main_doc = $object->element.'/'.$object->ref.'/'.$object->ref.'.pdf';

		if($object->element == 'propal'){
			$last_main_doc = 'propale/'.$object->ref.'/'.$object->ref.'.pdf';
        }
		elseif($object->element == 'shipping'){
            $last_main_doc =  'expedition/sending/'.$object->ref.'/'.$object->ref.'.pdf';
        }

		if(is_readable(DOL_DATA_ROOT.'/'.$last_main_doc) && is_file ( DOL_DATA_ROOT.'/'.$last_main_doc )) $object->last_main_doc = $last_main_doc;
	}

}
