<?php


class TicketsController extends Controller
{

	public function __construct() {
		global $conf, $user;
		parent::__construct();

		$this->accessRight = !empty($conf->ticket->enabled) && $conf->global->EACCESS_ACTIVATE_TICKETS && !empty($user->rights->externalaccess->view_tickets);
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

		$context->title = $langs->trans('ViewTickets');
		$context->desc = $langs->trans('ViewTicketsDesc');
		$context->menu_active[] = 'tickets';

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
			print '<section id="section-ticket"><div class="container">';
			self::print_ticketTable($user->socid);
			print '</div></section>';
		}
		$this->loadTemplate('footer');
	}


	static public function print_ticketTable($socId = 0)
	{
		global $langs,$db, $user;
		$context = Context::getInstance();

		dol_include_once('ticket/class/ticket.class.php');
		$ticketStatic = new Ticket($context->dbTool->db);

		$langs->load('ticket');

		$sql = 'SELECT rowid ';
		$sql.= ' FROM `'.MAIN_DB_PREFIX.'ticket` t';
		$sql.= ' WHERE fk_soc = '. intval($socId);
		$sql.= ' ORDER BY t.datec DESC';
		$tableItems = $context->dbTool->executeS($sql);


		if(checkUserTicketRight($user, $ticketStatic, 'create')) {
			print '<div><a href="' . $context->getRootUrl('ticket_card', '&action=create') . '" class="btn btn-primary btn-strong pull-right" >' . $langs->trans('NewTicket') . '</a></div>';
		}

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
				print ' <td data-search="'.$object->ref.'" data-order="'.$object->ref.'"  ><a href="'.$context->getRootUrl('ticket_card', '&id='.$item->rowid).'">'.$object->ref.'</a></td>';
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
						'order': [[1, 'desc']], // 1 = 2e colonne

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
