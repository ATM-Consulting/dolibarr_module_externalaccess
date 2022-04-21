<?php



function print_ticketCard($ticketId = 0, $socId = 0, $action = ''){
	global $user, $langs;
	dol_include_once('ticket/class/ticket.class.php');
	$context = Context::getInstance();

	if($action == 'create'){
		$ticketStatic = new Ticket($context->dbTool->db);
		if(checkUserTicketRight($user, $ticketStatic, 'create')) {
			return print_ticketCard_form($ticketId = 0, $socId = 0, $action = '');
		}
		else{
			$context->setEventMessages($langs->trans('ErrorNoRightToCreateTicket'), 'errors');
		}
	}

	return print_ticketCard_view($ticketId, $socId,  $action);
}


function print_ticketCard_form($ticketId = 0, $socId = 0, $action = '')
{
	global $langs,$db, $conf;

	$out = '';

	$ticketId = intval($ticketId);

	dol_include_once('ticket/class/ticket.class.php');
	dol_include_once('user/class/user.class.php');
	dol_include_once('externalaccess/class/html.formexternal.class.php');


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

	$formExternal = new FormExternal($db, 'ticket_card');
	$formExternal->formAttributes['autocomplete'] = 'off';
	$formExternal->formAttributes['role'] = 'form';
	$formExternal->formAttributes['class'] = 'form';

	if($object->id > 0){
		$formExternal->formHiddenInputs['track_id'] = $object->track_id;
		$formExternal->formHiddenInputs['id'] = $object->id;
	}

	$item = $formExternal->newItem('subject');
	$item->setAsRequired();
	$item->nameText = $langs->transnoentities('TicketSubjectHere');
	$item->helpText = $langs->transnoentities('TicketSubjectHelp');
	$item->fieldAttr['placeholder'] = $langs->transnoentities('TicketSubjectHere');
	$item->fieldAttr['maxlength'] = 200;

	if(empty($object->message)){
		$object->message = $conf->global->TICKET_EXTERNAL_DESCRIPTION_MESSAGE;
	}
	$item = $formExternal->newItem('message');
	$item->setAsHtml();
	$item->setAsRequired();
	$item->nameText = $langs->transnoentities('TicketMessage');
	$item->fieldValue = dol_htmlentities($object->message);

	if($object->id > 0 ){
		$formExternal->btAttributes['action'] = 'save';
		$formExternal->btAttributes['text'] = $langs->transnoentities('TicketBtnSubmitSave');
	}
	else{
		$formExternal->btAttributes['action'] = 'savecreate';
		$formExternal->btAttributes['text'] = $langs->transnoentities('TicketBtnSubmitCreate');
	}


	$out .= $formExternal->generateOutput(true, 'ticket');

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
	global $langs,$db, $conf, $user, $hookmanager;
	$context = Context::getInstance();
	$out = '';

	dol_include_once('ticket/class/ticket.class.php');
	dol_include_once('user/class/user.class.php');

	$langs->load('ticket');

	/** @var Ticket $object */
	if(!empty($context->fetchedTicket)){
		$object = $context->fetchedTicket;
	}else{
		if(!empty($ticketId)){
			$object = new Ticket($db);
			$object->fetch($ticketId);
		}
	}


	if(empty($object->id)){
		$context->controller_found = false;
		return '';
	}

	// Droits d'accès
	if($object->fk_soc != $user->socid && (!$user->employee && empty($user->socid))){
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
	$outEaNavbar = getEaNavbar($context->getRootUrl('tickets', '&save_lastsearch_values=1'));

	/*
					<div class="row clearfix form-group" id="trackId">
						<div class="col-md-4">'.$langs->transnoentities('TicketTrackId').'</div>
						<div class="col-md-8">'.$object->track_id.'</div>
					</div>
	 */


	$documents = externalAccessGetTicketEcmList($object, true);
	$ticketFooter = '';
	if(!empty($documents))
	{
		$ticketFooter.= '<div class="panel-footer">';
		foreach ($documents as $doc)
		{
			$ticketFooter.= '<span id="document_'.$doc->id.'" class="timeline-documents" ';
			$ticketFooter.= ' data-id="'.$doc->id.'" ';
			$ticketFooter.= ' data-path="'.$doc->filepath.'"';
			$ticketFooter.= ' data-filename="'.dol_escape_htmltag($doc->filename).'" ';
			$ticketFooter.= '>';

			$filePath = DOL_DATA_ROOT . '/'. $doc->filepath . '/'. $doc->filename;
			$mime = dol_mimetype($filePath);
			$file = $object->id.'/'.$doc->filename;

			$mimeAttr = ' mime="'.$mime.'" ';
			$class = '';
			if(in_array($mime, array('image/png', 'image/jpeg', 'application/pdf'))){
				$class.= ' documentpreview';
			}

			if(!empty($doc->share)){
				$doclink = $context->getRootUrl(false, array('action'=> 'get-file', 'share' => $doc->share)).'script/interface.php?action=get-file&amp;share='.$doc->share;
				$ticketFooter.= '<a href="'.$doclink.'" class="btn-link '.$class.'" target="_blank"  '.$mimeAttr.' >';
				$ticketFooter.= img_mime($filePath).' '.$doc->filename;
				$ticketFooter.= '</a>';
			}
			else{
				$ticketFooter.= img_mime($filePath).' '.$doc->filename;
			}

			$ticketFooter.= '</span>';
		}
		$ticketFooter.= '</div>';
	}

	$ticketMorePanelBodyBottom = $ticketMorePanelBodyTop = '';

	$ticketMorePanelBodyBottom = print_ticketCard_extrafields($object);

	$parameters=array(
		'controller' => $context->controller,
		'out' =>& $out,
		'outEaNavbar' =>& $outEaNavbar,
		'ticketFooter' =>& $ticketFooter,
		'ticketMorePanelBodyBottom' =>& $ticketMorePanelBodyBottom,
		'ticketMorePanelBodyTop' =>& $ticketMorePanelBodyTop,
	);
	$reshook=$hookmanager->executeHooks('externalAccessTicketCardSummary',$parameters,$object, $context->action);    // Note that $action and $object may have been modified by hook
	if ($reshook > 0) {
		$out.= $hookmanager->resPrint;
	}elseif ($reshook < 0) {
		$context->setEventMessages($hookmanager->error,$hookmanager->errors,'errors');
	}
	else{
		$out.= $outEaNavbar;
		$out.= '
		<div class="container px-0">
			<h5>'.$langs->trans('Ticket').' '.$object->ref.'</h5>
			<div class="panel panel-default" id="ticket-summary">
				<div class="panel-body">
					'.$ticketMorePanelBodyTop.'
					<div class="row clearfix form-group" id="subject">
						<div class="col-md-2">'.$langs->transnoentities('Subject').'</div>
						<div class="col-md-10">'.$object->subject.'</div>
					</div>
					<div class="row clearfix form-group" id="status">
						<div class="col-md-2">'.$langs->transnoentities('Status').'</div>
						<div class="col-md-10">'.ticketLibStatut($object).'</div>
					</div>
					<div class="row clearfix form-group" id="Type">
						<div class="col-md-2">'.$langs->transnoentities('Type').'</div>
						<div class="col-md-10">'.$object->type_label.'</div>
					</div>
					<div class="row clearfix form-group" id="Severity">
						<div class="col-md-2">'.$langs->transnoentities('Severity').'</div>
						<div class="col-md-8">'.$object->severity_label.'</div>
					</div>
					<div class="row clearfix form-group" id="DateCreation">
						<div class="col-md-2">'.$langs->transnoentities('DateCreation').'</div>
						<div class="col-md-10">'.dol_print_date($object->datec, 'dayhour').'</div>
					</div>
					<div class="row clearfix form-group" id="Author">
						<div class="col-md-2">'.$langs->transnoentities('Author').'</div>
						<div class="col-md-10">'.$author.'</div>
					</div>
					<div class="row clearfix form-group" id="InitialMessage">
						<div class="col-md-2">'.$langs->transnoentities('InitialMessage').'</div>
						<div class="col-md-10">'.$object->message.'</div>
					</div>
					'.$ticketMorePanelBodyBottom.'
				</div>
				'.$ticketFooter.'
			</div>
		</div>';
	}

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
					// n'est pas censé arrivé
					// $out .= $langs->trans('TicketNewMessage');
					$out .= ' <em>('.$langs->trans('Private').')</em>';
				}

				$out .= '</span>';

				$out .= '</h3>';

				$out.= '<div class="timeline-body">'.nl2br($value['message']).'</div>';

				$footer = ''; // init footer as empty
				$documents = externalAccessGetTicketActionCommEcmList($actionstatic, true) ;
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

						$mimeAttr = ' mime="'.$mime.'" ';
						$class = '';
						if(in_array($mime, array('image/png', 'image/jpeg', 'application/pdf'))){
							$class.= ' documentpreview';
						}

						if(!empty($doc->share)){
							$doclink = $context->getRootUrl(false, array('action'=> 'get-file', 'share' => $doc->share));
							$footer.= '<a href="'.$doclink.'" class="btn-link '.$class.'" target="_blank"  '.$mimeAttr.' >';
						$footer.= img_mime($filePath).' '.$doc->filename;
							$footer.= '</a>';
						}
						else{
							$footer.= img_mime($filePath).' '.$doc->filename;
						}

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

	$outCommentForm = print_ticketCard_comment_form($object);

	$parameters=array(
		'controller' => $context->controller,
		'out' =>& $out,
		'outCommentForm' =>& $outCommentForm,
	);
	$reshook=$hookmanager->executeHooks('externalAccessTicketCard',$parameters,$object, $context->action);    // Note that $action and $object may have been modified by hook
    if ($reshook > 0) {
		print $hookmanager->resPrint;
	}elseif ($reshook < 0) {
		$context->setEventMessages($hookmanager->error,$hookmanager->errors,'errors');
	}
	else{
		print $out.$outCommentForm;
	}
}

/**
 * Méthode ShowOutputField des extrafields adaptée
 * @param Ticket $ticket
 * @return string
 * @throws Exception
 */
function print_ticketCard_extrafields($ticket) {
	global $conf, $db, $langs;
	dol_include_once('core/class/extrafields.class.php');
	$out = '';
	$e = new ExtraFields($db);
	$e->fetch_name_optionals_label('ticket');
	$TTicketAddedField = unserialize($conf->global->EACCESS_CARD_ADDED_FIELD_TICKET);
	if(! empty($TTicketAddedField)) {
		foreach($TTicketAddedField as $ticket_field) {
			$ticket_field = strtr($ticket_field, array('EXTRAFIELD_' => ''));
			$label = $e->attributes['ticket']['label'][$ticket_field];
			$type = $e->attributes['ticket']['type'][$ticket_field];
			$size = $e->attributes['ticket']['size'][$ticket_field];            // Can be '255', '24,8'...
			$default = $e->attributes['ticket']['default'][$ticket_field];
			$computed = $e->attributes['ticket']['computed'][$ticket_field];
			$unique = $e->attributes['ticket']['unique'][$ticket_field];
			$required = $e->attributes['ticket']['required'][$ticket_field];
			$param = $e->attributes['ticket']['param'][$ticket_field];
			$perms = dol_eval($e->attributes['ticket']['perms'][$ticket_field], 1);
			$langfile = $e->attributes['ticket']['langfile'][$ticket_field];
			$list = dol_eval($e->attributes['ticket']['list'][$ticket_field], 1);
			$help = $e->attributes['ticket']['help'][$ticket_field];
			$hidden = (empty($list) ? 1 : 0);
			if($type == 'separate') $out .= '<hr style="max-width : 100%;">';
			else {
				$value = $ticket->array_options['options_'.$ticket_field];
				if($type == 'text') {
					$value = dol_htmlentitiesbr($value);
				}
				else if($type == 'html') {
					$value = dol_htmlentitiesbr($value);
				}
				else if($type == 'password') {
					$value = dol_trunc(preg_replace('/./i', '*', $value), 8, 'right', 'UTF-8', 1);
				}
				else if($type == 'date') {
					$showsize = 10;
					$value = dol_print_date($value, 'day');    // For date without hour, date is always GMT for storage and output
				}
				else if($type == 'datetime') {
					$showsize = 19;
					$value = dol_print_date($value, 'dayhour', 'tzuserrel');
				}
				else if($type == 'int') {
					$showsize = 10;
				}
				else if($type == 'double') {
					if(! empty($value)) {
						//$value=price($value);
						$sizeparts = explode(",", $size);
						$number_decimals = $sizeparts[1];
						$value = price($value, 0, $langs, 0, 0, $number_decimals, '');
					}
				}
				else if($type == 'boolean') {
					$checked = '';
					if(! empty($value)) {
						$checked = ' checked ';
					}
					$value = '<input type="checkbox" '.$checked.' readonly disabled>';
				}
				else if($type == 'mail') {
					$value = dol_print_email($value, 0, 0, 0, 64, 1, 0);
				}
				else if($type == 'url') {
					$value = dol_print_url($value, '_blank', 32, 1);
				}
				else if($type == 'phone') {
					$value = dol_print_phone($value, '', 0, 0, '', '&nbsp;', '');
				}
				else if($type == 'price') {
					//$value = price($value, 0, $langs, 0, 0, -1, $conf->currency);
					if($value || $value == '0') {
						$value = price($value, 0, $langs, 0, $conf->global->MAIN_MAX_DECIMALS_TOT, -1).' '.$langs->getCurrencySymbol($conf->currency);
					}
				}
				else if($type == 'select') {
					$valstr = (! empty($param['options'][$value]) ? $param['options'][$value] : '');
					if(($pos = strpos($valstr, "|")) !== false) {
						$valstr = substr($valstr, 0, $pos);
					}
					if($langfile && $valstr) {
						$value = $langs->trans($valstr);
					}
					else {
						$value = $valstr;
					}
				}
				else if($type == 'sellist') {
					$param_list = array_keys($param['options']);
					$InfoFieldList = explode(":", $param_list[0]);

					$selectkey = "rowid";
					$keyList = 'rowid';

					if(count($InfoFieldList) >= 3) {
						$selectkey = $InfoFieldList[2];
						$keyList = $InfoFieldList[2].' as rowid';
					}

					$fields_label = explode('|', $InfoFieldList[1]);
					if(is_array($fields_label)) {
						$keyList .= ', ';
						$keyList .= implode(', ', $fields_label);
					}

					$filter_categorie = false;
					if(count($InfoFieldList) > 5) {
						if($InfoFieldList[0] == 'categorie') {
							$filter_categorie = true;
						}
					}

					$sql = 'SELECT '.$keyList;
					$sql .= ' FROM '.MAIN_DB_PREFIX.$InfoFieldList[0];
					if(strpos($InfoFieldList[4], 'extra') !== false) {
						$sql .= ' as main';
					}
					if($selectkey == 'rowid' && empty($value)) {
						$sql .= " WHERE ".$selectkey." = 0";
					}
					else if($selectkey == 'rowid') {
						$sql .= " WHERE ".$selectkey." = ".((int) $value);
					}
					else {
						$sql .= " WHERE ".$selectkey." = '".$db->escape($value)."'";
					}

					//$sql.= ' AND entity = '.$conf->entity;

					dol_syslog(get_class($e).':showOutputField:$type=sellist', LOG_DEBUG);
					$resql = $db->query($sql);
					if($resql) {
						if($filter_categorie === false) {
							$value = ''; // value was used, so now we reste it to use it to build final output

							$obj = $db->fetch_object($resql);

							// Several field into label (eq table:code|libelle:rowid)
							$fields_label = explode('|', $InfoFieldList[1]);

							if(is_array($fields_label) && count($fields_label) > 1) {
								foreach($fields_label as $field_toshow) {
									$translabel = '';
									if(! empty($obj->$field_toshow)) {
										$translabel = $langs->trans($obj->$field_toshow);
									}
									if($translabel != $field_toshow) {
										$value .= dol_trunc($translabel, 18).' ';
									}
									else {
										$value .= $obj->$field_toshow.' ';
									}
								}
							}
							else {
								$translabel = '';
								if(! empty($obj->{$InfoFieldList[1]})) {
									$translabel = $langs->trans($obj->{$InfoFieldList[1]});
								}
								if($translabel != $obj->{$InfoFieldList[1]}) {
									$value = dol_trunc($translabel, 18);
								}
								else {
									$value = $obj->{$InfoFieldList[1]};
								}
							}
						}
						else {
							require_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';

							$toprint = array();
							$obj = $db->fetch_object($resql);
							$c = new Categorie($db);
							$c->fetch($obj->rowid);
							$ways = $c->print_all_ways(); // $ways[0] = "ccc2 >> ccc2a >> ccc2a1" with html formatted text
							foreach($ways as $way) {
								$toprint[] = '<li class="select2-search-choice-dolibarr noborderoncategories">'.img_object('', 'category').' '.$way.'</li>';
							}
							$value = '<div class="select2-container-multi-dolibarr" style="width: 90%;"><ul class="select2-choices-dolibarr">'.implode(' ', $toprint).'</ul></div>';
						}
					}
					else {
						dol_syslog(get_class($e).'::showOutputField error '.$db->lasterror(), LOG_WARNING);
					}
				}
				else if($type == 'radio') {
					$value = $param['options'][$value];
				}
				else if($type == 'checkbox') {
					$value_arr = explode(',', $value);
					$value = '';
					if(is_array($value_arr)) {
						foreach($value_arr as $keyval => $valueval) {
							if(empty($valueval)) continue;
							$toprint[] = '<li class="select2-search-choice-dolibarr noborderoncategories" >'.$param['options'][$valueval].'</li>';
						}
					}
					if(!empty($toprint)) $value = '<div class="select2-container-multi-dolibarr" style="width: 90%;"><ul class="select2-choices-dolibarr">'.implode(' ', $toprint).'</ul></div>';
				}
				else if($type == 'chkbxlst') {
					$value_arr = explode(',', $value);

					$param_list = array_keys($param['options']);
					$InfoFieldList = explode(":", $param_list[0]);

					$selectkey = "rowid";
					$keyList = 'rowid';

					if(count($InfoFieldList) >= 3) {
						$selectkey = $InfoFieldList[2];
						$keyList = $InfoFieldList[2].' as rowid';
					}

					$fields_label = explode('|', $InfoFieldList[1]);
					if(is_array($fields_label)) {
						$keyList .= ', ';
						$keyList .= implode(', ', $fields_label);
					}

					$filter_categorie = false;
					if(count($InfoFieldList) > 5) {
						if($InfoFieldList[0] == 'categorie') {
							$filter_categorie = true;
						}
					}

					$sql = 'SELECT '.$keyList;
					$sql .= ' FROM '.MAIN_DB_PREFIX.$InfoFieldList[0];
					if(strpos($InfoFieldList[4], 'extra') !== false) {
						$sql .= ' as main';
					}
					// $sql.= " WHERE ".$selectkey."='".$this->db->escape($value)."'";
					// $sql.= ' AND entity = '.$conf->entity;

					dol_syslog(get_class($e).':showOutputField:$type=chkbxlst', LOG_DEBUG);
					$resql = $db->query($sql);
					if($resql) {
						if($filter_categorie === false) {
							$value = ''; // value was used, so now we reste it to use it to build final output
							$toprint = array();
							while($obj = $db->fetch_object($resql)) {
								// Several field into label (eq table:code|libelle:rowid)
								$fields_label = explode('|', $InfoFieldList[1]);
								if(is_array($value_arr) && in_array($obj->rowid, $value_arr)) {
									if(is_array($fields_label) && count($fields_label) > 1) {
										foreach($fields_label as $field_toshow) {
											$translabel = '';
											if(! empty($obj->$field_toshow)) {
												$translabel = $langs->trans($obj->$field_toshow);
											}
											if($translabel != $field_toshow) {
												$toprint[] = '<li class="select2-search-choice-dolibarr noborderoncategories" >'.dol_trunc($translabel, 18).'</li>';
											}
											else {
												$toprint[] = '<li class="select2-search-choice-dolibarr noborderoncategories"  >'.$obj->$field_toshow.'</li>';
											}
										}
									}
									else {
										$translabel = '';
										if(! empty($obj->{$InfoFieldList[1]})) {
											$translabel = $langs->trans($obj->{$InfoFieldList[1]});
										}
										if($translabel != $obj->{$InfoFieldList[1]}) {
											$toprint[] = '<li class="select2-search-choice-dolibarr noborderoncategories">'.dol_trunc($translabel, 18).'</li>';
										}
										else {
											$toprint[] = '<li class="select2-search-choice-dolibarr noborderoncategories">'.$obj->{$InfoFieldList[1]}.'</li>';
										}
									}
								}
							}
						}
						else {
							require_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';

							$toprint = array();
							while($obj = $db->fetch_object($resql)) {
								if(is_array($value_arr) && in_array($obj->rowid, $value_arr)) {
									$c = new Categorie($db);
									$c->fetch($obj->rowid);
									$ways = $c->print_all_ways(); // $ways[0] = "ccc2 >> ccc2a >> ccc2a1" with html formatted text
									foreach($ways as $way) {
										$toprint[] = '<li class="select2-search-choice-dolibarr noborderoncategories">'.img_object('', 'category').' '.$way.'</li>';
									}
								}
							}
						}
						$value = '<div class="select2-container-multi-dolibarr" style="width: 90%;"><ul class="select2-choices-dolibarr">'.implode(' ', $toprint).'</ul></div>';
					}
					else {
						dol_syslog(get_class($e).'::showOutputField error '.$db->lasterror(), LOG_WARNING);
					}
				}
				else if($type == 'link') {

					// Only if something to display (perf)
					if($value) {        // If we have -1 here, pb is into insert, not into ouptut (fix insert instead of changing code here to compensate)
						$param_list = array_keys($param['options']); // $param_list='ObjectName:classPath'

						$InfoFieldList = explode(":", $param_list[0]);
						$classname = $InfoFieldList[0];
						$classpath = $InfoFieldList[1];
						if(! empty($classpath)) {
							dol_include_once($InfoFieldList[1]);
							if($classname && class_exists($classname)) {
								$object = new $classname($db);
								$object->fetch($value);
								$value = strip_tags($object->getNomUrl(3));
							}
						}
						else {
							dol_syslog('Error bad setup of extrafield', LOG_WARNING);

							return 'Error bad setup of extrafield';
						}
					} else $value = '';
				}
				if (!$hidden) {
					$out .= '<div class="row clearfix form-group" id="Severity">';
					$out .= '<div class="col-md-2">'.$label.'</div>';
					$out .= '<div class="col-md-8"> '.$value.'</div> ';
					$out .= '</div > ';
				}
			}
		}
	}
	return $out;
}

/**
 * @param User $user
 * @param Ticket $ticket
 * @param string $rightToTest
 * @return bool
 */
function checkUserTicketRight($user, $ticket, $rightToTest = ''){

	$context = Context::getInstance();
	global $hookmanager, $db, $conf;

    if($user->employee && empty($user->socid)) $employee = true;

    // Add fields from hooks
    $parameters = array('user' => $user, 'ticket' => $ticket, 'rightToTest' => $rightToTest, 'employee' => $employee);
    $reshook = $hookmanager->executeHooks('checkUserTicketRight', $parameters);

    if($reshook == 1) return true;
    if($reshook == -1) return false;

	/*
	 * current right used in program
	 * create, comment, close, open
	 */
	if($user->socid && $rightToTest == 'create' || $employee && $user->rights->ticket->create){
        return true;
	}

	// TODO : Add hook
	if($user->socid > 0 && intval($ticket->socid) === intval($user->socid) || $employee){

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


/**
 * @param Ticket $ticket
 * @return array
 */
function getTicketPublicFiles(Ticket $ticket)
{
	global $conf, $db, $user, $dolibarr_main_url_root;

	if(empty($ticket->ref)) return false;

	$relativedir = 'ticket/'.$ticket->ref;

	include_once DOL_DOCUMENT_ROOT.'/ecm/class/ecmfiles.class.php';
	require_once DOL_DOCUMENT_ROOT.'/core/lib/security2.lib.php';
	$TPublicFiles = array();

	$filearrayindatabase = dol_dir_list_in_database($relativedir, '', null, 'name', SORT_ASC);

	// Define $urlwithroot
	$urlwithouturlroot = preg_replace('/'.preg_quote(DOL_URL_ROOT, '/').'$/i', '', trim($dolibarr_main_url_root));
	$urlwithroot = $urlwithouturlroot.DOL_URL_ROOT; // This is to use external domain name found into config file
	//$urlwithroot=DOL_MAIN_URL_ROOT;				// This is to use same domain name than current


	// Search if it exists into $filearrayindatabase
	foreach ($filearrayindatabase as $file)
	{
		if(!empty($file['rowid']) && !empty($file['share'])){
			$paramlink = '';
			if (!empty($file['share'])) $paramlink .= ($paramlink ? '&' : '').'hashp='.$file['share']; // Hash for public share
			$file['fulllink'] = $urlwithroot.'/document.php'.($paramlink ? '?'.$paramlink : '');
			$TPublicFiles[] = $file;
		}
	}

	return $TPublicFiles;
}

/**
 * @param Ticket $ticket
 * @param array  $listOfFileNames Array of files name
 * @param array  $TErrors
 * @return void
 * @throws Exception
 */
function updateFileUploadedToBePublic(Ticket $ticket, &$listOfFileNames, &$TErrors = array())
{
	global $conf, $db, $user;

	include_once DOL_DOCUMENT_ROOT.'/ecm/class/ecmfiles.class.php';
	require_once DOL_DOCUMENT_ROOT.'/core/lib/security2.lib.php';

	$relativedir = 'ticket/'.$ticket->ref;

	$filearrayindatabase = dol_dir_list_in_database($relativedir, '', null, 'name', SORT_ASC);

	$TErrors = array();
	// Complete filearray with properties found into $filearrayindatabase
	foreach ($listOfFileNames as $key => $val)
	{
		$tmpfilename = preg_replace('/\.noexe$/', '', $val);

		$found = false;
		// Search if it exists into $filearrayindatabase
		foreach ($filearrayindatabase as $key2 => $val2)
		{
			if ($val2['name'] == $tmpfilename)
			{
				$found = true;

				if(!empty($val2['rowid']) && empty($val2['share'])){
					$ecmfile = new EcmFiles($db);
					$res = $ecmfile->fetch($val2['rowid']);
					if($res>0){
						$ecmfile->share = getRandomPassword(true);
						if(empty($ecmfile->src_object_type)){
							$ecmfile->src_object_type = $ticket->element;
							$ecmfile->src_object_id =$ticket->id;
						}

						if($ecmfile->gen_or_uploaded == 'unknown'){
							$ecmfile->gen_or_uploaded = 'uploaded';
							$ecmfile->description = 'Added by external access module'; // indexed content
						}

						$result = $ecmfile->update($user);
						if ($result < 0)
						{
							$TErrors[$ecmfile->filename] = new stdClass();
							$TErrors[$ecmfile->filename]->error = $ecmfile->error;
							$TErrors[$ecmfile->filename]->errors = $ecmfile->errors;
						}
					}
				}

				break;
			}
		}

		// File not found in database
		// it's probably because of adding file by externalaccess form
		// so we add it as public
		if (!$found)    // This happen in transition toward version 6, or if files were added manually into os dir.
		{
			$rel_filename = $relativedir.'/'.$val;
			if (!preg_match('/([\\/]temp[\\/]|[\\/]thumbs|\.meta$)/', $rel_filename))     // If not a tmp file
			{
				dol_syslog("list_of_documents We found a file called '".$val."' not indexed into database. We add it");
				$ecmfile = new EcmFiles($db);

				// Add entry into database
				$filename = basename($rel_filename);
				$rel_dir = dirname($rel_filename);
				$rel_dir = preg_replace('/[\\/]$/', '', $rel_dir);
				$rel_dir = preg_replace('/^[\\/]/', '', $rel_dir);

				$ecmfile->filepath = $rel_dir;
				$ecmfile->filename = $filename;
				$ecmfile->label = md5_file(dol_osencode($relativedir.'/'.$val)); // $destfile is a full path to file
				$ecmfile->fullpath_orig = $relativedir.'/'.$val;
				$ecmfile->gen_or_uploaded = 'uploaded';
				$ecmfile->description = 'Added by external access module'; // indexed content
				$ecmfile->keyword = ''; // keyword content
				$ecmfile->src_object_type = $ticket->element;
				$ecmfile->src_object_id =$ticket->id;

				require_once DOL_DOCUMENT_ROOT.'/core/lib/security2.lib.php';
				$ecmfile->share = getRandomPassword(true);

				$result = $ecmfile->create($user);
				if ($result < 0)
				{
					$TErrors[$ecmfile->filename] = new stdClass();
					$TErrors[$ecmfile->filename]->error = $ecmfile->error;
					$TErrors[$ecmfile->filename]->errors = $ecmfile->errors;
				}
			}
		}
	}
}


/**
 * externalAccessGetTicketActionCommEcmList
 *
 * @param ActionComm $object Object ActionComm
 * @param bool       $pulicOnly
 * @return    array                            Array of documents in index table
 */
function externalAccessGetTicketActionCommEcmList($object, $pulicOnly = true)
{
	global $conf, $db;

	$documents = array();

	$sql = 'SELECT ecm.rowid as id, ecm.src_object_type, ecm.src_object_id, ecm.filepath, ecm.filename, ecm.share';
	$sql.= ' FROM '.MAIN_DB_PREFIX.'ecm_files ecm';
	$sql.= ' WHERE ecm.filepath = \'agenda/'.$object->id.'\'';
	if($pulicOnly){ $sql.= ' AND ecm.share IS NOT NULL '; }
	//$sql.= ' ecm.src_object_type = \''.$object->element.'\' AND ecm.src_object_id = '.$object->id; // Actually upload file doesn't add type
	$sql.= ' ORDER BY ecm.position ASC';

	$resql= $db->query($sql);
	if ($resql) {
		if ($db->num_rows($resql)) {
			while ($obj = $db->fetch_object($resql)) {
				$documents[$obj->id] = $obj;
			}
		}
	}

	return $documents;
}


/**
 * externalAccessGetTicketEcmList
 *
 * @param ActionComm $object Object ActionComm
 * @param bool       $pulicOnly
 * @return    array                            Array of documents in index table
 */
function externalAccessGetTicketEcmList($object, $pulicOnly = true)
{
	global $conf, $db;

	$documents = array();

	$sql = 'SELECT ecm.rowid as id, ecm.src_object_type, ecm.src_object_id, ecm.filepath, ecm.filename, ecm.share';
	$sql.= ' FROM '.MAIN_DB_PREFIX.'ecm_files ecm';
	$sql.= ' WHERE ((ecm.src_object_type = \'ticket\' ';
	$sql.= ' AND  ecm.src_object_id = '.intval($object->id).') ';
	$sql.= ' OR  ecm.filepath = \''.$db->escape('ticket/'.$object->ref).'\' )';
	if($pulicOnly){ $sql.= ' AND ecm.share IS NOT NULL '; }

	$sql.= ' AND ecm.entity = '.intval($conf->entity).' ';
	//$sql.= ' ecm.src_object_type = \''.$object->element.'\' AND ecm.src_object_id = '.$object->id; // Actually upload file doesn't add type
	$sql.= ' ORDER BY ecm.position ASC';

	$resql= $db->query($sql);
	if ($resql) {
		if ($db->num_rows($resql)) {
			while ($obj = $db->fetch_object($resql)) {
				$documents[$obj->id] = $obj;
			}
		}
	}

	return $documents;
}
