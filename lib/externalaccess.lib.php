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
                $downloadLink = '<a class="btn btn-xs btn-primary" href="'.$dowloadUrl.'&amp;forcedownload=1" target="_blank" ><i class="fa fa-download"></i> '.$langs->trans('Download').'</a>';
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
                $downloadLink = '<a class="btn btn-xs btn-primary" href="'.$downloadUrl.'&amp;forcedownload=1" target="_blank" ><i class="fa fa-download"></i> '.$langs->trans('Download').'</a>';
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
				$downloadLink = '<a class="btn btn-xs btn-primary" href="'.$dowloadUrl.'&amp;forcedownload=1" target="_blank" ><i class="fa fa-download"></i> '.$langs->trans('Download').'</a>';
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

function print_ticketTable($socId = 0)
{
    global $langs,$db;
    $context = Context::getInstance();

    dol_include_once('ticket/class/ticket.class.php');

    $langs->load('ticket');

    $sql = 'SELECT rowid ';
    $sql.= ' FROM `'.MAIN_DB_PREFIX.'ticket` t';
    $sql.= ' WHERE fk_soc = '. intval($socId);
    $sql.= ' ORDER BY t.datec DESC';
    $tableItems = $context->dbTool->executeS($sql);

    if(!empty($tableItems))
    {
        print '<table id="ticket-list" class="table table-striped" >';
        print '<thead>';
        print '<tr>';
        print ' <th class="text-center" >'.$langs->trans('Ref').'</th>';
        print ' <th class="text-center" >'.$langs->trans('Date').'</th>';
        print ' <th class="text-center" >'.$langs->trans('Subject').'</th>';
        print ' <th class="text-center" >'.$langs->trans('Type').'</th>';
        print ' <th class="text-center" >'.$langs->trans('TicketSeverity').'</th>';
        print ' <th class="text-center" >'.$langs->trans('Status').'</th>';
        print '</tr>';
        print '</thead>';
        print '<tbody>';
        foreach ($tableItems as $item)
        {
            $object = new Ticket($db);
            $object->fetch($item->rowid);

            print '<tr>';
            print ' <td data-search="'.$object->ref.'" data-order="'.$object->ref.'"  ><a href="'.$context->getRootUrl('ticket_card', '&ticketId='.$item->rowid).'">'.$object->ref.'</a></td>';
            print ' <td data-search="'.dol_print_date($object->datec).'" data-order="'.$object->datec.'" >'.dol_print_date($object->datec).'</td>';
            print ' <td data-search="'.$object->subject.'" data-order="'.$object->subject.'" >'.$object->subject.'</td>';
            print ' <td data-search="'.$object->type_label.'" data-order="'.$object->type_label.'" >'.$object->type_label.'</td>';
            print ' <td data-search="'.$object->severity_label.'" data-order="'.$object->severity_label.'" >'.$object->severity_label.'</td>';
            print ' <td class="text-center" >'.$object->getLibStatut(1).'</td>';
            print '</tr>';
        }
        print '</tbody>';
        print '</table>';
        ?>
        <script type="text/javascript" >
            $(document).ready(function(){
                $("#ticket-list").DataTable({
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

function print_ticketCard($ticketId = 0, $socId = 0)
{
	global $langs,$db, $conf;
	$context = Context::getInstance();
	$out = '';

	dol_include_once('ticket/class/ticket.class.php');
	dol_include_once('user/class/user.class.php');

	$langs->load('ticket');

	/** @var Ticket $object */
	$object = new Ticket($db);
	$object->fetch($ticketId);
	$author = '';
	$fuser = new User($db);

	if ($object->fk_user_create > 0) {
		$langs->load("users");
		$fuser->fetch($object->fk_user_create);
		$author.= $fuser->getFullName($langs);
	} else {
		$author.= dol_escape_htmltag($object->origin_email);
	}
	$out.= getEaNavbar($context->getRootUrl('tickets', '&save_lastsearch_values=1'));

	$out.= '
		<div class="container px-0">
			<h5>'.$langs->trans('Ticket').' '.$object->ref.'</h5>
			<div class="panel panel-default" id="ticket-summary">
				<div class="panel-body">
					<div class="row clearfix form-group" id="subject">
						<div class="col-md-4">'.$langs->transnoentities('Subject').'</div>
						<div class="col-md-8">'.$object->subject.'</div>
					</div>
					<div class="row clearfix form-group" id="trackId">
						<div class="col-md-4">'.$langs->transnoentities('TicketTrackId').'</div>
						<div class="col-md-8">'.$object->track_id.'</div>
					</div>
					<div class="row clearfix form-group" id="status">
						<div class="col-md-4">'.$langs->transnoentities('Status').'</div>
						<div class="col-md-8">'.$object->getLibStatut().'</div>
					</div>
					<div class="row clearfix form-group" id="Type">
						<div class="col-md-4">'.$langs->transnoentities('Type').'</div>
						<div class="col-md-8">'.$object->type_label.'</div>
					</div>
					<div class="row clearfix form-group" id="Severity">
						<div class="col-md-4">'.$langs->transnoentities('Severity').'</div>
						<div class="col-md-8">'.$object->severity_label.'</div>
					</div>
					<div class="row clearfix form-group" id="DateCreation">
						<div class="col-md-4">'.$langs->transnoentities('DateCreation').'</div>
						<div class="col-md-8">'.dol_print_date($object->datec, 'dayhour').'</div>
					</div>
					<div class="row clearfix form-group" id="Author">
						<div class="col-md-4">'.$langs->transnoentities('Author').'</div>
						<div class="col-md-8">'.$author.'</div>
					</div>
					<div class="row clearfix form-group" id="InitialMessage">
						<div class="col-md-4">'.$langs->transnoentities('InitialMessage').'</div>
						<div class="col-md-8">'.$object->message.'</div>
					</div>
				</div>
			</div>
		</div>';

	// get list of messages for the ticket
	$object->loadCacheMsgsTicket();
	//var_dump($object->cache_msgs_ticket);

	if (!empty($object->cache_msgs_ticket))
	{
		require_once DOL_DOCUMENT_ROOT.'/comm/action/class/actioncomm.class.php';

		$actionstatic = new ActionComm($db);

		ob_start();
		define('ISLOADEDBYSTEELSHEET', true);
		require_once DOL_DOCUMENT_ROOT.'/theme/eldy/timeline.inc.php';
		$out.='<style>';
		$out.= ob_get_clean();
		$out.= '
			img.userphoto {			/* size for user photo in lists */
				border-radius: 0.72em;
				width: 1.4em;
				height: 1.4em;
				background-size: contain;
				vertical-align: middle;
			}';
		$out.='</style>';

		$out.='
			<div class="container px-0">
				<h5>'.$langs->trans('TicketMessagesList').'</h5>
			';

		$out .= '
			<ul class="timeline">';

		$datelabel = "";
		foreach ($object->cache_msgs_ticket as $key => $value)
		{
			/* @var ActionComm $actionstatic */
			$actionstatic->fetch($value['id']);

			if ($datelabel != dol_print_date($value['datec']) && empty($value['private']))
			{
				$datelabel = dol_print_date($value['datec']);
				$out .= '<!-- timeline time label -->';
				$out .= '<li class="time-label">';
				$out .= '<span class="timeline-badge-date">';
				$out .= $datelabel;
				$out .= '</span>';
				$out .= '</li>';
				$out .= '<!-- /.timeline-label -->';
			}

			if (empty($value['private']))
			{
//				$out.= '<li>'.dol_print_date($value['datec']).' lol </li>';
				$out .= '<!-- timeline item -->'."\n";
				$out .= '<li class="timeline-code-'.strtolower($actionstatic->code).'">';
				$out .= '<!-- timeline icon -->'."\n";

				$iconClass = 'fa fa-comments';
				$img_picto = '';
				$colorClass = '';
				$pictoTitle = '';

				if ($actionstatic->percentage == -1) {
					$colorClass = 'timeline-icon-not-applicble';
					$pictoTitle = $langs->trans('StatusNotApplicable');
				}
				elseif ($actionstatic->percentage == 0) {
					$colorClass = 'timeline-icon-todo';
					$pictoTitle = $langs->trans('StatusActionToDo').' (0%)';
				}
				elseif ($actionstatic->percentage > 0 && $actionstatic->percentage < 100) {
					$colorClass = 'timeline-icon-in-progress';
					$pictoTitle = $langs->trans('StatusActionInProcess').' ('.$actionstatic->percentage.'%)';
				}
				elseif ($actionstatic->percentage >= 100) {
					$colorClass = 'timeline-icon-done';
					$pictoTitle = $langs->trans('StatusActionDone').' (100%)';
				}

				if ($actionstatic->code == 'AC_TICKET_CREATE') {
					$iconClass = 'fa fa-ticket';
				}
				elseif ($actionstatic->code == 'AC_TICKET_MODIFY') {
					$iconClass = 'fa fa-pencil';
				}
				elseif ($actionstatic->code == 'TICKET_MSG') {
					$iconClass = 'fa fa-comments';
				}
				elseif ($actionstatic->code == 'TICKET_MSG_PRIVATE') {
					$iconClass = 'fa fa-mask';
				}
				elseif (!empty($conf->global->AGENDA_USE_EVENT_TYPE))
				{
					if ($actionstatic->type_picto) $img_picto = img_picto('', $actionstatic->type_picto);
					else {
						if ($actionstatic->type_code == 'AC_RDV')       $iconClass = 'fa fa-handshake';
						elseif ($actionstatic->type_code == 'AC_TEL')   $iconClass = 'fa fa-phone';
						elseif ($actionstatic->type_code == 'AC_FAX')   $iconClass = 'fa fa-fax';
						elseif ($actionstatic->type_code == 'AC_EMAIL') $iconClass = 'fa fa-envelope';
						elseif ($actionstatic->type_code == 'AC_INT')   $iconClass = 'fa fa-shipping-fast';
						elseif ($actionstatic->type_code == 'AC_OTH_AUTO')   $iconClass = 'fa fa-robot';
						elseif (!preg_match('/_AUTO/', $actionstatic->type_code)) $iconClass = 'fa fa-robot';
					}
				}

				$out.= '<i class="'.$iconClass.' '.$colorClass.'" title="'.$pictoTitle.'">'.$img_picto.'</i>'."\n";

				$out.= '<div class="timeline-item">';
				// Date
				$out.= '<span class="time"><i class="fa fa-clock-o"></i> ';
				$out.= dol_print_date($value['datec'], 'dayhour');
				$out.= '</span>';

				// Ref
				$out.='<h3 class="timeline-header">';

				// Author of event
				$out.='<span class="messaging-author">';
				if ($actionstatic->userownerid > 0)
				{
					if(!isset($userGetNomUrlCache[$actionstatic->userownerid])){ // is in cache ?
						$fuser->fetch($actionstatic->userownerid);
						$userGetNomUrlCache[$actionstatic->userownerid] = $fuser->getNomUrl(-1, '', 0, 0, 16, 0, 'firstelselast', '');
					}
					$out.= $userGetNomUrlCache[$actionstatic->userownerid];
				}
				$out.='</span>';

				// Title
				$out .= ' <span class="messaging-title">';

				if($actionstatic->code == 'TICKET_MSG') {
					$out .= $langs->trans('TicketNewMessage');
				}
				elseif($actionstatic->code == 'TICKET_MSG_PRIVATE') {
					$out .= $langs->trans('TicketNewMessage').' <em>('.$langs->trans('Private').')</em>';
				}

				$out .= '</span>';

				$out .= '</h3>';

				$out.= '<div class="timeline-body">'.$value['message'].'</div>';

				$out.= '</div>';

				$out.='</li>';
				$out.='<!-- END timeline item -->';
			}
		}

		$out.="
			</ul>\n";

		$out.="</div>";
	}

	print $out;
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
