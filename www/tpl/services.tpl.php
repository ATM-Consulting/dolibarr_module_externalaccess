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
<?php if(empty($conf->global->EACCESS_NO_FULL_HEADBAR_FOR_HOME)){ ?>
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

    if($conf->global->EACCESS_ACTIVATE_PROPALS && !empty($user->rights->externalaccess->view_propals)){
        $link = $context->getRootUrl('propals');
        printService($langs->trans('Quotations'),'fa-pencil',$link); // desc : $langs->trans('QuotationsDesc')
    }

	if($conf->global->EACCESS_ACTIVATE_ORDERS && !empty($user->rights->externalaccess->view_orders)){
		$link = $context->getRootUrl('orders');
		printService($langs->trans('Orders'),'fa-file-text-o',$link); // desc : $langs->trans('OrdersDesc')
	}

	if($conf->global->EACCESS_ACTIVATE_EXPEDITIONS && !empty($user->rights->externalaccess->view_expeditions)){
		$link = $context->getRootUrl('expeditions');
		printService($langs->trans('Expeditions'),'fa-truck',$link); // desc : $langs->trans('OrdersDesc')
	}

    if($conf->global->EACCESS_ACTIVATE_INVOICES && !empty($conf->facture->enabled) && !empty($user->rights->externalaccess->view_invoices)){
        $link = $context->getRootUrl('invoices');
        printService($langs->trans('Invoices'),'fa-file-text',$link); // desc : $langs->trans('InvoicesDesc')
    }

    if($conf->global->EACCESS_ACTIVATE_TICKETS && !empty($conf->ticket->enabled) && !empty($user->rights->externalaccess->view_tickets)){
        $link = $context->getRootUrl('tickets');
        printService($langs->trans('Tickets'),'fa-ticket',$link);
    }


    $link = $context->getRootUrl('personalinformations');
    printService($langs->trans('MyPersonalInfos'),'fa-user',$link);
}

if (!empty($hookmanager->resPrint)) print $hookmanager->resPrint;
?>
        </div>
      </div>
    </section>
