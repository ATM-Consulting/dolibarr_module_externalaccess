<?php


class PropalsController extends Controller
{

	/**
	 * check current access to controller
	 *
	 * @param void
	 * @return  bool
	 */
	public function checkAccess() {
		global $conf, $user;
		$this->accessRight = !empty($conf->propal->enabled) && $conf->global->EACCESS_ACTIVATE_PROPALS && !empty($user->rights->externalaccess->view_propals);
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

		$context->title = $langs->trans('ViewOrders');
		$context->desc = $langs->trans('ViewOrdersDesc');
		$context->menu_active[] = 'orders';

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
			print '<section id="section-propal"><div class="container">';
			self::print_propalTable($user->socid);
			print '</div></section>';
		}
		$this->loadTemplate('footer');
	}


	static public function print_propalTable($socId = 0, $searching = true, $paging=false, $propallink = true)
	{
		global $langs, $db, $conf, $hookmanager;
		$context = Context::getInstance();

		dol_include_once('comm/propal/class/propal.class.php');

		$parameters = array("socId" => $socId);

		$sql = 'SELECT rowid ';

		// Add fields from hooks
		$reshook = $hookmanager->executeHooks('printFieldListSelect', $parameters); // Note that $action and $object may have been modified by hook
		$sql .= $hookmanager->resPrint;

		$sql.= ' FROM `'.MAIN_DB_PREFIX.'propal` p';

		// Add From from hooks
		$reshook = $hookmanager->executeHooks('printFieldListFrom', $parameters); // Note that $action and $object may have been modified by hook
		$sql .= $hookmanager->resPrint;

		$sql.= ' WHERE fk_soc = '. intval($socId);
		$sql.= ' AND fk_statut > 0';
		$sql.= ' AND entity IN ('.getEntity("propal").')';//Compatibility with Multicompany

		// Add where from hooks
		$reshook = $hookmanager->executeHooks('printFieldListWhere', $parameters); // Note that $action and $object may have been modified by hook
		$sql .= $hookmanager->resPrint;

		$sql.= ' ORDER BY p.datep DESC';

		$tableItems = $context->dbTool->executeS($sql);

		if(!empty($tableItems))
		{

			//TODO : ajouter tableau $TFieldsCols et hook listColumnField comme dans print_expeditionlistTable

			$TOther_fields = unserialize($conf->global->EACCESS_LIST_ADDED_COLUMNS);
			if(empty($TOther_fields)) $TOther_fields = array();

            $TOther_fields_propal = unserialize($conf->global->EACCESS_LIST_ADDED_COLUMNS_PROPAL);
			if(empty($TOther_fields_propal)) $TOther_fields_propal = array();

            $TOther_fields = array_merge($TOther_fields, $TOther_fields_propal);

			print '<table id="propal-list" class="table table-striped" >';

			print '<thead>';

			print '<tr>';
			print ' <th class="text-center" >'.$langs->trans('Ref').'</th>';

			if(!empty($TOther_fields)) {
                $e = new ExtraFields($db); // Question d'opti, c'est mieux de charger l'objet avant la boucle, sinon le __construct vide le $e->attributes et donc nécessaire de refaire fetch_name_optionals_label à chaque itération
				foreach ($TOther_fields as $field) {
					if(property_exists('Propal', $field)) print ' <th class="text-center" >' . $langs->trans($field) . '</th>';
                    elseif(strpos($field, 'EXTRAFIELD') !== false) {

						if(empty($e->attributes)) $e->fetch_name_optionals_label('propal');
						print ' <th class="text-center" >' . $e->attributes['propal']['label'][strtr($field, array('EXTRAFIELD_'=>''))] . '</th>';
					}
				}
			}

			print ' <th class="text-center" >'.$langs->trans('Date').'</th>';
			print ' <th class="text-center" >'.$langs->trans('EndValidDate').'</th>';
			print ' <th class="text-center" >'.$langs->trans('Status').'</th>';
			print ' <th class="text-center" >'.$langs->trans('Amount_HT').'</th>';
			print ' <th class="text-center" ></th>';

			$parameters = array('context' => $context, 'controller' => $context->controller);
			$reshook = $hookmanager->executeHooks('printFieldListTitle', $parameters, $object, $action);
			if($reshook < 0) $context->setEventMessages($hookmanager->error,$hookmanager->errors,'errors');

			print '</tr>';

			print '</thead>';

			print '<tbody>';
			foreach ($tableItems as $item)
			{
				$object = new Propal($db);
				$object->fetch($item->rowid);
				load_last_main_doc($object);
				$downloadUrl = $context->getRootUrl().'script/interface.php?action=downloadPropal&id='.$object->id;


				if(!empty($object->last_main_doc) && is_readable(DOL_DATA_ROOT.'/'.$object->last_main_doc) && is_file ( DOL_DATA_ROOT.'/'.$object->last_main_doc )){
					$viewLink = '<a href="'.$downloadUrl.'" target="_blank" >'.$object->ref.'</a>';
					$downloadLink = '<a class="btn btn-xs btn-primary btn-strong" href="'.$downloadUrl.'&amp;forcedownload=1" target="_blank" ><i class="fa fa-download"></i> '.$langs->trans('Download').'</a>';
				}
				else{
					$viewLink = $object->ref;
					$downloadLink =  $langs->trans('DocumentFileNotAvailable');
				}

				print '<tr>';
				print ' <td data-search="'.$object->ref.'" data-order="'.$object->ref.'"  >'.(($propallink) ? $viewLink : $object->ref).'</td>';

				$total_more_fields=0;
				if(!empty($TOther_fields)) {
					foreach ($TOther_fields as $field) {
						if(property_exists('Propal', $field)) {
							$total_more_fields+=1;
							print ' <td data-search="' . strip_tags($object->{$field}) . '" data-order="' . strip_tags($object->{$field}) . '" >' . $object->{$field} . '</td>';
						} elseif (strpos($field, 'EXTRAFIELD') !== false) {
							print ' <td data-search="'
								. strip_tags($e->showOutputField(strtr($field, array('EXTRAFIELD_'=>'')), $object->array_options['options_'.strtr($field, array('EXTRAFIELD_'=>''))], '', 'propal')) . '" data-order="'
								. strip_tags($e->showOutputField(strtr($field, array('EXTRAFIELD_'=>'')), $object->array_options['options_'.strtr($field, array('EXTRAFIELD_'=>''))], '', 'propal')) . '" >'
								. strip_tags($e->showOutputField(strtr($field, array('EXTRAFIELD_'=>'')), $object->array_options['options_'.strtr($field, array('EXTRAFIELD_'=>''))], '', 'propal')) . '</td>';
						}
					}
				}

				print ' <td data-search="'.dol_print_date($object->date).'" data-order="'.$object->date.'" >'.dol_print_date($object->date).'</td>';
				print ' <td data-search="'.dol_print_date($object->fin_validite).'" data-order="'.$object->fin_validite.'" >'.dol_print_date($object->fin_validite).'</td>';
				print ' <td class="text-center" >'.$object->getLibStatut(0).'</td>';
				print ' <td data-order="'.$object->multicurrency_total_ht.'" class="text-right" >'.price($object->multicurrency_total_ht)  .' '.$object->multicurrency_code.'</td>';


				print ' <td  class="text-right" >'.$downloadLink.'</td>';

				$parameters = array('context' => $context, 'controller' => $context->controller);
				$reshook = $hookmanager->executeHooks('printFieldListValue', $parameters, $object, $action);
				if($reshook < 0) $context->setEventMessages($hookmanager->error,$hookmanager->errors,'errors');

				print '</tr>';

			}
			print '</tbody>';

			print '</table>';



			$jsonConf = array(
				'rootUrl' => $context->getRootUrl(),
				'searching' => $searching,
				'paging' => $paging,
				'total_more_fields' => $total_more_fields
			)

			?>
			<script type="text/javascript" >
				$(document).ready(function(){

					let jsonConf = <?php print json_encode($jsonConf) ?>;

					$("#propal-list").DataTable({

						searching: jsonConf.searching,
						paging: jsonConf.paging,

						"language": {
							"url": jsonConf.rootUrl + "vendor/data-tables/french.json"
						},
						"order": [[jsonConf.total_more_fields + 1, 'desc']],

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
}
