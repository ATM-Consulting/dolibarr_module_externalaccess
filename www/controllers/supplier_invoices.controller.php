<?php


class SupplierInvoicesController extends Controller
{
	/**
	 * check current access to controller
	 *
	 * @param void
	 * @return  bool
	 */
	public function checkAccess() {
		global $conf, $user;
		$this->accessRight = !empty($conf->supplier_invoice->enabled) && $conf->global->EACCESS_ACTIVATE_SUPPLIER_INVOICES && !empty($user->rights->externalaccess->view_supplier_invoices);
		return parent::checkAccess();
	}

	/**
	 * action method is called before html output
	 * can be used to manage security and change context
	 *
	 * @param void
	 * @return void
	 */
	public function action(){
		global $langs;
		$context = Context::getInstance();
		if(!$context->controllerInstance->checkAccess()) { return; }

		$context->title = $langs->trans('ViewSupplierInvoices');
		$context->desc = $langs->trans('ViewSupplierInvoicesDesc');
		$context->menu_active[] = 'supplier_invoices';

		$hookRes = $this->hookDoAction();
		if(empty($hookRes)){

		}
	}


	/**
	 *
	 * @param void
	 * @return void
	 */
	public function display(){
		global $conf, $user;
		$context = Context::getInstance();
		if(!$context->controllerInstance->checkAccess()) {  return $this->display404(); }

		$this->loadTemplate('header');

		$hookRes = $this->hookPrintPageView();
		if(empty($hookRes)){
			print '<section id="section-supplier-invoice"><div class="container">';
			$this->print_supplierinvoiceTable($user->socid);
			print '</div></section>';
		}

		$this->loadTemplate('footer');
	}



	public function print_supplierinvoiceTable($socId = 0)
	{
		global $langs, $db, $conf, $hookmanager;
		$context = Context::getInstance();

		dol_include_once('fourn/class/fournisseur.facture.class.php');

		$langs->load('factures');

		$parameters = array("socId" => $socId);

		$sql = 'SELECT rowid ';

		// Add fields from hooks
		$reshook = $hookmanager->executeHooks('printFieldListSelect', $parameters); // Note that $action and $object may have been modified by hook
		$sql .= $hookmanager->resPrint;

		$sql.= ' FROM `'.MAIN_DB_PREFIX.'facture_fourn` f';

		// Add From from hooks
		$reshook = $hookmanager->executeHooks('printFieldListFrom', $parameters); // Note that $action and $object may have been modified by hook
		$sql .= $hookmanager->resPrint;

		$sql.= ' WHERE fk_soc = '. intval($socId);
//    $sql.= ' AND fk_statut > 0';
		$sql.= ' AND entity IN ('.getEntity("invoice").')'; //Compatibility with Multicompany

		// Add where from hooks
		$reshook = $hookmanager->executeHooks('printFieldListWhere', $parameters); // Note that $action and $object may have been modified by hook
		$sql .= $hookmanager->resPrint;

		$sql.= ' ORDER BY f.datef DESC';

		$tableItems = $context->dbTool->executeS($sql);

		if(!empty($tableItems))
		{

			//TODO : ajouter tableau $TFieldsCols et hook listColumnField comme dans print_expeditionlistTable

			$TOther_fields = unserialize($conf->global->EACCESS_LIST_ADDED_COLUMNS);
			if(empty($TOther_fields)) $TOther_fields = array();

			print '<table id="invoice-list" class="table table-striped" >';

			print '<thead>';

			print '<tr>';
			print ' <th class="text-center" >'.$langs->trans('Ref').'</th>';
			print ' <th class="text-center" >'.$langs->trans('FournRef').'</th>';

			if(!empty($TOther_fields)) {
				foreach ($TOther_fields as $field) {
					if(property_exists('Facture', $field)) print ' <th class="text-center" >' . $langs->trans($field) . '</th>';
				}
			}

			print ' <th class="text-center" >'.$langs->trans('Date').'</th>';
			print ' <th class="text-center" >'.$langs->trans('DatePayLimit').'</th>';
			print ' <th class="text-center" >'.$langs->trans('Status').'</th>';
			if(!empty($conf->global->EACCESS_ACTIVATE_INVOICES_HT_COL)){
				print ' <th class="text-center" >'.$langs->trans('Amount_HT').'</th>';
			}
			print ' <th class="text-center" >'.$langs->trans('Amount_TTC').'</th>';
			print ' <th class="text-center" >'.$langs->trans('RemainderToPay').'</th>';
//        print ' <th class="text-center" ></th>';
			print '</tr>';

			print '</thead>';

			print '<tbody>';
			foreach ($tableItems as $item)
			{
				$object = new FactureFournisseur($db);
				$object->fetch($item->rowid);
				load_last_main_doc($object);
				$dowloadUrl = $context->getRootUrl().'script/interface.php?action=downloadInvoice&id='.$object->id;
				//var_dump($object); exit;
				$totalpaye = $object->getSommePaiement();
				$totalcreditnotes = $object->getSumCreditNotesUsed();
				$totaldeposits = $object->getSumDepositsUsed();
				$resteapayer = price2num($object->total_ttc - $totalpaye - $totalcreditnotes - $totaldeposits, 'MT');

				if(!empty($object->last_main_doc) && is_readable(DOL_DATA_ROOT.'/'.$object->last_main_doc) && is_file ( DOL_DATA_ROOT.'/'.$object->last_main_doc )){
					$viewLink = '<a href="'.$dowloadUrl.'" target="_blank" >'.$object->ref.'</a>';
					$downloadLink = '<a class="btn btn-xs btn-primary btn-strong" href="'.$dowloadUrl.'&amp;forcedownload=1" target="_blank" ><i class="fa fa-download"></i> '.$langs->trans('Download').'</a>';
				}
				else{
					$viewLink = $object->ref;
					$downloadLink =  $langs->trans('DocumentFileNotAvailable');
				}


				print '<tr >';
				print ' <td data-search="'.$object->ref.'" data-order="'.$object->ref.'" >'.$viewLink.'</td>';
				print ' <td data-search="'.dol_htmlentities($object->ref_supplier, ENT_QUOTES).'" data-order="'.dol_htmlentities($object->ref_supplier, ENT_QUOTES).'" >'.dol_htmlentities($object->ref_supplier, ENT_QUOTES).'</td>';

				$total_more_fields=0;
				if(!empty($TOther_fields)) {
					foreach ($TOther_fields as $field) {
						if(property_exists('Facture', $field)) {
							$total_more_fields+=1;
							print ' <td data-search="' . strip_tags($object->{$field}) . '" data-order="' . strip_tags($object->{$field}) . '" >' . $object->{$field} . '</td>';
						}
					}
				}

				print ' <td data-order="'.$object->date.'" data-search="'.dol_print_date($object->date).'"  >'.dol_print_date($object->date).'</td>';
				print ' <td data-order="'.$object->date_lim_reglement.'"  >'.dol_print_date($object->date_lim_reglement).'</td>';
				print ' <td  >'.$object->getLibStatut(0).'</td>';

				if(!empty($conf->global->EACCESS_ACTIVATE_INVOICES_HT_COL)){
					print ' <td data-order="'.$object->multicurrency_total_ht.'" class="text-right" >'.price($object->multicurrency_total_ht)  .' '.$object->multicurrency_code.'</td>';
				}
				print ' <td data-order="'.$object->multicurrency_total_ttc.'" class="text-right" >'.price($object->multicurrency_total_ttc)  .' '.$object->multicurrency_code.'</td>';
				print ' <td data-order="'.$resteapayer.'" class="text-right" >'.price($resteapayer)  .' '.$object->multicurrency_code.'</td>';
//            print ' <td  class="text-right" >'.$downloadLink.'</td>';
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
						"order": [[<?php echo ($total_more_fields + 1); ?>, 'desc']],

						responsive: true,
						columnDefs: []
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

}
