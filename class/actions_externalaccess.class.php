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
	public function interface($parameters, &$object, &$action, $hookmanager)
	{
	    $error = 0; // Error counter
	    global $langs, $db, $conf;
	    
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
	        
	    }
	}
	
	
	/**
	 * Overloading the PrintTopMenu function : replacing the parent's function with the one below
	 *
	 * @param   array()         $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    &$object        The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          &$action        Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function PrintTopMenu($parameters, &$object, &$action, $hookmanager)
	{
	    $error = 0; // Error counter
	    global $langs;
	    
	    if (in_array('externalaccesspage', explode(':', $parameters['context'])))
	    {
	        $context = Context::getInstance();
	        
	        if($context->conf->global->EACCESS_ACTIVATE_PROPALS && !empty($context->user->rights->externalaccess->view_propals))
	        {
	            $this->results[] = array(
	                'id' => 'propals',
	                'url' => $context->getRootUrl('propals'),
	                'name' => $langs->trans('EALINKNAME_propals'),
	            );
	        }
	        
	        if($context->conf->global->EACCESS_ACTIVATE_ORDERS && !empty($context->user->rights->externalaccess->view_orders))
	        {
	            $this->results[] = array(
	                'id' => 'orders',
	                'url' => $context->getRootUrl('orders'),
	                'name' => $langs->trans('EALINKNAME_orders'),
	            );
	        }
	        
	        if($context->conf->global->EACCESS_ACTIVATE_INVOICES && !empty($context->user->rights->externalaccess->view_invoices))
	        {
	            $this->results[] = array(
	                'id' => 'invoices',
	                'url' => $context->getRootUrl('invoices'),
	                'name' => $langs->trans('EALINKNAME_invoices'),
	            );
	        }
	        
	        
	        
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
	    $error = 0; // Error counter
	    
	    if (in_array('externalaccesspage', explode(':', $parameters['context'])))
	    {
	        $context = Context::getInstance();
	        if($context->controller == 'default')
	        {
	            include $context->tplPath .'/headbar_full.tpl.php';
	            include $context->tplPath .'/services.tpl.php';
	            return 1;
	        }
	        elseif($context->controller == 'invoices')
	        {
	            if($context->conf->global->EACCESS_ACTIVATE_INVOICES && !empty($context->user->rights->externalaccess->view_invoices))
	            {
	                $this->print_invoiceList($context->user->societe_id);
	            }
	            return 1;
	        }
	        elseif($context->controller == 'orders')
	        {
	            if($context->conf->global->EACCESS_ACTIVATE_ORDERS && !empty($context->user->rights->externalaccess->view_orders))
	            {
	                $this->print_orderList($context->user->societe_id);
	            }
	            return 1;
	        }
	        elseif($context->controller == 'propals')
	        {
	            if($context->conf->global->EACCESS_ACTIVATE_PROPALS && !empty($context->user->rights->externalaccess->view_propals))
	            {
	                $this->print_propalList($context->user->societe_id);
	            }
	            return 1;
	        }
	    }
	    
	}
	
	public function print_invoiceList($socId = 0)
	{
	    global $langs,$db;
	    $context = Context::getInstance();
	    
	    print '<section id="section-invoice"><div class="container">';
	    print_invoiceList($socId);
	    print '</div></section>';
	}
	
	public function print_orderList($socId = 0)
	{
	    global $langs,$db;
	    $context = Context::getInstance();
	    
	    print '<section id="section-invoice"><div class="container">';
	    print_orderList($socId);
	    print '</div></section>';
	}
	
	public function print_propalList($socId = 0)
	{
	    global $langs,$db;
	    $context = Context::getInstance();
	    
	    print '<section id="section-invoice"><div class="container">';
	    print_propalList($socId);
	    print '</div></section>';
	}
	
	
	private function _downloadInvoice(){
	    
	    global $langs, $db, $conf;
	    
	    $context = Context::getInstance();
	    $id = GETPOST('id','int');
	    $forceDownload = GETPOST('forcedownload','int');
	    if(!empty($context->user->societe_id) && $context->conf->global->EACCESS_ACTIVATE_INVOICES && !empty($context->user->rights->externalaccess->view_invoices))
	    {
	        dol_include_once('compta/facture/class/facture.class.php');
	        $facture = new Facture($db);
	        if($facture->fetch($id)>0)
	        {
	            if($facture->statut==Facture::STATUS_VALIDATED && $facture->socid==$context->user->societe_id)
	            {
	                $filename = DOL_DATA_ROOT.'/'.$facture->last_main_doc;
	                
	                downloadFile($filename, $forceDownload);
	            }
	        }
	    }
	
	}
	
	private function _downloadPropal(){
	    
	    global $langs, $db, $conf;
	    
	    $context = Context::getInstance();
	    $id = GETPOST('id','int');
	    $forceDownload = GETPOST('forcedownload','int');
	    if(!empty($context->user->societe_id) && $context->conf->global->EACCESS_ACTIVATE_INVOICES && !empty($context->user->rights->externalaccess->view_invoices))
	    {
	        dol_include_once('comm/propal/class/propal.class.php');
	        $object = new Propal($db);
	        if($object->fetch($id)>0)
	        {
	            if($object->statut==Propal::STATUS_VALIDATED && $object->socid==$context->user->societe_id)
	            {
	                $filename = DOL_DATA_ROOT.'/'.$object->last_main_doc;
	                
	                downloadFile($filename, $forceDownload);
	            }
	        }
	    }
	    
	}
	
	
	
	private function _downloadCommande(){
	    
	    global $langs, $db, $conf;
	    
	    $context = Context::getInstance();
	    $id = GETPOST('id','int');
	    $forceDownload = GETPOST('forcedownload','int');
	    if(!empty($context->user->societe_id) && $context->conf->global->EACCESS_ACTIVATE_ORDERS && !empty($context->user->rights->externalaccess->view_orders))
	    {
	        dol_include_once('commande/class/commande.class.php');
	        $object = new Commande($db);
	        if($object->fetch($id)>0)
	        {
	            if($object->statut==Commande::STATUS_VALIDATED && $object->socid==$context->user->societe_id)
	            {
	                $filename = DOL_DATA_ROOT.'/'.$object->last_main_doc;
	                
	                downloadFile($filename, $forceDownload);
	            }
	        }
	    }
	    
	}
}