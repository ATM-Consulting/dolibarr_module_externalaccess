<?php


class ProjectsController extends Controller
{

	public function __construct() {
		global $conf, $user;
		parent::__construct();

		$this->accessRight = !empty($conf->projet->enabled) && $conf->global->EACCESS_ACTIVATE_PROJECTS && !empty($user->rights->externalaccess->view_projects);
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

		$context->title = $langs->trans('ViewProjects');
		$context->desc = $langs->trans('ViewProjectsDesc');
		$context->menu_active[] = 'projects';

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
			print '<section id="section-project"><div class="container">';
			$this->print_projetsTable($user->socid);
			print '</div></section>';
		}

		$this->loadTemplate('footer');
	}



	public function print_projetsTable($socId = 1)
	{
		global $langs,$db,$hookmanager;
		$context = Context::getInstance();

		//dol_include_once('compta/facture/class/facture.class.php');
		dol_include_once('projet/class/project.class.php');
		$langs->load('projet');

		$parameters = array("socId" => $socId);

		$sql = 'SELECT rowid ';

		// Add fields from hooks
		$reshook = $hookmanager->executeHooks('printFieldListSelect', $parameters); // Note that $action and $object may have been modified by hook
		$sql .= $hookmanager->resPrint;

		$sql.= ' FROM `'.MAIN_DB_PREFIX.'projet` projet';

		// Add From from hooks
		$reshook = $hookmanager->executeHooks('printFieldListFrom', $parameters); // Note that $action and $object may have been modified by hook
		$sql .= $hookmanager->resPrint;

		$sql.= ' WHERE fk_soc = '. intval($socId);
		$sql.= ' AND fk_statut > 0';

		// Add where from hooks
		$reshook = $hookmanager->executeHooks('printFieldListWhere', $parameters); // Note that $action and $object may have been modified by hook
		$sql .= $hookmanager->resPrint;

		$sql.= ' ORDER BY projet.datec DESC';

		$tableItems = $context->dbTool->executeS($sql);

		if(!empty($tableItems))
		{

			//TODO : ajouter tableau $TFieldsCols et hook listColumnField comme dans print_expeditionlistTable

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
}
