<?php // Protection to avoid direct call of template
if (empty($context) || ! is_object($context))
{
    print "Error, template page can't be called as URL";
    exit;
    // Note: use fontawesome v4.7.0 : https://fontawesome.com/v4.7.0/
}

global $langs, $user, $conf;
?>

	<section id="services">
<?php if(!getDolGlobalString('EACCESS_NO_FULL_HEADBAR_FOR_HOME')){ ?>
      <div class="container">
        <div class="row">
          <div class="col-lg-12 text-center">
            <h2 class="section-heading" id="services-title"><?php print $langs->trans('Services');  ?></h2>
            <hr class="my-4">
          </div>
        </div>
      </div>
<?php } ?>
      <div class="container">
        <div class="row">

<?php
$parameters=array(
    'item' =>& $context->controller
);
$reshook=$hookmanager->executeHooks('PrintServices',$parameters,$context, $context->action);    // Note that $action and $object may have been modified by hook
if ($reshook < 0) $context->setEventMessages($hookmanager->error,$hookmanager->errors,'errors');

if(empty($reshook)){

    if(getDolGlobalInt("EACCESS_ACTIVATE_PROPALS") && $user->hasRight('externalaccess', 'view_propals')){
        $link = $context->getControllerUrl('propals');
        printService($langs->trans('Quotations'),'fa-pencil',$link); // desc : $langs->trans('QuotationsDesc')
    }

	if(getDolGlobalInt("EACCESS_ACTIVATE_ORDERS") && $user->hasRight('externalaccess', 'view_orders')){
		$link = $context->getControllerUrl('orders');
		printService($langs->trans('Orders'),'fa-file-text-o',$link); // desc : $langs->trans('OrdersDesc')
	}

	if(getDolGlobalInt("EACCESS_ACTIVATE_EXPEDITIONS") && $user->hasRight('externalaccess', 'view_expeditions')){
		$link = $context->getControllerUrl('expeditions');
		printService($langs->trans('Expeditions'),'fa-truck',$link); // desc : $langs->trans('OrdersDesc')
	}

    if(getDolGlobalInt("EACCESS_ACTIVATE_INVOICES") && isModEnabled('facture') && $user->hasRight('externalaccess', 'view_invoices')){
        $link = $context->getControllerUrl('invoices');
        printService($langs->trans('Invoices'),'fa-file-text',$link); // desc : $langs->trans('InvoicesDesc')
    }

    if(getDolGlobalInt("EACCESS_ACTIVATE_TICKETS") && isModEnabled('ticket') && $user->hasRight('externalaccess', 'view_tickets')){
        $link = $context->getControllerUrl('tickets');
        printService($langs->trans('Tickets'),'fa-ticket',$link);
    }

	if(getDolGlobalInt("EACCESS_ACTIVATE_PROJECTS") && isModEnabled('projet') && $user->hasRight('externalaccess', 'view_projects')){
		$link = $context->getControllerUrl('projects');
		printService($langs->trans('ViewProjects'),'fa-folder-open',$link);
	}

	if(getDolGlobalInt("EACCESS_ACTIVATE_SUPPLIER_INVOICES") && isModEnabled('supplier_invoice') && $user->hasRight('externalaccess', 'view_supplier_invoices')){
		$link = $context->getControllerUrl('supplier_invoices');
		printService($langs->trans('ViewSupplierInvoices'),'fa-file-text',$link);
	}

	if(getDolGlobalInt("EACCESS_ACTIVATE_TASKS") && isModEnabled('projet') && $user->hasRight('externalaccess', 'view_tasks')){
		$link = $context->getControllerUrl('tasks');
		printService($langs->trans('ViewTasks'),'fa-tasks',$link);
	}

    $link = $context->getControllerUrl('personalinformations');
    printService($langs->trans('MyPersonalInfos'),'fa-user',$link);
}

if (!empty($hookmanager->resPrint)) print $hookmanager->resPrint;
?>
        </div>
      </div>
    </section>
