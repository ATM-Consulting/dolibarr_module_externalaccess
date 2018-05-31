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
		    
		    
		    if($conf->global->EACCESS_ACTIVATE_INVOICES && !empty($context->user->rights->externalaccess->view_invoices))
		    {
		        $context->menu_active[] = 'invoices';
		    }
		    elseif($context->controller == 'invoices')
		    {
		        
		    }
		   
		    
		    
		    if($context->controller == 'invoices')
		    {
		        $context->title = $langs->trans('WiewInvoices');
		        $context->desc = $langs->trans('WiewInvoicesDesc');
		    }
		    else
		    {
		        $context->title = $langs->trans('Welcome');
		        $context->desc = $langs->trans('WelcomeDesc');
		       
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
	            
	        }
	        elseif($context->controller == 'invoices')
	        {
	            
	        }
	    }
	    
	}
	
	public function print_invoiceList()
	{
	    global $langs;
	    $context = Context::getInstance();
	    ?>

<section id="section-invoice">
      <div class="container">
        <div class="row">
        
          <div class="col-lg-3 col-md-4 ">
			<?php  include __DIR__ .'/tpl/menu.left.tpl.php'; ?>
          </div>
          <div class="col-lg-9 col-md-8">
<?php 

$sql = 'SELECT rowid ';
$sql.= ' FROM `'.MAIN_DB_PREFIX.'facture` f';
$sql.= ' WHERE fk_soc = '. intval($context->user->societe_id);
$sql.= ' AND fk_statut > 1';
$sql.= ' ORDER BY f.datef DESC';

$tableItems = $context->dbTool->executeS($sql);

if(!empty($tableItems))
{
    
    print '<table>';
    
    print '<thead>';
    
    print '<tr>';
    print ' <th>'.$langs->trans('Ref').'</th>';
    print ' <th>'.$langs->trans('Date').'</th>';
    print ' <th></th>';
    print '</tr>';
    
    print '<thead>';
    
    print '<tbody>';
    foreach ($tableItems as $item)
    {
        $facture = new Facture($context->db);
        $facture->fetch($item->rowid);
        
        print '<tr>';
        print ' <td>'.$facture->ref.'</td>';
        print ' <td>'.$facture->datef.'</td>';
        print ' <td></td>';
        print '</tr>';
        
    }
    print '</tbody>';
    
    print '</table>';
}
else {
    print '<div class="info clearboth" >';
    print  $langs->trans('Nothing');
    print '</div>';
}

?>
          </div>
          
    </div>
  </div>
</section>


<?php 
	    
	}
	
}