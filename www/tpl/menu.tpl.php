<?php // Protection to avoid direct call of template
if (empty($conf) || ! is_object($conf))
{
	print "Error, template page can't be called as URL";
	exit;
}
?>
<!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top" id="mainNav">
      <div class="container">
        <a class="navbar-brand js-scroll-trigger" href="<?php print !empty($conf->global->EACCESS_GOBACK_URL)?$conf->global->EACCESS_GOBACK_URL:'#page-top';  ?>"><?php print !empty($conf->global->EACCESS_TITLE)?$conf->global->EACCESS_TITLE:$conf->global->MAIN_INFO_SOCIETE_NOM;  ?></a>
        <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarResponsive">
          <ul class="navbar-nav ml-auto">

            <?php 
            /*
            if(!empty($conf->global->EACCESS_ACTIVATE_INVOICES))
            {
                $active = $context->menuIsActive('invoices')?'active':'';
                print '<li class="nav-item  '.$active.'"><a href="'.$context->rootUrl.'" class="nav-link" >'. $langs->trans('Invoices').'</a></li>';
            }*/
            ?>
            
            <?php if($context->userIsLog()){ ?>
            <li class="nav-item">
              <a class="nav-link js-scroll-trigger" href="<?php print $context->getRootUrl().'logout.php'; ?>"><i class="fa fa-sign-out"></i> <?php print $langs->trans('Logout'); ?></a>
            </li>
            <?php } ?>

            
          </ul>
        </div>
      </div>
    </nav>