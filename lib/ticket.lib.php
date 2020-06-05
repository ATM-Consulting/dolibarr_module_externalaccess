<?php


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
						$userGetNomUrlCache[$actionstatic->userownerid] = getUserName($fuser, -1,  0, 16, 'firstelselast');
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
