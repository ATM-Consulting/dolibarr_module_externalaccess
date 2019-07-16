<?php // Protection to avoid direct call of template
if (empty($context) || ! is_object($context))
{
	print "Error, template page can't be called as URL";
	exit;
}



global $conf,$user;

$Tmenu=array();


if($context->userIsLog())
{
    
    if($conf->global->EACCESS_ACTIVATE_PROPALS && !empty($user->rights->externalaccess->view_propals))
    {
        $Tmenu['propals'] = array(
            'id' => 'propals',
            'rank' => 10,
            'url' => $context->getRootUrl('propals'),
            'name' => $langs->trans('EALINKNAME_propals'),
        );
    }

	if($conf->global->EACCESS_ACTIVATE_ORDERS && !empty($user->rights->externalaccess->view_orders))
	{
		$Tmenu['orders'] = array(
			'id' => 'orders',
			'rank' => 20,
			'url' => $context->getRootUrl('orders'),
			'name' => $langs->trans('EALINKNAME_orders'),
		);
	}

	if($conf->global->EACCESS_ACTIVATE_EXPEDITIONS && !empty($user->rights->externalaccess->view_expeditions))
	{
		$Tmenu['expeditions'] = array(
			'id' => 'expeditions',
			'rank' => 20,
			'url' => $context->getRootUrl('expeditions'),
			'name' => $langs->trans('EALINKNAME_expeditions'),
		);
	}
    
    if($conf->global->EACCESS_ACTIVATE_INVOICES && !empty($user->rights->externalaccess->view_invoices))
    {
        $Tmenu['invoices'] = array(
            'id' => 'invoices',
            'rank' => 40,
            'url' => $context->getRootUrl('invoices'),
            'name' => $langs->trans('EALINKNAME_invoices'),
        );
    }

    if($conf->global->EACCESS_ACTIVATE_TICKETS && !empty($user->rights->externalaccess->view_tickets))
    {
        $Tmenu['tickets'] = array(
            'id' => 'tickets',
            'rank' => 50,
            'url' => $context->getRootUrl('tickets'),
            'name' => $langs->trans('EALINKNAME_tickets'),
        );
    }
    
    
    
    $Tmenu['user'] = array(
        'id' => 'user',
        'rank' => 100,
        'url' => '',
        'name' => '<i class="fa fa-user"></i> ' . $user->login,
    );
    
    $Tmenu['user']['children']['personalinformations'] = array(
        'id' => 'personalinformations',
        'rank' => 10,
        'url' => $context->getRootUrl('personalinformations'),
        'name' => '<i class="fa fa-user"></i> '.$langs->trans('PersonalInformations'),
    );
    
    $Tmenu['user']['children']['logout'] = array(
        'id' => 'logout',
        'separator' => 1,
        'rank' => 100,
        'url' => $context->getRootUrl().'logout.php',
        'name' => '<i class="fa fa-sign-out"></i> '.$langs->trans('Logout'),
    );
    
}


if(!empty($conf->global->EACCESS_GOBACK_URL)){
    $Tmenu['gobackurl'] = array(
        'id' => 'gobackurl',
        'rank' => 90,
        'url' => $conf->global->EACCESS_GOBACK_URL,
        'name' => '<i class="fa fa-external-link"></i> '.$langs->trans('EALINKNAME_gobackurl'),
    );
}




$parameters=array(
    'controller' => $context->controller,
    'Tmenu' =>& $Tmenu,
);
$reshook=$hookmanager->executeHooks('PrintTopMenu',$parameters,$context, $context->action);    // Note that $action and $object may have been modified by hook
if ($reshook < 0) $context->setEventMessages($hookmanager->error,$hookmanager->errors,'errors');

if(empty($reshook)){
    if(!empty($hookmanager->resArray)){
        $Tmenu = array_replace($Tmenu,$hookmanager->resArray);
    }
    
    if(!empty($Tmenu)){
        
        
    // Sorting
    uasort ( $Tmenu,'menuSortInv');
?>
<!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top <?php print !empty($context->topMenu->shrink)?'navbar-shrink':''; ?>" id="mainNav" <?php print !empty($context->topMenu->shrink)?'data-defaultshrink="1"':''; ?> >
      <div class="container">
        <a class="navbar-brand js-scroll-trigger" href="<?php print $context->getRootUrl();  ?>"><?php print !empty($conf->global->EACCESS_TITLE)?$conf->global->EACCESS_TITLE:$conf->global->MAIN_INFO_SOCIETE_NOM;  ?></a>
        <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarResponsive">
          <ul class="navbar-nav ml-auto">
            <?php 
                print printNav($Tmenu);
            ?>
          </ul>
        </div>
      </div>
    </nav>
    
<?php 
    }
}    