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

	print '<div><a href="'.$context->getRootUrl('ticket_card', '&action=create').'" class="btn btn-primary btn-strong pull-right" >'.$langs->trans('NewTicket').'</a></div>';


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

	$out .= '<div class="form-ticket-message-container" >';
	$out .= '<div class="form-group">
				<label for="ticket-subject">'.$langs->transnoentities('TicketSubject').'</label>
				<input required type="text" name="subject" class="form-control" id="ticket-subject" aria-describedby="ticket-subject-help" placeholder="'.$langs->transnoentities('TicketSubjectHere').'" maxlength="200">
				<small id="ticket-subject-help" class="form-text text-muted">'.$langs->transnoentities('TicketSubjectHelp').'</small>
			</div>';

//	$out .= '<div class="form-group">
//				<label for="ticket-type-code">'.$langs->transnoentities('TicketTypeCode').'</label>';
//	$out .=  $formticket->selectTypesTickets($object->type_code, 'ticket-type-code', '', 2, 1, 1, 0, 'form-control');
//	$out .=  '<small id="ticket-subject-help" class="form-text text-muted">'.$langs->transnoentities('TicketSubjectHelp').'</small>
//			</div>';

	$out .=  '<div class="form-group">
				<label for="ticket-message">'.$langs->transnoentities('TicketMessage').'</label>
				<textarea required name="message" class="form-control" id="ticket-message" rows="10">'.dol_htmlentities($object->message).'</textarea>
			</div>
	';

	if (!empty($conf->global->FCKEDITOR_ENABLE_TICKET)){
		$out .= '<script>CKEDITOR.replace( "message" );</script>';
	}

	$out .=  '<div class="form-btn-action-container">';
	if($object->id > 0 ){
		$out .=  '<button type="submit" class="btn btn-success btn-strong pull-right" name="action" value="save" >'.$langs->transnoentities('TicketBtnSubmitSave').'</button>';
	}
	else{
		$out .=  '<button type="submit" class="btn btn-success btn-strong pull-right" name="action" value="savecreate"  >'.$langs->transnoentities('TicketBtnSubmitCreate').'</button>';
	}

	$out .= '
			</div>
		</div>
	</form>
	';

	print $out;
}


/**
 * @param Ticket $ticket
 * @param string $action
 * @param int $timelineIntegration  true add timeline , embed, false to disable
 * @return string
 */
function print_ticketCard_comment_form($ticket, $action = '', $timelineIntegration = 1)
{
	global $langs, $db, $conf, $user;

	dol_include_once('custom/externalaccess/class/ExternalFormTicket.class.php');
	$externalForm = new ExternalFormTicket($db);

	$langs->loadLangs(array("ticket", "externalticket@externalaccess"));
	$context = Context::getInstance();

	$out = '<!-- ticket.lib START print_ticketCard_comment_form -->';

	if (!checkUserTicketRight($user, $ticket, 'create')) {
		$out .= '<!-- not enough right -->';
		$out .= '<!-- END print_ticketCard_comment_form -->';
		return $out;
	}

	$out .= '<form role="form" autocomplete="off" class="form" method="post" enctype="multipart/form-data" action="' . $context->getRootUrl('ticket_card') . '&id=' . $ticket->id . '&time=' . time() . '#form-ticket-message-container">';
	if ($timelineIntegration) {
		if ($timelineIntegration != 'embed') $out .= '<ul class="timeline">';

		$out .= '<li class="time-label"><span class="timeline-badge-date"><i class="fa fa-comments" ></i> ' . $langs->transnoentities('AddMessage') . '</span></li>';
		$out .= '<li class="timeline-code-ticket_msg">';
		$out .= '<div class="timeline-item">';
		$out .= '<div id="form-ticket-message-container" class="' . ($timelineIntegration ? 'timeline-body' : '') . ' form-ticket-message-container">';
	}

	if ($ticket->id > 0) {
		$out .= '<input type="hidden" name="track_id" value="' . $ticket->track_id . '" />';
		$out .= '<input type="hidden" name="id" value="' . $ticket->id . '" />';
	}


	$out .= '<div class="form-group">
				<textarea name="ticket-comment" class="form-control" id="ticket-comment" placeholder="' . $langs->transnoentities('YourCommentHere') . '" rows="10">' . dol_htmlentities(GETPOST('ticket-comment', 'none')) . '</textarea>';
	$out .= '</div>';

	if (!empty($conf->global->FCKEDITOR_ENABLE_TICKET)){
		$out .= '<script>CKEDITOR.replace( "ticket-comment" );</script>';
	}


	//Files
	$externalForm->track_id = $ticket->track_id;
	$externalForm->ref = $ticket->ref;
	$externalForm->id = $ticket->id;
	$externalForm->withfile = 2;
	$externalForm->withcancel = 1;
	$externalForm->param = array('fk_user_create' => $user->id);
	$out .=	'<div class="form-group">';
	$out .= $externalForm->showFilesForm();
	$out .= '</div>';

	$out .= '</div><!-- end timeline-body -->';

	$out .=	'<div class="'.($timelineIntegration?'timeline-footer':'').' text-right">';




	$status = (int)$ticket->fk_statut; // TODO : vérifier sur les nouvelles version si le nom du champ na pas changé

	$out .=	'<div class="btn-group">';

	$btnNewComment = '<button type="submit" class="btn btn-success" name="action" value="new-comment" data-toggle="tooltip" title="'.dol_htmlentities($langs->transnoentities('SendMessageHelp'),ENT_QUOTES).'"  >'.$langs->transnoentities('SendMessage').'</button>';
	$btnCommentAndReopen = '<button type="submit" class="btn btn-primary" name="action" value="new-comment-reopen" data-toggle="tooltip" title="'.dol_htmlentities($langs->transnoentities('SendMessageAndReopenHelp'),ENT_QUOTES).'" >'.$langs->transnoentities('SendMessageAndReopen').'</button>';
	$dropDown = false;
	if (in_array($status, array(
		$ticket::STATUS_NOT_READ,
		$ticket::STATUS_READ,
		$ticket::STATUS_ASSIGNED,
		$ticket::STATUS_IN_PROGRESS,
		$ticket::STATUS_WAITING))
	){
		$out .=	$btnNewComment;
	}
	elseif ($status == $ticket::STATUS_NEED_MORE_INFO) {
		$out .=	'<button type="submit" class="btn btn-primary" name="action" value="new-comment"  data-toggle="tooltip" title="'.dol_htmlentities($langs->transnoentities('SendAnswerMessageHelp'),ENT_QUOTES).'"  >'.$langs->transnoentities('SendAnswerMessage').'</button>';
		$out .=	'<button type="submit" class="btn btn-success" name="action" value="new-comment-close" >'.$langs->transnoentities('SendAnswerMessageAndClose').'</button>';

		//$dropDown = true;
	}
	elseif ($status == $ticket::STATUS_CANCELED) {
		if (!checkUserTicketRight($user, $ticket, 'reopen')) {
			$out .=	$btnCommentAndReopen;
		}
	}
	elseif ($status == $ticket::STATUS_CLOSED) {
		$out .= '<button type="submit" class="btn btn-success" name="action" value="new-comment" data-toggle="tooltip" title="'.dol_htmlentities($langs->transnoentities('SendMessageOnClosedStatusHelp'),ENT_QUOTES).'"  >'.$langs->transnoentities('SendMessageOnClosedStatus').'</button>';
		if (!checkUserTicketRight($user, $ticket, 'reopen')) {
			$out .=	$btnCommentAndReopen;
		}
		//$dropDown = true;
	}

	if($dropDown){
		// j'ai préparé un drop down pour plus tard au niveau interface utilisateur
		$out .= '<button type="button" class="btn btn-secondary btn-strong dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="sr-only">'.$langs->transnoentities('SendCommentOtherActions').'</span> <i class="fa fa-cog" aria-hidden="true"></i> </button>';
		$out .= '<div class="dropdown-menu">';

		if ($status == $ticket::STATUS_NEED_MORE_INFO) {
			$out .= '<button type="submit" class="dropdown-item" name="action" value="new-comment-test" >'.$langs->transnoentities('test').'</button>';
		}
		elseif ($status == $ticket::STATUS_CLOSED) {
			$out .= '<button type="submit" class="dropdown-item" name="action" value="new-comment-test" >'.$langs->transnoentities('test').'</button>';
		}

		$out .= '</div>';
	}


	$out .=	'</div"><!-- end btn-group -->';

	$out .= '</div><!-- end timeline-footer -->';

	if($timelineIntegration) {
		$out .= '</div><!-- end timeline-item -->';
		$out .= '</li>';
		if ($timelineIntegration != 'embed') $out .= '</ul>';
	}

	$out .= '</form><!-- end form -->';
	return $out;
}


function print_ticketCard_view($ticketId = 0, $socId = 0, $action = '')
{
	global $langs,$db, $conf, $user;
	$context = Context::getInstance();
	$out = '';

	dol_include_once('ticket/class/ticket.class.php');
	dol_include_once('user/class/user.class.php');

	$langs->load('ticket');

	/** @var Ticket $object */
	if(!empty($context->fetchedTicket)){
		$object = $context->fetchedTicket;
	}else{
		$object = new Ticket($db);
		$object->fetch($ticketId);
	}

	if(empty($object->id)  || $object->fk_soc != $user->socid){
		$context->controller_found = false;
		return '';
	}

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
						<div class="col-md-8">'.ticketLibStatut($object).'</div>
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
		require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';

		$actionstatic = new ActionComm($db);

		// Sort messages
		$sortMsg = !empty($user->conf->EA_TICKET_MSG_SORT_ORDER)?$user->conf->EA_TICKET_MSG_SORT_ORDER:'asc';
		$getSortMsg = GETPOST('sortmsg', 'none');
		if(!empty($getSortMsg) && in_array($getSortMsg, array('asc','desc'))){
			$sortMsg = $getSortMsg;
			dol_set_user_param($db, $conf,$user, array('EA_TICKET_MSG_SORT_ORDER' => $sortMsg));
		}

		if($sortMsg == 'asc'){
			$sortBtn = '<a class="pull-right btn btn-light" data-toggle="tooltip" title="'.$langs->trans('SortMessagesDesc').'" href="'.$context->getRootUrl('ticket_card', '&id='.$object->id.'&sortmsg=desc').'" ><i class="fa fa-sort-numeric-desc"></i></a>';
		}else{
			$sortBtn = '<a class="pull-right btn btn-light" data-toggle="tooltip" title="'.$langs->trans('SortMessagesAsc').'"  href="'.$context->getRootUrl('ticket_card', '&id='.$object->id.'&sortmsg=asc').'" ><i class="fa fa fa-sort-numeric-asc""></i></a>';
		}

		$out.='
			<div class="container px-0">'.$sortBtn.'
				<h5>'.$langs->trans('TicketMessagesList').'</h5>
			';

		$out .= '
			<ul class="timeline">';

		$datelabel = "";


		$numComments = count($object->cache_msgs_ticket);
		$iComment = 0;

		$TMessage = $object->cache_msgs_ticket;
		if($sortMsg == 'asc') {
			$TMessage = array_reverse($object->cache_msgs_ticket, true);
		}

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

			// strange behavior, in some case $value['private'] is empty but $actionstatic->code == 'TICKET_MSG_PRIVATE'
			$TExcluseActionCode = array('TICKET_MSG_PRIVATE');
			if (empty($value['private']) || !in_array($actionstatic->code, $TExcluseActionCode))
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
					// $out .= $langs->trans('TicketNewMessage');
				}
				elseif($actionstatic->code == 'TICKET_MSG_PRIVATE') {
					// $out .= $langs->trans('TicketNewMessage');
					$out .= ' <em>('.$langs->trans('Private').')</em>';
				}

				$out .= '</span>';

				$out .= '</h3>';

				$out.= '<div class="timeline-body">'.$value['message'].'</div>';

				$footer = ''; // init footer as empty
				$documents = getTicketActionCommEcmList($actionstatic) ;
				if(!empty($documents))
				{
					$footer.= '<div class="timeline-documents-container">';
					foreach ($documents as $doc)
					{
						$footer.= '<span id="document_'.$doc->id.'" class="timeline-documents" ';
						$footer.= ' data-id="'.$doc->id.'" ';
						$footer.= ' data-path="'.$doc->filepath.'"';
						$footer.= ' data-filename="'.dol_escape_htmltag($doc->filename).'" ';
						$footer.= '>';

						$filePath = DOL_DATA_ROOT . '/'. $doc->filepath . '/'. $doc->filename;
						$mime = dol_mimetype($filePath);
						$file = $actionstatic->id.'/'.$doc->filename;
						$thumb = $actionstatic->id.'/thumbs/'.substr($doc->filename, 0, strrpos($doc->filename, '.')).'_mini'.substr($doc->filename, strrpos($doc->filename, '.'));

						// TODO : make a document.php and viewimage.php external access version
						//$doclink = dol_buildpath('document.php', 1).'?modulepart=actions&attachment=0&file='.urlencode($file).'&entity='.$conf->entity;
						//$viewlink = dol_buildpath('viewimage.php', 1).'?modulepart=actions&file='.urlencode($thumb).'&entity='.$conf->entity;

						$mimeAttr = ' mime="'.$mime.'" ';
						$class = '';
						if(in_array($mime, array('image/png', 'image/jpeg', 'application/pdf'))){
							$class.= ' documentpreview';
						}

						// TODO : uncomment link when we have a secured display document syteme
						//$footer.= '<a href="'.$doclink.'" class="btn-link '.$class.'" target="_blank"  '.$mimeAttr.' >';
						$footer.= img_mime($filePath).' '.$doc->filename;
						//$footer.= '</a>';

						$footer.= '</span>';
					}
					$footer.= '</div>';
				}

				if(!empty($footer)){
					$out.='<div class="timeline-footer">'.$footer.'</div>';
				}

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
	 * create, comment, close, open
	 */
	if($user->socid && $rightToTest == 'create'){
		return true;
	}


	// TODO : Add hook
	if($user->socid > 0 && intval($ticket->socid) === intval($user->socid) ){

		if($rightToTest == 'close'){
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
		if($rightToTest == 'comment' && in_array($ticket->statut , $TAvailableStatus)){
			return true;
		}


	}

	return false;
}


/**
 *    Return status label of object
 *
 * @param Ticket $ticket
 * @param int $mode
 * @return     string                 Label
 */
function ticketLibStatut(Ticket $ticket, $mode = 2)
{
	// phpcs:enable
	global $langs;

	if(intval(DOL_VERSION) > 11){
		return $ticket->getLibStatut($mode);
	}

	$status = $ticket->fk_statut;

	$ticket_statuts_short = $ticket_statuts = array($ticket::STATUS_NOT_READ => 'Unread', $ticket::STATUS_READ => 'Read', $ticket::STATUS_ASSIGNED => 'Assigned', $ticket::STATUS_IN_PROGRESS => 'InProgress', $ticket::STATUS_NEED_MORE_INFO => 'NeedMoreInformation', $ticket::STATUS_WAITING => 'Suspended', $ticket::STATUS_CLOSED => 'Closed', $ticket::STATUS_CANCELED => 'Canceled');

	$labelStatus = $ticket_statuts[$status];
	$labelStatusShort = $ticket_statuts_short[$status];

	if ($status == $ticket::STATUS_NOT_READ) {
		$statusType = 'status0';
	}
	elseif ($status == $ticket::STATUS_READ) {
		$statusType = 'status1';
	}
	elseif ($status == $ticket::STATUS_ASSIGNED) {
		$statusType = 'status3';
	}
	elseif ($status == $ticket::STATUS_IN_PROGRESS) {
		$statusType = 'status4';
	}
	elseif ($status == $ticket::STATUS_WAITING) {
		$statusType = 'status3';
	}
	elseif ($status == $ticket::STATUS_NEED_MORE_INFO) {
		$statusType = 'status9';
	}
	elseif ($status == $ticket::STATUS_CANCELED) {
		$statusType = 'status9';
	}
	elseif ($status == $ticket::STATUS_CLOSED) {
		$statusType = 'status6';
	}
	else {
		$labelStatus = $langs->trans('Unknown');
		$labelStatusShort = $langs->trans('Unknown');
		$statusType = 'status0';
		$mode = 0;
	}

	return dolGetStatus($langs->trans($labelStatus), $langs->trans($labelStatusShort), '', $statusType, $mode);
}
