<?php // Protection to avoid direct call of template
if (empty($context) || ! is_object($context))
{
	print "Error, template page can't be called as URL";
	exit;
}
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
            
            $parameters=array(
                'controller' => $context->controller
            );
            $reshook=$hookmanager->executeHooks('PrintTopMenu',$parameters,$context, $context->action);    // Note that $action and $object may have been modified by hook
            if ($reshook < 0) $context->setEventMessages($hookmanager->error,$hookmanager->errors,'errors');
            
            if(empty($reshook) && !empty($hookmanager->resArray)){
                foreach ($hookmanager->resArray as $item){
                    $active = $context->menuIsActive($item['id'])?'active':'';
                    
                    if(!empty($item['overrride'])){
                        print $item['overrride'];
                    }
                    else {
                        print '<li class="nav-item"><a href="'.$item['url'].'" class="nav-link '.$active.'" >'. $item['name'].'</a></li>';
                    }
                    
                }
            }
            ?>
            
            <?php if($context->userIsLog()){ ?>
            <li class="nav-item dropdown">
          		<a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><i class="fa fa-user"></i> <?php print $langs->trans('Logout'); ?> <span class="caret"></span></a>
                  <ul class="dropdown-menu">
                    <!-- <li class="dropdown-item" ><a href="#">Action</a></li>
                    <li class="dropdown-item" ><a href="#">Another action</a></li>
                    <li class="dropdown-item" ><a href="#">Something else here</a></li>
                    <li role="separator" class="divider"></li>-->
                    <li class="dropdown-item"><a href="#"><i class="fa fa-user"></i> <?php print $langs->trans('PersoanalInformations'); ?></a></li> 
                    <li role="separator" class="dropdown-divider"></li>
                    <li class="dropdown-item" ><a href="<?php print $context->getRootUrl().'logout.php'; ?>"><i class="fa fa-sign-out"></i> <?php print $langs->trans('Logout'); ?></a></li>
                  </ul>
            </li>
            <?php } ?>

            
          </ul>
        </div>
      </div>
    </nav>