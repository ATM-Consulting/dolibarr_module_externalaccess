<?php


function print_ticketTable($socId = 0)
{
	global $langs,$db, $user;
	$context = Context::getInstance();

	dol_include_once('ticket/class/ticket.class.php');

	$langs->load('ticket');

	$sql = 'SELECT rowid ';
	$sql.= ' FROM `'.MAIN_DB_PREFIX.'ticket` t';
	$sql.= ' WHERE fk_soc = '. intval($socId);
	$sql.= ' ORDER BY t.datec DESC';
	$tableItems = $context->dbTool->executeS($sql);

	print '<div><a href="'.$context->getRootUrl('ticket_card', '&action=create').'" class="btn btn-primary pull-right" >'.$langs->trans('NewTicket').'</a></div>';


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

function print_ticketCard($ticketId = 0, $socId = 0, $action = ''){

	if($action == 'create'){
		return print_ticketCard_form($ticketId = 0, $socId = 0, $action = '');
	}

	return print_ticketCard_view($ticketId, $socId,  $action);
}


function print_ticketCard_form($ticketId = 0, $socId = 0, $action = '')
{
	global $langs,$db, $conf;
	$context = Context::getInstance();
	$out = '';

	$ticketId = intval($ticketId);

	dol_include_once('ticket/class/ticket.class.php');
	dol_include_once('user/class/user.class.php');


	/** @var Ticket $object */
	$object = new Ticket($db);
	if($ticketId > 0){
		$res = $object->fetch($ticketId);
		if($res<1){
			print '<div class="alert alert-danger" >'.$langs->transnoentities('TicketNotFound').'<div>';
			return;
		}
	}

	$out .= '<!-- ticket.lib print_ticketCard_form -->';
	//$out.= '<h5>'.$langs->trans('Ticket').' '.$object->ref.'</h5>';


	$out .= '<form role="form" autocomplete="off" class="form" method="post"  action="'.$context->getRootUrl('ticket_card').'" >';

	if($object->id > 0){
		$out.= '<input type="hidden" name="track_id" value="'.$object->track_id.'" />';
		$out.= '<input type="hidden" name="id" value="'.$object->id.'" />';
	}

	//include_once DOL_DOCUMENT_ROOT . '/core/class/html.formticket.class.php';
	//$formticket = new FormTicket($db); // TODO creer une class qui etend FormTicket pour rendre les methodes compatibles

	$out .= '<div class="form-ticket-message-container" >';
	$out .= '<div class="form-group">
				<label for="ticket-subject">'.$langs->transnoentities('TicketSubject').'</label>
				<input required type="text" name="subject" class="form-control" id="ticket-subject" aria-describedby="ticket-subject-help" placeholder="'.$langs->transnoentities('TicketSubjectHere').'" maxlength="200">
				<small id="ticket-subject-help" class="form-text text-muted">'.$langs->transnoentities('TicketSubjectHelp').'</small>
			</div>';


//	$out .= '<div class="form-group">
//				<label for="ticket-subject">'.$langs->transnoentities('TicketSubject').'</label>';
//	$out .=  $formticket->selectTypesTickets($object->type_code, 'update_value_type', '', 2, 1, 1, 0, 'form-control');
//	$out .=  '<small id="ticket-subject-help" class="form-text text-muted">'.$langs->transnoentities('TicketSubjectHelp').'</small>
//			</div>';

	$out .=  '<div class="form-group">
				<label for="ticket-message">'.$langs->transnoentities('TicketMessage').'</label>
				<textarea required name="message" class="form-control" id="ticket-message" rows="10">'.dol_htmlentities($object->message).'</textarea>
			</div>
			<div class="form-btn-action-container">';

		if($object->id > 0 ){
			$out .=  '<button type="submit" class="btn btn-success pull-right" name="action" value="save" >'.$langs->transnoentities('TicketBtnSubmitSave').'</button>';
		}
		else{
			$out .=  '<button type="submit" class="btn btn-success pull-right" name="action" value="savecreate"  >'.$langs->transnoentities('TicketBtnSubmitCreate').'</button>';
		}

	$out .= '
			</div>
		</div>
	</form>
	';

	print $out;
}


/**
 * @param Ticket $object
 * @param string $action
 */
function print_ticketCard_comment_form($object, $action = '')
{
	global $langs,$db, $conf, $user;
	$langs->loadLangs(array("ticket", "externalticket@externalaccess"));
	$context = Context::getInstance();

	$out = '<!-- ticket.lib START print_ticketCard_comment_form -->';

	if(!checkUserTicketRight($user, $object, 'create')){
		$out .= '<!-- not enough right -->';
		$out .= '<!-- END print_ticketCard_comment_form -->';
		return $out;
	}


	$out .= '<form role="form" autocomplete="off" class="form" method="post"  action="'.$context->getRootUrl('ticket_card').'" >';

	if($object->id > 0){
		$out.= '<input type="hidden" name="track_id" value="'.$object->track_id.'" />';
		$out.= '<input type="hidden" name="id" value="'.$object->id.'" />';
	}
	$out .= '<div class="form-ticket-message-container" >';
	$out .=  '<div class="form-group">
				<label for="ticket-message">'.$langs->transnoentities('AddMessage').'</label>
				<textarea required name="ticket-comment" class="form-control" id="ticket-comment" placeholder="'.$langs->transnoentities('YourCommentHere').'" rows="10">'.dol_htmlentities(GETPOST('ticket-comment')).'</textarea>
			</div>
			<div class="form-btn-action-container">
				<button type="submit" class="btn btn-success pull-right" name="action" value="new-comment" >'.$langs->transnoentities('SendMessage').'</button>
			</div>';

	$out .= '</div>';
	$out .= '</form>';

	return $out;
}


function print_ticketCard_view($ticketId = 0, $socId = 0, $action = '')
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

	/*
					<div class="row clearfix form-group" id="trackId">
						<div class="col-md-4">'.$langs->transnoentities('TicketTrackId').'</div>
						<div class="col-md-8">'.$object->track_id.'</div>
					</div>
	 */

	$out.= '
		<div class="container px-0">
			<h5>'.$langs->trans('Ticket').' '.$object->ref.'</h5>
			<div class="panel panel-default" id="ticket-summary">
				<div class="panel-body">
					<div class="row clearfix form-group" id="subject">
						<div class="col-md-4">'.$langs->transnoentities('Subject').'</div>
						<div class="col-md-8">'.$object->subject.'</div>
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


		$numComments = count($object->cache_msgs_ticket);
		$iComment = 0;

		// TODO : ERGO : ajouter une conf utilisateur pour le choix d'orientation des messages (penser alors a changer l'emplacement du formulaire et du #lastcomment
		$TMessage = array_reverse($object->cache_msgs_ticket, true);

		foreach ($TMessage as $value)
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
				$out .= '<li id="comment-message-'.$actionstatic->id.'" class="timeline-code-'.strtolower($actionstatic->code).'">';
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

				if(++$iComment === $numComments) {
					$out.= '<div id="lastcomment"></div>';
				}

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

	$out.= '<hr/>';
	$out.= print_ticketCard_comment_form($object);

	print $out;
}

/**
 * @param User $user
 * @param Ticket $ticket
 * @param string $rightToTest
 * @return bool
 */
function checkUserTicketRight($user, $ticket, $rightToTest = ''){

	/*
	 * current right used in program
	 * create, addcomment, close, open
	 */
	if($user->socid && $rightToTest == 'create'){
		return true;
	}


	// TODO : Add hook
	if($user->socid > 0 && intval($ticket->socid) === intval($user->socid) ){

		if($rightToTest == 'create' || $rightToTest == 'close'){
			return true;
		}

		$TAvailableStatus = array(
			$ticket::STATUS_ASSIGNED,
			$ticket::STATUS_CLOSED,
			$ticket::STATUS_IN_PROGRESS,
			$ticket::STATUS_WAITING,
			$ticket::STATUS_READ,
			$ticket::STATUS_NEED_MORE_INFO,
			$ticket::STATUS_NOT_READ
		);
		if($rightToTest == 'addcomment' && in_array($ticket->statut , $TAvailableStatus)){
			return true;
		}


	}

	return false;
}
