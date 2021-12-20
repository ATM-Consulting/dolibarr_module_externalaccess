<?php

/**
 * Class ProjectsController
 */
class ProjectsController extends Controller
{


	/**
	 * check current access to controller
	 *
	 * @param void
	 * @return  bool
	 */
	public function checkAccess() {
		global $conf, $user;
		$this->accessRight = !empty($conf->projet->enabled) && $conf->global->EACCESS_ACTIVATE_PROJECTS && !empty($user->rights->externalaccess->view_projects);
		return parent::checkAccess();
	}

	/**
	 * action method is called before html output
	 * can be used to manage security and change context
	 *
	 * @return void
	 */
	public function action()
	{
		global $langs;
		$context = Context::getInstance();
		if (!$context->controllerInstance->checkAccess()) { return; }

		$context->title = $langs->trans('ViewProjects');
		$context->desc = $langs->trans('ViewProjectsDesc');
		$context->menu_active[] = 'projects';

		$hookRes = $this->hookDoAction();
		if (empty($hookRes)){
		}
	}


	/**
	 *
	 * @return void
	 */
	public function display()
	{
		global $conf, $user;
		$context = Context::getInstance();
		if (!$context->controllerInstance->checkAccess()) {  return $this->display404(); }

		$this->loadTemplate('header');

		$hookRes = $this->hookPrintPageView();
		if (empty($hookRes)){
			print '<section id="section-project"><div class="container">';
			$this->printProjectTable($user->socid, $user->contact_id);
			print '</div></section>';
		}

		$this->loadTemplate('footer');
	}

	/**
	 * @param int $socId socid
	 * @param int $contactId contactId
	 * @return void
	 */
	public function printProjectTable($socId = 0, $contactId = 0)
	{
		global $langs, $db, $conf, $hookmanager;
		$context = Context::getInstance();

		include_once DOL_DOCUMENT_ROOT . '/projet/class/project.class.php';
		include_once DOL_DOCUMENT_ROOT . '/projet/class/task.class.php';
		//include_once DOL_DOCUMENT_ROOT . '/core/lib/pdf.lib.php';
		include_once DOL_DOCUMENT_ROOT . '/core/lib/date.lib.php';
		///home/florian/Develop/www/module/dolibarr/htdocs

		$langs->load('projects', 'main');


		$sql = 'SELECT p.rowid as p_rowid ';

		// Add fields from hooks
		$parameters = array();
		$reshook = $hookmanager->executeHooks('printFieldListSelect', $parameters); // Note that $action and $object may have been modified by hook
		$sql .= $hookmanager->resPrint;

		$sql.= ' FROM '.MAIN_DB_PREFIX.'projet as p';
		$sql.= ' INNER JOIN '.MAIN_DB_PREFIX.'element_contact as ct ON ct.element_id=p.rowid';
		$sql.= ' INNER JOIN '.MAIN_DB_PREFIX.'c_type_contact as cct ON cct.rowid=ct.fk_c_type_contact';
		$sql.= '  AND  cct.element=\'project\' AND cct.source=\'external\'';
		$sql.= '  AND  ct.fk_socpeople='.(int) $contactId;

		// Add From from hooks
		$parameters = array();
		$reshook = $hookmanager->executeHooks('printFieldListFrom', $parameters); // Note that $action and $object may have been modified by hook
		$sql .= $hookmanager->resPrint;

		$sql.= ' WHERE p.fk_soc = '. intval($socId);
		$sql.= ' AND p.fk_statut > 0';
		$sql.= ' AND p.entity IN ('.getEntity("project").')';//Compatibility with Multicompany

		// Add where from hooks
		$parameters = array();
		$reshook = $hookmanager->executeHooks('printFieldListWhere', $parameters); // Note that $action and $object may have been modified by hook
		$sql .= $hookmanager->resPrint;

		$sql.= ' ORDER BY p.ref DESC';

		$tableItems = $context->dbTool->executeS($sql);

		if (!empty($tableItems))
		{
			//TODO : ajouter la variable $dataTableConf en paramètre du hook => résoudre le souci de "order"
			//      $dataTableConf = array(
			//          'language' => array(
			//              'url' => $context->getRootUrl() . 'vendor/data-tables/french.json',
			//          ),
			//          'order' => array(),
			//          'responsive' => true,
			//          'columnDefs' => array(
			//              array(
			//                  'orderable' => false,
			//                  'aTargets' => array(-1),
			//              ),
			//              array(
			//                  'bSearchable' => false,
			//                  'aTargets' => array(-1, -2),
			//              ),
			//          ),
			//      );

			$TFieldsCols = array(
				'p.ref' => array('status' => true),
				'p.title' => array('status' => true),
				'p.dateo' => array('status' => true),
				'p.datee' => array('status' => true),
				'p.fk_statut' => array('status' => true),
				//'downloadlink' => array('status' => true),
			);

			$parameters = array(
				'socId' => $socId,
				'tableItems' =>& $tableItems,
				'TFieldsCols' =>& $TFieldsCols
			);

			$reshook = $hookmanager->executeHooks('listColumnField', $parameters, $context); // Note that $object may have been modified by hook
			if ($reshook < 0) {
				$context->setEventMessages($hookmanager->errors, 'errors');
			} elseif (empty($reshook)) {
				$TFieldsCols = array_replace($TFieldsCols, $hookmanager->resArray); // array_replace is used to preserve keys
			} else {
				$TFieldsCols = $hookmanager->resArray;
			}


			$TOther_fields_all = unserialize($conf->global->EACCESS_LIST_ADDED_COLUMNS);
			if (empty($TOther_fields_all))
				$TOther_fields_all = array();

			$TOther_fields_project = unserialize($conf->global->EACCESS_LIST_ADDED_COLUMNS_PROJECT);
			if (empty($TOther_fields_project))
				$TOther_fields_project = array();

			$TOther_fields = array_merge($TOther_fields_all, $TOther_fields_project);

			print '<table id="projettask-list" class="table table-striped" >';

			print '<thead>';

			print '<tr>';

			if (!empty($TFieldsCols['p.ref']['status'])){
				print ' <th class="text-center" >'.$langs->trans('Ref').'</th>';
			}
			if (!empty($TFieldsCols['p.title']['status'])) {
				print ' <th class="p_title_title text-center" >' . $langs->trans('Label') . '</th>';
			}

			if (!empty($TOther_fields)) {
				foreach ($TOther_fields as $field) {
					//if ($field === 'ref_client' && !isset($object->field)) $field = 'ref_customer';
					if (property_exists('Project', $field))
					{
						$project = new Project($db);
						$label = $project->fields[$field]['label'];
						print ' <th class="'.$field.'_title text-center" >'.$langs->trans($label).'</th>';
					}
				}
			}
			if (!empty($TFieldsCols['p.dateo']['status'])) {
				print ' <th class="p_dated_title text-center" >' . $langs->trans('DateStart') . '</th>';
			}
			if (!empty($TFieldsCols['p.datee']['status'])) {
				print ' <th class="p_datee_title text-center" >' . $langs->trans('DateEnd') . '</th>';
			}
			if (!empty($TFieldsCols['p.fk_statut']['status'])) {
				print ' <th class="p_statut_title text-center" >' . $langs->trans('Status') . '</th>';
			}

			print '</tr>';

			print '</thead>';

			print '<tbody>';
			foreach ($tableItems as $item)
			{
				$project = new Project($db);
				$project->fetch($item->p_rowid);
				$project->fetchObjectLinked();

				print '<tr>';

				if (!empty($TFieldsCols['p.ref']['status'])) {
					print ' <td class="p_ref_value text-center" data-search="' . $project->ref . '" data-order="' . $project->ref . '"  >' . $project->ref . '</td>';
				}
				if (!empty($TFieldsCols['p.title']['status'])) {
					print ' <td class="p_title_value text-center" data-search="' . dol_string_nospecial($project->title) . '" data-order="' . dol_string_nospecial($project->title) . '"  >' . $project->title . '</td>';
				}

				$total_more_fields = 0;
				if (!empty($TOther_fields)) {
					foreach ($TOther_fields as $field) {
						if (property_exists('Project', $field)) {
							$total_more_fields+=1;
							if ($field =='budget_amount') {
								print ' <td class="'.$field.'_value text-center" data-search="' . strip_tags($field) . '" data-order="' . strip_tags($field) . '" >' . price($project->budget_amount, 0, $langs, 1, 0, 0, $conf->currency) . '</td>';
							} else {
								print ' <td class="'.$field.'_value text-center" data-search="' . strip_tags($project->{$field}) . '" data-order="' . strip_tags($project->{$field}) . '" >' . $project->{$field} . '</td>';
							}
						}
					}
				}

				if (!empty($TFieldsCols['p.dateo']['status'])) {
					print ' <td class="p_dateo_value text-center" data-search="' . dol_print_date($project->date_start) . '" data-order="' . $project->date_start . '"  >' . dol_print_date($project->date_start) . '</td>';
				}
				if (!empty($TFieldsCols['p.datee']['status'])) {
					print ' <td class="p_datee_value text-center" data-search="' . dol_print_date($project->date_end) . '" data-order="' . $project->date_end . '"  >' . dol_print_date($project->date_end) . '</td>';
				}
				if (!empty($TFieldsCols['p.fk_statut']['status'])) {
					print ' <td class="p_statut_value text-center" data-search="' . dol_string_nospecial($project->getLibStatut($project->status, 1)) . '" data-order="' . dol_string_nospecial($project->getLibStatut($project->status, 1)) . '"  >' . $project->getLibStatut(2) . '</td>';
				}
				print '</tr>';
			}
			print '</tbody>';

			print '</table>';
			?>
			<script type="text/javascript" >
				$(document).ready(function(){
					//$("#expedition-list").DataTable(<?php //echo json_encode($dataTableConf) ?>//);
					$("#projet-list").DataTable({
						"language": {
							"url": "<?php print $context->getRootUrl(); ?>vendor/data-tables/french.json"
						},
						//"order": [[<?php echo ($total_more_fields + 2); ?>, 'desc']],

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
}
