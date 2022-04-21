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
 * \file    class/actions_externalaccess.class.php
 * \ingroup externalaccess
 * \brief   This file is an example hook overload class file
 *          Put some comments here
 */

/**
 * Class Actionsexternalaccess
 */
class Actionsexternalaccess
{
	/**
	 * @var array Hook results. Propagated to $hookmanager->resArray for later reuse
	 */
	public $results = array();

	/**
	 * @var string String displayed by executeHook() immediately after return
	 */
	public $resprints;

	/**
	 * @var array Errors
	 */
	public $errors = array();

	/**
	 * Constructor
	 */
	public function __construct()
	{
	}

	/**
	 * Overloading the doActions function : replacing the parent's function with the one below
	 *
	 * @param   array()         $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    &$object        The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          &$action        Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function doActions($parameters, &$object, &$action, $hookmanager)
	{
		$error = 0; // Error counter
		global $langs, $conf;

		if (in_array('externalaccesspage', explode(':', $parameters['context'])))
		{
		    $context = Context::getInstance();

		    if($context->controller == 'ticket_card' && !empty($conf->ticket->enabled))
            {
				$this->actionTicketCard($parameters, $object, $action, $hookmanager);
            }
		}

	}

	/**
	 * Overloading the interface function : replacing the parent's function with the one below
	 *
	 * @param   array()         $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    &$object        The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          &$action        Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function doActionInterface($parameters, &$object, &$action, $hookmanager)
	{
	    $error = 0; // Error counter
	    global $langs, $db, $conf, $user;

	    if (in_array('externalaccessinterface', explode(':', $parameters['context'])))
	    {
	        if($action === 'downloadInvoice')
	        {
	            $this->_downloadInvoice();
	        }
	        elseif ($action === 'downloadPropal')
	        {
	            $this->_downloadPropal();
	        }
	        elseif ($action === 'downloadCommande')
	        {
	            $this->_downloadCommande();
	        }
			elseif ($action === 'downloadExpedition')
			{
				$this->_downloadExpedition();
			}
	        /*elseif ($action === 'getOrdersList')
	        {
	            if($conf->global->EACCESS_ACTIVATE_ORDERS && !empty($user->rights->externalaccess->view_orders))
	            {
	                print json_orderList($user->societe_id,99999, GETPOST('offset','int'));
	                exit();
	            }
	        }
	        elseif ($action === 'getPropalsList')
	        {
	            if($conf->global->EACCESS_ACTIVATE_PROPALS && !empty($user->rights->externalaccess->view_propals))
	            {
	                print json_propalList($user->societe_id,99999, GETPOST('offset','int'));
	                exit();
	            }
	        }
	        elseif ($action === 'getInvoicesList')
	        {
	            if($conf->global->EACCESS_ACTIVATE_INVOICES && !empty($user->rights->externalaccess->view_invoices))
	            {
	                print json_invoiceList($user->societe_id,99999, GETPOST('offset','int'));
	                exit();
	            }
	        }*/

	    }
	}





	/**
	 * Overloading the PrintPageView function : replacing the parent's function with the one below
	 *
	 * @param   array()         $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    &$object        The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          &$action        Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function PrintPageView($parameters, &$object, &$action, $hookmanager)
	{
	    global $conf, $user, $langs;
	    $error = 0; // Error counter

		if(empty($user->socid)){
			$user->socid = $user->societe_id; // For compatibility support
		}

	    if (in_array('externalaccesspage', explode(':', $parameters['context'])))
	    {
	        $context = Context::getInstance();
	        if($context->controller == 'ticket_card' && !empty($conf->ticket->enabled))
            {
                $context->setControllerFound();
				$ticketId = GETPOST('id', 'int');
                if($conf->global->EACCESS_ACTIVATE_TICKETS && !empty($user->rights->externalaccess->view_tickets))
                {
                    $this->print_ticketCard($ticketId, $user->socid);
                }
                return 1;
            }
	    }

		return 0;
	}


    public function print_ticketCard($ticketId = 0, $socId = 0)
    {
        print '<section id="section-ticket-card" class="type-content"><div class="container">';
		print_ticketCard($ticketId, $socId, GETPOST('action', 'none'));
        print '</div></section>';
    }

	private function _downloadInvoice(){

	    global $langs, $db, $conf, $user;

		if(empty($user->socid)){
			$user->socid = $user->societe_id; // For compatibility support
		}

	    $filename=false;
	    $context = Context::getInstance();
	    $id = GETPOST('id','int');
	    $forceDownload = GETPOST('forcedownload','int');
         if($user->employee && empty($user->socid)) $employee = true;
		if(!empty($user->socid) && $conf->global->EACCESS_ACTIVATE_INVOICES && !empty($user->rights->externalaccess->view_invoices) || $employee)
	    {
	        dol_include_once('compta/facture/class/facture.class.php');
	        $object = new Facture($db);
	        if($object->fetch($id)>0)
	        {
	            if($object->statut>=Facture::STATUS_VALIDATED && ($object->socid==$user->socid || $employee))
	            {
					load_last_main_doc($object);
	                $filename = DOL_DATA_ROOT.'/'.$object->last_main_doc;

	                if(!empty($object->last_main_doc)){
	                    downloadFile($filename, $forceDownload);
	                }
	                else{
	                    print $langs->trans('FileNotExists');
	                }

	            }
	        }
	    }

	}

	private function _downloadPropal(){

	    global $langs, $db, $conf, $user;

		if(empty($user->socid)){
			$user->socid = $user->societe_id; // For compatibility support
		}

	    $context = Context::getInstance();
	    $id = GETPOST('id','int');
	    $forceDownload = GETPOST('forcedownload','int');
        if($user->employee && empty($user->socid)) $employee = true;
	    if(!empty($user->socid) && $conf->global->EACCESS_ACTIVATE_PROPALS && !empty($user->rights->externalaccess->view_propals) || $employee)
	    {
	        dol_include_once('comm/propal/class/propal.class.php');
	        $object = new Propal($db);
	        if($object->fetch($id)>0)
	        {
	            if($object->statut>=Propal::STATUS_VALIDATED && ($object->socid==$user->socid || $employee))
	            {
                    if (!empty($conf->global->EACCESS_RESET_LASTMAINDOC_BEFORE_DOWNLOAD_PROPAL)){
                        $object->last_main_doc = '';
                    }
                    load_last_main_doc($object);
                    $filename = DOL_DATA_ROOT.'/'.$object->last_main_doc;

	                if(!empty($object->last_main_doc)){
	                    downloadFile($filename, $forceDownload);
	                }
	                else{
	                    print $langs->trans('FileNotExists');
	                }
	            }
	        }
	    }

	}



	private function _downloadCommande(){

	    global $langs, $db, $conf, $user;

		if(empty($user->socid)){
			$user->socid = $user->societe_id; // For compatibility support
		}

	    $context = Context::getInstance();
	    $id = GETPOST('id','int');
	    $forceDownload = GETPOST('forcedownload','int');
	    if(!empty($user->socid) && $conf->global->EACCESS_ACTIVATE_ORDERS && !empty($user->rights->externalaccess->view_orders))
	    {
	        dol_include_once('commande/class/commande.class.php');
	        $object = new Commande($db);
	        if($object->fetch($id)>0)
	        {
	            if($object->statut>=Commande::STATUS_VALIDATED && $object->socid==$user->socid)
	            {
					load_last_main_doc($object);
	                $filename = DOL_DATA_ROOT.'/'.$object->last_main_doc;

	                downloadFile($filename, $forceDownload);

	                if(!empty($object->last_main_doc)){
	                    downloadFile($filename, $forceDownload);
	                }
	                else{
	                    print $langs->trans('FileNotExists');
	                }
	            }
	        }
	    }

	}


	private function _downloadExpedition(){

		global $langs, $db, $conf, $user;

		$context = Context::getInstance();
		$id = GETPOST('id','int');
		$forceDownload = GETPOST('forcedownload','int');

		if(empty($user->socid)){
			$user->socid = $user->societe_id;
		}

		if(!empty($user->socid) && $conf->global->EACCESS_ACTIVATE_EXPEDITIONS && !empty($user->rights->externalaccess->view_expeditions))
		{
			require_once DOL_DOCUMENT_ROOT . '/expedition/class/expedition.class.php';
			$object = new Expedition($db);
			if($object->fetch($id)>0)
			{
				if($object->statut>=Expedition::STATUS_VALIDATED && $object->socid==$user->socid)
				{
					load_last_main_doc($object);
					$filename = DOL_DATA_ROOT.'/'.$object->last_main_doc;

					downloadFile($filename, $forceDownload);

					if(!empty($object->last_main_doc)){
						downloadFile($filename, $forceDownload);
					}
					else{
						print $langs->trans('FileNotExists');
					}
				}
			}
		}

	}

	public function actionTicketCard($parameters, $object, $action, $hookmanager)
	{
		global $langs, $user, $conf;
		$context = Context::getInstance();
		$langs->loadLangs(array("companies", "other", "mails", "ticket", "externalticket@externalaccess"));

		dol_include_once('ticket/class/ticket.class.php');

		$ticket = new Ticket($context->dbTool->db);
		$ticketId = GETPOST('id', 'int');
		if($ticketId > 0) {
			$res = $ticket->fetch($ticketId);
			$context->fetchedTicket = $ticket;

			if($ticket->fk_soc != $user->socid && (!$user->employee && empty($user->socid)) ){
				return null;
			}
		}

		// DO ACTIONS

		// Remove file
		if (GETPOST('removedfile', 'alpha') && !GETPOST('add', 'alpha')) {
			include_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

			// Set tmp directory
			$vardir = $conf->ticket->dir_output.'/';
			$upload_dir_tmp = $vardir.'/temp/'.session_id();

			// TODO Delete only files that was uploaded from email form
			dol_remove_file_process($_POST['removedfile'], 0, 0);
		}

		if($action == "add-comment-file" || $action == "new-comment"){
			global $conf;
			if ($ticket->id > 0 && checkUserTicketRight($user, $ticket, 'comment')) {
				include_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

				// Set tmp directory TODO Use a dedicated directory for temp mails files
				$vardir = $conf->ticket->dir_output;
				$upload_dir_tmp = $vardir.'/temp/'.session_id();
				if (!dol_is_dir($upload_dir_tmp)) {
					dol_mkdir($upload_dir_tmp);
				}

				$addFileProcessRes = dol_add_file_process($upload_dir_tmp, 0, 0, 'addedfile', '', null, '', 0);
			}
		}

		$newCommentActions = array('new-comment', 'new-comment-close', 'new-comment-reopen');
		if(in_array($action, $newCommentActions)){
			if ($ticket->id > 0 && checkUserTicketRight($user, $ticket, 'comment')) {

				$ticket->message = GETPOST('ticket-comment', 'none');

				if (empty($ticket->message)) {
					$context->setEventMessages($langs->trans('TicketCommentMissing'), 'warnings');
					header('Location: '.$context->getRootUrl('ticket_card', '&id='.$ticket->id.'&time='.microtime().'#form-ticket-message-container'));
					exit;
				}

				// Copy attached files (saved into $_SESSION) as linked files to ticket. Return array with final name used.
				$resarray = $ticket->copyFilesForTicket();

				$listofpaths = $resarray['listofpaths'];
				$listofnames = $resarray['listofnames'];
				$listofmimes = $resarray['listofmimes'];


				// MANAGE STATUS
				if($action == 'new-comment-reopen'){
					$ticket->setStatut($ticket::STATUS_NOT_READ);
				}elseif($action == 'new-comment-close'){
					$ticket->setStatut($ticket::STATUS_CLOSED);
				}
				elseif (in_array($ticket->fk_statut, array(
						//$ticket::STATUS_NOT_READ,
						//$ticket::STATUS_READ,
						//$ticket::STATUS_ASSIGNED,
						//$ticket::STATUS_IN_PROGRESS,
						//$ticket::STATUS_WAITING,
						$ticket::STATUS_NEED_MORE_INFO,
						//$ticket::STATUS_CANCELED,
						//$ticket::STATUS_CLOSED
				))){
					// Leave status as is
					$ticket->setStatut($ticket::STATUS_NOT_READ);
				}
				else{
					// Leave status as is
				}

				$ret = $ticket->createTicketMessage($user, 0, $listofpaths, $listofmimes, $listofnames);

				if ($ret > 0) {
					$Terrors = array();
					// TODO remove not uploaded file from $listofnames.
					// TODO Il ne faudrait pas rendre un fichier de ce ticket public juste parceque l'utilisateur a tenté d'envoyer un fichier avec le même nom...
					if(! empty($conf->global->EACCESS_SET_UPLOADED_FILES_AS_PUBLIC)) updateFileUploadedToBePublic($ticket, $listofnames, $Terrors);

					header('Location: '.$context->getRootUrl('ticket_card', '&id='.$ticket->id.'#lastcomment'));
					exit();
				} else {
					$context->setEventMessages($langs->trans('AnErrorOccurredDuringTicketSave'), 'errors');
				}
			}
			else{
				// not enough rights
				$context->setEventMessages($langs->trans('ErrorNoRightToDoThisAction'), 'errors');
			}
		}
		elseif($action == 'savecreate' )
		{

			if(checkUserTicketRight($user, $ticket, 'create')){

				// Check
				$errors = 0;

				$ticket->message = GETPOST('message', 'none');
				$ticket->subject = GETPOST('subject', 'none');
				$ticket->fk_soc = $user->socid;

				if(empty($ticket->message)){
					$errors ++;
					$context->setEventMessages($langs->trans('MessageIsEmpty'), 'errors');
				}

				if(empty($ticket->subject)){
					$errors ++;
					$context->setEventMessages($langs->trans('SubjectIsEmpty'), 'errors');
				}

				if(empty($ticket->fk_soc)){
					$errors ++;
					$context->setEventMessages($langs->trans('SocIsEmpty'), 'errors');
				}

				$e = new ExtraFields($ticket->db);
				$e->fetch_name_optionals_label('ticket');
				$TTicketAddedField = unserialize($conf->global->EACCESS_CARD_ADDED_FIELD_TICKET);
				if(! empty($TTicketAddedField)) {
					foreach($TTicketAddedField as $ticket_field) {
						$ticket_field = strtr($ticket_field, array('EXTRAFIELD_' => ''));
						$type = $e->attributes['ticket']['type'][$ticket_field];
						if($type == 'date' || $type == 'datetime') {
							$valdate = GETPOST('options_'.$ticket_field, 'alphanohtml');
							$dateTime = DateTime::createFromFormat('Y-m-d', $valdate);
							if($type == 'datetime' && $dateTime){
								$valtime = GETPOST('options_'.$ticket_field.'-time', 'alphanohtml');
								if(!empty($valtime)) {
									list($hour, $min) = explode(':', $valtime);
									$dateTime->setTime($hour, $min);
								}
							}
							if($dateTime) $ticket->array_options['options_'.$ticket_field] = $dateTime->getTimestamp();
						}
						else if($e->setOptionalsFromPost(null, $ticket, $ticket_field) < 0) {
							$errors ++;
							$context->setEventMessages($langs->trans('ErrorFieldsRequired').' : '.$e->attributes['ticket']['label'][$ticket_field], 'errors');
						}
					}
				}

				if(empty($errors)){
					$ticket->ref = $ticket->getDefaultRef();
					$ticket->datec = time();
					$ticket->fk_statut = Ticket::STATUS_NOT_READ;

					$res = $ticket->create($user);

					if($res>0)
					{
						// Add contact to the ticket
						if(empty($user->contact_id)){
							$user->contact_id = $user->contactid; // Dolibarr < 13 retrocompatibility
						}
						$ticket->add_contact($user->contact_id, "SUPPORTCLI", 'external', 0);

						header('Location: '.$context->getRootUrl('ticket_card', '&id='.$res));
						exit();
					}else{
						$context->setEventMessages($langs->trans('AnErrorOccurredDuringTicketSave'), 'errors');
					}
				}
			}
			else{
				$context->setEventMessages($langs->trans('ErrorNoRightToCreateTicket'), 'errors');
			}
		}

		// ADAPT MENU AND TITLE
		$context->menu_active[] = 'tickets';

		if($action == 'create'){
			$context->title = $langs->trans('NewTicketTitle');
			$context->desc = $langs->trans('NewTicketTitleDesc');
		}
		elseif($ticket->id > 0){
			$context->title = $langs->trans('ViewTickets').' '.$ticket->ref;
			$context->desc = $ticket->subject;
		}
		else{
			$context->title = $langs->trans('ViewTickets');
			$context->desc = $langs->trans('ViewTicketsDesc');
		}
	}
}
