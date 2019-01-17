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
		global $langs;

		if (in_array('externalaccesspage', explode(':', $parameters['context'])))
		{
		    $context = Context::getInstance();
		      
		    
		    if($context->controller == 'invoices')
		    {
		        $context->title = $langs->trans('WiewInvoices');
		        $context->desc = $langs->trans('WiewInvoicesDesc');
		        $context->menu_active[] = 'invoices';
		    }
		    elseif($context->controller == 'orders')
		    {
		        $context->title = $langs->trans('WiewOrders');
		        $context->desc = $langs->trans('WiewOrdersDesc');
		        $context->menu_active[] = 'orders';
		    }
		    elseif($context->controller == 'propals')
		    {
	            $context->title = $langs->trans('WiewPropals');
	            $context->desc = $langs->trans('WiewPropalsDesc');
	            $context->menu_active[] = 'propals';
		    }
		    elseif($context->controller == 'default')
		    {
		        $context->title = $langs->trans('Welcome');
		        $context->desc = $langs->trans('WelcomeDesc');
		        //$context->topMenu->shrink = 1; // no transparency menu
		        $context->doNotDisplayHeaderBar=1;// hide default header
		    }
		    elseif($context->controller == 'personalinformations')
		    {
		        global $user;
		        $context->title = $langs->trans('UserInfosDesc') ; //$user->firstname .' '. $user->lastname;
		        $context->desc = $user->firstname .' '. $user->lastname;; //$langs->trans('UserInfosDesc');
		        $context->meta_title = $user->firstname .' '. $user->lastname .' - '. $langs->trans('UserInfosDesc');
		        //$context->doNotDisplayHeaderBar=1;// hide default header
		        
		        if($context->action == 'save'){
		            // TODO: need to check all send informations to prevent and verbose errors
		            $user->firstname = GETPOST('firstname');
		            $user->lastname = GETPOST('lastname');
		            $user->address = GETPOST('address');
		            $user->zip = GETPOST('addresszip');
		            $user->town = GETPOST('town');
		            $user->user_mobile = GETPOST('user_mobile');
		            $user->office_phone = GETPOST('office_phone');
		            $user->office_fax = GETPOST('office_fax');
		            $user->email = GETPOST('email');
		            
		            if($user->update($user)>0)
		            {
		                header('Location: '.$context->getRootUrl('personalinformations').'&action=saved');
		            }
		            else {
		                $context->action == 'saveError';
		            }
		        }
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
	    
	    if (in_array('externalaccesspage', explode(':', $parameters['context'])))
	    {
	        $context = Context::getInstance();
	        if($context->controller == 'default')
	        {
				$context->setControllerFound();
	            include $context->tplPath .'/headbar_full.tpl.php';
	            include $context->tplPath .'/services.tpl.php';
	            return 1;
	        }
	        elseif($context->controller == 'invoices')
	        {
				$context->setControllerFound();
	            if($conf->global->EACCESS_ACTIVATE_INVOICES && !empty($user->rights->externalaccess->view_invoices))
	            {
	                $this->print_invoiceList($user->societe_id);
	            }
	            return 1;
	        }
	        elseif($context->controller == 'orders')
	        {
				$context->setControllerFound();
	            if($conf->global->EACCESS_ACTIVATE_ORDERS && !empty($user->rights->externalaccess->view_orders))
	            {
	                $this->print_orderList($user->societe_id);
	            }
	            return 1;
	        }
	        elseif($context->controller == 'propals')
	        {
				$context->setControllerFound();
	            if($conf->global->EACCESS_ACTIVATE_PROPALS && !empty($user->rights->externalaccess->view_propals))
	            {
	                $this->print_propalList($user->societe_id);
	            }
	            return 1;
	        }
	        elseif($context->controller == 'personalinformations')
	        {
				$context->setControllerFound();
	            if($context->userIslog())
	            {
	                $this->print_personalinformations();
	            }
	            return 1;
	        }
	    }
	    
		return 0;
	}
	
	public function print_invoiceList($socId = 0)
	{
	    print '<section id="section-invoice"><div class="container">';
	    //print_invoiceList($socId);
	    print_invoiceTable($socId);
	    print '</div></section>';
	}
	
	public function print_orderList($socId = 0)
	{
	    print '<section id="section-invoice"><div class="container">';
	    //print_orderList($socId);
	    print_orderListTable($socId);
	    print '</div></section>';
	}
	
	public function print_propalList($socId = 0)
	{
	    print '<section id="section-invoice"><div class="container">';
	    //print_propalList($socId);
	    print_propalTable($socId);
	    print '</div></section>';
	}
	
	public function print_personalinformations()
	{
	    global $langs,$db,$user;
	    $context = Context::getInstance();
	    
	    include $context->tplPath.'/userinfos.tpl.php';
	}
	
	private function _downloadInvoice(){
	    
	    global $langs, $db, $conf, $user;
	    $filename=false;
	    $context = Context::getInstance();
	    $id = GETPOST('id','int');
	    $forceDownload = GETPOST('forcedownload','int');
	    if(!empty($user->societe_id) && $conf->global->EACCESS_ACTIVATE_INVOICES && !empty($user->rights->externalaccess->view_invoices))
	    {
	        dol_include_once('compta/facture/class/facture.class.php');
	        $object = new Facture($db);
	        if($object->fetch($id)>0)
	        {
	            if($object->statut>=Facture::STATUS_VALIDATED && $object->socid==$user->societe_id)
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
	    
	    $context = Context::getInstance();
	    $id = GETPOST('id','int');
	    $forceDownload = GETPOST('forcedownload','int');
	    if(!empty($user->societe_id) && $conf->global->EACCESS_ACTIVATE_INVOICES && !empty($user->rights->externalaccess->view_invoices))
	    {
	        dol_include_once('comm/propal/class/propal.class.php');
	        $object = new Propal($db);
	        if($object->fetch($id)>0)
	        {
	            if($object->statut>=Propal::STATUS_VALIDATED && $object->socid==$user->societe_id)
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
	
	
	
	private function _downloadCommande(){
	    
	    global $langs, $db, $conf, $user;
	    
	    $context = Context::getInstance();
	    $id = GETPOST('id','int');
	    $forceDownload = GETPOST('forcedownload','int');
	    if(!empty($user->societe_id) && $conf->global->EACCESS_ACTIVATE_ORDERS && !empty($user->rights->externalaccess->view_orders))
	    {
	        dol_include_once('commande/class/commande.class.php');
	        $object = new Commande($db);
	        if($object->fetch($id)>0)
	        {
	            if($object->statut>=Commande::STATUS_VALIDATED && $object->socid==$user->societe_id)
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
}
